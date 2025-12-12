<?php $subtitle = get_post_meta( get_the_ID(), 'subtitle', true ); ?>
<div class="entry-item">
	<?php blockchain_the_post_thumbnail( 'blockchain_item_tall' ); ?>

	<div class="entry-item-content">
		<h2 class="entry-item-title">
			<a href="<?php the_permalink(); ?>">
				<?php the_title(); ?>
			</a>
		</h2>

		<?php if ( $subtitle ) : ?>
			<p class="entry-item-subtitle"><?php echo wp_kses( $subtitle, blockchain_get_allowed_tags( 'guide' ) ); ?></p>
		<?php endif; ?>

		<a href="<?php the_permalink(); ?>" class="entry-item-read-more">
			<?php echo wp_kses( __( 'Learn More <i class="fa fa-angle-right"></i>', 'blockchain' ), blockchain_get_allowed_tags() ); ?>
		</a>
	</div>
</div>
