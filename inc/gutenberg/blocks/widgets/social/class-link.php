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
 * @since		6.2.9
 * @version		6.2.9
 */

namespace WeCodeArt\Gutenberg\Blocks\Widgets\Social;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Social block.
 */
class Link extends Dynamic {

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
	protected $block_name = 'social-link';

	/**
	 * Block args.
	 *
	 * @param	array $current	Existing register args
	 *
	 * @return 	array
	 */
	public function block_type_args( $current ): array {
		$context	= get_prop( $current, [ 'uses_context' ], [] );
		$context[]  = 'namespace';

		return [
			'render_callback' 	=> [ $this, 'render' ],
			'uses_context' 		=> $context,
		];
	}

	/**
	 * Dynamically renders the `core/social-link` block.
	 *
	 * @param 	array 	$attributes	The block attributes.
	 * @param 	string 	$content 	The block markup.
	 * @param 	array 	$block 		The parsed block.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( array $attributes = [], string $content = '', object $block = null ): string {
		$is_sharing	= wecodeart( 'blocks' )->get( 'core/social-links' )::is_sharing_variation( $block->context );

		if ( $is_sharing ) {
			$has_url	= get_prop( $attributes, [ 'url' ] );
			$attributes['url'] = $has_url ?: '#';
		}

		// Core render.
		$content	= render_block_core_social_link( $attributes, $content, $block );
		
		if ( $is_sharing ) {
			$service 	= get_prop( $attributes, [ 'service' ], '' );
			$content 	= new \WP_HTML_Tag_Processor( $content );

			$window_title = esc_html__( 'Share the post!', 'wecodeart' );

			if( $content->next_tag( [
				'tag_name' => 'A'
			] ) ) {
				$content->set_attribute( 'href', self::get_sharing_url( $service ) );
				$content->set_attribute( 'rel', 'noopener noreferrer nofollow' );
				$content->set_attribute( 'aria-label', $window_title );
				$content->set_attribute( 'title', sprintf( esc_html__( 'Share on %s', 'wecodeart' ), ucfirst( $service ) ) );

				if( $content->get_attribute( 'target' ) === '_blank' ) {
					if( $service === 'mail' ) {
						$content->remove_attribute( 'target' );
	
						return (string) $content;
					}

					$content->set_attribute( 'onclick', "return !window.open(this.href, '{$window_title}', 'width=500,height=500')" );
				}
			}
		}

		return (string) $content;
	}

	/**
	 * Get social network sharing url
	 *
	 * @param 	string 	$service The service.
	 *
	 * @return 	string
	 */
	public static function get_sharing_url( string $service = '' ): string {
		$sharing_url = '';
		$network_args= [];
		$current_url = get_permalink();
		$current_ttl = get_the_title();
		$current_txt = get_the_excerpt();
		$current_img = get_the_post_thumbnail_url();

		switch( $service ):
			case 'wordpress':
				$sharing_url 	= 'https://wordpress.com/press-this.php';
				$network_args 	= [
					'u'	=> $current_url,
					't'	=> $current_ttl,
					's'	=> $current_txt,
					'i'	=> $current_img
				];
			break;
			case 'facebook':
				$sharing_url 	= 'https://www.facebook.com/sharer/sharer.php';
				$network_args 	= [
					'u' => $current_url
				];
			break;
			case 'x':
			case 'twitter':
				$sharing_url 	= 'https://twitter.com/intent/tweet';
				$network_args 	= [
					'url' 	=> $current_url,
					'text'	=> $current_txt
				];
			break;
			case 'linkedin':
				$sharing_url 	= 'https://www.linkedin.com/shareArticle';
				$network_args 	= [
					'mini'	=> 1,
					'url' 	=> $current_url,
					'title'	=> $current_ttl,
					'source'	=> home_url( '/' ),
					'summary'	=> $current_txt
				];
			break;
			case 'whatsapp':
				$sharing_url 	= 'https://api.whatsapp.com/send';
				$network_args 	= [
					'text'	=> $current_ttl . ' ' . $current_url
				];
			break;
			case 'telegram':
				$sharing_url 	= 'https://telegram.me/share/url';
				$network_args 	= [
					'url'	=> $current_url,
					'text'	=> $current_txt
				];
			break;
			case 'pinterest':
				$sharing_url 	= 'https://pinterest.com/pin/create/button';
				$network_args 	= [
					'url' 	=> $current_url,
					'media'	=> $current_img,
					'description'	=> $current_txt
				];
			break;
			case 'reddit':
				$sharing_url 	= 'https://www.reddit.com/submit';
				$network_args 	= [
					'url'	=> $current_url,
				];
			break;
			case 'tumblr':
				$sharing_url 	= 'https://www.tumblr.com/widgets/share/tool';
				$network_args 	= [
					'canonicalUrl'	=> $current_url,
					'title'			=> $current_ttl,
					'caption'		=> $current_txt
				];
			break;
			case 'pocket':
				$sharing_url 	= 'https://getpocket.com/edit';
				$network_args 	= [
					'url'	=> $current_url,
				];
			break;
			case 'vk':
				$sharing_url 	= 'http://vk.com/share.php';
				$network_args 	= [
					'url'		=> $current_url,
					'title'		=> $current_ttl,
					'caption'	=> $current_txt
				];
			break;
			case 'skype':
				$sharing_url 	= 'https://web.skype.com/share';
				$network_args 	= [
					'url'	=> $current_url,
					'text'	=> $current_ttl,
					'source'=> 'button',
				];
			break;
			case 'google':
				$sharing_url 	= 'https://plus.google.com/share';
				$network_args 	= [
					'url'	=> $current_url,
				];
			break;
			case 'mail':
				$sharing_url 	= 'mailto:';
				$network_args 	= [
					'subject'	=> $current_ttl,
					'body'		=> $current_txt . ' ' . $current_url,
				];
			break;
		endswitch;

		return apply_filters(
			'wecodeart/filter/gutenberg/social-link/sharing/url',
			add_query_arg( urlencode_deep( $network_args ), esc_url( $sharing_url ) ),
			$service,
			$sharing_url,
			$network_args
		);
	}
}
