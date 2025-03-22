<?php
/**
 * Plugin Name: QuickSEO Meta Manager
 * Plugin URI: https://webcreate.kesug.com/
 * Description: A lightweight WordPress plugin for managing meta tags (title, description, Open Graph, Twitter Cards) with an SEO score indicator.
 * Version: 1.2
 * Author: Anthony Agughasi
 * Author URI: https://webcreate.kesug.com/
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Add meta box to post and pages
function quickseo_add_meta_box() {
    add_meta_box('quickseo_meta_box', 'QuickSEO Meta Tags', 'quickseo_meta_box_callback', ['post', 'page'], 'normal', 'high');
}
add_action('add_meta_boxes', 'quickseo_add_meta_box');

// Meta box callback
function quickseo_meta_box_callback($post) {
    $meta_title = get_post_meta($post->ID, '_quickseo_meta_title', true);
    $meta_desc = get_post_meta($post->ID, '_quickseo_meta_desc', true);
    $og_image = get_post_meta($post->ID, '_quickseo_og_image', true);
    ?>
    <p>
        <label for="quickseo_meta_title">Meta Title:</label><br>
        <input type="text" id="quickseo_meta_title" name="quickseo_meta_title" value="<?php echo esc_attr($meta_title); ?>" size="80" onkeyup="quickseo_calculate_score()" />
    </p>
    <p>
        <label for="quickseo_meta_desc">Meta Description:</label><br>
        <textarea id="quickseo_meta_desc" name="quickseo_meta_desc" rows="3" cols="80" onkeyup="quickseo_calculate_score()"><?php echo esc_textarea($meta_desc); ?></textarea>
    </p>
    <p>
        <label for="quickseo_og_image">Open Graph Image URL:</label><br>
        <input type="text" id="quickseo_og_image" name="quickseo_og_image" value="<?php echo esc_attr($og_image); ?>" size="80" />
    </p>
    <p><strong>SEO Score: <span id="quickseo_score">0</span>/100</strong></p>
    <script>
        function quickseo_calculate_score() {
            let title = document.getElementById('quickseo_meta_title').value.trim();
            let desc = document.getElementById('quickseo_meta_desc').value.trim();
            let score = 0;
            
            if (title.length >= 50 && title.length <= 60) score += 40;
            if (desc.length >= 150 && desc.length <= 160) score += 40;
            if (title && desc) score += 20;
            
            document.getElementById('quickseo_score').innerText = score;
        }
    </script>
    <?php
}

// Save meta box data
function quickseo_save_meta_data($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (isset($_POST['quickseo_meta_title'])) {
        update_post_meta($post_id, '_quickseo_meta_title', sanitize_text_field($_POST['quickseo_meta_title']));
    }
    if (isset($_POST['quickseo_meta_desc'])) {
        update_post_meta($post_id, '_quickseo_meta_desc', sanitize_textarea_field($_POST['quickseo_meta_desc']));
    }
    if (isset($_POST['quickseo_og_image'])) {
        update_post_meta($post_id, '_quickseo_og_image', esc_url($_POST['quickseo_og_image']));
    }
}
add_action('save_post', 'quickseo_save_meta_data');

// Output meta tags in head
function quickseo_add_meta_tags() {
    if (is_single() || is_page()) {
        global $post;
        $meta_title = get_post_meta($post->ID, '_quickseo_meta_title', true);
        $meta_desc = get_post_meta($post->ID, '_quickseo_meta_desc', true);
        $og_image = get_post_meta($post->ID, '_quickseo_og_image', true);
        
        if ($meta_title) {
            echo '<title>' . esc_html($meta_title) . '</title>' . "
";
        }
        if ($meta_desc) {
            echo '<meta name="description" content="' . esc_attr($meta_desc) . '">' . "
";
        }
        if ($og_image) {
            echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "
";
        }
        echo '<meta property="og:title" content="' . esc_attr($meta_title) . '">' . "
";
        echo '<meta property="og:description" content="' . esc_attr($meta_desc) . '">' . "
";
        echo '<meta name="twitter:card" content="summary_large_image">' . "
";
        echo '<meta name="twitter:title" content="' . esc_attr($meta_title) . '">' . "
";
        echo '<meta name="twitter:description" content="' . esc_attr($meta_desc) . '">' . "
";
        if ($og_image) {
            echo '<meta name="twitter:image" content="' . esc_url($og_image) . '">' . "
";
        }
    }
}
add_action('wp_head', 'quickseo_add_meta_tags');
