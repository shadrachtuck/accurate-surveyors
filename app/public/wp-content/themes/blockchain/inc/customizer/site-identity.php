<?php
	$custom_logo_args = get_theme_support( 'custom-logo' );
	$wp_customize->add_setting( 'theme_custom_logo_alt', array(
		'transport'         => 'postMessage',
		'default'           => '',
		'sanitize_callback' => 'blockchain_sanitize_intval_or_empty',
	) );
	$wp_customize->add_control( new WP_Customize_Cropped_Image_Control( $wp_customize, 'theme_custom_logo_alt', array(
		'label'         => __( 'Alternative Logo', 'blockchain' ),
		'description'   => __( 'Set this if you need a differently styled logo to appear when the header appears over the content.', 'blockchain' ),
		'section'       => 'title_tagline',
		'priority'      => 8,
		'height'        => $custom_logo_args[0]['height'],
		'width'         => $custom_logo_args[0]['width'],
		'flex_height'   => $custom_logo_args[0]['flex-height'],
		'flex_width'    => $custom_logo_args[0]['flex-width'],
		'button_labels' => array(
			'select'       => __( 'Select logo', 'blockchain' ),
			'change'       => __( 'Change logo', 'blockchain' ),
			'remove'       => __( 'Remove', 'blockchain' ),
			'default'      => __( 'Default', 'blockchain' ),
			'placeholder'  => __( 'No logo selected', 'blockchain' ),
			'frame_title'  => __( 'Select logo', 'blockchain' ),
			'frame_button' => __( 'Choose logo', 'blockchain' ),
		),
	) ) );

	$wp_customize->selective_refresh->get_partial( 'custom_logo' )->settings[] = 'theme_custom_logo_alt';


	$wp_customize->get_setting( 'blogname' )->transport = 'postMessage';
	$wp_customize->selective_refresh->add_partial( 'theme_blogname', array(
		'selector'        => '.site-logo a',
		'render_callback' => 'blockchain_customize_preview_blogname',
	) );

	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';
	$wp_customize->selective_refresh->add_partial( 'theme_blogdescription', array(
		'selector'        => '.site-tagline',
		'render_callback' => 'blockchain_customize_preview_blogdescription',
	) );

	$wp_customize->add_setting( 'limit_logo_size', array(
		'transport'         => 'postMessage',
		'default'           => 0,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'limit_logo_size', array(
		'type'        => 'checkbox',
		'section'     => 'title_tagline',
		'priority'    => 8,
		'label'       => esc_html__( 'Limit logo size (for Retina display)', 'blockchain' ),
		'description' => esc_html__( 'This option will limit the image size to half its width. You will need to upload your image in 2x the dimension you want to display it in.', 'blockchain' ),
	) );

	$wp_customize->selective_refresh->get_partial( 'theme_style' )->settings[] = 'limit_logo_size';


	$wp_customize->add_setting( 'show_site_title', array(
		'transport'         => 'postMessage',
		'default'           => 1,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'show_site_title', array(
		'type'    => 'checkbox',
		'section' => 'title_tagline',
		'label'   => esc_html__( 'Show site title', 'blockchain' ),
	) );

	$wp_customize->add_setting( 'show_site_description', array(
		'transport'         => 'postMessage',
		'default'           => 1,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'show_site_description', array(
		'type'    => 'checkbox',
		'section' => 'title_tagline',
		'label'   => esc_html__( 'Show site tagline', 'blockchain' ),
	) );

	$wp_customize->selective_refresh->add_partial( 'theme_site_branding', array(
		'selector'            => '.site-branding',
		'render_callback'     => 'blockchain_the_site_identity',
		'settings'            => array( 'custom_logo', 'show_site_title', 'show_site_description' ),
		'container_inclusive' => true,
	) );
