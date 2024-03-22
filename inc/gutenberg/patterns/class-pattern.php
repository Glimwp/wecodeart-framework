<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg Pattern
 * @copyright   Copyright (c) 2024, WeCodeArt Framework
 * @since		5.0.0
 * @version		6.3.7
 */

namespace WeCodeArt\Gutenberg\Patterns;

defined( 'ABSPATH' ) || exit;

/**
 * Class representing a block pattern.
 */
class Pattern {
    /**
	 * Namespace: wecodeart
	 *
	 * @var string
	 */
    const NAMESPACE = 'wecodeart';

	/**
	 * Container of valid json properties.
	 *
	 * @var array
	 */
	const VALID_PROPERTIES = [
		'slug',
		'title',
		'content',
		'description',
		'categories',
		'viewportWidth',
		'blockTypes',
		'postTypes',
		'templateTypes',
		'inserter',
		'theme'
	];

	/**
	 * Template slug.
	 *
	 * @var string
	 */
	public $slug;

	/**
	 * Title.
	 *
	 * @var string
	 */
	public $title = '';

	/**
	 * Content.
	 *
	 * @var string
	 */
	public $content = '';

	/**
	 * Description.
	 *
	 * @var string
	 */
	public $description = '';

    /**
	 * Viewport Width.
	 *
	 * @var int
	 */
	public $viewportWidth;

	/**
	 * Keywords.
	 *
	 * @var array
	 */
	public $keywords = [];

    /**
	 * Categories.
	 *
	 * @var array
	 */
	public $categories = [];
    
	/**
	 * Block Types.
	 *
	 * @var array
	 */
	public $blockTypes = [];

	/**
	 * Post Types.
	 *
	 * @var array
	 */
	public $postTypes = [];

	/**
	 * Template Types.
	 *
	 * @var array
	 */
	public $templateTypes = [];

	/**
	 * Inserter.
	 *
	 * @var boolean
	 */
	public $inserter = true;

	/**
	 * Theme.
	 *
	 * @var string
	 */
	public $theme;

	/**
	 * Source of the content.
	 *
	 * @var string
	 */
	public $source = 'theme';

	/**
	 * Construct template.
	 *
	 * @param 	array	$args	Pattern attributes
	 */
	public function __construct( $args = [] ) {
		$valid = wp_array_slice_assoc( $args, self::VALID_PROPERTIES );
		$valid = self::translate( self::sanitize( $valid ) );

		foreach( $valid as $key => $value ) $this->{ $key } = $value;
	}

    /**
     * Register Template
     *
     * @return void
     */
    public function register() {
		if ( \WP_Block_Patterns_Registry::get_instance()->is_registered( $this->get_name() ) || empty( $this->content )) return;

		$args = array_filter( [
            'title'       	=> $this->title,
            'content'     	=> $this->content,
            'categories'  	=> $this->categories,
            'description' 	=> $this->description,
            'keywords' 		=> $this->keywords,
            'viewportWidth'	=> $this->viewportWidth,
			'blockTypes' 	=> $this->blockTypes,
			'postTypes' 	=> $this->postTypes,
			'templateTypes'	=> $this->templateTypes,
			'blockTypes' 	=> $this->blockTypes,
			'inserter' 		=> $this->inserter,
        ], fn( $v ) => is_bool( $v ) || ! empty( $v ) || $v === 0 );

        register_block_pattern( $this->get_name(), $args );
    }

	/**
     * Get Name
     *
     * @return string
     */
	public function get_name() {
		return join( '/', [ self::NAMESPACE, $this->slug ] );
	}

	/**
     * Sanitize
	 *
	 * @param	array $json
     *
     * @return 	array
     */
	private static function sanitize( $json = [] ) {
		$sanitized = [];

		foreach( $json as $key => $value ) {
			switch( $key ) :
				// Strings
				case 'slug':
				case 'title':
				case 'theme':
				case 'description':
					$sanitized[$key] = sanitize_text_field( $value );
				break;
				// Number
				case 'viewportWidth':
					$sanitized[$key] = absint( $value );
				break;
				// Arrays
				case 'keywords':
				case 'categories':
				case 'blockTypes':
				case 'postTypes':
				case 'templateTypes':
					$sanitized[$key] = array_map( 'sanitize_text_field', $value );
				break;
				// Boolean
				case 'inserter':
					$sanitized[$key] = (bool) $value;
				break;
				// Content
				case 'content':
					$sanitized[$key] = serialize_blocks( parse_blocks( $value ) );
				break;
				default:
					$sanitized[$key] = $value;
				break;
			endswitch;
		}

		return $sanitized;
	}

	/**
     * Translate
	 *
	 * @param	array $json
     *
     * @return 	array
     */
	private static function translate( $json = [] ) {
		return translate_settings_using_i18n_schema( (object) [
			'title' 		=> 'pattern title',
			'description' 	=> 'pattern description',
		], $json, wp_get_theme()->get( 'TextDomain' ) );
	}
}
