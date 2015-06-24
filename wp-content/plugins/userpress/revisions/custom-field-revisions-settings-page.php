<div class="wrap">
	<div id="icon-options-general" class="icon32"></div>  
	<h2><?php echo self::PLUGIN_DISPLAY_NAME;?> Settings</h2>

	<form action="options.php" method="post">
		<?php settings_fields( self::PLUGIN_OPTIONS_PAGE ); ?>
		<?php do_settings_sections( self::PLUGIN_OPTIONS_PAGE ); ?>

		<?php submit_button(); ?>
	</form>
</div>
