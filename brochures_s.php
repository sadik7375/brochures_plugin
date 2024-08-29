<?php
/*
 * Plugin Name:       brochures_s plugin
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Handle the basics with this plugin.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            mediusware
 * Author URI:        https://author.example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       my-basics-plugin
 * Domain Path:       /languages
 */

// Hook into the 'init' action to register custom post type and taxonomies
function register_brochures_post_type() {
    $args = array(
        'labels' => array(
            'name' => 'Brochures',
            'singular_name' => 'Brochure',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Brochure',
            'edit_item' => 'Edit Brochure',
            'new_item' => 'New Brochure',
            'view_item' => 'View Brochure',
            'search_items' => 'Search Brochures',
            'not_found' => 'No brochures found',
            'not_found_in_trash' => 'No brochures found in Trash',
            'all_items' => 'All Brochures',
            'menu_name' => 'Brochures',
            'name_admin_bar' => 'Brochure',
        ),
        'public' => true,
        'has_archive' => true,
        'menu_position' => 5,
        'supports' => array('thumbnail'),
        'taxonomies' => array('brochure_type'),
    );
    register_post_type('brochure', $args);
}
add_action('init', 'register_brochures_post_type');

// Register Brochure Type Taxonomy
function register_brochure_taxonomies() {
    $args = array(
        'labels' => array(
            'name' => 'Brochure Types',
            'singular_name' => 'Brochure Type',
            'search_items' => 'Search Brochure Types',
            'all_items' => 'All Brochure Types',
            'edit_item' => 'Edit Brochure Type',
            'update_item' => 'Update Brochure Type',
            'add_new_item' => 'Add New Brochure Type',
            'new_item_name' => 'New Brochure Type Name',
            'menu_name' => 'Brochure Types',
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'brochure-type'),
    );
    register_taxonomy('brochure_type', array('brochure'), $args);
}
add_action('init', 'register_brochure_taxonomies');

function add_brochure_columns($columns) {
    $columns['Brochure'] = 'Brochure';
    $columns['slider_image'] = 'Slider Image';
    $columns['slider_image_portrait'] = 'Slider Image Portrait';
    $columns['teaser_image'] = 'Teaser Image';
    $columns['format'] = 'Format';
    $columns['headline'] = 'Headline';
    $columns['subheadline'] = 'Subheadline';
    $columns['starting_from'] = 'Starting From';
    $columns['end'] = 'Ending';
    
    return $columns;
}
add_filter('manage_brochure_posts_columns', 'add_brochure_columns');


// Populate custom columns with ACF field values
function manage_brochure_columns($column, $post_id) {
    switch ($column) {
        case 'brochure_file':
        $brochure_file_url = get_post_meta($post_id, 'brochure_file', true);
        if ($brochure_file_url) {
            echo '<a href="' . esc_url($brochure_file_url) . '" target="_blank">View File</a>';
        } else {
            echo 'No file';
        }
        break;

    case 'slider_image':
        $slider_image = get_post_meta($post_id, 'slider_image', true);
        if (is_array($slider_image) && isset($slider_image['url'])) {
            echo esc_url($slider_image['url']);
        } else {
            echo 'No image';
        }
        break;

    case 'slider_image_portrait':
        $slider_image_portrait = get_post_meta($post_id, 'slider_image_portrait', true);
        if (is_array($slider_image_portrait) && isset($slider_image_portrait['url'])) {
            echo esc_url($slider_image_portrait['url']);
        } else {
            echo 'No image';
        }
        break;

    case 'teaser_image':
        $teaser_image = get_post_meta($post_id, 'teaser_image_', true);
        if (is_array($teaser_image) && isset($teaser_image['url'])) {
            echo esc_url($teaser_image['url']);
        } else {
            echo 'No image';
        }
        break;



        case 'format':
            $format = get_field('format', $post_id);
            echo esc_html($format ? ucfirst($format) : 'Not Set');
            break;

        case 'headline':
            $headline = get_field('headline', $post_id);
            echo esc_html($headline ? $headline : 'Not Set');
            break;

        case 'subheadline':
            $subheadline = get_field('subheadline', $post_id);
            echo esc_html($subheadline ? $subheadline : 'Not Set');
            break;

        case 'starting_from':
            $starting_from = get_field('starting_from', $post_id);
            echo esc_html($starting_from ? date('Y-m-d H:i:s', strtotime($starting_from)) : 'Not Set');
            break;

        case 'end':
            $end = get_field('end', $post_id);
            echo esc_html($end ? date('Y-m-d H:i:s', strtotime($end)) : 'Not Set');
            break;
    }
}
add_action('manage_brochure_posts_custom_column', 'manage_brochure_columns', 10, 2);



