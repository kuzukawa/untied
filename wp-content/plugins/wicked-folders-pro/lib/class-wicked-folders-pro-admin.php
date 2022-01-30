<?php

// Disable direct load
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

use Wicked_Folders\Wicked_Common;

final class Wicked_Folders_Pro_Admin {

	private static $instance;

	private function __construct() {
		$core_admin = Wicked_Folders_Admin::get_instance();

		add_filter( 'manage_plugins_columns', 						array( $core_admin, 'manage_posts_columns' ) );
		add_filter( 'manage_users_columns', 						array( $core_admin, 'manage_posts_columns' ) );
		add_filter( 'all_plugins', 									array( $this, 'all_plugins_filter' ) );
		add_filter( 'wicked_folders_after_ajax_scripts', 			array( $this, 'wicked_folders_after_ajax_scripts' ) );
		add_filter( 'gform_form_list_columns', 						array( $core_admin, 'manage_posts_columns' ) );
		add_filter( 'gform_entry_list_columns', 					array( $this, 'gform_entry_list_columns' ) );
		add_filter( 'gform_form_list_forms', 						array( $this, 'gform_form_list_forms' ), 10, 6 );
		add_filter( 'gform_get_entries_args_entry_list', 			array( $this, 'gform_get_entries_args_entry_list' ), 10, 2 );
		add_filter( 'gform_noconflict_scripts', 					array( $this, 'gform_noconflict_scripts' ) );
		add_filter( 'gform_noconflict_styles', 						array( $this, 'gform_noconflict_styles' ) );
		add_filter( 'wicked_folders_setting_tabs', 					array( $this, 'wicked_folders_setting_tabs' ) );
		add_filter( 'tag_row_actions', 								array( $this, 'tag_row_actions' ), 10, 2 );
		add_filter( 'get_edit_term_link', 							array( $this, 'get_edit_term_link' ), 10, 4 );
		add_filter( 'get_terms', 									array( $this, 'get_terms' ), 10, 4 );
		add_filter( 'map_meta_cap', 								array( $this, 'map_meta_cap' ), 10 ,4 );
		add_filter( 'redirect_post_location', 						array( $this, 'redirect_post_location' ), 10, 2 );
		add_filter( 'manage_users_custom_column', 					array( $this, 'manage_users_custom_column' ), 10, 3 );
		add_filter( 'tablepress_load_all_tables', 					array( $this, 'tablepress_load_all_tables' ) );

		add_action( 'pre_user_query', 								array( $this, 'pre_user_query' ) );
		add_action( 'manage_plugins_custom_column', 				array( $this, 'manage_plugins_custom_column' ), 10, 4 );
		add_action( 'gform_form_list_column_wicked_move', 			array( $this, 'gform_form_list_column_wicked_move' ) );
		add_action( 'gform_entries_field_value', 					array( $this, 'gform_entries_field_value' ), 10, 4 );
		add_action( 'deleted_plugin', 								array( $this, 'deleted_plugin' ), 10, 2 );
		add_action( 'add_meta_boxes', 								array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', 									array( $this, 'save_security_policy_permissions_meta_box' ) );
		add_action( 'admin_post_tablepress_editor_button_thickbox', array( $this, 'add_tablepress_thickbox_folder_filter' ) );
		add_action( 'tablepress_event_copied_table', 				array( $this, 'tablepress_event_copied_table' ), 10, 2 );
	}

	public static function get_instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new Wicked_Folders_Pro_Admin();
		}

