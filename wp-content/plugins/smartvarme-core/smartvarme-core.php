<?php
/**
 * Plugin Name: Smartvarme Core
 * Plugin URI: https://smartvarme.no
 * Description: Core business logic for Smartvarme site
 * Version: 1.0.0
 * Author: Smartvarme Team
 * Author URI: https://smartvarme.no
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: smartvarme-core
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Plugin version (for cache busting)
define( 'SMARTVARME_CORE_VERSION', '1.0.0' );

// Plugin path
define( 'SMARTVARME_CORE_PATH', plugin_dir_path( __FILE__ ) );

// Plugin URL
define( 'SMARTVARME_CORE_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_smartvarme_core() {
	require_once SMARTVARME_CORE_PATH . 'includes/class-smartvarme-core-activator.php';
	Smartvarme_Core_Activator::activate();
}
register_activation_hook( __FILE__, 'activate_smartvarme_core' );

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_smartvarme_core() {
	require_once SMARTVARME_CORE_PATH . 'includes/class-smartvarme-core-deactivator.php';
	Smartvarme_Core_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_smartvarme_core' );

/**
 * Begin plugin execution.
 */
function run_smartvarme_core() {
	require_once SMARTVARME_CORE_PATH . 'includes/class-smartvarme-core.php';
	$plugin = new Smartvarme_Core();
	$plugin->run();
}
run_smartvarme_core();

/**
 * Load custom blocks
 */
function smartvarme_load_custom_blocks() {
	$blocks_dir = plugin_dir_path( __FILE__ ) . 'blocks/';

	// Load product comparison block
	if ( file_exists( $blocks_dir . 'product-comparison/index.php' ) ) {
		require_once $blocks_dir . 'product-comparison/index.php';
	}

	// Load energy calculator block
	if ( file_exists( $blocks_dir . 'energy-calculator/index.php' ) ) {
		require_once $blocks_dir . 'energy-calculator/index.php';
	}
}
add_action( 'plugins_loaded', 'smartvarme_load_custom_blocks' );
