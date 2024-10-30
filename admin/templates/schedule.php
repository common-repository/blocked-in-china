<?php  global $bic_fs; ?>
<?php 
	$is_freemius_premium = false;
	$show = [];

	if ( $bic_fs->is_plan( 'personal', true ) ) {
		$is_freemius_premium = true;
		$show = [ 'monthly', 'weekly' ];
	} else if ( $bic_fs->is_plan( 'pro', true ) ) {
		$is_freemius_premium = true;
		$show = [ 'monthly', 'weekly', 'daily' ];
	} else if ( $bic_fs->is_plan( 'agency', true ) ) {
		$is_freemius_premium = true;
		$show = [ 'monthly', 'weekly', 'daily', 'hourly' ];
	}
?>
<div class="wrap">
	<h1><?php _e( 'Blocked in China', 'blocked-in-china' ); ?></h1>

	<nav class="nav-tab-wrapper">
		<a href="?page=blocked-in-china" class="nav-tab "><?php _e( 'Status', 'blocked-in-china' ); ?></a>
		<a href="?page=blocked-in-china&tab=schedule" class="nav-tab nav-tab-active"><?php _e( 'Schedule', 'blocked-in-china' ); ?></a>
		<a href="?page=blocked-in-china&tab=log" class="nav-tab"><?php _e( 'Log', 'blocked-in-china' ); ?></a>
		<a href="?page=blocked-in-china&tab=bundle" class="nav-tab"><?php _e( 'China Plugins Bundle <span class="dashicons dashicons-star-filled bic-yellow"></span>', 'blocked-in-china' ); ?></a>
	</nav>
	<h2><?php _e( 'Schedule settings', 'blocked-in-china' ); ?></h2>
	
	<?php if( !$is_freemius_premium ){ ?>
		<p>
			<?php
			printf(
			    __( 'To schedule status updates, %1$s.', 'blocked-in-china' ),
			    '<a href="' . esc_url( $bic_fs->get_upgrade_url() ) . '">' . __( 'Upgrade here', 'blocked-in-china' ) . ' ></a>'
			);
			?>
		</p>
	<?php } ?>
	
	<div id="gpbic_form_response"></div>

	<form id="gpbic_form" method="POST" novalidate="novalidate">
		<input type="hidden" id="form_action" name="action" value="gpbic_run_api" />
		<?php wp_nonce_field( 'gpbic_nonce' ); ?>

		<?php $is_cron_on = get_option('bic_cron_on');?>
		<table>
			<tr>
				<td>
					<label>
						<input id="bic_cron_on" type="checkbox" name="bic_cron_on" <?php echo $is_cron_on ? 'checked' : ''; ?> <?php echo $is_freemius_premium ? '' : 'disabled'; ?>> <?php _e( 'Schedule Status Updates', 'blocked-in-china' )?>
					</label>
				</td>
				<td>
					<?php 
					$schedules = wp_get_schedules();
					
					$bic_schedules = array(
						'monthly' => $schedules['monthly'],
						'weekly' => $schedules['weekly'],
						'daily' => $schedules['daily'],
						'hourly' => $schedules['hourly'],
					);

					$intervals = array_column($bic_schedules, 'interval');
					array_multisort($intervals, SORT_DESC, $bic_schedules);
					$current_selection = !empty(get_option( 'bic_cron_schedule' )) ? get_option( 'bic_cron_schedule' ) : 'monthly';
					if( isset( $bic_schedules ) ){

						printf( '<select id="bic_cron_frequency" name="frequency" %s>', $is_freemius_premium ? '' : 'disabled' );
						foreach ($bic_schedules as $key => $schedule) {

							printf(
								'<option value="%s" %s %s>%s</option>',
								$key,
								$current_selection == $key ? 'selected' : '',
								!in_array( $key, $show ) ? 'disabled' : '',
								esc_html( str_replace( "Once ", "", $schedule['display'] ) )
							);


						}
						printf( '</select>' );
					}?>
				</td>
			</tr>
		</table>
		
		<p class="submit">
			<button type="submit" name="submit" id="submit" class="button button-primary" <?php echo $is_freemius_premium ? '' : 'disabled'; ?>><?php echo esc_html( __( 'Save Changes', 'blocked-in-china' ) ); ?></button>
			<span class="spinner"></span>
		</p>
	</form>

	<!--Features-->
	<?php require_once GPBIC_PLUGIN_PATH . '/libs/features-api-integration-library/templates/features.php'; ?>
</div>