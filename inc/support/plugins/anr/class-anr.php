<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Support\ANR Captcha
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since 		3.8.1
 * @version		4.2.0
 */

namespace WeCodeArt\Support\Plugins;

defined( 'ABSPATH' ) || exit;

use anr_captcha_class as Captcha;
use WeCodeArt\Markup;
use WeCodeArt\Singleton;
use WeCodeArt\Integration;

/**
 * ANR Integration
 * @see https://wordpress.org/plugins/advanced-nocaptcha-recaptcha/
 */
class ANR implements Integration {

	use Singleton;

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
	 * @version	4.1.6
	 *
	 * @return  void
	 */
	public function register_hooks() {
		// Comments
		if( anr_is_form_enabled( 'comment' ) && ( ! is_admin() || ! current_user_can( 'moderate_comments' ) ) ) {
			add_action( 'init', function() {
				$anr_instance = Captcha::init();
				if ( ! is_user_logged_in() ) {
					remove_action( 'comment_form_after_fields', 	[ $anr_instance, 'form_field' ], 99 );
					add_action( 'comment_form_after_fields', 		[ $this, 'comment_field' ] );
				} else {
					remove_filter( 'comment_form_field_comment', 	[ $anr_instance, 'comment_form_field' ], 99 );
					add_filter( 'comment_form_field_comment', 		[ $this, 'comment_field_return' ] );
				}
			} );
		}

		// Hooks
		do_action( 'wecodeart/action/support/anr/init' );
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
	 * @version	4.2.0
	 * @see 	anr_captcha_class::init()->form_field_return();
	 * @uses	anr_captcha_class::init()->form_field_return();
	 *
	 * @return  void
	 */
	public function comment_field_return( $defaults = '' ) {
		$hide	= anr_get_option( 'loggedin_hide' );
		$ver	= anr_get_option( 'captcha_version', 'v2_checkbox' );

		if ( is_user_logged_in() && $hide === (bool) true ) {
			return $defaults;
		}

		$classes = [ 'mb-3', 'comment-form-captcha', 'col-12', 'col-md-7', $ver === 'v3' ? 'd-none' : '' ];

		$defaults .= Markup::wrap( 'comment-captcha', [ [ 
			'tag' 	=> 'div', 
			'attrs' => [
				'class' => implode( ' ', array_filter( $classes ) )
			]
		] ], function() { ?>
			<label for="g-recaptcha-response"><?php esc_html_e( 'Captcha *', 'wecodeart' ); ?></label>
			<?php

			echo Captcha::init()->form_field_return(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		
		}, [], false );

		return $defaults;
	}
}