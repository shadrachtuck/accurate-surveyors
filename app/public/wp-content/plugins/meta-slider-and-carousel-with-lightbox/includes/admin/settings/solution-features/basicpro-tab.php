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
<div id="igsp_basic_tabs" class="igsp-vtab-cnt igsp_basic_tabs igsp-clearfix">
	<?php /*
	<h3 class="igsp-basic-heading">Compare <span class="igsp-blue">"Meta Slider and Carousel with Lightbox"</span> Basic VS Pro</h3>
	
	<div class="igsp-deal-offer-wrap">
		<div class="igsp-deal-offer"> 
			<div class="igsp-inn-deal-offer">
				<h3 class="igsp-inn-deal-hedding"><span>Buy Meta Slider Pro</span> today and unlock all the powerful features.</h3>
				<h4 class="igsp-inn-deal-sub-hedding"><span style="color:red;">Extra Bonus: </span>Users will get <span>extra best discount</span> on the regular price using this coupon code.</h4>
			</div>
			<div class="igsp-inn-deal-offer-btn">
				<div class="igsp-inn-deal-code"><span>EPSEXTRA</span></div>
				<a href="<?php echo esc_url(WP_IGSP_PLUGIN_BUNDLE_LINK); ?>"  target="_blank" class="igsp-sf-btn igsp-sf-btn-orange"><span class="dashicons dashicons-cart"></span> Get Essential Bundle Now</a>
				<em class="risk-free-guarantee"><span class="heading">Risk-Free Guarantee </span> - We offer a <span>30-day money back guarantee on all purchases</span>. If you are not happy with your purchases, we will refund your purchase. No questions asked!</em>
			</div>
		</div>
	</div>
	

	<div class="igsp-deal-offer-wrap">
		<div class="igsp-deal-offer"> 
			<div class="igsp-inn-deal-offer">
				<h3 class="igsp-inn-deal-hedding"><span>Try Meta Slider Pro</span> in Essential Bundle Free For 5 Days.</h3>
			</div>
			<div class="igsp-deal-free-offer">
				<a href="<?php echo esc_url( WP_IGSP_PLUGIN_BUNDLE_LINK ); ?>" target="_blank" class="igsp-sf-free-btn"><span class="dashicons dashicons-cart"></span>Try Pro For 5 Days Free</a>
			</div>
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

	<h3 class="igsp-basic-heading">Compare <span class="igsp-blue">"Meta Slider and Carousel with Lightbox"</span> Basic VS Pro</h3>

	<table class="wpos-plugin-pricing-table">
		<colgroup></colgroup>
		<colgroup></colgroup>
		<colgroup></colgroup>
		<thead>
			<tr>
				<th></th>
				<th>
					<h2>Free</h2>
				</th>
				<th>
					<h2 class="wpos-epb">Premium</h2>
				</th>
			</tr>
		</thead>

		<tbody>
			<tr>
				<th>Designs <span>Designs that make your website better</span></th>
				<td>1</td>
				<td>15+</td>
			</tr>

			<tr>
				<th>Shortcodes <span>Shortcode provide output to the front-end side</span></th>
				<td>2 (Slider, Carousel)</td>
				<td>3 (Slider, Carousel, Variable width)</td>
			</tr>

			<tr>
				<th>Shortcode Parameters <span>Add extra power to the shortcode</span></th>
				<td>8</td>
				<td>16+</td>
			</tr>

			<tr>
				<th>WP Templating Features <span class="subtext">You can modify plugin html/designs in your current theme.</span></th>
				<td><i class="dashicons dashicons-no-alt"> </i></td>
				<td><i class="dashicons dashicons-yes"> </i></td>
			</tr>

			<tr>
				<th>Title Hide/Show <span>Option to slider/carousel title hide or show</span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>

			<tr>
				<th>Caption Hide/Show<span>Option to slider/carousel caption show or hide </span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>

			<tr>
				<th>Description Hide/Show <span>option to display slider/carousel description hide or show.</span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>

			<tr>
				<th>Arrows Hide/Show options  <span>option to Arrows hide or show</span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>

			<tr>
				<th>Pagination Hide/Show options  <span>Option to display pagination or not</span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>

			<tr>
				<th>Loop Control for slider/carousel  <span>Infinite scroll control </span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>

			<tr>
				<th>Navigation columns setting <span>Number of image columns show in navigation</span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>

			<tr>
				<th>Lightbox/Link Support for slider/carousel  <span>Display lightbox OR link for slider/carousel </span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>

			<tr>
				<th>Gutenberg Block Supports <span>Use this plugin with Gutenberg easily</span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>

			<tr>
				<th>Elementor Page Builder Support <em class="wpos-new-feature">New</em> <span>Use this plugin with Elementor easily</span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>

			<tr>
				<th>Beaver Builder Support <em class="wpos-new-feature">New</em> <span>Use this plugin with Beaver Builder easily</span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>

			<tr>
				<th>SiteOrigin Page Builder Support <em class="wpos-new-feature">New</em> <span>Use this plugin with SiteOrigin easily</span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>

			<tr>
				<th>Divi Page Builder Native Support <em class="wpos-new-feature">New</em> <span>Use this plugin with Divi Builder easily</span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>

			<tr>
				<th>Fusion Page Builder (Avada) Native Support <em class="wpos-new-feature">New</em> <span>Use this plugin with Fusion Page Builder (Avada) easily</span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>

			<tr>
				<th>WPBakery Page Builder Support <span>Use this plugin with WPBakery Page Builder easily</span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>

			<tr>
				<th>External Link Support  <span>Enable External link and link target for an image.  </span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>

			<tr>
				<th>Image Size Support  <span>Add image size for slider/carousel.  </span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>

			<tr>
				<th>Custom CSS for plugin <span>Plugin related CSS add in settings menu</span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>

			<tr>
				<th>Multiple Sliders/Carousels Support <span>Display multiple sliders/Carousels on the same post/page.</span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>

			<tr>
				<th>Slider RTL Support <span>Slider supports for RTL website</span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>

			<tr>
				<th>Automatic Update <span>Get automatic  plugin updates </span></th>
				<td>Lifetime</td>
				<td>Lifetime</td>
			</tr>

			<tr>
				<th>Support <span>Get support for plugin</span></th>
				<td>Limited</td>
				<td>1 Year</td>
			</tr>
		</tbody>
	</table>

<?php /*
	<div class="igsp-deal-offer-wrap">
		<div class="igsp-deal-offer"> 
			<div class="igsp-inn-deal-offer">
				<h3 class="igsp-inn-deal-hedding"><span>Buy Meta Slider Pro</span> today and unlock all the powerful features.</h3>
				<h4 class="igsp-inn-deal-sub-hedding"><span style="color:red;">Extra Bonus: </span>Users will get <span>extra best discount</span> on the regular price using this coupon code.</h4>
			</div>
			<div class="igsp-inn-deal-offer-btn">
				<div class="igsp-inn-deal-code"><span>EPSEXTRA</span></div>
				<a href="<?php echo esc_url(WP_IGSP_PLUGIN_BUNDLE_LINK); ?>"  target="_blank" class="igsp-sf-btn igsp-sf-btn-orange"><span class="dashicons dashicons-cart"></span> Get Essential Bundle Now</a>
				<em class="risk-free-guarantee"><span class="heading">Risk-Free Guarantee </span> - We offer a <span>30-day money back guarantee on all purchases</span>. If you are not happy with your purchases, we will refund your purchase. No questions asked!</em>
			</div>
		</div>
	</div>

	<div class="igsp-deal-offer-wrap">
		<div class="igsp-deal-offer"> 
			<div class="igsp-inn-deal-offer">
				<h3 class="igsp-inn-deal-hedding"><span>Try Meta Slider Pro</span> in Essential Bundle Free For 5 Days.</h3>
			</div>
			<div class="igsp-deal-free-offer">
				<a href="<?php echo esc_url( WP_IGSP_PLUGIN_BUNDLE_LINK ); ?>" target="_blank" class="igsp-sf-free-btn"><span class="dashicons dashicons-cart"></span>Try Pro For 5 Days Free</a>
			</div>
		</div>
	</div>
	*/ ?>

	<!-- <div class="igsp-black-friday-banner-wrp">
		<a href="<?php // echo esc_url( WP_IGSP_PLUGIN_BUNDLE_LINK ); ?>" target="_blank"><img style="width: 100%;" src="<?php // echo esc_url( WP_IGSP_URL ); ?>assets/images/black-friday-banner.png" alt="black-friday-banner" /></a>
	</div> -->

</div>