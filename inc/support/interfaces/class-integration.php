<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage  Support\Interfaces\Integration Interface
 * @copyright   Copyright (c) 2019, WeCodeArt Framework
 * @since		4.0.2
 * @version		4.0.2
 */

namespace WeCodeArt\Support\Interfaces;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Loadable;

/**
 * An interface for registering integrations with WordPress/WeCodeArt
 */
interface Integration extends Loadable {

	/**
	 * Initializes the integration.
	 *
	 * This is the place to register hooks and filters.
	 *
	 * @return void
	 */
	public function register_hooks();
}