<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg Modules
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		4.0.3
 * @version		5.0.6
 */

namespace WeCodeArt\Gutenberg;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use function WeCodeArt\Functions\get_prop;

/**
 * Handles Gutenberg Modules.
 */
class Modules implements \ArrayAccess {

	use Singleton;

	/**
	 * The registered modules.
	 *
	 * @var Modules[]
	 */
	protected $items = [];

	/**
	 * Send to Constructor
	 */
	public function init() {
		$this->register( 'styles',      Modules\Styles::class   );
		$this->register( 'classes',     Modules\Classes::class  );
		$this->register( 'patterns',    Modules\Patterns::class );
        
        $this->load();
	}

	/**
	 * Loads all registered integrations if their conditionals are met.
	 *
	 * @return void
	 */
	public function load() {
		foreach ( $this->items as $class ) {
			if ( ! $this->conditionals_are_met( $class ) ) continue;
			$class::get_instance()->register_hooks();
		}
	}

	/**
	 * Checks if all conditionals of a given integration are met.
	 *
	 * @param 	Integration $class The class name of the integration.
	 *
	 * @return 	bool Whether or not all conditionals of the integration are met.
	 */
	protected function conditionals_are_met( $class ) {
		$conditionals = $class::get_conditionals();
		return wecodeart_if( $conditionals );
	}

	/**
     * Set a given module value.
     *
     * @param  array|string  $key
     * @param  mixed   $value
     *
     * @return void
     */
    public function register( $key, $value = null ) {
        $this->set( $key, $value );
	}
	
    /**
     * Set a given module value.
     *
     * @param  array|string  $key
     * @param  mixed   $value
     *
     * @return void
     */
    public function set( $key, $value = null ) {
        $keys = is_array( $key ) ? $key : [ $key => $value ];

        foreach ( $keys as $key => $value ) {
            $this->items[$key] = apply_filters( "wecodeart/gutenberg/modules/set/{$key}", $value );
        }
	}

	/**
     * Determine if the given module value exists.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function has( $key ) {
        return isset( $this->items[$key] );
    }

    /**
     * Get the specified module value.
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

        return apply_filters( "wecodeart/gutenberg/modules/get/{$key}", $this->items[$key] );
    }
	
	/**
     * Removes module from the container.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function forget( $key ) {
		unset( $this->items[$key] );
    }

    /**
     * Get all of the module items for the application.
     *
     * @return array
     */
    public function all() {
        return $this->items;
    }

    /**
     * Determine if the given module option exists.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function offsetExists( $key ) {
        return $this->has( $key );
    }

    /**
     * Get a module option.
     *
     * @param  string  $key
     *
     * @return mixed
     */
    public function offsetGet( $key ) {
        return $this->get( $key );
    }

    /**
     * Set a module option.
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
     * Unset a module option.
     *
     * @param  string  $key
     *
     * @return void
     */
    public function offsetUnset( $key ) {
        $this->set( $key, null );
    }
}
