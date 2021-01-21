<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Notifications\Notification
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since 		4.2
 * @version		4.2
 */

namespace WeCodeArt\Admin\Notifications;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Markup;
use WeCodeArt\Singleton;

/**
 * Notification
 *
 * @since 4.2
 */
class Notification {

	use Singleton;

	/**
	 * Type of capability check.
	 *
	 * @var string
	 */
	const MATCH_ALL = 'all';

	/**
	 * Type of capability check.
	 *
	 * @var string
	 */
	const MATCH_ANY = 'any';

	/**
	 * Notification type.
	 *
	 * @var string
	 */
	const ERROR = 'error';

	/**
	 * Notification type.
	 *
	 * @var string
	 */
	const WARNING = 'warning';

	/**
	 * Notification type.
	 *
	 * @var string
	 */
	const UPDATED = 'updated';

	/**
	 * Options of this Notification.
	 *
	 * Contains optional arguments:
	 *
	 * -             type: The notification type, i.e. 'updated' or 'error'
	 * -               id: The ID of the notification
	 * -            nonce: Security nonce to use in case of dismissible notice.
	 * -         priority: From 0 to 1, determines the order of Notifications.
	 * -    dismissal_key: Option name to save dismissal information in, ID will be used if not supplied.
	 * -     capabilities: Capabilities that a user must have for this Notification to show.
	 * - capability_check: How to check capability pass: all or any.
	 *
	 * @var array
	 */
	private $options = [];

	/**
	 * Contains default values for the optional arguments.
	 *
	 * @var array
	 */
	private $defaults = [
		'type'             => self::UPDATED,
		'id'               => '',
		'user'             => null,
		'nonce'            => null,
		'priority'         => 0.5,
		'data_json'        => [],
		'dismissal_key'    => null,
		'capabilities'     => [],
		'capability_check' => self::MATCH_ALL,
	];

	/**
	 * The message for the notification.
	 *
	 * @var string
	 */
	private $message;

	/**
	 * Notification class constructor.
	 *
	 * @param string $message Message string.
	 * @param array  $options Set of options.
	 */
	public function __construct( $message, $options = [] ) {
		$this->message = $message;
		$this->options = $this->normalize_options( $options );
	}

	/**
	 * Retrieve notification ID string.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->options['id'];
	}

	/**
	 * Notice classes.
	 *
	 * @since 4.0
	 *
	 * @param  array 	$notice Notice arguments.
	 * @return array
	 */
	public function get_classes() {
		$classes   = [ 'wca-notice', 'is-dismissible' ];
		$classes[] = $this->options['class'];

		if ( isset( $this->options['type'] ) && '' !== $this->options['type'] ) {
			$classes[] = 'notice-' . $this->options['type'];
		}

		// Maintain WordPress visualisation of alerts when they are not persistent.
		if ( ! $this->is_persistent() ) {
			$classes[] = 'notice';
			$classes[] = $this->get_type();
		}

		return esc_attr( implode( ' ', $classes ) );
	}

	/**
	 * Retrieve the user to show the notification for.
	 *
	 * @return WP_User The user to show this notification for.
	 */
	public function get_user() {
		return $this->options['user'];
	}

	/**
	 * Retrieve the is of the user to show the notification for.
	 *
	 * Returns the id of the current user if not user has been sent.
	 *
	 * @return integer The user id
	 */
	public function get_user_id() {
		if ( $this->get_user() !== null ) {
			return $this->get_user()->ID;
		}
		return get_current_user_id();
	}

	/**
	 * Retrieve nonce identifier.
	 *
	 * @return null|string Nonce for this Notification.
	 */
	public function get_nonce() {
		if ( $this->options['id'] && empty( $this->options['nonce'] ) ) {
			$this->options['nonce'] = wp_create_nonce( $this->options['id'] );
		}

		return $this->options['nonce'];
	}

	/**
	 * Make sure the nonce is up to date.
	 */
	public function refresh_nonce() {
		if ( $this->options['id'] ) {
			$this->options['nonce'] = wp_create_nonce( $this->options['id'] );
		}
	}

	/**
	 * Get the type of the notification.
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->options['type'];
	}

	/**
	 * Priority of the notification.
	 *
	 * Relative to the type.
	 *
	 * @return float Returns the priority between 0 and 1.
	 */
	public function get_priority() {
		return $this->options['priority'];
	}

