<?php

namespace ACFCustomDatabaseTables\Model;

/**
 * Class ACFSelector
 * @package ACFCustomDatabaseTables\Model
 */
class ACFSelector {

	public $id = 0;
	public $type = 'post';
	private $context = '';

	public static function make( $acf_selector ) {
		$info = acf_decode_post_id( $acf_selector );

		$obj = new self;
		$obj->type = $info['type'];
		$obj->id = $info['id'];
		$obj->context = $obj->context();

		return $obj;
	}

	public function context() {
		if ( $this->context ) {
			return $this->context;
		}

		$context = '';

		switch ( $this->type ) {
			case 'user':
				$context = 'user';
				break;
			case 'post':
				$context = 'post:' . get_post_type( $this->id );
		}

		return $context;
	}

}