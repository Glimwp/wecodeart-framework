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
 * @since		6.2.9
 * @version		6.2.9
 */

namespace WeCodeArt\Gutenberg\Modules\Styles\Blocks;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Modules\Styles\Processor;

/**
 * Block CSS Processor
 */
class Quote extends Processor {
	/**
	 * Additional selectors to remove styles from.
	 *
	 * @return 	array
	 */
	public function remove_style(): array {
		return [ 
			[ 'tag_name' => 'blockquote' ]
		];
	}
}