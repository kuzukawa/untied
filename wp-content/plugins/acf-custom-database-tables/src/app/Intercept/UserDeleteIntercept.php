<?php

namespace ACFCustomDatabaseTables\Intercept;

class UserDeleteIntercept extends InterceptBase {

	/**
	 * Hooks anything needed by the intercept in order to intercept data for return to InterceptCoordinator
	 */
	public function init() {
		add_action( 'delete_user', [ $this, 'delete_user_data' ], 10, 2 );
	}

	/**
	 * Deletes user data only after post object has been successfully deleted
	 *
	 * @param int $user_id
	 */
	public function delete_user_data( $user_id, $reassign ) {

		$this->coordinator->delete_all_data_for_user( $user_id );

	}

}