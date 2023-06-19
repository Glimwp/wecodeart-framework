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
 * @since		5.2.4
 * @version		6.1.2
 */

namespace WeCodeArt\Gutenberg\Blocks\Design;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Columns blocks.
 */
class Columns extends Dynamic {

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
	protected $block_name = 'columns';

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles() {
		$breaks	= wecodeart_json( [ 'settings', 'custom', 'breakpoints' ], [] );
		$mobile	= get_prop( $breaks, 'sm', '640px' );

		$inline = '
			*[class*="col-"] {
				flex: 0 0 auto;
			}
		';
		
		foreach( $breaks as $key => $val ) {
			$inline .= "@media (min-width:{$val}){";
			$inline .= ".col-{$key}{flex: 1 0 0%}";
			$inline .= "}";
		}

		return "
			.wp-block-columns {
				display: flex;
				flex-wrap: wrap;
			}
			.wp-block-columns.grid {
				display: grid;
				gap: var(--wp--style--block-gap);
			}
			.wp-block-columns.are-vertically-aligned-top {
				align-items: flex-start;
			}
			.wp-block-columns.are-vertically-aligned-center {
				align-items: center;
			}
			.wp-block-columns.are-vertically-aligned-bottom {
				align-items: flex-end;
			}
			.wp-block-column {
				width: 100%;
				flex: 1 0 0%;
				max-width: 100%;
				box-sizing: border-box;
			}
			.wp-block-column.is-vertically-aligned-top {
				align-self: flex-start;
			}
			.wp-block-column.is-vertically-aligned-center {
				align-self: center;
			}
			.wp-block-column.is-vertically-aligned-bottom {
				align-self: flex-end;
			}
			@media (max-width: $mobile) {
				.wp-block-columns:not(.is-not-stacked-on-mobile) {
					flex-direction: column;
				}
			}
			{$inline}
		";
	}
}
