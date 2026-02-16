<?php
/**
 * Enqueue's all the scripts
 *
 * @return void
 */

function maksimer_setup() {
	// Load textdomain.
	load_theme_textdomain( 'maksimer-lang', get_stylesheet_directory() . '/assets/languages' );
}

add_action( 'after_setup_theme', 'maksimer_setup' );

function maksimer_enqueue_scripts() {
	$translation_array = [
		'ajaxurl'        => admin_url( 'admin-ajax.php' ),
		'wp_nonce'       => wp_create_nonce( 'maksimer_ajax_nonce' ),
		'read_more'      => __( 'Read more', 'maksimer-lang' ),
		'close'          => __( 'Close', 'maksimer-lang' ),
		'edit'           => __( 'Edit', 'maksimer-lang' ),
		'search'         => __( 'Search', 'maksimer-lang' ),
		'delete_product' => __( 'Are you sure you want to remove', 'maksimer-lang' ),
		'fetching_more'  => __( 'Fetching more items…', 'maksimer-lang' ),
		'login_text'     => __( 'Log in', 'maksimer-lang' ),
		'page_id'        => function_exists( 'WC' ) ? is_product_taxonomy() ? get_queried_object()->term_id : 0 : '',
		'query_arg'     => isset( $_GET['filter_merke'] ) ? $_GET['filter_merke'] : '',
	];

	wp_enqueue_style( 'style', get_theme_file_uri( 'build/main-style.css' ), false, filemtime( get_theme_file_path( 'build/main-style.css' ) ), 'all' );
	wp_register_script( 'maksimer', get_theme_file_uri( 'build/maksimer.js' ), [ 'jquery' ], filemtime( get_theme_file_path( 'build/maksimer.js' ) ), true );
	wp_localize_script( 'maksimer', 'translation', $translation_array );
	wp_enqueue_script( 'maksimer' );
}

add_action( 'wp_enqueue_scripts', 'maksimer_enqueue_scripts', 999 );


/**
 * Enqueue block editor assets
 *
 * @return void
 */

function maksimer_block_editor_styles() {
	wp_enqueue_style( 'maksimer-block-editor-style', get_stylesheet_directory_uri() . '/build/editor-style-block.css', [], filemtime( get_stylesheet_directory() . '/build/editor-style-block.css' ), 'all' );
}

add_action( 'enqueue_block_editor_assets', 'maksimer_block_editor_styles', 1, 1 );

