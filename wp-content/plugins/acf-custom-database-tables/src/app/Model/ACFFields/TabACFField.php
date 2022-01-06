<?php

namespace ACFCustomDatabaseTables\Model\ACFFields;

class TabACFField extends ACFFieldBase {

	const TYPE = 'tab';

	// this field doesn't store values so we naturally don't support it
	protected $is_supported = false;
	protected $is_supported__filterable = false;

}