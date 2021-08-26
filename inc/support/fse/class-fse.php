<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Support\FSE
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since 		5.0.0
 * @version		5.0.0
 */

namespace WeCodeArt\Support;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Singleton;
use WeCodeArt\Integration;

/**
 * The FSE object.
 */
class FSE implements Integration {

    use Singleton;

	/**
	 * Get Conditionals
	 *
	 * @return void
	 */
	public static function get_conditionals() {
		wecodeart( 'conditionals' )->set( [
			'with_fse' => FSE\Condition::class,
		] );
		
		return [ 'with_fse' ];
	}

	/**
	 * Send to Constructor
	 */
	public function register_hooks() {
		add_action( 'admin_init', [ $this, 'admin_init' ] );
	}

	/**
	 * Admin Hooks
	 */
	public function admin_init() {
		add_settings_field(
			'full-site-editor',
			esc_html__( 'Site Editor', 'wecodeart' ),
			'gutenberg_display_experiment_field',
			'gutenberg-experiments',
			'gutenberg_experiments_section',
			[
				'label' => esc_html__( 'Enable Site Editor', 'wecodeart' ),
				'id'    => 'full-site-editor',
			]
		);
	
		$this->legacy_admin_links();
	
		if ( ! $this->site_editor_enabled() ) {
			$this->remove_site_editor_admin_link();
		}
	}

	/**
	 * Site editor enabled
	 */
	public function site_editor_enabled() {
		return get_option( 'gutenberg-experiments' ) && array_key_exists( 'full-site-editor', get_option( 'gutenberg-experiments' ) );
	}
	
	/**
	 * Adds the Customizer and Widgets menu links back to the Dashboard under themes.
	 */
	public function legacy_admin_links() {
		global $submenu;
		if ( isset( $submenu['themes.php'] ) ) {
			// Add Customize back to the admin menu.
			$customize_url            = add_query_arg( 'return', urlencode( remove_query_arg( wp_removable_query_args(), wp_unslash( $_SERVER['REQUEST_URI'] ) ) ), 'customize.php' );
			$submenu['themes.php'][6] = array( __( 'Customize', 'wecodeart' ), 'customize', esc_url( $customize_url ), '', 'hide-if-no-customize' );
	
			if (
				function_exists( 'gutenberg_use_widgets_block_editor' ) &&
				gutenberg_use_widgets_block_editor() &&
				! function_exists( 'wp_use_widgets_block_editor' ) &&
				current_theme_supports( 'widgets' )
			) {
				// Find Widgets menu
				$has_widgets_menu = false;
				foreach ( $submenu['themes.php'] as $index => $menu_item ) {
					if (
						! empty( $menu_item[2] ) &&
						( false !== strpos( $menu_item[2], 'gutenberg-widgets' ) ||
						false !== strpos( $menu_item[2], 'widgets.php' ) )
					) {
						$has_widgets_menu = true;
					}
				}
	
				// Add Widgets back to the admin menu.
				if ( ! $has_widgets_menu ) {
					add_theme_page(
						esc_html__( 'Widgets', 'wecodeart' ),
						esc_html__( 'Widgets', 'wecodeart' ),
						'edit_theme_options',
						'gutenberg-widgets',
						'the_gutenberg_widgets',
						2
					);
				}
			}
	
			ksort( $submenu['themes.php'] );
		}
	}
	
	/**
	 * Removes the Site Editor link from the admin.
	 */
	public function remove_site_editor_admin_link() {
		global $menu;
		// Remove Site Editor.
		$site_editor_index = false;
		if( ! empty( $menu ) ) {
			foreach ( $menu as $index => $menu_item ) {
				if ( ! empty( $menu_item[5] ) && false !== strpos( $menu_item[5], 'toplevel_page_gutenberg-edit-site' ) ) {
					$site_editor_index = $index;
				}
			}
		}
		if( $site_editor_index ) {
			unset( $menu[ $site_editor_index ] );
		}
	}
}