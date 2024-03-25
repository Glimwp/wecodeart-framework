<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Support\Modules
 * @copyright   Copyright (c) 2024, WeCodeArt Framework
 * @since 		6.2.9
 * @version		6.3.7
 */

namespace WeCodeArt\Support;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Admin\Installer\Module;
use WeCodeArt\Config\Traits\Singleton;
use WeCodeArt\Config\Traits\No_Conditionals;
use WeCodeArt\Config\Interfaces\Integration;
use function WeCodeArt\Functions\get_prop;

/**
 * Modules
 */
final class Modules implements Integration {

	use Singleton;
	use No_Conditionals;

	CONST MODULES_CACHE_KEY = 'wecodeart/support/modules/check';

	/**
	 * Hooks
	 *
	 * @return  void
	 */
	public function register_hooks() {
		// Check modules for updates
		\add_action( 'init', 									[ $this, 'check_updates'	] );
		\add_action( 'init', 									[ $this, 'merge_installed'	] );
		\add_filter( 'wecodeart/filter/support/plugins/admin', 	[ $this, 'extend_plugins' 	] );
		\add_action( 'wecodeart/upgrade/finish',				[ $this, 'update_modules' 	] );
	}

	/**
	 * Extend plugins localize.
	 *
	 * @since   6.2.9
	 * @version	6.3.5
	 *
	 * @param 	array	$data
	 *
	 * @return	array
	 */
	public function extend_plugins( $data = [] ): array {
		$installers = get_prop( wecodeart_config( 'installers' ), [ 'modules' ], [] );
		$installers = array_merge( get_prop( $data, [ 'installers' ], [] ), $installers );

		usort( $installers, function ( $a, $b ) {
			return ( $a['slug'] === null ) <=> ( $b['slug'] === null );
		} );

		return wp_parse_args( [
			'installers'	=> $installers,
			'modules'		=> wecodeart_option( 'modules', [] ),
		], $data );
	}

	/**
	 * Detect installed.
	 *
	 * @since   6.3.7
	 * @version	6.3.7
	 *
	 * @return 	void
	 */
	public function merge_installed(): void {
		$install 	= [];
		
		$options  	= wecodeart_option( 'modules', [] );
		$modules	= wecodeart( 'config' )['installers']['modules'] ?? [];
		
		foreach ( glob( dirname( __FILE__ ) . '/*', GLOB_ONLYDIR ) as $directory ) {
			$directory 	= basename( $directory );
			$namespace	= 'WeCodeArt\\Support\\Modules\\' . ucfirst( $directory );
			$in_options = wp_list_filter( $options, [ 'destination' => $directory ] );

			if( ! class_exists( $namespace ) || current( $in_options ) ) {
				continue;
			}

			$installer = current( wp_list_filter( $modules, [ 'destination' => $directory ] ) );

			if( $installer ) {
				$install[] = $installer;
			}
		}

		if( empty( $install ) ) {
			return;
		}

		wecodeart_option( [
			'modules' => array_values( array_merge( $options, $install ) )
		] );
	}

	/**
	 * Check modules for updates.
	 *
	 * @since   6.2.9
	 * @version	6.3.7
	 *
	 * @return 	void
	 */
	public function check_updates(): void {
		$modules  = wecodeart_option( 'modules', [] );

        // Check for updates
		if ( false === ( get_transient( self::MODULES_CACHE_KEY ) ) ) {
			foreach( $modules as $key => $module ) {
				$current_ver	= get_prop( $module, [ 'version' ], '1.0.0' );
				unset( $module['version'] ); // Get latest
				$installer 		= new Module( $module );
				$remote_ver 	= $installer->get_ver();
				
				if ( version_compare( $current_ver, $remote_ver, '<' ) ) {
					$modules[$key]['update'] = $remote_ver; 
				}
			}
			
			wecodeart_option( [
				'modules' => $modules
			] );

			set_transient( self::MODULES_CACHE_KEY, true, 12 * HOUR_IN_SECONDS );
		}
	}

	/**
	 * Upgrade modules with theme.
	 *
	 * @since   6.3.1
	 * @version	6.3.5
	 *
	 * @return 	void
	 */
	public function update_modules(): void {
		Module\Ajax::install( wecodeart_option( 'modules', [] ) );
	}
}