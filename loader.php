<?php
/*
Plugin Name: Editor Clean Copy-Paste
Description: Remove unwanted formatting when pasting from Word
Version: 1.0.0
Text Domain: editor-clean-paste
*/

// Define Constants

if ( ! defined( 'ECCP_VERSION' ) ) {
	define( 'ECCP_VERSION', '1.0.0' );
}

// Plugin Folder Path
if ( ! defined( 'ECCP_PATH' ) ) {
	define( 'ECCP_PATH', plugin_dir_path( __FILE__ ) );
}

// Plugin Folder URL
if ( ! defined( 'ECCP_URL' ) ) {
	define( 'ECCP_URL', plugin_dir_url( __FILE__ ) );
}

// Plugin Root File
if ( ! defined( 'ECCP_FILE' ) ) {
	define( 'ECCP_FILE', __FILE__ );
}

require_once ECCP_PATH . 'inc/Plugin.php';
new \ECCP\Plugin();
