<?php
/**
 * Controller class for admin pages
 */
class AdminController {

    // Single constructor
    public function __construct() {
        // Admin menu and scripts
        add_action('admin_menu', [ $this, 'blocked_in_china_page_callback' ]);
        add_action('admin_enqueue_scripts', [ $this, 'blocked_in_china_scripts_enqueue' ]);

        // Disable Google Fonts on the front-end
        add_action('wp_enqueue_scripts', [ $this, 'bic_maybe_disable_google_fonts' ], 9999);

        // Disable Google Fonts in admin area
        add_action('admin_enqueue_scripts', [ $this, 'bic_maybe_disable_google_fonts' ], 9999);

        // Remove inline styles that load Google Fonts
        add_filter('style_loader_tag', [ $this, 'bic_remove_inline_google_fonts' ], 10, 2);

        // Cron schedules
        add_filter('cron_schedules', [ $this, 'blocked_in_china_cron_add_monthly' ]);

        // AJAX actions
        add_action("wp_ajax_gpbic_run_api", [ $this, 'blocked_in_china_run_api' ]);
        add_action("wp_ajax_nopriv_gpbic_run_api", [ $this, 'blocked_in_china_run_api' ]);

        add_action('wp_ajax_bic_toggle_google_fonts', [$this, 'bic_toggle_google_fonts']);
        add_action("wp_ajax_bic_filter_log", [ $this, 'blocked_in_china_filter_log' ]);
        add_action("wp_ajax_nopriv_bic_filter_log", [ $this, 'blocked_in_china_filter_log' ]);

        add_action("wp_ajax_gpbic_run_api_manual", [ $this, 'blocked_in_china_run_api_manual' ]);
        add_action("wp_ajax_nopriv_gpbic_run_api_manual", [ $this, 'blocked_in_china_run_api_manual' ]);

        add_action("wp_ajax_bic_admin_status_bar", [ $this, 'blocked_in_china_admin_status_bar' ]);
        add_action("wp_ajax_nopriv_bic_admin_status_bar", [ $this, 'blocked_in_china_admin_status_bar' ]);

        // Admin bar custom style and item
        if (get_option('bic_admin_bar_status')) {
            add_action('admin_bar_menu', [ $this, 'admin_bar_item' ], 500);
            add_action('admin_head', [ $this, 'bic_custom_style' ]);
        }

        // Register REST API routes
        add_action('rest_api_init', [ $this, 'bic_register_custom_routes' ]);

        // Register Google Fonts setting
        add_action('admin_init', [$this, 'bic_register_google_fonts_setting']);
    }

    // Dequeue Google Fonts across the website
    public function bic_maybe_disable_google_fonts() {
        if (get_option('bic_disable_google_fonts', false)) {
            global $wp_styles;
            foreach ($wp_styles->registered as $style) {
                if (strpos($style->src, 'fonts.googleapis.com') !== false || strpos($style->src, 'fonts.gstatic.com') !== false) {
                    wp_dequeue_style($style->handle); // Dequeue the style that loads Google Fonts
                }
            }
        }
    }

    // Remove Google Fonts in inline styles
    public function bic_remove_inline_google_fonts($html, $handle) {
        if (get_option('bic_disable_google_fonts', false) && strpos($html, 'fonts.googleapis.com') !== false) {
            return ''; // Return empty string to remove the inline Google Fonts
        }
        return $html;
    }

    // Function to enqueue admin scripts
    public function blocked_in_china_scripts_enqueue($hook) {
        if ($hook != "toplevel_page_blocked-in-china") {
            return;
        }

        wp_enqueue_style('gpbic-style', GPBIC_PLUGIN_URL . '/assets/css/gpbic.css');
        wp_enqueue_script(
            'gpbic-script',
            GPBIC_PLUGIN_URL . '/assets/js/gpbic.js',
            ['jquery', 'wp-api-request'],
            GPBIC_PLUGIN_VERSION,
            true
        );
        wp_localize_script(
            'gpbic-script',
            'gpbic',
            [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'current_page' => isset($_GET["tab"]) ? $_GET["tab"] : '',
                'siteurl' => site_url(),
                'nonce' => wp_create_nonce('wp_rest'),
            ]
        );
    }

