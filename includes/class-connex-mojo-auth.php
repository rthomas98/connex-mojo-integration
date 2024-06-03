<?php
class Connex_Mojo_Auth {
    private $api;

    public function __construct( $api ) {
        $this->api = $api;
        add_action( 'wp_ajax_connex_mojo_login', array( $this, 'handle_login_request' ) );
        add_action( 'wp_ajax_nopriv_connex_mojo_login', array( $this, 'handle_login_request' ) );
    }

    public function handle_login_request() {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $response = $this->api->request( '/connect/token', array(
            'grant_type' => 'password',
            'client_id' => $this->api->client_id,
            'client_secret' => $this->api->client_secret,
            'username' => $username,
            'password' => $password,
            'scope' => 'openid profile mojoApi offline_access roles'
        ), 'POST' );

        if ( $response && isset( $response['access_token'] ) ) {
            echo 'Login successful! Access token: ' . $response['access_token'];
        } else {
            echo 'Login failed. Please check your credentials.';
        }

        wp_die();
    }
}
