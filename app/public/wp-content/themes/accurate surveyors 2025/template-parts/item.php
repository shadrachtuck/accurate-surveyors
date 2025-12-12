<?php
// Check if this is a service post type and use service card template
if ( get_post_type() === 'blockchain_service' ) {
	get_template_part( 'template-parts/item', 'blockchain_service' );
	return;
}
?>
<div class="entry-item">
	<?php blockchain_the_post_thumbnail( 'blockchain_item' ); ?>

	<div class="entry-item-content">
		<h2 class="entry-item-title">
			<a href="<?php the_permalink(); ?>">
				<?php the_title(); ?>
			</a>
		</h2>

		<!-- <div class="entry-item-excerpt">
			<?php the_excerpt(); ?>
		</div> -->

		<a href="<?php the_permalink(); ?>" class="entry-item-read-more">
			<?php echo wp_kses( __( 'Learn More <i class="fa fa-angle-right"></i>', 'blockchain' ), blockchain_get_allowed_tags() ); ?>
		</a>
	</div>
</div>
