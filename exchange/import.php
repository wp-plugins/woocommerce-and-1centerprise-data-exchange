<?php
if (!defined('ABSPATH')) exit;

require_once ABSPATH . "wp-admin/includes/media.php";
require_once ABSPATH . "wp-admin/includes/file.php";
require_once ABSPATH . "wp-admin/includes/image.php";

function wc1c_import_start_element_handler($is_full, $names, $depth, $name, $attrs) {
  global $wc1c_groups, $wc1c_group_depth, $wc1c_group_order, $wc1c_property, $wc1c_property_order, $wc1c_product;

  if (@$names[$depth - 1] == 'Классификатор' && $name == 'Группы') {
    $wc1c_groups = array();
    $wc1c_group_depth = -1;
    $wc1c_group_order = 1;
  }
  elseif (@$names[$depth - 1] == 'Группы' && $name == 'Группа') {
    $wc1c_group_depth++;
    $wc1c_groups[] = array('ИдРодителя' => @$wc1c_groups[$wc1c_group_depth - 1]['Ид']);
  }
  elseif (@$names[$depth - 1] == 'Группа' && $name == 'Группы') {
    wc1c_replace_group($is_full, $wc1c_groups[$wc1c_group_depth], $wc1c_group_order);
    $wc1c_group_order++;

    $wc1c_groups[$wc1c_group_depth]['Группы'] = true;
  }
  elseif (@$names[$depth - 1] == 'Классификатор' && $name == 'Свойства') {
    $wc1c_property_order = 1;
  }
  elseif (@$names[$depth - 1] == 'Свойства' && $name == 'Свойство') {
    $wc1c_property = array();
  }
  elseif (@$names[$depth - 1] == 'Свойство' && $name == 'ВариантыЗначений') {
    $wc1c_property['ВариантыЗначений'] = array();
  }
  elseif (@$names[$depth - 1] == 'ВариантыЗначений' && $name == 'Справочник') {
    $wc1c_property['ВариантыЗначений'][] = array();
  }
  elseif (@$names[$depth - 1] == 'Товары' && $name == 'Товар') {
    $wc1c_product = array();
  }
  elseif (@$names[$depth - 1] == 'Товар' && $name == 'Группы') {
    $wc1c_product['Группы'] = array();
  }
  elseif (@$names[$depth - 1] == 'Группы' && $name == 'Ид') {
    $wc1c_product['Группы'][] = '';
  }
  elseif (@$names[$depth - 1] == 'Товар' && $name == 'Картинка') {
    if (!isset($wc1c_product['Картинка'])) $wc1c_product['Картинка'] = array();
    $wc1c_product['Картинка'][] = '';
  }
  elseif (@$names[$depth - 1] == 'Товар' && $name == 'Изготовитель') {
    $wc1c_product['Изготовитель'] = array();
  }
  elseif (@$names[$depth - 1] == 'Товар' && $name == 'ЗначенияСвойств') {
    $wc1c_product['ЗначенияСвойств'] = array();
  }
  elseif (@$names[$depth - 1] == 'ЗначенияСвойств' && $name == 'ЗначенияСвойства') {
    $wc1c_product['ЗначенияСвойств'][] = array();
  }
  elseif (@$names[$depth - 1] == 'ЗначенияСвойства' && $name == 'Значение') {
    $i = count($wc1c_product['ЗначенияСвойств']) - 1;
    if (!isset($wc1c_product['ЗначенияСвойств'][$i]['Значение'])) $wc1c_product['ЗначенияСвойств'][$i]['Значение'] = array();
    $wc1c_product['ЗначенияСвойств'][$i]['Значение'][] = '';
  }
  elseif (@$names[$depth - 1] == 'Товар' && $name == 'ЗначенияРеквизитов') {
    $wc1c_product['ЗначенияРеквизитов'] = array();
  }
  elseif (@$names[$depth - 1] == 'ЗначенияРеквизитов' && $name == 'ЗначениеРеквизита') {
    $wc1c_product['ЗначенияРеквизитов'][] = array();
  }
  elseif (@$names[$depth - 1] == 'ЗначениеРеквизита' && $name == 'Значение') {
    $i = count($wc1c_product['ЗначенияРеквизитов']) - 1;
    if (!isset($wc1c_product['ЗначенияРеквизитов'][$i]['Значение'])) $wc1c_product['ЗначенияРеквизитов'][$i]['Значение'] = array();
    $wc1c_product['ЗначенияРеквизитов'][$i]['Значение'][] = '';
  }
}