    // Toggle Google Fonts setting via AJAX
    public function bic_toggle_google_fonts() {
        check_ajax_referer('bic_google_fonts_nonce', 'security');
        $disable_google_fonts = isset($_POST['disable_google_fonts']) ? intval($_POST['disable_google_fonts']) : 0;
        update_option('bic_disable_google_fonts', $disable_google_fonts);
        wp_send_json_success(['message' => __('Google Fonts setting updated successfully!', 'blocked-in-china')]);
    }

    // Register REST API routes
    public function bic_register_custom_routes(){
        register_rest_route(
            'bic-api/v1',
            '/scanning',
            array(
                array(
                    'methods'  => 'GET',
                    'callback' => array( $this, 'bic_get_scanning_callback' ),
                    'permission_callback' => function () {
                        return '__return_true';
                    }
                ),
            )
        );
    }

    // Register a setting to disable Google Fonts
    public function bic_register_google_fonts_setting() {
        register_setting('bic_settings_group', 'bic_disable_google_fonts', [
            'sanitize_callback' => 'sanitize_text_field',
            'default' => false,
        ]);
    }

    public function bic_get_scanning_callback($request){
        $parameters = $request->get_query_params();
        if( isset($parameters['domain']) && !empty($parameters['domain']) ){
            $domain = esc_url($parameters['domain']);
        } else{
            return new WP_Error( 'rest_invald_param', __( 'Domain to check is required.', 'blocked-in-china' ), array( 'status' => 400 ) );
        }

        if( isset($parameters['apikey']) && !empty($parameters['apikey']) ){
            $api_key = $parameters['apikey'];
        } else{
            return new WP_Error( 'rest_invald_param', __( 'API Key to check is required.', 'blocked-in-china' ), array( 'status' => 400 ) );
        }

        $apiurl = add_query_arg( array(
            'domain' => $domain,
            'apikey' => $api_key,
            'output' => 'json',
        ), esc_url( 'https://api.viewdns.info/chinesefirewall' ) );

        $response = wp_remote_get( $apiurl, array( 'timeout' => 1000 ) );

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            return new WP_Error( 'api-error', esc_html( $error_message ), array( 'status' => 500 ) );
        } else{
            $body = json_decode( wp_remote_retrieve_body( $response ), true );
            return new WP_REST_Response( $body, 200 );
        }
    }

    // Add cron schedule
    public function blocked_in_china_cron_add_monthly($schedules){
        $schedules['monthly'] = array(
            'interval' => MONTH_IN_SECONDS,
            'display'  => __( 'Once Monthly', 'blocked-in-china' )
        );
        return $schedules;
    }

    // Filter log
    public function blocked_in_china_filter_log(){
        if( !isset($_POST['option']) || empty($_POST['option']) ){
            wp_send_json_error( __("Please select an option to filter", 'blocked-in-china' ) );
        } else{
            $option = sanitize_text_field($_POST['option']);
        }

        global $wpdb;
        $query = $wpdb->prepare("SELECT `option_value` FROM $wpdb->options WHERE `option_id` LIKE %d", $option);
        $record = $wpdb->get_var($query);
        $record = maybe_unserialize($record);

        if($record){
            if(!isset($record['data']['summary']['result'])){
                wp_send_json_error($record['data']);
            }

            if($record['data']['summary']['result'] == "visible"){
                wp_send_json_success($record['data']);
            } else{
                wp_send_json_error($record['data']['summary']['description']);
            }
        } else{
            wp_send_json_error( __("Unable to fetch the log. Reload the page and try again...", 'blocked-in-china') );
        }
    }

    // Run API Manually
    public function blocked_in_china_run_api(){
        // verify nonce
        check_ajax_referer( 'gpbic_nonce', 'security' );

        global $bic_fs;
        if( $bic_fs->is_plan('free', true) ){
            wp_send_json_error(
                sprintf(
                    __("Scheduling status updates is not allowed on %1$s plan, %2$s", 'blocked-in-china'),
                    '<b>' . ucfirst( __( 'Free', 'blocked-in-china' ) ) . '</b>',
                    '<a href="' . esc_url( $bic_fs->get_upgrade_url() ) . '">' . __( 'Upgrade here', 'blocked-in-china' ) . '</a>'
                )
            );
        }

        if(!isset($_POST['bic_cron_on']) || empty($_POST['bic_cron_on'])){
            // unschedule current CRON
            if ( wp_next_scheduled( 'bic_cron_hook' ) ) {
                $timestamp = wp_next_scheduled('bic_cron_hook');
                wp_unschedule_event($timestamp, 'bic_cron_hook');
            }
            delete_option('bic_cron_schedule');
            $cron_status = false;
        } else {
            $cron_status = true;
            if( !isset($_POST['frequency']) || empty($_POST['frequency']) ){
                wp_send_json_error( __("Please select a frequency", 'blocked-in-china' ) );
            } else{
                $frequency = sanitize_text_field($_POST['frequency']);
            }

            // Perform Freemius plan validations
            $show = false;
            if ( $bic_fs->is_plan( 'personal', true ) ) {
                $show = [ 'monthly', 'weekly' ];
            } else if ( $bic_fs->is_plan( 'pro', true ) ) {
                $show = [ 'monthly', 'weekly', 'daily' ];
            } else if ( $bic_fs->is_plan( 'agency', true ) ) {
                $show = [ 'monthly', 'weekly', 'daily', 'hourly' ];
            }

            if( !$show || !in_array($frequency, $show) ){
                wp_send_json_error(
                    sprintf(
                        __("You are not allowed to schedule %s updates, %1$s", 'blocked-in-china'),
                        $frequency,
                        '<a href="' . esc_url( $bic_fs->get_upgrade_url() ) . '">' . __( 'Upgrade here', 'blocked-in-china' ) . '</a>'
                    )
                );
            }

            // unschedule current CRON
            if ( wp_next_scheduled( 'bic_cron_hook' ) ) {
                $timestamp = wp_next_scheduled( 'bic_cron_hook' );
                wp_unschedule_event( $timestamp, 'bic_cron_hook' );
            }

            // check if CRON is already set at same Frequency
            if( get_option( 'bic_cron_schedule' ) == $frequency ){
                wp_send_json_error(
                    sprintf(
                        __("Cron is already scheduled to run %1$s.", 'blocked-in-china'),
                        $frequency
                    )
                );
            }

            // schedule new CRON
            $response = wp_schedule_event(time(), $frequency, 'bic_cron_hook');
            if($response){
                update_option('bic_cron_schedule', $frequency);
            }
        }

        update_option('bic_cron_on', $cron_status);
        wp_send_json_success( __("Settings saved successfully!", 'blocked-in-china' ) );
    }

    public function blocked_in_china_run_api_manual(){
        // verify nonce
        check_ajax_referer( 'gpbic_nonce', 'security' );

        // validate the request
        $next_run_time = get_option('bic_manual_api_run');
        $current_time = wp_date('Y-m-d H:i:s', time(), new DateTimeZone('UTC'));

        if( $current_time < $next_run_time ){
            wp_send_json_error( __( 'You are not authorized to perform the request now.', 'blocked-in-china' ) );
        }

        global $bic_fs;

        $next_time = strtotime("+30 day", time());

        if ( $bic_fs->is_plan('personal', true) ) {
            $next_time = strtotime("+7 day", time());
        } else if ( $bic_fs->is_plan('pro', true) ) {
            $next_time = strtotime("+1 day", time());
        } else if ( $bic_fs->is_plan('agency', true) ) {
            $next_time = strtotime("+1 hour", time());
        }

        $return = bic_cron_execute_callback();

        if( $return['success'] ){
            $option_prefix = wp_date('Y-m-d H:i', strtotime($return['timestamp']), new DateTimeZone('UTC'));
            get_option('bic_cron_log' . '-' . $option_prefix);
            update_option('bic_manual_api_run', wp_date('Y-m-d H:i:s', $next_time, new DateTimeZone('UTC')));
            update_option('bic_manual_api_run_last', wp_date('Y-m-d H:i:s', strtotime($return['timestamp']), new DateTimeZone('UTC')));
            wp_send_json_success(
                array(
                    'data' => $return['data'],
                    'next_run' => wp_date('Y-m-d H:i:s', $next_time, new DateTimeZone('UTC'))
                )
            );
        } else {
            wp_send_json_error( $return['data'] );
        }
    }

    // Admin Menu
    public function blocked_in_china_page_callback() {
        add_menu_page(
            __( 'Blocked in China', 'blocked-in-china' ),
            'Blocked in China',
            'manage_options',
            'blocked-in-china',
            [ $this, 'blocked_in_china_menu_callback' ],
            'dashicons-unlock',
            100
        );
    }

    public function blocked_in_china_menu_callback() {
        if( isset($_GET['tab']) && $_GET['tab'] == "bundle" ){
            require_once GPBIC_PLUGIN_PATH . '/admin/templates/bundle.php';
        } else if( isset($_GET['tab']) && $_GET['tab'] == "log" ){
            require_once GPBIC_PLUGIN_PATH . '/admin/templates/log.php';
        } else if( isset($_GET['tab']) && $_GET['tab'] == "schedule" ){
            require_once GPBIC_PLUGIN_PATH . '/admin/templates/schedule.php';
        } else {
            require_once GPBIC_PLUGIN_PATH . '/admin/templates/form.php';
        }
    }

    public function blocked_in_china_admin_status_bar() {
        if( !isset($_POST['option']) || empty($_POST['option']) ){
            wp_send_json_error( __("Please select an option to filter", 'blocked-in-china' ) );
        }

        update_option('bic_admin_bar_status', $_POST['option']=='true' ? 1 : 0);

        return true;
    }

    public function admin_bar_item (WP_Admin_Bar $admin_bar) {
        $admin_item = array(
            'id'    => 'bic-status',
            'parent' => 'top-secondary',
            'group'  => null,
            'href' => admin_url('admin.php?page=blocked-in-china')
        );

        $bic_manual_api_run = get_option('bic_manual_api_run');
        $bic_manual_api_run_last = get_option('bic_manual_api_run_last');
        $wp_timezone = empty(get_option('timezone_string')) ? 'UTC' : get_option('timezone_string');
        $admin_bar_display_time = wp_date('F d, Y H:i', strtotime($bic_manual_api_run_last), new DateTimeZone($wp_timezone));
        $option_prefix = wp_date('Y-m-d H:i', strtotime($bic_manual_api_run_last), new DateTimeZone('UTC'));
        $manual_log = get_option('bic_cron_log' . '-' . $option_prefix);

        if($bic_manual_api_run && !empty($manual_log['data']['servers'])) {
            $title = sprintf(
                __( 'Blocked in China Status: Not Blocked as of %1$s', 'blocked-in-china' ),
                $admin_bar_display_time . " " . $wp_timezone
            );
            $meta = array(
                'title' => __( 'Blocked in China Status: Not Blocked', 'blocked-in-china' ),
                'class' => 'bic-green'
            );
        } elseif($bic_manual_api_run && empty($manual_log['data']['servers'])) {
            $title = sprintf(
                __( 'Blocked in China Status: Blocked as of %1$s', 'blocked-in-china' ),
                $admin_bar_display_time . " " . $wp_timezone
            );
            $meta = array(
                'title' => __( 'Blocked in China Status: Blocked', 'blocked-in-china' ),
                'class' => 'bic-red'
            );
        } else {
            $title = __( 'Blocked in China Status: Check', 'blocked-in-china' );
            $meta = array(
                'title' => __( 'Blocked in China Status: Check', 'blocked-in-china' ),
                'class' => 'bic-gray'
            );
        }

        $admin_item['title'] = $title;
        $admin_item['meta'] = $meta;

        $admin_bar->add_menu($admin_item);
    }

    public function bic_custom_style() {
        echo '<style>
            .bic-gray { background: #434343 !important; }
            .bic-green { background: #6aa84f !important; }
            .bic-red { background: #cc0000 !important; }
        </style>';
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
            'em'     => [ 'align' => [], 'class' => [], 'type' => [], 'id' => [], 'style' => [] ],
            'hr'     => [],
            'p'      => [ 'align' => [], 'class' => [], 'type' => [], 'id' => [], 'style' => [], 'data' => [] ],
            'img'    => [ 'align' => [], 'class' => [], 'type' => [], 'id' => [], 'style' => [], 'src' => [], 'alt' => [], 'href' => [], 'rel' => [], 'target' => [], 'value' => [], 'name' => [], 'width' => [], 'height' => [], 'data' => [], 'title' => [] ]
        ];
    }
}

new AdminController();
