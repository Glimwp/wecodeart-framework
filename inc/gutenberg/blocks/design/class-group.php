<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg\Blocks
 * @copyright   Copyright (c) 2024, WeCodeArt Framework
 * @since		6.0.0
 * @version		6.3.7
 */

namespace WeCodeArt\Gutenberg\Blocks\Design;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\{ get_prop, dom_element, dom_get_element, dom_create_element };

/**
 * Gutenberg Group blocks.
 */
class Group extends Dynamic {

	use Singleton;

	/**
	 * Block namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'core';

	/**
	 * Block name.
	 *
	 * @var string
	 */
	protected $block_name = 'group';

	/**
	 * Init.
	 */
	public function init() {
		\add_action( 'enqueue_block_editor_assets', [ $this, 'register_variation' ], 30, 1 );
	}

	/**
	 * Block args.
	 *
	 * @param	array $current	Existing register args
	 *
	 * @return 	array
	 */
	public function block_type_args( $current ): array {
		$attributes	= get_prop( $current, [ 'attributes' ], [] );
		$supports 	= get_prop( $current, [ 'supports' ], [] );

		return [
			'render_callback' 	=> [ $this, 'render' ],
			'supports'			=> wp_parse_args( [
				'shadow'		=> true, 
			], $supports ),
			'attributes' 		=> wp_parse_args( [
				'namespace'		=> [
					'type'		=> 'string',
				],
			], $attributes )
		];
	}

	/**
	 * Dynamically renders the `core/group` block.
	 *
	 * @param 	string 	$attributes 	The block attrs.
	 * @param 	string 	$content 		The block markup.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( array $attributes = [], string $content = '' ): string {
		$content 	= new \WP_HTML_Tag_Processor( $content );

		static $processed = null;
		// Handle <main /> tag (once).
		if( $content->next_tag( [ 'tag_name' => 'main' ] ) && is_null( $processed ) ) {
			$content->set_attribute( 'class', 'wp-site-main ' . $content->get_attribute( 'class' ) );
			$processed = true;
		}

		// Handle marquee group.
		if( self::is_marquee_variation( $attributes ) ) {
			$content = $this->create_marquee( $attributes, $content );
			wp_add_inline_style( $this->get_asset_handle(), self::marquee_styles() );
		}
	
		return (string) $content;
	}

	/**
	 * Registers variation.
	 *
	 * @return 	string
	 */
	public function register_variation(): void {
		wp_add_inline_script( 'wecodeart-gutenberg', <<<JS
			const {
				domReady,
				i18n: { __ },
				blocks: { registerBlockVariation },
			} = wp;

			domReady(() => registerBlockVariation('core/group', {
				name: 'wecodeart/group/marquee',
				title: __('Group: Marquee', 'wecodeart'),
				description: __('Gather blocks in a sliding container.', 'wecodeart'),
				icon: 'align-right',
				isDefault: false,
				isActive: ['namespace'],
				scope: ['block', 'inserter'],
				attributes: {
					namespace: 'wecodeart/group/marquee',
					layout: {
						type: 'flex',
						flexWrap: 'nowrap',
						justifyContent: 'center',
						alignItems: 'center'
					}
				},
			}));
		JS );
	}

	/**
	 * Determines whether the block is a marquee block.
	 *
	 * @param 	array 	$attributs The block attrs.
	 *
	 * @return 	bool 	Whether the block is a marquee block.
	 */
	public static function is_marquee_variation( array $attributes = [] ): bool {
		if ( get_prop( $attributes, [ 'namespace' ], '' ) === 'wecodeart/group/marquee' ) {
			return true;
		}

		return false;
	}

