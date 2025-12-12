<?php
	$wp_customize->add_setting( 'header_top_bar_padding', array(
		'transport'         => 'postMessage',
		'default'           => '',
		'sanitize_callback' => 'blockchain_sanitize_intval_or_empty',
	) );
	$wp_customize->add_control( 'header_top_bar_padding', array(
		'type'        => 'number',
		'input_attrs' => array(
			'min'  => 0,
			'step' => 1,
		),
		'section'     => 'theme_header_top_bar',
		'label'       => esc_html__( 'Vertical padding (in pixels)', 'blockchain' ),
	) );

	$wp_customize->add_setting( 'header_top_bar_text_size', array(
		'transport'         => 'postMessage',
		'default'           => '',
		'sanitize_callback' => 'blockchain_sanitize_intval_or_empty',
	) );
	$wp_customize->add_control( 'header_top_bar_text_size', array(
		'type'        => 'number',
		'input_attrs' => array(
			'min'  => 0,
			'step' => 1,
		),
		'section'     => 'theme_header_top_bar',
		'label'       => esc_html__( 'Text size (in pixels)', 'blockchain' ),
	) );

	$partial = $wp_customize->selective_refresh->get_partial( 'theme_style' );
	$partial->settings = array_merge( $partial->settings, array(
		'header_top_bar_padding',
		'header_top_bar_text_size',
	) );


	$wp_customize->add_setting( 'header_top_bar_text_1', array(
		'transport'         => 'postMessage',
		'default'           => '',
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'header_top_bar_text_1', array(
		'type'        => 'text',
		'section'     => 'theme_header_top_bar',
		'label'       => esc_html__( 'Text 1', 'blockchain' ),
		'description' => esc_html__( 'Accepts HTML', 'blockchain' ),
	) );

	$wp_customize->add_setting( 'header_top_bar_text_2', array(
		'transport'         => 'postMessage',
		'default'           => '',
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'header_top_bar_text_2', array(
		'type'        => 'text',
		'section'     => 'theme_header_top_bar',
		'label'       => esc_html__( 'Text 2', 'blockchain' ),
		'description' => esc_html__( 'Accepts HTML', 'blockchain' ),
	) );

	$wp_customize->add_setting( 'theme_header_top_bar_show_social_icons', array(
		'transport'         => 'postMessage',
		'default'           => 1,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'theme_header_top_bar_show_social_icons', array(
		'type'    => 'checkbox',
		'section' => 'theme_header_top_bar',
		'label'   => esc_html__( 'Show social icons', 'blockchain' ),
	) );

	if ( class_exists( 'SitePress' ) ) {
		$wp_customize->add_setting( 'theme_header_top_bar_show_lang_select', array(
			'transport'         => 'postMessage',
			'default'           => 1,
			'sanitize_callback' => 'absint',
		) );
		$wp_customize->add_control( 'theme_header_top_bar_show_lang_select', array(
			'type'    => 'checkbox',
			'section' => 'theme_header_top_bar',
			'label'   => esc_html__( 'Show language selector', 'blockchain' ),
		) );
	}

	$wp_customize->add_setting( 'theme_header_top_bar_show_search', array(
		'transport'         => 'postMessage',
		'default'           => 1,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'theme_header_top_bar_show_search', array(
		'type'    => 'checkbox',
		'section' => 'theme_header_top_bar',
		'label'   => esc_html__( 'Show search', 'blockchain' ),
	) );

	$wp_customize->selective_refresh->add_partial( 'theme_header_top_bar', array(
		'selector'        => '.header',
		'render_callback' => 'blockchain_header',
		'settings'            => array( 'header_top_bar_text_1', 'header_top_bar_text_2', 'theme_header_top_bar_show_social_icons', 'theme_header_top_bar_show_search' ),
		'container_inclusive' => true,
	) );

	if ( class_exists( 'SitePress' ) ) {
		$partial           = $wp_customize->selective_refresh->get_partial( 'theme_header_top_bar' );
		$partial->settings = array_merge( $partial->settings, array(
			'theme_header_top_bar_show_lang_select',
		) );
	}
