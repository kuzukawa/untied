<?php

namespace ACFCustomDatabaseTables\Model\ACFFields;

class GenericField extends ACFFieldBase {

	const TYPE = '_generic';

	/**
	 * Support value here is false to ensure consistent behaviour as previous versions of plugin. Third party fields can
	 * be marked as supported by using available filters in @see \ACFCustomDatabaseTables\Model\ACFFields\ACFFieldBase::is_supported().
	 *
	 * @var bool
	 */
	protected $is_supported = false;

}