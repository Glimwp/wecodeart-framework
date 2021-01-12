<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg CSS Handler
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		4.2.0
 * @version		4.2.0
 */

namespace WeCodeArt\Gutenberg\Modules\Styles;

defined( 'ABSPATH' ) || exit();

use WP_REST_Request;
use WeCodeArt\Gutenberg;
use WeCodeArt\Support\FileSystem;
use WeCodeArt\Gutenberg\Modules\Styles;
use function WeCodeArt\Functions\compress_css;

/**
 * Class Handler
 *
 * Process/create/save/delete CSS code for gutenberg blocks
 */
class Handler {

	use \WeCodeArt\Singleton;

	/**
	 * The Styles Processor
	 *
	 * @access 	public
	 * @var 	null|object
	 */
	public $styles = null;
	
	/**
	 * Initialize the class
	 */
	public function init() {
		$this->FS 		= FileSystem::get_instance()->set_folder( 'css' );
		$this->styles 	= wecodeart( 'integrations' )->get( 'styles' )::get_instance();

		add_action( 'rest_api_init', 		[ $this, 'register_routes' ] );
		add_action( 'before_delete_post', 	[ $this, 'delete_css_file' ] );
	}

	/**
	 * Register REST API route
	 *
	 * @since   4.2.0
	 * @access  public
	 */
	public function register_routes() {
		$namespace = 'wecodeart/v1';

		register_rest_route( $namespace, '/save_post_meta/(?P<id>\d+)', array(
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'save_post_meta' ),
				'args'                => array(
					'id' => array(
						'type'              => 'intval',
						'required'          => true,
						'description'       => __( 'ID of the Post.', 'wecodeart' ),
						'validate_callback' => function ( $param, $request, $key ) {
							return is_numeric( $param );
						},
					),
				),
				'permission_callback' => function () {
					return current_user_can( 'publish_posts' );
				},
			),
		) );

		register_rest_route( $namespace, '/save_block_meta/(?P<id>\d+)', array(
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'save_block_meta' ),
				'args'                => array(
					'id' => array(
						'type'              => 'intval',
						'required'          => true,
						'description'       => __( 'ID of the Reusable Block.', 'wecodeart' ),
						'validate_callback' => function ( $param, $request, $key ) {
							return is_numeric( $param );
						},
					),
				),
				'permission_callback' => function () {
					return current_user_can( 'publish_posts' );
				},
			),
		) );
	}

	/**
	 * Function to save post CSS.
	 *
	 * @param 	\WP_REST_Request $request Rest request.
	 *
	 * @return 	mixed
	 * @since   4.2.0
	 * @access  public
	 */
	public function save_post_meta( \WP_REST_Request $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		$post_id 	= $request->get_param( 'id' );
		$file 		= $this->generate_css_file( $post_id );

		return rest_ensure_response( [
			'message' 	=> __( 'CSS updated.', 'wecodeart' ),
			'postId' 	=> $post_id,
			'status'	=> $file ? 'updated' : 'removed'
		] );
	}

	/**
	 * Function to save reusable block CSS.
	 *
	 * @param   \WP_REST_Request $request Rest request.
	 *
	 * @return  mixed
	 * @since   4.2.0
	 * @access  public
	 */
	public function save_block_meta( \WP_REST_Request $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		$block_id 	= $request->get_param( 'id' );
		$css     	= Styles::get_instance()->get_reusable_block_css( $block_id );

		if( $css ) {
			$this->save_css_file( $block_id, $css );
			$status = 'updated';
		} else {
			$this->delete_css_file( $block_id, $css );
			$status = 'removed';
		}

		return rest_ensure_response( [
			'message' 	=> __( 'CSS updated.', 'wecodeart' ),
			'blockId' 	=> $block_id,
			'status'	=> $status
		] );
	}

	/**
	 * Generate CSS file.
	 *
	 * @param int $post_id Post id.
	 */
	public function generate_css_file( $post_id ) {
		$css = Styles::get_instance()->get_blocks_css( $post_id );
		
		if( $css ) {
			return $this->save_css_file( $post_id, $css );
		}

		return $this->delete_css_file( $post_id );
	}

	/**
	 * Function to save CSS into WordPress Filesystem.
	 *
	 * @param   int    $post_id Post id.
	 * @param   string $css CSS string.
	 *
	 * @return  bool
	 * @since   4.2.0
	 * @access  public
	 */
	public function save_css_file( $post_id, $css ) {
		$css = $this->styles::compress( $css );

		update_post_meta( $post_id, '_wca_gutenberg_block_styles', $css );

		do_action( 'wecodeart/gutenberg/css/handler/save', $post_id );

		$file_name = 'post-' . $post_id;

		$file = $this->FS->create_file( $file_name . '.css', $css );

		// If it went successfully, update meta with the filename;
		if ( $file ) {
			update_post_meta( $post_id, '_wca_gutenberg_block_stylesheet', $file_name );
			return true;
		}
		
		return false;
	}

	/**
	 * Get CSS url for post.
	 *
	 * @param   int 	$post_id Post id.
	 *
	 * @return  string 	File url.
	 */
	public function get_css_url( $post_id ) {
		$file_name = get_post_meta( $post_id, '_wca_gutenberg_block_stylesheet', true );

		if ( empty( $file_name ) ) {
			return false;
		}

		return $this->FS->get_file_url( $file_name . '.css', true );
	}

	/**
	 * Check if we have a CSS file for this post.
	 *
	 * @param   int 	$post_id Post ID.
	 *
	 * @return  bool
	 */
	public function has_css_file( $post_id ) {
		$file_name = get_post_meta( $post_id, '_wca_gutenberg_block_stylesheet', true );

		if ( empty( $file_name ) ) {
			return false;
		}

		return $this->FS->has_file( $file_name . '.css' );
	}

	/**
	 * Function to delete CSS from WordPress Filesystem.
	 *
	 * @param   int $post_id Post id.
	 *
	 * @return  bool
	 * @since   4.2.0
	 * @access  public
	 */
	public function delete_css_file( $post_id ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		$file_name = get_post_meta( $post_id, '_wca_gutenberg_block_stylesheet', true );

		// Clear Meta
		delete_post_meta( $post_id, '_wca_gutenberg_block_styles' );
		delete_post_meta( $post_id, '_wca_gutenberg_block_stylesheet' );
		
		return $this->FS->delete_file( $file_name . '.css' );
	}
}