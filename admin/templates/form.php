<?php  global $bic_fs; ?>
<div class="wrap">
	<h1><?php _e( 'Blocked in China', 'blocked-in-china' ); ?></h1>

	<nav class="nav-tab-wrapper">
		<a href="?page=blocked-in-china" class="nav-tab nav-tab-active"><?php _e( 'Status', 'blocked-in-china' ); ?></a>
		<a href="?page=blocked-in-china&tab=schedule" class="nav-tab"><?php _e( 'Schedule', 'blocked-in-china' ); ?></a>
		<a href="?page=blocked-in-china&tab=log" class="nav-tab"><?php _e( 'Log', 'blocked-in-china' ); ?></a>
		<a href="?page=blocked-in-china&tab=bundle" class="nav-tab"><?php _e( 'China Plugins Bundle <span class="dashicons dashicons-star-filled bic-yellow"></span>', 'blocked-in-china' ); ?></a>
	</nav>

	<?php 

	$bic_manual_api_run = get_option( 'bic_manual_api_run' );
	$remaining_gap = 0;
	if($bic_manual_api_run){

		$current_time = wp_date( 'Y-m-d H:i:s', time(), new DateTimeZone('UTC') );
		$b = new DateTime($bic_manual_api_run);
		$a = new DateTime($current_time);
		$interval = $b->diff($a);
		if( $interval->invert ){
			$remaining_gap = $interval->y + $interval->m + $interval->d + $interval->h + $interval->i;
		} else{
			$remaining_gap = 0;
		}
	}

	$frequency = 'monthly';
	$freemius_plan = 'free';
	$current_plan = $bic_fs->get_plan();

	if ( $bic_fs->is_plan( 'personal', true ) ) {
		$freemius_plan = 'personal';
		$frequency = bic_get_preemius_levels('personal');

		if($interval->d > 7){
			bic_after_license_change( 'changed', $current_plan );
		}

	} else if ( $bic_fs->is_plan( 'pro', true ) ) {
		$freemius_plan = 'pro';
		$frequency = bic_get_preemius_levels('pro');

		if($interval->d > 1){
			bic_after_license_change( 'changed', $current_plan );
		}

	} else if ( $bic_fs->is_plan( 'agency', true ) ) {
		$freemius_plan = 'agency';
		$frequency = bic_get_preemius_levels('agency');

		if($interval->h > 1){
			bic_after_license_change( 'changed', $current_plan );
		}
	}
	

	?>

	<?php if( !$remaining_gap ){?>
		<h2 class="title"><?php _e( 'Welcome to Blocked in China', 'blocked-in-china' ); ?></h2>
		<p><?php _e( 'Check the status of your site to determine if it is available in China.', 'blocked-in-china' ); ?></p>
	<?php } ?>

	<form id="gpbic_form" method="POST" novalidate="novalidate">
		<input type="hidden" id="form_action" name="action" value="gpbic_run_api_manual" />	
		<input type="hidden" id="next_run" value="<?php echo $remaining_gap ? $bic_manual_api_run : 0;?>" />	
		<?php wp_nonce_field( 'gpbic_nonce' ); ?>

		<p class="submit">
			<button type="submit" name="submit" id="submit" class="button button-primary" <?php echo ( $remaining_gap > 0 ) ? 'disabled' : '' ; ?> ><?php _e( 'Check Status', 'blocked-in-china' ); ?></button>
			<span class="spinner"></span>
		</p>
	</form>

	<p>
		<?php 
		printf(
		    __( 'Your %1$s plan entitles you to %2$s Blocked in China Status updates for your site.', 'blocked-in-china' ),
		    '<strong>' . ucfirst($freemius_plan) . '</strong>',
		    '<strong>' . ucfirst($frequency) . '</strong>'
		);
		?>
	</p>
	<p>
		<?php
		if ( $remaining_gap ) {
			
			$remaining_time = [];
			if( $interval->d ){
				/* translators: 1: days count, 2: plural days count. */
				$days = sprintf(
				    _n(
				        '%1$s day',
				        '%1$s days',
				        $interval->d,
				        'blocked-in-china'
				    ),
				    number_format_i18n( $interval->d )
				);

				array_push( $remaining_time, $days );

			}

			if( $interval->h ){
				/* translators: 1: hours count, 2: plural hours count. */
				$hours = sprintf(
				    _n(
				        '%1$s hour',
				        '%1$s hours',
				        $interval->h,
				        'blocked-in-china'
				    ),
				    number_format_i18n( $interval->h )
				);

				array_push( $remaining_time, $hours );

			}

			if( $interval->i ){
				/* translators: 1: minutes count, 2: minutes hours count. */
				$mins = sprintf(
				    _n(
				        '%1$s minute',
				        '%1$s minutes',
				        $interval->i,
				        'blocked-in-china'
				    ),
				    number_format_i18n( $interval->i )
				);

				array_push( $remaining_time, $mins );

			}

			if( $interval->s ){
				/* translators: 1: seconds count, 2: plural seconds count. */
				$seconds = sprintf(
				    _n(
				        '%1$s second',
				        '%1$s seconds',
				        $interval->s,
				        'blocked-in-china'
				    ),
				    number_format_i18n( $interval->s )
				);

				array_push( $remaining_time, $seconds );

			}

		} 
		
		if( $remaining_gap > 0 ){
			$remaining_time_text = sprintf( __( 'in %s', 'blocked-in-china' ), implode( ", ", $remaining_time ) );
		} else{
			$remaining_time_text = __( 'now', 'blocked-in-china' );
		}

		printf(
		    __( 'Your next status update is available <span id="api_countdown">%1$s</span>.', 'blocked-in-china' ),
		    $remaining_time_text
		);
		?>
	</p>
	<p>
		<?php
		printf(
		    __( 'To get more frequent Status Updates, %1$s.', 'blocked-in-china' ),
		    '<a href="' . esc_url( $bic_fs->get_upgrade_url() ) . '">' . __( 'Upgrade here', 'blocked-in-china' ) . ' ></a>'
		);
		?>
	</p>

	<p>
		<?php

		printf( /* Translators: %1$s: Documentation link, %2$s: Blocked In China website */
			__( 'Learn more about our plugin in our %1$s or by %2$s.', 'blocked-in-china' ),
			'<a href="'. esc_url( 'https://docs.blockedinchina.io' ) .'" target="_blank">' . __( 'Documentation', 'blocked-in-china' ) . '</a>',
			'<a href="'. esc_url( 'https://blockedinchina.io' ) .'" target="_blank">' . __( 'visiting our site', 'blocked-in-china' ) . '</a>'
		);
		?>

	</p>
	
	<p>
		<label>
				<input id="bic_status_bar" type="checkbox" name="bic_status" <?php echo get_option('bic_admin_bar_status') ? 'checked' : ''; ?>> <?php _e( 'Enable WP Admin bar status', 'blocked-in-china' )?>
		</label>
	</p>

	<tr>
    <th scope="row">
        <label for="bic_disable_google_fonts"><?php _e('Disable Google Fonts', 'blocked-in-china'); ?></label>
    </th>
    <td>
        <input type="checkbox" id="bic_disable_google_fonts" name="bic_disable_google_fonts" value="1" <?php checked(1, get_option('bic_disable_google_fonts', 0)); ?> />
        <p class="description"><?php _e('Check this option to disable loading Google Fonts on your site.', 'blocked-in-china'); ?></p>
    </td>
