<?php
// Child theme footer override: render footer with inline backgrounds for logo/nav star.

$sidebars           = array('footer-1', 'footer-2', 'footer-3');
$classes            = blockchain_footer_widget_area_classes(get_theme_mod('footer_layout', blockchain_footer_layout_default()));
$has_active_sidebar = false;
foreach ($sidebars as $sidebar) {
	if (is_active_sidebar($sidebar) && $classes[$sidebar]['active']) {
		$has_active_sidebar = true;
		break;
	}
}

$accurate_footer_logo = get_stylesheet_directory_uri() . '/assets/images/AS_Logo_Footer.svg';
$nav_star             = get_stylesheet_directory_uri() . '/assets/images/nav-star.svg';
$instagram_icon       =  get_stylesheet_directory_uri() . '/assets/images/insta.svg';
$linkedin_icon        =  get_stylesheet_directory_uri() . '/assets/images/linkedin.svg';
$contact_top    = home_url('/contact');

// Path marker for debugging in View Source

?>

<footer class="<?php blockchain_the_footer_classes(); ?> accurate-footer-override" style="position:relative; overflow:hidden;">
	<div class="footer-widgets">
		<div class="footer-tagline-top">
			Call, email, or use our <a href="<?php echo esc_url($contact_top); ?>">Contact Form</a> for a free quote on your next project
		</div>
		<img src="<?php echo esc_url($nav_star); ?>" alt="Compass star" loading="lazy" class="footer-nav-star" />
		<?php if ($has_active_sidebar) : ?>
			<div class="row footer-column-layout">
				<?php foreach ($sidebars as $sidebar) : ?>
					<?php if ($classes[$sidebar]['active']) : ?>
						<div class="<?php echo esc_attr($classes[$sidebar]['class']); ?>">
							<?php dynamic_sidebar($sidebar); ?>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<div class="footer-brand-inline">
			<img class="footer-logo-stack" src="<?php echo esc_url($accurate_footer_logo); ?>" alt="Accurate Surveying & Mapping" loading="lazy" />
			<span class="bottom-info">
				<span class="footer-tagline-center">Superior Land Surveying</span>
				<p class="bottom-info-text">
					1520 W. Washington St., Boise, ID 83702 | (208) 488-4227 | <a href="/contact">EMAIL US!</a>
				</p>
			</span>
			<div class="footer-social-icons-section">
				<div class="footer-social-icons">
					<p>Follow Us!</p>
					<span class="footer-social-icons-links">
						<a href="https://www.instagram.com/accuratesurveyors/">
							<img src="<?php echo $instagram_icon; ?>" loading="lazy" class="footer-instagram-icon" />
						</a>
						<a href="https://www.linkedin.com/company/accurate-surveying-&-mapping/">
							<img src="<?php echo $linkedin_icon; ?>" loading="lazy" class="footer-linkedin-icon" />
						</a>
					</span>
				</div>
				<span class="bottom-menu">
					<a href="/">HOME </a>
					<a href="/services">SERVICES</a>
					<a href="/team">TEAM</a>
					<a href="/projects">PROJECTS </a>
					<a href="/contact">CONTACT</a>
				</span>
			</div>

		</div>
	</div>

	<!-- <?php blockchain_footer_bottom_bar(); ?> -->

</footer>

<?php wp_footer(); ?>
</body>

</html>