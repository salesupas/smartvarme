<?php
/**
 * Main plugin class
 *
 * @package Smartvarme_Core
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Main Smartvarme_Core class
 */
class Smartvarme_Core {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Placeholder for future hooks/filters
	}

	/**
	 * Run the plugin
	 */
	public function run() {
		// Load performance module early
		$this->load_performance_module();

		// Initialize FAQ custom post type
		require_once SMARTVARME_CORE_PATH . 'includes/class-smartvarme-faq-cpt.php';
		new Smartvarme_FAQ_CPT();

		// Load FAQ schema functionality
		$this->load_faq_schema();

		// Load Formidable Forms placeholders automation
		$this->load_formidable_placeholders();

		// Load Image Importer
		$this->load_image_importer();

		// Load WooCommerce integration on plugins_loaded to ensure hooks are ready
		add_action( 'plugins_loaded', array( $this, 'load_woocommerce_integration' ), 20 );
	}

	/**
	 * Load performance module
	 */
	private function load_performance_module() {
		require_once SMARTVARME_CORE_PATH . 'includes/class-smartvarme-performance.php';
		new Smartvarme_Performance();
	}

	/**
	 * Load FAQ schema class
	 */
	private function load_faq_schema() {
		require_once SMARTVARME_CORE_PATH . 'includes/class-smartvarme-faq-schema.php';
		new Smartvarme_FAQ_Schema();
	}

	/**
	 * Load Formidable Forms placeholders automation
	 */
	private function load_formidable_placeholders() {
		// Only load if Formidable Forms is active
		if ( ! class_exists( 'FrmForm' ) ) {
			return;
		}

		require_once SMARTVARME_CORE_PATH . 'includes/class-smartvarme-formidable-placeholders.php';
		new Smartvarme_Formidable_Placeholders();

		// Load admin interface
		if ( is_admin() ) {
			require_once SMARTVARME_CORE_PATH . 'includes/class-smartvarme-formidable-admin.php';
			new Smartvarme_Formidable_Admin();
		}
	}

	/**
	 * Load Image Importer
	 */
	private function load_image_importer() {
		// Load admin interface
		if ( is_admin() ) {
			require_once SMARTVARME_CORE_PATH . 'includes/class-smartvarme-image-importer-admin.php';
			new Smartvarme_Image_Importer_Admin();
		}
	}

	/**
	 * Load WooCommerce integration
	 */
	public function load_woocommerce_integration() {
		// Only load if WooCommerce is active
		if ( class_exists( 'WooCommerce' ) ) {
			require_once SMARTVARME_CORE_PATH . 'includes/class-smartvarme-woocommerce.php';
			new Smartvarme_WooCommerce();
		}
	}
}
