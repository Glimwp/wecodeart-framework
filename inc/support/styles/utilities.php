<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg Utilities
 * @copyright   Copyright (c) 2022, WeCodeArt Framework
 * @since		5.2.4
 * @version		5.4.9
 */

// Theme Shadows
$shadows    = wecodeart_json( [ 'settings', 'custom', 'shadows' ], [] );
$shadows    = array_merge( $shadows, [
    null    => '0 .3rem .75rem rgba(0,0,0, .15)',
    'none'  => 'none'
] );

// Theme Spacers
$spacers    = wecodeart_json( [ 'settings', 'custom', 'spacers' ], [] );
$spacers    = array_merge( $spacers, [
    'auto'  => 'auto'
] );

// Theme Colors
$palette 	= wecodeart_json( [ 'settings', 'color', 'palette', 'default' ], [] );
$palette 	= wecodeart_json( [ 'settings', 'color', 'palette', 'theme' ], $palette );
$palette 	= wecodeart_json( [ 'settings', 'color', 'palette', 'user' ], $palette );
$palette    = wp_list_pluck( $palette, 'color', 'slug' );

// Default utilities
foreach( [
    // Align
    [
        'property'  => 'vertical-align',
        'class'		=> 'align',
        'values'	=> [
            'top'       => 'top',
            'middle'    => 'middle',
            'bottom'    => 'bottom',
            'baseline'  => 'baseline'
        ],
    ],
    // Border
    [
        'property'  => 'border',
        'class'		=> 'border',
        'values'	=> [
            null    => '1px solid lightgrey',
            0       => 0,
        ],
    ],
    [
        'property'  => 'border-top',
        'class'		=> 'border-top',
        'values'	=> [
            null    => '1px solid lightgrey',
            0       => 0,
        ],
    ],
    [
        'property'  => 'border-bottom',
        'class'		=> 'border-bottom',
        'values'	=> [
            null    => '1px solid lightgrey',
            0       => 0,
        ],
    ],
    [
        'property'  => 'border-left',
        'class'		=> 'border-start',
        'values'	=> [
            null    => '1px solid lightgrey',
            0       => 0,
        ],
    ],
    [
        'property'  => 'border-right',
        'class'		=> 'border-end',
        'values'	=> [
            null    => '1px solid lightgrey',
            0       => 0,
        ],
    ],
    [
        'property'  => 'border-width',
        'class'		=> 'border',
        'values'	=> [
            0 => 0,
            1 => '1px',
            2 => '2px',
            3 => '3px',
            4 => '4px',
            5 => '5px',
        ],
    ],
    // Rounded
    [
        'property'  => 'border-radius',
        'class'		=> 'rounded',
        'values'	=> [
            null    => '.25rem',
            0       => 0,
            1       => '.5rem',
            2       => '1rem',
            3       => '1.5rem',
            'circle'=> '50%',
            'pill'  => '50px',
        ],
    ],
    [
        'property'  => [ 'border-top-left-radius', 'border-top-right-radius' ],
        'class'		=> 'rounded-top',
        'values'	=> [
            null    => '.25rem',
            0       => 0,
        ],
    ],
    [
        'property'  => [ 'border-bottom-left-radius', 'border-bottom-right-radius' ],
        'class'		=> 'rounded-bottom',
        'values'	=> [
            null    => '.25rem',
            0       => 0,
        ],
    ],
    [
        'property'  => [ 'border-top-left-radius', 'border-bottom-left-radius' ],
        'class'		=> 'rounded-start',
        'values'	=> [
            null    => '.25rem',
            0       => 0,
        ],
    ],
    [
        'property'  => [ 'border-top-right-radius', 'border-bottom-right-radius' ],
        'class'		=> 'rounded-end',
        'values'	=> [
            null    => '.25rem',
            0       => 0,
        ],
    ],
    // Border Color - use Gutenberg classes
    // Display
    [
        'property'  => 'display',
        'class'		=> 'd',
        'responsive'=> true,
        'values'	=> [
            'none'          => 'none',
            'inline'        => 'inline',
            'block'         => 'block',
            'inline-block'  => 'inline-block',
            'flex'          => 'flex',
            'inline-flex'   => 'inline-flex',
            'grid'          => 'grid',
            'table'         => 'table',
            'table-cell'    => 'table-cell',
            'table-row'     => 'table-row',
        ],
    ],
    // Float
    [
        'property'  => 'float',
        'class'		=> 'float',
        'values'	=> [
            'start' => 'left',
            'end'   => 'right',
            'none'  => 'none'
        ],
    ],
    // Typography
    [
        'property'  => 'font-family',
        'class'		=> 'font',
        'values'	=> [
            'monospace' => 'var(--wp--font-monospace)',
        ],
    ],
    [
        'property'  => 'font-weight',
        'class'		=> 'fw',
        'values'	=> [
            100 => '100',
            200 => '200',
            300 => '300',
            400 => '400',
            500 => '500',
            600 => '600',
            700 => '700',
            800 => '800',
            900 => '900',
        ],
    ],
    [
        'property'  => 'text-align',
        'class'		=> 'has-text-align',
        'responsive'=> true,
        'values'	=> [
            'left'  => 'left',
            'center'=> 'center',
            'right' => 'right',
        ],
    ],
    [
        'property'  => 'white-space',
        'class'		=> 'text',
        'values'	=> [
            'wrap'      => 'wrap',
            'nowrap'    => 'nowrap',
        ],
    ],
    [
        'property'  => [ 'word-wrap', 'word-break' ],
        'class'		=> 'text',
        'values'	=> [
            'break' => 'break-word',
        ],
    ],
    // Flex
    [
        'property'  => 'flex',
        'class'		=> 'flex',
        'responsive'=> true,
        'values'	=> [
            'fill'  => '1 1 auto',
        ],
    ],
    [
        'property'  => 'flex-direction',
        'class'		=> 'flex',
        'responsive'=> true,
        'values'	=> [
            'row'           => 'row',
            'row-reverse'   => 'row-reverse',
            'column'        => 'column',
            'column-reverse'=> 'column-reverse',
        ],
    ],
    [
        'property'  => 'flex-grow',
        'class'		=> 'flex',
        'responsive'=> true,
        'values'	=> [
            'grow-0'    => 0,
            'grow-1'    => 1,
        ],
    ],
    [
        'property'  => 'flex-shrink',
        'class'		=> 'flex',
        'responsive'=> true,
        'values'	=> [
            'shrink-0'  => 0,
            'shrink-1'  => 1,
        ],
    ],
    [
        'property'  => 'flex-wrap',
        'class'		=> 'flex',
        'responsive'=> true,
        'values'	=> [
            'wrap'          => 'wrap',
            'nowrap'        => 'nowrap',
            'wrap-reverse'  => 'wrap-reverse',
        ],
    ],
    [
        'property'  => 'gap',
        'class'		=> 'gap',
        'responsive'=> true,
        'values'	=> $spacers,
    ],
    [
        'property'  => 'justify-content',
        'class'     => 'justify-content',
        'responsive'=> true,
        'values'	=> [
            'start'     => 'flex-start',
            'end'       => 'flex-end',
            'center'    => 'center',
            'between'   => 'space-between',
            'around'    => 'space-around',
            'evenly'    => 'space-evenly',
        ],
    ],
    [
        'property'  => 'align-items',
        'responsive'=> true,
        'values'	=> [
            'start'     => 'flex-start',
            'end'       => 'flex-end',
            'center'    => 'center',
            'baseline'  => 'baseline',
            'stretch'   => 'stretch',
        ],
    ],
    [
        'property'  => 'align-items',
        'class'     => 'align-items',
        'responsive'=> true,
        'values'	=> [
            'start'     => 'flex-start',
            'end'       => 'flex-end',
            'center'    => 'center',
            'baseline'  => 'baseline',
            'stretch'   => 'stretch',
        ],
    ],
    [
        'property'  => 'align-content',
        'class'     => 'align-content',
        'responsive'=> true,
        'values'	=> [
            'start'     => 'flex-start',
            'end'       => 'flex-end',
            'center'    => 'center',
            'between'   => 'space-between',
            'around'    => 'space-around',
            'stretch'   => 'stretch',
        ],
    ],
    [
        'property'  => 'align-self',
        'class'     => 'align-self',
        'responsive'=> true,
        'values'	=> [
            'auto'      => 'auto',
            'start'     => 'flex-start',
            'end'       => 'flex-end',
            'center'    => 'center',
            'baseline'  => 'baseline',
            'stretch'   => 'stretch',
        ],
    ],
    // Margin
    [
        'property'  => 'margin',
        'class'		=> 'm',
        'responsive'=> true,
        'values'	=> $spacers,
    ],
    [
        'property'  => [ 'margin-left', 'margin-right' ],
        'class'		=> 'mx',
        'responsive'=> true,
        'values'	=> $spacers,
    ],
    [
        'property'  => [ 'margin-top', 'margin-bottom' ],
        'class'		=> 'my',
        'responsive'=> true,
        'values'	=> $spacers,
    ],
    [
        'property'  => 'margin-top',
        'class'		=> 'mt',
        'responsive'=> true,
        'values'	=> $spacers,
    ],
    [
        'property'  => 'margin-bottom',
        'class'		=> 'mb',
        'responsive'=> true,
        'values'	=> $spacers,
    ],
    [
        'property'  => 'margin-left',
        'class'		=> 'ms',
        'responsive'=> true,
        'values'	=> $spacers,
    ],
    [
        'property'  => 'margin-right',
        'class'		=> 'me',
        'responsive'=> true,
        'values'	=> $spacers,
    ],
    // Padding
    [
        'property'  => 'padding',
        'class'		=> 'p',
        'responsive'=> true,
        'values'	=> $spacers,
    ],
    [
        'property'  => [ 'padding-left', 'padding-right' ],
        'class'		=> 'px',
        'responsive'=> true,
        'values'	=> $spacers,
    ],
    [
        'property'  => [ 'padding-top', 'padding-bottom' ],
        'class'		=> 'py',
        'responsive'=> true,
        'values'	=> $spacers,
    ],
    [
        'property'  => 'padding-top',
        'class'		=> 'pt',
        'responsive'=> true,
        'values'	=> $spacers,
    ],
    [
        'property'  => 'padding-bottom',
        'class'		=> 'pb',
        'responsive'=> true,
        'values'	=> $spacers,
    ],
    [
        'property'  => 'padding-left',
        'class'		=> 'ps',
        'responsive'=> true,
        'values'	=> $spacers,
    ],
    [
        'property'  => 'padding-right',
        'class'		=> 'pe',
        'responsive'=> true,
        'values'	=> $spacers,
    ],
    // Opacity
    [
        'property'  => 'opacity',
        'class'		=> 'opacity',
        'values'	=> [
            0   => 0,
            25  => '.25',
            50  => '.5',
            75  => '.75',
            100 => 1
        ],
    ],
    // Text Opacity
    [
        'property'  => '--wp--text-opacity',
        'class'		=> 'text-opacity',
        'values'	=> [
            25  => '.25',
            50  => '.5',
            75  => '.75',
            100 => 1
        ],
    ],
    // Background Opacity
    [
        'property'  => '--wp--bg-opacity',
        'class'		=> 'bg-opacity',
        'values'	=> [
            25  => '.25',
            50  => '.5',
            75  => '.75',
            100 => 1
        ],
    ],
    // Overflow
    [
        'property'  => 'overflow',
        'class'		=> 'overflow',
        'values'	=> [
            'auto'      => 'auto',
            'hidden'    => 'hidden',
            'visible'   => 'visible',
            'scroll'    => 'scroll'
        ],
    ],
    // Order
    [
        'property'  => 'order',
        'class'		=> 'order',
        'responsive'=> true,
        'values'	=> [
            'first' => -1,
            0       => 0,
            1       => 1,
            2       => 2,
            3       => 3,
            4       => 4,
            5       => 5,
            'last'  => 99
        ],
    ],
    // Pointer events
    [
        'property'  => 'pointer-events',
        'class'		=> 'pe',
        'values'	=> [
            'auto' => 'auto',
            'none' => 'none'
        ],
    ],
    // Position
    [
        'property'  => 'position',
        'class'     => 'position',
        'values'	=> [
            'static'    => 'static',
            'relative'  => 'relative',
            'absolute'  => 'absolute',
            'sticky'    => 'sticky',
            'fixed'     => 'fixed'
        ],
    ],
    [
        'property'  => 'top',
        'class'     => 'top',
        'values'	=> [
            0,
            50  => '50%',
            100 => '100%',
        ],
    ],
    [
        'property'  => 'bottom',
        'class'     => 'bottom',
        'values'	=> [
            0   => 0,
            50  => '50%',
            100 => '100%',
        ],
    ],
    [
        'property'  => 'left',
        'class'		=> 'start',
        'values'	=> [
            0   => 0,
            50  => '50%',
            100 => '100%',
        ],
    ],
    [
        'property'  => 'right',
        'class'		=> 'end',
        'values'	=> [
            0   => 0,
            50  => '50%',
            100 => '100%',
        ],
    ],
    [
        'property'  => 'inset',
        'class'     => 'inset',
        'values'	=> [
            0   => 0,
            1   => '1rem',
        ],
    ],
    // Shadow
    [
        'property'  => 'box-shadow',
        'class'		=> 'shadow',
        'values'	=> $shadows,
    ],
    // Sizing (w/h/mw/mh/mw/mh)
    [
        'property'  => 'height',
        'class'		=> 'h',
        'values'	=> [
            'auto'  => 'auto',
            25      => '25%',
            50      => '50%',
            75      => '75%',
            100     => '100%'
        ],
    ],
    [
        'property'  => 'width',
        'class'		=> 'w',
        'values'	=> [
            'auto'  => 'auto',
            25      => '25%',
            50      => '50%',
            75      => '75%',
            100     => '100%'
        ],
    ],
    [
        'property'  => 'max-width',
        'class'		=> 'mw',
        'values'	=> [
            100 => '100%'
        ],
    ],
    [
        'property'  => 'max-height',
        'class'		=> 'mh',
        'values'	=> [
            100 => '100%'
        ],
    ],
    [
        'property'  => 'width',
        'class'		=> 'vw',
        'values'	=> [
            100 => '100vw'
        ],
    ],
    [
        'property'  => 'height',
        'class'		=> 'min-vh',
        'values'	=> [
            100 => '100vh'
        ],
    ],
    [
        'property'  => 'min-width',
        'class'		=> 'min-vw',
        'values'	=> [
            100 => '100vw'
        ],
    ],
    [
        'property'  => 'min-height',
        'class'		=> 'min-vh',
        'values'	=> [
            100 => '100vh'
        ],
    ],
    // Translate
    [
        'property'  => 'transform',
        'class'		=> 'translate-middle',
        'values'	=> [
            null    => 'translate3d(-50%,-50%,0)',
            'x'     => 'translateX(-50%)',
            'y'     => 'translateY(-50%)',
        ],
    ],
    // User Select
    [
        'property'  => 'user-select',
        'class'		=> 'user-select',
        'values'	=> [
            'all'   => 'all',
            'auto'  => 'auto',
            'none'  => 'none'
        ],
    ],
    // Visibility
    [
        'property'  => 'visibility',
        'class'     => 'visibility',
        'values'	=> [
            'visible'   => 'visible',
            'hidden'    => 'hidden'
        ],
    ],
] as $utility ) wecodeart( 'styles' )::register_utility( $utility );