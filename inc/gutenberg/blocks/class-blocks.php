<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg Blocks Registry
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		5.0.0
 * @version		5.0.0
 */

namespace WeCodeArt\Gutenberg;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Blocks Registry.
 */
class Blocks implements \ArrayAccess {

	use Singleton;

	/**
	 * The registered Blocks.
	 *
	 * @var Blocks[]
	 */
	protected $items = [];

    /**
	 * The CSS ID for registered style.
	 *
	 * @var string
	 */
    const CSS_ID = 'wecodeart-blocks';

    /**
	 * An array of blocks used in this page.
	 *
	 * @static
	 * @access private
	 * @since 1.0.2
	 * @var array
	 */
	private static $blocks = [];

	/**
	 * Send to Constructor
	 */
	public function init() {
        // Media Blocks
		$this->register( 'core/media-text', Blocks\Media\Text::class );
		$this->register( 'core/file',	    Blocks\Media\File::class );
        $this->register( 'core/image',	    Blocks\Media\Image::class );
        $this->register( 'core/audio',	    Blocks\Media\Audio::class );
        $this->register( 'core/video',	    Blocks\Media\Video::class );
        $this->register( 'core/embed',	    Blocks\Media\Embed::class );
        $this->register( 'core/cover',	    Blocks\Media\Cover::class );
        $this->register( 'core/gallery',    Blocks\Media\Gallery::class );
        // Text Blocks
        $this->register( 'core/code',	    Blocks\Text\Code::class );
        $this->register( 'core/table',	    Blocks\Text\Table::class );
		$this->register( 'core/quote',	    Blocks\Text\Quote::class );
		$this->register( 'core/pullquote',  Blocks\Text\Pullquote::class );
        // Design Blocks
		$this->register( 'core/group',	    Blocks\Design\Group::class );
		$this->register( 'core/buttons',    Blocks\Design\Buttons::class );
		$this->register( 'core/button',     Blocks\Design\Button::class );
		$this->register( 'core/spacer',     Blocks\Design\Spacer::class );
		$this->register( 'core/separator',  Blocks\Design\Separator::class );
        // Widget Blocks
		$this->register( 'core/search',	            Blocks\Widgets\Search::class );
		$this->register( 'core/calendar',           Blocks\Widgets\Calendar::class );
	    $this->register( 'core/rss',                Blocks\Widgets\RSS::class );
	    $this->register( 'core/latest-posts',       Blocks\Widgets\Posts::class );
		$this->register( 'core/latest-comments',    Blocks\Widgets\Comments::class );
		$this->register( 'core/social-links',       Blocks\Widgets\Social::class );
		$this->register( 'core/loginout',           Blocks\Widgets\Login::class );
        // Navigation Blocks
		$this->register( 'core/navigation',         Blocks\Navigation::class );
		$this->register( 'core/navigation-link',    Blocks\Navigation\Link::class );
		$this->register( 'core/navigation-submenu', Blocks\Navigation\Menu::class );
		$this->register( 'core/home-link',          Blocks\Navigation\Home::class );
        // Post Blocks
		$this->register( 'core/post-date',          Blocks\Post\Date::class );
		$this->register( 'core/post-title',         Blocks\Post\Title::class );
		$this->register( 'core/post-terms',         Blocks\Post\Terms::class );
		$this->register( 'core/post-author',        Blocks\Post\Author::class );
		$this->register( 'core/post-excerpt',       Blocks\Post\Excerpt::class );
		$this->register( 'core/post-content',       Blocks\Post\Content::class );
		$this->register( 'core/post-template',      Blocks\Post\Template::class );
		$this->register( 'core/post-featured-image',Blocks\Post\Image::class );
		$this->register( 'core/post-comments-link', Blocks\Post\Comments::class );
        // Query Blocks
		$this->register( 'core/query',	                    Blocks\Query::class );
		$this->register( 'core/query-title',	            Blocks\Query\Title::class );
		$this->register( 'core/template-part',              Blocks\Query\Template::class );
		$this->register( 'core/query-pagination-numbers',   Blocks\Query\Pagination\Numbers::class );
        
        // Hooks
        add_action( 'init',                 [ $this, 'load' ] );
        add_filter( 'render_block',         [ $this, 'collect_blocks' ], 10, 2 );
        add_action( 'wp_enqueue_scripts',   [ $this, 'register_styles' ], 20, 1 );
        add_action( 'wp_print_styles',      [ $this, 'remove_styles' ], 100 );
	}

    /**
	 * Filters the content of a single block.
     *
	 * @param   string  $block_content The block content about to be appended.
	 * @param   array   $block         The full block, including name and attributes.
     *
	 * @return  string
	 */
	public function collect_blocks( $block_content, $block ) {
		if ( $name = get_prop( $block, 'blockName' ) ) {
			self::$blocks[] = $name;
		}

		return $block_content;
	}

    /**
	 * Output styles on the frontend, during render.
	 *
	 * @return void
	 */
	public function register_styles() {
        if( empty( self::$blocks ) ) return;

        $inline_css = '';

        foreach( array_unique( self::$blocks ) as $block ) {
            if( ! $this->has( $block ) ) continue;
            $inline_css .= $this->get( $block )::get_instance()->styles();
        } 
        
        $inline_css = wecodeart( 'integrations' )->get( 'styles' )::compress( $inline_css );

        if( empty( $inline_css ) ) return;
        
        wp_register_style( self::CSS_ID, false, [], true, true );
        wp_add_inline_style( self::CSS_ID, $inline_css );
        wp_enqueue_style( self::CSS_ID );
	}

    /**
	 * Remove default styles
	 *
	 * @return void
	 */
	public function remove_styles() {
        wp_dequeue_style( 'wp-block-library' );         // WordPress Core
        wp_dequeue_style( 'wp-block-library-theme' );   // WordPress Core
	}

	/**
	 * Loads all registered integrations if their conditionals are met.
	 *
	 * @return void
	 */
	public function load() {
		foreach( $this->items as $class ) {
            $class::get_instance()->register();
        }
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
            $this->items[$key] = apply_filters( "wecodeart/gutenberg/blocks/set/{$key}", $value );
        }
	}

	/**
     * Determine if the given Blocks value exists.
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function has( $key ) {
        return isset( $this->items[$key] );
    }

    /**
     * Get the specified Blocks value.
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

        return apply_filters( "wecodeart/gutenberg/blocks/get/{$key}", $this->items[$key] );
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
