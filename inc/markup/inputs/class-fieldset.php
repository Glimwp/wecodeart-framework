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

use WeCodeArt\Markup;
use function WeCodeArt\Functions\get_prop;

/**
 * Standard Inputs Markup
 */
class Fieldset extends Base {

    /**
     * Input's Label - disabled.
     *
     * @since   4.2.0
     * @var     string
     */
    public $label_position = 'none';

    /**
     * All choices tied to the control.
     *
     * @since   4.2.0
     * @var     array
     */
    public $choices = [];
    
    /**
	 * Constructor 
	 */
	public function __construct( string $class = 'fieldset', array $args = [] ) {
        $type               = get_prop( $args, 'type', 'checkbox' );
        $this->type         = in_array( $type, [ 'radio', 'checkbox' ] ) ? $type : 'checkbox';
        $this->unique_id    = wp_unique_id( 'fieldset-' );
        $this->label        = get_prop( $args, 'label', '' );
        $this->attrs        = get_prop( $args, 'attrs', [] );
        $this->choices      = get_prop( $args, 'choices', [] );
        $this->messages     = get_prop( $args, 'messages', [] );
    }

	/**
	 * Create HTML Inputs
	 *
	 * @since	unknown
	 * @version	4.2.0
	 */
	public function content() {
        Markup::wrap( 'fieldset-' . $this->type, [
            [
                'tag'   => 'fieldset',
                'attrs' => [
                    'class' => implode( ' ', [ 'fieldset', 'fieldset--' . $this->type ] ),
                ]
            ]
        ], function() {
            // Legend
            $this->label();
            
            // Fields
            $fields = '';
            foreach( $this->choices as $key => $label ) {
                // Create a basic input
                $fields .= ( new Toggle( $this->type, [
                    'type'  => $this->type,
                    'label' => $label,
                    'attrs' => [
                        'value'		=> $key,
                        'class'     => $this->attrs['class'],
                        'name' 		=> $this->get_option_name( $key ),
                        'id'        => $this->get_option_id( $key ),
                        'checked'	=> $this->checked_option( $key ),
                    ]
                ] ) )->get_content();
            }

            // Already escaped in the Basic Input and its methods
            echo $fields; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        } );
    }

    /**
	 * Render the label HTML of the input
     *
     * @since   4.2.0
	 * @return	mixed|string
	 */
	public function label() {
        if( empty( $this->label ) ) return;

        Markup::wrap( 'fieldset-label', [
            [
                'tag'   => 'legend',
            ]
        ], $this->label );
    }

    /**
	 * Get Item Name
     *
     * @since   4.2.0
	 * @return	string
	 */
	public function get_option_name( string $item ) {
        if( $this->type === 'checkbox' ) $value = get_prop( $this->attrs, 'name', $this->unique_id ) . '[' . $item . ']';
        if( $this->type === 'radio' ) $value = get_prop( $this->attrs, 'name', $this->unique_id );
        return $value;
    }

    /**
	 * Get Item ID
     *
     * @since   4.2.0
	 * @return	string
	 */
	public function get_option_id( string $item ) {
        $value = get_prop( $this->attrs, 'name', $this->unique_id ) . '-' . $item;
        return $value;
    }

    /**
	 * Checked Option
     *
     * @since   4.2.0
	 * @return	mixed|string
	 */
	public function checked_option( string $value ) {
        if( isset( $this->attrs['value'] ) ) {
            if( is_array( $this->attrs['value'] ) && in_array( $value, $this->attrs['value'] ) ) {
                return true;
            }

            if( is_string( $this->attrs['value'] ) && $this->attrs['value'] === $value ) {
                return true;
            }
        }
    }
}