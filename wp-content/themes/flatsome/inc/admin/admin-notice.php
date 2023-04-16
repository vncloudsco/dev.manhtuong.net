<?php

add_action( 'admin_notices', 'flatsome_maintenance_admin_notice' );

function flatsome_maintenance_admin_notice() {
	$screen       = get_current_screen();
	$advanced_url = get_admin_url() . 'admin.php?page=optionsframework&tab=';
	$errors       = flatsome_envato()->registration->get_errors();

	if ( get_theme_mod( 'maintenance_mode', 0 ) && get_theme_mod( 'maintenance_mode_admin_notice', 1 ) ) {
		?>
		<div class="notice notice-info">
				<p><?php echo sprintf( __( 'Flatsome Maintenance Mode is <strong>active</strong>. Please don\'t forget to <a href="%s">deactivate</a> it as soon as you are done.', 'flatsome-admin' ), $advanced_url . 'of-option-maintenancemode' ); ?></p>
		</div>
		<?php
	}
}
