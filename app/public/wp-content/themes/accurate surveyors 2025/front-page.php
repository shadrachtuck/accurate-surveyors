<?php
/**
 * Template Name: Front page
 */
get_header(); ?>

<?php
	// Original hero slider functionality
	$slider_id = get_post_meta( get_queried_object_id(), 'blockchain_front_slider_id', true );
	if ( $slider_id && function_exists( 'MaxSlider' ) ) {
		echo apply_filters( 'blockchain_front_page_maxslider_html', do_shortcode( sprintf( '[maxslider id="%s" template="home"]', intval( $slider_id ) ) ) );
	} else {
		get_template_part( 'template-parts/hero' );
	}
?>

<main class="main widget-sections">

	<?php dynamic_sidebar( 'frontpage' ); ?>

</main>

<?php get_footer(); ?>
