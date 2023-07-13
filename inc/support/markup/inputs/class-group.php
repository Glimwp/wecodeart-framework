<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework 
 * @subpackage 	Markup\Inputs
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since		6.1.7
 * @version		6.1.7
 */

namespace WeCodeArt\Support\Markup\Inputs;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Support\Markup\Inputs\Basic;
use function WeCodeArt\Functions\get_prop;

/**
 * Group Inputs Markup
 */
class Group extends Basic {

    /**
     * Input's Type / Label Position.
     *
     * @var     string
     */
    public $type    = 'group';
    public $_label  = 'none';

    /**
	 * Constructor 
	 */
	public function __construct( string $type = 'group', array $args = [] ) {
        $this->args = $args;
    }
	
	/**
	 * Create HTML Inputs
	 *
	 * @return	void
	 */
	public function content() {
        wecodeart( 'markup' )::wrap( 'input-group', [
            [
                'tag' => 'div',
                'attrs' => [
                    'class' => 'input-group',
                ]
            ]
        ], function( $fields ) {
            foreach( $fields as $field ) {
                // If is a string, we ouput a label.
                if( is_string( $field ) ) {
                    wecodeart( 'markup' )::wrap( 'input-group-text', [
                        [
                            'tag'   => 'label',
                        ]
                    ], $field );

                    continue;
                }

                $type = get_prop( $field, 'type', 'text' );
                
                // Filter - Update wrapper class accordingly.
                add_filter( 'wecodeart/filter/wrappers/' . $type . '-label', [ __CLASS__, 'update_label' ] );

                // Generate input.
                $input = wecodeart_input( $type, $field, false );

                // Wrap specific input types.
                if( in_array( $type, [ 'checkbox', 'radio', 'toggle' ], true ) ) {
                    $input = wecodeart( 'markup' )::wrap( 'input-group-text', [
                        [
                            'tag'   => 'label',
                        ]
                    ], $input, false );
                }

                echo $input; // Is ok, the API does all necessary escaping.

                // Filter - Update wrapper class accordingly.
                remove_filter( 'wecodeart/filter/wrappers/' . $type . '-label', [ __CLASS__, 'update_label' ] );
            }
        }, [ $this->args ?? [] ] );
    }

    /**
	 * Update labels.
	 *
	 * @return 	array
	 */
    public static function update_label( $wrappers ): array {
        $wrappers[0]['attrs']['class'] = 'input-group-text';

        return $wrappers;
    }

    /**
	 * Input styles.
	 *
	 * @return 	string
	 */
	public static function styles(): string {
		return '
            /* Input Group */
            .input-group {
                position: relative;
                display: flex;
                flex-wrap: wrap;
                align-items: stretch;
                width: 100%;
            }
            .input-group > :is(.form-control,.form-select,.form-floating) {
                position: relative;
                flex: 1 1 auto;
                width: 1%;
                min-width: 0;
            }
            .input-group > :is(.form-control,.form-select,.form-floating):is(:focus,:focus-within) {
                z-index: 3;
            }
            .input-group .wp-element-button {
                position: relative;
                z-index: 2;
            }
            .input-group .wp-element-button:focus {
                z-index: 3;
            }
            .input-group .form-check-input {
                margin-top: 0;
            }
            .input-group-text {
                display: flex;
                align-items: center;
                padding: var(--wp--input--padding);
                font-size: 1rem;
                font-weight: 400;
                line-height: 1.5;
                color: var(--wp--input--color);
                text-align: center;
                white-space: nowrap;
                background-color: var(--wp--input--background-color);
                border: var(--wp--input--border);
                border-radius: var(--wp--input--border-radius);
            }
            .input-group-lg > :is(.form-control,.form-select,.input-group-text,.wp-element-button) {
                font-size: var(--wp--preset--font-size--medium);
                padding: 0.5rem 1rem;
                border-radius: calc(1.25 * var(--wp--input--border-radius));
            }
            .input-group-sm > :is(.form-control,.form-select,.input-group-text,.wp-element-button) {
                font-size: var(--wp--preset--font-size--small);
                padding: 0.25rem 0.5rem;
                border-radius: calc(.75 * var(--wp--input--border-radius));
            }
            :is(.input-group-lg,.input-group-sm) > .form-select {
                padding-right: 3rem;
            }
            .input-group:not(.has-validation) > :not(:last-child):not(.dropdown-toggle):not(.dropdown-menu):not(.form-floating),
            .input-group:not(.has-validation) > .dropdown-toggle:nth-last-child(n+3),
            .input-group:not(.has-validation) > .form-floating:not(:last-child) > :is(.form-control,.form-select) {
                border-top-right-radius: 0;
                border-bottom-right-radius: 0;
            }
            .input-group.has-validation > :nth-last-child(n+3):not(.dropdown-toggle):not(.dropdown-menu):not(.form-floating),
            .input-group.has-validation > .dropdown-toggle:nth-last-child(n+4),
            .input-group.has-validation > .form-floating:nth-last-child(n+3) > :is(.form-control,.form-select) {
                border-top-right-radius: 0;
                border-bottom-right-radius: 0;
            }
            .input-group > :not(:first-child):not(.dropdown-menu):not(.form-floating):not(.valid-tooltip):not(.valid-feedback):not(.invalid-tooltip):not(.invalid-feedback),
            .input-group > .form-floating:not(:first-child) > :is(.form-control,.form-select) {
                margin-left: -1px;
                border-top-left-radius: 0;
                border-bottom-left-radius: 0;
            }
            /* Input Group Validation */
            .was-validated .input-group .form-control:is(:valid,:invalid),
			.input-group :is(.form-control,.form-select).is-valid {
				z-index: 1;
			}
			.was-validated .input-group :is(.form-control,.form-select):is(:valid,:invalid):focus,
			.input-group :is(.form-control,.form-select):is(.is-valid,.is-invalid):focus {
				z-index: 3;
			}
			.was-validated .input-group :is(.form-control,.form-select):invalid,
			.input-group :is(.form-control,.form-select).is-invalid {
				z-index: 2;
			}
        ';
	}
}