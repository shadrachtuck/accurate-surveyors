<?php
	$info = blockchain_get_layout_info();

	if ( ! $info['has_sidebar'] ) {
		return;
	}
?>
<div class="<?php blockchain_the_sidebar_classes(); ?>">
	<div class="sidebar">
		<?php
			if ( is_page_template( 'templates/listing-blockchain_portfolio.php' ) || is_singular( 'blockchain_portfolio' ) ) {
				dynamic_sidebar( 'portfolio' );
			} elseif ( is_page_template( 'templates/listing-blockchain_service.php' ) || is_singular( 'blockchain_service' ) ) {
				dynamic_sidebar( 'services' );
			} elseif ( is_page_template( 'templates/listing-blockchain_cstudy.php' ) || is_singular( 'blockchain_cstudy' ) ) {
				dynamic_sidebar( 'case-studies' );
			} elseif ( is_page_template( 'templates/listing-blockchain_team.php' ) || is_singular( 'blockchain_team' ) ) {
				dynamic_sidebar( 'teams' );
			} elseif ( is_page_template( 'templates/listing-blockchain_job.php' ) || is_singular( 'blockchain_job' ) ) {
				dynamic_sidebar( 'jobs' );
			} elseif ( is_page_template( 'templates/listing-blockchain_event.php' ) || is_singular( 'blockchain_event' ) ) {
				dynamic_sidebar( 'events' );
			} elseif ( is_page_template( 'templates/listing-blockchain_testimon.php' ) || is_singular( 'blockchain_testimon' ) ) {
				dynamic_sidebar( 'testimonials' );
			} elseif ( blockchain_is_woocommerce_with_sidebar() ) {
				dynamic_sidebar( 'shop' );
			} elseif ( ! is_page() ) {
				dynamic_sidebar( 'sidebar-1' );
			} else {
				dynamic_sidebar( 'sidebar-2' );
			}
		?>
	</div>
</div>
