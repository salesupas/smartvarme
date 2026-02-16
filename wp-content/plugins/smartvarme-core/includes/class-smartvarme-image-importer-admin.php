<?php
/**
 * Image Importer Admin Interface
 *
 * Provides admin interface for importing missing images from live site
 *
 * @package Smartvarme_Core
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Smartvarme_Image_Importer_Admin {

	/**
	 * Constructor - register hooks
	 */
	public function __construct() {
		// Add admin menu
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		// Handle form submissions
		add_action( 'admin_post_smartvarme_scan_images', array( $this, 'handle_scan_images' ) );
		add_action( 'admin_post_smartvarme_import_images', array( $this, 'handle_import_images' ) );
		add_action( 'admin_post_smartvarme_import_product_images', array( $this, 'handle_import_product_images' ) );
		add_action( 'admin_post_smartvarme_fix_product_gallery', array( $this, 'handle_fix_product_gallery' ) );
	}

	/**
	 * Add admin menu page
	 */
	public function add_admin_menu() {
		add_management_page(
			'Import Bilder',
			'Import Bilder',
			'manage_options',
			'smartvarme-image-importer',
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

		// Get scan/import results if available
		$scan_results = get_transient( 'smartvarme_image_scan_results' );
		$import_results = get_transient( 'smartvarme_image_import_results' );
		$product_import_result = get_transient( 'smartvarme_product_image_import_result' );

		if ( $scan_results ) {
			delete_transient( 'smartvarme_image_scan_results' );
		}
		if ( $import_results ) {
			delete_transient( 'smartvarme_image_import_results' );
		}
		if ( $product_import_result ) {
			delete_transient( 'smartvarme_product_image_import_result' );
		}

		?>
		<div class="wrap">
			<h1>🖼️ Import Bilder fra Live Site</h1>

			<?php if ( $product_import_result ) : ?>
				<div class="notice notice-<?php echo $product_import_result['success'] ? 'success' : 'error'; ?> is-dismissible" style="padding: 20px; margin-top: 20px;">
					<h2 style="margin-top: 0;">🧪 Test Resultat</h2>
					<p style="font-size: 16px;">
						<strong>Produkt:</strong> <?php echo esc_html( $product_import_result['product_name'] ); ?><br>
						<strong>Status:</strong> <?php echo esc_html( $product_import_result['message'] ); ?>
					</p>

					<?php if ( isset( $product_import_result['product_url'] ) ) : ?>
						<p>
							<a href="<?php echo esc_url( $product_import_result['product_url'] ); ?>" target="_blank">
								Se produkt på live-siten →
							</a>
						</p>
					<?php endif; ?>

					<table class="widefat" style="margin-top: 15px; max-width: 600px;">
						<thead>
							<tr>
								<th>Bildetype</th>
								<th>Status</th>
								<th>Detaljer</th>
							</tr>
						</thead>
						<tbody>
							<?php if ( isset( $product_import_result['featured'] ) ) : ?>
								<tr>
									<td><strong>Featured Image</strong></td>
									<td>
										<?php if ( isset( $product_import_result['featured']['imported'] ) && $product_import_result['featured']['imported'] ) : ?>
											<span style="color: green;">✅ Importert</span>
										<?php elseif ( isset( $product_import_result['featured']['error'] ) ) : ?>
											<span style="color: red;">❌ Feilet</span>
										<?php else : ?>
											<span style="color: gray;">ℹ️ Ikke funnet</span>
										<?php endif; ?>
									</td>
									<td>
										<?php if ( isset( $product_import_result['featured']['url'] ) ) : ?>
											<small><?php echo esc_html( basename( $product_import_result['featured']['url'] ) ); ?></small>
										<?php endif; ?>
										<?php if ( isset( $product_import_result['featured']['error'] ) ) : ?>
											<br><small style="color: red;"><?php echo esc_html( $product_import_result['featured']['error'] ); ?></small>
										<?php endif; ?>
									</td>
								</tr>
							<?php endif; ?>

							<?php if ( ! empty( $product_import_result['gallery'] ) ) : ?>
								<?php foreach ( $product_import_result['gallery'] as $idx => $gallery_img ) : ?>
									<tr>
										<td><strong>Gallery #<?php echo ( $idx + 1 ); ?></strong></td>
										<td>
											<?php if ( isset( $gallery_img['imported'] ) && $gallery_img['imported'] ) : ?>
												<span style="color: green;">✅ Importert</span>
											<?php elseif ( isset( $gallery_img['error'] ) ) : ?>
												<span style="color: red;">❌ Feilet</span>
											<?php else : ?>
												<span style="color: gray;">ℹ️ Ikke importert</span>
											<?php endif; ?>
										</td>
										<td>
											<?php if ( isset( $gallery_img['url'] ) ) : ?>
												<small><?php echo esc_html( basename( $gallery_img['url'] ) ); ?></small>
											<?php endif; ?>
											<?php if ( isset( $gallery_img['error'] ) ) : ?>
												<br><small style="color: red;"><?php echo esc_html( $gallery_img['error'] ); ?></small>
											<?php endif; ?>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php else : ?>
								<tr>
									<td colspan="3"><em>Ingen gallery-bilder funnet</em></td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>

					<p style="margin-top: 20px;">
						<a href="<?php echo get_edit_post_link( isset( $_GET['product_id'] ) ? intval( $_GET['product_id'] ) : 0 ); ?>" class="button button-secondary">
							Se produktet i admin →
						</a>
					</p>
				</div>
			<?php endif; ?>

			<div class="card" style="max-width: 800px; margin-top: 20px;">
				<h2>Scan etter Manglende Bilder</h2>
				<p>
					Skanner innhold, produkter og sider for bilder som mangler i media library.
					Bildene kan deretter importeres fra <strong>smartvarme.no</strong>.
				</p>

				<?php if ( $scan_results ) : ?>
					<div class="notice notice-info inline">
						<p>
							<strong>Scan fullført!</strong><br>
							Funnet <strong><?php echo esc_html( $scan_results['total_found'] ); ?></strong> manglende bilder.
						</p>
					</div>

					<?php
					// Group images by type
					$content_images = array();
					$product_images = array();

					if ( ! empty( $scan_results['missing_images'] ) ) {
						$content_images = array_filter( $scan_results['missing_images'], function( $img ) {
							return isset( $img['image_type'] ) && $img['image_type'] === 'content';
						} );
						$product_images = array_filter( $scan_results['missing_images'], function( $img ) {
							return isset( $img['image_type'] ) && in_array( $img['image_type'], array( 'featured', 'gallery' ), true );
						} );
					}
					?>

					<?php if ( ! empty( $scan_results['missing_images'] ) ) : ?>

						<?php if ( ! empty( $content_images ) ) : ?>
							<details style="margin-top: 20px;" open>
								<summary style="cursor: pointer; font-weight: 600; padding: 10px; background: #e3f2fd;">
									📄 Innholdsbilder (<?php echo count( $content_images ); ?>)
								</summary>
								<div style="max-height: 400px; overflow-y: auto; margin-top: 10px; border: 1px solid #ddd; padding: 10px;">
									<?php foreach ( $content_images as $img ) : ?>
										<div style="padding: 8px; border-bottom: 1px solid #eee;">
											<strong><?php echo esc_html( $img['filename'] ); ?></strong>
											<br>
											<small>
												Funnet i: <?php echo esc_html( $img['found_in'] ); ?>
												(<?php echo esc_html( $img['post_type'] ); ?>)
											</small>
											<?php if ( ! empty( $img['url'] ) ) : ?>
												<br>
												<small style="color: #666;">
													<?php echo esc_html( $img['url'] ); ?>
												</small>
											<?php endif; ?>
										</div>
									<?php endforeach; ?>
								</div>
							</details>
						<?php endif; ?>

						<?php if ( ! empty( $product_images ) ) : ?>
							<details style="margin-top: 20px;" open>
								<summary style="cursor: pointer; font-weight: 600; padding: 10px; background: #fff3cd;">
									🛍️ Produktbilder (<?php echo count( $product_images ); ?>)
								</summary>
								<div style="max-height: 400px; overflow-y: auto; margin-top: 10px; border: 1px solid #ddd; padding: 10px;">
									<?php
									// Group by product
									$by_product = array();
									foreach ( $product_images as $img ) {
										$by_product[ $img['post_id'] ][] = $img;
									}

									foreach ( $by_product as $product_id => $images ) :
										$product_name = $images[0]['found_in'];
										$missing_types = array_map( function( $img ) {
											return $img['image_type'];
										}, $images );
										?>
										<div style="padding: 12px; border-bottom: 1px solid #eee; background: #fafafa;">
											<strong><?php echo esc_html( $product_name ); ?></strong>
											<br>
											<small style="color: #d63638;">
												Mangler: <?php echo esc_html( implode( ', ', array_unique( $missing_types ) ) ); ?>
											</small>
											<br>
											<small>
												<a href="<?php echo get_edit_post_link( $product_id ); ?>" target="_blank">
													Rediger produkt →
												</a>
											</small>
										</div>
									<?php endforeach; ?>
								</div>
							</details>
						<?php endif; ?>
					<?php endif; ?>
				<?php endif; ?>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top: 20px;">
					<?php wp_nonce_field( 'smartvarme_scan_images' ); ?>
					<input type="hidden" name="action" value="smartvarme_scan_images">

					<h3>Velg hva som skal scannes:</h3>
					<p>
						<label>
							<input type="checkbox" name="scan_content" value="1" checked>
							<strong>Innholdsbilder</strong> - Bilder i posts, sider og produktbeskrivelser
						</label>
					</p>
					<p>
						<label>
							<input type="checkbox" name="scan_products" value="1" checked>
							<strong>Produktbilder</strong> - Featured images og gallery-bilder som mangler
						</label>
					</p>

					<p style="margin-top: 20px;">
						<button type="submit" class="button button-primary button-large">
							🔍 Scan etter Manglende Bilder
						</button>
					</p>
				</form>
			</div>

			<?php if ( $scan_results && ! empty( $scan_results['missing_images'] ) ) : ?>
				<div class="card" style="max-width: 800px; margin-top: 20px;">
					<h2>Import Bilder fra smartvarme.no</h2>
					<p>
						Klikk knappen nedenfor for å importere <strong><?php echo esc_html( $scan_results['total_found'] ); ?></strong>
						manglende bilder fra live-siten.
					</p>

					<div class="notice notice-warning inline">
						<p>
							<strong>Advarsel:</strong> Dette kan ta litt tid avhengig av antall bilder.
							Ikke lukk vinduet før importen er ferdig.
						</p>
					</div>

					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
						  onsubmit="return confirm('Er du sikker på at du vil importere alle manglende bilder fra smartvarme.no?');">
						<?php wp_nonce_field( 'smartvarme_import_images' ); ?>
						<input type="hidden" name="action" value="smartvarme_import_images">

						<p>
							<button type="submit" class="button button-primary button-hero">
								⬇️ Import Alle Bilder
							</button>
						</p>
					</form>
				</div>
			<?php endif; ?>

			<?php if ( $import_results ) : ?>
				<div class="card" style="max-width: 800px; margin-top: 20px;">
					<h2>Import Resultater</h2>

					<div class="notice notice-<?php echo $import_results['imported'] > 0 ? 'success' : 'info'; ?> inline">
						<p>
							<strong>Import fullført!</strong>
						</p>
						<ul>
							<li>Totalt funnet: <strong><?php echo esc_html( $import_results['total_found'] ); ?></strong></li>
							<li>Importert: <strong style="color: green;"><?php echo esc_html( $import_results['imported'] ); ?></strong></li>
							<li>Feilet: <strong style="color: red;"><?php echo esc_html( $import_results['failed'] ); ?></strong></li>
							<li>Hoppet over: <strong><?php echo esc_html( $import_results['skipped'] ); ?></strong></li>
						</ul>
					</div>

					<?php if ( ! empty( $import_results['imported_files'] ) ) : ?>
						<details style="margin-top: 20px;">
							<summary style="cursor: pointer; font-weight: 600; padding: 10px; background: #d4edda; color: #155724;">
								✅ Importerte bilder (<?php echo count( $import_results['imported_files'] ); ?>)
							</summary>
							<div style="max-height: 300px; overflow-y: auto; margin-top: 10px; border: 1px solid #ddd; padding: 10px;">
								<?php foreach ( $import_results['imported_files'] as $file ) : ?>
									<div style="padding: 8px; border-bottom: 1px solid #eee;">
										<strong><?php echo esc_html( $file['filename'] ); ?></strong>
										<br>
										<small>
											Attachment ID: <?php echo esc_html( $file['attachment_id'] ); ?>
											| Funnet i: <?php echo esc_html( $file['found_in'] ); ?>
										</small>
									</div>
								<?php endforeach; ?>
							</div>
						</details>
					<?php endif; ?>

					<?php if ( ! empty( $import_results['failed_files'] ) ) : ?>
						<details style="margin-top: 20px;">
							<summary style="cursor: pointer; font-weight: 600; padding: 10px; background: #f8d7da; color: #721c24;">
								❌ Feilede bilder (<?php echo count( $import_results['failed_files'] ); ?>)
							</summary>
							<div style="max-height: 300px; overflow-y: auto; margin-top: 10px; border: 1px solid #ddd; padding: 10px;">
								<?php foreach ( $import_results['failed_files'] as $file ) : ?>
									<div style="padding: 8px; border-bottom: 1px solid #eee;">
										<strong><?php echo esc_html( $file['filename'] ); ?></strong>
										<br>
										<small style="color: red;">
											Feil: <?php echo esc_html( $file['error'] ); ?>
										</small>
										<br>
										<small>
											URL: <?php echo esc_html( $file['url'] ); ?>
										</small>
									</div>
								<?php endforeach; ?>
							</div>
						</details>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $scan_results && ! empty( $product_images ) ) : ?>
				<div class="card" style="max-width: 800px; margin-top: 20px; border-left: 4px solid #2271b1;">
					<h2>🧪 Test med Ett Produkt</h2>
					<p>
						Velg et produkt fra listen ovenfor og test importen først.
						Når det fungerer, kan du importere alle.
					</p>

					<?php if ( $product_import_result ) : ?>
						<div class="notice notice-<?php echo $product_import_result['success'] ? 'success' : 'error'; ?> inline">
							<p>
								<strong><?php echo esc_html( $product_import_result['product_name'] ); ?></strong><br>
								<?php echo esc_html( $product_import_result['message'] ); ?>
							</p>
							<?php if ( isset( $product_import_result['featured'] ) ) : ?>
								<p>
									<small>
										Featured image:
										<?php if ( isset( $product_import_result['featured']['imported'] ) ) : ?>
											✅ Importert
										<?php elseif ( isset( $product_import_result['featured']['error'] ) ) : ?>
											❌ <?php echo esc_html( $product_import_result['featured']['error'] ); ?>
										<?php else : ?>
											ℹ️ Ikke funnet
										<?php endif; ?>
									</small>
								</p>
							<?php endif; ?>
							<?php if ( ! empty( $product_import_result['gallery'] ) ) : ?>
								<p>
									<small>
										Gallery bilder: <?php echo count( $product_import_result['gallery'] ); ?> funnet
									</small>
								</p>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<?php wp_nonce_field( 'smartvarme_import_product_images' ); ?>
						<input type="hidden" name="action" value="smartvarme_import_product_images">

						<p>
							<label for="product_id"><strong>Velg produkt:</strong></label><br>
							<select name="product_id" id="product_id" style="width: 100%; max-width: 500px;" required>
								<option value="">-- Velg et produkt --</option>
								<?php
								$products_with_missing = array();
								foreach ( $product_images as $img ) {
									if ( ! isset( $products_with_missing[ $img['post_id'] ] ) ) {
										$products_with_missing[ $img['post_id'] ] = $img['found_in'];
									}
								}
								foreach ( $products_with_missing as $pid => $pname ) :
									?>
									<option value="<?php echo esc_attr( $pid ); ?>">
										<?php echo esc_html( $pname ); ?> (ID: <?php echo $pid; ?>)
									</option>
								<?php endforeach; ?>
							</select>
						</p>

						<p>
							<button type="submit" class="button button-primary">
								🧪 Test Import for Dette Produktet
							</button>
						</p>
					</form>

					<hr style="margin: 20px 0;">

					<h3>Deretter: Import Alle</h3>
					<p>Når du har testet og det fungerer, kan du importere for alle produkter.</p>
					<p style="color: #666;">
						<em>Denne funksjonaliteten kommer snart...</em>
					</p>
				</div>
			<?php endif; ?>

			<div class="card" style="max-width: 800px; margin-top: 20px; background: #fff3cd;">
				<h2>🔧 Fiks Enkelt-Produkt Gallery</h2>
				<p>
					Hvis et spesifikt produkt mangler gallery-bilder, kan du fikse det her.
					Systemet henter gallery fra smartvarme.no og legger dem til produktet.
				</p>

				<?php
				$fix_result = get_transient( 'smartvarme_fix_gallery_result' );
				if ( $fix_result ) {
					delete_transient( 'smartvarme_fix_gallery_result' );
					?>
					<div class="notice notice-<?php echo $fix_result['success'] ? 'success' : 'error'; ?> inline">
						<p><strong><?php echo esc_html( $fix_result['message'] ); ?></strong></p>
						<?php if ( ! empty( $fix_result['images'] ) ) : ?>
							<ul>
								<?php foreach ( $fix_result['images'] as $img ) : ?>
									<li><?php echo esc_html( $img ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				<?php } ?>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'smartvarme_fix_product_gallery' ); ?>
					<input type="hidden" name="action" value="smartvarme_fix_product_gallery">

					<p>
						<label for="product_slug"><strong>Produkt URL-slug:</strong></label><br>
						<input type="text"
						       name="product_slug"
						       id="product_slug"
						       value="peisinnsats-med-gjennomsyn-caminaschmid-lina-tv-120"
						       style="width: 100%; max-width: 500px;"
						       placeholder="f.eks. peisinnsats-med-gjennomsyn-caminaschmid-lina-tv-120"
						       required>
						<br>
						<small>Kopier slug fra produkt-URL: /produkt/<strong>slug-her</strong>/</small>
					</p>

					<p>
						<button type="submit" class="button button-primary">
							🔧 Fiks Gallery for Dette Produktet
						</button>
					</p>
				</form>
			</div>

			<div class="card" style="max-width: 800px; margin-top: 20px;">
				<h2>Hvordan det fungerer</h2>
				<ol>
					<li><strong>Scan:</strong> Finner alle bilder referert i innhold som ikke finnes lokalt</li>
					<li><strong>Import:</strong> Laster ned manglende bilder fra smartvarme.no</li>
					<li><strong>Media Library:</strong> Bildene legges automatisk i WordPress media library</li>
				</ol>

				<h3>Hva scannes?</h3>
				<ul>
					<li>Bilder i innhold (posts, sider, produkter)</li>
					<li>Featured images som mangler</li>
					<li>Produkt gallery-bilder</li>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle scan images form submission
	 */
	public function handle_scan_images() {
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		// Verify nonce
		check_admin_referer( 'smartvarme_scan_images' );

		// Get scan options
		$options = array(
			'scan_content'  => isset( $_POST['scan_content'] ) && $_POST['scan_content'] === '1',
			'scan_products' => isset( $_POST['scan_products'] ) && $_POST['scan_products'] === '1',
		);

		// Load importer class
		require_once SMARTVARME_CORE_PATH . 'includes/class-smartvarme-image-importer.php';

		// Run scan
		$missing_images = Smartvarme_Image_Importer::scan_missing_images( $options );

		// Store results in transient
		$results = array(
			'total_found'    => count( $missing_images ),
			'missing_images' => $missing_images,
			'options'        => $options,
		);
		set_transient( 'smartvarme_image_scan_results', $results, 300 );

		// Redirect back
		wp_safe_redirect( admin_url( 'tools.php?page=smartvarme-image-importer' ) );
		exit;
	}

	/**
	 * Handle import images form submission
	 */
	public function handle_import_images() {
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		// Verify nonce
		check_admin_referer( 'smartvarme_import_images' );

		// Increase time limit for large imports
		set_time_limit( 600 ); // 10 minutes

		// Get last scan options from transient
		$scan_results = get_transient( 'smartvarme_image_scan_results' );
		$scan_options = isset( $scan_results['options'] ) ? $scan_results['options'] : array(
			'scan_content'  => true,
			'scan_products' => true,
		);

		// Load importer class
		require_once SMARTVARME_CORE_PATH . 'includes/class-smartvarme-image-importer.php';

		// Run import with same scan options
		$results = Smartvarme_Image_Importer::import_missing_images( array(
			'scan_options' => $scan_options,
		) );

		// Store results in transient
		set_transient( 'smartvarme_image_import_results', $results, 300 );

		// Redirect back
		wp_safe_redirect( admin_url( 'tools.php?page=smartvarme-image-importer' ) );
		exit;
	}

	/**
	 * Handle import product images form submission
	 */
	public function handle_import_product_images() {
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		// Verify nonce
		check_admin_referer( 'smartvarme_import_product_images' );

		// Get product ID
		$product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;

		if ( ! $product_id ) {
			wp_die( 'Produkt ID mangler' );
		}

		// Load helper class
		require_once SMARTVARME_CORE_PATH . 'includes/class-smartvarme-product-image-helper.php';

		// Import images for this product
		$result = Smartvarme_Product_Image_Helper::import_product_images( $product_id );

		// Store result in transient
		set_transient( 'smartvarme_product_image_import_result', $result, 300 );

		// Redirect back with product_id
		wp_safe_redirect( admin_url( 'tools.php?page=smartvarme-image-importer&product_id=' . $product_id ) );
		exit;
	}

	/**
	 * Handle fix product gallery form submission
	 */
	public function handle_fix_product_gallery() {
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		// Verify nonce
		check_admin_referer( 'smartvarme_fix_product_gallery' );

		// Get product slug
		$slug = isset( $_POST['product_slug'] ) ? sanitize_text_field( $_POST['product_slug'] ) : '';

		if ( ! $slug ) {
			wp_die( 'Product slug missing' );
		}

		// Find product
		$product = get_page_by_path( $slug, OBJECT, 'product' );

		if ( ! $product ) {
			$result = array(
				'success' => false,
				'message' => 'Produkt ikke funnet: ' . $slug,
			);
			set_transient( 'smartvarme_fix_gallery_result', $result, 300 );
			wp_safe_redirect( admin_url( 'tools.php?page=smartvarme-image-importer' ) );
			exit;
		}

		// Fetch from live site
		$live_url = 'https://smartvarme.no/produkt/' . $slug . '/';
		$response = wp_remote_get( $live_url, array( 'timeout' => 30 ) );

		if ( is_wp_error( $response ) ) {
			$result = array(
				'success' => false,
				'message' => 'Kunne ikke hente fra live site: ' . $response->get_error_message(),
			);
			set_transient( 'smartvarme_fix_gallery_result', $result, 300 );
			wp_safe_redirect( admin_url( 'tools.php?page=smartvarme-image-importer' ) );
			exit;
		}

		$html = wp_remote_retrieve_body( $response );

		// Extract only product gallery section
		$gallery_html = '';
		if ( preg_match( '/<div[^>]+class="[^"]*woocommerce-product-gallery[^"]*"[^>]*>(.*?)<\/div>/s', $html, $gallery_section ) ) {
			$gallery_html = $gallery_section[1];
		} elseif ( preg_match( '/<figure[^>]+class="[^"]*woocommerce-product-gallery[^"]*"[^>]*>(.*?)<\/figure>/s', $html, $gallery_section ) ) {
			$gallery_html = $gallery_section[1];
		} else {
			// Fallback: look for product images div
			if ( preg_match( '/<div[^>]+class="[^"]*product-images[^"]*"[^>]*>(.*?)<\/div>/s', $html, $gallery_section ) ) {
				$gallery_html = $gallery_section[1];
			}
		}

		// Find gallery images only in gallery section
		preg_match_all( '/<a[^>]+href="([^"]+\.(jpg|jpeg|png|gif))"[^>]*>/', $gallery_html, $matches );

		$gallery_ids = array();
		$imported_images = array();

		if ( ! empty( $matches[1] ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';

			foreach ( array_unique( $matches[1] ) as $img_url ) {
				// Skip thumbnails
				if ( strpos( $img_url, '-150x150' ) !== false ||
				     strpos( $img_url, '-300x300' ) !== false ||
				     strpos( $img_url, '-100x100' ) !== false ) {
					continue;
				}

				// Download and import
				$tmp = download_url( $img_url );

				if ( ! is_wp_error( $tmp ) ) {
					$file_array = array(
						'name'     => basename( parse_url( $img_url, PHP_URL_PATH ) ),
						'tmp_name' => $tmp,
					);

					$attachment_id = media_handle_sideload( $file_array, $product->ID );
					@unlink( $tmp );

					if ( ! is_wp_error( $attachment_id ) ) {
						$gallery_ids[] = $attachment_id;
						$imported_images[] = basename( $img_url );
					}
				}
			}
		}

		// Update product gallery
		if ( ! empty( $gallery_ids ) ) {
			$wc_product = wc_get_product( $product->ID );
			$existing_gallery = $wc_product->get_gallery_image_ids();
			$all_gallery_ids = array_unique( array_merge( $existing_gallery, $gallery_ids ) );
			$wc_product->set_gallery_image_ids( $all_gallery_ids );
			$wc_product->save();

			$result = array(
				'success' => true,
				'message' => 'Gallery oppdatert for: ' . $product->post_title . ' (' . count( $gallery_ids ) . ' bilder)',
				'images'  => $imported_images,
			);
		} else {
			$result = array(
				'success' => false,
				'message' => 'Ingen gallery-bilder funnet på live site',
			);
		}

		set_transient( 'smartvarme_fix_gallery_result', $result, 300 );
		wp_safe_redirect( admin_url( 'tools.php?page=smartvarme-image-importer' ) );
		exit;
	}
}
