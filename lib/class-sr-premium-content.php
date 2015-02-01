<?php

/**
 * SR Premium Content
 */
class SR_Premium_Content {
	static $premium_meta_key = '_sr_premium_content';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save' ) );
		add_action( 'wp', array( $this, 'premium_check' ) );
	}

	/**
	 * Add the meta box to all post types
	 */
	public function add_meta_box( $postType ) {
		$types = get_post_types( '', 'names' ); 

		if( in_array( $postType, $types ) ){
			add_meta_box(
				'sr_premium_content_checkbox',
				__( 'Premium Content', 'sr_premium_content_textdomain' ),
				array( $this, 'render_premium_checkbox' ),
				$postType
			);
		}
	}

	/**
	 * The premium checkbox HTML
	 */
	public function render_premium_checkbox( $post ) {
		wp_nonce_field( 'sr_premium_content_checkbox', 'sr_premium_content_checkbox_nonce' );

		$value = get_post_meta( $post->ID, self::$premium_meta_key, true );
		?>
		<label for="sr_premium_content_checkbox">
			<?php _e( 'Premium Content ', 'sr_premium_content_textdomain' ); ?>
		</label>
		<input type="checkbox" id="sr_premium_content_checkbox" name="sr_premium_content" <?php checked( $value, 'on' ); ?> />
		<?php
	}

	/**
	 * Save the premium content checkbox
	 */
	public function save( $post_id ) {
		// Check if our nonce is set.
		if ( ! isset( $_POST['sr_premium_content_checkbox_nonce'] ) ) {
			return $post_id;
		}

		$nonce = $_POST['sr_premium_content_checkbox_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'sr_premium_content_checkbox' ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted,
		// so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}
		
		// Sanitize the user input.
		$mydata = sanitize_text_field( $_POST['sr_premium_content'] );

		// Update the meta field.
		update_post_meta( $post_id, self::$premium_meta_key, $mydata );
	}

	/**
	 * Check if the post is premium or not
	 */
	public function premium_check() {
		$post_id = get_the_id();

		$is_premium = get_post_meta( $post_id, self::$premium_meta_key );

		if( 'on' == $is_premium[0] && !is_archive() && !is_admin() && !$this->check_form_submit() ) {
			$this->premium_content_wall( $post_id );
		}
	}

	/**
	 * Check if any of the signs exist that show a user submitted their email
	 */
	public function check_form_submit() {
		if( isset( $_COOKIE['hsrecentfields'] ) || isset( $_GET['submissionGuid'] ) || isset( $_COOKIE['hs_view_premium_content'] ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Load the JS to prevent user from accessing content without giving email
	 */
	public function premium_content_wall( $post_id ) {
		$post = get_post( $post_id );
		$dirname_file = dirname( __FILE__ );
		$custom_overlay_file = get_template_directory_uri() . '/sr-premium-content/overlay.html';

		// Register the script
		wp_register_script( 'sr-premium-content-script', plugins_url( 'assets/script.js', $dirname_file ), array( 'jquery' ) );
		wp_register_script( 'hubspot-forms', 'http://js.hsforms.net/forms/current.js', array( 'jquery' ) );

		// see if theme contains custom overlay HTML
		if( file_exists( $custom_overlay_file ) ) {
			$overlay_html = file_get_contents( $custom_overlay_file );
		} else {
			$overlay_html = file_get_contents( plugins_url( 'templates/overlay.html', $dirname_file ) );
		}

		// Localize the script with new data
		$script_obj = array(
			'overlay_html' => $overlay_html,
			'post' => $post
		);
		wp_localize_script( 'sr-premium-content-script', 'wp', $script_obj );

		// Enqueued script with localized data.
		wp_enqueue_script( 'sr-premium-content-script' );
		wp_enqueue_script( 'hubspot-forms' );
		wp_enqueue_style( 'sr-premium-content-script', plugins_url( 'assets/style.css', $dirname_file ) );
	}

}