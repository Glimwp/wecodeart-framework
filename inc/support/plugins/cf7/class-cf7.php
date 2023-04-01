<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Support\CF7
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since 		5.0.0
 * @version		6.0.0
 */

namespace WeCodeArt\Support\Plugins;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Singleton;
use WeCodeArt\Integration;
use WeCodeArt\Admin\Notifications;
use WeCodeArt\Admin\Notifications\Notification;

/**
 * CF7 Integration
 * @see https://wordpress.org/plugins/contact-form-7/
 */
class CF7 implements Integration {

	use Singleton;

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
	 * @version	5.4.5
	 *
	 * @return  void
	 */
	public function register_hooks() {
		add_action( 'admin_notices', [ $this, 'manage_notice' ] );
	}

	/**
	 * Manage Notice
	 *
	 * @since 	5.0.0
	 * @version	5.0.0
	 */
	public function manage_notice() {
		$notification = new Notification(
			sprintf( '<h3 style="margin: .5em 0;">%s</h3>', esc_html__( 'Amazing news!', 'wecodeart' ) ) .
			sprintf(
				esc_html__( 'We have an extension that integrates our theme with Contact Form 7 plugin! You can get it (soon) from %s.', 'wecodeart' ),
				sprintf( '<a href="https://www.wecodeart.com/shop/" target="_blank">%s</a>', esc_html__( 'here', 'wecodeart' ) )
			),
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
}