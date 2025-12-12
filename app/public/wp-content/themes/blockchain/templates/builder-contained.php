<?php
/**
 * Template Name: Page Builder Contained
 */

get_header(); ?>

<?php get_template_part( 'template-parts/hero' ); ?>

<main class="main-builder main-builder-contained">

	<div class="container">
		<div class="row">

			<div class="col-12">

				<?php while ( have_posts() ) : the_post(); ?>
					
						<div class="builder-content">
							<?php the_content(); ?>							
						</div>

					</article>					

				<?php endwhile; ?>

			</div>

		</div>
	</div>

</main>

<?php get_footer();
