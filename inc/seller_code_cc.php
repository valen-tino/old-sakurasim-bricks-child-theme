<?php 

// Function to preselect value based on URL parameter and ensure it persists
function preselect_based_on_url_parameter_and_persist() {
    if (isset($_GET['seller_code'])) {
        $seller_code = sanitize_text_field($_GET['seller_code']);
        $seller_name = '';
        $is_seller_found = false;

        // Query for a post with the matching title as 'seller_code'
        $args = array(
            'post_type' => 'registration-code',
            'posts_per_page' => -1,
            'title' => $seller_code
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()): $query->the_post();
                $seller_name = get_field('seller_name'); // Get 'seller_name' ACF field
                $is_seller_found = true;
                break; // Break after finding the first match
            endwhile;
        }
        wp_reset_postdata();

        // Adjusted inline script for pre-selection, alert, and URL persistence
        echo "<script type='text/javascript'>
            document.addEventListener('DOMContentLoaded', function() {
                var sellerCode = '" . esc_js($seller_code) . "';
                var sellerName = '" . esc_js($seller_name) . "';
                var isSellerFound = " . json_encode($is_seller_found) . ";

                if (isSellerFound) {                    
                    var select = document.getElementById('registration_code');
                    for (var i = 0; i < select.options.length; i++) {
                        if (select.options[i].value === sellerCode) {
                            select.options[i].selected = true;
                            select.readonly = true;
                            break;
                        }
                    }
                }

                // Append seller_code to URLs to ensure it persists
                var links = document.querySelectorAll('a');
                links.forEach(function(link) {
                    var currentUrl = link.href;
                    if (currentUrl.includes('?')) {
                        link.href = currentUrl + '&seller_code=' + sellerCode;
                    } else {
                        link.href = currentUrl + '?seller_code=' + sellerCode;
                    }
                });
            });
        </script>";
    }
}

// Hook the above function into WordPress
add_action('wp_footer', 'preselect_based_on_url_parameter_and_persist');

function ensure_seller_code_persistence() {
    // Check if the seller_code parameter is present in the URL
    if (isset($_GET['seller_code'])) {
        $seller_code = sanitize_text_field($_GET['seller_code']);

        // Inject JavaScript to append 'seller_code' to all site links, forms' action URLs, and during AJAX requests
        echo "<script type='text/javascript'>
            document.addEventListener('DOMContentLoaded', function() {
                var sellerCode = '{$seller_code}';

                // Function to update or append seller_code to a URL
                function updateSellerCode(url) {
                    var urlObj = new URL(url, window.location.href);
                    urlObj.searchParams.set('seller_code', sellerCode); // Set or update the seller_code parameter
                    return urlObj.href;
                }

                // Update all anchor tags
                document.querySelectorAll('a').forEach(function(link) {
                    link.href = updateSellerCode(link.href);
                });

                // Update form actions
                document.querySelectorAll('form').forEach(function(form) {
                    form.action = updateSellerCode(form.action);
                });

                // Intercept AJAX requests to append or update seller_code
                var originalOpen = XMLHttpRequest.prototype.open;
                XMLHttpRequest.prototype.open = function(method, url, async, user, password) {
                    arguments[1] = updateSellerCode(url); // Update the URL
                    originalOpen.apply(this, arguments);
                };
            });
        </script>";
    }
}
add_action('wp_footer', 'ensure_seller_code_persistence');

// Shortcode to show seller name on frontend
function sakura_show_seller_name_shortcode() {
    // Check if 'seller_code' is present in the URL
    $seller_code = isset($_GET['seller_code']) ? sanitize_text_field($_GET['seller_code']) : '';

    if (!empty($seller_code)) {
        // Arguments for WP_Query
        $args = array(
            'post_type' => 'registration-code', // Your custom post type
            'posts_per_page' => 1,
            'title' => $seller_code,
            'fields' => 'ids', // Only get post IDs to improve performance
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            $posts = $query->posts;
            $post_id = array_shift($posts); // Get the first post ID
            $seller_name = get_field('seller_name', $post_id); // Assuming ACF is used

            if (!empty($seller_name)) {
                return esc_html($seller_name);
            }
        }
    }

    return 'none'; // Fallback message
}
add_shortcode('sakura_seller_name', 'sakura_show_seller_name_shortcode');

// Check if 'seller_code' exists in the URL
function check_if_seller_code_exists() {
    // Check if 'seller_code' exists in the URL and is not empty
    if ( isset( $_GET['seller_code'] ) && ! empty( $_GET['seller_code'] ) ) {
        // Additional check: return "0" if 'seller_code' value is "Seller not found"
        if ( $_GET['seller_code'] === "none" ) {
            return "0"; // 'seller_code' is "Seller not found"
        }
        return "1"; // 'seller_code' is present and not "Seller not found"
    }
    return "0"; // 'seller_code' is not present or is empty
}
