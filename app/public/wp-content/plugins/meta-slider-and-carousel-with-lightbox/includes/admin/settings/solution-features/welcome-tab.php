<?php
/**
 * Admin Class
 *
 * Handles the Admin side functionality of plugin
 *
 * @package Meta Slider and Carousel with Lightbox
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} ?>

<div id="igsp_welcome_tabs" class="igsp-vtab-cnt igsp_welcome_tabs igsp-clearfix">
<?php /*	
	<div class="igsp-deal-offer-wrap">
		<h3 style="font-weight: bold; font-size: 30px; color:#ffef00; text-align:center; margin: 15px 0 5px 0;">Why Invest Time On Free Version?</h3>

		<h3 style="font-size: 18px; text-align:center; margin:0; color:#fff;">Explore Meta Slider and Carousel with Lightbox Pro with Essential Bundle Free for 5 Days.</h3>			

		<div class="igsp-deal-free-offer">
			<a href="<?php echo esc_url( WP_IGSP_PLUGIN_BUNDLE_LINK ); ?>" target="_blank" class="igsp-sf-free-btn"><span class="dashicons dashicons-cart"></span> Try Pro For 5 Days Free</a>
		</div>
	</div>
*/ ?>

	<!-- <div class="igsp-black-friday-banner-wrp">
		<a href="<?php // echo esc_url( WP_IGSP_PLUGIN_BUNDLE_LINK ); ?>" target="_blank"><img style="width: 100%;" src="<?php // echo esc_url( WP_IGSP_URL ); ?>assets/images/black-friday-banner.png" alt="black-friday-banner" /></a>
	</div> -->

	<div class="igsp-black-friday-banner-wrp" style="background:#e1ecc8;padding: 20px 20px 40px; border-radius:5px; text-align:center;margin-bottom: 40px;">
		<h2 style="font-size:30px; margin-bottom:10px;"><span style="color:#0055fb;">Meta Slider and Carousel with Lightbox</span> is included in <span style="color:#0055fb;">Essential Plugin Bundle</span> </h2> 
		<h4 style="font-size: 18px;margin-top: 0px;color: #ff5d52;margin-bottom: 24px;">Now get Designs, Optimization, Security, Backup, Migration Solutions @ one stop. </h4>

		<div class="igsp-black-friday-feature">

			<div class="igsp-inner-deal-class" style="width:40%;">
				<div class="igsp-inner-Bonus-class">Bonus</div>
				<div class="igsp-image-logo" style="font-weight: bold;font-size: 26px;color: #222;"><img style="width: 34px; height:34px;vertical-align: middle;margin-right: 5px;" class="igsp-img-logo" src="<?php echo esc_url( WP_IGSP_URL ); ?>assets/images/essential-logo-small.png" alt="essential-logo" /><span class="igsp-esstial-name" style="color:#0055fb;">Essential </span>Plugin</div>
				<div class="igsp-sub-heading" style="font-size: 16px;text-align: left;font-weight: bold;color: #222;margin-bottom: 10px;">Includes All premium plugins at no extra cost.</div>
				<a class="igsp-sf-btn" href="<?php echo esc_url( WP_IGSP_PLUGIN_BUNDLE_LINK ); ?>" target="_blank">Grab The Deal</a>
			</div>

			<div class="igsp-main-list-class" style="width:60%;">
				<div class="igsp-inner-list-class">
					<div class="igsp-list-img-class"><img src="<?php echo esc_url( WP_IGSP_URL ); ?>assets/images/logo-image/img-slider.png" alt="essential-logo" /> Image Slider</li></div>

					<div class="igsp-list-img-class"><img src="<?php echo esc_url( WP_IGSP_URL ); ?>assets/images/logo-image/advertising.png" alt="essential-logo" /> Publication</li></div>

					<div class="igsp-list-img-class"><img src="<?php echo esc_url( WP_IGSP_URL ); ?>assets/images/logo-image/marketing.png" alt="essential-logo" /> Marketing</li></div>

					<div class="igsp-list-img-class"><img src="<?php echo esc_url( WP_IGSP_URL ); ?>assets/images/logo-image/photo-album.png" alt="essential-logo" /> Photo album</li></div>

					<div class="igsp-list-img-class"><img src="<?php echo esc_url( WP_IGSP_URL ); ?>assets/images/logo-image/showcase.png" alt="essential-logo" /> Showcase</li></div>

					<div class="igsp-list-img-class"><img src="<?php echo esc_url( WP_IGSP_URL ); ?>assets/images/logo-image/shopping-bag.png" alt="essential-logo" /> WooCommerce</li></div>

					<div class="igsp-list-img-class"><img src="<?php echo esc_url( WP_IGSP_URL ); ?>assets/images/logo-image/performance.png" alt="essential-logo" /> Performance</li></div>

					<div class="igsp-list-img-class"><img src="<?php echo esc_url( WP_IGSP_URL ); ?>assets/images/logo-image/security.png" alt="essential-logo" /> Security</li></div>

					<div class="igsp-list-img-class"><img src="<?php echo esc_url( WP_IGSP_URL ); ?>assets/images/logo-image/forms.png" alt="essential-logo" /> Pro Forms</li></div>

					<div class="igsp-list-img-class"><img src="<?php echo esc_url( WP_IGSP_URL ); ?>assets/images/logo-image/seo.png" alt="essential-logo" /> SEO</li></div>

					<div class="igsp-list-img-class"><img src="<?php echo esc_url( WP_IGSP_URL ); ?>assets/images/logo-image/backup.png" alt="essential-logo" /> Backups</li></div>

					<div class="igsp-list-img-class"><img src="<?php echo esc_url( WP_IGSP_URL ); ?>assets/images/logo-image/White-labeling.png" alt="essential-logo" /> Migration</li></div>
				</div>
			</div>
		</div>
		<div class="igsp-main-feature-item">
			<div class="igsp-inner-feature-item">
				<div class="igsp-list-feature-item">
					<img src="<?php echo esc_url( WP_IGSP_URL ); ?>assets/images/logo-image/layers.png" alt="layer" />
					<h5>Site management</h5>
					<p>Manage, update, secure & optimize unlimited sites.</p>
				</div>
				<div class="igsp-list-feature-item">
					<img src="<?php echo esc_url( WP_IGSP_URL ); ?>assets/images/logo-image/risk.png" alt="backup" />
					<h5>Backup storage</h5>
					<p>Secure sites with auto backups and easy restore.</p>
				</div>
				<div class="igsp-list-feature-item">
					<img src="<?php echo esc_url( WP_IGSP_URL ); ?>assets/images/logo-image/support.png" alt="support" />
					<h5>Support</h5>
					<p>Get answers on everything WordPress at anytime.</p>
				</div>
			</div>
		</div>
		<a class="igsp-sf-btn" href="<?php echo esc_url( WP_IGSP_PLUGIN_BUNDLE_LINK ); ?>" target="_blank">Grab The Deal</a>
	</div>

	<!-- Start - Welcome Box -->
	<div class="igsp-sf-welcome-wrap" style="padding: 30px;border-radius: 10px;border: 1px solid #e5ecf6;">
		<div class="igsp-sf-welcome-inr igsp-sf-center">
			<h1 class="igsp-sf-heading" style="font-size: 24px; margin: 20px 0;">Build and display multiple responsive <span class="igsp-sf-blue">image sliders & carousels </span> to create animated image for increase website engagement.</h1>
			<h5 class="igsp-sf-content" style="font-size: 20px; margin: 20px 0;">Experience <span class="igsp-sf-blue">5 Layouts</span>, <span class="igsp-sf-blue">15+ stunning designs</span>.</h5>
			<h5 class="igsp-sf-content" style="font-size: 18px; margin: 20px 0;"><span class="igsp-sf-blue">7,000+ </span>websites are using <span class="igsp-sf-blue">Meta Slider</span>.</h5>
		</div>
		<div style=" text-transform: uppercase; text-align:center;">
			<a href="<?php echo esc_url( $igsp_add_link ); ?>" class="igsp-sf-btn">Launch Meta Slider With Free Features</a>
		</div>
	</div>
	<!-- End - Welcome Box -->
	
</div>