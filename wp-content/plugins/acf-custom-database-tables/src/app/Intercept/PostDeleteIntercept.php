<?php

namespace ACFCustomDatabaseTables\Intercept;

use WP_Post;

/**
 * Intercept the post deletion process and ensure custom database table data is removed.
 *
 * @package ACFCustomDatabaseTables\Intercept
 */
class PostDeleteIntercept extends InterceptBase {

	/**
	 * Hooks anything needed by the intercept in order to intercept data for return to InterceptCoordinator
	 */
	public function init() {
		add_action( 'deleted_post', [ $this, 'delete_post_data' ], 10, 2 );
	}

	/**
	 * Deletes post data only after post object has been successfully deleted
	 *
	 * @param $post_id
	 * @param WP_Post $post
	 */
	public function delete_post_data( $post_id, $post ) {
		$this->coordinator->delete_all_data_for_post( $post_id, $post->post_type );
	}

}