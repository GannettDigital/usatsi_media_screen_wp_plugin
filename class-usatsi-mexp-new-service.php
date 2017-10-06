<?php
/**
 * Class-usatsi-mexp-new-service.php
 *
 * @author    Thomas J. Rivera
 * @copyright USA Today Sports Images
 * @license   GPL-2
 * @package   Vendor/Project
 * @see       https://www.usatsimg.com
 */

/**
 * USAT SI New Media Explorer Service.
 */
class Usatsi_MEXP_New_Service extends MEXP_Service {
	/**
	 * Constructor.
	 *
	 * Creates the Backbone view template.
	 */
	public function __construct() {
		$this->set_template( new Usatsi_MEXP_New_Template() );
	}

	/**
	 * Fired when the service is loaded.
	 *
	 * Allows the service to enqueue JS/CSS only when it's required. Akin to WordPress' load action.
	 */
	public function load() {
		add_action( 'mexp_enqueue', array( $this, 'enqueue_statics' ) );
		add_filter( 'mexp_tabs',   array( $this, 'tabs' ),   10, 1 );
		add_filter( 'mexp_labels', array( $this, 'labels' ), 10, 1 );
		add_filter( 'admin_body_class', array( $this, 'usatsi_body_classes' ), 10, 1 );
	}

	/**
	 * Adds custom css tag to admin body.
	 *
	 * @param array $classes Name of class.
	 * @return Array Body tag class names
	 */
	public function usatsi_body_classes( $classes ) {
		$classes = 'usatsi-media-exp-service';
		return $classes;
	}

