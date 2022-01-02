<?php
/**
 * Content helper.
 *
 * @package Ultimate_Dashboard_Pro
 */

namespace UdbPro\Helpers;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use Udb\Helpers\Content_Helper as Free_Content_Helper;

/**
 * Class to setup content helper.
 */
class Content_Helper extends Free_Content_Helper {

	/**
	 * Check whether or not post is built with Elementor.
	 *
	 * @param int $post_id ID of the post being checked.
	 * @return bool
	 */
	public function is_built_with_elementor( $post_id ) {
		return ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->documents->get( $post_id )->is_built_with_elementor() ? true : false );
	}

	/**
	 * Check whether or not post is built with Beaver Builder.
	 *
	 * @param int $post_id ID of the post being checked.
	 * @return bool
	 */
	public function is_built_with_beaver( $post_id ) {
		return ( class_exists( '\FLBuilderModel' ) && \FLBuilderModel::is_builder_enabled( $post_id ) ? true : false );
	}

	/**
	 * Check whether the post type of the given post id is checked in Brizy settings.
	 *
	 * @see wp-content/plugins/brizy/editor.php
	 *
	 * @param int $post_id The post ID to check.
	 */
	public function supported_in_brizy_post_types( $post_id ) {

		$post = get_post( $post_id );

		$brizy_editor         = \Brizy_Editor::get();
		$supported_post_types = $brizy_editor->supported_post_types();

		if ( in_array( $post->post_type, $supported_post_types, true ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Check whether or not post is built with Brizy Builder.
	 *
	 * @param int $post_id ID of the post being checked.
	 * @return bool
	 */
	public function is_built_with_brizy( $post_id ) {

		if ( class_exists( '\Brizy_Editor_Post' ) ) {

			if ( ! $this->supported_in_brizy_post_types( $post_id ) ) {
				return false;
			}

			try {
				$post = \Brizy_Editor_Post::get( $post_id );

				if ( is_object( $post ) && method_exists( $post, 'uses_editor' ) && $post->uses_editor() ) {
					return true;
				}
			} catch ( Exception $e ) {
				return false;
			}
		}

		return false;

	}

	/**
	 * Check whether or not post is built with Divi Builder.
	 *
	 * @param int $post_id ID of the post being checked.
	 * @return bool
	 */
	public function is_built_with_divi( $post_id ) {

		return ( function_exists( 'et_pb_is_pagebuilder_used' ) && et_pb_is_pagebuilder_used( $post_id ) ? true : false );

	}

	/**
	 * Check whether or not post is built with WordPress block editor.
	 *
	 * @param int $post_id ID of the post being checked.
	 * @return bool
	 */
	public function is_built_with_blocks( $post_id ) {

		if ( version_compare( $GLOBALS['wp_version'], '5.0', '<' ) ) {
			return false;
		}

		if ( ! function_exists( 'has_blocks' ) || ! has_blocks( $post_id ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Check whether or not post is built with Oxygen Builder.
	 *
	 * @param int $post_id ID of the post being checked.
	 * @return bool
	 */
	public function is_built_with_oxygen( $post_id ) {

		if ( ! function_exists( 'oxygen_vsb_current_user_can_access' ) || ! defined( 'CT_VERSION' ) ) {
			return false;
		}

		if ( ! get_post_meta( $post_id, 'ct_builder_shortcodes', true ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Get the editor/ builder of the given post.
	 *
	 * @param int $post_id ID of the post being checked.
	 * @return string The content editor name.
	 */
	public function get_content_editor( $post_id ) {

		if ( $this->is_built_with_elementor( $post_id ) ) {
			return 'elementor';
		} elseif ( $this->is_built_with_beaver( $post_id ) ) {
			return 'beaver';
		} elseif ( $this->is_built_with_brizy( $post_id ) ) {
			return 'brizy';
		} elseif ( $this->is_built_with_divi( $post_id ) ) {
			return 'divi';
		} elseif ( $this->is_built_with_blocks( $post_id ) ) {
			return 'block';
		} elseif ( $this->is_built_with_oxygen( $post_id ) ) {
			return 'oxygen';
		}

		return 'default';

	}

	/**
	 * Get active page builders.
	 *
	 * @return array The list of builder names.
	 */
	public function get_active_page_builders() {

		$names = array();

		if ( defined( 'ELEMENTOR_VERSION' ) || defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			array_push( $names, 'elementor' );
		}

		if ( defined( 'ET_BUILDER_VERSION' ) ) {
			array_push( $names, 'divi' );
		}

		if ( defined( 'FL_BUILDER_VERSION' ) ) {
			array_push( $names, 'beaver' );
		}

		if ( defined( 'BRIZY_VERSION' ) ) {
			array_push( $names, 'brizy' );
		}

		if ( defined( 'CT_VERSION' ) ) {
			array_push( $names, 'oxygen' );
		}

		return $names;

	}

	/**
	 * Parse content with the specified builder in admin area.
	 *
	 * @param WP_Post|int $post Either the admin page's post object or post ID.
	 * @param string      $builder_name The content builder name.
	 */
	public function output_content_using_builder( $post, $builder_name ) {

		if ( is_int( $post ) ) {
			$post_id = $post;
			$post    = get_post( $post_id );
		} elseif ( is_object( $post ) && property_exists( $post, 'ID' ) ) {
			$post_id = $post->ID;
		} else {
			return;
		}

		if ( 'elementor' === $builder_name ) {

			$elementor = \Elementor\Plugin::$instance;

			$elementor->frontend->register_styles();
			$elementor->frontend->enqueue_styles();

			echo $elementor->frontend->get_builder_content( $post_id, true );

			$elementor->frontend->register_scripts();
			$elementor->frontend->enqueue_scripts();

		} elseif ( 'beaver' === $builder_name ) {

			echo do_shortcode( '[fl_builder_insert_layout id="' . $post_id . '"]' );

		} elseif ( 'divi' === $builder_name ) {

			$style_suffix = et_load_unminified_styles() ? '' : '.min';

			wp_enqueue_style( 'et-builder-modules-style', ET_BUILDER_URI . '/styles/frontend-builder-plugin-style' . $style_suffix . '.css', array(), ET_BUILDER_VERSION );

			$post_content = $post->post_content;
			$post_content = et_builder_get_layout_opening_wrapper() . $post_content . et_builder_get_layout_closing_wrapper();
			$post_content = et_builder_get_builder_content_opening_wrapper() . $post_content . et_builder_get_builder_content_closing_wrapper();

			echo apply_filters( 'the_content', $post_content );

		} elseif ( 'brizy' === $builder_name ) {

			$this->render_brizy_content( $post_id );

		} elseif ( 'oxygen' === $builder_name ) {

			$shortcodes = get_post_meta( $post_id, 'ct_builder_shortcodes', true );

			echo ct_do_shortcode( $shortcodes );

		} else {

			echo apply_filters( 'the_content', $post->post_content );

		}

	}

	/**
	 * Prepare Brizy output.
	 * This function is being called in the admin page module output and widget module output.
	 *
	 * This source can be found at `is_view_page` condition inside `initialize_front_end` function
	 * in brizy/public/main.php file.
	 *
	 * What we don't use from that function:
	 * - preparePost private function
	 * - template_include hook
	 * - `wpautop` filter removal from `the_content` (moved to our `render_brizy_content` function)
	 *
	 * @see wp-content/plugins/brizy/public/main.php
	 * @see wp-content/plugins/brizy/editor/post.php
	 *
	 * @param int    $post_id The post id.
	 * @param string $location The output location.
	 *                  Accepts "frontend", and other values (such as "admin_page", "dashboard").
	 */
	public function prepare_brizy_output( $post_id, $location = 'admin_page' ) {

		$post = get_post( $post_id );

		if ( ! $post ) {
			return;
		}

		$brizy_post   = \Brizy_Editor_Post::get( $post_id );
		$brizy_public = \Brizy_Public_Main::get( $brizy_post );

		if ( 'admin_page' === $location || 'dashboard' === $location ) {

			/**
			 * Check if the post needs to be compiled.
			 *
			 * Let's compile it if it hasn't been compiled.
			 * However, when compiling it, it takes sometime.
			 *
			 * That's why it takes time / very slow when
			 * first time visiting the dashboard / admin page
			 * or first time visiting it after the post being updated with Brizy.
			 *
			 * However, in the next visit (since it has been compiled), it will be much faster.
			 *
			 * @see wp-content/plugins/brizy/public/main.php
			 * @see wp-content/plugins/brizy/editor/post.php
			 */
			$needs_compile = ! $brizy_post->isCompiledWithCurrentVersion() || $brizy_post->get_needs_compile();

			if ( $needs_compile ) {
				$brizy_post->compile_page();
				$brizy_post->saveStorage();
				$brizy_post->savePost();
			}

			// The value of $body_class is array, let's convert it to string.
			$body_classes = $brizy_public->body_class_frontend( array() );
			$body_classes = implode( ' ', $body_classes );

			add_filter(
				'admin_body_class',
				function ( $classes ) use ( $body_classes ) {
					return $classes . ' ' . $body_classes;
				}
			);

			// insert the compiled head and content.
			add_action( 'admin_head', array( $brizy_public, 'insert_page_head' ) );
			add_action( 'admin_bar_menu', array( $brizy_public, 'toolbar_link' ), 999 );
			add_action( 'admin_enqueue_scripts', array( $brizy_public, '_action_enqueue_preview_assets' ), 9999 );
			add_filter( 'the_content', array( $brizy_public, 'insert_page_content' ), -12000 );
			add_action( 'brizy_template_content', array( $brizy_public, 'brizy_the_content' ) );

			$this->handle_brizy_assets();

		} else {

			// Insert the compiled head and content.
			add_filter( 'body_class', array( $brizy_public, 'body_class_frontend' ) );
			add_action( 'wp_head', array( $brizy_public, 'insert_page_head' ) );
			add_action( 'admin_bar_menu', array( $brizy_public, 'toolbar_link' ), 999 );
			add_action( 'wp_enqueue_scripts', array( $brizy_public, '_action_enqueue_preview_assets' ), 9999 );
			add_filter( 'the_content', array( $brizy_public, 'insert_page_content' ), - 12000 );
			add_action( 'brizy_template_content', array( $brizy_public, 'brizy_the_content' ) );

			$this->handle_brizy_assets();

		}

	}

	/**
	 * Handle Brizy assets.
	 */
	public function handle_brizy_assets() {

		if ( ! class_exists( '\Brizy_Public_AssetEnqueueManager' ) ) {
			return;
		}

		$brizy_enqueue_manager = \Brizy_Public_AssetEnqueueManager::_init();

		add_action( 'admin_enqueue_scripts', array( $brizy_enqueue_manager, 'enqueueStyles' ), 10002 );
		add_action( 'admin_enqueue_scripts', array( $brizy_enqueue_manager, 'enqueueScripts' ), 10002 );
		add_filter( 'admin_enqueue_scripts', array( $brizy_enqueue_manager, 'addEditorConfigVar' ), 10002 );
		add_filter( 'script_loader_tag', array( $brizy_enqueue_manager, 'addScriptAttributes' ), 10, 2 );
		add_filter( 'style_loader_tag', array( $brizy_enqueue_manager, 'addStyleAttributes' ), 10, 2 );
		add_action( 'admin_head', array( $brizy_enqueue_manager, 'insertHeadCodeAssets' ) );
		add_action( 'admin_footer', array( $brizy_enqueue_manager, 'insertBodyCodeAssets' ) );

	}

	/**
	 * Render Brizy content.
	 * This function is being called from `output_content_using_builder` function in this file.
	 *
	 * @see wp-content/plugins/brizy/public/main.php
	 *
	 * @param int $post_id The post id.
	 */
	public function render_brizy_content( $post_id ) {

		$post = get_post( $post_id );

		if ( ! $post ) {
			return;
		}

		// @see wp-content/plugins/brizy/public/main.php
		remove_filter( 'the_content', 'wpautop' );

		$brizy_post   = \Brizy_Editor_Post::get( $post_id );
		$brizy_public = \Brizy_Public_Main::get( $brizy_post );

		$brizy_public->brizy_the_content();

		// Let's bring back the filter after rendering the content.
		add_filter( 'the_content', 'wpautop' );

	}

	/**
	 * Prepare Oxygen output.
	 * This function is being called in the admin page module output.
	 * Might also be called in the widget module output.
	 *
	 * @see wp-content/plugins/oxygen/component-framework/component-init.php
	 * @see wp-content/plugins/oxygen/component-framework/includes/cache.php
	 * @see wp-content/plugins/oxygen/component-framework/components/classes/gallery.class.php as a component example.
	 * @see wp-content/plugins/wp-admin-pages-pro/inc/class-wu-oxygen-builder-support.php to look at "wp admin pages pro" implementation of Oxygen support.
	 *
	 * @param int    $post_id The post id.
	 * @param string $location The output location.
	 *                  Accepts "frontend", and other values (such as "admin_page", "dashboard").
	 */
	public function prepare_oxygen_output( $post_id, $location = 'admin_page' ) {

		add_filter( 'admin_body_class', array( $this, 'oxygen_body_class' ) );
		add_action( 'admin_enqueue_scripts', 'ct_enqueue_scripts' );
		add_action(
			'admin_enqueue_scripts',
			function () use ( $post_id ) {
				$this->oxygen_enqueue_scripts( $post_id );
			}
		);

		$shortcodes = get_post_meta( $post_id, 'ct_builder_shortcodes', true );

		global $oxygen_vsb_components;

		if ( ! empty( $shortcodes ) && ! empty( $oxygen_vsb_components ) ) {
			foreach ( $oxygen_vsb_components as $component_name => $component ) {
				$contains_oxy_prefix = false !== stripos( $shortcodes, '[/oxy_' . $component_name . ']' ) ? true : false;
				$contains_ct_prefix  = false !== stripos( $shortcodes, '[/ct_' . $component_name . ']' ) ? true : false;

				if ( $contains_oxy_prefix || $contains_ct_prefix ) {
					if ( method_exists( $component, 'output_js' ) ) {
						add_action( 'admin_enqueue_scripts', array( $component, 'output_js' ) );
					}

					if ( method_exists( $component, 'js_css_output' ) ) {
						add_action( 'admin_enqueue_scripts', array( $component, 'js_css_output' ) );
					}
				}
			}
		}

	}

	/**
	 * Add oxygen body classes.
	 * This function is being hooked in the prepare_oxygen_output() function.
	 *
	 * @param string $classes The existing body class names.
	 * @return string
	 */
	public function oxygen_body_class( $classes ) {

		$oxygen_classes = ct_body_class( array() );
		$oxygen_classes = implode( ' ', $oxygen_classes );

		return $classes . ' ' . $oxygen_classes;

	}

	/**
	 * Enqueue necessary assets to support oxygen builder.
	 * This function is being called in the prepare_oxygen_output() function.
	 *
	 * @param int $post_id The post ID.
	 */
	public function oxygen_enqueue_scripts( $post_id = 0 ) {

		$xlink = 'css';

		// Check whether to load universal css or not.
		if ( (bool) get_option( 'oxygen_vsb_universal_css_cache' ) && (bool) get_option( 'oxygen_vsb_universal_css_cache_success' ) ) {
			$xlink = 'css&nouniversal=true';

			$load_cached_universal = true;
		}

		// Check whether to load dynamic xlink or cached CSS files.
		if ( ! oxygen_vsb_load_cached_css_files() ) {
			wp_enqueue_style( 'oxygen-styles', ct_get_current_url( 'xlink=' . $xlink ) );
		}

		if ( $load_cached_universal ) {
			$universal_css_url = get_option( 'oxygen_vsb_universal_css_url' );
			$universal_css_url = add_query_arg( 'cache', get_option( 'oxygen_vsb_last_save_time' ), $universal_css_url );

			wp_enqueue_style( 'oxygen-universal-styles', $universal_css_url );

			if ( $post_id ) {
				$files_meta = get_option( 'oxygen_vsb_css_files_state', array() );

				if ( isset( $files_meta[ $post_id ] ) && isset( $files_meta[ $post_id ]['success'] ) ) {
					$individual_css_url = $files_meta[ $post_id ]['url'];
					$individual_css_url = add_query_arg( 'cache', $files_meta[ $post_id ]['last_save_time'], $individual_css_url );

					wp_enqueue_style( 'oxygen-cache-' . $post_id, $individual_css_url );
				}
			}
		}

	}

	/**
	 * Get saved templates for specified page builder.
	 *
	 * @param string $builder The page builder name.
	 * @return array The saved templates.
	 */
	public function get_page_builder_templates( $builder ) {

		$templates = array();

		if ( 'elementor' === $builder ) {
			$builder_posts = get_posts(
				array(
					'post_type'   => 'elementor_library',
					'post_status' => 'publish',
					'numberposts' => -1,
				)
			);

			foreach ( $builder_posts as $builder_post ) {
				array_push(
					$templates,
					array(
						'id'      => $builder_post->ID,
						'title'   => $builder_post->post_title,
						'builder' => 'elementor',
					)
				);
			}
		} elseif ( 'divi' === $builder ) {
			$builder_posts = get_posts(
				array(
					'post_type'   => 'et_pb_layout',
					'post_status' => 'publish',
					'numberposts' => -1,
				)
			);

			foreach ( $builder_posts as $builder_post ) {
				array_push(
					$templates,
					array(
						'id'      => $builder_post->ID,
						'title'   => $builder_post->post_title,
						'builder' => 'divi',
					)
				);
			}
		} elseif ( 'beaver' === $builder ) {
			if ( class_exists( '\FLBuilderModel' ) ) {
				$builder_posts = get_posts(
					array(
						'post_type'   => 'fl-builder-template',
						'post_status' => 'publish',
						'numberposts' => -1,
					)
				);

				foreach ( $builder_posts as $builder_post ) {
					array_push(
						$templates,
						array(
							'id'      => $builder_post->ID,
							'title'   => $builder_post->post_title,
							'builder' => 'beaver',
						)
					);
				}
			}
		} elseif ( 'brizy' === $builder ) {
			$builder_posts = get_posts(
				array(
					'post_type'   => 'brizy_template',
					'post_status' => 'publish',
					'numberposts' => -1,
				)
			);

			foreach ( $builder_posts as $builder_post ) {
				array_push(
					$templates,
					array(
						'id'      => $builder_post->ID,
						'title'   => $builder_post->post_title,
						'builder' => 'brizy',
					)
				);
			}
		} elseif ( 'oxygen' === $builder ) {
			$builder_posts = get_posts(
				array(
					'post_type'   => 'ct_template',
					'post_status' => 'publish',
					'numberposts' => -1,
				)
			);

			foreach ( $builder_posts as $builder_post ) {
				array_push(
					$templates,
					array(
						'id'      => $builder_post->ID,
						'title'   => $builder_post->post_title,
						'builder' => 'oxygen',
					)
				);
			}
		}

		return $templates;

	}

}
