<?php namespace WeCodeArt\Support\WooCommerce\Customizer;
// No direct access, please.
if ( ! defined( 'ABSPATH' ) ) exit;
// Use
use WeCodeArt\Customizer\Config as Config;
use WeCodeArt\Customizer\Formatting as Formatting;
use WeCodeArt\Support\WooCommerce\Callbacks;
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Support\WooCommerce\Customizer
 * @copyright   Copyright (c) 2019, WeCodeArt Framework
 * @since 		v3.5
 * @version		v3.6.0.3
 */
/**
 * Customizer Config initial setup
 */
class Content extends Config {
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
		$callbacks	= Callbacks::get_instance();
		// Content Modules Choices
		$c_modules = array();
		$inner_modules = \WeCodeArt\Core\Content::content_modules();
		foreach( $inner_modules as $key => $val ) $c_modules[$key] = $val['label']; 

		$_configs = array( 
			array(
				'name'			=> 'content-layout-container-product-archive',
				'type' 			=> 'control',
				'control'  		=> 'select',
				'section'		=> 'content-layout',
				'title' 		=> __( 'Grid Type: Product Archive', 'wecodeart' ),
				'description' 	=> __( 'Choose the type of .container class.', 'wecodeart' ),
				'choices'  		=> array(
					'container' 		=> __( 'Container', 'wecodeart' ),
					'container-fluid' 	=> __( 'Container Fluid', 'wecodeart' ),
				), 
				'priority' 		=> 15, 
				'sanitize_callback'    => [ $formatting, 'sanitize_choices' ],
				'transport' 		   => 'postMessage',
				'active_callback'	   => [ $callbacks, '_is_woocommerce_archive' ],
				'transport' 		   => 'postMessage' 
			),
			array(
				'name'			=> 'content-layout-modules-product-archive',
				'type'        	=> 'control',
				'control'  		=> 'wecodeart-sortable',
				'section'		=> 'content-layout',
				'title'			=> __( 'Content Modules: Product Archive', 'wecodeart' ),
				'description'	=> __( 'Enable and reorder Site Inner modules.', 'wecodeart' ),
				'priority'		=> 20, 
				'choices'		=> $c_modules,
				'active_callback' => [ $callbacks, '_is_woocommerce_archive' ],
				'transport' 		   => 'postMessage',
				'partial'		=> [
					'selector'        => '.content-area',
					'render_callback' => [ 'WeCodeArt\Support\WooCommerce\Customizer', 'render_content' ],
					'container_inclusive' => true
				]
			),
			array(
				'name'			=> 'content-layout-container-product-singular',
				'type' 			=> 'control',
				'control'  		=> 'select',
				'section'		=> 'content-layout',
				'title' 		=> __( 'Grid Type: Product Single', 'wecodeart' ),
				'description' 	=> __( 'Choose the type of .container class.', 'wecodeart' ),
				'choices'  		=> array(
					'container' 		=> __( 'Container', 'wecodeart' ),
					'container-fluid' 	=> __( 'Container Fluid', 'wecodeart' ),
				), 
				'priority' 		=> 15, 
				'sanitize_callback'    => [ $formatting, 'sanitize_choices' ],
				'transport' 		   => 'postMessage',
				'active_callback'	   => 'is_product',
				'transport' 		   => 'postMessage' 
			),
			array(
				'name'			=> 'content-layout-modules-product-singular',
				'type'        	=> 'control',
				'control'  		=> 'wecodeart-sortable',
				'section'		=> 'content-layout',
				'title'			=> __( 'Content Modules: Product Single', 'wecodeart' ),
				'description'	=> __( 'Enable and reorder Site Inner modules.', 'wecodeart' ),
				'priority'		=> 20, 
				'choices'		=> $c_modules,
				'active_callback' => 'is_product',
				'transport'		=> 'postMessage',
				'partial'		=> [
					'selector'        => '.content-area',
					'render_callback' => [ 'WeCodeArt\Support\WooCommerce\Customizer', 'render_content' ],
					'container_inclusive' => true
				]
			)
		);

		$configurations = array_merge( $configurations, $_configs );

		return $configurations;
	}
}