function wc1c_import_character_data_handler($is_full, $names, $depth, $name, $data) {
  global $wc1c_groups, $wc1c_group_depth, $wc1c_property, $wc1c_product;

  if (@$names[$depth - 2] == 'Группы' && @$names[$depth - 1] == 'Группа' && $name != 'Группы') {
    @$wc1c_groups[$wc1c_group_depth][$name] .= $data;
  }
  elseif (@$names[$depth - 2] == 'Свойства' && @$names[$depth - 1] == 'Свойство' && $name != 'ВариантыЗначений') {
    @$wc1c_property[$name] .= $data;
  }
  elseif (@$names[$depth - 2] == 'ВариантыЗначений' && @$names[$depth - 1] == 'Справочник') {
    $i = count($wc1c_property['ВариантыЗначений']) - 1;
    @$wc1c_property['ВариантыЗначений'][$i][$name] .= $data;
  }
  elseif (@$names[$depth - 2] == 'Товары' && @$names[$depth - 1] == 'Товар' && !in_array($name, array('Группы', 'Картинка', 'Изготовитель', 'ЗначенияСвойств', 'СтавкиНалогов', 'ЗначенияРеквизитов'))) {
    @$wc1c_product[$name] .= $data;
  }
  elseif (@$names[$depth - 2] == 'Товар' && @$names[$depth - 1] == 'Группы' && $name == 'Ид') {
    $i = count($wc1c_product['Группы']) - 1;
    $wc1c_product['Группы'][$i] .= $data;
  }
  elseif (@$names[$depth - 2] == 'Товары' && @$names[$depth - 1] == 'Товар' && $name == 'Картинка') {
    $i = count($wc1c_product['Картинка']) - 1;
    $wc1c_product['Картинка'][$i] .= $data;
  }
  elseif (@$names[$depth - 2] == 'Товар' && @$names[$depth - 1] == 'Изготовитель') {
    @$wc1c_product['Изготовитель'][$name] .= $data;
  }
  elseif (@$names[$depth - 2] == 'ЗначенияСвойств' && @$names[$depth - 1] == 'ЗначенияСвойства') {
    $i = count($wc1c_product['ЗначенияСвойств']) - 1;
    if ($name != 'Значение') {
      @$wc1c_product['ЗначенияСвойств'][$i][$name] .= $data;
    }
    else {
      $j = count($wc1c_product['ЗначенияСвойств'][$i]['Значение']) - 1;
      $wc1c_product['ЗначенияСвойств'][$i]['Значение'][$j] .= $data;
    }
  }
  elseif (@$names[$depth - 2] == 'ЗначенияРеквизитов' && @$names[$depth - 1] == 'ЗначениеРеквизита') {
    $i = count($wc1c_product['ЗначенияРеквизитов']) - 1;
    if ($name != 'Значение') {
      @$wc1c_product['ЗначенияРеквизитов'][$i][$name] .= $data;
    }
    else {
      $j = count($wc1c_product['ЗначенияРеквизитов'][$i]['Значение']) - 1;
      $wc1c_product['ЗначенияРеквизитов'][$i]['Значение'][$j] .= $data;
    }
  }
}

