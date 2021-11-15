<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Core
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since 		3.0
 * @version		5.2.4
 */

namespace WeCodeArt;

defined( 'ABSPATH' ) || exit(); 

use WeCodeArt\Singleton;
use WeCodeArt\Markup\SVG;
use function WeCodeArt\Functions\get_prop;

/**
 * General Hooks
 */
class Core {

	use Singleton;

	/**
	 * Send to Constructor
	 *
	 * @since 3.6.2
	 */
	public function init() {
		add_filter( 'body_class',				[ $this, 'body_classes'		] );
		add_filter( 'get_the_archive_title',	[ $this, 'archive_title'	] );
		add_filter( 'post_gallery',				[ $this, 'post_gallery'		], 10, 2 );

		Core\Scripts	::get_instance();
		Core\Header		::get_instance();
		Core\Content	::get_instance();
		Core\Footer		::get_instance();
	}

	/**
	 * Adds custom classes to the array of body classes.
	 *
	 * @since	4.0.6
	 * @version 5.2.2
	 *
	 * @param 	array 	$classes Classes for the body element.
	 *
	 * @return 	array
	 */
	public function body_classes( $classes ) {
		// Add a class of hfeed to non-singular pages.
		if ( ! is_singular() && ! is_404() ) {
			$classes[] = 'hfeed';
		}
		
		// Adds a class of group-blog to blogs with more than 1 published author.
		if ( is_multi_author() ) {
			$classes[] = 'group-blog';
		}
		
		// Theme
		$classes[] = 'theme-' . wecodeart( 'name' );
		$classes[] = is_child_theme() ? 'theme-is-skin' : 'theme-is-base'; 

		return $classes;
	}

	/**
	 * Archives Title
	 *
	 * @since 	unknown
	 * @version	5.2.4
	 *
	 * @return 	string
	 */
	public function archive_title( $content ) {
		$output = SVG::compile( 'folder' );

		$title_template = '<span>%s</span>';

		if ( is_search() ) {
			$output = SVG::compile( 'search' , [
				'class' => 'me-3'
			] );
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
	 * Gallery Shortcode
	 *
	 * @since	5.0.0
	 * @version	5.0.0
	 *
	 * @return 	string
	 */
	public function post_gallery( $output, $attr ) {
		global $post, $wp_locale;

		static $instance = 0;
		$instance++;

		extract( shortcode_atts( [
			'id'         => $post->ID,
			'order'      => 'ASC',
			'orderby'    => 'menu_order ID',
			'itemtag'    => 'figure',
			'icontag'    => 'div',
			'captiontag' => 'figcaption',
			'columns'    => 0,
			'size'       => 'large',
			'include'    => '',
			'exclude'    => ''
		], $attr ) );

		$id 	= intval( $id );

		if( 'RAND' == $order ) $orderby = 'none';

		$args 	= [
			'post_type'		 => 'attachment',
			'post_status' 	 => 'inherit',
			'post_mime_type' =>	'image',
			'order' 		 => $order, 
			'orderby' 		 => $orderby
		];

		$attachments = [];

		if( ! empty( $include ) ) {
			$include 		= preg_replace( '/[^0-9,]+/', '', $include );
			$_attachments 	= get_posts( wp_parse_args( [
				'include' 	=> $include,
			], $args ) );

			foreach( $_attachments as $key => $val ) {
				$attachments[$val->ID] = $_attachments[$key];
			}
		} elseif( ! empty( $exclude ) ) {
			$exclude 	 = preg_replace( '/[^0-9,]+/', '', $exclude );
			$attachments = get_children( wp_parse_args( [
				'exclude' 		=> $exclude,
				'post_parent' 	=> $id, 
			], $args ) );
		} else {
			$attachments = get_children( wp_parse_args( [
				'post_parent' 	=> $id,
			], $args ) );
		}

		if( empty( $attachments ) ) {
			return '';
		}

		if( is_feed() ) {
			$output = "\n";
			foreach( $attachments as $att_id => $attachment ) $output .= wp_get_attachment_link( $att_id, $size, true ) . "\n";
			return $output;
		}

		$itemtag 	= tag_escape( $itemtag );
		$captiontag = tag_escape( $captiontag );
		$columns 	= intval( $columns );
		$has_cols 	= $columns > 0 ? true : false;

		$wrapper	= [ 'wp-gallery', 'wp-gallery--id-' . $id ];
		$items 		= [ 'wp-gallery__item' ];

		if( $has_cols ) {
			$items[]	= 'col';
			$wrapper 	= array_merge( $wrapper, [ 'row', 'row-cols-2', 'row-cols-md-' . $columns ] );
		}
		
		$items 		= implode( ' ', $items );
		$wrapper 	= implode( ' ', $wrapper );

		$output 	= "<div id='gallery-{$instance}' class='${wrapper}'>";

		$i = 0;
		foreach( $attachments as $id => $attachment ) {
			$link 	= ( isset( $attr['link'] ) && 'file' == $attr['link'] );
			$link	= wp_get_attachment_link( $id, $size, $link, false );

			$output .= "<{$itemtag} class='{$items}'>";
			$output .= "<{$icontag} class='wp-gallery__icon'>$link</{$icontag}>";

			if ( $captiontag && trim( $attachment->post_excerpt ) ) {
				$output .= "<{$captiontag} class='wp-gallery__caption'>" . wptexturize( $attachment->post_excerpt ) . "</{$captiontag}>";
			}
			
			$output .= "</{$itemtag}>";
		}

		$output .= "</div>\n";

		return $output;
	}
}