<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Footer Template
 * @since  		1.0.0
 * @version 	3.8.1
 */

        do_action( 'wecodeart/hook/inner/bottom' );     // Hook Inner Bottom

        echo '</div>';

        if( WP_DEBUG === true ) {
            ?>
            <!-- /.content @filter = `wecodeart/filter/attributes/content` -->
            <?php 
        }

        do_action( 'wecodeart/hook/inner/after' ); 	    // Hook Inner After.

        do_action( 'wecodeart/hook/footer/before' );    // Hook Before Footer.

        /**
         * @see WeCodeArt\Core\Footer->footer_markup();
         */
        do_action( 'wecodeart_footer_markup' );

        do_action( 'wecodeart/hook/footer/after' );	// Hook After Footer.
        
        /**
         * WP Footer
         */
        wp_footer(); 
    ?>
    </body>
</html>