function wc1c_import_end_element_handler($is_full, $names, $depth, $name) {
  global $wc1c_groups, $wc1c_group_depth, $wc1c_group_order, $wc1c_property, $wc1c_property_order, $wc1c_product;

  if (@$names[$depth - 1] == 'Группы' && $name == 'Группа') {
    if (empty($wc1c_groups[$wc1c_group_depth]['Группы'])) {
      wc1c_replace_group($is_full, $wc1c_groups[$wc1c_group_depth], $wc1c_group_order);
      $wc1c_group_order++;
    }

    array_pop($wc1c_groups);
    $wc1c_group_depth--;
  }
  if (@$names[$depth - 1] == 'Классификатор' && $name == 'Группы') {
    wc1c_clean_woocommerce_categories($is_full);
  }
  elseif (@$names[$depth - 1] == 'Свойства' && $name == 'Свойство') {
    $attribute_taxonomy = wc1c_replace_property($is_full, $wc1c_property, $wc1c_property_order);
    $wc1c_property_order++;

    wc1c_clean_woocommerce_attribute_options($attribute_taxonomy);
  }
  elseif (@$names[$depth - 1] == 'Классификатор' && $name == 'Свойства') {
    wc1c_clean_woocommerce_attributes($is_full);

    delete_transient('wc_attribute_taxonomies');
  }
  elseif (@$names[$depth - 1] == 'Товары' && $name == 'Товар') {
    $is_deleted = @$wc1c_product['Статус'] == 'Удален';
    wc1c_replace_product($is_full, $wc1c_product['Ид'], $wc1c_product['Наименование'], $is_deleted, @$wc1c_product['Артикул'], @$wc1c_product['БазоваяЕдиница'], @$wc1c_product['Группы'], @$wc1c_product['Описание'], @$wc1c_product['Картинка'], @$wc1c_product['Изготовитель']['Наименование'], @$wc1c_product['ЗначенияСвойств'], @$wc1c_product['ЗначенияРеквизитов']);
  }
  elseif (@$names[$depth - 1] == 'Каталог' && $name == 'Товары') {
    wc1c_clean_products($is_full);
    wc1c_clean_product_terms();
  }
}

function wc1c_term_id_by_meta($key, $value) {
  global $wpdb;

  if ($value === null) return;

  $cache_key = "wc1c_term_id_by_meta-$key-$value";
  $term_id = wp_cache_get($cache_key);
  if ($term_id === false) {
    $term_id = $wpdb->get_var($wpdb->prepare("SELECT term_id FROM $wpdb->woocommerce_termmeta JOIN $wpdb->terms ON woocommerce_term_id = term_id WHERE meta_key = %s AND meta_value = %s", $key, $value));
    wc1c_check_wpdb_error();

    if ($term_id) wp_cache_set($cache_key, $term_id);
  }

  return $term_id;
}

function wc1c_replace_term($is_full, $guid, $parent_guid, $name, $taxonomy, $order) {
  $term_id = wc1c_term_id_by_meta('wc1c_guid', $guid);

  if (!$term_id) {
    $args = array(
      'parent' => wc1c_term_id_by_meta('wc1c_guid', $parent_guid),
    );
    $result = wp_insert_term($name, $taxonomy, $args);

    if (!is_wp_error($result)) {
      $term_id = $result['term_id'];
      $is_added = true;
    }
    else {
      foreach ($result->get_error_codes() as $error_code) {
        if ($error_code == 'term_exists') {
          $term_id = $result->get_error_data('term_exists');
        }
        else {
          wc1c_wp_error($result, $error_code);
        }
      }
    }

    update_woocommerce_term_meta($term_id, 'wc1c_guid', $guid);
  }

  if (empty($is_added)) {
    $args = array(
      'parent' => wc1c_term_id_by_meta('wc1c_guid', $parent_guid),
      'name' => $name,
      'slug' => sanitize_title($name),
    );
    $result = wp_update_term($term_id, $taxonomy, $args);

    if (is_wp_error($result)) {
      foreach ($result->get_error_codes() as $error_code) {
        if ($error_code == 'duplicate_term_slug') {
          $term = get_term($term_id, $taxonomy);
          wc1c_check_wp_error($term);

          $args['slug'] = wp_unique_term_slug($args['slug'], $term);
          $result = wp_update_term($term_id, $taxonomy, $args);
          wc1c_check_wp_error($result);
        }
        else {
          wc1c_wp_error($result, $error_code);
        }
      }
    }
  }

  if ($is_full) wc_set_term_order($term_id, $order, $taxonomy);
    
  update_woocommerce_term_meta($term_id, 'wc1c_timestamp', WC1C_TIMESTAMP);
}

function wc1c_replace_group($is_full, $group, $order) {
  wc1c_replace_term($is_full, $group['Ид'], $group['ИдРодителя'], $group['Наименование'], 'product_cat', $order);
}

