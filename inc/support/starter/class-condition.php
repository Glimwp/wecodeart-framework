<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Support\Starter\Condition\is_fresh
 * @copyright   Copyright (c) 2024, WeCodeArt Framework
 * @since 		5.0.0
 * @version		6.3.7
 */

namespace WeCodeArt\Support\Starter;

defined( 'ABSPATH' ) || exit(); 

use WeCodeArt\Conditional\Interfaces\ConditionalInterface;

/**
 * Conditional.
 */
class Condition implements ConditionalInterface {

	/**
	 * @inheritdoc
	 */
	public function is_met(): bool {
		return (bool) \get_option( 'fresh_site' );
	}
}