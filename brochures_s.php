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
        'supports' => array('title', 'thumbnail'),
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

// Add Meta Boxes
function brochures_add_meta_boxes() {
    add_meta_box(
        'brochure_details',
        'Brochure Details',
        'brochure_details_callback',
        'brochure',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'brochures_add_meta_boxes');

// Meta Box Callback
// Meta Box HTML
function brochure_details_callback($post) {
    wp_nonce_field(basename(__FILE__), 'brochure_nonce');
    $brochure_stored_meta = get_post_meta($post->ID);

    ?>
   

    <p>
        <label for="brochure_images" style="display:block;"><strong>Upload Brochure Images</strong></label>
        <input type="file" name="brochure_images" id="brochure-images" />
    </p>

    <!-- Slider Image Portrait Upload -->
    <!-- <p>
        <label for="slider-image-portrait"><strong>Upload Slider Image (Portrait 1:1)</strong></label><br />
        <input type="file" name="slider_image-portrait" id="slider-image-portrait" />
    </p> -->

    <!-- Teaser Image Upload -->
    <!-- <p>
        <label for="teaser-image"><strong>Upload Teaser Image</strong></label><br />
        <input type="file" name="teaser_image" id="teaser-image" />
    </p> -->

    <!-- Format Selection -->
    <p>
        <label for="format"><strong>Format</strong></label><br />
        <select name="format" id="format">
            <option value="landscape" <?php selected($brochure_stored_meta['format'][0] ?? '', 'landscape'); ?>>Landscape</option>
            <option value="portrait" <?php selected($brochure_stored_meta['format'][0] ?? '', 'portrait'); ?>>Portrait</option>
        </select>
    </p>

    <!-- Home Headline Input -->
    <p>
        <label for="home-headline"><strong>Home Headline</strong></label><br />
        <input type="text" name="home-headline" id="home-headline" value="<?php echo esc_attr($brochure_stored_meta['home-headline'][0] ?? ''); ?>" />
    </p>

    <!-- Home Subline Input -->
    <p>
        <label for="home-subline"><strong>Home Subline</strong></label><br />
        <input type="text" name="home-subline" id="home-subline" value="<?php echo esc_attr($brochure_stored_meta['home-subline'][0] ?? ''); ?>" />
    </p>
    <?php
}




// Save Meta Box Data
function save_brochure_meta_box_data($post_id) {
   
    if (isset($_FILES['brochure_images']) && !empty($_FILES['brochure_images']['name'])) {
        $uploadedfile = $_FILES['brochure_images'];
        
        
        // Validate file
        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['basedir'] . '/custom_uploads/';
        
        if (!file_exists($upload_path)) {
            wp_mkdir_p($upload_path);
        }
        
        $file_name = wp_unique_filename($upload_path, basename($uploadedfile['name']));
        $upload_file = $upload_path . $file_name;
        
        if ($uploadedfile['error'] === UPLOAD_ERR_OK) {
            if (move_uploaded_file($uploadedfile['tmp_name'], $upload_file)) {
                $file_url = $upload_dir['baseurl'] . '/custom_uploads/' . $file_name;
                update_post_meta($post_id, '_brochure_file', $file_url);
            } else {
                // Handle file upload error
                echo '<div class="error"><p>Failed to move the file to the upload directory.</p></div>';
            }
        } else {
            // Handle file upload error
            echo '<div class="error"><p>File upload error: ' . $uploadedfile['error'] . '</p></div>';
        }
    }



    // Handle text fields
    $text_fields = array('home-headline', 'home-subline', 'format');
    foreach ($text_fields as $field) {
        if (isset($_POST[$field])) {
            $value = sanitize_text_field($_POST[$field]);
            update_post_meta($post_id, $field, $value);
        }
    }
}
add_action('save_post', 'save_brochure_meta_box_data');






// Define and Manage Columns
function add_brochure_columns($columns) {
    $columns['brochure_file'] = 'Brochure File';
    $columns['slider_images'] = 'Slider Images';
    $columns['slider_image_portrait'] = 'Slider Image Portrait';
    $columns['teaser_image'] = 'Teaser Image';
    $columns['format'] = 'Format';
    $columns['home_headline'] = 'Home Headline';
    $columns['home_subline'] = 'Home Subline';
    
    return $columns;
}
add_filter('manage_brochure_posts_columns', 'add_brochure_columns');

function manage_brochure_columns($column, $post_id) {
    switch ($column) {
        case 'brochure_file':
            $brochure_file = get_post_meta($post_id, 'brochure-file', true);
            if ($brochure_file) {
                echo '<a href="' . esc_url($brochure_file) . '" target="_blank">View PDF</a>';
            } else {
                echo 'No file';
            }
            break;

        case 'slider_images':
            $slider_images = get_post_meta($post_id, 'slider-images', true);
            if (!empty($slider_images) && is_array($slider_images)) {
                foreach ($slider_images as $image_url) {
                    if (filter_var($image_url, FILTER_VALIDATE_URL)) {
                        echo '<img src="' . esc_url($image_url) . '" style="max-width:50px; margin-right:5px;" />';
                    }
                }
            } else {
                echo 'No images';
            }
            break;

        case 'slider_image_portrait':
            $slider_image_portrait = get_post_meta($post_id, 'slider-image-portrait', true);
            if ($slider_image_portrait && filter_var($slider_image_portrait, FILTER_VALIDATE_URL)) {
                echo '<img src="' . esc_url($slider_image_portrait) . '" style="max-width:50px;" />';
            } else {
                echo 'No image';
            }
            break;

        case 'teaser_image':
            $teaser_image = get_post_meta($post_id, 'teaser-image', true);
            if ($teaser_image && filter_var($teaser_image, FILTER_VALIDATE_URL)) {
                echo '<img src="' . esc_url($teaser_image) . '" style="max-width:50px;" />';
            } else {
                echo 'No image';
            }
            break;

        case 'format':
            $format = get_post_meta($post_id, 'format', true);
            echo esc_html($format ?: 'Not set');
            break;

        case 'home_headline':
            $home_headline = get_post_meta($post_id, 'home-headline', true);
            echo esc_html($home_headline ?: 'No headline');
            break;

        case 'home_subline':
            $home_subline = get_post_meta($post_id, 'home-subline', true);
            echo esc_html($home_subline ?: 'No subline');
            break;
    }
} 

add_action('manage_brochure_posts_custom_column', 'manage_brochure_columns', 10, 2);




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
                // Retrieve custom fields
                $brochure_file = get_post_meta($post->ID, 'brochure-file', true);
                $slider_images = get_post_meta($post->ID, 'slider-images', true);
                $slider_image_portrait = get_post_meta($post->ID, 'slider-image-portrait', true);
                $teaser_image = get_post_meta($post->ID, 'teaser-image', true);
                $format = get_post_meta($post->ID, 'format', true);
                $home_headline = get_post_meta($post->ID, 'home-headline', true);
                $home_subline = get_post_meta($post->ID, 'home-subline', true);

                // Retrieve the brochure type
                $brochure_types = wp_get_post_terms($post->ID, 'brochure_type', array('fields' => 'names'));

                // Prepare data array
                $data = array(
                    'Brochure File' => $brochure_file,
                    'Slider Images' => $slider_images,
                    'Slider Image Portrait' => $slider_image_portrait,
                    'Teaser Image' => $teaser_image,
                    'Format' => $format,
                    'Home Headline' => $home_headline,
                    'Home Subline' => $home_subline,
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

