<?php get_header(); ?>

<?php get_template_part( 'template-parts/hero' ); ?>

<main class="main">

	<div class="container">

		<?php if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'single' ) ) : ?>

			<div class="row">

				<div class="<?php blockchain_the_container_classes();?>">

					<?php while ( have_posts() ) : the_post(); ?>

						<article id="entry-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>

							<?php blockchain_the_post_header(); ?>

							<?php blockchain_the_post_thumbnail(); ?>

							<div class="entry-content">
								<?php if ( has_excerpt() ) : ?>
									<div class="entry-content-intro">
										<?php the_excerpt(); ?>
									</div>
								<?php endif; ?>

								<?php the_content(); ?>
							</div>

						</article>

						<?php comments_template(); ?>

					<?php endwhile; ?>

				</div>

				<?php get_sidebar(); ?>

			</div>

		<?php endif; ?>

	</div>

</main>

<?php get_footer();