function wc1c_replace_woocommerce_attribute($is_full, $guid, $attribute_label, $attribute_type, $order) {
  global $wpdb;

  $guids = get_option('wc1c_guid_attributes', array());
  $attribute_id = @$guids[$guid];
  
  if ($attribute_id) {
    $attribute_id = $wpdb->get_var($wpdb->prepare("SELECT attribute_id FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_id = %d", $attribute_id));
    wc1c_check_wpdb_error();
  }

  $data = compact('attribute_label', 'attribute_type');

  if (!$attribute_id) {
    $attribute_id = $wpdb->get_var($wpdb->prepare("SELECT attribute_id FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_label = %s", $attribute_label));
    wc1c_check_wpdb_error();

    if (!$attribute_id) {
      $attribute_name = wc_sanitize_taxonomy_name($attribute_label);
      $attribute_name = substr($attribute_name, 0, 32 - strlen('pa_'));

      $data = array_merge($data, array(
        'attribute_name' => $attribute_name,
        'attribute_orderby' => 'menu_order',
      ));
      $wpdb->insert("{$wpdb->prefix}woocommerce_attribute_taxonomies", $data);
      wc1c_check_wpdb_error();

      $attribute_id = $wpdb->insert_id;
      $is_added = true;
    }

    $guids[$guid] = $attribute_id;
    update_option('wc1c_guid_attributes', $guids);
  }

  if (empty($is_added)) {
    $wpdb->update("{$wpdb->prefix}woocommerce_attribute_taxonomies", $data, compact('attribute_id'));
    wc1c_check_wpdb_error();
  }

  if ($is_full) {
    $orders = get_option('wc1c_order_attributes', array());
    $order_index = array_search($attribute_id, $orders) or 0;
    if ($order_index !== false) unset($orders[$order_index]);
    array_splice($orders, $order, 0, $attribute_id);
    update_option('wc1c_order_attributes', $orders);
  }

  $timestamps = get_option('wc1c_timestamp_attributes', array());
  $timestamps[$guid] = WC1C_TIMESTAMP;
  update_option('wc1c_timestamp_attributes', $timestamps);

  return $attribute_id;
}

function wc1c_replace_property_option($property_option, $attribute_taxonomy, $order) {
  wc1c_replace_term(true, $property_option['ИдЗначения'], null, $property_option['Значение'], $attribute_taxonomy, $order);
}

function wc1c_replace_property($is_full, $property, $order) {
  $attribute_type = @$property['ТипЗначений'] == 'Справочник' ? 'select' : 'text';
  $attribute_id = wc1c_replace_woocommerce_attribute($is_full, $property['Ид'], $property['Наименование'], $attribute_type, $order);

  $attribute = wc1c_woocommerce_attribute_by_id($attribute_id);
  if (!$attribute) wc1c_error("Failed to get attribute");

  register_taxonomy($attribute['taxonomy'], null);

  if ($attribute_type == 'select') {
    foreach ($property['ВариантыЗначений'] as $i => $property_option) {
      wc1c_replace_property_option($property_option, $attribute['taxonomy'], $i + 1);
    }
  }

  return $attribute['taxonomy'];
}

