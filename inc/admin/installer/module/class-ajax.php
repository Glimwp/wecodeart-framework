<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Admin\Installer\Ajax
 * @copyright   Copyright (c) 2024, WeCodeArt Framework
 * @since 		6.2.9
 * @version		6.2.9
 */

namespace WeCodeArt\Admin\Installer\Module;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Admin\Request\Async;
use WeCodeArt\Admin\Installer\Module;
use function WeCodeArt\Functions\get_prop;

/**
 * This class handles a post request being send to a given endpoint.
 */
class Ajax extends Async {

    /**
     * Prefix
     *
     * @var     string
     * @access  protected
     */
    protected $prefix = 'wca';

    /**
     * Action
     *
     * @var     string
     * @access  protected
     */
    protected $action = 'manage_theme_modules';

    /**
     * Handle
     * 
     * @return  mixed
     */
    protected function handle() {
        $type       = sanitize_text_field( get_prop( $_POST, 'type', '' ) );
        $modules    = array_map( function( $plugin ) {
            return array_map( 'sanitize_text_field', wp_array_slice_assoc( $plugin, [ 'slug', 'source', 'destination' ] ) );
        }, json_decode( stripslashes( get_prop( $_POST, 'plugins', '{}' ) ), true ) );

		if ( empty( $modules ) ) {
            wp_send_json_success( [
                'message'   => esc_html__( 'Modules array is empty.', 'wecodeart' ),
                'installed' => []
            ] );
        }

        switch( $type ) :
            case 'install':
                $data = self::install( $modules );
            break;
            case 'remove':
                $data = self::uninstall( $modules );
            break;
            default: 
                $data = [
                    'message' => esc_html__( 'Please define an action type.', 'wecodeart' ),
                ];
            break;
        endswitch;

        wp_send_json_success( $data );
    }

    /**
     * Install modules
     *
     * @param   array   $modules    Array of modules slugs/sources
     * 
     * @return  array
     */
    public static function install( array $modules = [] ): array {
        $message    = '';
        $success    = [];
        $failed     = [];

        // Remove unnecessary output
		remove_all_actions( 'upgrader_pre_install' );
		remove_all_actions( 'upgrader_process_complete' );
		remove_all_actions( 'upgrader_post_install' );

        foreach( $modules as $module ) {
            $installer = new Module( $module );
            $installer->install();

            if( $installer->ok() ) {
                $success[] = $module;
            } else {
                $failed[] = $module;
            }
        }

        if( count( $success ) === count( $modules ) ) {
            $message = esc_html__( 'All modules installed successfully.', 'wecodeart' );
        }

        if( count( $success ) === 1 ) {
            $message = esc_html__( 'Module installed successfully.', 'wecodeart' );
        }

        if( count( $success ) === 0 ) {
            $message = esc_html__( 'Module failed to install.', 'wecodeart' );
        }
        
        if( count( $success ) && count( $failed ) ) {
            $message = esc_html__( 'Some modules installed successfully, others have failed.', 'wecodeart' );
        }
        
        if( count( $failed ) === count( $modules ) > 1 ) {
            $message = esc_html__( 'All modules have failed to install.', 'wecodeart' );
        }

        return [
            'message'   => $message,
            'success'   => $success,
            'failed'    => $failed,
        ];
    }

    /**
     * Remove modules
     *
     * @param   array   $modules    Array of modules slugs/sources
     * 
     * @return  array
     */
    public static function uninstall( array $modules = [] ): array {
        $message    = _nx( 'Module uninstalled.', 'Modules uninstalled.', count( $modules ), 'modules uninstall ajax', 'wecodeart' );

        foreach( $modules as $module ) {
            $installer = new Module( $module );
            $installer->uninstall();
        }

        return [
            'message'   => $message,
            'success'   => $modules,
        ];
    }
}