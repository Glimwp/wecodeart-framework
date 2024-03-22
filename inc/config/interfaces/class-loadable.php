<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	WeCodeArt/Config/Interfaces
 * @copyright   Copyright (c) 2024, WeCodeArt Framework
 * @since		4.0.2
 * @version		4.0.2
 */

 namespace WeCodeArt\Config\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * An interface for registering integrations with WordPress\WeCodeArt
 */
interface Loadable {

	/**
	 * Returns the conditionals based in which this loadable should be active.
	 *
	 * @return array
	 */
	public static function get_conditionals();
}