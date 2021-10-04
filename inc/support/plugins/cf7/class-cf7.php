<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Support\CF7
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since 		5.0.0
 * @version		5.0.6
 */

namespace WeCodeArt\Support\Plugins;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Markup;
use WeCodeArt\Singleton;
use WeCodeArt\Integration;
use WeCodeArt\Core\Scripts;
use WeCodeArt\Support\Plugins\CF7\Modules;
use WeCodeArt\Admin\Notifications;
use WeCodeArt\Admin\Notifications\Notification;

/**
 * CF7 Integration
 * @see https://wordpress.org/plugins/contact-form-7/
 */
class CF7 implements Integration {

	use Singleton;
	use Scripts\Base;

	const NOTICE_ID = 'wecodeart-cf7-notice';

	/**
	 * Get Conditionals
	 *
	 * @return void
	 */
	public static function get_conditionals() {
		wecodeart( 'conditionals' )->set( [
			'is_cf7_active'	=> CF7\Condition::class,
		] );

		return [ 'is_cf7_active' ];
	}

	/**
	 * Hooks
	 *
	 * @since   5.0.0
	 * @version	5.0.0
	 *
	 * @return  void
	 */
	public function register_hooks() {
		add_action( 'admin_notices',				[ $this, 'manage_notice' ] );
		add_action( 'wp_enqueue_scripts', 			[ $this, 'enqueue' ], 50 );

		add_filter( 'wpcf7_load_css', 				'__return_false' );
		add_filter( 'wpcf7_form_response_output', 	[ $this, 'form_response_output' ], 10, 3 );
		add_filter( 'wpcf7_form_class_attr', 		[ $this, 'form_class_output' 	], 10, 1 );

		// Load Modules
		Modules::get_instance();
	}

	/**
	 * Manage Notice
	 *
	 * @since 	5.0.0
	 * @version	5.0.0
	 */
	public function manage_notice() {
		$notification = new Notification(
			esc_html__( 'Contact Form 7 support is enabled. Enjoy properly styled forms based on your theme!', 'wecodeart' ),
			[
				'id'			=> self::NOTICE_ID,
				'type'     		=> Notification::INFO,
				'priority' 		=> 1,
				'class'			=> 'notice is-dismissible',
				'capabilities' 	=> 'activate_plugins',
			]
		);
		
		if( get_user_option( self::NOTICE_ID ) === 'seen' ) {
			Notifications::get_instance()->remove_notification( $notification );
			set_transient( self::NOTICE_ID, true, WEEK_IN_SECONDS );
			return;
		}

		if( get_transient( self::NOTICE_ID ) === false ) {
			Notifications::get_instance()->add_notification( $notification );
		}
	}

	/**
	 * Enqueue Scripts.
	 *
	 * @since 	5.0.0
	 * @version	5.0.6
	 */
	public function enqueue() {
		wp_enqueue_script(
			$this->make_handle(),
			$this->get_asset( 'js', 'plugins/cf7' ),
			[ 'wecodeart-core-scripts', 'contact-form-7' ],
			wecodeart( 'version' ),
			true
		);
	}

	/**
	 * Filter CF7 Submission output
	 *
	 * @since   5.0.0
	 * @version	5.0.0
	 *
	 * @param	string	$output 
	 * @param	string	$class 
	 * @param	string	$content 
	 *
	 * @return  string	$output
	 */
	public function form_response_output( $output, $class, $content ) {
		if( $content ) {
			$output = str_replace( 'wpcf7-response-output', 'wpcf7-response-output alert alert-warning my-3', $output );
		}

		return $output;
	}
	
	/**
	 * Filter CF7 Form class
	 *
	 * @since   5.0.0
	 * @version	5.0.0
	 *
	 * @param	string	$class
	 *
	 * @return  string	$class
	 */
	public function form_class_output( $class ) {
		return str_replace( ' invalid', ' invalid was-validated', $class );
	}
}