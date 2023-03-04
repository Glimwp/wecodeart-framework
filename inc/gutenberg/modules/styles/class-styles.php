<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg CSS Module
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since		4.0.3
 * @version		5.7.2
 */

namespace WeCodeArt\Gutenberg\Modules;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Integration;
use WeCodeArt\Gutenberg;
use WeCodeArt\Config\Traits\Asset;
use function WeCodeArt\Functions\get_prop;
use function WeCodeArt\Functions\get_json_color;

/**
 * Handles Gutenberg Theme CSS Functionality.
 */
class Styles implements Integration {

	use Singleton;
	use Asset;

	/**
	 * The CSS context for style engine.
	 *
	 * @var string
	 */
	const CONTEXT 	= 'blocks';

	/**
	 * The Styles Processor
	 *
	 * @access 	public
	 * @var 	null|object
	 */
	public $CSS		= null;

	/**
	 * The blocks duotone
	 *
	 * @access 	public
	 * @var 	array
	 */
	public $filters	= [];

	/**
	 * The blocks classes
	 *
	 * @access 	public
	 * @var 	array
	 */
	public $classes	= [];

	/**
	 * Get Conditionals
	 *
	 * @return void
	 */
	public static function get_conditionals() {
		wecodeart( 'conditionals' )->set( [
			'with_blocks_styles' => Styles\Condition::class,
		] );

		return [ 'with_blocks_styles' ];
	}