</tr>

<script type="text/javascript">
    document.getElementById('bic_disable_google_fonts').addEventListener('change', function() {
        var isChecked = this.checked ? 1 : 0;

        var data = {
            'action': 'bic_toggle_google_fonts',
            'disable_google_fonts': isChecked,
            'security': '<?php echo wp_create_nonce("bic_google_fonts_nonce"); ?>'
        };

        jQuery.post(ajaxurl, data, function(response) {
            if (response.success) {
                alert(response.data.message);
            } else {
                alert('Error: ' + response.data.message);
            }
        });
    });
</script>


	<?php 
	$bic_manual_api_run_last = get_option( 'bic_manual_api_run_last' );
	
	$option_prefix = wp_date( 'Y-m-d H:i', strtotime( $bic_manual_api_run_last ), new DateTimeZone('UTC') );
	$manual_log = get_option( 'bic_cron_log' .'-'. $option_prefix );
	$class_name = '';
	if( $bic_manual_api_run_last && isset( $manual_log ) && !empty( $manual_log ) ){
		$class_name = $manual_log['data']['summary']['result'] == "visible" ? 'notice-success' : 'notice-error' ;
	}
	?>
	<div id="gpbic_form_response" class="bic-notice <?php echo esc_attr($class_name);?>" style="display: <?php echo $remaining_gap ? 'block' : 'none';?>;" >
		<?php 
		if(isset($manual_log)){
			printf(
				'<p>%s</p>',
				isset($manual_log['data']['summary']['description']) ? $manual_log['data']['summary']['description'] : ''
			);
		}
		?>
	</div>

	<table id="gpbic_form_result" class="wp-list-table widefat fixed striped" style="display: <?php echo $remaining_gap ? 'table' : 'none';?>;">
		<thead>
			<tr>
				<th width="30%"><?php _e( 'Location', 'blocked-in-china' ); ?></th>
				<th width="50%"><?php _e( 'Lookup Result', 'blocked-in-china' ); ?></th>
				<th width="20%"><?php _e( 'Status', 'blocked-in-china' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			if(isset($manual_log['data']['servers'])){
				foreach ($manual_log['data']['servers'] as $server) {
					printf('<tr>');
					printf('<td>%s</td>', esc_html($server['location']));
					printf('<td>%s</td>', esc_html($server['resultvalue']));
					printf('<td>%s</td>', $server['resultstatus'] == 'ok' ? '<span class="dashicons dashicons-yes-alt"></span>' : '<span class="dashicons dashicons-dismiss"></span>');
					printf('</tr>');
				}
			}else{
				echo '<tr><td colspan="3">' . esc_html__( 'No result found!', 'blocked-in-china' ) . '</td></tr>';
			}
			
			?>
		</tbody>
	</table>
    <?php require_once GPBIC_PLUGIN_PATH . '/libs/features-api-integration-library/templates/features.php'; ?>
</div>