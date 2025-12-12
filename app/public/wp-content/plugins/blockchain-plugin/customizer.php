<?php
/**
 * Customizer Sections and Settings
 */

function blockchain_plugin_customize_register( $wp_customize ) {

	//
	// Other
	//
	$wp_customize->add_panel( 'theme_other', array(
		'title'                    => esc_html_x( 'Other', 'customizer section title', 'blockchain' ),
		'description'              => esc_html__( 'Other options affecting the whole site.', 'blockchain' ),
		'auto_expand_sole_section' => true,
		'priority'                 => 110,
	) );

	$wp_customize->add_section( 'theme_other_coinmarketcap', array(
		'title'    => esc_html_x( 'CoinMarketcap', 'customizer section title', 'blockchain' ),
		'panel'    => 'theme_other',
		'priority' => 20,
	) );

	$wp_customize->add_setting( 'title_coinmarketcap_api_key', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'title_coinmarketcap_api_key', array(
		'type'        => 'text',
		'section'     => 'theme_other_coinmarketcap',
		'label'       => esc_html__( 'CoinMarketCap API Key', 'blockchain' ),
		/* translators: %1$s is a URL */
		'description' => wp_kses( sprintf( __( 'From December 4th, 2018, CoinMarketCap will require a valid API Key to continue working. You can get your API key by registering <a href="%1$s">here</a>', 'blockchain' ), 'https://pro.coinmarketcap.com/' ), blockchain_plugin_get_allowed_tags( 'guide' ) ),
	) );



}
add_action( 'customize_register', 'blockchain_plugin_customize_register' );
