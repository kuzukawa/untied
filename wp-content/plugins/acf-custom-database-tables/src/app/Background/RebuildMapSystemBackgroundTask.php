<?php

namespace ACFCustomDatabaseTables\Background;

use ACFCustomDatabaseTables\Facade\Api;
use ACFCustomDatabaseTables\Facade\App;
use ACFCustomDatabaseTables\Notice\RebuildMapSystemCompleteNotice;
use ACFCustomDatabaseTables\Notice\RebuildMapSystemRunningNotice;
use ACFCustomDatabaseTables\Utils\Arr;
use ACFCustomDatabaseTables\Vendor\BackgroundProcessing\BackgroundProcess;

class RebuildMapSystemBackgroundTask extends BackgroundProcess {

	const STATUS_INIT = 'init';
	const STATUS_RUNNING = 'running';
	const STATUS_DONE = 'done';

	protected $action = 'rebuild-map-system';
	private $state = [];

	public function listen() {
		parent::listen();
		$this->init_state();
		if ( $this->is_running() ) {
			App::make( RebuildMapSystemRunningNotice::class )->init();
		}
	}

	public function run( $data ) {
		$data = wp_parse_args( $data, [
			'batch_size' => 10,
			'field_group_keys' => [],
		] );

		// Enforce minimum batch size.
		if ( $data['batch_size'] < 1 ) {
			$data['batch_size'] = 1;
		}

		// Clear anything that is already running and start again.
		$this->cancel_process();
		$this->delete_state();

		// Push the new config and save to DB.
		$this->push_to_queue( $data );
		$this->save();

		// Set initial state.
		$this->init_state();
		$this->mark_as_running();
		$this->state['total_field_groups'] = count( $data['field_group_keys'] );
		$this->save_state();

		// Start the process.
		return $this->dispatch();
	}

	/**
	 * Cancel and clear related data.
	 */
	public function cancel() {
		$this->cancel_process();
		$this->delete_state();
	}

	protected function task( $item ) {
		// Make sure we have our status prop ready.
		$this->init_state();
		
		$n_handled = 0;
		while ( ( $n_handled < $item['batch_size'] ) and $field_group_key = array_shift( $item['field_group_keys'] ) ) {
			$n_handled ++;

			$this->rebuild_field_group_json( $field_group_key );
			$this->increment_n_field_groups_handled();
			$this->update_percentage_completed();
		}

		// If we don't have any more field group keys to handle, update the table map. But, only do it if we haven't
		// handled any JSON updates on this request.
		if ( empty( $n_handled ) and empty( $item['field_group_keys'] ) ) {
			$this->rebuild_table_map_file();
			$this->log( 'Map system rebuild complete!' );
			$this->mark_as_done(); // Mark it again, so we have the right time.
			$this->enqueue_notice();
		}

		$this->save_state();

		// If done, return false to kill the background task (along with the queue) entirely. Otherwise, return the item
		// for further processing.
		return $this->is_done() ? false : $item;
	}

	/**
	 * Get the field group object and update its JSON file. This part is a touch complicated as our JSON file generation
	 * is dependent on an ACFFieldGroup object. The ACFFieldGroup object has been modified to support both post and
	 * array-based instantiation and will work with field groups that are in JSON but not in the DB as a post.
	 *
	 * @param string $field_group_key
	 */
	private function rebuild_field_group_json( $field_group_key ) {
		$field_group_arr = acf_get_field_group( $field_group_key );
		if ( ! is_array( $field_group_arr ) ) {
			$this->log( sprintf( "ERROR: failed to rebuild JSON file for [%s] (key: %s). Could not get the ACF field group array.", $field_group_arr['title'], $field_group_key ) );

			return;
		}

		$result = Api::generate_field_group_table_json( $field_group_arr );
		if ( is_wp_error( $result ) ) {
			$this->log( sprintf( "ERROR: failed to rebuild JSON file for [%s] (key: %s). Message: %s …",
				$field_group_arr['title'],
				$field_group_key,
				$result->get_error_message()
			) );

			return;
		}

		$this->log( sprintf( "Rebuilt table JSON for field group: [%s] (key: %s) …", $field_group_arr['title'], $field_group_key ) );
	}

