<?php

namespace ACFCustomDatabaseTables\Provider;

use ACFCustomDatabaseTables\Factory\ACFFieldFactory;
use ACFCustomDatabaseTables\Factory\ACFFieldGroupFactory;
use ACFCustomDatabaseTables\Model\ACFFields\AccordionACFField;
use ACFCustomDatabaseTables\Model\ACFFields\ButtonGroupACFField;
use ACFCustomDatabaseTables\Model\ACFFields\CheckboxACFField;
use ACFCustomDatabaseTables\Model\ACFFields\CloneACFField;
use ACFCustomDatabaseTables\Model\ACFFields\ColorPickerACFField;
use ACFCustomDatabaseTables\Model\ACFFields\DatePickerACFField;
use ACFCustomDatabaseTables\Model\ACFFields\DateTimePickerACFField;
use ACFCustomDatabaseTables\Model\ACFFields\GenericField;
use ACFCustomDatabaseTables\Model\ACFFields\EmailACFField;
use ACFCustomDatabaseTables\Model\ACFFields\FileACFField;
use ACFCustomDatabaseTables\Model\ACFFields\FlexibleContentACFField;
use ACFCustomDatabaseTables\Model\ACFFields\GalleryACFField;
use ACFCustomDatabaseTables\Model\ACFFields\GoogleMapACFField;
use ACFCustomDatabaseTables\Model\ACFFields\GroupACFField;
use ACFCustomDatabaseTables\Model\ACFFields\ImageACFField;
use ACFCustomDatabaseTables\Model\ACFFields\LinkACFField;
use ACFCustomDatabaseTables\Model\ACFFields\MessageACFField;
use ACFCustomDatabaseTables\Model\ACFFields\NumberACFField;
use ACFCustomDatabaseTables\Model\ACFFields\OembedACFField;
use ACFCustomDatabaseTables\Model\ACFFields\PageLinkACFField;
use ACFCustomDatabaseTables\Model\ACFFields\PasswordACFField;
use ACFCustomDatabaseTables\Model\ACFFields\PostObjectACFField;
use ACFCustomDatabaseTables\Model\ACFFields\RadioACFField;
use ACFCustomDatabaseTables\Model\ACFFields\RangeACFField;
use ACFCustomDatabaseTables\Model\ACFFields\RelationshipACFField;
use ACFCustomDatabaseTables\Model\ACFFields\RepeaterACFField;
use ACFCustomDatabaseTables\Model\ACFFields\SelectACFField;
use ACFCustomDatabaseTables\Model\ACFFields\TabACFField;
use ACFCustomDatabaseTables\Model\ACFFields\TaxonomyACFField;
use ACFCustomDatabaseTables\Model\ACFFields\TextACFField;
use ACFCustomDatabaseTables\Model\ACFFields\TextAreaACFField;
use ACFCustomDatabaseTables\Model\ACFFields\TimePickerACFField;
use ACFCustomDatabaseTables\Model\ACFFields\TrueFalseACFField;
use ACFCustomDatabaseTables\Model\ACFFields\UrlACFField;
use ACFCustomDatabaseTables\Model\ACFFields\UserACFField;
use ACFCustomDatabaseTables\Model\ACFFields\WysiwygACFField;
use ACFCustomDatabaseTables\Service\ACFFieldSupportManager;
use ACFCustomDatabaseTables\Service\ACFLocalReferenceFallback;
use ACFCustomDatabaseTables\Service\DiagnosticReporter;
use ACFCustomDatabaseTables\Service\TableNameValidator;
use ACFCustomDatabaseTables\UI\FieldGroupCustomTableMetaBox;
use ACFCustomDatabaseTables\Vendor\Pimple\Container;
use ACFCustomDatabaseTables\Vendor\Pimple\ServiceProviderInterface;

class ACFProvider implements ServiceProviderInterface {

	/**
	 * @param Container $c
	 */
	public function register( Container $c ) {
		foreach ( $this->definitions() as $key => $callback ) {
			$c[ $key ] = $callback;
		}

		foreach ( $this->field_definitions() as $key => $class ) {
			$c[ $key ] = $c->factory( function ( $c ) use ( $class ) {
				return new $class();
			} );
		}
	}

	/**
	 * @param Container $c
	 */
	public function boot( Container $c ) {
		/** @var ACFFieldSupportManager $support */
		$support = $c[ ACFFieldSupportManager::class ];

		foreach ( $this->field_definitions() as $key => $def ) {
			$support->register_field( $c[ $key ] );
		}
	}

	/**
	 * @param Container $c
	 */
	public function init( Container $c ) {
		$c[ ACFLocalReferenceFallback::class ]->init();
	}