function wc1c_replace_post($guid, $post_title, $post_type, $is_deleted, $post_content, $post_meta, $category_taxonomy, $category_guids) {
  $post_id = wc1c_post_id_by_meta('wc1c_guid', $guid);

  $args = compact('post_type', 'post_title', 'post_content');

  if (!$post_id) {
    $args = array_merge($args, array(
      'post_name' => sanitize_title($post_title),
      'post_status' => 'publish',
    ));
    $post_id = wp_insert_post($args, true);
    wc1c_check_wp_error($post_id);

    update_post_meta($post_id, '_visibility', 'visible');
    update_post_meta($post_id, 'wc1c_guid', $guid);

    $is_added = true;
  }

  $post = get_post($post_id);
  if (!$post) wc1c_error("Failed to get post");

  if (empty($is_added)) {
    foreach ($args as $key => $value) {
      if ($post->$key == $value) continue;

      $is_changed = true;
      break;
    }

    if (!empty($is_changed)) {
      $post_date = current_time('mysql');
      $args = array_merge($args, array(
        'ID' => $post_id,
        'post_date' => $post_date,
        'post_date_gmt' => get_gmt_from_date($post_date),
      ));
      $post_id = wp_update_post($args, true);
      wc1c_check_wp_error($post_id);
    }
  }

  if ($is_deleted && $post->post_status != 'trash') {
    wp_trash_post($post_id);
  }
  elseif (!$is_deleted && $post->post_status == 'trash') {
    wp_untrash_post($post_id);
  }

  $current_post_meta = get_post_meta($post_id);
  foreach ($current_post_meta as $meta_key => $meta_value) {
    $current_post_meta[$meta_key] = $meta_value[0];
  }

  foreach ($post_meta as $meta_key => $meta_value) {
    $current_meta_value = @$current_post_meta[$meta_key];
    if ($current_meta_value == $meta_value) continue;

    update_post_meta($post_id, $meta_key, $meta_value);
  }

  $current_category_ids = wp_get_post_terms($post_id, $category_taxonomy, "fields=ids");
  wc1c_check_wp_error($current_category_ids);

  $category_ids = array();
  if ($category_guids) {
    foreach ($category_guids as $category_guid) {
      $category_id = wc1c_term_id_by_meta('wc1c_guid', $category_guid);
      if ($category_id) $category_ids[] = $category_id;
    }
  }

  sort($current_category_ids);
  sort($category_ids);
  if ($current_category_ids != $category_ids) {
    $result = wp_set_post_terms($post_id, $category_ids, $category_taxonomy);
    wc1c_check_wp_error($result);
  }

  update_post_meta($post_id, 'wc1c_timestamp', WC1C_TIMESTAMP);

  return array($post_id, $current_post_meta);
}

function wc1c_replace_post_attachments($post_id, $attachments) {
  $data_dir = WC1C_DATA_DIR . "catalog";

  $attachment_path_by_hash = array();
  foreach ($attachments as $attachment_path => $attachment) {
    $attachment_path = "$data_dir/$attachment_path";
    $attachment_hash = md5_file($attachment_path);
    $attachment_path_by_hash[$attachment_hash] = $attachment_path;
  }
  $attachment_hash_by_path = array_flip($attachment_path_by_hash);

  $post_attachments = get_attached_media('image', $post_id);
  $post_attachment_id_by_hash = array();
  foreach ($post_attachments as $post_attachment) {
    $post_attachment_path = get_attached_file($post_attachment->ID);
    $post_attachment_hash = md5_file($post_attachment_path);
    $post_attachment_id_by_hash[$post_attachment_hash] = $post_attachment->ID;

    if (isset($attachment_path_by_hash[$post_attachment_hash])) {
      unset($attachment_path_by_hash[$post_attachment_hash]);
    }
    else {
      $result = wp_delete_attachment($post_attachment->ID);
      if ($result === false) wc1c_error("Failed to delete post attachment");
    }
  }

  $attachment_ids = array();
  foreach ($attachments as $attachment_path => $attachment) {
    $attachment_path = "$data_dir/$attachment_path";
    $attachment_hash = $attachment_hash_by_path[$attachment_path];
    $attachment_id = @$post_attachment_id_by_hash[$attachment_hash];
    if (!$attachment_id) {
      $file = array(
        'tmp_name' => $attachment_path,
        'name' => basename($attachment_path),
      );
      $attachment_id = media_handle_sideload($file, $post_id, @$attachment['description']);
      wc1c_check_wp_error($attachment_id);
      
      $uploaded_attachment_path = get_attached_file($attachment_id);
      copy($uploaded_attachment_path, $attachment_path);
    }

    $attachment_ids[] = $attachment_id;
  }

  return $attachment_ids;
}

