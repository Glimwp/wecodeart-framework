<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Notification Template
 * @since 		3.8.1
 * @version		5.5.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * @param   array  	$attributes Notification attributes
 * @param   string	$message   	Notification message
 */

?>
<div <?php echo wecodeart( 'markup' )::generate_attr( 'admin-notification', $attributes ); // WPCS ok - attributes escaped with fn above. ?>>
	<div class="wca-notice__branding"><?php

		printf( '<img style="margin:10px;" src="%s" />', wecodeart_config( 'paths' )['uri'] . '/assets/images/logo.png' );
 
	?></div>
	<div class="wca-notice__container" style="padding-left: 75px;"><?php

		echo wp_kses_post( $message );
 
	?></div>
</div>
<!-- /wecodeart-notification -->