<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Conditional\Settings
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since 		5.0.0
 * @version		6.0.0
 */

namespace WeCodeArt\Conditional;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Conditional\Interfaces\ConditionalInterface;
use function WeCodeArt\Functions\get_prop;

/**
 * Conditional that is only met when in the admin.
 */
class Settings implements ConditionalInterface {

	/**
	 * @inheritdoc
	 */
	public function is_met() {
		global $pagenow;

		if( $pagenow === 'themes.php' ) {
			if( get_prop( $_GET, [ 'page' ] ) === 'wecodeart' ) {
				return true;
			}
		}

		return false;
	}
}