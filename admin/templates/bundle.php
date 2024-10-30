<?php  global $bic_fs; ?>
<div class="wrap">
	<h1><?php _e( 'Blocked in China', 'blocked-in-china' ); ?></h1>

	<nav class="nav-tab-wrapper">
		<a href="?page=blocked-in-china" class="nav-tab "><?php _e( 'Status', 'blocked-in-china' ); ?></a>
		<a href="?page=blocked-in-china&tab=schedule" class="nav-tab"><?php _e( 'Schedule', 'blocked-in-china' ); ?></a>
		<a href="?page=blocked-in-china&tab=log" class="nav-tab"><?php _e( 'Log', 'blocked-in-china' ); ?></a>
		<a href="?page=blocked-in-china&tab=bundle" class="nav-tab nav-tab-active"><?php _e( 'China Plugins Bundle <span class="dashicons dashicons-star-filled bic-yellow"></span>', 'blocked-in-china' ); ?></a>
	</nav>
	<table class="form-table china-plugins-bundle" style="width: 1200px;" align="center">
		<tr>
			<td>
				<div class="plugin-block">
					<center>
						<img width="50" height="50" src="<?php echo GPBIC_PLUGIN_URL . 'assets/images/icon-payment.png' ?>">
					</center>
					<h2><?php _e( 'China Payments Plugin', 'blocked-in-china' ); ?></h2>
					<p><?php _e( 'Accept WeChat Pay and Alipay payments from Chinese customers.', 'blocked-in-china' ); ?></p>
					<hr/>
					<center>

						<?php 
						$cpp_plugin_url = add_query_arg(
							urlencode_deep(
								array(
								    's' => 'China Payments Plugin',
								    'tab' => 'search',
								    'type' => 'term'
								)
							),
							admin_url() . 'plugin-install.php'
						);
						?>
						<a href="https://chinapaymentsplugin.com" target="_blank" class="button"><?php _e( 'Learn more', 'blocked-in-china' ); ?></a>
						<a href="<?php echo esc_url( $cpp_plugin_url ); ?>" class="button button-primary"><?php _e( 'Install Free', 'blocked-in-china' ); ?></a>
					</center>
				</div>
			</td>
			<td>
				<div class="plugin-block">
					<center>
						<img width="50" height="50" src="<?php echo GPBIC_PLUGIN_URL . 'assets/images/icon-sic.png' ?>">
					</center>
					<h2><?php _e( 'Speed in China', 'blocked-in-china' ); ?></h2>
					<p><?php _e( 'Test your site\'s speed in the Chinese mainland.', 'blocked-in-china' ); ?></p>
					<hr/>
					<center>
						<?php 
						$sic_plugin_url = add_query_arg(
							urlencode_deep(
								array(
								    's' => 'Speed in China',
								    'tab' => 'search',
								    'type' => 'term'
								)
							),
							admin_url() . 'plugin-install.php'
						);
						?>
						<a href="https://speedinchina.io" target="_blank" class="button"><?php _e( 'Learn more', 'blocked-in-china' ); ?></a>
						<a href="<?php echo esc_url( $sic_plugin_url ); ?>" class="button button-primary"><?php _e( 'Install Free', 'blocked-in-china' ); ?></a>
					</center>
				</div>
			</td>
			<td>
				<div class="plugin-block">
					<center>
						<img width="50" height="50" src="<?php echo GPBIC_PLUGIN_URL . 'assets/images/icon-bic.png' ?>">
					</center>
					<h2><?php _e( 'Blocked in China', 'blocked-in-china' ); ?></h2>
					<p><?php _e( 'Check if your site is available from mainland China servers.', 'blocked-in-china' ); ?></p>
					<hr/>
					<center>
						<?php 
						$sic_plugin_url = add_query_arg(
							urlencode_deep(
								array(
								    's' => 'Blocked in China',
								    'tab' => 'search',
								    'type' => 'term'
								)
							),
							admin_url() . 'plugin-install.php'
						);
						?>
						<a href="https://blockedinchina.io" target="_blank" class="button"><?php _e( 'Learn more', 'blocked-in-china' ); ?></a>
						<a href="<?php echo esc_url( $sic_plugin_url ); ?>" class="button button-primary"><?php _e( 'Install Free', 'blocked-in-china' ); ?></a>
					</center>
				</div>
			</td>
		</tr>
		<tr><td colspan="3"></td></tr>
		<tr>
			<td colspan="3" style="padding-bottom: 0;">
				<h2 style="margin-bottom: 0;"><?php _e( 'Save 35% with China Plugins Bundle <span class="dashicons dashicons-star-filled bic-yellow"></span>', 'blocked-in-china' ); ?></h2>
			</td>
		</tr>
		<tr>
			<td colspan="3"  style="padding-top: 0;">
				<p><?php _e( 'Get Blocked in China, Speed in China, and China Payments plugin bundled in one low-price.', 'blocked-in-china' ); ?></p>
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<center>
					<a href="https://chinaplugins.com" target="_blank" class="button"><?php _e( 'Learn more', 'blocked-in-china' ); ?></a>
					<!-- <a href="<?php echo esc_url( $bic_fs->get_upgrade_url() ); ?>" class="button button-primary"><?php _e( 'Buy now', 'blocked-in-china' ); ?></a> -->
				</center>
			</td>
		</tr>
	</table>

	<!--Features-->
	<?php
		require_once GPBIC_PLUGIN_PATH . '/libs/features-api-integration-library/templates/features.php';
	?>
	
</div>