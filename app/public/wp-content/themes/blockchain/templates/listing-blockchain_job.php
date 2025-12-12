<?php
/**
 * Template Name: Jobs listing
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

					<div class="entry-content">
						<?php the_content(); ?>


						<?php
							$q = new WP_Query( array(
								'post_type'      => 'blockchain_job',
								'posts_per_page' => - 1,
							) );
						?>
						<?php if ( $q->have_posts() ) : ?>
							<div class="table-responsive">
								<table class="table-list-jobs table-styled table-styled-lg">
									<thead>
										<tr>
											<th><?php esc_html_e( 'Position', 'blockchain' ); ?></th>
											<th><?php esc_html_e( 'Location', 'blockchain' ); ?></th>
											<th><?php esc_html_e( 'Department', 'blockchain' ); ?></th>
											<th><?php esc_html_e( 'Date', 'blockchain' ); ?></th>
											<th><?php esc_html_e( 'Salary', 'blockchain' ); ?></th>
										</tr>
									</thead>

									<tbody>
										<?php while ( $q->have_posts() ) : $q->the_post(); ?>
										<?php
											$location   = get_post_meta( get_the_ID(), 'blockchain_job_location', true );
											$department = get_post_meta( get_the_ID(), 'blockchain_job_department', true );
											$date       = get_post_meta( get_the_ID(), 'blockchain_job_date', true );
											$salary     = get_post_meta( get_the_ID(), 'blockchain_job_salary', true );
										?>
										<tr>
											<td><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></td>
											<td><?php echo esc_html( $location ); ?></td>
											<td><?php echo esc_html( $department ); ?></td>
											<td><?php echo esc_html( $date ); ?></td>
											<td><?php echo esc_html( $salary ); ?></td>
										</tr>
										<?php endwhile; ?>
										<?php wp_reset_postdata(); ?>
									</tbody>
								</table>
							</div>
						<?php endif; ?>

					</div>

				<?php endwhile; ?>

			</div>

			<?php get_sidebar(); ?>

		</div>
	</div>

</main>

<?php get_footer();
