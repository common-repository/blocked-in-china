<?php 
/**
 * 
 */
class GPNIL_AdminNotification
{
	
	function __construct()
	{
		add_action( 'admin_enqueue_scripts', [ $this, 'nil_scripts_enqueue' ] );
		add_action( 'rest_api_init', [ $this,'bic_plugin_routes' ] );
		add_action( 'admin_notices', [ $this, 'bic_admin_notice' ] );
	}

	public function nil_scripts_enqueue() {
		
		wp_enqueue_style( 'gpnil-style', plugin_dir_url( __FILE__ ) . 'assets/css/notifications-library.css' );
	   	wp_enqueue_script(
	        'gpnil-script',
	        plugin_dir_url( __FILE__ ) . 'assets/js/notifications-library.js',
	        [ 'jquery', 'wp-api-request' ],
	        '1.0.0',
	        true
	    );
	    wp_localize_script(
	        'gpnil-script',
	        'gpnil',
	        array( 
	        	'ajaxurl' => admin_url( 'admin-ajax.php' ),
	        	'current_page' => isset($_GET["tab"]) ? $_GET["tab"] : '',
	        	'siteurl'	=> site_url(),
	        	'nonce' => wp_create_nonce('wp_rest'),
	        	'api_url' => isset($_GET["page"]) ? $_GET["page"] : '',
	        	)
	    );
 
	}

	public function bic_plugin_routes(){
	    register_rest_route(
	      'blocked-in-china/v1',
	      '/administration/dismiss-notification',
	      [
	        'methods'  => WP_REST_Server::CREATABLE,
	        'callback' => [ $this, 'dismiss_notification' ],
	        'permission_callback' => function () {
		        return '__return_true';
	        }
	      ]
	    );
	}

	public function dismiss_notification( WP_REST_Request $request ) {
	    
		global $wpdb;

	    $latest_notification = $this->get_latest_notification();
	    
	    if( isset( $latest_notification[ 'id' ] ) )
	      update_user_meta(
	        get_current_user_id(),
	        'nil_last_notification_id',
	        $latest_notification[ 'id' ]
	      );

	    return rest_ensure_response( [
	      'status' => 'ok'
	    ] );
	}

	public function get_latest_notification() {

    	global $bic_fs;

		$plan = $bic_fs->get_plan();
		$plan_id = ( $plan->id ?? 0 );

		$response = get_transient( 'nil_latest_notification_' . $plan_id );

		if( !empty( $response ) ) return $response;

		$response = wp_remote_get( 'https://api.blockedinchina.io/wp-json/notification-system-api/v1/notifications/latest?segment=' . $plan_id );

		if( is_wp_error( $response ) ) return null;

		$response = wp_remote_retrieve_body( $response );

		if( empty( $response ) ) return null;

		$response = json_decode( $response, true );

		if( empty( $response ) || !is_array( $response ) || !isset( $response[ 'data' ] ) ) return null;

		set_transient( 'nil_latest_notification_' . $plan_id, $response[ 'data' ], HOUR_IN_SECONDS );

		return $response[ 'data' ];
	}

	public function bic_admin_notice() {
	   
	   	if( !current_user_can( 'manage_options' ) ) return;

	    $latest_notification = $this->get_latest_notification();

	    if( intval( get_user_meta( get_current_user_id(), 'nil_last_notification_id', true ) ) === intval( $latest_notification[ 'id' ] ) )
	    	return;

	    $protocols = wp_allowed_protocols();

	    if( !in_array( 'data', $protocols ) )
	      $protocols[] = 'data';

	    $content = wp_kses( $latest_notification[ 'content' ], $this->china_blocked_content_allowed_html_tags(), $protocols );

	    echo '<div id="nil-notification-container" class="notice notice-info is-dismissible">
	            <h2>' . $latest_notification[ 'title' ]. '</h2>
	            ' . $content . '
	          </div>';
	}

	public function china_blocked_content_allowed_html_tags() {
		return [
			'a'      => [ 'href' => [], 'target' => [], 'alt' => [] ],
			'br'     => [],
			'video'  => [ 'width' => [], 'height' => [] ],
			'source' => [ 'src' => [], 'type' => [] ],
			'strong' => [ 'style' => [] ],
			'sub'    => [ 'style' => [] ],
			'sup'    => [ 'style' => [] ],
			's'      => [ 'style' => [] ],
			'i'      => [ 'style' => [] ],
			'u'      => [ 'style' => [] ],
			'span'   => [ 'align' => [], 'class' => [], 'type' => [], 'id' => [], 'style' => [], 'data' => [] ],
			'h1'     => [ 'align' => [], 'class' => [], 'type' => [], 'id' => [], 'style' => [], 'data' => [] ],
			'h2'     => [ 'align' => [], 'class' => [], 'type' => [], 'id' => [], 'style' => [], 'data' => [] ],
			'h3'     => [ 'align' => [], 'class' => [], 'type' => [], 'id' => [], 'style' => [], 'data' => [] ],
			'ol'     => [ 'align' => [], 'class' => [], 'type' => [], 'id' => [], 'style' => [], 'data' => [] ],
			'ul'     => [ 'align' => [], 'class' => [], 'type' => [], 'id' => [], 'style' => [], 'data' => [] ],
			'li'     => [ 'align' => [], 'class' => [], 'type' => [], 'id' => [], 'style' => [], 'data' => [] ],
			'em'     => [ 'align' => [], 'class' => [], 'type' => [], 'id' => [], 'style' => [], 'data' => [] ],
			'hr'     => [],
			'p'      => [ 'align' => [], 'class' => [], 'type' => [], 'id' => [], 'style' => [], 'data' => [] ],
			'img'    => [ 'align' => [], 'class' => [], 'type' => [], 'id' => [], 'style' => [], 'src' => [], 'alt' => [], 'href' => [], 'rel' => [], 'target' => [], 'value' => [], 'name' => [], 'width' => [], 'height' => [], 'data' => [], 'title' => [] ]
		];
	}
}
new GPNIL_AdminNotification;
?>