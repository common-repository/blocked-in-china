<?php

/**
 * Features integration library Admin Controller
 */
class GPFIL_AdminController {

	function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'gpfil_scripts_enqueue' ] );

		add_action( "wp_ajax_fil_get_all_features", [ $this, 'fil_get_all_features_callback' ] );
		add_action( "wp_ajax_nopriv_fil_get_all_features", [ $this, 'fil_get_all_features_callback' ] );

		add_action( "wp_ajax_fil_features_contact", [ $this, 'fil_features_contact_callback' ] );
		add_action( "wp_ajax_nopriv_fil_features_contact", [ $this, 'fil_features_contact_callback' ] );
	}

	public function gpfil_scripts_enqueue() {
		wp_enqueue_style( 'gpfil-style', plugin_dir_url( __FILE__ ) . 'assets/css/features-library.css' );
		wp_enqueue_script(
			'gpfil-script',
			plugin_dir_url( __FILE__ ) . 'assets/js/features-library.js',
			[ 'jquery', 'wp-api-request' ],
			'1.0.0',
			true
		);
		wp_localize_script(
			'gpfil-script',
			'gpfil',
			[
				'ajaxurl'      => admin_url( 'admin-ajax.php' ),
				'current_page' => isset( $_GET["tab"] ) ? $_GET["tab"] : '',
				'siteurl'      => site_url(),
				'nonce'        => wp_create_nonce( 'wp_rest' ),
				'api_url'      => isset( $_GET["page"] ) ? $_GET["page"] : '',
			]
		);
	}

	public function fil_get_all_features_callback() {

		$slug = empty( $_POST['current_page'] ) ? 'status' : sanitize_text_field( $_POST['current_page'] );

		$transient = get_transient( 'china_blocked_area_' . $slug );

		if ( ! empty( $transient ) ) {
			wp_send_json_success( $transient );
		}

		$api_url = ! empty( $_POST['api_url'] ) ? $_POST['api_url'] : '';

		$response = $this->get_features( $slug, $api_url );

		if ( ! empty( $response ) ) {
			set_transient( 'china_blocked_area_' . $slug, $response['data'], HOUR_IN_SECONDS );
		}

		wp_send_json_success( $response['data'] );

	}

	public function fil_features_contact_callback() {

		if ( ! isset( $_POST['features'] ) || empty( $_POST['features'] ) ) {
			wp_send_json_error( __( "Please select at least one feature.", 'blocked-in-china' ) );
		}
		if ( ! isset( $_POST['fname'] ) || empty( $_POST['fname'] ) ) {
			wp_send_json_error( __( "Please enter first name.", 'blocked-in-china' ) );
		}
		if ( ! isset( $_POST['lname'] ) || empty( $_POST['lname'] ) ) {
			wp_send_json_error( __( "Please enter last name.", 'blocked-in-china' ) );
		}
		if ( ! isset( $_POST['email'] ) || empty( $_POST['email'] ) ) {
			wp_send_json_error( __( "Please enter email address.", 'blocked-in-china' ) );
		}

		$body = $this->subscribe( $_POST );

		$message = ! empty( $body['message'] ) ? $body['message'] : __( 'Thank You', 'blocked-in-china' );
		wp_send_json_success( $message );

	}

	public function get_features( $slug = 'status', $api_url = '' ) {
		if ( $api_url == 'blocked-in-china' ) {
			$url = esc_url( 'https://api.blockedinchina.io/wp-json/features-api/v1/area/' . $slug );
		} else {
			$url = '';
		}

		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {

			$error_message = $response->get_error_message();
			wp_send_json_error( __( $error_message, 'blocked-in-china' ) );

		}

		$response = wp_remote_retrieve_body( $response );
		$response = json_decode( $response, true );

		if ( ! is_array( $response ) ) {
			$response = [];
		}

		return $response;
	}

	public function subscribe( $post = [] ) {

		if ( $post['api_url'] == 'blocked-in-china' ) {
			$url = esc_url( 'https://api.blockedinchina.io/wp-json/features-api/v1/subscriber/tag' );
		} else {
			$url = '';
		}

		$first_name    = sanitize_text_field( $post['fname'] );
		$last_name     = sanitize_text_field( $post['lname'] );
		$email_address = sanitize_text_field( $post['email'] );
		$area_slug     = sanitize_text_field( $post['area_slug'] );
		$tags          = $post['features'];
		$message       = sanitize_textarea_field( $post['message'] );

		$response = wp_remote_post(
			$url,
			[
				'body' => [
					'first_name'    => $first_name,
					'last_name'     => $last_name,
					'email_address' => $email_address,
					'tags'          => $tags,
					'area_slug'     => $area_slug,
					'message'       => $message,
				],
			]

		);

		if ( is_wp_error( $response ) ) {

			$error_message = $response->get_error_message();
			wp_send_json_error( __( $error_message, 'blocked-in-china' ) );

		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}
}

new GPFIL_AdminController;
?>