	/**
	 * Register Hooks - into styles processor action if enabled
	 *
	 * @since 	5.0.0
	 *
	 * @return 	void
	 */
	public function register_hooks() {
		$this->CSS	= wecodeart( 'styles' );

		// Custom Style Attributes support
		\WP_Block_Supports::get_instance()->register( 'customStyle', [
			'register_attribute' 	=> [ $this, 'register_attribute' ],
		] );

		// Hooks
		add_filter( 'should_load_separate_core_block_assets', '__return_true', PHP_INT_MAX );
		add_action( 'enqueue_block_editor_assets',	[ $this, 'block_editor_assets' 	], 20, 1 );
		add_filter( 'render_block',					[ $this, 'filter_render' 		], 20, 2 );
		add_action( 'wp_enqueue_scripts',			[ $this, 'manage_styles'		], 20, 1 );
		add_filter( 'wp_theme_json_data_theme',  	[ $this, 'link_brightness' 		], 20, 1 );
		add_action( 'wp_body_open',					[ $this, 'output_duotone'		], 20, 1 );
		
		// Remove WP/GB plugins hooks - we dont need this anymore!
		remove_filter( 'render_block', 'wp_render_spacing_gap_support', 10, 2 );
		remove_filter( 'render_block', 'wp_render_duotone_support',		10, 2 );
		remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters',	10, 1 );
		
		// Eventually it will be removed - 1 check since they are all from GB.
		if( function_exists( 'gutenberg_render_layout_support_flag' ) ) {
			remove_filter( 'render_block', 'gutenberg_render_spacing_gap_support', 	10, 2 );
			remove_filter( 'render_block', 'gutenberg_render_duotone_support', 		10, 2 );
		 	remove_action( 'wp_body_open', 'gutenberg_global_styles_render_svg_filters',	10, 1 );
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
	 * Adds the `customCSS` attributes to all blocks, to avoid `Invalid parameter(s): attributes` error.
	 *
	 * @since   5.2.x
	 *
	 * @return 	void
	 */
	public function register_attribute( $block ) {
		// Currently we suport only core blocks
		if( ! in_array( $block->name, self::core_blocks( true ) ) ) return;

		$block->attributes['customCSS'] = [
			'type'    => 'string',
			'default' => null,
		];
		
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
	public function filter_render( $content, $block ) {
		$block_name	= get_prop( $block, 'blockName' );

		// Skip nulls
		if( ! $block_name ) {
			return $content;
		}

		// Remove styles, where needed.
		if ( in_array( $block_name, self::core_blocks( true ) ) ) {
			// Process a block
			$processed 	= self::process_block( $block );
			$block_id	= $processed->get_id();
			$content 	= new \WP_HTML_Tag_Processor( $content );

			$content->next_tag();

			// Inject class name to block tag.
			$content->add_class( $block_id );
			
			// Remove inline style attribute.
			$content->remove_attribute( 'style' );
			
			if( $block_name === 'core/media-text' ) {
				// Media & Text also adds style to the <figure>.
				$content->next_tag( [ 'tag_name' => 'figure' ] );
				$content->remove_attribute( 'style' );
			}
			
			if( $block_name === 'core/button' ) {
				// Button also adds style to the <a>.
				$content->next_tag( [ 'tag_name' => 'a' ] );
				$content->remove_attribute( 'style' );
			}
			
			if( $block_name === 'core/table' ) {
				// Table also adds style to the <table>.
				$content->next_tag( [ 'tag_name' => 'table' ] );
				$content->remove_attribute( 'style' );
			}
			
			if( $block_name === 'core/quote' ) {
				// Quote also adds style to the <blockquote>.
				$content->next_tag( [ 'tag_name' => 'blockquote' ] );
				$content->remove_attribute( 'style' );
			}
			
			if( in_array( $block_name, [ 'core/avatar', 'core/image', 'core/cover' ], true ) ) {
				// Blocks that also adds style to the <img />.
				$content->next_tag( [ 'tag_name' => 'img' ] );
				$content->remove_attribute( 'style' );
			}
			
			$classes	= $processed->get_classes();
			$filters	= $processed->get_duotone();
			
			// Process Duotone SVG filters.
			if( $filters ) {
				$this->filters[$block_id] = $filters;
			}
			
			// Utilities CSS.
			if( $classes ) {
				$this->classes = array_merge( $this->classes, $classes );
			}
		}

		return (string) $content;
	}

	/**
	 * Output styles.
	 *
	 * @return 	string
	 */
	public function manage_styles() {
		global $_wp_current_template_content;

		// Manage Styles.
		wp_dequeue_style( 'wp-block-library' );         // WordPress Core
        wp_dequeue_style( 'wp-block-library-theme' );   // WordPress Core

		$style = '';

		// Box Sizing
		$style .= '*,*::before,*::after {box-sizing: border-box;}';
		// Colors RGB as CSS var
		$palette 	= wecodeart_json( [ 'settings', 'color', 'palette', 'default' ], [] );
		$palette 	= wecodeart_json( [ 'settings', 'color', 'palette', 'theme' ], $palette );
		$palette 	= array_merge( $palette, wecodeart_json( [ 'settings', 'color', 'palette', 'custom' ], [] ) );

		foreach( $palette as $item ) {
			$slug 	= get_prop( $item, [ 'slug' ] );
			$value 	= $this->CSS::color_to_rgba( get_prop( $item, [ 'color' ] ), false, true );
			$value  = join( ', ', wp_array_slice_assoc( $value, [ 'r', 'g', 'b' ] ) );
			
			$style .= sprintf( '.has-%s-color{--wp--color--rgb:%s}', $slug, $value );
			$style .= sprintf( '.has-%s-background-color{--wp--background--rgb:%s}', $slug, $value );
		}

		wp_add_inline_style( 'global-styles', $style );

		// Collect template classes.
		$blocks		= parse_blocks( $_wp_current_template_content );
		$classes	= self::collect_classes( _flatten_blocks( $blocks ) );

		$this->classes = array_merge( $this->classes, $classes );

		// Process utilities.
		if( ! empty( $this->classes ) ) {
			$this->CSS->Utilities->load( $this->classes );
		}
	}

	/**
	 * Link hover brightness.
	 *
	 * @param	object  WP_Theme_JSON object
	 *
	 * @return 	object
	 */
	public function link_brightness( $object ): object {
		$link = get_json_color( [ 'styles', 'elements', 'link', 'color', 'text' ] );

		if( $link ) {
			$data = $object->get_data();

			// Adjust hover brightness.
			$data['styles']['elements']['link'][':hover'] = wp_parse_args( [
				'color' => [
					'text' => $this->CSS::hex_brightness( $link, -25 )
				]
			], get_prop( $data, [ 'styles', 'element', 'link', ':hover' ], [] ) );

			// Update object.
			$object->update_with( $data );
		}

		return $object;
	}

	/**
	 * Output duotone in footer.
	 *
	 * @return 	string
	 */
	public function output_duotone() {
		$default_duotones = wp_get_global_settings( [ 'color', 'duotone', 'default' ] );

		// Not sure why they render those filters in multiple SVG files.
		// Even though the defaults are printed in wp_body_open,
		// using them will generate another SVG in footer (eg grayscale)
		if ( ! empty( $default_duotones ) ) {
			foreach( $default_duotones as $preset ) {
				$this->filters[get_prop( $preset, 'slug' )] = wecodeart( 'styles' )::get_duotone( $preset );
			}	
		}

		if( empty( $this->filters ) ) return;
		?>
		<svg xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 0 0" width="0" height="0" focusable="false" role="none" style="position:absolute;left:-9999px;visibility:hidden;overflow:hidden;">
			<defs>
			<?php foreach( $this->filters as $block_id => $filter ) : ?>
				<filter id="wp-duotone-<?php echo esc_attr( $block_id ); ?>">
					<feColorMatrix
						type="matrix"
						color-interpolation-filters="sRGB"
						values=" .299 .587 .114 0 0 .299 .587 .114 0 0 .299 .587 .114 0 0 .299 .587 .114 0 0"
					/>
					<feComponentTransfer color-interpolation-filters="sRGB">
						<feFuncR type="table" tableValues="<?php echo esc_attr( implode( ' ', $filter['r'] ) ); ?>" />
						<feFuncG type="table" tableValues="<?php echo esc_attr( implode( ' ', $filter['g'] ) ); ?>" />
						<feFuncB type="table" tableValues="<?php echo esc_attr( implode( ' ', $filter['b'] ) ); ?>" />
						<feFuncA type="table" tableValues="<?php echo esc_attr( implode( ' ', $filter['a'] ) ); ?>" />
					</feComponentTransfer>
					<feComposite in2="SourceGraphic" operator="in" />
				</filter>
			<?php endforeach; ?>
			</defs>
		</svg>
		<?php
	}

	/**
	 * Core blocks.
	 *
	 * @param	bool	$exclude Whether to exclude blocks that should be skipped
	 *
	 * @return 	array
	 */
	public static function core_blocks( $exclude = false ): array {
		$blocks = apply_filters( 'wecodeart/filter/gutenberg/styles/core', [
			'core/archives',
			'core/audio',
			'core/avatar',
			'core/block',
			'core/button',
			'core/buttons',
			'core/calendar',
			'core/categories',
			'core/code',
			'core/column',
			'core/columns',
			'core/cover',
			'core/comment-author-avatar',
			'core/comment-author-name',
			'core/comment-content',
			'core/comment-date',
			'core/comment-edit-link',
			'core/comment-reply-link',
			'core/comment-template',
			'core/comments',
			'core/comments-title',
			'core/comments-pagination',
			'core/comments-pagination-next',
			'core/comments-pagination-previous',
			'core/comments-pagination-numbers',
			'core/embed',
			'core/file',
			'core/freeform',
			'core/gallery',
			'core/group',
			'core/heading',
			'core/home-link',
			'core/html',
			'core/image',
			'core/latest-comments',
			'core/latest-posts',
			'core/list',
			'core/list-item',
			'core/loginout',
			'core/media-text',
			'core/missing',
			'core/more',
			'core/navigation',
			'core/navigation-link',
			'core/navigation-submenu',
			'core/next-page',
			'core/pattern',
			'core/page-list',
			'core/page-list-item',
			'core/paragraph',
			'core/preformatted',
			'core/pullquote',
			'core/post-author',
			'core/post-author-name',
			'core/post-author-biography',
			'core/post-title',
			'core/post-terms',
			'core/post-date',
			'core/post-excerpt',
			'core/post-content',
			'core/post-featured-image',
			'core/post-navigation-link',
			'core/post-template',
			'core/post-comment',
			'core/post-comments-link',
			'core/post-comments-form',
			'core/post-comments-count',
			'core/quote',
			'core/query',
			'core/query-title',
			'core/query-no-results',
			'core/query-pagination',
			'core/query-pagination-next',
			'core/query-pagination-previous',
			'core/query-pagination-numbers',
			'core/read-more',
			'core/rss',
			'core/search',
			'core/separator',
			'core/shortcode',
			'core/site-logo',
			'core/site-title',
			'core/site-tagline',
			'core/social-link',
			'core/social-links',
			'core/spacer',
			'core/table',
			'core/table-of-contents',
			'core/tag-cloud',
			'core/template-part',
			'core/term-description',
			'core/text-columns',
			'core/verse',
			'core/video',
		] );

		if( (bool) $exclude ) {
			// Exclude this blocks from styles extensions for various reasons
			// like: no wrapper, renders html or other blocks or simply it should not remove styles
			$blocks = array_diff( $blocks, Gutenberg::get_restricted_blocks() );
		}

		return $blocks;
	}

	/**
	 * Get classNames.
	 *
	 * @param	array	$blocks  List with all blocks
	 *
	 * @return 	array
	 */
	public static function collect_classes( array $blocks = [] ): array {
		$return = [];

		foreach( $blocks as $block ) {
			// Dont bother with empty blocks from parse_blocks
			if( ! get_prop( $block, [ 'blockName' ] ) ) continue;

			$classes	= get_prop( $block, [ 'attrs', 'className' ], '' );
			$classes 	= array_filter( explode( ' ', $classes ) );

			if( ! empty( $classes ) ) {
				$return = array_merge( $return, $classes );
			}
		}

		return array_unique( $return );
	}

	/**
	 * Process a block.
	 *
	 * @param 	array 	$block 	The block data.
	 *
	 * @return 	object
	 */
	public static function process_block( array $block = [] ) {
		// Find the class that will handle the output for this block.
		$classname	= Styles\Processor::class;
		$defaults   = [
			'core/button' 			=> Styles\Blocks\Button::class,
			'core/column' 			=> Styles\Blocks\Column::class,
			'core/cover' 			=> Styles\Blocks\Cover::class,
			'core/gallery' 			=> Styles\Blocks\Gallery::class,
			'core/media-text' 		=> Styles\Blocks\Media::class,
			'core/navigation' 		=> Styles\Blocks\Navigation::class,
			'core/search' 			=> Styles\Blocks\Search::class,
			'core/separator' 		=> Styles\Blocks\Separator::class,
			'core/social-links'		=> Styles\Blocks\Social::class,
			'core/spacer' 			=> Styles\Blocks\Spacer::class,
			'core/post-featured-image'	=> Styles\Blocks\Featured::class,
		];

		$output_classes = apply_filters( 'wecodeart/filter/gutenberg/styles/processor', $defaults );

		if ( array_key_exists( $block['blockName'], $output_classes ) ) {
			$classname = $output_classes[ $block['blockName'] ];
		}
		
		if( class_exists( $classname ) ) {
			return ( new $classname( $block ) );
		};
	}
}