// Shortcode to display brochures
function add_brochure_admin_menu() {
    add_menu_page(
        'Brochure Data',
        'Brochure Data',
        'manage_options',
        'brochure-data',
        'display_brochure_data_page',
        'dashicons-clipboard', // Icon for the menu item
        6 // Position in the menu
    );
}
add_action('admin_menu', 'add_brochure_admin_menu');

// Display the custom field data
function display_brochure_data_page() {
    // Check user permissions
    if (!current_user_can('manage_options')) {
        return;
    }

    // Get all posts of the 'brochure' post type
    $args = array(
        'post_type' => 'brochure',
        'posts_per_page' => -1 // Get all posts
    );
    $posts = get_posts($args);

    ?>
    <div class="wrap">
        <h1>Brochure Data</h1>

        <?php if (!empty($posts)) : ?>
            <?php foreach ($posts as $post) : ?>
                <h2><?php echo esc_html($post->post_title); ?></h2>
                
                <?php
                $image_url = wp_get_attachment_image_url( get_post_meta($post->ID, 'slider_image_portrait', true) );

                print_r($image_url);
                // Retrieve custom fields
                $brochure_file = get_post_meta($post->ID, 'brochure_file', true);
                $slider_images = get_post_meta($post->ID, 'slider_image', true);
                $slider_image_portrait = get_post_meta($post->ID, 'slider_image_portrait', true);
                $teaser_image = get_post_meta($post->ID, 'teaser_image', true);
                $format = get_post_meta($post->ID, 'format', true);
                $home_headline = get_post_meta($post->ID, 'headline', true);
                $home_subline = get_post_meta($post->ID, 'subheadline', true);
                $starting_from = get_post_meta($post->ID, 'starting_from', true);
                // Retrieve the brochure type
                $brochure_types = wp_get_post_terms($post->ID, 'brochure_type', array('fields' => 'names'));

                // Prepare data array
                $data = array(
                    'Brochure File' => $brochure_file,
                    'Slider Images' => is_array($slider_images) ? implode(', ', array_map('esc_url', $slider_images)) : $slider_images,
                    'Slider Image Portrait' => $slider_image_portrait,
                    'Teaser Image' => $teaser_image,
                    'Format' => $format,
                    'Home Headline' => $home_headline,
                    'Home Subline' => $home_subline,
                    'starting_from' => $starting_from,
                    'Brochure Type' => !empty($brochure_types) ? implode(', ', $brochure_types) : 'No type assigned',
                );
                ?>

                <pre><?php echo esc_html(print_r($data, true)); ?></pre>
            <?php endforeach; ?>
        <?php else : ?>
            <p>No brochures found.</p>
        <?php endif; ?>
    </div>
    <?php
}


function display_brochure_shortcode() {
    // Get all posts of the 'brochure' post type
    $args = array(
        'post_type' => 'brochure',
        'posts_per_page' => -1 // Get all posts
    );
    $posts = get_posts($args);

    // Start the output buffer
    ob_start();

    if (!empty($posts)) {
        echo '<div class="brochure-grid">';
        foreach ($posts as $post) {
            // Retrieve custom fields
            $cover_image_id = get_post_meta($post->ID, 'cover_image', true);
            $home_headline = get_post_meta($post->ID, 'headline', true);

            // Get the URL of the cover image using the attachment ID
            $cover_image_url = wp_get_attachment_image_url($cover_image_id, 'medium');

            // Output the brochure content
            echo '<div class="brochure-item">';
            if ($cover_image_url) {
                echo '<img src="' . esc_url($cover_image_url) . '" alt="' . esc_attr($post->post_title) . '" class="brochure-cover">';
            }
            echo '<h3 class="brochure-headline">' . esc_html($home_headline) . '</h3>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<p>No brochures found.</p>';
    }

    // Get the content from the output buffer
    $output = ob_get_clean();

    // Return the output
    return $output;
}
add_shortcode("display_brochures", "display_brochure_shortcode");


function enqueue_brochure_styles() {
    // Define the path to the CSS file
    wp_enqueue_style(
        'brochures-style', 
        plugin_dir_url(__FILE__) . 'css/brochures-style.css', 
        array(), 
        '1.0.0' 
    );
}
add_action('wp_enqueue_scripts', 'enqueue_brochure_styles');