	private function increment_n_field_groups_handled() {
		$this->state['n_field_groups_handled'] ++;
	}

	private function update_percentage_completed() {
		// Avoid 'division by zero' warnings when we have no field groups to process.
		$this->state['percentage'] = ( $this->state( 'total_field_groups' ) > 0 )
			? $this->state['percentage'] = absint( floor( $this->state( 'n_field_groups_handled' ) / $this->state( 'total_field_groups' ) * 100 ) )
			: 99;

		// Don't allow this to fully reach 100% yet as we need to account for the last part of the process where the
		// table map PHP file is rebuilt.
		if ( $this->state( 'percentage' ) > 99 ) {
			$this->state['percentage'] = 99;
		}
	}

	private function rebuild_table_map_file() {
		$result = Api::generate_table_map_file();
		if ( is_wp_error( $result ) ) {
			$this->log( sprintf( "ERROR: failed to rebuild table map PHP file. Message: %s …", $result->get_error_message() ) );

			return;
		}

		$this->log( 'Rebuilt table map file …' );
	}

	private function log( $message ) {
		$this->state['log'][] = sprintf( '[%s] %s', $this->now(), $message );
	}

	public function is_done() {
		return $this->state( 'status' ) === self::STATUS_DONE;
	}

	public function is_running() {
		return $this->state( 'status' ) === self::STATUS_RUNNING;
	}

	public function time_completed() {
		return $this->is_done() ? $this->state( 'time_completed' ) : '';
	}

	/**
	 * @param null $key
	 *
	 * @return array|mixed
	 */
	public function state( $key = null ) {
		if ( empty( $this->state ) ) {
			$this->init_state();
		}

		// If a key is specified, return only that item. Otherwise, return the whole state array.
		return $key ? Arr::get( $this->state, $key, [] ) : $this->state;
	}

	public function percent_complete() {
		return $this->state( 'percentage' ) ?: 0;
	}

	public function logs() {
		return (array) $this->state( 'log' );
	}

	public function n_completed() {
		return (int) $this->state( 'n_field_groups_handled' );
	}

	public function n_total() {
		return (int) $this->state( 'total_field_groups' );
	}

	public function snapshot() {
		return sprintf( 'Rebuilt %d of %d field groups (%d%%)', $this->n_completed(), $this->n_total(), $this->percent_complete() );
	}

	private function init_state() {
		$stored_state = get_site_option( $this->state_key() ) ?: [];
		$this->state = wp_parse_args( $stored_state, [
			'status' => self::STATUS_INIT,
			'percentage' => 0,
			'total_field_groups' => 0,
			'n_field_groups_handled' => 0,
			'time_started' => $this->now(),
			'time_completed' => '',
			// Array of string messages to print to user.
			'log' => [],
		] );
	}

	private function save_state() {
		update_site_option( $this->state_key(), $this->state );
	}

	private function delete_state() {
		delete_site_option( $this->state_key() );
		$this->state = [];
	}

	private function state_key() {
		return sprintf( '%s-%s-status', $this->prefix, $this->action );
	}

	private function mark_as_running() {
		$this->state['status'] = self::STATUS_RUNNING;
	}

	private function mark_as_done() {
		// Make sure we don't end up with a total count greater than the total found posts.
		if ( $this->state( 'n_field_groups_handled' ) >= $this->state( 'total_field_groups' ) ) {
			$this->state['n_field_groups_handled'] = $this->state( 'total_field_groups' );
		}

		$this->state['status'] = self::STATUS_DONE;
		$this->state['time_completed'] = $this->now();
		$this->state['percentage'] = 100;
	}

	private function now() {
		return current_time( 'mysql' );
	}

	private function enqueue_notice() {
		/** @var RebuildMapSystemCompleteNotice $notice */
		$notice = App::make( RebuildMapSystemCompleteNotice::class );
		$notice->enqueue();
	}

}