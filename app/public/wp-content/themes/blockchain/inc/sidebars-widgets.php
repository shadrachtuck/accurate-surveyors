<?php
/**
 * Blockchain sidebars and widgets related functions.
 */

/**
 * Register widget areas.
 */
function blockchain_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Blog', 'blockchain' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Widgets added here will appear on the blog section.', 'blockchain' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Page', 'blockchain' ),
		'id'            => 'sidebar-2',
		'description'   => esc_html__( 'Widgets added here will appear on the static pages.', 'blockchain' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Portfolio', 'blockchain' ),
		'id'            => 'portfolio',
		'description'   => esc_html__( 'Widgets added here will appear on portfolio-related pages.', 'blockchain' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Services', 'blockchain' ),
		'id'            => 'services',
		'description'   => esc_html__( 'Widgets added here will appear on service-related pages.', 'blockchain' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Case Studies', 'blockchain' ),
		'id'            => 'case-studies',
		'description'   => esc_html__( 'Widgets added here will appear on case study-related pages.', 'blockchain' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Teams', 'blockchain' ),
		'id'            => 'teams',
		'description'   => esc_html__( 'Widgets added here will appear on team-related pages.', 'blockchain' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Jobs', 'blockchain' ),
		'id'            => 'jobs',
		'description'   => esc_html__( 'Widgets added here will appear on job-related pages.', 'blockchain' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Events', 'blockchain' ),
		'id'            => 'events',
		'description'   => esc_html__( 'Widgets added here will appear on event-related pages.', 'blockchain' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Testimonials', 'blockchain' ),
		'id'            => 'testimonials',
		'description'   => esc_html__( 'Widgets added here will appear on testimonial-related pages.', 'blockchain' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Shop', 'blockchain' ),
		'id'            => 'shop',
		'description'   => esc_html__( 'Widgets added here will appear on the shop page.', 'blockchain' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Front Page', 'blockchain' ),
		'id'            => 'frontpage',
		'description'   => esc_html__( 'These widgets appear on pages that have the "Front page" template assigned.', 'blockchain' ),
		'before_widget' => '<section id="%1$s" class="widget-section %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="section-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer - 1st column', 'blockchain' ),
		'id'            => 'footer-1',
		'description'   => esc_html__( 'Widgets added here will appear on the first footer column.', 'blockchain' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Footer - 2nd column', 'blockchain' ),
		'id'            => 'footer-2',
		'description'   => esc_html__( 'Widgets added here will appear on the second footer column.', 'blockchain' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Footer - 3rd column', 'blockchain' ),
		'id'            => 'footer-3',
		'description'   => esc_html__( 'Widgets added here will appear on the third footer column.', 'blockchain' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Footer - 4th column', 'blockchain' ),
		'id'            => 'footer-4',
		'description'   => esc_html__( 'Widgets added here will appear on the fourth footer column.', 'blockchain' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
}
add_action( 'widgets_init', 'blockchain_widgets_init' );


function blockchain_load_widgets() {
	require get_template_directory() . '/inc/widgets/socials.php';
	require get_template_directory() . '/inc/widgets/contact.php';
	require get_template_directory() . '/inc/widgets/schedule.php';
	require get_template_directory() . '/inc/widgets/latest-post-type.php';

	require get_template_directory() . '/inc/widgets/home-latest-post-type.php';
	require get_template_directory() . '/inc/widgets/home-post-type-items.php';
	require get_template_directory() . '/inc/widgets/home-instagram.php';

	register_widget( 'CI_Widget_Socials' );
	register_widget( 'CI_Widget_Contact' );
	register_widget( 'CI_Widget_Schedule' );
	register_widget( 'CI_Widget_Latest_Post_Type' );

	register_widget( 'CI_Widget_Home_Latest_Post_Type' );
	register_widget( 'CI_Widget_Home_Post_Type_Items' );
	if ( class_exists( 'CI_Widget_Home_Instagram' ) ) {
		register_widget( 'CI_Widget_Home_Instagram' );
	}

	require get_template_directory() . '/inc/widgets/buttons.php';
	register_widget( 'CI_Widget_Buttons' );

	require get_template_directory() . '/inc/widgets/callout.php';
	register_widget( 'CI_Widget_Callout' );

	require get_template_directory() . '/inc/widgets/home-hero-callout.php';
	register_widget( 'CI_Widget_Home_Hero_Callout' );

	require get_template_directory() . '/inc/widgets/home-brands.php';
	register_widget( 'CI_Widget_Home_Brands' );

}
add_action( 'widgets_init', 'blockchain_load_widgets' );


function blockchain_get_fullwidth_sidebars() {
	return apply_filters( 'blockchain_fullwidth_sidebars', array(
		'frontpage',
	) );
}


function blockchain_get_fullwidth_widgets() {
	return apply_filters( 'blockchain_fullwidth_widgets', array(
		'ci-home-instagram',
		'ci-home-latest-post-type',
		'ci-home-post-type-items',
		'ci-home-brands',
		'ci-home-hero-callout',
	) );
}


function blockchain_wrap_non_fullwidth_widgets( $params ) {
	$sidebar = $params[0]['id'];
	if ( is_admin() || ! in_array( $sidebar, blockchain_get_fullwidth_sidebars(), true ) ) {
		return $params;
	}

	$fullwidth_widgets = blockchain_get_fullwidth_widgets();

	$pattern = '/\-' . $params[1]['number'] . '$/';
	$widget  = $params[0]['widget_id'];
	$widget  = preg_replace( $pattern, '', $widget, 1 );

	$wrap_widget = ! in_array( $widget, $fullwidth_widgets, true );
	$wrap_widget = apply_filters( 'blockchain_wrap_non_fullwidth_widget', $wrap_widget, $widget, $sidebar, $params );

	if ( $wrap_widget ) {
		$params[0]['before_widget'] = $params[0]['before_widget'] . '<div class="container"><div class="row"><div class="col-12">';
		$params[0]['after_widget']  = '</div></div></div>' . $params[0]['after_widget'];
		$params[0]['before_title']  = '<div class="section-heading">' . $params[0]['before_title'];
		$params[0]['after_title']   = $params[0]['after_title'] . '</div>';
	}

	return $params;
}
add_filter( 'dynamic_sidebar_params', 'blockchain_wrap_non_fullwidth_widgets' );
