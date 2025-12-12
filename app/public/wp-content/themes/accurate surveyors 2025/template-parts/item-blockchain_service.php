<div class="entry-item service-card">
	<div class="service-card-image-wrapper">
		<?php blockchain_the_post_thumbnail( 'blockchain_item' ); ?>
		<div class="service-card-overlay">
			<a href="<?php the_permalink(); ?>" class="entry-item-read-more">
				<?php echo wp_kses( __( 'Learn More <i class="fa fa-angle-right"></i>', 'blockchain' ), blockchain_get_allowed_tags() ); ?>
			</a>
		</div>
	</div>

	<div class="entry-item-content service-card-content">
		<h2 class="entry-item-title service-card-name">
			<a href="<?php the_permalink(); ?>">
				<?php the_title(); ?>
			</a>
		</h2>
	</div>
</div>
