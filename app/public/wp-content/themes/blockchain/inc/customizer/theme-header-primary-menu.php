<?php
	$wp_customize->add_setting( 'header_primary_menu_padding', array(
		'transport'         => 'postMessage',
		'default'           => '',
		'sanitize_callback' => 'blockchain_sanitize_intval_or_empty',
	) );
	$wp_customize->add_control( 'header_primary_menu_padding', array(
		'type'        => 'number',
		'input_attrs' => array(
			'min'  => 0,
			'step' => 1,
		),
		'section'     => 'theme_header_primary_menu',
		'label'       => esc_html__( 'Vertical padding (in pixels)', 'blockchain' ),
	) );

	$wp_customize->add_setting( 'header_primary_menu_text_size', array(
		'transport'         => 'postMessage',
		'default'           => '',
		'sanitize_callback' => 'blockchain_sanitize_intval_or_empty',
	) );
	$wp_customize->add_control( 'header_primary_menu_text_size', array(
		'type'        => 'number',
		'input_attrs' => array(
			'min'  => 0,
			'step' => 1,
		),
		'section'     => 'theme_header_primary_menu',
		'label'       => esc_html__( 'Menu text size (in pixels)', 'blockchain' ),
	) );

	$partial = $wp_customize->selective_refresh->get_partial( 'theme_style' );
	$partial->settings = array_merge( $partial->settings, array(
		'header_primary_menu_padding',
		'header_primary_menu_text_size',
	) );

	$wp_customize->add_setting( 'theme_header_primary_menu_sticky', array(
		'transport'         => 'postMessage',
		'default'           => 0,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'theme_header_primary_menu_sticky', array(
		'type'    => 'checkbox',
		'section' => 'theme_header_primary_menu',
		'label'   => esc_html__( 'Sticky menu bar', 'blockchain' ),
	) );

	$wp_customize->selective_refresh->get_partial( 'theme_style' )->settings[] = 'theme_header_primary_menu_sticky';
