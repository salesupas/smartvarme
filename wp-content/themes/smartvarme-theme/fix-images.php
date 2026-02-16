<?php
/**
 * Fix Missing Featured Images
 *
 * Visit: http://localhost:8080/wp-content/themes/smartvarme-theme/fix-images.php?action=check
 * To fix: Add &action=fix&product_id=X&image_id=Y
 *
 * Note: This script helps reconnect existing images from media library to products
 */

// Load WordPress
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

if (!is_user_logged_in() || !current_user_can('manage_options')) {
    die('Access denied');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Featured Images</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial; padding: 20px; max-width: 1200px; margin: 0 auto; }
        h1 { color: #1e3a8a; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #1e3a8a; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .button { background: #f7a720; color: #1e3a8a; padding: 8px 16px; border: none; cursor: pointer; text-decoration: none; display: inline-block; border-radius: 4px; }
        .button:hover { background: #e89610; }
        img { max-width: 100px; height: auto; }
    </style>
</head>
<body>
    <h1>🔧 Fix Missing Featured Images</h1>

    <div style="background: #f0f0f0; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="margin-top: 0;">⚙️ Live Site Configuration</h3>
        <form method="post">
            <label for="live_url">Live Site URL:</label><br>
            <input type="text" id="live_url" name="live_url" value="<?php echo esc_attr($live_site_url); ?>" style="width: 400px; padding: 8px; margin: 8px 0;" placeholder="https://smartvarme.no">
            <button type="submit" name="save_live_url" class="button">Save</button>
        </form>
        <p style="margin-bottom: 0; color: #666;"><small>This URL will be used to download missing images from your live site.</small></p>
    </div>

<?php

$action = isset($_GET['action']) ? $_GET['action'] : 'check';

// Configuration: Live site URL
$live_site_url = get_option('smartvarme_live_url', 'https://smartvarme.no');

// Save live URL if provided
if (isset($_POST['save_live_url'])) {
    $new_live_url = sanitize_text_field($_POST['live_url']);
    update_option('smartvarme_live_url', $new_live_url);
    $live_site_url = $new_live_url;
    echo '<p class="success">✓ Live URL saved: ' . $live_site_url . '</p>';
}

// Set placeholder image for all products without featured images
if ($action === 'set_placeholder_all') {
    echo '<h2>🖼️ Setting Placeholder Image</h2>';
    echo '<p>Adding "Bilde mangler" placeholder to all products without featured images...</p>';
    echo '<div style="background:#f9f9f9;padding:15px;border-radius:8px;max-height:400px;overflow-y:auto;margin:20px 0;">';

    // Check if placeholder exists, if not upload it
    $placeholder_path = ABSPATH . 'wp-content/uploads/2026/02/bilde-mangler.jpg';
    $placeholder_id = null;

    // Search for existing placeholder
    $existing = $wpdb->get_var(
        "SELECT ID FROM {$wpdb->posts}
        WHERE post_type = 'attachment'
        AND post_title = 'Bilde mangler - Smart Varme'
        LIMIT 1"
    );

    if ($existing) {
        $placeholder_id = $existing;
        echo '<p class="success">✓ Using existing placeholder image (ID: ' . $placeholder_id . ')</p>';
    } elseif (file_exists($placeholder_path)) {
        // Upload the placeholder image
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $wp_filetype = wp_check_filetype(basename($placeholder_path), null);
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title'     => 'Bilde mangler - Smart Varme',
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        $placeholder_id = wp_insert_attachment($attachment, $placeholder_path);

        if (!is_wp_error($placeholder_id)) {
            $attach_data = wp_generate_attachment_metadata($placeholder_id, $placeholder_path);
            wp_update_attachment_metadata($placeholder_id, $attach_data);
            echo '<p class="success">✓ Uploaded placeholder image (ID: ' . $placeholder_id . ')</p>';
        }
    } else {
        echo '<p class="error">✗ Placeholder image not found at: ' . $placeholder_path . '</p>';
        echo '<p>Please upload "bilde mangler.jpg" to wp-content/uploads/2026/02/</p>';
        echo '</div>';
        echo '<p><a href="?action=check" class="button">← Back</a></p>';
        return;
    }

    // Get all products without featured images
    $products_without_thumbs = $wpdb->get_results(
        "SELECT p.ID, p.post_title
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_thumbnail_id'
        WHERE p.post_type = 'product'
        AND p.post_status = 'publish'
        AND (pm.meta_value IS NULL OR pm.meta_value = '')
        ORDER BY p.post_title"
    );

    $success_count = 0;
    $error_count = 0;

    foreach ($products_without_thumbs as $prod) {
        echo '<p><strong>' . $prod->post_title . '</strong> (ID: ' . $prod->ID . ')<br>';

        $result = set_post_thumbnail($prod->ID, $placeholder_id);

        if ($result) {
            echo '<span class="success">✓ Placeholder set!</span></p>';
            $success_count++;
        } else {
            echo '<span class="error">✗ Failed to set placeholder</span></p>';
            $error_count++;
        }
    }

    echo '</div>';
    echo '<h3>Summary</h3>';
    echo '<p class="success">✓ Successfully set placeholder on: ' . $success_count . ' products</p>';
    if ($error_count > 0) {
        echo '<p class="error">✗ Errors: ' . $error_count . ' products</p>';
    }
    echo '<p><a href="?action=check" class="button">← Back to Check</a></p>';
}

// Download ALL missing images
if ($action === 'download_all') {
    echo '<h2>📥 Bulk Download from Live Site</h2>';
    echo '<p>Starting bulk download process...</p>';
    echo '<div style="background:#f9f9f9;padding:15px;border-radius:8px;max-height:400px;overflow-y:auto;margin:20px 0;">';

    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    // Get all products with missing files
    $all_products = $wpdb->get_results(
        "SELECT p.ID, p.post_title, pm.meta_value as thumbnail_id
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_thumbnail_id'
        WHERE p.post_type = 'product'
        AND p.post_status = 'publish'
        AND pm.meta_value != ''
        ORDER BY p.post_title"
    );

    $success_count = 0;
    $error_count = 0;
    $skipped_count = 0;

    foreach ($all_products as $prod) {
        $file_path = get_attached_file($prod->thumbnail_id);
        if (!file_exists($file_path)) {
            $image_url = wp_get_attachment_url($prod->thumbnail_id);
            $live_image_url = str_replace('http://localhost:8080', rtrim($live_site_url, '/'), $image_url);

            echo '<p><strong>' . $prod->post_title . '</strong> (ID: ' . $prod->ID . ')<br>';
            echo '<small>Downloading: ' . basename($live_image_url) . '</small><br>';

            // Download and attach to product
            $attachment_id = media_sideload_image($live_image_url, $prod->ID, $prod->post_title, 'id');

            if (is_wp_error($attachment_id)) {
                echo '<span class="error">✗ Failed: ' . $attachment_id->get_error_message() . '</span></p>';
                $error_count++;
            } else {
                // Set as featured image
                $result = set_post_thumbnail($prod->ID, $attachment_id);
                if ($result) {
                    echo '<span class="success">✓ Downloaded and set as featured!</span></p>';
                    $success_count++;
                } else {
                    echo '<span class="error">✗ Downloaded but failed to set as featured</span></p>';
                    $error_count++;
                }
            }

            // Small delay to avoid overwhelming the server
            usleep(500000); // 0.5 seconds
        } else {
            $skipped_count++;
        }
    }

    echo '</div>';
    echo '<h3>Summary</h3>';
    echo '<p class="success">✓ Successfully fixed: ' . $success_count . ' products</p>';
    if ($error_count > 0) {
        echo '<p class="error">✗ Errors: ' . $error_count . ' products</p>';
    }
    if ($skipped_count > 0) {
        echo '<p>Skipped (already had files): ' . $skipped_count . ' products</p>';
    }
    echo '<p><a href="?action=check" class="button">← Back to Check</a></p>';
}

// Download from live and set as featured (single product)
if ($action === 'download' && isset($_GET['product_id']) && isset($_GET['image_url'])) {
    $product_id = intval($_GET['product_id']);
    $image_url = urldecode($_GET['image_url']);

    echo '<h2>Downloading Image from Live...</h2>';
    echo '<p>Product ID: ' . $product_id . '</p>';
    echo '<p>Image URL: ' . $image_url . '</p>';

    // Replace localhost with live URL
    $live_image_url = str_replace('http://localhost:8080', rtrim($live_site_url, '/'), $image_url);
    echo '<p>Live Image URL: ' . $live_image_url . '</p>';

    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    // Download and attach to product
    $attachment_id = media_sideload_image($live_image_url, $product_id, get_the_title($product_id), 'id');

    if (is_wp_error($attachment_id)) {
        echo '<p class="error">✗ Download failed: ' . $attachment_id->get_error_message() . '</p>';
    } else {
        echo '<p class="success">✓ Image downloaded! Attachment ID: ' . $attachment_id . '</p>';

        // Set as featured image
        $result = set_post_thumbnail($product_id, $attachment_id);

        if ($result) {
            echo '<p class="success">✓ Featured image set successfully!</p>';
            echo '<p>Product: <a href="' . get_permalink($product_id) . '" target="_blank">' . get_the_title($product_id) . '</a></p>';
        } else {
            echo '<p class="error">✗ Failed to set featured image</p>';
        }
    }
    echo '<p><a href="?action=check" class="button">← Back to Check</a></p>';
}

if ($action === 'fix' && isset($_GET['product_id']) && isset($_GET['image_id'])) {
    $product_id = intval($_GET['product_id']);
    $image_id = intval($_GET['image_id']);

    $result = set_post_thumbnail($product_id, $image_id);

    if ($result) {
        echo '<p class="success">✓ Featured image set successfully!</p>';
        echo '<p>Product: <a href="' . get_permalink($product_id) . '" target="_blank">' . get_the_title($product_id) . '</a></p>';
    } else {
        echo '<p class="error">✗ Failed to set featured image</p>';
    }
    echo '<p><a href="?action=check" class="button">← Back to Check</a></p>';
}

// Check the specific Firepot product
echo '<h2>Specific Product: Utepeis Morsø Firepot</h2>';

$firepot = get_page_by_path('utepeis-morso-firepot', OBJECT, 'product');

if ($firepot) {
    $thumb_id = get_post_thumbnail_id($firepot->ID);

    echo '<table>';
    echo '<tr><th>Property</th><th>Value</th></tr>';
    echo '<tr><td><strong>Product ID</strong></td><td>' . $firepot->ID . '</td></tr>';
    echo '<tr><td><strong>Title</strong></td><td>' . $firepot->post_title . '</td></tr>';
    echo '<tr><td><strong>URL</strong></td><td><a href="' . get_permalink($firepot->ID) . '" target="_blank">View Product</a></td></tr>';
    echo '<tr><td><strong>Current Featured Image</strong></td><td>' . ($thumb_id ? $thumb_id . ' (exists)' : '<span class="error">NONE</span>') . '</td></tr>';
    echo '</table>';

    // Search for the image
    echo '<h3>Searching for: Firepot-Morso-miljobilde-y.jpg</h3>';

    global $wpdb;
    $images = $wpdb->get_results(
        "SELECT ID, post_title, guid
        FROM {$wpdb->posts}
        WHERE post_type = 'attachment'
        AND guid LIKE '%Firepot-Morso%'
        LIMIT 10"
    );

    if ($images) {
        echo '<table>';
        echo '<tr><th>Image ID</th><th>Title</th><th>URL</th><th>File Path</th><th>Exists?</th><th>Preview</th><th>Action</th></tr>';
        foreach ($images as $img) {
            // Get file path
            $file_path = get_attached_file($img->ID);
            $file_exists = file_exists($file_path);

            // Get various image sizes
            $full_url = wp_get_attachment_url($img->ID);
            $thumb_url = wp_get_attachment_thumb_url($img->ID);
            $medium_url = wp_get_attachment_image_url($img->ID, 'medium');

            echo '<tr>';
            echo '<td>' . $img->ID . '</td>';
            echo '<td>' . $img->post_title . '</td>';
            echo '<td><a href="' . $full_url . '" target="_blank">View Full</a></td>';
            echo '<td><small>' . str_replace($_SERVER['DOCUMENT_ROOT'], '', $file_path) . '</small></td>';
            echo '<td>' . ($file_exists ? '<span class="success">✓ Yes</span>' : '<span class="error">✗ No</span>') . '</td>';
            echo '<td>';
            if ($file_exists) {
                // Try multiple image sizes
                $img_src = $medium_url ? $medium_url : ($thumb_url ? $thumb_url : $full_url);
                echo '<img src="' . esc_url($img_src) . '" style="max-width:120px;height:auto;" onerror="this.src=\'' . esc_url($full_url) . '\'" />';
            } else {
                echo '<span class="error">File missing on disk</span>';
            }
            echo '</td>';
            if ($thumb_id != $img->ID) {
                echo '<td><a href="?action=fix&product_id=' . $firepot->ID . '&image_id=' . $img->ID . '" class="button">Set as Featured</a></td>';
            } else {
                echo '<td><span class="success">Current</span></td>';
            }
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p class="error">❌ No matching images found in media library</p>';
    }
} else {
    echo '<p class="error">Product not found</p>';
}

// Find all products with MISSING image files on disk
echo '<h2>🔴 Products with Missing Image Files on Disk</h2>';

$all_products = $wpdb->get_results(
    "SELECT p.ID, p.post_title, p.post_name, pm.meta_value as thumbnail_id
    FROM {$wpdb->posts} p
    INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_thumbnail_id'
    WHERE p.post_type = 'product'
    AND p.post_status = 'publish'
    AND pm.meta_value != ''
    ORDER BY p.post_title"
);

$products_with_missing_files = array();

foreach ($all_products as $prod) {
    $file_path = get_attached_file($prod->thumbnail_id);
    if (!file_exists($file_path)) {
        $products_with_missing_files[] = array(
            'product_id' => $prod->ID,
            'product_title' => $prod->post_title,
            'product_slug' => $prod->post_name,
            'product_url' => get_permalink($prod->ID),
            'thumbnail_id' => $prod->thumbnail_id,
            'expected_path' => $file_path,
            'image_url' => wp_get_attachment_url($prod->thumbnail_id),
        );
    }
}

echo '<p>Found: <strong>' . count($products_with_missing_files) . '</strong> products with missing image files</p>';

if ($products_with_missing_files) {
    echo '<p><a href="?action=download_all" class="button" style="background:#4CAF50;color:white;font-size:16px;padding:12px 24px;">📥 Download All ' . count($products_with_missing_files) . ' Missing Images</a></p>';
    echo '<p style="color:#666;"><small>⚠️ This will download all images from live site and may take a few minutes. Each image has a 0.5 second delay to avoid server overload.</small></p>';

    echo '<table>';
    echo '<tr><th>Product ID</th><th>Product</th><th>Image ID</th><th>Expected Path</th><th>Image URL</th><th>Actions</th></tr>';
    foreach ($products_with_missing_files as $item) {
        echo '<tr>';
        echo '<td>' . $item['product_id'] . '</td>';
        echo '<td><a href="' . $item['product_url'] . '" target="_blank">' . $item['product_title'] . '</a></td>';
        echo '<td>' . $item['thumbnail_id'] . '</td>';
        echo '<td><small>' . str_replace($_SERVER['DOCUMENT_ROOT'], '', $item['expected_path']) . '</small></td>';
        echo '<td><a href="' . $item['image_url'] . '" target="_blank">View URL</a></td>';
        echo '<td>';
        echo '<a href="?action=download&product_id=' . $item['product_id'] . '&image_url=' . urlencode($item['image_url']) . '" class="button" style="background: #4CAF50;">📥 Download from Live</a><br>';
        echo '<a href="' . admin_url('post.php?post=' . $item['product_id'] . '&action=edit') . '" target="_blank" class="button" style="margin-top:4px;">Edit Product</a>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';

    // Export as CSV
    echo '<h3>Export List</h3>';
    echo '<textarea style="width:100%;height:200px;font-family:monospace;font-size:12px;">';
    echo "Product ID,Product Title,Product Slug,Product URL,Image ID,Expected Path,Image URL\n";
    foreach ($products_with_missing_files as $item) {
        echo $item['product_id'] . ',';
        echo '"' . str_replace('"', '""', $item['product_title']) . '",';
        echo $item['product_slug'] . ',';
        echo $item['product_url'] . ',';
        echo $item['thumbnail_id'] . ',';
        echo '"' . str_replace('"', '""', $item['expected_path']) . '",';
        echo $item['image_url'] . "\n";
    }
    echo '</textarea>';
} else {
    echo '<p class="success">✓ All products with featured images have valid image files on disk!</p>';
}

// Find all products without featured images
echo '<h2>⚪ Products Without Featured Images (not set)</h2>';

$products_without_thumbs = $wpdb->get_results(
    "SELECT p.ID, p.post_title, p.post_name
    FROM {$wpdb->posts} p
    LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_thumbnail_id'
    WHERE p.post_type = 'product'
    AND p.post_status = 'publish'
    AND (pm.meta_value IS NULL OR pm.meta_value = '')
    ORDER BY p.post_title
    LIMIT 100"
);

echo '<p>Found: <strong>' . count($products_without_thumbs) . '</strong> products without featured images set</p>';

if ($products_without_thumbs) {
    echo '<p><a href="?action=set_placeholder_all" class="button" style="background:#ff9800;color:white;font-size:16px;padding:12px 24px;">🖼️ Set Placeholder on All ' . count($products_without_thumbs) . ' Products</a></p>';
    echo '<p style="color:#666;"><small>This will set the "Bilde mangler - Smart Varme" placeholder image on all products without featured images.</small></p>';

    echo '<table>';
    echo '<tr><th>ID</th><th>Product</th><th>Slug</th><th>Action</th></tr>';
    foreach ($products_without_thumbs as $prod) {
        echo '<tr>';
        echo '<td>' . $prod->ID . '</td>';
        echo '<td><a href="' . get_permalink($prod->ID) . '" target="_blank">' . $prod->post_title . '</a></td>';
        echo '<td>' . $prod->post_name . '</td>';
        echo '<td><a href="' . admin_url('post.php?post=' . $prod->ID . '&action=edit') . '" target="_blank">Edit</a></td>';
        echo '</tr>';
    }
    echo '</table>';
}

?>

</body>
</html>
