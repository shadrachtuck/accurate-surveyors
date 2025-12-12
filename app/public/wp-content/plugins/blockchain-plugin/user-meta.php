<?php
	add_action( 'show_user_profile', 'blockchain_plugin_show_extra_profile_fields' );
	add_action( 'edit_user_profile', 'blockchain_plugin_show_extra_profile_fields' );

	function blockchain_plugin_show_extra_profile_fields( $user ) {
		wp_nonce_field( 'blockchain_plugin_show_extra_profile_fields_nonce', '_blockchain_plugin_show_extra_profile_fields_nonce' );

		?>
		<h3><?php esc_html_e( 'Author Information', 'blockchain-plugin' ); ?></h3>

		<table class="form-table">
			<tr>
				<th><label for="user_subtitle"><?php esc_html_e( 'Subtitle', 'blockchain-plugin' ); ?></label></th>

				<td>
					<input type="text" name="user_subtitle" id="user_subtitle" value="<?php echo esc_attr( get_user_meta( $user->ID, 'user_subtitle', true ) ); ?>" class="regular-text"/><br/>
					<span class="description"><?php esc_html_e( "The author subtitle is displayed on the author box, along with the author's name, below each article.", 'blockchain-plugin' ); ?></span>
				</td>
			</tr>
		</table>
		<?php
	}

	add_action( 'personal_options_update', 'blockchain_plugin_save_extra_profile_fields' );
	add_action( 'edit_user_profile_update', 'blockchain_plugin_save_extra_profile_fields' );

	function blockchain_plugin_save_extra_profile_fields( $user_id ) {
		if ( isset( $_POST['_blockchain_plugin_show_extra_profile_fields_nonce'] ) && wp_verify_nonce( $_POST['_blockchain_plugin_show_extra_profile_fields_nonce'], 'blockchain_plugin_show_extra_profile_fields_nonce' ) ) { // Input vars okay.

			if ( ! current_user_can( 'edit_user', $user_id ) ) {
				return false;
			}

			update_user_meta( $user_id, 'user_subtitle', sanitize_text_field( $_POST['user_subtitle'] ) ); // Input var okay.
		}
	}
