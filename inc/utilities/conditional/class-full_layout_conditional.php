<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Utilities\Conditional\is_full_layout
 * @copyright   Copyright (c) 2019, WeCodeArt Framework
 * @since 		4.0
 * @version		4.0
 */

namespace WeCodeArt\Utilities\Conditional;

defined( 'ABSPATH' ) || exit(); 

use WeCodeArt\Utilities\Helpers;
use WeCodeArt\Utilities\Conditional;

/**
 * Conditional that is only met when in Gutenberg has wide/full content.
 */
class Full_Layout_Conditional implements Conditional {

	/**
	 * @inheritdoc
	 */
	public function is_met() {
		global $post;
		
		$enabled = apply_filters( 'wecodeart/filter/gutenberg/auto_layout', true );

		if( ! is_singular() || $enabled === false ) return false;

		// Retrieve an array of blocks used for this post.
		$blocks = parse_blocks( $post->post_content ); 

		foreach( $blocks as $block ) {  
			// If we have full/wide allign return true early and bail.
			if( in_array( Helpers::get_prop( $block['attrs'], 'align' ), [ 'full', 'wide' ] ) ) {
				return true;
			}
		}

		return false;
	}
}