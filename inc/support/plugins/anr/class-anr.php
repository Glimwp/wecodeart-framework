<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Support\ANR Captcha
 * @copyright   Copyright (c) 2022, WeCodeArt Framework
 * @since 		3.8.1
 * @version		5.7.1
 */

namespace WeCodeArt\Support\Plugins;

defined( 'ABSPATH' ) || exit;

use C4wp_Captcha_Class as Captcha;
use WeCodeArt\Singleton;
use WeCodeArt\Integration;
use WeCodeArt\Admin\Notifications;
use WeCodeArt\Admin\Notifications\Notification;

/**
 * ANR Integration
 * @see https://wordpress.org/plugins/advanced-nocaptcha-recaptcha/
 */
class ANR implements Integration {

	use Singleton;

	const NOTICE_ID = 'wecodeart-anr-notice';

	/**
	 * Send to Constructor
	 */
	public function init() {}

	/**
	 * Get Conditionals
	 *
	 * @return void
	 */
	public static function get_conditionals() {
		wecodeart( 'conditionals' )->set( [
			'is_anr_active'		=> ANR\Condition::class,
		] );

		return [ 'is_anr_active' ];
	}

	/**
	 * Hooks
	 *
	 * @since   3.8.1
	 * @version	5.7.1
	 *
	 * @return  void
	 */
	public function register_hooks() {
		add_action( 'admin_notices',	[ $this, 'manage_notice' ] );

		// Comments
		if( c4wp_is_form_enabled( 'comment' ) && ( ! is_admin() || ! current_user_can( 'moderate_comments' ) ) ) {
			add_action( 'init', function() {
				$instance = Captcha::init();
				if ( ! is_user_logged_in() ) {
					remove_action( 'comment_form_after_fields', 	[ $instance, 'form_field' ], 99 );
					add_action( 'comment_form_after_fields', 		[ $this, 'comment_field' ] );
				} else {
					remove_filter( 'comment_form_field_comment', 	[ $instance, 'form_field_return' ], 99 );
					add_filter( 'comment_form_field_comment', 		[ $this, 'comment_field_return' ] );
				}
			} );
		}

		// Hooks
		do_action( 'wecodeart/action/support/anr/init' );
	}

	/**
	 * Manage Notice
	 *
	 * @since 	5.0.0
	 * @version	5.4.8
	 */
	public function manage_notice() {
		$notification = new Notification(
			esc_html__( 'Captcha 4WP support is enabled!', 'wecodeart' ),
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
	 * Comment Captcha
	 *
	 * @since   3.8.1
	 * @version	4.1.6
	 * @uses	$this->comment_field_return();
	 *
	 * @return  void
	 */
	public function comment_field() {
		echo $this->comment_field_return(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Comment Captcha
	 *
	 * @since   3.8.1
	 * @version	5.4.8
	 * @see 	C4wp_Captcha_Class::init()->form_field();
	 * @uses	C4wp_Captcha_Class::init()->form_field();
	 *
	 * @return  void
	 */
	public function comment_field_return( $defaults = '' ) {
		$hide	= c4wp_get_option( 'loggedin_hide' );
		$ver	= c4wp_get_option( 'captcha_version', 'v2_checkbox' );

		if ( is_user_logged_in() && $hide === (bool) true ) {
			return $defaults;
		}

		$defaults .= wecodeart( 'markup' )::wrap( 'comment-captcha', [ [ 
			'tag' 	=> 'div', 
			'attrs' => [
				'class' => 'comment-form-field comment-form-captcha',
				'style' => $ver === 'v3' ? 'display:none;' : null,
			]
		] ], [ Captcha::init(), 'form_field' ], [], false );

		return $defaults;
	}
}