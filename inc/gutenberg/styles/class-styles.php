<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg CSS Module
 * @copyright   Copyright (c) 2024, WeCodeArt Framework
 * @since		4.0.3
 * @version		6.3.7
 */

namespace WeCodeArt\Gutenberg;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg;
use WeCodeArt\Config\Traits\Asset;
use WeCodeArt\Config\Interfaces\Configuration;
use function WeCodeArt\Functions\get_prop;

/**
 * Handles Gutenberg Theme CSS Functionality.
 */
class Styles implements Configuration {

	use Singleton;
	use Asset;

	/**
	 * The CSS context for style engine.
	 *
	 * @var string
	 */
	const CONTEXT 	= 'block-supports';

	/**
	 * The registered style processors.
	 *
	 * @var array[]
	 */
	protected   $items = [];

	/**
	 * Register Hooks - into styles processor action if enabled
	 *
	 * @since 	5.0.0
	 *
	 * @return 	void
	 */
	public function init() {
		// Default core processor.
		$this->set( 'core', 						Styles\Processor::class );
		
		// Some blocks require additional processing.
		$this->set( 'core/button', 					Styles\Blocks\Button::class );
		$this->set( 'core/column', 					Styles\Blocks\Column::class );
		$this->set( 'core/cover', 					Styles\Blocks\Cover::class );
		$this->set( 'core/group', 					Styles\Blocks\Group::class );
		$this->set( 'core/image', 					Styles\Blocks\Image::class );
		$this->set( 'core/media-text',				Styles\Blocks\Media::class );
		$this->set( 'core/navigation',				Styles\Blocks\Navigation::class );
		$this->set( 'core/quote',					Styles\Blocks\Quote::class );
		$this->set( 'core/search',					Styles\Blocks\Search::class );
		$this->set( 'core/separator',				Styles\Blocks\Separator::class );
		$this->set( 'core/social-links',			Styles\Blocks\Social::class );
		$this->set( 'core/spacer',					Styles\Blocks\Spacer::class );
		$this->set( 'core/table',					Styles\Blocks\Table::class );
		$this->set( 'core/template',				Styles\Blocks\Template::class );
		$this->set( 'core/post-featured-image', 	Styles\Blocks\Featured::class );

		// Custom style attributes support
		\WP_Block_Supports::get_instance()->register( 'customStyle', [
			'register_attribute' 	=> [ $this, 'register_attribute' ],
		] );

		// Hooks
        add_filter( 'should_load_separate_core_block_assets', '__return_true', PHP_INT_MAX );
		add_action( 'enqueue_block_editor_assets',	[ $this, 'block_editor_assets' 	], 20, 1 );
		add_filter( 'render_block',					[ $this, 'filter_render' 		], 20, 2 );
		
		// Eventually it will be removed - 1 check since they are all from GB.
		if( function_exists( 'gutenberg_render_duotone_support' ) ) {
			// Gutenberg plugin removes styles and adds them in footer - we dont want that.
			add_action( 'wp_enqueue_scripts', 'wp_enqueue_stored_styles', PHP_INT_MAX ); // Temporary
			add_action( 'wp_footer', 'wp_enqueue_stored_styles', 1 ); // Temporary.
		}
	}

	/**
	 * Editor only.
	 *
	 * @return  void
	 */
	public function block_editor_assets() {
		// Javascript
		wp_enqueue_script( $this->make_handle(), $this->get_asset( 'js', 'gutenberg/ext-styles' ), [
			'wecodeart-gutenberg-inline'
		], wecodeart( 'version' ) );
	}

	/**
	 * Adds the `customStyle` attributes to all blocks, to avoid `Invalid parameter(s): attributes` error.
	 *
	 * @since   5.2.x
	 *
	 * @return 	void
	 */
	public function register_attribute( $block ) {
		$has_support = block_has_support( $block, '__experimentalStyles', false );

		if ( ! $has_support ) {
			return;
		}
		
		if ( ! $block->attributes ) {
			$block->attributes = [];
		}

		$block->attributes['customStyle'] = [
			'type'    => 'string',
			'default' => null,
		];
	}

	/**
	 * Filter render_block
	 *
	 * @param	string 	$block_content
	 * @param	array 	$block
	 *
	 * @return 	string 	HTML
	 */
	public function filter_render( string $content = '', array $block = [] ): string {
		$block_name	= get_prop( $block, 'blockName' );

		// Skip nulls
		if( ! $block_name ) {
			return $content;
		}

		$block_type		= \WP_Block_Type_Registry::get_instance()->get_registered( $block_name );
		$has_support 	= block_has_support( $block_type, '__experimentalStyles', false );

		// To be implemented when we have dimensions / other styles
		// $has_styles_1	= get_prop( $block, [ 'attrs', 'style' ] );
		// $has_styles_2	= get_prop( $block, [ 'attrs', 'customStyle' ] );

		// Remove styles, where needed.
		if ( $has_support ) {
			// Process a block
			$processed 	= $this->process_block( $block );
			$content 	= new \WP_HTML_Tag_Processor( $content );

			$content->next_tag();

			// Inject class name to block tag.
			$content->add_class( $processed->get_id() );
			
			// Remove inline style attribute.
			$content->remove_attribute( 'style' );
			
			// Remove inline style attribute for extra wrappers.
			if( ! empty( $selectors = $processed->remove_style() ) ) {				
				foreach( $selectors as $selector ) {
					if( $content->next_tag( $selector ) ) {
						$content->remove_attribute( 'style' );
					}
				}
			}
		}

		return (string) $content;
	}

	/**
	 * Process a block.
	 *
	 * @param 	array 	$block 	Parsed block.
	 *
	 * @return 	object
	 */
	public function process_block( array $block = [] ): Styles\Processor {
		$classname 	= $this->get( 'core' );
		$block_name	= get_prop( $block, 'blockName', '' );

		if( $this->has( $block_name ) ) {
			$classname = $this->get( $block_name );
		}
		
		return ( new $classname( $block ) );
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
            $this->items[$key] = $value;
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

        return $this->items[$key];
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
}