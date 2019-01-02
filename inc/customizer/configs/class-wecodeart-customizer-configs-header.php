<?php namespace WeCodeArt\Customizer\Configs;
// No direct access, please.
if ( ! defined( 'ABSPATH' ) ) exit;
// Use
use WeCodeArt\Customizer\Config as Config;
use WeCodeArt\Customizer\Formatting as Formatting;

/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	WP-Customizer Config
 * @copyright   Copyright (c) 2019, WeCodeArt Framework
 * @since 		v3.5
 * @version		v3.5
 */

/**
 * Customizer Config initial setup
 */
class Header extends Config {
	/**
	 * Register Site Layout Customizer Configurations.
	 * @param 	Array                $configurations 
	 * @param 	WP_Customize_Manager $wp_customize instance of WP_Customize_Manager.
	 * @since 	3.5
	 * @return 	Array 
	 */
	public function register( $configurations, $wp_customize ) {
		// A handy class for formatting theme mods.
		$formatting = Formatting::get_instance();
		// Header Modules Choices
		$h_modules = array();
		$modules = \WeCodeArt\Core\Header::nav_bar_modules();
		foreach( $modules as $key => $val ) $h_modules[$key] = $val['label']; 

		$_configs = array( 
			array(
				'name'			=> 'header-bar-container',
				'type' 			=> 'control',
				'control'  		=> 'select',
				'section'		=> 'header-bar',
				'title' 		=> __( 'Grid Type', 'wecodeart' ),
				'description' 	=> __( 'Choose the width of .grid-container class.', 'wecodeart' ),
				'choices'  		=> array(
					'grid-container' 		=> __( 'Grid', 'wecodeart' ),
					'grid-container fluid' 	=> __( 'Fluid Grid', 'wecodeart' ),
				), 
				'priority' 		=> 5, 
				'sanitize_callback'    => [ $formatting, 'sanitize_choices' ], 
				'transport' 		   => 'postMessage'
			),
			array(
				'name'			=> 'header-bar-modules',
				'type'        	=> 'control',
				'control'  		=> 'wecodeart-sortable',
				'section'		=> 'header-bar',
				'title'        	=> __( 'Header Bar Modules', 'wecodeart' ),
				'description'	=> __( 'Enable and reorder Header Bar modules.', 'wecodeart' ),
				'priority'   	=> 10, 
				'choices'		=> $h_modules,
				'transport'		=> 'postMessage',
				'partial'		=> [
					'selector'        		=> '.header__bar.header-bar',
					'render_callback' 		=> [ 'WeCodeArt\Core\Header', 'render_header_bar' ],
					'container_inclusive' 	=> true
				]
			)
		);

		return array_merge( $configurations, $_configs );
	}
}
