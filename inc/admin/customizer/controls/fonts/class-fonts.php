<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Customizer\Controls\Fonts
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since 		5.0.0
 * @version		5.0.0
 */

namespace WeCodeArt\Admin\Customizer\Controls;

defined( 'ABSPATH' ) || exit;

use WP_Customize_Control;
use WeCodeArt\Core\Scripts;
use WeCodeArt\Admin\Request;
use WeCodeArt\Support\Fonts as The_Fonts;

/**
 * Fonts.
 */
class Fonts extends \WP_Customize_Control {

	use Scripts\Base;

	/**
	 * The control type.
	 *
	 * @access 	public
	 * @var 	string
	 */
	public $type = 'wecodeart-fonts';

	/**
	 * Enqueue control related scripts/styles.
	 *
	 * @access public
	 *
	 * @since 	unknown
	 * @version	5.0.0
	 */
	public function enqueue() {
		wp_enqueue_style(
			$this->make_handle(),
			$this->get_asset( 'css', 'customizer-fonts' ),
			[ 'wp-components' ],
			wecodeart( 'version' )
		);

		$deps = [ 'lodash', 'react', 'react-dom', 'wp-components', 'wp-element', 'wp-i18n', 'wp-polyfill' ];
		
		wp_enqueue_script(
            $this->make_handle(),
			$this->get_asset( 'js', 'customizer-fonts' ),
           	array_merge( $deps, [ 'jquery', 'customize-base' ] ),
			wecodeart( 'version' ),
            true
		);

		wp_localize_script( $this->make_handle(), 'wecodeartFontsControl', [
			'typekit' 	=> [],
			'fonts' 	=> [
				'System' => The_Fonts::get_standard_fonts(),
				'Google' => The_Fonts::get_google_fonts(),
			]
		] );

		wp_enqueue_style(
			$this->make_handle( 'fonts' ),
			add_query_arg( [
				'family' 	=> implode( '|', wp_list_pluck( The_Fonts::get_google_fonts(), 'family' ) ),
				'text'		=> 'Abc',
				'display' 	=> 'swap',
			], '//fonts.googleapis.com/css' ),
			[],
			wecodeart( 'version' )
		);
	}

	/**
	 * Send to JS.
	 */
	public function to_json() {
		parent::to_json();
		$this->json['default'] 		= $this->setting->default;
		$this->json['inputAttrs'] 	= wp_json_encode( $this->input_attrs );
	}
}
