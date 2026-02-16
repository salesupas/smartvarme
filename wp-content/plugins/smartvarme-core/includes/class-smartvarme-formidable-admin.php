<?php
/**
 * Formidable Forms Admin Interface
 *
 * Provides admin interface for managing Formidable Forms settings
 * including placeholder automation.
 *
 * @package Smartvarme_Core
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Smartvarme_Formidable_Admin {

	/**
	 * Constructor - register hooks
	 */
	public function __construct() {
		// Only run if Formidable Forms is active
		if ( ! class_exists( 'FrmForm' ) ) {
			return;
		}

		// Add admin menu
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		// Handle form submission
		add_action( 'admin_post_smartvarme_update_placeholders', array( $this, 'handle_update_placeholders' ) );
	}

	/**
	 * Add admin menu page
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'formidable',
			'Placeholder Setup',
			'Placeholder Setup',
			'manage_options',
			'smartvarme-formidable-placeholders',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Render admin page
	 */
	public function render_admin_page() {
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Get update status if redirected after update
		$update_status = get_transient( 'smartvarme_placeholder_update_status' );
		if ( $update_status ) {
			delete_transient( 'smartvarme_placeholder_update_status' );
		}

		?>
		<div class="wrap">
			<h1>Formidable Forms - Placeholder Setup</h1>

			<div class="card" style="max-width: 800px; margin-top: 20px;">
				<h2>Automatisk Placeholder-Funksjonalitet</h2>
				<p>
					<strong>Status:</strong> <span style="color: green;">✓ Aktivert</span>
				</p>
				<p>
					Alle nye felt og oppdaterte felt vil automatisk få label som placeholder-tekst
					hvis placeholder er tom.
				</p>
			</div>

			<div class="card" style="max-width: 800px; margin-top: 20px;">
				<h2>Oppdater Eksisterende Skjemaer</h2>
				<p>
					Klikk på knappen nedenfor for å oppdatere alle eksisterende skjemaer som
					mangler placeholder-tekst. Dette vil sette label som placeholder for alle
					feltene som ikke har placeholder.
				</p>

				<?php if ( $update_status ) : ?>
					<div class="notice notice-<?php echo esc_attr( $update_status['type'] ); ?> inline">
						<p><?php echo esc_html( $update_status['message'] ); ?></p>
						<?php if ( ! empty( $update_status['updated_fields'] ) ) : ?>
							<details style="margin-top: 10px;">
								<summary style="cursor: pointer;">
									<strong>Se oppdaterte felt (<?php echo count( $update_status['updated_fields'] ); ?>)</strong>
								</summary>
								<ul style="margin-top: 10px; padding-left: 20px;">
									<?php foreach ( $update_status['updated_fields'] as $field ) : ?>
										<li>
											<strong><?php echo esc_html( $field['name'] ); ?></strong>
											(ID: <?php echo esc_html( $field['id'] ); ?>,
											Type: <?php echo esc_html( $field['type'] ); ?>)
										</li>
									<?php endforeach; ?>
								</ul>
							</details>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
					  onsubmit="return confirm('Er du sikker på at du vil oppdatere alle skjemaer med placeholders?');">
					<?php wp_nonce_field( 'smartvarme_update_placeholders' ); ?>
					<input type="hidden" name="action" value="smartvarme_update_placeholders">

					<p>
						<button type="submit" class="button button-primary button-hero">
							Oppdater Alle Skjemaer
						</button>
					</p>
				</form>
			</div>

			<div class="card" style="max-width: 800px; margin-top: 20px;">
				<h2>Hvordan det fungerer</h2>
				<ul style="padding-left: 20px;">
					<li>Automatisk oppdatering av nye felt når de opprettes eller redigeres</li>
					<li>Støtter alle tekstinput-typer (text, email, textarea, osv.)</li>
					<li>Beholder eksisterende placeholder-tekst hvis den finnes</li>
					<li>Fungerer på alle Formidable Forms på nettstedet</li>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle placeholder update form submission
	 */
	public function handle_update_placeholders() {
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		// Verify nonce
		check_admin_referer( 'smartvarme_update_placeholders' );

		// Load placeholder class
		require_once SMARTVARME_CORE_PATH . 'includes/class-smartvarme-formidable-placeholders.php';

		// Run update
		$result = Smartvarme_Formidable_Placeholders::update_existing_forms();

		// Store result in transient for display
		$status = array(
			'type'           => $result['success'] ? 'success' : 'error',
			'message'        => $result['message'],
			'updated_fields' => isset( $result['updated_fields'] ) ? $result['updated_fields'] : array(),
		);
		set_transient( 'smartvarme_placeholder_update_status', $status, 60 );

		// Redirect back to admin page
		wp_safe_redirect( admin_url( 'admin.php?page=smartvarme-formidable-placeholders' ) );
		exit;
	}
}
