<?php $subtitle = get_post_meta( get_the_ID(), 'subtitle', true ); ?>
<blockquote class="item-testimonial">
	<div class="item-testimonial-content">
		<?php the_content(); ?>
	</div>

	<cite class="item-testimonial-cite">
		<?php the_post_thumbnail( 'blockchain_item_media', array( 'class' => 'item-testimonial-cite-thumb' ) ); ?>

		<span class="item-testimonial-cite-author">
			<span class="item-testimonial-cite-name"><?php the_title(); ?></span>

			<?php if ( $subtitle ) : ?>
				<span class="item-testimonial-cite-subtitle"><?php echo wp_kses( $subtitle, blockchain_get_allowed_tags( 'guide' ) ); ?></span>
			<?php endif; ?>
		</span>
	</cite>
</blockquote>
