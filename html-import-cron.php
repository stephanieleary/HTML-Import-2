<?php
// set up scheduled jobs
register_activation_hook( dirname ( __FILE__ )."/html-import.php", 'html_import_cron_activate' );
register_deactivation_hook( dirname ( __FILE__ )."/html-import.php", 'html_import_cron_deactivate' );
//add_action( 'html_import_schedule', 'html_import_cron_run' );

function html_import_cron_activate() {
	$options = html_import_get_options();
	
	if ( '1' == $options['cron'] && !wp_next_scheduled( 'html_import_schedule' ) ) {
		wp_schedule_event( time(), 'daily', 'html_import_schedule' );
	}
}

function html_import_cron_deactivate() {
	wp_clear_scheduled_hook( 'html_import_schedule' );
}



add_action('html_import_schedule', "html_import_cron_run_2" );
function html_import_cron_run_2() {
	require_once ABSPATH . 'wp-admin/includes/import.php';

	if ( !class_exists( 'WP_Importer' ) ) {
		$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
		if ( file_exists( $class_wp_importer ) )
			require_once $class_wp_importer;
	}
	if ( !class_exists( 'HTML_Import' ) ) {
		$class_html_importer = plugin_dir_path( __FILE__ ) . 'html-importer.php';
		if ( file_exists( $class_html_importer ) )
			require_once $class_html_importer;
	}
	
    $html_import = new HTML_Import();
    $html_import->cron_import();
}


//require_once ( 'html-importer.php' );
//$html_import = new HTML_Import();
add_action( 'html_import_schedule', 'html_import_cron_run' );
//add_action( 'html_import_schedule', array( $html_import, 'cron_import' ) );

function html_import_cron_run() {
	
	// set URL to the import screen
	$params = array(
	        'import' => 'html',
			'step'	 => '3',
	);
	$url = add_query_arg( $params, admin_url( 'admin.php' ) );
	
	$options = html_import_get_options();
	$user = get_userdata( $options['cron_user'] );
	if ( !$user ) {
		error_log( 'Cron HTML Import failed: No user ID set.' );
		wp_die( 'Cron HTML Import failed: No user ID set.' );
	}	
	
	/*
	// REMOTE POST TO IMPORT URL
	
	// set up form fields and submitted values
	$fields = array(
	    'import_files' => 'directory',
	    'action' => 'save',
		'_wpnonce' => wp_create_nonce( 'html-import' ),
		'username' => $user->user_login, 
		'password' => $user->user_pass,
	);
	
	$args = array(
		'method' => 'POST',
		'timeout' => 45,
		'httpversion' => '1.0',
		'sslverify' => false,
		'headers' => array(
			'Authorization' => 'Basic ' . base64_encode( $user->user_login . ':' . $user->user_pass )
		),
		'body' => $fields,
	);
	
	 
	$response = wp_remote_post( $url, $args );

	if ( is_wp_error( $response ) && true === WP_DEBUG ) {
		error_log( $response->get_error_message() );
		wp_die( $response->get_error_message() );
	} else {
		error_log( 'HTML Import complete. ' . print_r( $response, true ) );
		wp_die( 'HTML Import complete. ' . print_r( $response, true ) );
	}
	/**/
}