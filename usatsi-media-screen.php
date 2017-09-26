<?php
/*
Plugin Name: USA Today Sports Images
Plugin URI: http://www.usatimg.com/
Description: Extends Media Explorer plugin to included USA Today Sports Images
Author: USA Today Sports Images
Author URI: http://www.usatimg.com/
Domain Path: /languages/
License: GPLv2 or later
Requires at least: 3.6
Tested up to: 3.7
Text Domain: mexp
Version: 1

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/

/**
 * Create our new service. Everything starts here.
 *
 * @param array $services Associative array of Media Explorer services to load; key is a string, value is a MEXP_Template object.
 * @return array $services Associative array of Media Explorer services to load; key is a string, value is a MEXP_Template object.
 */
function usatsi_mexp_service_new( array $services ) {
  // This key name is important. You must use the same name for the tabs() and labels() methods in Usatsi_MEXP_New_Service.
  $services['usatsi_mexmp_service'] = new Usatsi_MEXP_New_Service;
  return $services;
}
add_filter( 'mexp_services', 'usatsi_mexp_service_new' );
add_action( 'wp_ajax_usatsi_download_image', 'usatsi_download_image' );
add_action( 'wp_ajax_usatsi_image_proxy', 'usatsi_image_proxy' );


// add hidden tab to media upload window
function usatsi_upload_hidden_tabs_handler($tabs) {
    $tabs['usatsitab_hidden'] = __('USAT Sports Images Hidden', 'usatsi_images');
    return $tabs;
}
add_filter('media_upload_tabs', 'usatsi_upload_hidden_tabs_handler');

//hidden tab conent handler
function usatsi_upload_hidden_tabs_content_handler() {
    wp_iframe('usatsi_media_upload_images_tab_hidden');
}
add_action('media_upload_usatsitab_hidden', 'usatsi_upload_hidden_tabs_content_handler');


