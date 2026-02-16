<?php
/**
 * Image Importer from Live Site
 *
 * Scans content for missing images and imports them from smartvarme.no
 *
 * @package Smartvarme_Core
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Smartvarme_Image_Importer {

	/**
	 * Live site URL
	 */
	const LIVE_SITE_URL = 'https://smartvarme.no';

	/**
	 * Scan and find all missing images
	 *
	 * @param array $options Scan options
	 * @return array Missing images data
	 */
	public static function scan_missing_images( $options = array() ) {
		global $wpdb;

		$defaults = array(
			'scan_content'  => true,
			'scan_products' => false,
		);
		$options = wp_parse_args( $options, $defaults );

		$missing_images = array();
		$checked_urls = array();

		// 1. Scan content images
		if ( $options['scan_content'] ) {
			$posts = get_posts( array(
				'post_type'      => array( 'post', 'page', 'product' ),
				'posts_per_page' => -1,
				'post_status'    => 'any',
			) );

			foreach ( $posts as $post ) {
				// Find all image URLs in content
				$content = $post->post_content;
				preg_match_all( '/<img[^>]+src=["\']([^"\']+)["\']/', $content, $matches );

				if ( ! empty( $matches[1] ) ) {
					foreach ( $matches[1] as $img_url ) {
						// Skip if already checked
						if ( isset( $checked_urls[ $img_url ] ) ) {
							continue;
						}
						$checked_urls[ $img_url ] = true;

						// Skip localhost URLs - these are already local
						if ( self::is_localhost_url( $img_url ) ) {
							continue;
						}

						// Check if image exists locally
						if ( ! self::image_exists_locally( $img_url ) ) {
							$missing_images[] = array(
								'url'         => $img_url,
								'found_in'    => $post->post_title,
								'post_id'     => $post->ID,
								'post_type'   => $post->post_type,
								'filename'    => basename( parse_url( $img_url, PHP_URL_PATH ) ),
								'image_type'  => 'content',
							);
						}
					}
				}
			}
		}

		// 2. Scan product images (featured + gallery)
		if ( $options['scan_products'] ) {
			$missing_images = array_merge(
				$missing_images,
				self::scan_product_images()
			);
		}

		return $missing_images;
	}

	/**
	 * Scan for missing product images (featured + gallery)
	 * Scans product descriptions for image URLs and checks if they exist
	 *
	 * @return array Missing product images
	 */
	public static function scan_product_images() {
		global $wpdb;

		$missing_images = array();
		$checked_urls = array();

		// Get all products
		$products = wc_get_products( array(
			'limit'  => -1,
			'status' => array( 'publish', 'draft', 'pending' ),
		) );

		foreach ( $products as $product ) {
			$product_id = $product->get_id();
			$product_name = $product->get_name();

			// Get product content (description + short description)
			$content = $product->get_description() . ' ' . $product->get_short_description();

			// Also check product meta and custom fields
			$post = get_post( $product_id );
			if ( $post ) {
				$content .= ' ' . $post->post_content;
			}

			// Find all image URLs in product content
			preg_match_all( '/<img[^>]+src=["\']([^"\']+)["\']/', $content, $matches );

			if ( ! empty( $matches[1] ) ) {
				foreach ( $matches[1] as $img_url ) {
					// Skip if already checked
					if ( isset( $checked_urls[ $img_url ] ) ) {
						continue;
					}
					$checked_urls[ $img_url ] = true;

					// Skip localhost URLs - these should exist locally
					if ( self::is_localhost_url( $img_url ) ) {
						// But check if they actually exist
						if ( ! self::image_exists_locally( $img_url ) ) {
							$missing_images[] = array(
								'url'         => $img_url,
								'found_in'    => $product_name,
								'post_id'     => $product_id,
								'post_type'   => 'product',
								'filename'    => basename( parse_url( $img_url, PHP_URL_PATH ) ),
								'image_type'  => 'gallery',
							);
						}
						continue;
					}

					// Check if image exists locally
					if ( ! self::image_exists_locally( $img_url ) ) {
						$missing_images[] = array(
							'url'         => $img_url,
							'found_in'    => $product_name,
							'post_id'     => $product_id,
							'post_type'   => 'product',
							'filename'    => basename( parse_url( $img_url, PHP_URL_PATH ) ),
							'image_type'  => 'gallery',
						);
					}
				}
			}
		}

		return $missing_images;
	}

	/**
	 * Check if image exists locally
	 *
	 * @param string $url Image URL
	 * @return bool
	 */
	private static function image_exists_locally( $url ) {
		global $wpdb;

		// Parse URL
		$parsed_url = parse_url( $url );
		if ( ! isset( $parsed_url['path'] ) ) {
			return true; // Skip invalid URLs
		}

		$filename = basename( $parsed_url['path'] );

		// For localhost URLs, check if file actually exists on disk
		if ( self::is_localhost_url( $url ) ) {
			// Extract path from URL
			$path = $parsed_url['path'];

			// Remove leading slash and get relative path
			$relative_path = ltrim( $path, '/' );

			// Check if it's in wp-content/uploads
			if ( strpos( $relative_path, 'wp-content/uploads/' ) !== false ) {
				$file_path = ABSPATH . $relative_path;
				return file_exists( $file_path );
			}
		}

		// Check in media library by filename
		$attachment = $wpdb->get_var( $wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts}
			WHERE post_type = 'attachment'
			AND guid LIKE %s
			LIMIT 1",
			'%' . $wpdb->esc_like( $filename )
		) );

		return ! empty( $attachment );
	}

	/**
	 * Import missing images from live site
	 *
	 * @param array $options Import options
	 * @return array Results
	 */
	public static function import_missing_images( $options = array() ) {
		// Get scan options
		$scan_options = isset( $options['scan_options'] ) ? $options['scan_options'] : array(
			'scan_content'  => true,
			'scan_products' => true,
		);

		$missing = self::scan_missing_images( $scan_options );

		$results = array(
			'success'        => true,
			'total_found'    => count( $missing ),
			'imported'       => 0,
			'failed'         => 0,
			'skipped'        => 0,
			'imported_files' => array(),
			'failed_files'   => array(),
		);

		foreach ( $missing as $image_data ) {
			// Skip if no URL
			if ( empty( $image_data['url'] ) ) {
				$results['skipped']++;
				continue;
			}

			// Convert localhost URL to smartvarme.no
			$url = str_replace(
				array( 'http://localhost:8080', 'http://localhost', 'https://localhost:8080', 'https://localhost' ),
				'https://smartvarme.no',
				$image_data['url']
			);

			// Try to import image
			$import_result = self::download_and_import( $url, $image_data['post_id'], $image_data['filename'] );

			if ( $import_result['success'] ) {
				$results['imported']++;
				$results['imported_files'][] = array(
					'filename'      => $image_data['filename'],
					'source_url'    => $url,
					'attachment_id' => $import_result['attachment_id'],
					'found_in'      => $image_data['found_in'],
				);

				// Replace old URL with new URL in post content
				$new_url = wp_get_attachment_url( $import_result['attachment_id'] );
				if ( $new_url ) {
					self::replace_image_url_in_content( $image_data['url'], $new_url, $image_data['post_id'] );
				}
			} else {
				$results['failed']++;
				$results['failed_files'][] = array(
					'filename'  => $image_data['filename'],
					'url'       => $url,
					'error'     => $import_result['error'],
					'found_in'  => $image_data['found_in'],
				);
			}
		}

		return $results;
	}

	/**
	 * Download and import a single image
	 *
	 * @param string $url Image URL
	 * @param int $post_id Post ID to attach to
	 * @param string $filename Filename
	 * @return array Result
	 */
	private static function download_and_import( $url, $post_id, $filename ) {
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
			'name'     => $filename,
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

	/**
	 * Import a single image from live site
	 *
	 * @param array $image_data Image data
	 * @return array Result
	 */
	private static function import_single_image( $image_data ) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$url = $image_data['url'];

		// Convert localhost URLs to live site URLs
		$url = self::convert_to_live_url( $url );

		// Skip external images (not from smartvarme.no)
		if ( ! self::is_smartvarme_url( $url ) ) {
			return array(
				'success' => false,
				'error'   => 'External image (not from smartvarme.no)',
			);
		}

		// Try to download and import
		$tmp = download_url( $url );

		if ( is_wp_error( $tmp ) ) {
			return array(
				'success' => false,
				'error'   => $tmp->get_error_message(),
			);
		}

		$file_array = array(
			'name'     => $image_data['filename'],
			'tmp_name' => $tmp,
		);

		// Import into media library
		$attachment_id = media_handle_sideload( $file_array, $image_data['post_id'] );

		// Clean up temp file
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

	/**
	 * Convert localhost URL to live site URL
	 *
	 * @param string $url Original URL
	 * @return string Converted URL
	 */
	private static function convert_to_live_url( $url ) {
		// If URL is relative, make it absolute with live site URL
		if ( strpos( $url, 'http' ) !== 0 ) {
			return self::LIVE_SITE_URL . $url;
		}

		// Replace localhost URLs with live site URL
		$localhost_patterns = array(
			'http://localhost:8080',
			'http://localhost',
			'https://localhost:8080',
			'https://localhost',
		);

		foreach ( $localhost_patterns as $pattern ) {
			if ( strpos( $url, $pattern ) === 0 ) {
				return str_replace( $pattern, self::LIVE_SITE_URL, $url );
			}
		}

		// Replace smartvarme.no variants
		$url = preg_replace( '#^https?://(www\.)?smartvarme\.no#', self::LIVE_SITE_URL, $url );

		return $url;
	}

	/**
	 * Check if URL is from smartvarme.no
	 *
	 * @param string $url URL to check
	 * @return bool
	 */
	private static function is_smartvarme_url( $url ) {
		$parsed = parse_url( $url );
		if ( ! isset( $parsed['host'] ) ) {
			return false;
		}

		$host = strtolower( $parsed['host'] );
		return $host === 'smartvarme.no' || $host === 'www.smartvarme.no';
	}

	/**
	 * Check if URL is a localhost URL
	 *
	 * @param string $url URL to check
	 * @return bool
	 */
	private static function is_localhost_url( $url ) {
		$localhost_patterns = array(
			'http://localhost',
			'https://localhost',
			'http://127.0.0.1',
			'https://127.0.0.1',
		);

		foreach ( $localhost_patterns as $pattern ) {
			if ( strpos( $url, $pattern ) === 0 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Replace image URL in post content
	 *
	 * @param string $old_url Old image URL
	 * @param string $new_url New image URL
	 * @param int $post_id Post ID
	 */
	private static function replace_image_url_in_content( $old_url, $new_url, $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return;
		}

		// Replace in post content
		$content = $post->post_content;
		$new_content = str_replace( $old_url, $new_url, $content );

		if ( $content !== $new_content ) {
			wp_update_post( array(
				'ID'           => $post_id,
				'post_content' => $new_content,
			) );
		}

		// For products, also check description and short description
		if ( $post->post_type === 'product' ) {
			$product = wc_get_product( $post_id );
			if ( $product ) {
				$description = $product->get_description();
				$short_description = $product->get_short_description();

				$new_description = str_replace( $old_url, $new_url, $description );
				$new_short_description = str_replace( $old_url, $new_url, $short_description );

				if ( $description !== $new_description ) {
					$product->set_description( $new_description );
				}
				if ( $short_description !== $new_short_description ) {
					$product->set_short_description( $new_short_description );
				}

				if ( $description !== $new_description || $short_description !== $new_short_description ) {
					$product->save();
				}
			}
		}
	}
}
