<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Conditional\is_dev_mode
 * @copyright   Copyright (c) 2024, WeCodeArt Framework
 * @since 		4.0.1
 * @version		6.3.7
 */

namespace WeCodeArt\Support\IfSo;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Config\Interfaces\Conditional;

/**
 * Conditional that is only met when in dev mode.
 */
class Debug implements Conditional {

	/**
	 * @inheritdoc
	 */
	public function is_met(): bool {
		return \defined( 'WP_DEBUG' ) && \WP_DEBUG || \defined( 'SCRIPT_DEBUG' ) && \SCRIPT_DEBUG;
	}
}