function wc1c_replace_product($is_full, $guid, $title, $is_deleted, $sku, $unit, $group_guids, $description, $picture_paths, $producer, $properties, $requisites) {
  $post_meta = array(
    '_sku' => $sku,
    'wc1c_unit' => $unit,
    'wc1c_producer' => $producer,
  );

  list($post_id, $post_meta) = wc1c_replace_post($guid, $title, 'product', $is_deleted, $description, $post_meta, 'product_cat', $group_guids);

  $current_product_attributes = isset($post_meta['_product_attributes']) ? maybe_unserialize($post_meta['_product_attributes']) : array();

  $current_product_attribute_variations = array(); 
  foreach ($current_product_attributes as $current_product_attribute_key => $current_product_attribute) {
    if (!$current_product_attribute['is_variation']) continue;

    unset($current_product_attributes[$current_product_attribute_key]);
    $current_product_attribute_variations[$current_product_attribute_key] = $current_product_attribute;
  }

  $product_attributes = array();

  if ($properties) {
    $attribute_guids = get_option('wc1c_guid_attributes', array());
    foreach ($properties as $property) {
      $attribute_guid = $property['Ид'];
      $attribute_id = @$attribute_guids[$attribute_guid];
      if (!$attribute_id) continue;

      $attribute = wc1c_woocommerce_attribute_by_id($attribute_id);
      if (!$attribute) wc1c_error("Failed to get attribute");
      
      $terms = array();
      $attribute_values = @$property['Значение'];
      if ($attribute_values) {
        foreach ($attribute_values as $attribute_value) {
          if ($attribute['attribute_type'] == 'select') {
            $term_id = wc1c_term_id_by_meta('wc1c_guid', $attribute_value);
            if ($term_id) $terms[] = (int) $term_id;
          }
          elseif ($attribute['attribute_type'] == 'text') {
            $terms[] = $attribute_value;
          }
        }
      }

      register_taxonomy($attribute['taxonomy'], null);
      $result = wp_set_post_terms($post_id, $terms, $attribute['taxonomy']);
      wc1c_check_wp_error($result);

      if ($terms) {
        $product_attribute_key = sanitize_title($attribute['taxonomy']);
        $product_attribute_position = count($product_attributes);
        $product_attributes[$product_attribute_key] = array(
          'name' => $attribute['taxonomy'],
          'value' => '',
          'position' => $product_attribute_position,
          'is_visible' => 1,
          'is_variation' => 0,
          'is_taxonomy' => 1,
        );
      }
    }
  }

  if ($requisites) {
    foreach ($requisites as $requisite) {
      $attribute_values = @$requisite['Значение'];
      if (!$attribute_values) continue;
      if (strpos($attribute_values[0], "import_files/") === 0) continue;

      $attribute_name = $requisite['Наименование'];
      $product_attribute_key = sanitize_title($attribute_name);
      $product_attribute_position = count($product_attributes);
      $product_attributes[$product_attribute_key] = array(
        'name' => wc_clean($attribute_name),
        'value' => implode(" | ", $attribute_values),
        'position' => $product_attribute_position,
        'is_visible' => 0,
        'is_variation' => 0,
        'is_taxonomy' => 0,
      );
    }
  }

  $old_product_attributes = array_diff_key($current_product_attributes, $product_attributes);
  $old_taxonomies = array();
  foreach ($old_product_attributes as $old_product_attribute) {
    if ($old_product_attribute['is_taxonomy']) $old_taxonomies[] = $old_product_attribute['name'];
  }
  wp_delete_object_term_relationships($post_id, $old_taxonomies);

  ksort($current_product_attributes);
  $product_attributes_copy = $product_attributes;
  ksort($product_attributes_copy);
  if ($current_product_attributes != $product_attributes_copy) {
    $product_attributes = array_merge($product_attributes, $current_product_attribute_variations);
    update_post_meta($post_id, '_product_attributes', $product_attributes);
  }

  $attachments = array();
  if ($picture_paths) {
    foreach ($picture_paths as $picture_path) {
      $attachments[$picture_path] = array();
    }
  }

  if ($requisites) {
    $attachment_keys = array(
      'ОписаниеФайла' => 'description',
    );
    foreach ($requisites as $requisite) {
      $attribute_name = $requisite['Наименование'];
      if (!isset($attachment_keys[$attribute_name])) continue;

      $attribute_values = @$requisite['Значение'];
      if (!$attribute_values) continue;

      $attribute_value = $attribute_values[0];
      if (strpos($attribute_value, "import_files/") !== 0) continue;
        
      list($picture_path, $attribute_value) = explode('#', $attribute_value, 2);
      if (!isset($attachments[$picture_path])) continue;

      $attachment_key = $attachment_keys[$attribute_name];
      $attachments[$picture_path][$attachment_key] = $attribute_value;
    }
  }

  if ($attachments || $is_full) {
    $attachment_ids = wc1c_replace_post_attachments($post_id, $attachments);

    $new_post_meta = array(
      '_product_image_gallery' => implode(',', array_slice($attachment_ids, 1)),
      '_thumbnail_id' => @$attachment_ids[0],
    );
    foreach ($new_post_meta as $meta_key => $meta_value) {
      if ($meta_value != @$post_meta[$meta_key]) update_post_meta($post_id, $meta_key, $meta_value);
    }
  }
}

