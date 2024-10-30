<?php  global $bic_fs; ?>
<div class="wrap">
	<h1><?php _e( 'Blocked in China', 'blocked-in-china' ); ?></h1>

	<nav class="nav-tab-wrapper">
		<a href="?page=blocked-in-china" class="nav-tab "><?php _e( 'Status', 'blocked-in-china' ); ?></a>
		<a href="?page=blocked-in-china&tab=schedule" class="nav-tab"><?php _e( 'Schedule', 'blocked-in-china' ); ?></a>
		<a href="?page=blocked-in-china&tab=log" class="nav-tab nav-tab-active"><?php _e( 'Log', 'blocked-in-china' ); ?></a>
		<a href="?page=blocked-in-china&tab=bundle" class="nav-tab"><?php _e( 'China Plugins Bundle <span class="dashicons dashicons-star-filled bic-yellow"></span>', 'blocked-in-china' ); ?></a>
	</nav>
	<h2><?php _e( 'Log History', 'blocked-in-china' ); ?></h2>

	<p><?php _e( 'You can see all your API call log history.', 'blocked-in-china' ); ?></p>
	<div id="gpbic_form_response"></div>
	<?php 
		global $wpdb;
		$query = $wpdb->prepare(
			"SELECT * FROM $wpdb->options WHERE `option_name` LIKE %s",
			'bic_cron_log-%'
		);
		$logs = $wpdb->get_results($query);
		
		if( isset($logs) ): 
			printf(
				'<label>%s: </label>',
				__( 'Filter log', 'blocked-in-china' )
			);
			printf( '<select id="bic-filter-logs">' );
				printf(
					'<option value="0">--%s--</option>',
					__( 'Select', 'blocked-in-china' )
				);
				foreach ($logs as $key => $log) {

					$label = str_replace("bic_cron_", "", $log->option_name) . " UTC";
					echo sprintf(
						'<option value="%1$s">%2$s</option>',
						esc_attr($log->option_id),
						esc_attr($label)
					);
				} 
			printf( '</select>' );
		endif; 
	?>
	<hr/>
	<table id="gpbic_form_result" class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th width="30%"><?php _e( 'Location', 'blocked-in-china' ); ?></th>
				<th width="50%"><?php _e( 'Lookup Result', 'blocked-in-china' ); ?></th>
				<th width="20%"><?php _e( 'Status', 'blocked-in-china' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="3"><?php _e( 'Select option to see the results.', 'blocked-in-china' ); ?></td>
			</tr>
		</tbody>
	</table>

	<!--Features-->
	<?php require_once GPBIC_PLUGIN_PATH . '/libs/features-api-integration-library/templates/features.php'; ?>
	
</div>