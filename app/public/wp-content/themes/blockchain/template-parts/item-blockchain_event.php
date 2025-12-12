<div class="entry-item">
	<?php blockchain_the_post_thumbnail( 'blockchain_item_tall' ); ?>

	<div class="entry-item-content">
		<h2 class="entry-item-title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>

		<div class="entry-meta entry-block-meta">
			<?php blockchain_the_block_meta_entry_meta_event_fields(); ?>
		</div>

		<a href="<?php the_permalink(); ?>" class="entry-item-read-more">
			<?php echo wp_kses( __( 'Learn More <i class="fa fa-angle-right"></i>', 'blockchain' ), blockchain_get_allowed_tags() ); ?>
		</a>
	</div>
</div>
