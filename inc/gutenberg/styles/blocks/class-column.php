<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg CSS Frontend
 * @copyright   Copyright (c) 2024, WeCodeArt Framework
 * @since		5.0.0
 * @version		6.3.7
 */

namespace WeCodeArt\Gutenberg\Styles\Blocks;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Styles\Processor;
use function WeCodeArt\Functions\get_prop;

/**
 * Block CSS Processor
 */
class Column extends Processor {
	/**
	 * Parses an output and creates the styles array for it.
	 *
	 * @return 	void
	 */
	protected function process_style(): void {
		// Custom Width
		if ( $value = get_prop( $this->attrs, 'width' ) ) {
			$this->add_declarations( [
				'flex'	=> sprintf( '0 0 %s', $value ),
			] );
		}

		parent::process_style();
	}
}