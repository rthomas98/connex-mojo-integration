<?php
class Connex_Mojo_API {
    private $client_id = 'TradewingConnexfmApiClientId';
    private $client_secret = 'TradewingConnexfmApiClientSecret';
    private $token_endpoint = 'https://testsso.connexfm.com/connect/token';
    private $api_base_url = 'https://test.mojosrv.com';

    public function get_access_token() {
        $response = wp_remote_post( $this->token_endpoint, array(
            'body' => array(
                'grant_type' => 'client_credentials',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
            ),
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
            )
        ));

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        return isset( $data['access_token'] ) ? $data['access_token'] : false;
    }

    public function request( $endpoint, $params = array(), $method = 'GET' ) {
        $access_token = $this->get_access_token();
        if ( ! $access_token ) {
            return false;
        }

        $url = $this->api_base_url . $endpoint;
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
            ),
            'method'  => $method,
        );

        if ( $method == 'POST' ) {
            $args['body'] = $params;
        }

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            return false;
        }
        
        # 20240528: temporary workaround for issue with certain special characters causing json_decode() to fail
        $json = wp_remote_retrieve_body( $response );
        $json = preg_replace('/[[:cntrl:]]/', '', $json);
        
        return json_decode($json, true );
    }
}
