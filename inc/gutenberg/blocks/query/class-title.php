<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg\Blocks
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since		5.0.0
 * @version		6.0.0
 */

namespace WeCodeArt\Gutenberg\Blocks\Query;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Query Title block.
 */
class Title extends Dynamic {

	use Singleton;

    /**
	 * Block namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'core';

	/**
	 * Block name.
	 *
	 * @var string
	 */
	protected $block_name = 'query-title';

	/**
	 * Init.
	 */
	public function init() {
		\add_filter( 'get_the_archive_title', [ $this, 'filter_title' ] );
	}

	/**
	 * Block args.
	 *
	 * @return 	array
	 */
	public function block_type_args(): array {
		return [
			'render_callback' => [ $this, 'render' ]
		];
	}

	/**
	 * Dynamically renders the `core/query-title` block.
	 *
	 * @param 	array 	$attributes	The attributes.
	 * @param 	string 	$content 	The block markup.
	 * @param 	string 	$block 		The block data.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( array $attributes = [], string $content = '', $block = null ): string {
        $type       = get_prop( $attributes, 'type' );
        $is_archive = is_archive() || is_search() || is_home();

        if ( ! $type || ( 'archive' === $type && ! $is_archive ) ) {
            return '';
        }

        $title = '';

        if ( $is_archive ) {
            $title = get_the_archive_title();
        }

        $classnames = [];

        if( $align = get_prop( $attributes, 'textAlign' ) ) {
            $classnames[] = 'has-text-align-' . $align;
        }

        return wecodeart( 'markup' )::wrap( 'wp-block-' . $this->block_name, [
            [
                'tag' 	=> 'h' . get_prop( $attributes, 'level', '1' ),
                'attrs'	=> $this->get_block_wrapper_attributes( [
                    'class' => join( ' ', $classnames )
                ] )
            ]
        ], $title, [], false );
	}

	/**
	 * Archives Title
	 *
	 * @since 	unknown
	 * @version	5.3.1
	 *
	 * @return 	string
	 */
	public function filter_title( $content ) {
		$output = wecodeart( 'markup' )->SVG::compile( 'folder' );

		$title_template = '<span>%s</span>';

		if ( is_search() ) {
			$output = wecodeart( 'markup' )->SVG::compile( 'search' );
			$output .= sprintf( $title_template, sprintf( 
				esc_html__( 'Search Results for "%s".', 'wecodeart' ),
				'<span>' .  get_search_query() . '</span>' 
			) );
		} elseif ( is_category() ) {
			$output .= sprintf( $title_template, sprintf(
				esc_html__( 'Category Archives: %s', 'wecodeart' ),
				single_cat_title( '', false ) 
			) );
		} elseif ( is_tag() ) {
			$output .= sprintf( $title_template, sprintf(
				esc_html__( 'Tag Archives: %s', 'wecodeart' ),
				single_tag_title( '', false ) 
			) );
		} elseif( is_author() ) {
			$output .= sprintf( $title_template, sprintf(
				esc_html__( 'Author Archives: %s', 'wecodeart' ),
				get_the_author_meta( 'display_name' )
			) );
		} elseif ( is_year() ) {
			$output .= sprintf( $title_template, sprintf(
				esc_html__( 'Yearly Archives: %s', 'wecodeart' ), 
				get_the_date( _x( 'Y', 'yearly archives date format', 'wecodeart' ) ) 
			) );
		} elseif ( is_month() ) {
			$output .= sprintf( $title_template, sprintf(
				esc_html__( 'Monthly Archives: %s', 'wecodeart' ), 
				get_the_date( _x( 'F Y', 'monthly archives date format', 'wecodeart' ) ) 
			) );
		} elseif ( is_day() ) {
			$output .= sprintf( $title_template, sprintf(
				esc_html__( 'Daily Archives: %s', 'wecodeart' ),
				get_the_date()
			) );
		} elseif ( is_tax() ) {
			$output .= sprintf( esc_html__( '%s Archives', 'wecodeart' ), single_term_title( '', false ) );
		} elseif ( is_post_type_archive() ) {
			$output .= sprintf( $title_template, sprintf(
				esc_html__( 'Post Type Archives: %s', 'wecodeart' ), post_type_archive_title( '', false ) 
			) );
		} elseif ( is_home() ) {
			$output = sprintf( $title_template, esc_html__( 'Blog', 'wecodeart' ) );
		} else {
			$output .= esc_html__( 'Archives', 'wecodeart' );
		} 

		return $output;
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles() {
		return "
        .wp-block-query-title svg {
			margin-right: 1rem;
		}
		";
	}
}
