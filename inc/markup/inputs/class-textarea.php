<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework 
 * @subpackage 	Markup\Inputs
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		4.2.0
 * @version		4.2.0
 */

namespace WeCodeArt\Markup\Inputs;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Markup\Inputs\Basic;
use function WeCodeArt\Functions\get_prop;

/**
 * Standard Inputs Markup
 */
class TextArea extends Basic {

    /**
     * Input's Type.
     *
     * @since   4.2.0
     * @var     string
     */
    public $type = 'textarea';
    
    /**
	 * Constructor 
	 */
	public function __construct( string $type = 'textarea', array $args = [] ) {
        $this->unique_id    = wp_unique_id( 'textarea-' );
        $this->label        = get_prop( $args, 'label', '' );
        $this->label_position   = get_prop( $args, '_label', 'before' );
        $this->attrs        = get_prop( $args, 'attrs', [] );
        $this->messages     = get_prop( $args, 'messages', [] );
    }

	/**
	 * Create HTML Inputs
	 *
	 * @since	unknown
	 * @version	4.2.0
	 */
	public function content() {
        ?>
        <textarea <?php $this->input_attrs(); ?>></textarea>
        <?php
    }
}