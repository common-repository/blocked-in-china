<div class="features-container">
	<div id="features_loading">Loading features...</div>
	<div id="gpbic_feature_alert"></div>
	<h3 id="feature-title"></h3>
	<p id="feature-description"></p>
	<form name="bic_fetures" id="bic_featuers" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>" method="POST">
		<?php 
			wp_nonce_field( 'bic_features', 'bic_features_nonce' );
		?>
		<input type="hidden" name="action" value="fil_features_contact">
		<input type="hidden" name="api_url" value="<?php _e($_GET["page"]);?>">
		<input type="hidden" name="area_slug" value="<?php _e( empty($_GET['tab']) ? 'status' : $_GET['tab'], 'blocked-in-china' )?>">
		<ul class="bic-features"></ul>

		<div id="features-fields" style="display:none;">
            <div class="field-group">
                <div class="fname-field">
                	<label><?php _e('First Name','blocked-in-china');?></label>
	                <br />
	                <input type="text" name="fname" value="" required />
                </div>
            	<div class="lname-field">
                	<label><?php _e('Last Name','blocked-in-china');?></label>
	                <br />
	                <input type="text" name="lname" value="" required />
                </div>
            </div>
            <div class="field-group">
            	<div class="email-field">
            		<label><?php _e('Email Address','blocked-in-china');?></label>
	                <br />
	                <input type="email" name="email" value="" required />
            	</div>
            </div>
            <div class="field-group" id="message-field-group" style="display: none;">
            	<div class="message-field">
            		<label><?php _e('Message','blocked-in-china');?></label>
	                <br />
	                <textarea name="message" id="message"></textarea>
            	</div>
            </div>
            <p class="submit">
	            <button type="submit" name="get_notified" id="get_notified" class="button button-primary" ></button>
				<span class="spinner"></span>
	        </p>

        </div>
        
	</form>
</div>