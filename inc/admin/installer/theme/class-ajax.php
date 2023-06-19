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

namespace WeCodeArt\Admin\Installer\Theme;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Admin\Request\Async;
use WeCodeArt\Admin\Installer\Theme;
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
    protected $action = 'manage_themes';

    /**
     * Handle
     * 
     * @return  mixed
     */
    protected function handle() {
        $type   = sanitize_text_field( get_prop( $_POST, 'type', '' ) );
        $data   = json_decode( stripslashes( get_prop( $_POST, 'data', '{}' ) ), true );
        $data   = wp_array_slice_assoc( $data, [ 'slug', 'source', 'name', 'email', 'url', 'message' ] );
        $data   = array_map( 'sanitize_text_field', $data ); // Basic Sanitization

		if ( empty( $data ) ) {
            wp_send_json_success( [
                'message'   => esc_html__( 'Theme is not defined.', 'wecodeart' ),
                'installed' => false
            ] );
        }

        switch( $type ) :
            case 'install':
                $data = self::install( $data );
            break;
            case 'activate':
                $data = self::activate( $data );
            break;
            case 'deactivate':
                $data = self::deactivate( $data );
            break;
            case 'submit':
                $data = self::submit( $data );
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
     * Install theme
     *
     * @param   array   $theme   Theme data
     * 
     * @return  array
     */
    public static function install( array $theme = [] ): array {
        $message    = esc_html__( 'Theme failed to install.', 'wecodeart' );
        $success    = false;

        // Remove unnecessary output
		remove_all_actions( 'upgrader_pre_install' );
		remove_all_actions( 'upgrader_process_complete' );
		remove_all_actions( 'upgrader_post_install' );
		// remove_all_actions( 'upgrader_source_selection' );
		// remove_all_actions( 'upgrader_overwrote_package' );
        
        $installer = new Theme( $theme );
        $installer->install();

        if( $installer->ok() === null ) {
            $message = esc_html__( 'Theme package not found.', 'wecodeart' );
        }

        if( $installer->ok() ) {
            $success = $theme;
        }

        if( $success ) {
            $message = esc_html__( 'Theme installed successfully.', 'wecodeart' );
        }

        return [
            'message'   => $message,
            'success'   => $success,
        ];
    }
    
    /**
     * Activate theme
     *
     * @param   array   $theme  Theme to activate
     * 
     * @return  array
     */
    public static function activate( array $theme = [] ): array {
        $message    = esc_html__( 'Failed to switch theme.', 'wecodeart' );
        $success    = false;
        $slug       = get_prop( $theme, 'slug', '' );
        $theme      = wp_get_theme( $slug );

        if( $theme->exists() ) {
            switch_theme( $theme->get_stylesheet() );
            $success = true;
            $message = esc_html__( 'Theme switched.', 'wecodeart' );
        }

        return [
            'message'   => $message,
            'success'   => $success,
        ];
    }

    /**
     * Deactivate theme
     *
     * @param   array   $theme  Theme to deactivate
     * 
     * @return  array
     */
    public static function deactivate( array $theme = [] ): array {
        $message    = esc_html__( 'Failed to switch theme.', 'wecodeart' );
        $success    = false;

        $theme = wp_get_theme( 'wecodeart' );

        if( $theme->exists() ) {
            switch_theme( $theme->get_stylesheet() );
            $success = true;
            $message = esc_html__( 'Theme switched to Core Framework.', 'wecodeart' );
        }
        
        return [
            'message'   => $message,
            'success'   => $success,
        ];
    }
    
    /**
     * Submit theme
     *
     * @param   array   $data  Submission data
     * 
     * @return  array
     */
    public static function submit( array $data = [] ): array {
        $name = sanitize_text_field( get_prop( $data, 'name', '' ) );
        $mail = sanitize_email( get_prop( $data, 'email', '' ) );
        $link = esc_url_raw( get_prop( $data, 'url', '' ) );
        $info = sanitize_text_field( get_prop( $data, 'message', '' ) );
        
        $to = 'to_email@example.com';
        $subject = 'New Theme Submission';
        $message = "Name: $name\nEmail: $mail\nTheme URL: $link\nMessage: $info";
    
        $sent = wp_mail( 'contact@wecodeart.com', $subject, $message, [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $name . ' <' . $mail . '>',
        ] );
    
        if ( $sent ) {
            $message = esc_html__( 'Your submission was successfully sent. We well get back to you shortly.', 'wecodeart' );
        } else {
            $message = esc_html__( 'Failed to send your submission.', 'wecodeart' );
        }
        
        return [
            'message'   => $message,
            'success'   => $data,
        ];
    }
}