<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Conditional
 * @copyright   Copyright (c) 2019, WeCodeArt Framework
 * @since 		4.0
 * @version		4.0.1
 */

namespace WeCodeArt;

defined( 'ABSPATH' ) || exit();

use ArrayAccess;

/**
 * Conditional.
 *
 * @author     Bican Marian Valeriu <marianvaleriubican@gmail.com>
 */
class Conditional implements ArrayAccess {
    /**
     * All of the conditional items.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Create a new conditional repository.
     *
     * @param  array  $items
     *
     * @return void
     */
    public function __construct( array $items = [] ) {
        $this->items = $items;
    }

	/**
     * Get all conditionals array.
     *
     * @return array The conditionals classes.
     */
    public static function get_conditionals() {
        $conditionals = apply_filters( 'wecodeart/conditional/get/before', [
			'is_admin'          => Conditional\Admin::class,
            'is_logged'         => Conditional\Logged_In::class,
            'is_dev_mode'       => Conditional\Dev_Mode::class,
			'is_front_page'     => Conditional\Front_Page::class,
			'is_full_layout'    => Conditional\Full_Layout::class,
			'is_post_archive'   => Conditional\Post_Archive::class,
		] );

        return (array) $conditionals;
	}
	
    /**
     * Determine if the given conditional value exists.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function has( $key ) {
        return isset( $this->items[$key] );
    }

    /**
     * Get the specified conditional value.
     *
     * @param  string  $key
     * @param  mixed   $default
     *
     * @return mixed
     */
    public function get( $key, $default = null ) {
        if ( ! isset( $this->items[$key] ) ) {
            return $default;
        }

        return apply_filters( "wecodeart/conditional/get/{$key}", $this->items[$key] );
    }

    /**
     * Set a given conditional value.
     *
     * @param  array|string  $key
     * @param  mixed   $value
     *
     * @return void
     */
    public function set( $key, $value = null ) {
        $keys = is_array( $key ) ? $key : [ $key => $value ];

        foreach ( $keys as $key => $value ) {
            $this->items[$key] = apply_filters( "wecodeart/conditional/set/{$key}", $value );
        }
    }

    /**
     * Get all of the conditional items for the application.
     *
     * @return array
     */
    public function all() {
        return $this->items;
    }

    /**
     * Determine if the given conditional option exists.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function offsetExists( $key ) {
        return $this->has( $key );
    }

    /**
     * Get a conditional option.
     *
     * @param  string  $key
     *
     * @return mixed
     */
    public function offsetGet( $key ) {
        return $this->get( $key );
    }

    /**
     * Set a conditional option.
     *
     * @param  string  $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function offsetSet( $key, $value ) {
        $this->set( $key, $value );
    }

    /**
     * Unset a conditional option.
     *
     * @param  string  $key
     *
     * @return void
     */
    public function offsetUnset( $key ) {
        $this->set( $key, null );
    }
}