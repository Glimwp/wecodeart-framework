<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Support/Assets/Asset
 * @copyright   Copyright (c) 2024, WeCodeArt Framework
 * @since 		5.4.5
 * @version		6.3.7
 */ 

namespace WeCodeArt\Support\Assets;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Config\Interfaces\Configuration;
use WeCodeArt\Config\Exceptions\FileNotFoundException;
use function WeCodeArt\Functions\get_prop;

/**
 * Framework Assets
 */
class Asset {

	/**
     * Theme config instance.
     *
     * @var \WeCodeArt\Config\Interfaces\Configuration
     */
	protected $config;
	
    /**
     * Asset file.
     *
     * @var string
     */
	protected $file;
	
    /**
     * Construct asset.
     *
     * @param \WeCodeArt\Config\Interfaces\Configuration $config
     */
    public function __construct( Configuration $config ) {
        $this->config = $config;
	}
    
	/**
     * Get asset file URI.
     *
     * @return string
     */
    public function get_uri(): string {
        $file = $this->get_public_path();

        if ( $this->file_exists( $file ) ) {
            return esc_url( $this->get_public_uri() );
        }

        throw new FileNotFoundException( sprintf( 'Asset file [%s] cannot be located.', $file ) );
	}
	
    /**
     * Get asset file path.
     *
     * @return string
     */
    public function get_path(): string {
        $file = $this->get_public_path();

        if ( $this->file_exists( $file ) ) {
            return wp_normalize_path( $file );
        }
    
        throw new FileNotFoundException( sprintf( 'Asset file [%s] cannot be located.', $file ) );
	}
	
    /**
     * Gets asset uri path.
     *
     * @return string
     */
    public function get_public_uri(): string {
        return $this->config['paths']['uri'] . '/' . $this->get_relative_path();
	}

    /**
     * Gets asset directory path.
     *
     * @return string
     */
    public function get_public_path(): string {
        return $this->config['paths']['directory'] . '/' . $this->get_relative_path();
	}
	
    /**
     * Gets asset relative path.
     *
     * @return string
     */
    public function get_relative_path(): string {
        return $this->config['directories']['assets'] . '/' . $this->get_file();
	}
	
    /**
     * Checks if asset file exsist.
     *
     * @param  string $file
     *
     * @return boolean
     */
    public function file_exists( $file ): bool {
        return file_exists( $file );
	}
	
    /**
     * Gets the Asset file.
     *
     * @return string
     */
    public function get_file(): string {
        return $this->file;
	}
	
    /**
     * Sets the Asset file.
     *
     * @param   string $file the file
     *
     * @return  self
     */
    public function set_file( string $file ) {
        $this->file = $file;

        return $this;
    }
}