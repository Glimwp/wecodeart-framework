<?php namespace WeCodeArt\Customizer\Configs;
// No direct access, please.
if ( ! defined( 'ABSPATH' ) ) exit;
// Use
use WeCodeArt\Customizer\Config as Config;
use WeCodeArt\Customizer\Formatting as Formatting;
use WeCodeArt\Support\WooCommerce\Callbacks as WooCB;

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
		$callbacks	= WooCB::get_instance();

		// Content Modules Choices
		$c_modules = array();
		$inner_modules = \WeCodeArt\Core\Content::content_modules();
		foreach( $inner_modules as $key => $val ) $c_modules[$key] = $val['label']; 

		$_configs = array( 
			array(
				'name'			=> 'content-layout-container',
				'type' 			=> 'control',
				'control'  		=> 'select',
				'section'		=> 'content-layout',
				'title' 		=> __( 'Grid Type', 'wecodeart' ),
				'description' 	=> __( 'Choose the wrapper type.', 'wecodeart' ),
				'choices'  		=> [
					'grid-container' 		=> __( 'Grid', 'wecodeart' ),
					'grid-container fluid' 	=> __( 'Fluid Grid', 'wecodeart' )	
				],
				'priority' 		=> 5, 
				'sanitize_callback'    => [ $formatting, 'sanitize_choices' ],
				'transport' 		   => 'postMessage'
			),
			array(
				'name'			=> 'content-layout-modules',
				'type'        	=> 'control',
				'control'  		=> 'wecodeart-sortable',
				'section'		=> 'content-layout',
				'title'			=> __( 'Content Modules: Default', 'wecodeart' ),
				'description'	=> __( 'Enable and reorder Site Inner modules.', 'wecodeart' ),
				'priority'		=> 10,
				'default'		=> [ 'content', 'primary' ],
				'choices'		=> $c_modules, 
				'transport'		=> 'postMessage',
				'partial'		=> [
					'selector'        		=> '.content-area',
					'render_callback' 		=> [ 'WeCodeArt\Core\Content', 'render_modules' ],
					'container_inclusive' 	=> true
				]
			),
			array(
				'name'			=> 'content-layout-container-blog',
				'type' 			=> 'control',
				'control'  		=> 'select',
				'section'		=> 'content-layout',
				'title'			=> __( 'Grid Type: Blog Page', 'wecodeart' ) ,
				'description'	=> __( 'Enable and reorder Site Inner modules. This will affect only the page you are currently viewing.', 'wecodeart' ),
				'choices'  		=> [
					'grid-container' 		=> __( 'Grid', 'wecodeart' ),
					'grid-container fluid' 	=> __( 'Fluid Grid', 'wecodeart' )
				], 
				'priority' 		=> 10, 
				'default'		=> 'grid-container',
				'sanitize_callback'    => [ $formatting, 'sanitize_choices' ],
				'active_callback'	   => 'is_home',
				'transport' 		   => 'postMessage'
			),
			array(
				'name'			=> 'content-layout-modules-blog',
				'type'        	=> 'control',
				'control'  		=> 'wecodeart-sortable',
				'section'		=> 'content-layout',
				'title'			=> __( 'Content Modules: Blog Page', 'wecodeart' ),
				'description'	=> __( 'Enable and reorder Site Inner modules. This will affect only the page you are currently viewing.', 'wecodeart' ),
				'priority'		=> 15,
				'default'		=> [ 'content', 'primary' ],
				'choices'		=> $c_modules, 
				'transport'		=> 'postMessage',
				'active_callback' => 'is_home',
				'partial'		=> [
					'selector'        		=> '.content-area',
					'render_callback' 		=> [ 'WeCodeArt\Core\Content', 'render_modules' ],
					'container_inclusive' 	=> true
				]
			)
		);

		$configurations = array_merge( $configurations, $_configs );

		// Page specific Mods
		$pages = get_pages();
		foreach( $pages as $page ) {
			$title = $page->post_title;
			$ID = $page->ID;
			$config = array(
				array(
					'name'			=> 'content-layout-container-page-' . $ID,
					'type'        	=> 'control',
					'control'  		=> 'select',
					'section'		=> 'content-layout',
					'title' 		=> sprintf( __( 'Grid Type: %s', 'wecodeart' ), $title ),
					'description' 	=> __( 'Choose the wrapper type.', 'wecodeart' ),
					'choices'  		=> [
						'grid-container' 		=> __( 'Grid', 'wecodeart' ),
						'grid-container fluid' 	=> __( 'Fluid Grid', 'wecodeart' )
					],
					'priority'             => 20,
					'default'              => 'grid-container',
					'active_callback'	   => function() use ( $ID ) { return is_page( $ID ); },
					'transport' 		   => 'postMessage'
				),
				array(
					'name'			=> 'content-layout-modules-page-' . $ID,
					'type'        	=> 'control',
					'control'  		=> 'wecodeart-sortable',
					'section'		=> 'content-layout',
					'title'			=> sprintf( __( 'Content Modules: %s', 'wecodeart' ), $title ),
					'description'	=> __( 'Enable and reorder Site Inner modules. This will affect only the page you are currently viewing.', 'wecodeart' ),
					'priority'		=> 30,
					'default'		=> [ 'content', 'primary' ],
					'choices'		=> $c_modules,
					'active_callback'	=> function() use ( $ID ) { return is_page( $ID ); },
					'transport'		=> 'postMessage',
					'partial'		=> [
						'selector'        		=> '.content-area',
						'render_callback' 		=> [ 'WeCodeArt\Core\Content', 'render_modules' ],
						'container_inclusive'	=> true
					]
				) 	 	
			);
			$configurations = array_merge( $configurations, $config );
		}

		// Post Types Archives And Singular Context Mods 
		$public_posts = get_post_types( array( 'public' => true ) );
		unset( $public_posts['page'] );
		if( isset( $public_posts['product'] ) ) unset( $public_posts['product'] );

		foreach( $public_posts as $type ) { 
			$post_type = get_post_type_object( $type );
			$config = array(
				array(
					'name'			=> 'content-layout-container-' . $type . '-archive',
					'type'        	=> 'control',
					'control'  		=> 'select',
					'section'		=> 'content-layout',
					'title' 		=> sprintf( __( 'Grid Type: %s Archive', 'wecodeart' ), $post_type->labels->singular_name ),
					'description' 	=> __( 'Choose the wrapper type.', 'wecodeart' ),
					'choices'  		=> [
						'grid-container' 		=> __( 'Grid', 'wecodeart' ),
						'grid-container fluid' 	=> __( 'Fluid Grid', 'wecodeart' )
					],
					'priority'             => 20,
					'default'              => 'grid-container',
					'active_callback'	   => function() use ( $type ) { return is_post_type_archive( $type ); },
					'transport' 		   => 'postMessage'
				),
				array(
					'name'			=> 'content-layout-modules-' . $type . '-archive',
					'type'        	=> 'control',
					'control'  		=> 'wecodeart-sortable',
					'section'		=> 'content-layout',
					'title'			=> sprintf( __( 'Content Modules: %s Archive', 'wecodeart' ), $post_type->labels->singular_name ),
					'description'	=> __( 'Enable and reorder Site Inner modules. This will affect only the page you are currently viewing.', 'wecodeart' ),
					'priority'		=> 30,
					'default'		=> [ 'content', 'primary' ],
					'choices'		=> $c_modules,
					'active_callback'	=> function() use( $type ) { return is_post_type_archive( $type ); }, 
					'transport'		=> 'postMessage',
					'partial'		=> [
						'selector'        => '.content-area',
						'render_callback' => [ 'WeCodeArt\Core\Content', 'render_modules' ],
						'container_inclusive' => true
					]
				),
				array(
					'name'			=> 'content-layout-container-' . $type . '-singular',
					'type'        	=> 'control',
					'control'  		=> 'select',
					'section'		=> 'content-layout',
					'title' 		=> sprintf( __( 'Grid Type: %s Single', 'wecodeart' ), $post_type->labels->singular_name ),
					'description' 	=> __( 'Choose the wrapper type.', 'wecodeart' ),
					'choices'  		=> [
						'grid-container' 		=> __( 'Grid', 'wecodeart' ),
						'grid-container fluid' 	=> __( 'Fluid Grid', 'wecodeart' )
					],
					'priority'             => 25,
					'default'              => 'grid-container',
					'active_callback'	   => function() use ( $type ) { return is_singular( $type ); },
					'transport' 		   => 'postMessage'
				),
				array(
					'name'			=> 'content-layout-modules-' . $type . '-singular',
					'type'        	=> 'control',
					'control'  		=> 'wecodeart-sortable',
					'section'		=> 'content-layout',
					'title'			=> sprintf( __( 'Content Modules: %s Single', 'wecodeart' ), $post_type->labels->singular_name ),
					'description'	=> __( 'Enable and reorder Site Inner modules. This will affect only the page you are currently viewing.', 'wecodeart' ),
					'priority'		=> 35,
					'default'		=> [ 'content', 'primary' ],
					'choices'		=> $c_modules,
					'active_callback'	=> function() use( $type ) { return is_singular( $type ); }, 
					'transport'		=> 'postMessage',
					'partial'		=> [
						'selector'        		=> '.content-area',
						'render_callback' 		=> [ 'WeCodeArt\Core\Content', 'render_modules' ],
						'container_inclusive' 	=> true
					]
				)	 	
			);
			$configurations = array_merge( $configurations, $config );
		}
		
		return $configurations;
	}
}
