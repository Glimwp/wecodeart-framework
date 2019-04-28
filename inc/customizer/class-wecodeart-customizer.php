<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Customizer
 * @copyright   Copyright (c) 2019, WeCodeArt Framework
 * @since 		1.6
 * @version		3.8.1
 */

namespace WeCodeArt;

if ( ! defined( 'ABSPATH' ) ) exit();

use WeCodeArt\Utilities\Helpers;
use WeCodeArt\Customizer\Partials;
use WeCodeArt\Customizer\Controls;

/**
 * Customizer Options
 */
class Customizer {

	use \WeCodeArt\Singleton; 

	/**
	 * Customizer Configurations.
	 *
	 * @access 	private
	 * @since 	3.5
	 * @var 	array
	 */
	private static $configurations; 
	
	/**
	 * Send to Constructor
	 * @since 3.6.2
	 */
	public function init() {
		// Init Defaults.
		add_action( 'init', array( $this, 'set_defaults' ), 10 );

		// Register our custom settings + defaults.
		add_action( 'customize_register', array( $this, 'register_settings'	) );
		add_action( 'customize_register', array( $this, 'apply_defaults'	) );

		// Add Preview Script.
		add_action( 'customize_preview_init', array( $this, 'enqueue_preview' ) );

		// Custom Controls.
		Controls::get_instance();
		
		// Selective Refresh Partials.
		Partials::get_instance(); // default overrides + templates.

		new Customizer\Configs;
		new Customizer\Configs\Overrides;
		new Customizer\Configs\Header;
		new Customizer\Configs\Content;
		new Customizer\Configs\Footer;
	}

	/**
	 * Grab our Customizer Scripts.
	 */
	public function enqueue_preview() {
		$handle = strtolower( str_replace( '\\', '-', __CLASS__ ) ) . '-preview';

		wp_enqueue_script( 
			$handle, 
			get_theme_file_uri( '/assets/js/customizer/preview.js' ), 
			[ 'jquery','customize-preview' ], 
			'3.5', 
			true 
		);
	}

	/**
	 * Return default values for the Customize Configuration.
	 *
	 * @since 	3.5
	 * @return 	array default values for the Customizer Configurations.
	 */
	private function get_config_defaults() {
		return apply_filters(
			'wecodeart/filter/customizer/config/defaults',
			array(
				'priority'             => null,
				'title'                => null,
				'label'                => null,
				'name'                 => null,
				'type'                 => null,
				'description'          => null,
				'capability'           => null,
				'datastore_type'       => 'theme_mod', // theme_mod or option. Default theme_mod.
				'settings'             => null,
				'active_callback'      => null,
				'sanitize_callback'    => null,
				'sanitize_js_callback' => null,
				'theme_supports'       => null,
				'transport'            => null,
				'default'              => null,
				'selector'             => null,
			)
		);
	}

	/**
	 * Filter and return Customizer Configurations.
	 *
	 * @param 	WP_Customize_Manager $wp_customize Reference to WP_Customize_Manager.
	 * @since 	3.5
	 * @return 	Array Customizer Configurations for registering Sections/Panels/Controls.
	 */
	private function get_configurations( $wp_customize ) {
		if ( ! is_null( self::$configurations ) ) return self::$configurations;
		return apply_filters( 'wecodeart/filter/customizer/configurations', [], $wp_customize );
	}

	/**
	 * Grab our Customizer Defaults.
	 *
	 * @param 	string 		mod_name
	 * @version	3.7.9
	 * @return 	mixed/array
	 */
	public static function get_defaults( $mod_name = '' ) {
		$defaults = array(
			'header-bar-container'			=> 'container',
			'header-bar-modules'			=> ['branding', 'menu', 'search'],
			// Default container/modules used if no other condition matches
			'content-layout-container'		=> 'container',
			'content-layout-modules'		=> ['content', 'primary'],
			// Special Blog page container/modules mod
			'content-layout-container-blog'	=> 'container',
			'content-layout-modules-blog'	=> ['content', 'primary'],  
			'footer-layout-container'		=> 'container',
			'footer-layout-modules'			=> ['footer-1', 'footer-2', 'footer-3' ],
			'footer-copyright-text'			=> sprintf( __( 'Copyright %s - All rights reserved.', 'wecodeart' ), '&copy; ' . date( 'Y' ) ),
		);

		/**
		 * Added post type defaults for Entry Meta and Container/Modules (singular/archive page types)
		 * @since 3.6.0.3
		 */
		// Customizer defaults for Post Types
		$get_post_types = get_post_types( [ 'public' => true, 'publicly_queryable' => true ] );
		foreach( $get_post_types as $type ) { 
			// Skip the WOO CPT
			if( $type === 'product' ) continue;
			// Entry Meta
			if( post_type_supports( $type, 'wecodeart-post-info' ) ) {
				$defaults['content-entry-meta-' . $type . '-archive'] 	= [ 'author', 'date', 'comments' ];
				$defaults['content-entry-meta-' . $type . '-singular'] 	= [ 'author', 'date', 'comments' ];
			}
			// Custom Container and Modules
			$defaults['content-layout-container-' . $type . '-archive']		= 'container';
			$defaults['content-layout-modules-' . $type . '-archive'] 		= [ 'content', 'primary' ];
			$defaults['content-layout-container-' . $type . '-singular'] 	= 'container';
			$defaults['content-layout-modules-' . $type . '-singular'] 		= [ 'content', 'primary' ];
		}
		
		$args = apply_filters( 'wecodeart/filter/customizer/defaults', $defaults );
		
		// Allows to return a single mod_name default value
		if( $mod_name && array_key_exists( $mod_name, $args ) ) return $args[$mod_name];
		else return $args;
	}

