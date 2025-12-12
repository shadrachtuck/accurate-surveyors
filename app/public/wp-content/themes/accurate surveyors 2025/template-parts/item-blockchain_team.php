<?php $subtitle = get_post_meta( get_the_ID(), 'subtitle', true ); ?>
<div class="entry-item team-card">
	<div class="team-card-image-wrapper">
		<?php blockchain_the_post_thumbnail( 'blockchain_item' ); ?>
		<div class="team-card-overlay">
			<a href="<?php the_permalink(); ?>" class="entry-item-read-more">
				<?php echo wp_kses( __( 'Learn More <i class="fa fa-angle-right"></i>', 'blockchain' ), blockchain_get_allowed_tags() ); ?>
			</a>
		</div>
	</div>

	<div class="entry-item-content team-card-content">
		<h2 class="entry-item-title team-card-name">
			<a href="<?php the_permalink(); ?>">
				<?php the_title(); ?>
			</a>
		</h2>

		<?php if ( $subtitle ) : ?>
			<p class="entry-item-subtitle team-card-title"><?php echo wp_kses( $subtitle, blockchain_get_allowed_tags( 'guide' ) ); ?></p>
		<?php endif; ?>
	</div>
</div>
