<?php
// @codingStandardsIgnoreFile

/**
 *
 * Plugin Name: USA Today Sports Images
 * Description: Allows searching and importing images from USA Today Sports Images
 * Version: 1.2
 * Author: Thomas J. Rivera
 * Author URI:  http://www.usatodaysportsimages.com
 * License: GPL2
 * Version: 1

 * @package   Vendor/Project
 */

/**
 * Create our new media services
 *
 *  @param array $services Assoc array of Media Explorer services to load.
 *  @return array $services Assoc array of Media Explorer services to load.
 */
function usatsi_media_services_new( array $services ) {
	// This key name is important. You must use the same name for the tabs() and labels() methods in Usatsi_MEXP_New_Service.
	$services['usatsi_mexmp_service'] = new Usatsi_MEXP_New_Service();
	return $services;
}
add_filter( 'mexp_services', 'usatsi_media_services_new' );
add_action( 'wp_ajax_usatsi_download_image', 'usatsi_download_image' );

/**
 * Create our tabs handler
 *
 * @param array $tabs Array of Media Explorer tabs.
 * @return array $Tabs Array of Media Explorer tabs.
 */
function usatsi_upload_hidden_tabs_handler( $tabs ) {
	$tabs['usatsitab_hidden'] = __( 'Insert USAT Sports Images', 'usatsi_images' );
	return $tabs;
}
add_filter( 'media_upload_tabs', 'usatsi_upload_hidden_tabs_handler' );


/**
 * Create our hidden upload tabs handler.
 *
 * @return void().
 */
function usatsi_upload_hidden_tabs_content_handler() {
	wp_iframe( 'usatsi_media_upload_images_tab_hidden' );
}
add_action( 'media_upload_usatsitab_hidden', 'usatsi_upload_hidden_tabs_content_handler' );

/**
 * Downloads user selected image.
 *
 * @return float image attachement.
 */
function usatsi_download_image() {

	// Need to require these files!
	if ( ! function_exists( 'media_handle_upload' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
	}

	if ( wp_verify_nonce( ( isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '' ), 'usatsi_download_image' ) ) {
		$post_id = (isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : null);
		$desc = (isset( $_POST['image_title'] ) ? sanitize_text_field( wp_unslash( $_POST['image_title'] ) ) : '');
		$post_title = (isset( $_POST['image_title'] ) ? sanitize_text_field( wp_unslash( $_POST['image_title'] ) ) : '');
		$image_id = (isset( $_POST['image_id'] ) ? sanitize_text_field( wp_unslash( $_POST['image_id'] ) ) : null);

		$allowed_html = array(
			'a' => array(
				'href' => array(),
				'title' => array(),
			),
			'b' => array(),
			'strong' => array(),
		);

		$post_content = (isset( $_POST['image_caption'] ) ? wp_kses( wp_unslash( $_POST['image_caption'] ), $allowed_html ) : '');

		$image_url = usatsi_build_auth_url( $image_id );

		$tmp = download_url( $image_url );

		$file_array = array();
		if ( is_wp_error( $tmp ) ) {
			return $tmp;
		}

		// Set variables for storage!
		$file_array['name'] = $image_id . '.jpg';
		$file_array['tmp_name'] = $tmp;

		// If error storing temporarily, unlink!
		if ( is_wp_error( $tmp ) ) {
			@unlink( $file_array['tmp_name'] );
			$file_array['tmp_name'] = '';
		}

		$image_data = array(
			'post_title' => $post_title,
			'post_content' => $post_content, // caption!
		'post_excerpt' => $post_content, // caption!
		);

		// Let's be safe make sure this is being done via admin ajax page!
		if ( is_admin() && wp_doing_ajax() ) {
			$id = media_handle_sideload( $file_array, $post_id, $desc, $image_data );
		} else {
			wp_die();
		}

		// If error storing permanently, unlink!
		if ( is_wp_error( $id ) ) {
			@unlink( $file_array['tmp_name'] );
			return $id;
		}

		$attach_data = wp_generate_attachment_metadata( $id, get_attached_file( $id ) );

		wp_update_attachment_metadata( $id, $attach_data );

		// Forces ppost not saved or autosaved to show edit image / insert!
		$post_data = array(
			'ID' => $post_id,
			'post_title' => $post_title,
			'post_content' => $post_content, // caption!
		'post_excerpt' => $post_content, // caption!
		);
		wp_insert_post( $post_data );

		echo esc_attr( $id );
		wp_die();
	} else {
		die();
		exit();
	};

}

/**
 * Hidden tab handler triggered by JavaScript event.
 *
 * @return void().
 */
function usatsi_media_upload_images_tab_hidden() {

	if ( isset( $_POST['_wpnonce'], $_REQUEST['post_id'] ) && wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'usatsi_download_image' ) ) {
		$post_id = sanitize_text_field( wp_unslash( $_REQUEST['post_id'] ) );
	} else {
		$post_id = '';
	}

	?>
	<script>
		jQuery('.media-iframe iframe', window.parent.document).find('#tab-usatsitab_hidden');
		jQuery('.media-iframe iframe',  window.parent.document).attr('src', 'media-upload.php?type=image&tab=library&post_id=' + <?php echo ( esc_attr( absint( $post_id ) ) ); ?> + '&attachment_id=' + parent.usatsi_image_ajax.attachmentId);
	</script>
	<?php
}


