<?php
function blockchain_plugin_sanitize_metabox_tab_hero( $post_id ) {
	// Ignore phpcs issues. nonce validation happens inside blockchain_plugin_can_save_meta(), from the caller of this function.
	// @codingStandardsIgnoreStart

	$support = get_theme_support( 'blockchain-hero' );
	$support = $support[0];
	if ( ! $support['required'] ) {
		update_post_meta( $post_id, 'hero_show', isset( $_POST['hero_show'] ) ? 1 : 0 );
	}

	update_post_meta( $post_id, 'hero_text_align', blockchain_plugin_sanitize_text_align( $_POST['hero_text_align'] ) );
	update_post_meta( $post_id, 'page_title_hide', isset( $_POST['page_title_hide'] ) ? 1 : 0 );

	update_post_meta( $post_id, 'hero_image_id', blockchain_plugin_sanitize_intval_or_empty( $_POST['hero_image_id'] ) );
	update_post_meta( $post_id, 'hero_bg_color', sanitize_hex_color( $_POST['hero_bg_color'] ) );
	update_post_meta( $post_id, 'hero_text_color', sanitize_hex_color( $_POST['hero_text_color'] ) );
	update_post_meta( $post_id, 'hero_overlay_color', blockchain_plugin_sanitize_rgba_color( $_POST['hero_overlay_color'] ) );
	update_post_meta( $post_id, 'hero_image_repeat', blockchain_plugin_sanitize_image_repeat( $_POST['hero_image_repeat'] ) );
	update_post_meta( $post_id, 'hero_image_position_x', blockchain_plugin_sanitize_image_position_x( $_POST['hero_image_position_x'] ) );
	update_post_meta( $post_id, 'hero_image_position_y', blockchain_plugin_sanitize_image_position_y( $_POST['hero_image_position_y'] ) );
	update_post_meta( $post_id, 'hero_image_attachment', blockchain_plugin_sanitize_image_attachment( $_POST['hero_image_attachment'] ) );
	update_post_meta( $post_id, 'hero_image_cover', isset( $_POST['hero_image_cover'] ) ? 1 : 0 );
	update_post_meta( $post_id, 'hero_video_url', esc_url_raw( $_POST['hero_video_url'] ) );
	// @codingStandardsIgnoreEnd
}

