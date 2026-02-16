<?php
/**
 * Product Image Helper
 *
 * Helps import individual product images from live site
 *
 * @package Smartvarme_Core
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Smartvarme_Product_Image_Helper {

	/**
	 * Live site URL
	 */
	const LIVE_SITE_URL = 'https://smartvarme.no';

	/**
	 * Import images for a specific product from live site
	 *
	 * @param int $product_id Product ID
	 * @return array Result
	 */
	public static function import_product_images( $product_id ) {
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return array(
				'success' => false,
				'message' => 'Produkt ikke funnet',
			);
		}

		$slug = $product->get_slug();
		$live_product_url = self::LIVE_SITE_URL . '/produkt/' . $slug . '/';

		// Fetch live product page
		$response = wp_remote_get( $live_product_url, array( 'timeout' => 30 ) );

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => 'Kunne ikke hente produktside: ' . $response->get_error_message(),
			);
		}

		$html = wp_remote_retrieve_body( $response );

		// Parse HTML to find images
		$results = array(
			'success'       => true,
			'product_name'  => $product->get_name(),
			'product_url'   => $live_product_url,
			'featured'      => null,
			'gallery'       => array(),
			'imported'      => 0,
			'failed'        => 0,
		);

		// Find featured image
		if ( preg_match( '/<img[^>]+class="[^"]*wp-post-image[^"]*"[^>]+src="([^"]+)"/', $html, $matches ) ) {
			$featured_url = $matches[1];
			$results['featured'] = array( 'url' => $featured_url );

			// Try to import
			if ( ! has_post_thumbnail( $product_id ) ) {
				$import_result = self::import_single_image( $featured_url, $product_id, 'featured' );
				if ( $import_result['success'] ) {
					set_post_thumbnail( $product_id, $import_result['attachment_id'] );
					$results['imported']++;
					$results['featured']['imported'] = true;
				} else {
					$results['failed']++;
					$results['featured']['error'] = $import_result['error'];
				}
			}
		}

		// Find gallery images
		preg_match_all( '/<a[^>]+data-thumb="([^"]+)"/', $html, $gallery_matches );
		if ( ! empty( $gallery_matches[1] ) ) {
			$gallery_ids = array();
			foreach ( $gallery_matches[1] as $thumb_url ) {
				// Convert thumb URL to full URL
				$full_url = preg_replace( '/-\d+x\d+\./', '.', $thumb_url );

				$results['gallery'][] = array( 'url' => $full_url );

				// Try to import
				$import_result = self::import_single_image( $full_url, $product_id, 'gallery' );
				if ( $import_result['success'] ) {
					$gallery_ids[] = $import_result['attachment_id'];
					$results['imported']++;
					$results['gallery'][ count( $results['gallery'] ) - 1 ]['imported'] = true;
				} else {
					$results['failed']++;
					$results['gallery'][ count( $results['gallery'] ) - 1 ]['error'] = $import_result['error'];
				}
			}

			// Set gallery images
			if ( ! empty( $gallery_ids ) ) {
				$product->set_gallery_image_ids( $gallery_ids );
				$product->save();
			}
		}

		$results['message'] = sprintf(
			'Importert %d bilder, %d feilet',
			$results['imported'],
			$results['failed']
		);

		return $results;
	}

	/**
	 * Import a single image
	 *
	 * @param string $url Image URL
	 * @param int $post_id Post ID to attach to
	 * @param string $type Image type (featured or gallery)
	 * @return array Result
	 */
	private static function import_single_image( $url, $post_id, $type = 'image' ) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		// Download file
		$tmp = download_url( $url );

		if ( is_wp_error( $tmp ) ) {
			return array(
				'success' => false,
				'error'   => $tmp->get_error_message(),
			);
		}

		$file_array = array(
			'name'     => basename( parse_url( $url, PHP_URL_PATH ) ),
			'tmp_name' => $tmp,
		);

		// Import into media library
		$attachment_id = media_handle_sideload( $file_array, $post_id );

		// Clean up
		@unlink( $tmp );

		if ( is_wp_error( $attachment_id ) ) {
			return array(
				'success' => false,
				'error'   => $attachment_id->get_error_message(),
			);
		}

		return array(
			'success'       => true,
			'attachment_id' => $attachment_id,
		);
	}
}
