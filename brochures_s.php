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
add_action('init', 'register_brochures_post_type');
add_action('init', 'register_brochure_taxonomies');

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
        'supports' => array('title', 'editor', 'thumbnail'),
        'taxonomies' => array('brochure_type'),
    );
    register_post_type('brochure', $args);
}

function register_brochure_taxonomies() {
    // Register custom taxonomy for brochure types
    $args = array(
        'labels' => array(
            'name' => 'Brochure Types',
            'singular_name' => 'Brochure Type',
            'search_items' => 'Search Brochure Types',
            'all_items' => 'All Brochure Types',
            'parent_item' => 'Parent Brochure Type',
            'parent_item_colon' => 'Parent Brochure Type:',
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
add_action( 'add_meta_boxes', 'brochures_add_meta_boxes' );

// Meta Box Callback
function brochure_details_callback( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'brochure_nonce' );
    $brochure_stored_meta = get_post_meta( $post->ID );
    ?>

    <p>
        <label for="brochure-type" style="display:block;"><strong>Brochure Type</strong></label>
        <?php
        $brochure_types = get_terms( array(
            'taxonomy' => 'brochure-type',
            'hide_empty' => false,
        ));
        ?>
        <select name="brochure-type" id="brochure-type">
            <?php foreach ($brochure_types as $type) : ?>
                <option value="<?php echo esc_attr( $type->slug ); ?>" <?php if ( isset ( $brochure_stored_meta['brochure-type'] ) ) selected( $brochure_stored_meta['brochure-type'][0], $type->slug ); ?>>
                    <?php echo esc_html( $type->name ); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="brochure-file" style="display:block;"><strong>Upload Brochure File (PDF)</strong></label>
        <input type="file" name="brochure-file" id="brochure-file" value="<?php if ( isset ( $brochure_stored_meta['brochure-file'] ) ) echo esc_attr( $brochure_stored_meta['brochure-file'][0] ); ?>" />
    </p>

    <p>
        <label for="brochure-images" style="display:block;"><strong>Upload Brochure Images</strong></label>
        <input type="file" name="brochure-images[]" id="brochure-images" multiple />
    </p>

    <p>
        <label for="viewport" style="display:block;"><strong>Viewport Orientation</strong></label>
        <select name="viewport" id="viewport">
            <option value="landscape" <?php if ( isset ( $brochure_stored_meta['viewport'] ) ) selected( $brochure_stored_meta['viewport'][0], 'landscape' ); ?>>Landscape</option>
            <option value="portrait" <?php if ( isset ( $brochure_stored_meta['viewport'] ) ) selected( $brochure_stored_meta['viewport'][0], 'portrait' ); ?>>Portrait</option>
        </select>
    </p>

    <p>
        <label for="brochure-subtitle" style="display:block;"><strong>Subtitle</strong></label>
        <input type="text" name="brochure-subtitle" id="brochure-subtitle" value="<?php if ( isset ( $brochure_stored_meta['brochure-subtitle'] ) ) echo esc_attr( $brochure_stored_meta['brochure-subtitle'][0] ); ?>" />
    </p>

    <?php
}

// Save the Meta Box Data
function save_brochure_meta( $post_id ) {
    // Check for nonce
    if ( ! isset( $_POST['brochure_nonce'] ) || ! wp_verify_nonce( $_POST['brochure_nonce'], basename( __FILE__ ) ) ) {
        return;
    }

    // Check for autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check permissions
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Save brochure type
    if ( isset( $_POST['brochure-type'] ) ) {
        update_post_meta( $post_id, 'brochure-type', sanitize_text_field( $_POST['brochure-type'] ) );
    }

    // Save brochure file
    if ( isset( $_FILES['brochure-file'] ) && !empty($_FILES['brochure-file']['name']) ) {
        $uploaded_file = wp_handle_upload( $_FILES['brochure-file'], array( 'test_form' => false ) );
        if ( $uploaded_file && ! isset( $uploaded_file['error'] ) ) {
            update_post_meta( $post_id, 'brochure-file', $uploaded_file['url'] );
        }
    }

    // Save multiple images
    if ( isset( $_FILES['brochure-images'] ) ) {
        $image_urls = array();
        foreach ( $_FILES['brochure-images']['name'] as $key => $value ) {
            if ( $_FILES['brochure-images']['name'][$key] ) {
                $file = array(
                    'name'     => $_FILES['brochure-images']['name'][$key],
                    'type'     => $_FILES['brochure-images']['type'][$key],
                    'tmp_name' => $_FILES['brochure-images']['tmp_name'][$key],
                    'error'    => $_FILES['brochure-images']['error'][$key],
                    'size'     => $_FILES['brochure-images']['size'][$key]
                );

                $upload = wp_handle_upload( $file, array( 'test_form' => false ) );
                if ( $upload && ! isset( $upload['error'] ) ) {
                    $image_urls[] = $upload['url'];
                }
            }
        }
        update_post_meta( $post_id, 'brochure-images', $image_urls );
    }

    // Save viewport orientation
    if ( isset( $_POST['viewport'] ) ) {
        update_post_meta( $post_id, 'viewport', sanitize_text_field( $_POST['viewport'] ) );
    }

    // Save subtitle
    if ( isset( $_POST['brochure-subtitle'] ) ) {
        update_post_meta( $post_id, 'brochure-subtitle', sanitize_text_field( $_POST['brochure-subtitle'] ) );
    }
}
add_action( 'save_post', 'save_brochure_meta' );