function blockchain_plugin_print_metabox_tab_hero( $object, $box ) {
	$support = get_theme_support( 'blockchain-hero' );
	$support = $support[0];

	$page_title_hide_default    = $support['required'] || $support['show-default'] ? 1 : 0;
	$page_title_hide_guide_text = __( 'Since the hero section shows the title by default, you may want to disable the page title (shown before the content).', 'blockchain-plugin' );

	if ( 'post' === get_post_type( $object->ID ) ) {
		$page_title_hide_default = 0;
		/* translators: %s is a user-provided title. */
		$page_title_hide_guide_text = sprintf( __( 'When checked, the title will appear on the hero section, replacing the blog title you have set from <em>Customize &rarr; Titles &rarr; General &rarr; Blog title</em>, currently set to: <em>%s</em>.', 'blockchain-plugin' ), get_theme_mod( 'title_blog', __( 'From the blog', 'blockchain-plugin' ) ) );
	}

	$page_title_hide_default    = apply_filters( 'blockchain_hero_page_title_hide_default', $page_title_hide_default, get_post_type( $object->ID ), $object->ID );
	$page_title_hide_guide_text = apply_filters( 'blockchain_hero_page_title_hide_guide_text', $page_title_hide_guide_text, get_post_type( $object->ID ), $object->ID, $page_title_hide_default );

	blockchain_plugin_metabox_open_tab( esc_html__( 'Hero section', 'blockchain-plugin' ) );

		if ( ! $support['required'] ) {
			blockchain_plugin_metabox_checkbox( 'hero_show', 1, esc_html__( 'Show hero section.', 'blockchain-plugin' ), array( 'default' => intval( $support['show-default'] ) ) );
		}

		blockchain_plugin_metabox_dropdown( 'hero_text_align', blockchain_plugin_get_text_align_choices(), esc_html__( 'Title / subtitle alignment:', 'blockchain-plugin' ), array( 'default' => $support['text-align'] ) );

		blockchain_plugin_metabox_guide( wp_kses( $page_title_hide_guide_text, blockchain_plugin_get_allowed_tags( 'guide' ) ) );
		blockchain_plugin_metabox_checkbox( 'page_title_hide', 1, esc_html__( 'Hide page title.', 'blockchain-plugin' ), array( 'default' => $page_title_hide_default ) );

		?><p class="ci-field-group ci-field-input"><?php
			blockchain_plugin_metabox_input( 'hero_bg_color', esc_html__( 'Background Color:', 'blockchain-plugin' ), array( 'input_class' => 'blockchain-color-picker widefat', 'before' => '', 'after' => '' ) );
		?></p><?php
		?><p class="ci-field-group ci-field-input"><?php
			blockchain_plugin_metabox_input( 'hero_text_color', esc_html__( 'Text Color:', 'blockchain-plugin' ), array( 'input_class' => 'blockchain-color-picker widefat', 'before' => '', 'after' => '' ) );
		?></p><?php
		?><p class="ci-field-group ci-field-input"><?php
			blockchain_plugin_metabox_input( 'hero_overlay_color', esc_html__( 'Overlay Color:', 'blockchain-plugin' ), array( 'input_class' => 'blockchain-alpha-color-picker widefat', 'before' => '', 'after' => '' ) );
		?></p><?php

		blockchain_plugin_metabox_input( 'hero_video_url', esc_html__( 'Video URL (YouTube or Vimeo)', 'blockchain-plugin' ), array( 'esc_func' => 'esc_url' ) );

		blockchain_plugin_metabox_guide( array(
			wp_kses( __( 'The following image options are only applicable when a Hero image is selected.', 'blockchain-plugin' ), blockchain_plugin_get_allowed_tags( 'guide' ) ),
		) );

		$hero_image_id = get_post_meta( $object->ID, 'hero_image_id', true );
		?>
		<div class="ci-field-group ci-field-input">
			<label for="header_image_id"><?php esc_html_e( 'Hero image:', 'blockchain-plugin' ); ?></label>
			<div class="ci-upload-preview">
				<div class="upload-preview">
					<?php if ( ! empty( $hero_image_id ) ) : ?>
						<?php
							$image_url = wp_get_attachment_image_url( $hero_image_id, 'blockchain_plugin_featgal_small_thumb' );
							echo sprintf( '<img src="%s" /><a href="#" class="close media-modal-icon" title="%s"></a>',
								esc_url( $image_url ),
								esc_attr__( 'Remove image', 'blockchain-plugin' )
							);
						?>
					<?php endif; ?>
				</div>
				<input name="hero_image_id" type="hidden" class="ci-uploaded-id" value="<?php echo esc_attr( $hero_image_id ); ?>" />
				<input id="hero_image_id" type="button" class="button ci-media-button" value="<?php esc_attr_e( 'Select Image', 'blockchain-plugin' ); ?>" />
			</div>
		</div>
		<?php

		blockchain_plugin_metabox_dropdown( 'hero_image_repeat', blockchain_plugin_get_image_repeat_choices(), esc_html__( 'Image repeat:', 'blockchain-plugin' ), array( 'default' => 'no-repeat' ) );
		blockchain_plugin_metabox_dropdown( 'hero_image_position_x', blockchain_plugin_get_image_position_x_choices(), esc_html__( 'Image horizontal position:', 'blockchain-plugin' ), array( 'default' => 'center' ) );
		blockchain_plugin_metabox_dropdown( 'hero_image_position_y', blockchain_plugin_get_image_position_y_choices(), esc_html__( 'Image vertical position:', 'blockchain-plugin' ), array( 'default' => 'center' ) );
		blockchain_plugin_metabox_dropdown( 'hero_image_attachment', blockchain_plugin_get_image_attachment_choices(), esc_html__( 'Image attachment:', 'blockchain-plugin' ), array( 'default' => 'scroll' ) );
		blockchain_plugin_metabox_checkbox( 'hero_image_cover', 1, esc_html__( 'Scale the image to cover its container.', 'blockchain-plugin' ), array( 'default' => 1 ) );
		?><?php

	blockchain_plugin_metabox_close_tab();
}
