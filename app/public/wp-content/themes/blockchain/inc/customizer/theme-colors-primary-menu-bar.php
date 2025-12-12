<?php
	$wp_customize->add_setting( 'header_primary_menu_bg_color', array(
		'transport'         => 'postMessage',
		'default'           => '',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'header_primary_menu_bg_color', array(
		'section' => 'theme_colors_primary_menu_bar',
		'label'   => esc_html__( 'Background color', 'blockchain' ),
	) ) );

	$wp_customize->add_setting( 'header_primary_menu_bg_image', array(
		'transport'         => 'postMessage',
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'header_primary_menu_bg_image', array(
		'section' => 'theme_colors_primary_menu_bar',
		'label'   => esc_html__( 'Background image', 'blockchain' ),
	) ) );

	$wp_customize->add_setting( 'header_primary_menu_bg_image_repeat', array(
		'transport'         => 'postMessage',
		'default'           => 'no-repeat',
		'sanitize_callback' => 'blockchain_sanitize_image_repeat',
	) );
	$wp_customize->add_control( 'header_primary_menu_bg_image_repeat', array(
		'type'    => 'select',
		'section' => 'theme_colors_primary_menu_bar',
		'label'   => esc_html__( 'Image repeat', 'blockchain' ),
		'choices' => blockchain_get_image_repeat_choices(),
	) );

	$wp_customize->add_setting( 'header_primary_menu_bg_image_position_x', array(
		'transport'         => 'postMessage',
		'default'           => 'center',
		'sanitize_callback' => 'blockchain_sanitize_image_position_x',
	) );
	$wp_customize->add_control( 'header_primary_menu_bg_image_position_x', array(
		'type'    => 'select',
		'section' => 'theme_colors_primary_menu_bar',
		'label'   => esc_html__( 'Image horizontal position', 'blockchain' ),
		'choices' => blockchain_get_image_position_x_choices(),
	) );

	$wp_customize->add_setting( 'header_primary_menu_bg_image_position_y', array(
		'transport'         => 'postMessage',
		'default'           => 'center',
		'sanitize_callback' => 'blockchain_sanitize_image_position_y',
	) );
	$wp_customize->add_control( 'header_primary_menu_bg_image_position_y', array(
		'type'    => 'select',
		'section' => 'theme_colors_primary_menu_bar',
		'label'   => esc_html__( 'Image vertical position', 'blockchain' ),
		'choices' => blockchain_get_image_position_y_choices(),
	) );

	$wp_customize->add_setting( 'header_primary_menu_bg_image_attachment', array(
		'transport'         => 'postMessage',
		'default'           => 'scroll',
		'sanitize_callback' => 'blockchain_sanitize_image_attachment',
	) );
	$wp_customize->add_control( 'header_primary_menu_bg_image_attachment', array(
		'type'    => 'select',
		'section' => 'theme_colors_primary_menu_bar',
		'label'   => esc_html__( 'Image attachment', 'blockchain' ),
		'choices' => blockchain_get_image_attachment_choices(),
	) );

	$wp_customize->add_setting( 'header_primary_menu_bg_image_cover', array(
		'transport'         => 'postMessage',
		'default'           => 1,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'header_primary_menu_bg_image_cover', array(
		'type'    => 'checkbox',
		'section' => 'theme_colors_primary_menu_bar',
		'label'   => esc_html__( 'Scale the image to cover its containing area.', 'blockchain' ),
	) );

	$wp_customize->add_setting( 'header_primary_menu_text_color', array(
		'transport'         => 'postMessage',
		'default'           => '',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'header_primary_menu_text_color', array(
		'section' => 'theme_colors_primary_menu_bar',
		'label'   => esc_html__( 'Text color', 'blockchain' ),
	) ) );

	$wp_customize->add_setting( 'header_primary_menu_text_on_bg_color', array(
		'transport'         => 'postMessage',
		'default'           => '',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'header_primary_menu_text_on_bg_color', array(
		'section' => 'theme_colors_primary_menu_bar',
		'label'   => esc_html__( 'Text color when on top of background', 'blockchain' ),
	) ) );

	$wp_customize->add_setting( 'header_primary_menu_active_color', array(
		'transport'         => 'postMessage',
		'default'           => '',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'header_primary_menu_active_color', array(
		'section' => 'theme_colors_primary_menu_bar',
		'label'   => esc_html__( 'Menu active & hover color', 'blockchain' ),
	) ) );

	$wp_customize->add_setting( 'header_primary_submenu_bg_color', array(
		'transport'         => 'postMessage',
		'default'           => '',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'header_primary_submenu_bg_color', array(
		'section' => 'theme_colors_primary_menu_bar',
		'label'   => esc_html__( 'Sub-menu background color', 'blockchain' ),
	) ) );

	$wp_customize->add_setting( 'header_primary_submenu_text_color', array(
		'transport'         => 'postMessage',
		'default'           => '',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'header_primary_submenu_text_color', array(
		'section' => 'theme_colors_primary_menu_bar',
		'label'   => esc_html__( 'Sub-menu text color', 'blockchain' ),
	) ) );

	$wp_customize->add_setting( 'header_primary_submenu_active_text_color', array(
		'transport'         => 'postMessage',
		'default'           => '',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'header_primary_submenu_active_text_color', array(
		'section' => 'theme_colors_primary_menu_bar',
		'label'   => esc_html__( 'Sub-menu active text color', 'blockchain' ),
	) ) );

	$partial = $wp_customize->selective_refresh->get_partial( 'theme_style' );
	$partial->settings = array_merge( $partial->settings, array(
		'header_primary_menu_bg_color',
		'header_primary_menu_bg_image',
		'header_primary_menu_bg_image_repeat',
		'header_primary_menu_bg_image_position_x',
		'header_primary_menu_bg_image_position_y',
		'header_primary_menu_bg_image_attachment',
		'header_primary_menu_bg_image_cover',
		'header_primary_menu_text_color',
		'header_primary_menu_active_color',
		'header_primary_submenu_bg_color',
		'header_primary_submenu_text_color',
		'header_primary_submenu_active_text_color',
		'header_primary_menu_text_on_bg_color',
	) );
