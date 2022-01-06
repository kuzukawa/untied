<?php

namespace ACFCustomDatabaseTables\Factory;

use ACFCustomDatabaseTables\Model\ACFFieldGroup;
use WP_Post;

class ACFFieldGroupFactory {

	public function make_from_post_id( $post_id ) {
		return new ACFFieldGroup( get_post( $post_id ) );
	}

	public function make_from_post_object( WP_Post $post ) {
		return new ACFFieldGroup( $post );
	}

	public function make_from_field_group_array( array $field_group ) {
		if ( ! empty( $field_group['ID'] ) ) {
			$post = get_post( $field_group['ID'] );
			if ( $post instanceof WP_Post ) {
				return new ACFFieldGroup( $post );
			}
		}

		return new ACFFieldGroup( $field_group );
	}

}