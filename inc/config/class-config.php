<?php
/**
 * WeCodeArt Framework Init.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework.
 * @subpackage  WeCodeArt/Config
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since		3.9.5
 * @version		5.4.5
 */

namespace WeCodeArt;

defined( 'ABSPATH' ) || exit();

use ArrayAccess;
use WeCodeArt\Config\Interfaces\Configuration;
use function WeCodeArt\Functions\wp_parse_args_r;

/**
 * Config.
 *
 * @author     Bican Marian Valeriu <marianvaleriubican@gmail.com>
 */
class Config implements Configuration, ArrayAccess {
    /**
     * All of the configuration items.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Create a new configuration repository.
     *
     * @param  array  $items
     *
     * @return void
     */
    public function __construct( array $items = [] ) {
        $this->items = $items;
    }

    /**
     * Locate and require a config file.
     *
     * First, search child theme for the config. If config file doesn't exist in the child,
     * search the parent for the config file.
     *
     * @since   3.9.5
     * @version 5.0.0
     *
     * @param   string  $config The config file to look for (not including .php file extension).
     * @return  array   The config data.
     */
    public static function get_config( $config = 'app' ) {
        $parent_file = sprintf( '%s/inc/%s.php',    get_template_directory(),   $config );
        $child_file  = sprintf( '%s/config/%s.php', get_stylesheet_directory(), $config );

        $data = [];

        if ( is_readable( $parent_file ) ) {
            $parent_req = require $parent_file;
            $data       = wp_parse_args_r( $parent_req, $data );
        }
        
        if ( is_readable( $child_file ) ) {
            $child_req  = require $child_file;
            $data       = wp_parse_args_r( $child_req, $data );
        }

        return (array) $data;
    }

    /**
     * Determine if the given configuration value exists.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function has( $key ) {
        return isset( $this->items[$key] );
    }

    /**
     * Get the specified configuration value.
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

        return apply_filters( "wecodeart/config/get/{$key}", $this->items[$key] );
    }

    /**
     * Set a given configuration value.
     *
     * @param  array|string  $key
     * @param  mixed   $value
     *
     * @return void
     */
    public function set( $key, $value = null ) {
        $keys = is_array( $key ) ? $key : [$key => $value];

        foreach ( $keys as $key => $value ) {
            $this->items[$key] = apply_filters( "wecodeart/config/set/{$key}", $value );
        }
    }

    /**
     * Removes given configuration from the container.
     *
     * @param  string  $key
     *
     * @return void
     */
    public function forget( $key ) {
		unset( $this->items[$key] );
    }

    /**
     * Get all of the configuration items for the application.
     *
     * @return array
     */
    public function all() {
        return $this->items;
    }

    /**
     * Determine if the given configuration option exists.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function offsetExists( $key ): bool {
        return $this->has( $key );
    }

    /**
     * Get a configuration option.
     *
     * @param  mixed   $key
     *
     * @return mixed
     */
    public function offsetGet( $key ): mixed {
        return $this->get( $key );
    }

    /**
     * Set a configuration option.
     *
     * @param  string   $key
     * @param  mixed    $value
     *
     * @return void
     */
    public function offsetSet( $key, $value ): void {
        $this->set( $key, $value );
    }

    /**
     * Unset a configuration option.
     *
     * @param  string   $key
     *
     * @return void
     */
    public function offsetUnset( $key ): void {
        $this->set( $key, null );
    }
}