<?php
if (!defined('WP_CLI')) {
  if (!current_user_can('shop_manager') && !current_user_can('administrator')) exit("No permissions\n");

  if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    ?>
    <form method="post">
      <input type="submit" value="Clean">
    </form>
    <?php
  }

  if ($_SERVER['REQUEST_METHOD'] != 'POST') exit;
}

global $wpdb;

if (!isset($wpdb->woocommerce_termmeta)) exit("WooCommerce plugin is not active");

wc1c_disable_time_limit();

$rows = $wpdb->get_results("SELECT term_id, taxonomy FROM $wpdb->woocommerce_termmeta JOIN $wpdb->term_taxonomy ON woocommerce_term_id = term_id WHERE meta_key = 'wc1c_guid'");
foreach ($rows as $row) {
  wp_delete_term($row->term_id, $row->taxonomy);
}

$attribute_ids = get_option('wc1c_guid_attributes', array());
foreach ($attribute_ids as $attribute_id) {
  wc1c_delete_woocommerce_attribute($attribute_id);
}
delete_transient('wc_attribute_taxonomies');

$option_names = $wpdb->get_col("SELECT option_name FROM $wpdb->options WHERE option_name LIKE 'wc1c_%'");
foreach ($option_names as $option_name) {
  delete_option($option_name);
}

$post_ids = $wpdb->get_col("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wc1c_guid'");
foreach ($post_ids as $post_id) {
  $post_attachments = get_attached_media('image', $post_id);
  foreach ($post_attachments as $post_attachment) {
    wp_delete_attachment($post_attachment->ID, true);
  }

  wp_delete_post($post_id, true);
}

$post_ids = $wpdb->get_col("SELECT DISTINCT post_id FROM $wpdb->postmeta WHERE meta_key = 'wc1c_querying' OR meta_key = 'wc1c_queried'");
foreach ($post_ids as $post_id) {
  wp_delete_post($post_id, true);
}

echo defined('WP_CLI') ? "\x07" : "Done";
