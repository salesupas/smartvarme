<?php
/**
 * FAQ Schema Markup
 *
 * Generates FAQPage JSON-LD schema for FAQ archive
 *
 * @package Smartvarme_Core
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * FAQ Schema class
 */
class Smartvarme_FAQ_Schema {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_head', array( $this, 'output_faq_schema' ) );
	}

	/**
	 * Output FAQ schema on FAQ archive page
	 */
	public function output_faq_schema() {
		// Only output on FAQ archive page
		if ( ! is_post_type_archive( 'faq' ) ) {
			return;
		}

		// Query all published FAQ posts
		$faq_posts = get_posts( array(
			'post_type'      => 'faq',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );

		if ( empty( $faq_posts ) ) {
			return;
		}

		// Build schema structure
		$schema = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => array(),
		);

		foreach ( $faq_posts as $faq ) {
			$schema['mainEntity'][] = array(
				'@type'          => 'Question',
				'name'           => get_the_title( $faq->ID ),
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $faq->post_excerpt ? wp_strip_all_tags( $faq->post_excerpt ) : wp_trim_words( wp_strip_all_tags( $faq->post_content ), 30 ),
				),
			);
		}

		// Output JSON-LD
		echo '<script type="application/ld+json">';
		echo wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
		echo '</script>' . "\n";
	}
}
