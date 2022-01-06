<?php

namespace ACFCustomDatabaseTables\Nonce;

class RebuildMapSystemNonce extends NonceBase {

	public function action() {
		return 'rebuild-map-system';
	}

	public function name() {
		return '_acfcdt_rebuild_map_system_nonce';
	}

}