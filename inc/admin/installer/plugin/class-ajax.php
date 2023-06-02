<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
  * @package 	WeCodeArt Framework
 * @subpackage 	Admin\Installer\Ajax
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since 		6.1.2
 * @version		6.1.2
 */

namespace WeCodeArt\Admin\Installer\Plugin;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Admin\Request\Async;
use WeCodeArt\Admin\Installer\Plugin;
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
    protected $action = 'manage_plugins';

    /**
     * Overwrite parent handle to remove some output
     */
    public function maybe_handle() {
        // Remove unnecessary output
		remove_all_actions( 'upgrader_pre_install' );
		remove_all_actions( 'upgrader_process_complete' );
		remove_all_actions( 'upgrader_post_install' );
		// remove_all_actions( 'upgrader_source_selection' );
		// remove_all_actions( 'upgrader_overwrote_package' );

        parent::maybe_handle();
    }

    /**
     * Handle
     * 
     * @return  mixed
     */
    protected function handle() {
        $type       = sanitize_text_field( get_prop( $_POST, 'type', '' ) );
        $plugins    = array_map( function( $plugin ) {
            return array_map( 'sanitize_text_field', wp_array_slice_assoc( $plugin, [ 'slug', 'source' ] ) );
        }, json_decode( stripslashes( get_prop( $_POST, 'plugins', '{}' ) ), true ) );

		if ( empty( $plugins ) ) {
            wp_send_json_success( [
                'message'   => esc_html__( 'Plugins array is empty.', 'wecodeart' ),
                'installed' => []
            ] );
        }

        switch( $type ) :
            case 'install':
                $data = self::install( $plugins );
            break;
            case 'activate':
                $data = self::activate( $plugins );
            break;
            case 'deactivate':
                $data = self::deactivate( $plugins );
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
     * Install plugins
     *
     * @param   array   $plugins    Array of plugins slugs/sources
     * 
     * @return  array
     */
    public static function install( array $plugins = [] ): array {
        $message    = '';
        $success    = [];
        $failed     = [];

        foreach( $plugins as $plugin ) {
            $installer = new Plugin( $plugin );
            $installer->install();

            if( $installer->ok() ) {
                $success[] = $plugin;
            } else {
                $failed[] = $plugin;
            }
        }

        if( count( $success ) === count( $plugins ) ) {
            $message = esc_html__( 'All plugins installed successfully.', 'wecodeart' );
        }

        if( count( $success ) === 1 ) {
            $message = esc_html__( 'Plugin installed successfully.', 'wecodeart' );
        }

        if( count( $success ) === 0 ) {
            $message = esc_html__( 'Plugin failed to install.', 'wecodeart' );
        }
        
        if( count( $success ) && count( $failed ) ) {
            $message = esc_html__( 'Some plugins installed successfully, others have failed.', 'wecodeart' );
        }
        
        if( count( $failed ) === count( $plugins ) > 1 ) {
            $message = esc_html__( 'All plugins have failed to install.', 'wecodeart' );
        }

        return [
            'message'   => $message,
            'success'   => $success,
            'failed'    => $failed,
        ];
    }
    
    /**
     * Activate plugins
     *
     * @param   array   $plugins    Array of plugins slugs/sources
     * 
     * @return  array
     */
    public static function activate( array $plugins = [] ): array {
        $message    = '';
        $success    = [];
        $failed     = [];

        foreach( $plugins as $plugin ) {
            $status = self::toggle_plugin_activation( get_prop( $plugin, 'slug', '' ) );
            if( $status ) {
                $success[] = $plugin;
            } else {
                $failed[] = $plugin;
            }
        }

        if( count( $success ) === count( $plugins ) ) {
            $message = esc_html__( 'All plugins activated successfully.', 'wecodeart' );
        }

        if( count( $success ) === 1 ) {
            $message = esc_html__( 'Plugin activated successfully.', 'wecodeart' );
        }

        if( count( $success ) === 0 ) {
            $message = esc_html__( 'Plugin failed to activate.', 'wecodeart' );
        }
        
        if( count( $success ) && count( $failed ) ) {
            $message = esc_html__( 'Some plugins activated successfully, others have failed.', 'wecodeart' );
        }

        if( count( $failed ) === count( $plugins ) > 1 ) {
            $message = esc_html__( 'All plugins have failed to activate.', 'wecodeart' );
        }

        return [
            'message'   => $message,
            'success'   => $success,
            'failed'    => $failed,
        ];
    }

    /**
     * Activate plugins
     *
     * @param   array   $plugins    Array of plugins slugs/sources
     * 
     * @return  array
     */
    public static function deactivate( array $plugins = [] ): array {
        $message    = '';
        $success    = [];
        $failed     = [];

        foreach( $plugins as $plugin ) {
            $status = self::toggle_plugin_activation( get_prop( $plugin, 'slug', '' ), 'deactivate' );
            if( $status ) {
                $success[] = $plugin;
            } else {
                $failed[] = $plugin;
            }
        }

        if( count( $success ) === count( $plugins ) ) {
            $message = esc_html__( 'All plugins deactivated successfully.', 'wecodeart' );
        }

        if( count( $success ) === 1 ) {
            $message = esc_html__( 'Plugin deactivated successfully.', 'wecodeart' );
        }

        if( count( $success ) === 0 ) {
            $message = esc_html__( 'Plugin failed to deactivate.', 'wecodeart' );
        }
        
        if( count( $success ) && count( $failed ) ) {
            $message = esc_html__( 'Some plugins deactivated successfully, others have failed.', 'wecodeart' );
        }

        if( count( $failed ) === count( $plugins ) > 1 ) {
            $message = esc_html__( 'All plugins have failed to deactivate.', 'wecodeart' );
        }

        return [
            'message'   => $message,
            'success'   => $success,
            'failed'    => $failed,
        ];
    }

    /**
     * Activate/Deactivate plugins
     *
     * @param   string   $plugin_dir    Plugin directory
     * @param   string   $action        Activate/Deactivate
     * 
     * @return  array
     */
    public static function toggle_plugin_activation( string $plugin_dir = '', string $action = 'activate' ) {
        $plugins    = get_plugins();
        $status     = null;

        foreach ( $plugins as $plugin_file => $plugin_info ) {
            if ( dirname( $plugin_file ) === $plugin_dir ) {
                switch( $action ) :
                    case 'activate': 
                        $status = activate_plugin( $plugin_file, false, false, false );
                        break;
                    case 'deactivate': 
                        $status = deactivate_plugins( $plugin_file, true );
                        break;
                endswitch;
                // Break Loop
                break;
            }
        }

        return is_wp_error( $status ) ? false : true;
    }
}