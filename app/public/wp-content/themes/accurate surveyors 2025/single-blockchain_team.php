<?php get_header(); ?>

<?php get_template_part( 'template-parts/hero' ); ?>

<main class="main">

	<div class="container">

		<?php if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'single' ) ) : ?>

			<div class="team row">

				<div class="<?php blockchain_the_container_classes(); ?>">

					<?php while ( have_posts() ) : the_post(); ?>

						<article id="entry-<?php the_ID(); ?>" <?php post_class( 'entry single-team-member' ); ?>>

							<?php 
							$subtitle = get_post_meta( get_the_ID(), 'subtitle', true );
							$services = get_post_meta( get_the_ID(), 'team_services', true );
							// Hide default post header for team members
							?>

							<div class="single-team-layout">
								
								<!-- Left Column: Profile Image, Name, Title -->
								<div class="single-team-left">
									<?php if ( has_post_thumbnail() ) : ?>
										<div class="single-team-image">
											<?php the_post_thumbnail( 'blockchain_item', array( 'class' => 'team-profile-image' ) ); ?>
										</div>
									<?php endif; ?>
									
									<h1 class="single-team-name">
										<?php the_title(); ?>
									</h1>
									
									<?php if ( $subtitle ) : ?>
										<p class="single-team-title"><?php echo esc_html( $subtitle ); ?></p>
									<?php endif; ?>
								</div>

								<!-- Middle Column: Bio Content -->
								<div class="single-team-middle">
									<div class="single-team-bio">
										<?php the_content(); ?>
									</div>
								</div>

								<!-- Right Column: Services List -->
								<?php if ( $services ) : ?>
								<div class="single-team-right">
									<ul class="single-team-services">
										<?php 
										// Handle services as newline-separated list or array
										$services_list = is_array( $services ) ? $services : explode( "\n", $services );
										foreach ( $services_list as $service ) :
											$service = trim( $service );
											if ( ! empty( $service ) ) :
										?>
											<li class="single-team-service-item">
												<?php echo esc_html( $service ); ?>
											</li>
										<?php 
											endif;
										endforeach; 
										?>
									</ul>
								</div>
								<?php endif; ?>

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

<?php get_footer(); ?>