	/**
	 * Generate marquee markup.
	 *
	 * @param 	string 	$attributes 	The block attrs.
	 * @param 	string 	$content 		The block markup.
	 *
	 * @return 	string 	The block updated markup.
	 */
	public function create_marquee( $attributes, $content ) {
		$dom 	= $this->dom( (string) $content );
		$div 	= dom_get_element( 'div', $dom );
		
		$wrap	= dom_create_element( 'div', $dom );
		$wrap->setAttribute( 'class', 'wp-block-group__marquee' );
		
		$styles 		= [];
		$orientation 	= get_prop( $attributes, [ 'layout', 'orientation' ] );
		$directionY 	= get_prop( $attributes, [ 'layout', 'verticalAlignment' ] );
		$directionX 	= get_prop( $attributes, [ 'layout', 'justifyContent' ] );

		// Handle direction
		if( ( $orientation === 'vertical' && $directionY === 'bottom' ) || ( $orientation === 'horizontal' && $directionX === 'right' ) ) {
			$styles[] = '--marquee-direction:reverse';
		}

		// Handle alternate
		if( ( $orientation === 'vertical' && $directionY === 'space-between' ) || ( $orientation === 'horizontal' && $directionX === 'space-between' ) ) {
			$styles[] = '--marquee-direction:alternate';
		}

		// Handle pause state
		if( ( $orientation === 'vertical' && $directionY === 'center' ) || ( $orientation === 'horizontal' && $directionX === 'center' ) ) {
			$styles[] = '--marquee-state:paused';
		}

		// Handle gap
		if( $blockGap = get_prop( $attributes, [ 'style', 'spacing', 'blockGap' ] ) ) {
			$styles[] = '--marquee-gap:' . wecodeart( 'styles' )::format_variable( $blockGap );
		}

		if( ! empty( $styles ) ) {
			$div->setAttribute( 'style', join( ';', $styles ) );
		}
		
		$count 	= $div->childNodes->count();
		for ( $i = 0; $i < $count; $i++ ) {
			$item = $div->childNodes->item( $i );

			if ( ! $item || ! method_exists( $item, 'setAttribute' ) ) {
				continue;
			}

			$wrap->appendChild( $item );
		}

		for ( $j = 0; $j < 2; $j++ ) {
			$clone = dom_element( $wrap->cloneNode( true ) );

			if ( ! $clone ) {
				continue;
			}

			$clone->setAttribute( 'aria-hidden', 'true' );
			$classes   = explode( ' ', $clone->getAttribute( 'class' ) );
			$classes[] = 'is-cloned';
			$clone->setAttribute( 'class', implode( ' ', $classes ) );
			$div->appendChild( $clone );
		}

		$div->insertBefore( $wrap, $div->firstChild );
		$div->setAttribute( 'class', $div->getAttribute( 'class' ) . ' wp-block-group--marquee' );

		$content = $dom->saveHTML();

		return $content;
	}

	/**
	 * Marquee styles
	 *
	 * @return 	string 	The block styles.
	 */
	public static function marquee_styles() {
		$mobile 	= wecodeart_json( [ 'settings', 'custom', 'mobileBreakpoint' ], 'lg' );
		$breakpoint = wecodeart_json( [ 'settings', 'custom', 'breakpoints', $mobile ], '992px' );

		$inline = "
			.wp-block-group--marquee {
				--marquee-animation: __marquee;
				--marquee-speed: 50s;
				--marquee-speed-mobile: calc(var(--marquee-speed) / 2);
				--marquee-state: running;
				--marquee-direction: forwards;
				--marquee-gap: var(--wp--style--block-gap);
				--marquee-transform: translate3d(calc(-100% - var(--marquee-gap)),0,0);
				--marquee-repeat: infinite;
				justify-content: initial /* required */;
				flex-wrap: nowrap /* required */;
				max-width: 100vw;
				overflow: hidden;
			}
			.wp-block-group--marquee.is-vertical { 
				--marquee-transform: translate3d(0,calc(-100% - var(--marquee-gap)),0);
			}
			.wp-block-group--marquee:is(.scroll.scroll) {
				--marquee-state: initial;
				--marquee-repeat: initial;
				--marquee-timeline: scroll(root);
			}
			.wp-block-group--marquee > .wp-block-group__marquee {
				position: relative;
				display: flex;
				flex-direction: inherit;
				flex-shrink: 0;
				gap: inherit;
				min-height: 1em;
				transform: translate3d(0,0,0);
				animation-timeline: var(--marquee-timeline, initial);
				animation-name: var(--marquee-animation);
				animation-timing-function: linear;
				animation-duration: var(--marquee-speed-mobile);
				animation-direction: var(--marquee-direction);
				animation-play-state: var(--marquee-state);
				animation-iteration-count: var(--marquee-repeat);
			}
			.wp-block-group--marquee:is(:hover,:focus,:focus-within) {
				--marquee-state: paused;
			}
			@media (min-width: {$breakpoint}) {
				.wp-block-group--marquee > .wp-block-group__marquee {
					animation-duration: var(--marquee-speed);
				}
			}
			@media (prefers-reduced-motion) {
				.wp-block-group--marquee {
					--marquee-state: paused;
				}
			}
			@keyframes __marquee {
				to {
					transform: var(--marquee-transform);
				}
			}
		";

		$inline = wecodeart( 'styles' )::compress( $inline );

		return $inline;
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles() {
		return <<<CSS
			.wp-block-group {
				box-sizing: border-box;
			}
		CSS;
	}
}
