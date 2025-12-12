<?php
	$wp_customize->add_setting( 'title_portfolio_related_title', array(
		'default'           => esc_html__( 'Related work', 'blockchain' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'title_portfolio_related_title', array(
		'type'    => 'text',
		'section' => 'theme_titles_portfolio',
		'label'   => esc_html__( 'Related portfolio title', 'blockchain' ),
	) );

	$wp_customize->add_setting( 'title_portfolio_related_subtitle', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'title_portfolio_related_subtitle', array(
		'type'    => 'text',
		'section' => 'theme_titles_portfolio',
		'label'   => esc_html__( 'Related portfolio subtitle', 'blockchain' ),
	) );
