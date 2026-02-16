<?php
/**
 * FAQ Custom Post Type
 *
 * @package Smartvarme_Core
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Smartvarme_FAQ_CPT {

	/**
	 * Initialize the class
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_faq_post_type' ) );
		add_action( 'init', array( $this, 'register_faq_taxonomy' ) );
	}

	/**
	 * Register FAQ custom post type
	 */
	public function register_faq_post_type() {
		$labels = array(
			'name'                  => _x( 'FAQ', 'Post type general name', 'smartvarme' ),
			'singular_name'         => _x( 'FAQ', 'Post type singular name', 'smartvarme' ),
			'menu_name'             => _x( 'FAQ', 'Admin Menu text', 'smartvarme' ),
			'name_admin_bar'        => _x( 'FAQ', 'Add New on Toolbar', 'smartvarme' ),
			'add_new'               => __( 'Legg til ny', 'smartvarme' ),
			'add_new_item'          => __( 'Legg til nytt spørsmål', 'smartvarme' ),
			'new_item'              => __( 'Nytt spørsmål', 'smartvarme' ),
			'edit_item'             => __( 'Rediger spørsmål', 'smartvarme' ),
			'view_item'             => __( 'Vis spørsmål', 'smartvarme' ),
			'all_items'             => __( 'Alle spørsmål', 'smartvarme' ),
			'search_items'          => __( 'Søk i spørsmål', 'smartvarme' ),
			'not_found'             => __( 'Ingen spørsmål funnet.', 'smartvarme' ),
			'not_found_in_trash'    => __( 'Ingen spørsmål funnet i papirkurv.', 'smartvarme' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_rest'       => true, // Enable Gutenberg editor
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'faq' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 20,
			'menu_icon'          => 'dashicons-editor-help',
			'supports'           => array( 'title', 'editor', 'excerpt', 'revisions' ),
			'template'           => array(
				array( 'core/paragraph', array(
					'placeholder' => 'Skriv det fullstendige svaret her...',
				) ),
			),
		);

		register_post_type( 'faq', $args );
	}

	/**
	 * Register FAQ category taxonomy (optional, for organizing FAQs)
	 */
	public function register_faq_taxonomy() {
		$labels = array(
			'name'              => _x( 'FAQ Kategorier', 'taxonomy general name', 'smartvarme' ),
			'singular_name'     => _x( 'FAQ Kategori', 'taxonomy singular name', 'smartvarme' ),
			'search_items'      => __( 'Søk kategorier', 'smartvarme' ),
			'all_items'         => __( 'Alle kategorier', 'smartvarme' ),
			'edit_item'         => __( 'Rediger kategori', 'smartvarme' ),
			'update_item'       => __( 'Oppdater kategori', 'smartvarme' ),
			'add_new_item'      => __( 'Legg til ny kategori', 'smartvarme' ),
			'new_item_name'     => __( 'Nytt kategorinavn', 'smartvarme' ),
			'menu_name'         => __( 'Kategorier', 'smartvarme' ),
		);

		$args = array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'faq-kategori' ),
		);

		register_taxonomy( 'faq_category', array( 'faq' ), $args );
	}
}
