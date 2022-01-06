<?php

namespace ACFCustomDatabaseTables\Model\ACFFields;

class CloneACFField extends ACFFieldBase {

	const TYPE = 'clone';

	// has a range of different configurations that all need to be accounted for
	protected $is_supported = false;
	protected $is_supported__filterable = false;

}