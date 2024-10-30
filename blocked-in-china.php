<?php

/*
 * Plugin Name:     Blocked in China
 * Plugin URI:      https://blockedinchina.io
 * Description:     Run status checks to see if your website is available in mainland China.
 * Version:         1.1.2
 * Author:          China Plugins
 * Author URI:      https://chinaplugins.com/
 * License:         GPLv3
 * Text Domain:     blocked-in-china
 * Domain Path:     /languages
 */
defined( 'ABSPATH' ) || exit;
define( "GPBIC_PLUGIN_VERSION", '1.1.2' );
define( 'GPBIC_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'GPBIC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
if ( function_exists( 'bic_fs' ) ) {
    bic_fs()->set_basename( false, __FILE__ );
} else {
    if ( !function_exists( 'bic_fs' ) ) {
        // Create a helper function for easy SDK access.
        function bic_fs() {
            global $bic_fs;
            if ( !isset( $bic_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $bic_fs = fs_dynamic_init( array(
                    'id'                             => '6351',
                    'slug'                           => 'blocked-in-china',
                    'premium_slug'                   => 'blocked-in-china-paid',
                    'type'                           => 'plugin',
                    'public_key'                     => 'pk_14f0843156b18459bb009f67e5138',
                    'is_premium'                     => false,
                    'has_addons'                     => false,
                    'has_paid_plans'                 => true,
                    'has_affiliation'                => 'selected',
                    'menu'                           => array(
                        'slug' => 'blocked-in-china',
                    ),
                    'bundle_license_auto_activation' => true,
                    'is_live'                        => true,
                ) );
            }
            return $bic_fs;
        }

        // Init Freemius.
        bic_fs();
        // Signal that SDK was initiated.
        do_action( 'bic_fs_loaded' );
    }
}
if ( !function_exists( 'bic_after_license_change' ) ) {
    function bic_after_license_change(  $change_name, FS_Plugin_Plan $plan  ) {
        // if ( in_array( $change_name, array( 'upgraded', 'changed' ) ) ) {
        // Plan was upgraded or changed.
        global $wpdb;
        $next_time = strtotime( "+30 day", time() );
        $bic_manual_api_run_last = get_option( 'bic_manual_api_run_last' );
        $current_time = wp_date( 'Y-m-d H:i:s', time(), new DateTimeZone('UTC') );
        $bic_last_api_run_time = new DateTime($bic_manual_api_run_last);
        $current_time = new DateTime($current_time);
        $interval = $bic_last_api_run_time->diff( $current_time );
        $day_lapse = $interval->d;
        if ( bic_fs()->is_plan( 'personal', true ) ) {
            if ( $day_lapse >= 7 ) {
                $timestamp = time();
            } else {
                $time_occurs = 7 - $day_lapse;
                $timestamp = strtotime( "+" . $time_occurs . " day", time() );
            }
            $current_plan = 'personal';
        } else {
            if ( bic_fs()->is_plan( 'pro', true ) ) {
                if ( $day_lapse >= 1 ) {
                    $timestamp = time();
                } else {
                    $timestamp = strtotime( "+1 day", time() );
                }
                $current_plan = 'pro';
            } else {
                if ( bic_fs()->is_plan( 'agency', true ) ) {
                    $timestamp = strtotime( "+1 hour", time() );
                    $current_plan = 'agency';
                }
            }
        }
        $next_time = wp_date( 'Y-m-d H:i:s', $timestamp, new DateTimeZone('UTC') );
        update_option( 'bic_manual_api_run', $next_time );
        update_option( 'bic_current_plan', $current_plan );
        update_option( 'bic_last_action', $change_name );
        if ( fs_redirect( admin_url() . 'admin.php?page=blocked-in-china' ) ) {
            exit;
        }
    }

    bic_fs()->add_action(
        'after_license_change',
        'bic_after_license_change',
        10,
        2
    );
}
if ( !function_exists( 'bic_load_translation' ) ) {
    /**
     * Register the plugin's translations files with WordPress.
     */
    function bic_load_translation() {
        load_plugin_textdomain( 'blocked-in-china', false, basename( dirname( __FILE__ ) ) . '/languages' );
    }

    add_action( 'plugins_loaded', 'bic_load_translation' );
}
// override Freemius optin message
if ( !function_exists( 'bic_fs_custom_connect_message_on_update' ) ) {
    function bic_fs_custom_connect_message_on_update(
        $message,
        $user_first_name,
        $product_title,
        $user_login,
        $site_link,
        $freemius_link
    ) {
        return sprintf(
            __( 'Hey %1$s,<br>In order to use <b>%2$s</b>, it\'s necessary to share your website URL and other minimal data with our servers. Click below to agree to our <a href="%3$s" target="_blank">Terms of Use & Privacy Policy</a> and connect to <a href="%4$s" target="_blank">blockedinchina.io</a> and <a href="%5$s" target="_blank">freemius.com</a>', 'blocked-in-china' ),
            $user_first_name,
            $product_title,
            'https://blockedinchina.io/terms-conditions-privacy-policy',
            'https://blockedinchina.io',
            'https://freemius.com'
        );
    }

    bic_fs()->add_filter(
        'connect_message_on_update',
        'bic_fs_custom_connect_message_on_update',
        10,
        6
    );
    bic_fs()->add_filter(
        'connect_message',
        'bic_fs_custom_connect_message_on_update',
        10,
        6
    );
}
// override Freemius optin message
if ( !function_exists( 'bic_fs_custom_connect_message_on_premium' ) ) {
    function bic_fs_custom_connect_message_on_premium(  $message, $user_first_name, $product_title  ) {
        return sprintf(
            __( 'Hey %1$s In order to use <b>%2$s</b>, it\'s necessary to share your website URL and other minimal data with our servers. Enter your license key and click below to agree to our <a href="%3$s" target="_blank">Terms of Use & Privacy Policy</a> and connect to <a href="%4$s" target="_blank">blockedinchina.io</a> and <a href="%5$s" target="_blank">freemius.com</a>', 'blocked-in-china' ),
            $user_first_name,
            $product_title,
            'https://blockedinchina.io/terms-conditions-privacy-policy',
            'https://blockedinchina.io',
            'https://freemius.com'
        );
    }

    bic_fs()->add_filter(
        'connect-message_on-premium',
        'bic_fs_custom_connect_message_on_premium',
        10,
        3
    );
}
// override Freemius opt-in logo
if ( !function_exists( 'bic_fs_custom_icon' ) ) {
    function bic_fs_custom_icon() {
        return dirname( __FILE__ ) . '/assets/images/icon-256x256.jpg';
    }

    bic_fs()->add_filter( 'plugin_icon', 'bic_fs_custom_icon' );
}
bic_fs()->override_i18n( array(
    'opt-in-connect' => __( "Yes - I'm in!", 'blocked-in-china' ),
) );
if ( function_exists( 'fs_override_i18n' ) ) {
    fs_override_i18n( array(
        'opt-in-connect' => __( 'Ok - I am in!', 'blocked-in-china' ),
    ), 'blocked-in-china' );
}
if ( !function_exists( 'bic_get_preemius_levels' ) ) {
    function bic_get_preemius_levels(  $key, $swap = false  ) {
        $levels = array(
            'personal' => 'weekly',
            'pro'      => 'daily',
            'agency'   => 'hourly',
            'free'     => 'monthly',
        );
        if ( $swap ) {
            $levels = array_flip( $levels );
        }
        return $levels[$key];
    }

}
if ( !function_exists( 'bic_get_api_key' ) ) {
    function bic_get_api_key() {
        $utc_month = wp_date( 'm', time(), new DateTimeZone('UTC') );
        $url = esc_url( 'https://api.blockedinchina.io/wp-json/blocked-in-china-api/v1/latest-key?token=' . md5( $utc_month ) );
        $response = wp_remote_get( $url );
        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            $return = array(
                'success' => false,
                'data'    => "Something went wrong: {$error_message}",
            );
        } else {
            $body = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( isset( $body['data']['error'] ) ) {
                $return = array(
                    'success' => false,
                    'data'    => $body['data']['error'],
                );
            } else {
                $return = array(
                    'success' => true,
                    'data'    => $body['data']['api_key'],
                );
            }
        }
        return $return;
    }

}
add_action( 'bic_cron_hook', 'bic_cron_execute' );
if ( !function_exists( 'bic_cron_execute' ) ) {
    function bic_cron_execute() {
        bic_cron_execute_callback();
    }

}
if ( !function_exists( 'bic_cron_execute_callback' ) ) {
    function bic_cron_execute_callback() {
        global $wpdb;
        $api_key = bic_get_api_key();
        $timestamp = wp_date( 'Y-m-d H:i:s', time(), new DateTimeZone('UTC') );
        if ( !$api_key['success'] ) {
            $return = array(
                'timestamp' => $timestamp,
                'success'   => false,
                'data'      => $api_key['data'],
            );
        } else {
            if ( 'production' !== wp_get_environment_type() ) {
                $site_domain = esc_url( 'https://alibaba.com' );
            } else {
                if ( strpos( site_url(), "playground.wordpress.net" ) !== false ) {
                    $site_domain = 'https://playground.wordpress.net';
                } else {
                    $site_domain = esc_url( site_url() );
                }
            }
            $request = new WP_REST_Request('GET', '/bic-api/v1/scanning');
            $request->set_query_params( array(
                'domain' => $site_domain,
                'apikey' => $api_key['data'],
            ) );
            $response = rest_do_request( $request );
            $server = rest_get_server();
            $response = $server->response_to_data( $response, false );
            $return = [];
            if ( isset( $response['code'] ) && in_array( $response['code'], array('api-error', 'rest_invald_param') ) ) {
                $return = array(
                    'timestamp' => $timestamp,
                    'success'   => false,
                    'data'      => "Something went wrong: " . $response['message'],
                );
            } else {
                $return = array(
                    'timestamp' => $timestamp,
                    'success'   => true,
                    'data'      => [
                        'servers' => $response['response']['detail']['server'],
                        'summary' => $response['response']['summary'],
                    ],
                );
            }
            $option_prefix = wp_date( 'Y-m-d H:i', strtotime( $timestamp ), new DateTimeZone('UTC') );
            update_option( 'bic_cron_log' . '-' . $option_prefix, $return );
        }
        return $return;
    }

}
/**
 * Deactivation hook.
 */
if ( !function_exists( 'bic_deactivate' ) ) {
    function bic_deactivate() {
        global $wpdb;
        delete_option( 'bic_cron_schedule' );
        if ( wp_next_scheduled( 'bic_cron_hook' ) ) {
            $timestamp = wp_next_scheduled( 'bic_cron_hook' );
            wp_unschedule_event( $timestamp, 'bic_cron_hook' );
        }
    }

}
register_deactivation_hook( __FILE__, 'bic_deactivate' );
require_once GPBIC_PLUGIN_PATH . '/admin/class-admin-controller.php';
require_once GPBIC_PLUGIN_PATH . '/libs/notifications-api-integration-library/class-admin-notification.php';
require_once GPBIC_PLUGIN_PATH . '/libs/features-api-integration-library/class-fil-admin-controller.php';