/**
 * Returns full authenticated asset URL for download.
 *
 * @param int $imageid USAT SI Fullsize Image ID.
 * @return string Qualified image URL.
 */
function usatsi_build_auth_url( $imageid ) {

	$options  = get_option( 'usatsi_options' );

	$usatsi_api = ( isset( $options['usatsi_apikey'] ) ? esc_attr( $options['usatsi_apikey'] ) : '' );
	$usatsi_api_secret = ( isset( $options['usatsi_apisecret'] ) ? esc_attr( $options['usatsi_apisecret'] ) : '' );

	// Oauth Params!
	$base_url = 'http://www.usatsimg.com/api/download/';
	$consumer_secret = $usatsi_api_secret;
	$consumer_key = $usatsi_api;
	$oauth_timestamp = time();
	$nonce = md5( mt_rand() );
	$oauth_signature_method = 'HMAC-SHA1';
	$oauth_version = '1.0';

	$sig_base = 'GET&' . rawurlencode( $base_url ) . '&'
	. rawurlencode(
		'imageID=' . rawurlencode( $imageid )
		. '&oauth_consumer_key=' . rawurlencode( $consumer_key )
		. '&oauth_nonce=' . rawurlencode( $nonce )
		. '&oauth_signature_method=' . rawurlencode( $oauth_signature_method )
		. '&oauth_timestamp=' . rawurlencode( $oauth_timestamp )
		. '&oauth_version=' . rawurlencode( $oauth_version )
	);
	$sig_key = $consumer_secret . '&';
	$oauth_sig = base64_encode( hash_hmac( 'sha1', $sig_base, $sig_key, true ) );

	$request_url = $base_url . '?oauth_consumer_key=' . rawurlencode( $consumer_key )
	. '&oauth_nonce=' . rawurlencode( $nonce )
	. '&oauth_signature_method=' . rawurlencode( $oauth_signature_method )
	. '&oauth_timestamp=' . rawurlencode( $oauth_timestamp )
	. '&oauth_version=' . rawurlencode( $oauth_version )
	. '&oauth_signature=' . rawurlencode( $oauth_sig )
	. '&imageID=' . rawurlencode( $imageid );

	return $request_url;

}

if ( class_exists( 'MEXP_Service' ) ) {
	include_once 'class-usatsi-mexp-new-template.php';
	include_once 'class-usatsi-mexp-new-service.php';
	include_once 'class-usatsi-options-page.php';
}
