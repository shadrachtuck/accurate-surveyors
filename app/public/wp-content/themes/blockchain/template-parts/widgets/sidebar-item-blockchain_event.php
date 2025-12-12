<div class="entry-item-media">
	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="entry-item-media-thumb">
			<a href="<?php the_permalink(); ?>">
				<?php the_post_thumbnail( 'blockchain_item_media' ); ?>
			</a>
		</figure>
	<?php endif; ?>

	<div class="entry-item-media-content">
		<p class="entry-item-media-title">
			<a href="<?php the_permalink(); ?>">
				<?php the_title(); ?>
			</a>
		</p>

		<div class="entry-meta">
			<?php
				$date     = get_post_meta( get_the_ID(), 'blockchain_event_date', true );
				$event_dt = strtotime( $date );
				if ( ! empty( $date ) && false !== $event_dt ) {
					$echo_date = date_i18n( get_option( 'date_format' ), $event_dt );
					?><span class="entry-meta-item"><i class="fa fa-calendar-o"></i> <?php echo wp_kses( sprintf( __( '<span class="screen-reader-text">Date:</span> %s', 'blockchain' ), $echo_date ), blockchain_get_allowed_tags() ); ?></span><?php
				}
			?>
		</div>
	</div>
</div>
