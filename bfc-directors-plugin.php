<?php

/**
 * Plugin Name: BFC Directors Plugin
 * Plugin URI: https://www.blackforest-coding.de/applikationsentwicklung/
 * Description: Custom plugin for managing directors.
 * Version: 1.0.0
 * Author: BlackForest Coding GmbH
 * Author URI: https://www.blackforest-coding.de/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Plugin code goes here.
function directors_enqueue_scripts()
{
    wp_enqueue_style('directors-plugin-style', plugin_dir_url(__FILE__) . 'style.css');
    wp_enqueue_script('directors-plugin-script', plugin_dir_url(__FILE__) . 'functions.js', array('jquery'), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'directors_enqueue_scripts');


function directors_register_post_type()
{
    $labels = array(
        'name'               => 'Directors',
        'singular_name'      => 'Director',
        'menu_name'          => 'Directors',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Director',
        'edit_item'          => 'Edit Director',
        'new_item'           => 'New Director',
        'view_item'          => 'View Director',
        'search_items'       => 'Search Directors',
        'not_found'          => 'No directors found',
        'not_found_in_trash' => 'No directors found in Trash',
        'parent_item_colon'  => 'Parent Director:',
        'all_items'          => 'All Directors',
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => array('slug' => 'directors'),
        'capability_type'     => 'post',
        'has_archive'         => true,
        'hierarchical'        => false,
        'menu_position'       => null,
        'supports'            => array('title', 'editor', 'thumbnail', 'custom-fields', 'page-attributes'),
        'menu_icon'           => 'dashicons-businessman',
        'show_in_rest'        => true, // For Gutenberg support
        'taxonomies'          => array('category', 'post_tag'),
        'show_in_graphql'     => true, // For GraphQL support
        'graphql_single_name' => 'director',
        'graphql_plural_name' => 'directors',
    );

    register_post_type('directors', $args);

    add_action('add_meta_boxes', 'directors_add_meta_boxes');
    add_action('save_post', 'directors_save_meta_data');
}
add_action('init', 'directors_register_post_type');

function directors_add_meta_boxes()
{
    add_meta_box('director_contact_meta_box', 'Contact Information', 'directors_render_contact_meta_box', 'directors', 'normal', 'default');
}

function directors_render_contact_meta_box($post)
{
    // Retrieve the existing values
    $director_email = get_post_meta($post->ID, 'director_email', true);
    $director_phone = get_post_meta($post->ID, 'director_phone', true);

    // Display the form fields
?>
    <label for="director_email">Email:</label>
    <input type="text" id="director_email" name="director_email" value="<?php echo esc_attr($director_email); ?>">

    <label for="director_phone">Phone:</label>
    <input type="text" id="director_phone" name="director_phone" value="<?php echo esc_attr($director_phone); ?>">
<?php
}

function directors_save_meta_data($post_id)
{
    if (isset($_POST['director_email'])) {
        update_post_meta($post_id, 'director_email', sanitize_text_field($_POST['director_email']));
    }

    if (isset($_POST['director_phone'])) {
        update_post_meta($post_id, 'director_phone', sanitize_text_field($_POST['director_phone']));
    }
}
 

// Enable drag and drop sorting for Directors post type
function directors_enable_sortable($sortable_columns)
{
    $sortable_columns['menu_order'] = 'menu_order';
    return $sortable_columns;
}
add_filter('manage_edit-directors_sortable_columns', 'directors_enable_sortable');


function directors_change_default_sorting($query)
{
    if (is_admin() && $query->is_main_query() && $query->get('post_type') === 'directors') {
        $query->set('orderby', 'menu_order');
        $query->set('order', 'ASC');
    }
}
add_action('pre_get_posts', 'directors_change_default_sorting');

function directors_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'order' => '', // ASC or DESC
    ), $atts, 'directors');

    $query_args = array(
        'post_type'      => 'directors',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => strtoupper($atts['order']),
    );

    $directors_query = new WP_Query($query_args);

    ob_start();

    if ($directors_query->have_posts()) {
        while ($directors_query->have_posts()) {
            $directors_query->the_post();
            $director_title = get_the_title();
            $director_content = get_the_content();
            $director_image = get_the_post_thumbnail_url();
            $director_email = get_post_meta(get_the_ID(), 'director_email', true);
            $director_phone = get_post_meta(get_the_ID(), 'director_phone', true);

            // Output the director information
            include 'templates/director-output.php';
        }
    }

    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('directors', 'directors_shortcode');