	/**
	 * Adds a value to each setting if one isn't already present.
	 *
	 * @uses get_defaults()
	 */
	public function set_defaults() {
		foreach( self::get_defaults() as $mod => $val ) {
			add_filter( 'theme_mod_' . $mod, [ $this, 'get_theme_mod_value' ], 10 );
		}
	}

	/**
	 * Apply Customizer setting defaults.
	 *
	 * @param  object $wp_customize the Customizer object.
	 * @uses   get_defaults()
	 */
	public function apply_defaults( $wp_customize ) {
		foreach( self::get_defaults() as $mod => $val ) $wp_customize->get_setting( $mod )->default = $val; 
	}
	
	/**
	 * Get theme mod value.
	 *
	 * @param 	string $value
	 * @return 	string
	 */
	public function get_theme_mod_value( $value ) {
		// Remove theme_mod_ prefix to find setting name
		$key = substr( current_filter(), 10 );
		$set_theme_mods = get_theme_mods();
		// If a value is set, return it early;
		if ( isset( $set_theme_mods[ $key ] ) ) return $value;
		// If not return the default
		$values = $this->get_defaults(); 
		return isset( $values[ $key ] ) ? $values[ $key ] : $value;
	}

	/**
	 * Get theme Mods
	 *
	 * @since 	3.5
	 * @uses 	get_theme_mods()
	 * @uses 	self::get_defaults()
	 */
	public function get_theme_mods() {
		return wp_parse_args( get_theme_mods(), self::get_defaults() );
	}

	/**
	 * Process and Register Customizer Panels, Sections, Settings and Controls.
	 *
	 * @param 	WP_Customize_Manager $wp_customize Reference to WP_Customize_Manager.
	 * @since 	3.5
	 *
	 * @return 	void
	 */
	public function register_settings( $wp_customize ) {
		$configurations = $this->get_configurations( $wp_customize );

		foreach ( $configurations as $key => $config ) {
			$config = wp_parse_args( $config, $this->get_config_defaults() );

			switch ( $config['type'] ) {
				case 'panel': 
					unset( $config['type'] );
					$this->register_panel( $config, $wp_customize );
					break;
				case 'section': 
					unset( $config['type'] );
					$this->register_section( $config, $wp_customize );
					break;
				case 'control': 
					unset( $config['type'] );
					$this->register_control( $config, $wp_customize );
					break;
			}
		}
	}
	
	/**
	 * Register Customizer Panel.
	 *
	 * @param 	Array                $config Panel Configuration settings.
	 * @param 	WP_Customize_Manager $wp_customize instance of WP_Customize_Manager.
	 * @since 	3.5
	 *
	 * @return 	void
	 */
	private function register_panel( $config, $wp_customize ) {
		$wp_customize->add_panel( 
			new Customizer\Extender\Panel( $wp_customize, Helpers::get_prop( $config, 'name' ), $config ) 
		);
	}

	/**
	 * Register Customizer Section.
	 *
	 * @param 	Array                $config Panel Configuration settings.
	 * @param 	WP_Customize_Manager $wp_customize instance of WP_Customize_Manager.
	 * @since 	3.5
	 *
	 * @return 	void
	 */
	private function register_section( $config, $wp_customize ) {
		$callback = Helpers::get_prop( $config, 'section_callback', 'WeCodeArt\Customizer\Extender\Section' );
		$wp_customize->add_section( new $callback( $wp_customize, Helpers::get_prop( $config, 'name' ), $config ) );
	}
	
	/**
	 * Register Customizer Control and Setting.
	 *
	 * @param 	Array                $config Panel Configuration settings.
	 * @param 	WP_Customize_Manager $wp_customize instance of WP_Customize_Manager.
	 * @since 	3.5
	 *
	 * @return 	void
	 */
	private function register_control( $config, $wp_customize ) {
		$wp_customize->add_setting(
			Helpers::get_prop( $config, 'name' ),
			[
				// Default of the 'default' is null if not exists since we apply it with wp filter after theme setup
				'default'			=> Helpers::get_prop( $config, 'default', false ),
				'type'              => Helpers::get_prop( $config, 'datastore_type' ),
				'transport'         => Helpers::get_prop( $config, 'transport', 'refresh' ),
				'sanitize_callback' => Helpers::get_prop( $config, 'sanitize_callback', 
					// Set Sanitize Callback Automatically
					Customizer\Controls::get_sanitize_call( Helpers::get_prop( $config, 'control' ) ) 
				),
			]
		);

		$instance = Customizer\Controls::get_control_instance( Helpers::get_prop( $config, 'control' ) );

		$config['label'] = Helpers::get_prop( $config, 'title' );
		$config['type']  = Helpers::get_prop( $config, 'control' ); 

		/**
		 * Register a new custom control instance or wp default
		 */
		if ( false !== $instance ) {
			$wp_customize->add_control(
				new $instance( $wp_customize, Helpers::get_prop( $config, 'name' ), $config )
			);
		} else {
			$wp_customize->add_control( Helpers::get_prop( $config, 'name' ), $config );
		}

		/**
		 * Add support to define partial inside our custom config
		 */
		if ( Helpers::get_prop( $config, 'partial', false ) ) {
			if ( isset( $wp_customize->selective_refresh ) ) {
				$wp_customize->selective_refresh->add_partial( Helpers::get_prop( $config, 'name' ), [
					'selector'            => Helpers::get_prop( $config['partial'], 'selector' ),
					'container_inclusive' => Helpers::get_prop( $config['partial'], 'container_inclusive' ),
					'render_callback'     => Helpers::get_prop( $config['partial'], 'render_callback' ),
				] );
			}
		}
	}
}