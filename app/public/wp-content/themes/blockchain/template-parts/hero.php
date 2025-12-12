<?php
	$hero = blockchain_get_hero_data();

	if ( ! $hero['show'] ) {
		return;
	}

	$text_align = $hero['text_align'] ? sprintf( 'text-%s', $hero['text_align'] ) : '';

	do_action( 'blockchain_before_hero', $hero );

	?>
	<div class="<?php blockchain_the_hero_classes(); ?>">

		<?php if ( $hero['video_info']['supported'] ) : ?>
			<div class="ci-video-wrap">
				<div class="ci-video-background" data-video-id="<?php echo esc_attr( $hero['video_info']['video_id'] ); ?>" data-video-type="<?php echo esc_attr( $hero['video_info']['provider'] ); ?>">
					<?php if ( 'youtube' === $hero['video_info']['provider'] ) : ?>
						<div id="youtube-vid"></div>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="page-hero-content <?php echo esc_attr( $text_align ); ?>">
						<?php if ( $hero['title'] ) : ?>
							<h2 class="page-hero-title"><?php echo wp_kses( $hero['title'], blockchain_get_allowed_tags() ); ?></h2>
						<?php endif; ?>

						<?php if ( $hero['subtitle'] ) : ?>
							<p class="page-hero-subtitle"><?php echo wp_kses( $hero['subtitle'], blockchain_get_allowed_tags( 'guide' ) ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

	</div>
	<?php

	do_action( 'blockchain_after_hero', $hero );