	/**
	 * Return array of container definitions
	 *
	 * @return array
	 */
	private function definitions() {
		return [
			ACFFieldFactory::class => function ( Container $c ) {
				return new ACFFieldFactory( $c );
			},
			ACFFieldGroupFactory::class => function ( Container $c ) {
				return new ACFFieldGroupFactory();
			},
			ACFFieldSupportManager::class => function ( Container $c ) {
				return new ACFFieldSupportManager();
			},
			FieldGroupCustomTableMetaBox::class => function ( Container $c ) {
				return new FieldGroupCustomTableMetaBox(
					null,
					$c[ ACFFieldGroupFactory::class ],
					$c[ DiagnosticReporter::class ],
					$c[ TableNameValidator::class ]
				);
			},
			ACFLocalReferenceFallback::class => function ( Container $c ) {
				return new ACFLocalReferenceFallback();
			},

			// Back compat — remove these in version 1.2
			'factory.field' => function ( Container $c ) {
				_deprecated_function( "'factory.field' container binding ", 1.1, ACFFieldFactory::class );

				return $c[ ACFFieldFactory::class ];
			},
			'factory.field_group' => function ( Container $c ) {
				_deprecated_function( "'factory.field_group' container binding ", 1.1, ACFFieldGroupFactory::class );

				return $c[ ACFFieldGroupFactory::class ];
			},
			'acf_field_support_manager' => function ( Container $c ) {
				_deprecated_function( "'acf_field_support_manager' container binding ", 1.1, ACFFieldSupportManager::class );

				return $c[ ACFFieldSupportManager::class ];
			},
			'field_group_custom_table_meta_box' => function ( Container $c ) {
				_deprecated_function( "'field_group_custom_table_meta_box' container binding ", 1.1, FieldGroupCustomTableMetaBox::class );

				return $c[ FieldGroupCustomTableMetaBox::class ];
			},
			'acf_local_reference_fallback' => function ( Container $c ) {
				_deprecated_function( "'acf_local_reference_fallback' container binding ", 1.1, ACFLocalReferenceFallback::class );

				return $c[ ACFLocalReferenceFallback::class ];
			},
			'acf_field_object_coordinator' => function ( Container $c ) {
				_deprecated_function( "'acf_field_object_coordinator' container binding ", 1.1, 'none' );

				return null;
			},
		];
	}

	private function field_definitions() {
		return [

			// Generic field to handle anything that doesn't have a specific object
			'acf_field._generic' => GenericField::class,

			// Field-specific objects
			'acf_field.text' => TextACFField::class,
			'acf_field.textarea' => TextAreaACFField::class,
			'acf_field.number' => NumberACFField::class,
			'acf_field.range' => RangeACFField::class,
			'acf_field.email' => EmailACFField::class,
			'acf_field.url' => UrlACFField::class,
			'acf_field.password' => PasswordACFField::class,
			'acf_field.oembed' => OembedACFField::class,
			'acf_field.file' => FileACFField::class,
			'acf_field.radio' => RadioACFField::class,
			'acf_field.true_false' => TrueFalseACFField::class,
			'acf_field.color_picker' => ColorPickerACFField::class,
			'acf_field.date_picker' => DatePickerACFField::class,
			'acf_field.date_time_picker' => DateTimePickerACFField::class,
			'acf_field.time_picker' => TimePickerACFField::class,
			'acf_field.button_group' => ButtonGroupACFField::class,
			'acf_field.image' => ImageACFField::class,
			'acf_field.wysiwyg' => WysiwygACFField::class,
			'acf_field.checkbox' => CheckboxACFField::class,
			'acf_field.gallery' => GalleryACFField::class,
			'acf_field.google_map' => GoogleMapACFField::class,
			'acf_field.link' => LinkACFField::class,
			'acf_field.page_link' => PageLinkACFField::class,
			'acf_field.post_object' => PostObjectACFField::class,
			'acf_field.select' => SelectACFField::class,
			'acf_field.taxonomy' => TaxonomyACFField::class,
			'acf_field.user' => UserACFField::class,
			'acf_field.repeater' => RepeaterACFField::class,
			'acf_field.relationship' => RelationshipACFField::class,
			'acf_field.accordion' => AccordionACFField::class,
			'acf_field.clone' => CloneACFField::class,
			'acf_field.flexible_content' => FlexibleContentACFField::class,
			'acf_field.group' => GroupACFField::class,
			'acf_field.message' => MessageACFField::class,
			'acf_field.tab' => TabACFField::class,
		];
	}

}