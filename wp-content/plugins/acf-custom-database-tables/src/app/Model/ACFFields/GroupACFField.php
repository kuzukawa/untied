<?php

namespace ACFCustomDatabaseTables\Model\ACFFields;

class GroupACFField extends ACFFieldBase {

	const TYPE = 'group';

	// these come in as group-name_field-name meta entries, so we need to dig into this a bit more
	protected $is_supported = false;
	protected $is_supported__filterable = false;

}