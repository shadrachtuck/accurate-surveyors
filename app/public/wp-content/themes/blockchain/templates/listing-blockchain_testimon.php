<?php
/**
 * Template Name: Testimonials listing
 */

get_header(); ?>

<?php get_template_part( 'template-parts/hero' ); ?>

<main class="main">

	<div class="container">
		<div class="row">

			<div class="<?php blockchain_the_container_classes(); ?>">

				<?php while ( have_posts() ) : the_post(); ?>

					<?php blockchain_the_post_header(); ?>

					<?php if ( blockchain_has_sidebar() ) {
						blockchain_the_post_thumbnail();
					} else {
						blockchain_the_post_thumbnail( 'blockchain_fullwidth' );
					} ?>

					<?php if ( ! blockchain_empty_content() ) : ?>
						<div class="entry-content">
							<?php the_content(); ?>
						</div>
					<?php endif; ?>

					<?php
						$data = blockchain_post_type_listing_get_template_data( get_the_ID(), 'blockchain_testimon' );
						$q    = new WP_Query( $data['query_args'] );
					?>
					<div class="row row-items <?php echo esc_attr( implode( ' ', $data['container_classes'] ) ); ?>">
						<?php while ( $q->have_posts() ) : $q->the_post(); ?>
							<div class="<?php echo esc_attr( blockchain_get_columns_classes( $data['columns'] ) ); ?>">
								<?php get_template_part( 'template-parts/item', get_post_type() ); ?>
							</div>
						<?php endwhile; ?>
						<?php wp_reset_postdata(); ?>
					</div>

					<?php blockchain_posts_pagination( array(), $q ); ?>

				<?php endwhile; ?>

			</div>

			<?php get_sidebar(); ?>

		</div>
	</div>

</main>

<?php get_footer();
