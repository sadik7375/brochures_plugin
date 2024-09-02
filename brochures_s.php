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


 function display_brochure_shortcode() {
    // Get all posts of the 'brocures-s' post type
    $query = new WP_Query(array(
        'post_type' => 'brocures-s',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC'
    ));

    // Start output buffering
    ob_start();

    if ($query->have_posts()) :

        echo '<div class="brochure-grid">';
        

        while ($query->have_posts()) :
            $query->the_post();
            $validity_group = get_field('standard_validity_for_all_branches');
            $valid_from = isset($validity_group['valid_from']) ? $validity_group['valid_from'] : '';
            $valid_until = isset($validity_group['valid_until']) ? $validity_group['valid_until'] : '';
            $current_date = current_time('Ymd'); 
            $basic_information = get_field('basic_information');
            

            
           

            
            
         if ( $current_date >= $valid_from && $current_date <= $valid_until ) {
                // Format the dates and times
                
                $cover_image_url = $basic_information['cover_image'] ;
                $home_headline = $basic_information['title_of_the_prospectus'];
                // Additional brochure details
                echo '<div class="brochure-item">';
                if ($cover_image_url) {
                    echo '<img src="' . esc_url($cover_image_url) . '" alt="' . esc_attr(get_the_title()) . '" class="brochure-cover">';
                }
                echo '<h3 class="brochure-headline">' . esc_html($home_headline) . '</h3>';
                echo '</div>';
    
          
    
            echo '</div>';
            } else {
                // Optionally output something if the post is not within the valid date range
                echo '<p>This brochure is not currently available.</p>';
            }

            echo '</div>';

        endwhile;

        echo '</div>';

        wp_reset_postdata();

    else :
        echo '<p>No brochures found.</p>';
    endif;

    // Return the output buffer content
    return ob_get_clean();
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