function wc1c_clean_woocommerce_categories($is_full) {
  global $wpdb;

  if (!$is_full) return;

  $term_ids = $wpdb->get_col($wpdb->prepare("SELECT term_id FROM $wpdb->woocommerce_termmeta JOIN $wpdb->term_taxonomy ON woocommerce_term_id = term_id WHERE taxonomy = 'product_cat' AND meta_key = 'wc1c_timestamp' AND meta_value != %s", WC1C_TIMESTAMP));
  wc1c_check_wpdb_error();

  foreach ($term_ids as $term_id) {
    $result = wp_delete_term($term_id, 'product_cat');
    wc1c_check_wp_error($result);
  }
}

function wc1c_clean_woocommerce_attributes($is_full) {
  global $wpdb;

  if (!$is_full) return;

  $timestamps = get_option('wc1c_timestamp_attributes', array());
  if (!$timestamps) return;

  $guids = get_option('wc1c_guid_attributes', array());

  foreach ($timestamps as $guid => $timestamp) {
    if ($timestamp == WC1C_TIMESTAMP) continue;

    $attribute_id = $guids[$guid];

    $attribute = wc1c_woocommerce_attribute_by_id($attribute_id);
    if (!$attribute) wc1c_error("Failed to get attribute");

    wc1c_delete_woocommerce_attribute($attribute_id);
    
    unset($guids[$guid]);
    unset($timestamps[$guid]);

    $is_deleted = true;
  }

  if (!empty($is_deleted)) {
    $orders = get_option('wc1c_order_attributes', array());
    $order_index = array_search($attribute_id, $orders);
    if ($order_index !== false) {
      unset($orders[$order_index]);
      update_option('wc1c_order_attributes', $orders);
    }

    update_option('wc1c_guid_attributes', $guids);
    update_option('wc1c_timestamp_attributes', $timestamps);
  }
}

function wc1c_clean_woocommerce_attribute_options($attribute_taxonomy) {
  global $wpdb;

  $term_ids = $wpdb->get_col($wpdb->prepare("SELECT term_id FROM $wpdb->woocommerce_termmeta JOIN $wpdb->term_taxonomy ON woocommerce_term_id = term_id WHERE taxonomy = %s AND meta_key = 'wc1c_timestamp' AND meta_value != %s", $attribute_taxonomy, WC1C_TIMESTAMP));
  wc1c_check_wpdb_error();

  foreach ($term_ids as $term_id) {
    $result = wp_delete_term($term_id, $attribute_taxonomy);
    wc1c_check_wp_error($result);
  }
}

function wc1c_clean_posts($post_type) {
  global $wpdb;

  $post_ids = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta JOIN $wpdb->posts ON post_id = ID WHERE post_type = %s AND meta_key = 'wc1c_timestamp' AND meta_value != %s", $post_type, WC1C_TIMESTAMP));
  wc1c_check_wpdb_error();

  foreach ($post_ids as $post_id) {
    wp_trash_post($post_id);
  }
}

function wc1c_clean_products($is_full) {
  if (!$is_full) return;

  wc1c_clean_posts('product');
}

function wc1c_clean_product_terms() {
  global $wpdb;

  $wpdb->query("UPDATE $wpdb->term_taxonomy tt SET count = (SELECT COUNT(*) FROM $wpdb->term_relationships WHERE term_taxonomy_id = tt.term_taxonomy_id) WHERE taxonomy LIKE 'pa_%'");
  wc1c_check_wpdb_error();

  $rows = $wpdb->get_results("SELECT term_id, taxonomy FROM $wpdb->term_taxonomy LEFT JOIN $wpdb->woocommerce_termmeta ON term_id = woocommerce_term_id AND meta_key = 'wc1c_guid' WHERE meta_value IS NULL AND taxonomy LIKE 'pa_%' AND COUNT = 0");
  wc1c_check_wpdb_error();

  foreach ($rows as $row) {
    $result = wp_delete_term($row->term_id, $row->taxonomy);
    wc1c_check_wp_error($result);
  }
}