		return self::$instance;
	}

	public function admin_init() {

		if ( is_plugin_active( 'wicked-folders/wicked-folders.php' ) ) {
			deactivate_plugins( 'wicked-folders/wicked-folders.php' );
		}

		Wicked_Folders_Pro_Admin::save_settings();
		$this->save_folder_collection_policy_assignments();

		$plugin_data = get_plugin_data( Wicked_Folders_Pro::$plugin_file );

		$edd_updater = new EDD_SL_Plugin_Updater( Wicked_Common::wicked_plugins_url(), Wicked_Folders_Pro::$plugin_file, array(
				'version'   => $plugin_data['Version'],
				'license'   => get_site_option( 'wicked_folders_pro_license_key', false ),
				'item_name' => $plugin_data['Name'],
				'author'    => $plugin_data['Author'],
			)
		);

		// Set option if it hasn't been set so that it can be filtered
		if ( null === get_option( 'wicked_folders_persist_media_modal_folder_state', null ) ) {
			add_option( 'wicked_folders_persist_media_modal_folder_state', true );
		}

		if ( isset( $_GET['page'] ) && 'wicked_folders_settings' == $_GET['page'] && isset( $_GET['action'] ) && 'unassign_deleted_plugins' == $_GET['action'] ) {
			$this->unassign_deleted_plugins();
		}
	}

	public static function admin_enqueue_scripts() {
		$screen = get_current_screen();

		if ( 'media' == $screen->base && 'add' == $screen->action ) {
			wp_enqueue_script( 'wicked-folders-media-new', plugin_dir_url( dirname( __FILE__ ) ) . 'js/media-new.js', array( 'jquery' ), Wicked_Folders::plugin_version()  );
		}

		if ( 'forms_page_gf_entries' == $screen->id ) {
			wp_enqueue_script( 'wicked-folders-gravity-forms', plugin_dir_url( dirname( __FILE__ ) ) . 'js/gravity-forms.js', array( 'jquery' ), Wicked_Folders::plugin_version()  );
		}

		if ( 'tablepress_list' == $screen->id ) {
			$tablepress_data = get_option( 'tablepress_tables', '' );
			$tablepress_data = json_decode( $tablepress_data );

			if ( isset( $tablepress_data->table_post ) ) {
				$data = array(
					'tables' => ( array ) $tablepress_data->table_post,
				);

				wp_enqueue_script( 'wicked-folders-tablepress', plugin_dir_url( dirname( __FILE__ ) ) . 'js/tablepress.js', array( 'jquery' ), Wicked_Folders::plugin_version()  );
				wp_localize_script( 'wicked-folders-tablepress', 'wickedFoldersTablePressData', $data );
			}
		}

	}

    public static function plugin_action_links( $links ) {

        $settings_link = '<a href="' . esc_url( menu_page_url( 'wicked_folders_settings', 0 ) ) . '">' . __( 'Settings', 'wicked-folders' ) . '</a>';

        array_unshift( $links, $settings_link );

        return $links;

    }

    public static function wp_enqueue_media() {
		static $done = false;

		// Only run this fuction once per request
		if ( $done ) return false;

		// No need to do anything if folders aren't enabled for media
		if ( ! Wicked_Folders::enabled_for( 'attachment' ) ) return false;

		// Other plugins are constantly calling wp_enqueue_media too soon;
		// therefore, register taxonomies here as well in case the core 'init'
		// function where the taxonomies are normally registered hasn't been run
		// yet
		Wicked_Folders::get_instance()->register_taxonomies();

		$user_id 			= get_current_user_id();
		$translated 		= Wicked_Folders::is_folder_taxonomy_translated( 'wf_attachment_folders' );
		$lang 				= $translated ? Wicked_Folders::get_language() : false;
		$state 				= new Wicked_Folders_Screen_State( 'upload', $user_id, $lang );
		$modal_state 		= new Wicked_Folders_Screen_State( 'media-modal', $user_id, $lang );
		$show_item_count 	= ( bool ) get_option( 'wicked_folders_show_item_counts', true );
		$in_footer 			= false;

		$folders_array 		= array();
		$folders 			= Wicked_Folders::get_folders( 'attachment' );

		$tree_view 			= new Wicked_Folders_Tree_View( 'attachment' );
		$tree_view->add_folders( $folders );

		$folders = $tree_view->build_flat_tree_array( 'root' );

		// Load scripts in footer when Thrive Quiz Builder is active; for some
		// reason media library isn't loading when scripts are loaded in head
		if ( isset( $_GET['post_type'] ) && 'tqb_quiz' == $_GET['post_type'] ) {
			$in_footer = true;
		}

		$in_footer = apply_filters( 'wicked_folders_enqueue_scripts_in_footer', $in_footer );

		// TODO: consider passing folders directly to wp_localize_script below.
		// The issue here is that when new properties are added to folders, we
		// have to remember to add the properties here as well
		foreach ( $folders as $key => $folder ) {
			$depth = $tree_view->get_ancestor_count( $folder->id );// - 1;
			$folders_array[] = array(
				'id' 			=> $folder->id,
				'name' 			=> $folder->name,
				'depth' 		=> $depth,
				'parent' 		=> $folder->parent,
				'type' 			=> get_class( $folder ),
				'itemCount' 	=> $folder->item_count,
				'showItemCount' => $folder->show_item_count,
				'order' 		=> $folder->order,
				'ownerId'      	=> $folder->owner_id,
				'ownerName' 	=> $folder->owner_name,
				'editable' 		=> $folder->editable,
				'deletable' 	=> $folder->deletable,
				'assignable' 	=> $folder->assignable,
				'order' 		=> $folder->order,
			);
		}

		// Parameters for folder pane controller
		$folder_pane_params = array(
			'postType' 				=> 'attachment',
			'taxonomy' 				=> 'wf_attachment_folders',
			'folder' 				=> false,
			'folders' 				=> array(),
			'screen' 				=> 'upload',
			'nounce' 				=> wp_create_nonce( 'wicked_folders_save_state' ),
			'expanded' 				=> array_values( $state->expanded_folders ),
			'treePaneWidth' 		=> $state->tree_pane_width,
			'isFolderPaneVisible' 	=> $state->is_folder_pane_visible,
			'sortMode' 				=> $state->sort_mode,
			'showItemCount'			=> $show_item_count,
			'lang' 					=> $lang,
			'enableCreate' 			=> apply_filters( 'wicked_folders_enable_create', true, 'wf_attachment_folders', get_current_user_id() ),
		);

		$modal_folder_pane_params = array(
			'screen' 				=> 'media-modal',
			'expanded' 				=> array_values( $modal_state->expanded_folders ),
			'treePaneWidth' 		=> $modal_state->tree_pane_width,
			'isFolderPaneVisible' 	=> $modal_state->is_folder_pane_visible,
			'sortMode' 				=> $modal_state->sort_mode,
			'showItemCount'			=> $show_item_count,
			'lang' 					=> $lang,
		);

		$modal_folder_pane_params = array_merge( $folder_pane_params, $modal_folder_pane_params );

		wp_enqueue_script( 'wicked-folders-admin' );
		wp_enqueue_script( 'wicked-folders-app' );
		wp_enqueue_style( 'wicked-folders-admin' );

		wp_enqueue_script( 'wicked-folders-select2' );
		wp_enqueue_style( 'wicked-folders-select2' );

		wp_enqueue_script( 'wicked-folders-media', plugin_dir_url( dirname( __FILE__ ) ) . 'js/media.js', array( 'media-editor', 'media-views' ), Wicked_Folders_Pro::plugin_version(), $in_footer );
		wp_localize_script( 'wicked-folders-media', 'WickedFoldersProData', array(
			'folders' 					=> $folders_array,
			'activeFolderId' 			=> $state->folder,
			'modalActiveFolderId' 		=> $modal_state->folder,
			'allFoldersText' 			=> __( 'All folders', 'wicked-folders' ),
			'syncUploadFolderDropdown' 	=> ( bool ) get_option( 'wicked_folders_sync_upload_folder_dropdown', false ),
			'persistFolderState' 		=> ( bool ) get_option( 'wicked_folders_persist_media_modal_folder_state', true ),
			'includeChildren' 			=> Wicked_Folders::include_children( 'attachment' ),
			'folderPaneParams' 			=> $folder_pane_params,
			'modalFolderPaneParams' 	=> $modal_folder_pane_params,
			'enableHorizontalScrolling' => Wicked_Folders::is_horizontal_scrolling_enabled(),
			'showItemCount' 			=> $show_item_count,
		) );

		// Add these actions here so that they're only triggered when
		// wp_enqueue_media is called
		add_action( 'admin_footer', 							array( 'Wicked_Folders_Pro', 'print_media_templates' ) );
		add_action( 'wp_footer', 								array( 'Wicked_Folders_Pro', 'print_media_templates' ) );
		add_action( 'print_media_templates', 					array( 'Wicked_Folders_Pro', 'print_media_templates' ) );
		add_action( 'customize_controls_print_footer_scripts', 	array( 'Wicked_Folders_Pro', 'print_media_templates' ) );

		$done = true;
	}

    public static function admin_menu() {

		$enable_folder_pages = get_option( 'wicked_folders_enable_folder_pages', false );

		if ( $enable_folder_pages && Wicked_Folders::enabled_for( 'attachment' ) ) {

			$page_title 	= __( 'Folders', 'wicked-folders' );
			$menu_title 	= __( 'Folders', 'wicked-folders' );
			$capability 	= 'edit_posts';
			$menu_slug 		= 'wf_attachment_folders';
			$callback 		= array( 'Wicked_Folders_Admin', 'folders_page' );

			add_media_page( $page_title, $menu_title, $capability, $menu_slug, $callback );

		}

    }

	/**
	 * network_admin_menu action. Adds a network settings page for the plugin.
	 */
	public static function network_admin_menu() {

		// Add menu item for plugin network settings page
		$parent_slug 	= 'settings.php';
		$page_title 	= __( 'Wicked Folders Settings', 'wicked-folders' );
		$menu_title 	= __( 'Wicked Folders', 'wicked-folders' );
		$capability 	= 'manage_options';
		$menu_slug 		= 'wicked_folders_settings';
		$callback 		= array( 'Wicked_Folders_Pro_Admin', 'network_settings_page' );

		add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback );

	}

    public static function manage_media_columns( $columns ) {

		if ( Wicked_Folders_Admin::is_folders_page() ) {
	        $columns = array(
				'wicked_move' 	=> '<div class="wicked-move-multiple" title="' . __( 'Move selected items', 'wicked-folders' ) . '"><span class="wicked-move-file dashicons dashicons-move"></span><div class="wicked-items"></div></div>',
				'cb' 			=> '<input type="checkbox" />',
	            'title' 		=> 'File',
	            'author' 		=> 'Author',
	            'parent' 		=> 'Uploaded to',
	            'date' 			=> 'Date',
				'wicked_sort' 	=> __( 'Sort', 'wicked-folders' ),
	        );
		} else {
			$a = array( 'wicked_move' => '<div class="wicked-move-multiple" title="' . __( 'Move selected items', 'wicked-folders' ) . '"><span class="wicked-move-file dashicons dashicons-move"></span><div class="wicked-items"></div><span class="screen-reader-text">' . __( 'Move to Folder', 'wicked-folders' ) . '</span></div>' );
			$columns = $a + $columns;
		}

		return $columns;

    }

    public static function manage_media_custom_column( $column_name, $post_id ) {
		if ( 'wicked_move' == $column_name ) {
			echo '<div class="wicked-move-multiple" data-object-id="' . esc_attr( $post_id ) . '"><span class="wicked-move-file dashicons dashicons-move"></span><div class="wicked-items"><div class="wicked-item" data-object-id="' . esc_attr( $post_id ) . '">' . get_the_title() . '</div></div>';
		}
		if ( 'wicked_sort' == $column_name ) {
			echo '<a class="wicked-sort" href="#"><span class="dashicons dashicons-menu"></span></a>';
		}
    }

	public static function manage_upload_sortable_columns( $columns ) {
		$columns['wicked_sort'] = 'wicked_folder_order';
		return $columns;
	}

	public static function restrict_manage_posts( $post_type ) {

		// Post type isn't set on the media list screen
		if ( ! $post_type ) {
			$screen = get_current_screen();
			if ( 'upload' == $screen->base ) {
				$post_type = 'attachment';
			}
		}

		if ( 'attachment' == $post_type && Wicked_Folders::enabled_for( $post_type ) ) {

			// It appears that as of 4.8, WordPress automatically adds a taxonomy
			// filter to attachments so this is no longer necessary and will
			// cause a duplicate dropdown
			if ( version_compare( get_bloginfo( 'version' ), '4.8', '<' ) ) {
				$folder = 0;

				if ( isset( $_GET['wicked_attachment_folder_filter'] ) ) {
					$folder = ( int ) $_GET['wicked_attachment_folder_filter'];
				}

				wp_dropdown_categories( array(
					'orderby'           => 'name',
					'order'             => 'ASC',
					'show_option_none'  => __( 'All folders', 'wicked-folders' ),
					'taxonomy'          => 'wf_attachment_folders',
					'depth'             => 0,
					'hierarchical'      => true,
					'hide_empty'        => false,
					'option_none_value' => 0,
					'name' 				=> 'wicked_attachment_folder_filter',
					'id' 				=> 'wicked-attachment-folder-filter',
					'selected' 			=> $folder,
				) );
			}

		}

	}

	public static function wp_prepare_attachment_for_js( $response, $attachment, $meta ) {

		$folders = wp_get_object_terms( $attachment->ID, 'wf_attachment_folders', array(
			'fields' => 'ids',
		) );

		if ( is_wp_error( $folders ) ) {
			$folders = array();
		}

		$folders = array_map( 'strval', $folders );

		$response['wickedFolders'] = $folders;

		return $response;

	}

	public static function ajax_query_attachments_args( $query ) {
		// Change attachment browser query to not include children folders
		if ( isset( $query['wf_attachment_folders'] ) ) {
			if ( ! empty( $query['wf_attachment_folders'] ) ) {
				// Check if folder is in type.id format
				if ( false !== $index = strpos( $query['wf_attachment_folders'], '.' ) ) {
					$query['wf_attachment_folders'] = substr( $query['wf_attachment_folders'], $index + 1 );
				}
				$tax_query = array(
					array(
						'taxonomy' 			=> 'wf_attachment_folders',
						'field' 			=> 'term_id',
						'terms' 			=> $query['wf_attachment_folders'],
						'include_children' 	=> Wicked_Folders::include_children( 'attachment', $query['wf_attachment_folders'] ),
					),
				);
				$query['tax_query'] = $tax_query;
			}

			unset( $query['wf_attachment_folders'] );
		}

		return $query;

	}

	public static function save_settings() {

		// WARNING: this function is used in both multisite and non-multisite
		// instances. Be careful when adding new pro options.

		$action = isset( $_REQUEST['action'] ) ? sanitize_key( $_REQUEST['action'] ) : false;

		// Save settings
		if ( 'wicked_folders_save_settings' == $action && wp_verify_nonce( $_REQUEST['nonce'], 'wicked_folders_save_settings' ) ) {
			// Handle deactivate requests
			if ( isset( $_POST['deactivate_license'] ) ) {
				delete_site_option( 'wicked_folders_pro_license_key' );
				delete_site_option( 'wicked_folders_pro_license_data' );

				Wicked_Folders_Admin::add_admin_notice( __( 'Your license has been deactivated for this site. You may reactivate it at any time.', 'wicked-folders' ) );
			}

			// Handle activate requests
			if ( isset( $_POST['activate_license'] ) ) {
				$existing_license_key 	= get_site_option( 'wicked_folders_pro_license_key', false );
				$new_license_key 		= trim( sanitize_text_field( $_POST['wicked_folders_pro_license_key'] ) );
				$license_data 			= get_site_option( 'wicked_folders_pro_license_data', false );
				$expired 				= Wicked_Folders_Pro::is_license_expired();

				// Save the license key
				update_site_option( 'wicked_folders_pro_license_key', $new_license_key );

				// Process license key if we don't have any info about the license,
				// if the key has changed or, if the license has expired
				if ( ! $license_data || $existing_license_key != $new_license_key ) {
					// Make sure a non-empty license key was entered
					if ( $new_license_key ) {
						try {
							// Attemp to activate the plugin
							Wicked_Folders_Pro::activate_license();
							// No errors, refresh license data
							$license_data = Wicked_Folders_Pro::fetch_license_data();
							if ( $license_data ) {
								update_site_option( 'wicked_folders_pro_license_data', $license_data );

								Wicked_Folders_Admin::add_admin_notice( __( 'Your license has been activated.', 'wicked-folders' ) );
							} else {
								delete_site_option( 'wicked_folders_pro_license_data' );
							}
						} catch ( Exception $e ) {
							// License activation failed, display an error
							Wicked_Folders_Admin::add_admin_notice( $e->getMessage(), 'notice notice-error' );
							// Clear the license data cache
							delete_site_option( 'wicked_folders_pro_license_data' );
						}
					} else {
						// License key has been removed, delete license meta
						delete_site_option( 'wicked_folders_pro_license_key' );
						delete_site_option( 'wicked_folders_pro_license_data' );
					}
				} else if ( $expired ) {
					try {
						$license_data = Wicked_Folders_Pro::fetch_license_data();

						if ( $license_data ) {
							update_site_option( 'wicked_folders_pro_license_data', $license_data );
						}
					} catch ( Exception $e ) {
						// Failed to fetch license data
						Wicked_Folders_Admin::add_admin_notice( $e->getMessage(), 'notice notice-error' );
					}
				}
			}
		}

	}

	public function save_folder_collection_policy_assignments() {
		$action 			= isset( $_POST['action'] ) ? sanitize_key( $_POST['action'] ) : false;
		$taxonomy_policies 	= array();

		// Save settings
		if ( 'wicked_folders_save_folder_collection_policy_assignments' == $action && wp_verify_nonce( $_POST['nonce'], 'wicked_folders_save_folder_collection_policy_assignments' ) ) {
			if ( isset( $_POST['wf_taxonomy'] ) && is_array( $_POST['wf_taxonomy'] ) ) {
				$taxonomies = array_map( 'sanitize_key', $_POST['wf_taxonomy'] );

				foreach ( $taxonomies as $index => $taxonomy ) {
					if ( ! empty( $_POST['wf_policy'][ $index ] ) ) {
						$taxonomy_policies[ $taxonomy ] = sanitize_text_field( $_POST['wf_policy'][ $index ] );
					}
				}

				update_option( 'wf_taxonomy_policies', $taxonomy_policies );

				Wicked_Folders_Admin::add_admin_notice( __( 'Policy assignments saved.', 'wicked-folders' ) );
			}
		}
	}

	public static function save_attachment( $post_id ) {

		// Attachments saved via media modal
		if ( isset( $_REQUEST['action'] ) && 'save-attachment' == $_REQUEST['action'] && isset( $_REQUEST['changes']['wickedFolders'] ) ) {

			$terms = '';

			if ( is_array( $_REQUEST['changes']['wickedFolders'] ) ) {
				$terms = array_map( 'intval', $_REQUEST['changes']['wickedFolders'] );
			}

			wp_set_object_terms( $post_id, $terms, 'wf_attachment_folders' );

		}

		// Attachments uploaded via media frame and upload new media page
		//if ( isset( $_REQUEST['action'] ) && 'upload-attachment' == $_REQUEST['action'] && ! empty( $_REQUEST['wicked_folder_id'] ) ) {
		if ( ! empty( $_REQUEST['wicked_folder_id'] ) ) {
			if ( is_array( $_REQUEST['wicked_folder_id'] ) ) {
				$terms = array_map( 'absint', $_REQUEST['wicked_folder_id'] );
			} else {
				$terms = array( absint( $_REQUEST['wicked_folder_id'] ) );
			}
			$terms = array_map( 'intval', $terms );
			wp_set_object_terms( $post_id, $terms, 'wf_attachment_folders' );
		}
	}

	/**
	 * WordPress post-plupload-upload-ui action.
	 */
	public static function post_plupload_upload_ui() {
		$folder_ids[] 	= array();
		$folder_id 		= isset( $_GET['wf_folder_id'] ) ? ( int ) $_GET['wf_folder_id'] : 0;
		$folders 		= Wicked_Folders::get_folders( 'attachment' );

		// Restrict folder dropdown to folders that are assignable
		foreach ( $folders as $folder ) {
			if ( $folder->assignable ) {
				$folder_ids[] = $folder->id;
			}
		}

		if ( Wicked_Folders::enabled_for( 'attachment' ) ) {
			echo '<div id="wicked-upload-folder-ui">';
			wp_dropdown_categories( array(
				'orderby'           => 'name',
				'order'             => 'ASC',
				'show_option_none'  => __( 'Assign to folder...', 'wicked-folders' ),
				'taxonomy'          => 'wf_attachment_folders',
				'depth'             => 0,
				'hierarchical'      => true,
				'hide_empty'        => false,
				'option_none_value' => 0,
				'name' 				=> 'wicked_upload_folder',
				'id' 				=> 'wicked-upload-folder',
				'selected' 			=> $folder_id,
				'include' 			=> $folder_ids,
			) );
			echo '</div>';
		}
	}

	public static function network_settings_page() {

		$license_key 	= get_site_option( 'wicked_folders_pro_license_key', false );
		$license_status = Wicked_Folders_Pro::get_license_status_text();
		$license_data 	= get_site_option( 'wicked_folders_pro_license_data' );
		$valid_license 	= isset( $license_data->license ) && 'valid' == $license_data->license ? true : false;

		include( dirname( dirname( __FILE__ ) ) . '/admin-templates/network-settings-page.php' );

	}

	public function manage_users_custom_column( $value, $column_name, $user_id ) {
		if ( 'wicked_move' == $column_name ) {
			$title = get_the_author_meta( 'user_login', $user_id );

			$value = '<div class="wicked-move-multiple" data-object-id="' . esc_attr( $user_id ) . '"><span class="wicked-move-file dashicons dashicons-move"></span><div class="wicked-items"><div class="wicked-item" data-object-id="' . esc_attr( $user_id ) . '">' . esc_html( $title ) . '</div></div>';
		}

		return $value;
	}

	public function manage_plugins_custom_column( $column_name, $plugin, $plugin_details ) {
		$ids = get_option( 'wicked_folders_plugin_ids', array() );

		$id = isset( $ids[ $plugin ] ) ? $ids[ $plugin ] : false;

		if ( ! $id ) return;

		if ( 'wicked_move' == $column_name ) {
			$title = $plugin_details['Name'];

			echo '<div class="wicked-move-multiple" data-object-id="' . esc_attr( $id ) . '"><span class="wicked-move-file dashicons dashicons-move"></span><div class="wicked-items"><div class="wicked-item" data-object-id="' . esc_attr( $id ) . '">' . esc_html( $title ) . '</div></div>';
		}
	}

	/**
	 * Hook into the user query for the purposes of filtering users by a folder.
	 */
	function pre_user_query( $query ) {
		global $current_screen, $wpdb;

		if ( ! is_admin() ) return false;

		// Exit if folders aren't enabled for users
		if ( ! Wicked_Folders::enabled_for( Wicked_Folders::get_user_post_type_name() ) ) return false;

		// Make sure we're on the 'Users' screen
		if ( ! ( isset( $current_screen->base ) && 'users' == $current_screen->base ) ) return false;

		$post_type 	= Wicked_Folders::get_user_post_type_name();
		$taxonomy 	= Wicked_Folders::get_tax_name( $post_type );
		$folder_id 	= isset( $_GET[ "wicked_{$post_type}_folder_filter" ] ) ? sanitize_text_field( $_GET[ "wicked_{$post_type}_folder_filter" ] ) : false;
		$state 		= new Wicked_Folders_Screen_State( $current_screen->id, get_current_user_id() );
		$folders 	= Wicked_Folders::get_folders( $post_type, $taxonomy );

		if ( false === $folder_id && ! empty( $state->folder ) ) {
			$folder_id = $state->folder;
		}

		// Nothing to do if we still don't have a folder
		if ( false == $folder_id ) return;

		// Numeric folders IDs are terms
		if ( is_numeric( $folder_id ) ) {
			$query->query_from 	.= " INNER JOIN {$wpdb->term_relationships} AS wf_term_relationships ON wf_term_relationships.object_id = {$wpdb->users}.ID INNER JOIN {$wpdb->term_taxonomy} AS wf_term_taxonomy ON wf_term_relationships.term_taxonomy_id = wf_term_taxonomy.term_taxonomy_id ";
			$query->query_where .= " AND (wf_term_taxonomy.taxonomy = '{$taxonomy}' AND wf_term_taxonomy.term_id = {$folder_id}) ";
		}

		if ( 'unassigned_dynamic_folder' == $folder_id ) {
			// Get the IDs of users who've been assigned to a folder
			$ids = $wpdb->get_col(
				$wpdb->prepare(
					"
						SELECT DISTINCT {$wpdb->users}.ID FROM {$wpdb->users}
						INNER JOIN {$wpdb->term_relationships} AS wf_term_relationships ON wf_term_relationships.object_id = {$wpdb->users}.ID
						INNER JOIN {$wpdb->term_taxonomy} AS wf_term_taxonomy ON wf_term_relationships.term_taxonomy_id = wf_term_taxonomy.term_taxonomy_id
						WHERE wf_term_taxonomy.taxonomy = %s
					", $taxonomy
				)
			);

			if ( $ids ) {
				// Filter out users who've been assigned
				$query->query_where .= " AND ({$wpdb->users}.ID NOT IN (" . join( ', ', $ids ) . ")) ";
			}
		}

		// Role dynamic folders
		if ( false !== strpos( $folder_id, 'dynamic_user_role_' ) ) {
			$role = serialize( substr( $folder_id, 18 ) );

			$query->query_from 	.= " INNER JOIN {$wpdb->usermeta} AS wf_usermeta ON {$wpdb->users}.ID = wf_usermeta.user_id ";
			$query->query_where .= " AND (wf_usermeta.meta_key = 'wrdp_capabilities' AND wf_usermeta.meta_value LIKE '%$role%') ";
		}
		// wrdp_capabilities
	}

	/**
	 * WordPress 'all_plugins' filter.
	 */
	public function all_plugins_filter( $plugins ) {
		global $current_screen, $wpdb;

		// This should only take place in the admin
		if ( ! is_admin() ) return $plugins;

		// Make sure screen variable is set
		if ( empty( $current_screen ) ) return $plugins;

		// Make sure we're on the Plugins screen
		if ( 'plugins' != $current_screen->id ) return $plugins;

		// Ensure all plugins have a numeric ID assigned
		$ids = $this->generate_numeric_ids_for_plugins( $plugins );

		$filtered_plugins 	= array();
		$post_type 			= Wicked_Folders::get_plugin_post_type_name();
		$taxonomy 			= Wicked_Folders::get_tax_name( $post_type );
		$folder_id 			= isset( $_GET[ "wicked_{$post_type}_folder_filter" ] ) ? sanitize_text_field( $_GET[ "wicked_{$post_type}_folder_filter" ] ) : false;
		$state 				= new Wicked_Folders_Screen_State( $current_screen->id, get_current_user_id() );

		if ( false === $folder_id && ! empty( $state->folder ) ) {
			$folder_id = $state->folder;
		}

		// Nothing to do if we still don't have a folder
		if ( false == $folder_id ) return $plugins;

		// Numeric folders IDs are terms
		if ( is_numeric( $folder_id ) ) {
			// Get plugins assigned to the folder
			$object_ids = $wpdb->get_col(
				$wpdb->prepare(
					"
						SELECT object_id FROM {$wpdb->term_relationships} AS wf_term_relationships
						INNER JOIN {$wpdb->term_taxonomy} AS wf_term_taxonomy ON wf_term_relationships.term_taxonomy_id = wf_term_taxonomy.term_taxonomy_id
						WHERE wf_term_taxonomy.taxonomy = %s AND wf_term_taxonomy.term_id = %d
					", $taxonomy, $folder_id
				)
			);
// 			print_r($object_ids);
// 			echo '---';
// 			print_r($ids);
// print_r($plugins);
// exit();
			// Filter out plugins that aren't assigned to the folder
			foreach ( $plugins as $key => $plugin ) {
				if ( isset( $ids[ $key ] ) ) {
					$id = $ids[ $key ];

					if ( in_array( $id, $object_ids ) ) {
						$filtered_plugins[ $key ] = $plugin;
					}
				}
			}
		}
// print_r($filtered_plugins);
// exit();
		if ( 'unassigned_dynamic_folder' == $folder_id ) {
			// Get IDs of plugins assigned to at least one folder
			$object_ids = $wpdb->get_col(
				$wpdb->prepare(
					"
						SELECT object_id FROM {$wpdb->term_relationships} AS wf_term_relationships
						INNER JOIN {$wpdb->term_taxonomy} AS wf_term_taxonomy ON wf_term_relationships.term_taxonomy_id = wf_term_taxonomy.term_taxonomy_id
						WHERE wf_term_taxonomy.taxonomy = %s
					", $taxonomy
				)
			);

			// Filter out plugins that are assigned to a folder
			foreach ( $plugins as $key => $plugin ) {
				if ( isset( $ids[ $key ] ) ) {
					$id = $ids[ $key ];

					if ( ! in_array( $id, $object_ids ) ) {
						$filtered_plugins[ $key ] = $plugin;
					}
				}
			}
		}
		// print_r($filtered_plugins);
		// exit();
		return $filtered_plugins;
	}

	public function gform_entry_list_columns( $columns ) {
		$columns += array( 'wicked_move' => '<div class="wicked-move-multiple" title="' . __( 'Move selected items', 'wicked-folders' ) . '"><span class="wicked-move-file dashicons dashicons-move"></span><div class="wicked-items"></div><span class="screen-reader-text">' . __( 'Move to Folder', 'wicked-folders' ) . '</span></div>' );

		return $columns;
	}

	public function gform_form_list_forms( $forms, $search_query, $active, $sort_column, $sort_direction, $trash ) {
		global $current_screen, $wpdb;

		// This should only take place in the admin
		if ( ! is_admin() ) return $forms;

		// Make sure screen variable is set
		if ( empty( $current_screen ) ) return $forms;

		// Make sure we're on the Forms screen
		if ( 'toplevel_page_gf_edit_forms' != $current_screen->id ) return $forms;

		$filtered_items 	= array();
		$post_type 			= Wicked_Folders::get_gravity_forms_form_post_type_name();
		$taxonomy 			= Wicked_Folders::get_tax_name( $post_type );
		$folder_id 			= isset( $_GET[ "wicked_{$post_type}_folder_filter" ] ) ? sanitize_text_field( $_GET[ "wicked_{$post_type}_folder_filter" ] ) : false;
		$state 				= new Wicked_Folders_Screen_State( $current_screen->id, get_current_user_id() );

		if ( false === $folder_id && ! empty( $state->folder ) ) {
			$folder_id = $state->folder;
		}

		// Nothing to do if we still don't have a folder
		if ( false == $folder_id ) return $forms;

		// Numeric folders IDs are terms
		if ( is_numeric( $folder_id ) ) {
			// Get items assigned to the folder
			$object_ids = $wpdb->get_col(
				$wpdb->prepare(
					"
						SELECT object_id FROM {$wpdb->term_relationships} AS wf_term_relationships
						INNER JOIN {$wpdb->term_taxonomy} AS wf_term_taxonomy ON wf_term_relationships.term_taxonomy_id = wf_term_taxonomy.term_taxonomy_id
						WHERE wf_term_taxonomy.taxonomy = %s AND wf_term_taxonomy.term_id = %d
					", $taxonomy, $folder_id
				)
			);

			// Filter out forms that aren't assigned to the folder
			foreach ( $forms as $form ) {
				if ( in_array( $form->id, $object_ids ) ) {
					$filtered_items[] = $form;
				}
			}
		}

		if ( 'unassigned_dynamic_folder' == $folder_id ) {
			// Get IDs of forms assigned to at least one folder
			$object_ids = $wpdb->get_col(
				$wpdb->prepare(
					"
						SELECT object_id FROM {$wpdb->term_relationships} AS wf_term_relationships
						INNER JOIN {$wpdb->term_taxonomy} AS wf_term_taxonomy ON wf_term_relationships.term_taxonomy_id = wf_term_taxonomy.term_taxonomy_id
						WHERE wf_term_taxonomy.taxonomy = %s
					", $taxonomy
				)
			);

			// Filter out forms that are assigned to a folder
			foreach ( $forms as $form ) {
				if ( ! in_array( $form->id, $object_ids ) ) {
					$filtered_items[] = $form;
				}
			}
		}

		return $filtered_items;
	}

	/**
	 * Assigns a unique integer to each plugin and saves the results as an
	 * array in the options table keyed by the plugin's path for the purpose of
	 * allowing plugins to be assigned to folders (which requires the object to
	 * have an integer ID).
	 *
	 * @param array $plugins
	 *  An array of plugins as returned by WordPress's get_plugins() function.
	 *
	 * @return array
	 *  Array of numeric plugin IDs keyed by the plugin's path.
	 */
	private function generate_numeric_ids_for_plugins( $plugins ) {
		$ids = get_option( 'wicked_folders_plugin_ids', array() );

		$last_id = empty( $ids ) ? 0 : max( array_values( $ids ) );

		// Assign an ID to any plugins that don't already have an ID
		foreach ( $plugins as $key => $plugin ) {
			if ( ! isset( $ids[ $key ] ) ) {
				$last_id += 1;

				$ids[ $key ] = $last_id;
			}
		}

		// Update the option
		update_option( 'wicked_folders_plugin_ids', $ids );

		return $ids;
	}

	/**
	 * 'wicked_folders_after_ajax_scripts' filter.
	 *
	 * Adds WordPress's updates.js script so that AJAX search functionality on
	 * plugins page still works after navigating between folders.
	 */
	public function wicked_folders_after_ajax_scripts( $scripts ) {
		global $current_screen, $wp_scripts;

		if ( ! isset( $current_screen ) ) return $scripts;

		// Check if we're on the Plugins screen
		if ( 'plugins' == $current_screen->id ) {
			$scripts[] = admin_url( 'js/updates.min.js' );
		}

		return $scripts;
	}

	public function gform_form_list_column_wicked_move( $item ) {
		$id 	= $item->id;
		$title 	= $item->title;

		echo '<div class="wicked-move-multiple" data-object-id="' . esc_attr( $id ) . '"><span class="wicked-move-file dashicons dashicons-move"></span><div class="wicked-items"><div class="wicked-item" data-object-id="' . esc_attr( $id ) . '">' . esc_html( $title ) . '</div></div>';
	}

	public function gform_entries_field_value( $value, $form_id, $field_id, $entry ) {
		if ( 'wicked_move' != $field_id ) return $value;

		$id 	= $entry['id'];
		$title 	= 'Entry ID #' . $id;

		$value = '<div class="wicked-move-multiple" data-object-id="' . esc_attr( $id ) . '"><span class="wicked-move-file dashicons dashicons-move"></span><div class="wicked-items"><div class="wicked-item" data-object-id="' . esc_attr( $id ) . '">' . esc_html( $title ) . '</div></div>';

		return $value;
	}

	public function gform_get_entries_args_entry_list( $args ) {
		global $current_screen, $wpdb;

		// This should only take place in the admin
		if ( ! is_admin() ) return $args;

		// Make sure screen variable is set
		if ( empty( $current_screen ) ) return $args;

		// Make sure we're on the Forms screen
		if ( 'forms_page_gf_entries' != $current_screen->id ) return $args;

		$post_type 			= Wicked_Folders::get_gravity_forms_entry_post_type_name();
		$taxonomy 			= Wicked_Folders::get_tax_name( $post_type );
		$folder_id 			= isset( $_GET[ "wicked_{$post_type}_folder_filter" ] ) ? sanitize_text_field( $_GET[ "wicked_{$post_type}_folder_filter" ] ) : false;
		$state 				= new Wicked_Folders_Screen_State( $current_screen->id, get_current_user_id() );

		if ( false === $folder_id && ! empty( $state->folder ) ) {
			$folder_id = $state->folder;
		}

		// Nothing to do if we still don't have a folder
		if ( false == $folder_id ) return $args;

		if ( ! isset( $args['search_criteria']['field_filters'] ) ) {
			$args['search_criteria']['field_filters'] = array();
		}

		// Numeric folders IDs are terms
		if ( is_numeric( $folder_id ) ) {
			$args['search_criteria']['field_filters'][] = array(
				'key' 		=> 'wf_folders',
				'operator' 	=> 'contains',
				'value' 	=> 'f-' . $folder_id
			);
		}

		if ( 'unassigned_dynamic_folder' == $folder_id ) {
			$args['search_criteria']['field_filters'][] = array(
				'key' 		=> 'wf_has_folders',
				'operator' 	=> '!=',
				'value' 	=> 'true'
			);
		}

		return $args;
	}

	/**
	 * Gravity Forms filter.  Registers Wicked Folders scripts so that folders
	 * will function in no-conflict mode.
	 */
	public function gform_noconflict_scripts( $scripts ) {
		$scripts[] = 'wicked-folders-admin';
		$scripts[] = 'wicked-folders-app';
		$scripts[] = 'wicked-folders-gravity-forms';
		$scripts[] = 'wicked-folders-select2';

		return $scripts;
	}

	/**
	 * Gravity Forms filter.  Registers Wicked Folders styles so that folders
	 * will display correctly in no-conflict mode.
	 */
	public function gform_noconflict_styles( $styles ) {
		$styles[] = 'wicked-folders-admin';
		$styles[] = 'wicked-folders-select2';

		return $styles;
	}

	/**
	 * WordPress 'deleted_plugin' action.
	 */
	public function deleted_plugin( $plugin_file, $deleted ) {
		// No need to do anything if there was an error deleting the plugin
		if ( $deleted ) $this->unassign_deleted_plugins();
	}

	/**
	 * Prior to version 2.21.7, plugins were not unassigned from folders upon
	 * deletion which could cause the folder item counts to be off.  This
	 * function unassigns deleted plugins from folders.
	 */
	private function unassign_deleted_plugins() {
		$plugins 			= get_plugins();
		$plugin_ids 		= get_option( 'wicked_folders_plugin_ids', array() );
		$taxonomy 			= Wicked_Folders::get_tax_name( Wicked_Folders::get_plugin_post_type_name() );
		$deleted_plugins 	= array_diff_key( $plugin_ids, $plugins );

		foreach ( $deleted_plugins as $plugin_id ) {
			// Unassign the plugin from all folders (this ensures that the
			// folder item counts will be correct)
			wp_set_object_terms( $plugin_id, array(), $taxonomy );
		}
	}

	public function wicked_folders_setting_tabs( $tabs ) {
		$tabs[] = array(
			'label' 	=> __( 'Permissions', 'wicked-folders' ),
			'callback' 	=> array( $this, 'permissions_settings_page' ),
			'slug'		=> 'permissions',
		);

		return $tabs;
	}

	public function permissions_settings_page() {
		$policies = get_posts( array(
		    'post_type'         => Wicked_Folders_Folder_Collection_Policy::get_post_type_name(),
		    'posts_per_page'    => -1,
		    'order'             => 'ASC',
		    'orderby'           => 'menu_order title',
		) );

		$post_types = get_post_types( array(
			'show_ui' => true,
		), 'objects' );

		$post_types[] = get_post_type_object( Wicked_Folders::get_user_post_type_name() );
		$post_types[] = get_post_type_object( Wicked_Folders::get_plugin_post_type_name() );
		$post_types[] = get_post_type_object( Wicked_Folders::get_gravity_forms_form_post_type_name() );
		$post_types[] = get_post_type_object( Wicked_Folders::get_gravity_forms_entry_post_type_name() );

		$taxonomy_policies = get_option( 'wf_taxonomy_policies', array() );

		unset( $post_types['wf_collection_policy'] );

		include( dirname( dirname( __FILE__ ) ) . '/admin-templates/permissions-settings-page.php' );
	}

	/**
	 * WordPress 'add_meta_boxes' action.
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'wf_collection_policy_permissions',
			__( 'Permissions', 'wicked-folders' ),
			array( $this, 'security_policy_permissions_meta_box' ),
			Wicked_Folders_Folder_Collection_Policy::get_post_type_name()
        );
	}

	/**
	 * Permissions meta box for security policies.
	 */
	public function security_policy_permissions_meta_box( $post ) {
		$roles 			= wp_roles();
		$permissions 	= get_post_meta( $post->ID, 'permissions', true );

		if ( ! is_array( $permissions ) ) $permissions = array();

		include( dirname( dirname( __FILE__ ) ) . '/admin-templates/security-policy-permissions-meta-box.php' );
	}

	/**
	 * WordPress 'save_post' action used to save the data from the security
	 * policy permissions meta box.
	 */
	public function save_security_policy_permissions_meta_box( $post_id ) {
		$post_type = get_post_type( $post_id );

		// This only applies to security policy post types
		if ( $post_type != Wicked_Folders_Folder_Collection_Policy::get_post_type_name() ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$permissions = array();

		// Sanity check
		if ( isset( $_POST['role'] ) && is_array( $_POST['role'] ) ) {
			$roles = array_map( 'sanitize_key', $_POST['role'] );

			foreach ( $roles as $index => $role ) {
				$permissions[] = array(
					'role' 			=> $role,
					'create' 		=> isset( $_POST[ "{$role}_create" ] ),
					'view_others' 	=> isset( $_POST[ "{$role}_view_others" ] ),
					'edit_others' 	=> isset( $_POST[ "{$role}_edit_others" ] ),
					'delete_others' => isset( $_POST[ "{$role}_delete_others" ] ),
					'assign_others' => isset( $_POST[ "{$role}_assign_others" ] ),
				);
			}
		}

		update_post_meta( $post_id, 'permissions', $permissions );
	}

	/**
	 * WordPress 'tag_row_actions' filter. Remove edit and delete links if
	 * necessary based on security policy.
	 */
	public function tag_row_actions( $actions, $tag ) {
		$policy 	= Wicked_Folders_Folder_Collection_Policy::get_taxonomy_policy( $tag->taxonomy );
		$user_id 	= get_current_user_id();

		if ( $policy ) {
			if ( ! $policy->can_edit( $tag->term_id, $user_id ) ) {
				unset( $actions['edit'] );
				unset( $actions['inline hide-if-no-js'] );
			}

			if ( ! $policy->can_delete( $tag->term_id, $user_id ) ) {
				unset( $actions['delete'] );
			}
		}

		return $actions;
	}

	/**
	 * WordPress 'get_edit_term_link' filter.
	 */
	public function get_edit_term_link( $location, $term_id, $taxonomy, $object_type ) {
		$policy 	= Wicked_Folders_Folder_Collection_Policy::get_taxonomy_policy( $taxonomy );
		$user_id 	= get_current_user_id();

		if ( $policy && ! $policy->can_edit( $term_id, $user_id ) ) {
			return;
		}

		return $location;
	}

	/**
	 * WordPress 'get_terms' filter.
	 */
	public function get_terms( $terms, $taxonomies, $args, $term_query ) {
		// Only apply security policy to terms UI
		if ( 'edit-tags.php' == basename( $_SERVER[ 'SCRIPT_FILENAME' ] ) ) {
			$policies 	= array();
			$user_id 	= get_current_user_id();

			// For some reason $terms isn't always an array of term objects.
			// Only perform this logic if we're working with an array of term
			// objects.
			if ( isset( $terms[0] ) && is_a( $terms[0], 'WP_Term' ) ) {
				for ( $i = count( $terms ) - 1; $i > -1; $i-- ) {
					$term 		= $terms[ $i ];
					$taxonomy 	= $term->taxonomy;

					if ( ! isset( $policies[ $taxonomy ] ) ) {
						$policies[ $term->taxonomy ] = Wicked_Folders_Folder_Collection_Policy::get_taxonomy_policy( $taxonomy );
					}

					$policy = $policies[ $taxonomy ];

					if ( $policy ) {
						if ( ! $policy->can_view( $term->term_id, $user_id ) ) {
							unset( $terms[ $i ] );
						}
					}
				}
			}
		}

		return $terms;
	}

	/**
	 * WordPress 'map_meta_cap' filter.
	 */
	public function map_meta_cap( $caps, $cap, $user_id, $args ) {
		if ( isset( $_POST['action'] ) && 'add-tag' == $_POST['action'] && isset( $_POST['taxonomy'] ) ) {
			$policy = Wicked_Folders_Folder_Collection_Policy::get_taxonomy_policy( sanitize_key( $_POST['taxonomy'] ) );

			if ( $policy && ! $policy->can_create( $user_id ) ) {
				if ( false !== $key = array_search( 'manage_categories', $caps ) ) {
					unset( $caps[ $key ] );

					$caps[] = 'do_not_allow';
				}
			}
		}

		if ( 'term.php' == basename( $_SERVER[ 'SCRIPT_FILENAME' ] ) ) {
			if ( 'edit_term' == $cap && isset( $args[0] ) ) {
				$term = get_term( $args[0] );

				if ( ! is_wp_error( $term ) ) {
					$policy = Wicked_Folders_Folder_Collection_Policy::get_taxonomy_policy( $term->taxonomy );

					if ( $policy && ! $policy->can_edit( $term->term_id, $user_id ) ) {
						if ( false !== $key = array_search( 'manage_categories', $caps ) ) {
							unset( $caps[ $key ] );

							$caps[] = 'do_not_allow';
						}
					}
				}
			}
		}

		return $caps;
	}

	/**
	 * WordPress 'redirect_post_location' filter.
	 */
	public function redirect_post_location( $location, $post_id ) {
		// Redirect collection policies back to permission settings after save
		if ( isset( $_POST['post_type'] ) && Wicked_Folders_Folder_Collection_Policy::get_post_type_name() == $_POST['post_type'] ) {
			if ( isset( $_POST['save'] ) || isset( $_POST['publish'] ) ) {
				$location = admin_url( 'options-general.php?page=wicked_folders_settings&tab=permissions&collection_policy_saved=1' );
			}
		}

		return $location;
	}

	/**
	 * Filters TablePress table IDs so that only tables assigned to the current
	 * folder are displayed.
	 */
	public function tablepress_load_all_tables( $table_ids = array() ) {
		global $current_screen, $wpdb;

		// This should only take place in the admin
		if ( ! is_admin() ) return $table_ids;

		// Make sure screen variable is set
		if ( empty( $current_screen ) ) return $table_ids;

		// Make sure we're on the TablePress list screen
		if ( 'tablepress_list' != $current_screen->id ) return $table_ids;

		$post_type 			= 'tablepress_table';
		$taxonomy 			= Wicked_Folders::get_tax_name( $post_type );
		$folder_id 			= isset( $_GET[ "wicked_{$post_type}_folder_filter" ] ) ? sanitize_text_field( $_GET[ "wicked_{$post_type}_folder_filter" ] ) : false;
		$state 				= new Wicked_Folders_Screen_State( $current_screen->id, get_current_user_id() );
		$map 				= array();
		$tablepress_data 	= get_option( 'tablepress_tables', '' );
		$tablepress_data 	= json_decode( $tablepress_data );

		if ( isset( $tablepress_data->table_post ) ) {
			$map = ( array ) $tablepress_data->table_post;
		}

		if ( false === $folder_id && ! empty( $state->folder ) ) {
			$folder_id = $state->folder;
		}

		// Nothing to do if we still don't have a folder
		if ( false == $folder_id ) return $table_ids;

		// Numeric folders IDs are terms
		if ( is_numeric( $folder_id ) ) {
			// Get the post IDs of all tables in the folder
			$post_ids = get_posts( array(
			    'post_type'         => $post_type,
			    'posts_per_page'    => -1,
				'fields' 			=> 'ids',
				'post_status' 		=> array( 'publish', 'pending', 'draft', 'future', 'private' ),
			    'tax_query' => array(
			        array(
			            'taxonomy'  		=> $taxonomy,
						'field' 			=> 'term_id',
			            'terms'     		=> array( $folder_id ),
						'include_children' 	=> false,
			        ),
			    ),
			) );

			// Reduce the map to only posts that are in the folder
			$map = array_intersect( $map, $post_ids );

			// TablePress table IDs are the keys
			$table_ids = array_keys( $map );
		}

		if ( 'unassigned_dynamic_folder' == $folder_id ) {
			// Get IDs of tables assigned to at least one folder
			$object_ids = $wpdb->get_col(
				$wpdb->prepare(
					"
						SELECT object_id FROM {$wpdb->term_relationships} AS wf_term_relationships
						INNER JOIN {$wpdb->term_taxonomy} AS wf_term_taxonomy ON wf_term_relationships.term_taxonomy_id = wf_term_taxonomy.term_taxonomy_id
						WHERE wf_term_taxonomy.taxonomy = %s
					", $taxonomy
				)
			);

			// Remove assigned posts
			$map = array_diff( $map, $object_ids );

			// TablePress table IDs are the keys
			$table_ids = array_keys( $map );
		}

		return $table_ids;
	}

	/**
	 * Uses the WordPress 'admin_post' action to inject a folder filter into
	 * the TablePress thickbox.
	 */
	public function add_tablepress_thickbox_folder_filter() {
		// TablePress doesn't really offer any options to hook into the
		// thickbox; however, it does call wp_print_scripts which in turn runs
		// the action 'wp_print_scripts'. Since this output comes before jQuery
		// is loaded (and we don't really have an option to make it come later),
		// a timer is used to check for jQuery
		add_action( 'wp_print_scripts', function(){
			$url 		= remove_query_arg( 'paged' );
			$url 		= remove_query_arg( 'wf_folder_id', $url );
			$folder_id 	= isset( $_GET['wf_folder_id'] ) ? ( int ) $_GET['wf_folder_id'] : 0;
			$filter 	= wp_dropdown_categories( array(
				'show_option_all' 	=> __( 'Filter by Folder', 'wicked-folders' ),
				'id' 				=> 'wf-tablepress-folder-filter',
				'name' 				=> 'wf_tablepress_folder_filter',
				'taxonomy' 			=> 'wf_tablepress_table_folders',
				'selected' 			=> $folder_id,
				'hide_empty' 		=> false,
				'hierarchical' 		=> true,
				'echo' 				=> false,
			) );

			$filter = preg_replace( "/\r|\n/", "", $filter );

			?>
				<script>
					( function(){
						var timer = null;

						timer = setInterval( function(){
							if ( jQuery ) {
								clearInterval( timer );

								ready();
							}
						}, 100 );

						function ready() {
							$ = jQuery;

							$( function(){
								var $filter = $( '<?php echo addslashes( $filter ); ?>' );

								$filter.appendTo( '#tablepress-page form' );
								$filter.change( function(){
									document.location = '<?php echo $url; ?>&wf_folder_id=' + $( this ).val();
								} );
							} );
						}
					} )();
				</script>
			<?php
		} );

		add_action( 'wp_print_styles', function(){
			?>
				<style type="text/css">
					#wf-tablepress-folder-filter {
						margin: 0 0 20px 0;
					}
				</style>
			<?php
		} );

		add_filter( 'tablepress_load_all_tables', function( $table_ids ){
			$post_type 			= 'tablepress_table';
			$taxonomy 			= Wicked_Folders::get_tax_name( $post_type );
			$folder_id 			= isset( $_GET['wf_folder_id'] ) ? ( int ) $_GET['wf_folder_id'] : false;
			$map 				= array();
			$tablepress_data 	= get_option( 'tablepress_tables', '' );
			$tablepress_data 	= json_decode( $tablepress_data );

			if ( isset( $tablepress_data->table_post ) ) {
				$map = ( array ) $tablepress_data->table_post;
			}

			// Don't do anything if we don't have a folder
			if ( ! $folder_id ) return $table_ids;

			// Get the post IDs of all tables in the folder
			$post_ids = get_posts( array(
			    'post_type'         => $post_type,
			    'posts_per_page'    => -1,
				'fields' 			=> 'ids',
				'post_status' 		=> array( 'publish', 'pending', 'draft', 'future', 'private' ),
			    'tax_query' => array(
			        array(
			            'taxonomy'  		=> $taxonomy,
						'field' 			=> 'term_id',
			            'terms'     		=> array( $folder_id ),
						'include_children' 	=> false,
			        ),
			    ),
			) );

			// Reduce the map to only posts that are in the folder
			$map = array_intersect( $map, $post_ids );

			// TablePress table IDs are the keys
			$table_ids = array_keys( $map );

			return $table_ids;
		} );
	}

	/**
	 * TablePress 'tablepress_event_copied_table' action.
	 *
	 * Copies folder terms to a table when the table is copied.
	 */
	function tablepress_event_copied_table( $new_table_id, $table_id ) {
		$tablepress_data 	= get_option( 'tablepress_tables', '' );
		$tablepress_data 	= json_decode( $tablepress_data );

		if ( isset( $tablepress_data->table_post->{ $table_id } ) && isset( $tablepress_data->table_post->{ $new_table_id } ) ) {
			$table_post_id 		= $tablepress_data->table_post->{ $table_id };
			$new_table_post_id 	= $tablepress_data->table_post->{ $new_table_id };
			$table_folders 		= wp_get_object_terms( $table_post_id, 'wf_tablepress_table_folders', array(
				'fields' => 'ids',
			) );

			// Append the folders that are assigned to the original table to the
			// newly copied table
			wp_set_object_terms( $new_table_post_id, $table_folders, 'wf_tablepress_table_folders', true );
		}
	}
}
