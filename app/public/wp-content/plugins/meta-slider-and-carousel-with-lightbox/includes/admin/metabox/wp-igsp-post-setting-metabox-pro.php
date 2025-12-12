<?php
/**
 * Function Custom meta box for Premium
 * 
 * @package Meta slider and carousel with lightbox
 * @since 1.5.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} ?>

<?php /*
<div class="pro-notice"><strong><?php echo sprintf( __( 'Utilize this <a href="%s" target="_blank">Premium Features (With Risk-Free 30 days money back guarantee)</a> to get best of this plugin with Annual or Lifetime bundle deal.', 'meta-slider-and-carousel-with-lightbox'), WP_IGSP_PLUGIN_LINK_UNLOCK); ?></strong></div>

<div class="pro-notice">
	<strong>
		<?php echo sprintf( __( 'Try All These <a href="%s" target="_blank">PRO Features in Essential Bundle Free For 5 Days.</a>', 'meta-slider-and-carousel-with-lightbox'), WP_IGSP_PLUGIN_LINK_UNLOCK); ?>
	</strong>
</div> -->
*/ ?>

<!-- <div class="igsp-black-friday-banner-wrp">
	<a href="<?php // echo esc_url( WP_IGSP_PLUGIN_LINK_UNLOCK ); ?>" target="_blank"><img style="width: 100%;" src="<?php // echo esc_url( WP_IGSP_URL ); ?>assets/images/black-friday-banner.png" alt="black-friday-banner" /></a>
</div> -->

<strong style="color:#2ECC71; font-weight: 700;"><?php echo sprintf( __( ' <a href="%s" target="_blank" style="color:#2ECC71;">Upgrade To Pro</a> and Get Designs, Optimization, Security, Backup, Migration Solutions @ one stop.', 'meta-slider-and-carousel-with-lightbox'), WP_IGSP_PLUGIN_LINK_UNLOCK); ?></strong>

<table class="form-table wp-igsp-metabox-table">
	<tbody>
		<tr class="wp-igsp-pro-feature">
			<th>
				<?php esc_html_e('Layouts ', 'meta-slider-and-carousel-with-lightbox'); ?><span class="wp-igsp-pro-tag"><?php esc_html_e('PRO','meta-slider-and-carousel-with-lightbox');?></span>
			</th>
			<td>
				<span class="description"><b><?php esc_html_e('5 (Slider, Carousel, Variable width', 'meta-slider-and-carousel-with-lightbox'); ?></b><?php esc_html_e(' In lite version only 2 layout.', 'meta-slider-and-carousel-with-lightbox'); ?></span>
			</td>
		</tr>

		<tr class="wp-igsp-pro-feature">
			<th>
				<?php esc_html_e('Designs ', 'meta-slider-and-carousel-with-lightbox'); ?><span class="wp-igsp-pro-tag"><?php esc_html_e('PRO','meta-slider-and-carousel-with-lightbox');?></span>
			</th>
			<td>
				<span class="description"><b>15+.</b><?php esc_html_e(' In lite version only one design.', 'meta-slider-and-carousel-with-lightbox'); ?></span>
			</td>
		</tr>

		<tr class="wp-igsp-pro-feature">
			<th>
				<?php esc_html_e('Description Hide/Show ', 'meta-slider-and-carousel-with-lightbox'); ?><span class="wp-igsp-pro-tag"><?php esc_html_e('PRO','meta-slider-and-carousel-with-lightbox');?></span>
			</th>
			<td>
				<span class="description"><?php esc_html_e('Option to display slider/carousel description hide or show.', 'meta-slider-and-carousel-with-lightbox'); ?></span>
			</td>
		</tr>

		<tr class="wp-igsp-pro-feature">
			<th>
				<?php esc_html_e('Navigation columns setting ', 'meta-slider-and-carousel-with-lightbox'); ?><span class="wp-igsp-pro-tag"><?php esc_html_e('PRO','meta-slider-and-carousel-with-lightbox');?></span>
			</th>
			<td>
				<span class="description"><b><?php esc_html_e('Number of image columns show in navigation.', 'meta-slider-and-carousel-with-lightbox'); ?></b></span>
			</td>
		</tr>

		<tr class="wp-igsp-pro-feature">
			<th>
				<?php esc_html_e('WP Templating Features ', 'meta-slider-and-carousel-with-lightbox'); ?><span class="wp-igsp-pro-tag"><?php esc_html_e('PRO','meta-slider-and-carousel-with-lightbox');?></span>
			</th>
			<td>
				<span class="description"><b><?php esc_html_e('You can modify plugin html/designs in your current theme.', 'meta-slider-and-carousel-with-lightbox'); ?></b></span>
			</td>
		</tr>

		<tr class="wp-igsp-pro-feature">
			<th>
				<?php esc_html_e('Shortcode Generator ', 'meta-slider-and-carousel-with-lightbox'); ?><span class="wp-igsp-pro-tag"><?php esc_html_e('PRO','meta-slider-and-carousel-with-lightbox');?></span>
			</th>
			<td>
				<span class="description"><?php esc_html_e('Play with all shortcode parameters with preview panel. No documentation required.' , 'meta-slider-and-carousel-with-lightbox'); ?></span>
			</td>
		</tr>

		<tr class="wp-igsp-pro-feature">
			<th>
				<?php esc_html_e('Drag & Drop Slide Order Change ', 'meta-slider-and-carousel-with-lightbox'); ?><span class="wp-igsp-pro-tag"><?php esc_html_e('PRO','meta-slider-and-carousel-with-lightbox');?></span>
			</th>
			<td>
				<span class="description"><?php esc_html_e('Arrange your desired slides with your desired order and display.' , 'meta-slider-and-carousel-with-lightbox'); ?></span>
			</td>
		</tr>

		<tr class="wp-igsp-pro-feature">
			<th>
				<?php esc_html_e('Page Builder Support ', 'meta-slider-and-carousel-with-lightbox'); ?><span class="wp-igsp-pro-tag"><?php esc_html_e('PRO','meta-slider-and-carousel-with-lightbox');?></span>
			</th>
			<td>
				<span class="description"><b><?php esc_html_e('Gutenberg Block, Elementor, Bevear Builder, SiteOrigin, Divi, Visual Composer and Fusion Page Builder Support', 'meta-slider-and-carousel-with-lightbox'); ?></b></span>
			</td>
		</tr>

		<tr class="wp-igsp-pro-feature">
			<th>
				<?php esc_html_e('Exclude Meta Slider Post and Exclude Some Categories ', 'meta-slider-and-carousel-with-lightbox'); ?><span class="wp-igsp-pro-tag"><?php esc_html_e('PRO','meta-slider-and-carousel-with-lightbox');?></span>
			</th>
			<td>
				<span class="description"><?php esc_html_e('Do not display the meta slider & Do not display the meta slider for particular categories.' , 'meta-slider-and-carousel-with-lightbox'); ?></span>
			</td>
		</tr>
	</tbody>
</table><!-- end .wp-igsp-metabox-table -->