function usatsi_download_image() {

  // Need to require these files
  if ( !function_exists('media_handle_upload') ) {
    require_once(ABSPATH . "wp-admin" . '/includes/image.php');
    require_once(ABSPATH . "wp-admin" . '/includes/file.php');
    require_once(ABSPATH . "wp-admin" . '/includes/media.php');
  }

  $url = $_POST['download_url'];
  $tmp = download_url( $url );

  if( is_wp_error( $tmp ) ){
    // download failed, handle error
  }

  $post_id = $_POST['post_id'];
  $desc = "The WordPress Logo";
  $file_array = array();

  // Set variables for storage
  // fix file filename for query strings
  preg_match('/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $url, $matches);
  $file_array['name'] = basename($matches[0]);
  $file_array['tmp_name'] = $tmp;

  // If error storing temporarily, unlink
  if ( is_wp_error( $tmp ) ) {
    @unlink($file_array['tmp_name']);
    $file_array['tmp_name'] = '';
  }

  // do the validation and storage stuff
  $id = media_handle_sideload( $file_array, $post_id, $desc );

  // If error storing permanently, unlink
  if ( is_wp_error($id) ) {
    @unlink($file_array['tmp_name']);
    return $id;
  }


  $src = wp_get_attachment_url( $id );

  echo $id;
  wp_die();

}

// function that output the wp_iframe content
function usatsi_media_upload_images_tab_hidden() {
    ?>
    <b>YIKES!!!!</b>

    <script>

        console.log(parent.usatsi_image_ajax.attachmentId);

        window.location = 'media-upload.php?type=image&tab=library&post_id=' + <?=absint($_REQUEST['post_id']) ?> + '&attachment_id=' + parent.usatsi_image_ajax.attachmentId;


    </script>


  <?php
}

/**
 * Backbone templates for various views for your new service
 */
class Usatsi_MEXP_New_Template extends MEXP_Template {

  /**
   * Outputs the Backbone template for an item within search results.
   *
   * @param string $id  The template ID.
   * @param string $tab The tab ID.
   */
  public function item( $id, $tab ) {
    ?>
    <div id="mexp-item-<?php echo esc_attr( $tab ); ?>-{{ data.id }}" class="mexp-item-area mexp-item" data-id="{{ data.id }}">
      <div class="mexp-item-container clearfix">
        <div class="mexp-item-thumb">
            <img src="{{ data.thumbnail }}" data-image-id="{{ data.id }}" data-download-url="{{ data.url }}" data-post-id="<?php echo esc_attr( get_the_id() ) ?>">
        </div>

        <div class="mexp-item-main">
          <div class="mexp-item-content">
            {{ data.content }}
          </div>
          <div class="mexp-item-date">
            {{ data.date }}
          </div>
        </div>

      </div>
    </div>

    <a href="#" id="mexp-check-{{ data.id }}" data-id="{{ data.id }}" class="check" title="<?php esc_attr_e( 'Deselect', 'mexp' ); ?>">
      <div class="media-modal-icon"></div>
    </a>
    <?php
  }

  /**
   * Outputs the Backbone template for a select item's thumbnail in the footer toolbar.
   *
   * @param string $id The template ID.
   */
  public function thumbnail( $id ) {
  }

  /**
   * Outputs the Backbone template for a tab's search fields.
   *
   * @param string $id  The template ID.
   * @param string $tab The tab ID.
   */
  public function search( $id, $tab ) {
    ?>
    <form action="#" class="mexp-toolbar-container clearfix tab-all">
      <input
        type="text"
        name="q"
        value="{{ data.params.q }}"
        class="mexp-input-text mexp-input-search"
        size="40"
        placeholder="<?php esc_attr_e( 'Search for anything!', 'mexp' ); ?>"
      >
      <input class="button button-large" type="submit" value="<?php esc_attr_e( 'Search', 'mexp' ); ?>">

      <div class="spinner"></div>
    </form>
    <?php
  }
}

/**
 * Your new service.
 *
 */
class Usatsi_MEXP_New_Service extends MEXP_Service {

  /**
   * Constructor.
   *
   * Creates the Backbone view template.
   */
  public function __construct() {
    $this->set_template( new Usatsi_MEXP_New_Template );
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
  }

  public function enqueue_statics() {
    // instance the class
    wp_enqueue_script(
      'mexp-service-usatsi',
      plugins_url( 'js/usatsi-media-service.js', __FILE__ ),
      array( 'jquery', 'mexp' ),
      false,
      true
    );

    wp_localize_script( 'mexp-service-usatsi', 'usatsi_image_ajax', array(
      'ajax_url' => admin_url( 'admin-ajax.php' )
    ));
  }


  /**
   * Handles the AJAX request and returns an appropriate response. This should be used, for example, to perform an API request to the service provider and return the results.
   *
   * @param array $request The request parameters.
   * @return MEXP_Response|bool|WP_Error A MEXP_Response object should be returned on success, boolean false should be returned if there are no results to show, and a WP_Error should be returned if there is an error.
   */
  public function request( array $request ) {

    $response = new MEXP_Response();

    // Oauth Params.
    $baseUrl = "http://www.usatodaysportsimages.com/api/searchAPI/";
    $consumerSecret = 'THeMinSe';
    $consumerKey = 'scoobydoo';
    $oauthTimestamp = time();
    $nonce = md5(mt_rand());
    $oauthSignatureMethod = "HMAC-SHA1";
    $oauthVersion = "1.0";
    $keywords = $request['params']['q'];
    $terms = $request['params']['q'];

    $sigBase = "GET&" . rawurlencode($baseUrl) . "&"
      . rawurlencode("keywords=" . rawurlencode($keywords)
        . "&limit=100&oauth_consumer_key=" . rawurlencode($consumerKey)
        . "&oauth_nonce=" . rawurlencode($nonce)
        . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
        . "&oauth_timestamp=" . $oauthTimestamp
        . "&oauth_version=" . $oauthVersion
        . "&offset=1&terms=" . rawurlencode($terms));

    $sigKey = $consumerSecret . "&";
    $oauthSig = base64_encode(hash_hmac("sha1", $sigBase, $sigKey, TRUE));

    $requestUrl = $baseUrl . "?oauth_consumer_key=" . rawurlencode($consumerKey)
      . "&oauth_nonce=" . rawurlencode($nonce)
      . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
      . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
      . "&oauth_version=" . rawurlencode($oauthVersion)
      . "&oauth_signature=" . rawurlencode($oauthSig)
      . "&terms=" . rawurlencode($terms)
      . "&keywords=" . rawurlencode($keywords)
      . "&limit=100&offset=1";


    $api_response = wp_remote_get( $requestUrl );
    $api_response = json_decode($api_response['body'], true);

    foreach ($api_response['results']['item'] as $row => $response_data) {
      foreach ($response_data as $innerRow => $value) {

        $item = new MEXP_Response_Item();

        $item->set_content( $value['headline'] );
        $item->set_date( strtotime( $value['dateCreate'] ) );
        $item->set_date_format( 'g:i A - j M y' );
        $item->set_id((int) 1 + (int) $row );
        $item->set_thumbnail( $value['thumbUrl'] );
        $item->set_url( esc_url_raw( $value['previewUrl'] ) );

        $response->add_item( $item );

      }
    }


    return $response;

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

class usatsi_options_page {

  public function __construct() {
    add_action( 'admin_menu', array( $this, 'usatsi_admin_menu' ) );
    add_action( 'admin_init', array( $this, 'register_usatsi_images_options' ) );

    add_filter('whitelist_options', array( $this, 'usatsi_whitelist' ) );
  }

  public function usatsi_admin_menu() {
    add_options_page(
      'USAT Sports Images Options',
      'USAT Sports Images',
      'manage_options',
      'usatsi-settings-admin',
      array(
        $this,
        'usatsi_settings_page'
      )
    );
  }

  public function usatsi_whitelist($options) {
    $added = array( 'usatsi_options_group' => array( 'usatsi_apikey', 'usatsi_apisecret') );
    $options = add_option_whitelist( $added, $options );
    return $options;
  }

  public function usatsi_settings_page() {
    // Set class property
    $this->options = get_option( 'usatsi_options' );
    ?>
      <div class="wrap">
          <h2>USA Today Sports Images</h2>
          <form method="post" action="options.php">
            <?php
            settings_fields('usatsi_options_group');
            do_settings_sections('usatsi-settings-admin');
            submit_button();
            ?>
          </form>
      </div>
  <?php }

   public function usatsi_render_apikey() {
    printf(
    '<input class="regular-text" type="text" id="usatsi_apikey" name="usatsi_options[usatsi_apikey]" value="%s" />',
    isset( $this->options['usatsi_apikey'] ) ? esc_attr( $this->options['usatsi_apikey']) : ''
);
}
  public function usatsi_render_apisecret() {
    printf(
      '<input class="regular-text" type="text" id="usatsi_apisecret" name="usatsi_options[usatsi_apisecret]" value="%s" />',
      isset( $this->options['usatsi_apisecret'] ) ? esc_attr( $this->options['usatsi_apisecret']) : ''
    );
  }

  public function register_usatsi_images_options(){
    register_setting('usatsi_options_group', 'usatsi_options',  array( $this, 'usatsi_options_validate'));
    add_settings_section('usatsi-settings-admin', 'Settings', array( $this, 'render_description' ), 'usatsi-settings-admin');
    add_settings_field('usatsi_apikey','USAT SI API KEY', array( $this, 'usatsi_render_apikey' ), 'usatsi-settings-admin', 'usatsi-settings-admin');
    add_settings_field('usatsi_apisecret','USAT SI API SECRET', array( $this, 'usatsi_render_apisecret' ), 'usatsi-settings-admin', 'usatsi-settings-admin');
  }

  public function render_description() {

  }


  /**
   * Sanitize each setting field as needed
   *
   * @param array $input Contains all settings fields as array keys
   */
  public function usatsi_options_validate( $input ){

    $new_input = array();
    if( isset( $input['usatsi_apikey'] ) )
      $new_input['usatsi_apikey'] = sanitize_text_field( $input['usatsi_apikey'] );

    if( isset( $input['usatsi_apisecret'] ) )
      $new_input['usatsi_apisecret'] = sanitize_text_field( $input['usatsi_apisecret'] );

    return $new_input;

  }

}

new usatsi_options_page;