	/**
	 * Get the User Meta key to check for dismissal of notification.
	 *
	 * @return string User Meta Option key that registers dismissal.
	 */
	public function get_dismissal_key() {
		if ( empty( $this->options['dismissal_key'] ) ) {
			return $this->options['id'];
		}

		return $this->options['dismissal_key'];
	}

	/**
	 * Is this Notification persistent.
	 *
	 * @return bool True if persistent, False if fire and forget.
	 */
	public function is_persistent() {
		$id = $this->get_id();

		return ! empty( $id );
	}

	/**
	 * Check if the notification is relevant for the current user.
	 *
	 * @return bool True if a user needs to see this notification, false if not.
	 */
	public function display_for_current_user() {
		// If the notification is for the current page only, always show.
		if ( ! $this->is_persistent() ) {
			return true;
		}

		// If the current user doesn't match capabilities.
		return $this->match_capabilities();
	}

	/**
	 * Does the current user match required capabilities.
	 *
	 * @return bool
	 */
	public function match_capabilities() {
		// Super Admin can do anything.
		if ( is_multisite() && is_super_admin( $this->options['user']->ID ) ) {
			return true;
		}

		/**
		 * Filter capabilities that enable the displaying of this notification.
		 */
		$capabilities = apply_filters( 'wecodeart/admin/notifications/capabilities', $this->options['capabilities'], $this );

		// Should be an array.
		if ( ! is_array( $capabilities ) ) {
			$capabilities = (array) $capabilities;
		}

		/**
		 * Filter capability check to enable all or any capabilities.
		 */
		$check = apply_filters( 'wecodeart/admin/notifications/capabilities/check', $this->options['capability_check'], $this );

		if ( ! in_array( $check, [ self::MATCH_ALL, self::MATCH_ANY ], true ) ) {
			$check = self::MATCH_ALL;
		}

		if ( ! empty( $capabilities ) ) {

			$has_capabilities = array_filter( $capabilities, [ $this, 'has_capability' ] );

			switch ( $check ) {
				case self::MATCH_ALL:
					return $has_capabilities === $capabilities;
				case self::MATCH_ANY:
					return ! empty( $has_capabilities );
			}
		}

		return true;
	}

	/**
	 * Array filter function to find matched capabilities.
	 *
	 * @param string $capability Capability to test.
	 *
	 * @return bool
	 */
	private function has_capability( $capability ) {
		$user = $this->options['user'];

		$role_caps = $user->get_role_caps();
		if ( array_key_exists( $capability, $role_caps ) ) {
			return $role_caps[ $capability ];
		}
		return false;
	}

	/**
	 * Return the object properties as an array.
	 *
	 * @return array
	 */
	public function to_array() {
		return [
			'message' => $this->message,
			'options' => $this->options,
		];
	}

	/**
	 * Adds string (view) behaviour to the notification.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->render();
	}

	/**
	 * Renders the notification as a string.
	 *
	 * @return string The rendered notification.
	 */
	public function render() {
		$attributes = [];
		$attributes['class'] = $this->get_classes();

		$message = null;

		if ( $message === null ) {
			$message = wpautop( $this->message );
        }

	    return Markup::template( 'admin/notification', [
            'attributes' => $attributes,
            'message'    => $message
        ], false );
	}

	/**
	 * Get the JSON if provided.
	 *
	 * @return false|string
	 */
	public function get_json() {
		if ( empty( $this->options['data_json'] ) ) {
			return '';
		}

		return wp_json_encode( $this->options['data_json'] );
	}

	/**
	 * Make sure we only have values that we can work with.
	 *
	 * @param array $options Options to normalize.
	 *
	 * @return array
	 */
	private function normalize_options( $options ) {
		$options = wp_parse_args( $options, $this->defaults );

		// Should not exceed 0 or 1.
		$options['priority'] = min( 1, max( 0, $options['priority'] ) );

		// Set default capabilities when not supplied.
		if ( empty( $options['capabilities'] ) || $options['capabilities'] === [] ) {
			$options['capabilities'] = [];
		}

		// Set to the current user if not supplied.
		if ( $options['user'] === null ) {
			$options['user'] = wp_get_current_user();
		}

		return $options;
	}
}