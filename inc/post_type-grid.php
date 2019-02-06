<?php
if(!function_exists('lb_flush_rewrite_rules')){
	// Flush rewrite rules
	add_action( 'after_switch_theme', 'lb_flush_rewrite_rules' );
	function lb_flush_rewrite_rules() {
		flush_rewrite_rules();
	}
}

add_action( 'init', 'lb_posttype_grid_items');
function lb_posttype_grid_items() {
	// Write these variables as all lowercase

	// Post variables
	$singular 			= __('grid item', 'rbl-gridhelper');
	$multiple 			= __('grid items', 'rbl-gridhelper');
	$description 		= __( 'Items to show in the Gridhelper-grid', 'rbl-gridhelper' );
	$single_slug 		= $singular;
	$archive_slug 		= $multiple;
	$post_type_name		= 'grid_items';

	register_post_type(
		$post_type_name,
		array(
			'labels' => array(
				'name'                  => __('Gridhelper', 'rbl-gridhelper'),
				'singular_name'         => ucfirst($singular),
				'all_items'             => __( 'All', 'rbl-gridhelper' ).' '.$multiple,
				'add_new'               => __( 'Add new', 'rbl-gridhelper' ),
				'add_new_item'          => __( 'Add new', 'rbl-gridhelper' ).' '.$singular,
				'edit'                  => __( 'Edit', 'rbl-gridhelper' ),
				'edit_item'             => __( 'Edit', 'rbl-gridhelper' ).' '.$singular,
				'new_item'              => __( 'New', 'rbl-gridhelper' ).' '.$singular,
				'view_item'             => __( 'View', 'rbl-gridhelper' ).' '.$singular,
				'search_items'          => __( 'Search for', 'rbl-gridhelper' ).' '.$singular,
				'not_found'             =>  __( 'Nothing found in the database', 'rbl-gridhelper' ),
				'not_found_in_trash'    => __( 'The trash is empty', 'rbl-gridhelper' ),
				'parent_item_colon'     => ''
			),
			'description'           => $description,
			'public'                => true,
			'publicly_queryable'    => true,
			'exclude_from_search'   => true,
			'show_ui'               => true,
			'query_var'             => true,
			'menu_position'         => 80,
			'menu_icon'             => 'dashicons-grid-view',
			'rewrite'               => false,
			'has_archive'           => false,
			'capability_type'		=> 'post',
			'hierarchical'          => false,
			'show_in_rest'			=> true,
			'supports'              => array(
				'title',
				'editor',
				'author',
				'thumbnail',
				'revisions',
			)
		)
	);
}