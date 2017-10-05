<?php

class usatsi_options_page {

  public function __construct() {
    add_action( 'admin_menu', array( $this, 'usatsi_admin_menu' ) );
    add_action( 'admin_init', array( $this, 'register_usatsi_images_options' ) );

    add_filter( 'whitelist_options', array( $this, 'usatsi_whitelist' ) );
  }

  public function usatsi_admin_menu() {
    add_options_page(
      'USAT Sports Images Options',
      'USAT Sports Images',
      'manage_options',
      'usatsi-settings-admin',
      array(
        $this,
        'usatsi_settings_page',
      )
    );
  }

  public function usatsi_whitelist( $options ) {
    $added = array(
      'usatsi_options_group' => array( 'usatsi_apikey', 'usatsi_apisecret' ),
    );
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
        settings_fields( 'usatsi_options_group' );
        do_settings_sections( 'usatsi-settings-admin' );
        submit_button();
        ?>
      </form>
    </div>
    <?php
  }

  public function usatsi_render_apikey() {
    printf(
      '<input class="regular-text" type="text" id="usatsi_apikey" name="usatsi_options[usatsi_apikey]" value="%s" />',
      isset( $this->options['usatsi_apikey'] ) ? esc_attr( $this->options['usatsi_apikey'] ) : ''
    );
  }
  public function usatsi_render_apisecret() {
    printf(
      '<input class="regular-text" type="text" id="usatsi_apisecret" name="usatsi_options[usatsi_apisecret]" value="%s" />',
      isset( $this->options['usatsi_apisecret'] ) ? esc_attr( $this->options['usatsi_apisecret'] ) : ''
    );
  }

  public function register_usatsi_images_options() {
    register_setting( 'usatsi_options_group', 'usatsi_options',  array( $this, 'usatsi_options_validate' ) );
    add_settings_section( 'usatsi-settings-admin', 'Settings', array( $this, 'render_description' ), 'usatsi-settings-admin' );
    add_settings_field( 'usatsi_apikey','USAT SI API KEY', array( $this, 'usatsi_render_apikey' ), 'usatsi-settings-admin', 'usatsi-settings-admin' );
    add_settings_field( 'usatsi_apisecret','USAT SI API SECRET', array( $this, 'usatsi_render_apisecret' ), 'usatsi-settings-admin', 'usatsi-settings-admin' );
  }

  public function render_description() {

  }


  /**
   * Sanitize each setting field as needed
   *
   * @param array $input Contains all settings fields as array keys
   */
  public function usatsi_options_validate( $input ) {

    $new_input = array();
    if ( isset( $input['usatsi_apikey'] ) ) {
      $new_input['usatsi_apikey'] = sanitize_text_field( $input['usatsi_apikey'] );
    }

    if ( isset( $input['usatsi_apisecret'] ) ) {
      $new_input['usatsi_apisecret'] = sanitize_text_field( $input['usatsi_apisecret'] );
    }

    return $new_input;

  }

}

new usatsi_options_page();
