<?php
/*
Plugin Name: MK Post Type example
Plugin URI: https://mklasen.com
Description: -
Version: 1.0
Author: mklasen
Author URI: https://automattic.com/wordpress-plugins/
License: GPLv2 or later
Text Domain: mk-post-types
*/


class MK_Post_Type_Example {

  // Init
  public function __construct() {
    $this->setup_hooks();
  }


  // Setup hooks
  public function setup_hooks() {
    add_action('init', array($this, 'setup_post_types'));
    add_action('init', array($this, 'setup_taxonomy'));
    add_filter('post_type_link', array($this, 'change_post_link'), 10, 2);
    add_action( 'generate_rewrite_rules', array($this, 'customize_rewrite_rules') );
  }

  // Setup taxonomy
  public function setup_taxonomy() {
    register_taxonomy(
      'custom_tax', // Tax Name
      'custom-post', // Post Type it's linked to
      array(
        'rewrite' => array( 'slug' => 'custom-post', 'with_front' => false ), // slug only contains the post type name
      )
    );
  }

  public function setup_post_types() {
    // Register the post type

    register_post_type('custom-post', array(
      'label' => 'Custom Post',
      'public' => true,
      'menu_icon' => 'dashicons-admin-home',
      'show_in_nav_menus'=> true,
      'has_archive' => true,
      'supports' => array('title', 'editor'),
      'rewrite' => array( 'slug' => '%custom_tax%', 'with_front' => false ), // Set the custom_tax here
      'has_archive' => 'archive_slug',
    ));

    // Disable auto, use verbose
    $GLOBALS['wp_rewrite']->use_verbose_page_rules = true;
  }

  // Make rewrite rules aware of this
  public function customize_rewrite_rules( &$wp_rewrite ) {

    // Add the tag for custom_tax
    $wp_rewrite->add_rewrite_tag( '%custom_tax%', '(.+?)', 'custom_tax=' );

    // This will generate the actual rewrite rules, and put the at the end of the list
    $wp_rewrite->rules += $wp_rewrite->generate_rewrite_rules( $wp_rewrite->front . '%custom_tax%', EP_NONE );
  }

  // Change the post link that is displayed
  public function change_post_link($post_link, $post) {
    if ( is_object( $post ) && $post->post_type == 'custom-post' ){
        $terms = wp_get_object_terms( $post->ID, 'custom_tax' );
        if( $terms ){
            return str_replace( '%custom_tax%' , $terms[0]->slug , $post_link );
        }
    }
    return $post_link;
  }
}

$mkPostType = new MK_Post_Type_Example();