	/**
	 * Handles loading of styles and scripts
	 *
	 * Void().
	 */
	public function enqueue_statics() {

		wp_enqueue_style(
			'mexp-service-usatsi-css',
			plugin_dir_url( __FILE__ ) . 'css/usatsi-media-screen.css'
		);

		wp_enqueue_style(
			'mexp-service-usatsi-branding-css',
			plugin_dir_url( __FILE__ ) . 'css/usatsi-media-screen-branding.css'
		);

		wp_enqueue_script(
			'mexp-service-usatsi',
			plugins_url( 'js/usatsi-media-service.js', __FILE__ ),
			array( 'jquery', 'mexp' ),
			false,
			true
		);

		wp_localize_script(
			'mexp-service-usatsi', 'usatsi_image_ajax', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'ajax_url_proxy' => admin_url( 'admin-ajax.php' ),
			)
		);

	}


	/**
	 * Handles the AJAX request and returns an appropriate response.
	 *
	 * @param array $request The request parameters.
	 * @return MEXP_Response|bool|WP_Error A MEXP_Response object should be returned on success,
	 * boolean false should be returned if there are no results to show, and
	 * a WP_Error should be returned if there is an error.
	 */
	public function request( array $request ) {

		if ( ! wp_verify_nonce( ( isset( $_POST['_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ) : ''  ), 'mexp_request' ) ) {
			die();
			exit();
		} else {

			$options  = get_option( 'usatsi_options' );

			$usatsi_api = ( isset( $options['usatsi_apikey'] ) ? esc_attr( $options['usatsi_apikey'] ) : '' );
			$usatsi_api_secret = ( isset( $options['usatsi_apisecret'] ) ? esc_attr( $options['usatsi_apisecret'] ) : '' );

			$response = new MEXP_Response();

			// Oauth Params.
			$base_url = 'http://www.usatodaysportsimages.com/api/searchAPI/';
			$consumer_secret = $usatsi_api_secret;
			$consumer_key = $usatsi_api;
			$oauth_timestamp = time();
			$nonce = md5( mt_rand() );
			$oauth_signature_method = 'HMAC-SHA1';
			$oauth_version = '1.0';
			$keywords = $request['params']['q'];
			$terms = $request['params']['q'];
			$page = ( ! empty( $_POST['page'] ) ? sanitize_text_field( wp_unslash( $_POST['page'] ) ) : 1 );

			$sig_base = 'GET&' . rawurlencode( $base_url ) . '&'
			. rawurlencode(
				'keywords=' . rawurlencode( $keywords )
				. '&limit=50&oauth_consumer_key=' . rawurlencode( $consumer_key )
				. '&oauth_nonce=' . rawurlencode( $nonce )
				. '&oauth_signature_method=' . rawurlencode( $oauth_signature_method )
				. '&oauth_timestamp=' . $oauth_timestamp
				. '&oauth_version=' . $oauth_version
				. '&offset=' . $page . '&terms=' . rawurlencode( $terms )
			);

			$sig_key = $consumer_secret . '&';
			$oauth_sig = base64_encode( hash_hmac( 'sha1', $sig_base, $sig_key, true ) );

			$request_url = $base_url . '?oauth_consumer_key=' . rawurlencode( $consumer_key )
			. '&oauth_nonce=' . rawurlencode( $nonce )
			. '&oauth_signature_method=' . rawurlencode( $oauth_signature_method )
			. '&oauth_timestamp=' . rawurlencode( $oauth_timestamp )
			. '&oauth_version=' . rawurlencode( $oauth_version )
			. '&oauth_signature=' . rawurlencode( $oauth_sig )
			. '&terms=' . rawurlencode( $terms )
			. '&keywords=' . rawurlencode( $keywords )
			. '&limit=50&offset=' . $page;

			$api_response = wp_remote_get( $request_url );
			$api_response = json_decode( $api_response['body'], true );

			foreach ( $api_response['results']['item'] as $row => $response_data ) {
				foreach ( $response_data as $inner_row => $value ) {

					$item = new MEXP_Response_Item();

					$item->set_content( $value['headline'] );
					$item->set_date( strtotime( $value['dateCreate'] ) );
					$item->set_date_format( 'M j, Y' );
					$item->set_id( (int) 1 + (int) $row );
					$item->set_thumbnail( $value['thumbUrl'] );
					$item->set_url( $value['fullUrl'] );

					// Is the image historical!
					$historical = 0;
					$time_is = current_time( 'timestamp' );

					$image_time_is = strtotime( $value['dateCreate'] );
					$timediff = floor( ( ( $time_is - $image_time_is ) / ( 60 * 60 * 24 ) ) / 365 );

					if ( $timediff >= 4 ) {
						$historical = 1;
					}

					$item->add_meta(
						array(
							'image_id' => $value['uniqueId'],
							'previewUrl' => esc_url_raw( $value['previewUrl'] ),
							'caption' => $value['caption'],
							'credit' => $value['credit'],
							'historical' => $historical,
							'parent_id' => $value['parentId'],
							'locked' => ( $historical ? 'media-locked' : '' ),
						)
					);

					$response->add_item( $item );

				}
			}

			  return $response;

		}
	}

	/**
	 * Returns an array of tabs (routers) for the service's media manager panel.
	 *
	 * @param array $tabs Associative array of default tab items.
	 * @return array Associative array of tabs. The key is the tab ID and the value is an array of tab attributes.
	 */
	public function tabs( array $tabs ) {
		$tabs['usatsi_mexmp_service'] = array(
			'all' => array(
				'defaultTab' => true,
				'text'       => _x( 'All', 'Tab title', 'mexp' ),
			),
		);

		return $tabs;
	}

	/**
	 * Returns an array of custom text labels for this service.
	 *
	 * @param array $labels Associative array of default labels.
	 * @return array Associative array of labels.
	 */
	public function labels( array $labels ) {
		$labels['usatsi_mexmp_service'] = array(
			'insert'    => __( 'Insert', 'mexp' ),
			'noresults' => __( 'No USA Today Sports Images matched your search query.', 'mexp' ),
			'title'     => __( 'USAT Sports Images', 'mexp' ),
		);

		return $labels;
	}
}
