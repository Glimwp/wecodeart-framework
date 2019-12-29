<?php
/**
 * WeCodeArt Framework Setup.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework.
 * @subpackage  Setup
 * @copyright   Copyright (c) 2019, WeCodeArt Framework
 * @since		3.9.5
 * @version		4.1.1
 */

/**
 * Bind Config.
 *
 * @param   array   $config     - configuration object passed earlier.
 * @return  void
 */
wecodeart()->bind( 'config', function () use ( $config ) {
    return new WeCodeArt\Config( $config );
} );

/**
 * Bind Conditionals.
 *
 * @since   4.0.1
 *
 * @param   array   $conditionals   - configuration object passed earlier.
 * @return  void
 */
wecodeart()->bind( 'conditionals', function () {
    return WeCodeArt\Conditional::get_instance();
} );

/**
 * Bind Integrations.
 *
 * @since   4.1.0
 *
 * @param   array   $conditionals   - configuration object passed earlier.
 * @return  void
 */
wecodeart()->bind( 'integrations', function () {
    return WeCodeArt\Support::get_instance();
} );

/**
 * Bind Layout.
 *
 * @param   array   $parameters  Array of strings/functions - accepts `header/content/footer` and callable function as keys
 *
 * @return  void
 */
wecodeart()->bind( 'layout', function ( WeCodeArt $theme, $parameters ) {
    if( empty( $parameters ) ) {
        $parameters = [ 'header', 'content', 'footer' ];
    }

    array_map( function( $partial ) {
        switch( $partial ) {
            case 'header' :
                /**
                 * @see - https://developer.wordpress.org/reference/functions/get_header/
                 */
                get_header();
                break;

            case 'content' :
                /**
                 * @see - WeCodeArt\Core\Content::render_modules();
                 */
                do_action( 'wecodeart_inner_markup' );
                break;

            case 'footer' :
                /**
                 * @see - https://developer.wordpress.org/reference/functions/get_footer/
                 */
                get_footer();
                break;

            default: 
                return is_callable( $partial ) && call_user_func( $partial );
        }
    }, $parameters );
} );

/**
 * Bind ThemeName.
 *
 * @since   3.9.5
 *
 * @return string
 */
wecodeart()->bind( 'name', function () {
    static $handle;

	if ( is_null( $handle ) ) {
		if ( defined( 'CHILD_THEME_NAME' ) && CHILD_THEME_NAME ) {
			$handle = sanitize_title_with_dashes( CHILD_THEME_NAME );
		} else {
			$handle = sanitize_title_with_dashes( wp_get_theme()->get( 'Name' ) );
		}
	}

	return $handle;
} );

/**
 * Bind Version.
 *
 * @since   3.9.5
 * @version 4.0.1
 *
 * @return string
 */
wecodeart()->bind( 'version', function () {
    if ( wecodeart_if( 'is_dev_mode' ) ) {
		return (string) time();
	}

	static $version;

	if ( is_null( $version ) ) {
		if ( defined( 'CHILD_THEME_VERSION' ) && CHILD_THEME_VERSION ) {
			$version = CHILD_THEME_VERSION;
		} else {
			$version = wp_get_theme()->get( 'Version' );
		}
	}

	return $version;
} );

/**
 * Bind Register Sidebars.
 *
 * @since   3.9.5
 *
 * @param   array $parameters
 *
 * @return  void
 */
wecodeart()->bind( 'register_sidebars', function( WeCodeArt $theme, $parameters ) {
    if( empty( $parameters ) ) return null;

    $can_run = array_map( function( $item ) {
        return ( isset( $item['name'] ) || isset( $item['label'] ) );
    }, $parameters );

    if( count( array_unique( $can_run ) ) !== 1  ) {
        _doing_it_wrong(
            __FUNCTION__, 
            esc_html__( 'Please define a name for each of the sidebars.', wecodeart_config( 'textdomain' ) ),
            '3.9.5'
        );
        return null;
    }

    foreach( $parameters as $key => $sidebar ) {
        $label  = isset( $sidebar['name'] ) ? $sidebar['name'] : $sidebar['label'];
        $desc   = isset( $sidebar['description'] ) ? esc_html( $sidebar['description'] ) : false;
        $class  = isset( $sidebar['class'] ) ? $sidebar['class'] : $key;
        // We can continue now.
        register_sidebar( [
            'id'            => esc_attr( isset( $sidebar['id'] ) ? $sidebar['id'] : $key ),
            'name'          => esc_html( $label ),
            'class'         => sanitize_html_class( $class, 'wecodeart-sidebar' ),
            'description'   => $desc ?: sprintf( esc_html__( 'This is the %s.', wecodeart_config( 'textdomain' ) ), $label ),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4 class="widget__title">',
            'after_title'   => '</h4>',
        ] );
    }
} );

/**
 * Bind Public Posts.
 *
 * @since   3.9.5
 * @version 4.1.1
 *
 * @param   array $parameters
 *
 * @return  array
 */
wecodeart()->factory( 'public_post_types', function( WeCodeArt $theme, $parameters ) {
    return get_post_types( wp_parse_args( $parameters, [ 
        'public' 				=> true,
        'publicly_queryable' 	=> true
    ] ) );
} );