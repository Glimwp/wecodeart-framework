<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Entry\Meta\Date
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since 		3.9.5
 * @version		5.2.4
 */

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Markup\SVG;
use function WeCodeArt\Functions\get_prop;

$classnames = [ 'wp-block-post-date' ];

if( isset( $modified ) ) {
    $classnames[] = 'wp-block-post-date--updated';
}

if( $value = get_prop( $attributes, 'textAlign', false ) ) {
    $classnames[] = 'has-text-align-' . $value;
}

if( $value = get_prop( $attributes, 'className', false ) ) {
    $classnames[] = $value;
}

?>
<div class="<?php echo esc_attr( implode( ' ', $classnames ) ); ?>"><?php

    SVG::render( 'clock', [
        'class' => 'wp-block-post-date__icon'
    ] );

    ?>
    <span class="screen-reader-text"><?php

        esc_html_e( 'Posted on ', 'wecodeart' );
    
    ?></span>
    <?php if ( get_prop( $attributes, [ 'isLink' ], false ) ) : ?>
    <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="wp-block-post-date__link me-2">
    <?php endif; ?>
        <time class="wp-block-post-date__published"
            <?php if( isset( $modified ) ) : ?>
            style="display:none;"
            <?php endif; ?>
            datetime="<?php echo esc_attr( $published['robot'] ); ?>"><?php

            echo esc_html( $published['human'] );

        ?></time>
        <?php if( isset( $modified ) ) : ?>
        <time class="wp-block-post-date__updated"
            datetime="<?php echo esc_attr( $modified['robot'] ); ?>"
            title="<?php echo esc_attr( sprintf(
                esc_html__( 'Post updated on %s.', 'wecodeart' ),
                esc_html( $modified['human'] )
            ) ); ?>"><?php

            echo esc_html( $modified['human'] );

        ?></time>
        <?php endif; ?>
    <?php if ( get_prop( $attributes, [ 'isLink' ], false ) ) : ?>
    </a>
    <?php endif; ?>
</div>