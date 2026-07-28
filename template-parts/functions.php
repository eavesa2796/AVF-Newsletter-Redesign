<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );

// END ENQUEUE PARENT ACTION
function avf_register_newsletter_article_cpt() {
  $labels = array(
    'name' => 'Newsletter Articles',
    'singular_name' => 'Newsletter Article',
    'add_new' => 'Add New',
    'add_new_item' => 'Add New Newsletter Article',
    'edit_item' => 'Edit Newsletter Article',
    'new_item' => 'New Newsletter Article',
    'view_item' => 'View Newsletter Article',
    'search_items' => 'Search Newsletter Articles',
    'not_found' => 'No newsletter articles found',
    'not_found_in_trash' => 'No newsletter articles found in Trash',
    'menu_name' => 'Newsletter Articles',
  );

  $args = array(
    'labels' => $labels,
    'public' => false,
    'publicly_queryable' => false,
    'exclude_from_search' => false,
    'show_ui' => true,
    'show_in_menu' => true,
    'show_in_admin_bar' => true,
    'menu_position' => 25,
    'menu_icon' => 'dashicons-media-document',
    'supports' => array('title', 'editor', 'thumbnail'),
    'show_in_rest' => true,
    'query_var' => true,
    'capability_type' => 'post',
    'map_meta_cap' => true,
  );

  register_post_type('newsletter_article', $args);
}
add_action('init', 'avf_register_newsletter_article_cpt');

function avf_newsletter_selected_articles_query($args, $field, $post_id) {
  $args['post_type'] = 'newsletter_article';
  $args['post_status'] = array('publish', 'draft', 'pending', 'private', 'future');
  $args['posts_per_page'] = 50;
  $args['orderby'] = 'date';
  $args['order'] = 'DESC';
  $args['ignore_sticky_posts'] = true;

  return $args;
}
add_filter('acf/fields/relationship/query/name=newsletter_selected_articles', 'avf_newsletter_selected_articles_query', 10, 3);
add_filter('acf/fields/relationship/query/key=field_avf_newsletter_selected_articles', 'avf_newsletter_selected_articles_query', 10, 3);

function avf_newsletter_relationship_query($args, $field, $post_id) {
  $field_key = $field['key'] ?? '';
  $field_name = $field['name'] ?? '';

  if ($field_key !== 'field_avf_newsletter_selected_articles' && $field_name !== 'newsletter_selected_articles') {
    return $args;
  }

  return avf_newsletter_selected_articles_query($args, $field, $post_id);
}
add_filter('acf/fields/relationship/query', 'avf_newsletter_relationship_query', 10, 3);

function avf_newsletter_selected_articles_result($title, $post, $field, $post_id) {
  if (!$post instanceof WP_Post || $post->post_type !== 'newsletter_article') {
    return $title;
  }

  $status = get_post_status_object($post->post_status);
  $status_label = $status ? $status->label : $post->post_status;

  if ($post->post_status !== 'publish') {
    $title .= ' <span style="color:#667085;">(' . esc_html($status_label) . ')</span>';
  }

  return $title;
}
add_filter('acf/fields/relationship/result/key=field_avf_newsletter_selected_articles', 'avf_newsletter_selected_articles_result', 10, 4);

function avf_newsletter_assets() {
  if (is_page_template('page-newsletter.php')) {
    wp_enqueue_style(
      'avf-newsletter-css',
      get_stylesheet_directory_uri() . '/assets/css/newsletter.css',
      array(),
      '1.1'
    );

    $newsletter_js_path = get_stylesheet_directory() . '/assets/js/newsletter.js';
    if (file_exists($newsletter_js_path)) {
      wp_enqueue_script('jquery');
      wp_enqueue_script(
        'avf-newsletter-js',
        get_stylesheet_directory_uri() . '/assets/js/newsletter.js',
        array('jquery'),
        '1.0',
        true
      );
    }
  }
}
add_action('wp_enqueue_scripts', 'avf_newsletter_assets');

function avf_register_newsletter_acf_fields() {
  if (!function_exists('acf_add_local_field_group')) {
    return;
  }

  $article_groups = array();
  for ($i = 1; $i <= 8; $i++) {
    $extra_image_fields = array();
    for ($image_number = 1; $image_number <= 8; $image_number++) {
      if ($image_number > 3) {
        $extra_image_fields[] = array(
          'key' => 'field_avf_article_image_' . $image_number . '_' . $i,
          'label' => 'Article Image ' . $image_number,
          'name' => 'article_image_' . $image_number,
          'type' => 'image',
          'return_format' => 'array',
          'preview_size' => 'medium',
        );
      }

      $extra_image_fields[] = array(
        'key' => 'field_avf_article_image_' . $image_number . '_caption_' . $i,
        'label' => 'Article Image ' . $image_number . ' Caption',
        'name' => 'article_image_' . $image_number . '_caption',
        'type' => 'textarea',
        'rows' => 2,
        'new_lines' => 'br',
      );
      $extra_image_fields[] = array(
        'key' => 'field_avf_article_image_' . $image_number . '_alt_' . $i,
        'label' => 'Article Image ' . $image_number . ' Alt Text',
        'name' => 'article_image_' . $image_number . '_alt',
        'type' => 'text',
        'instructions' => 'Describe the image for visitors using screen readers.',
      );
    }

    $article_groups[] = array(
      'key' => 'field_avf_newsletter_article_group_' . $i,
      'label' => 'Article ' . $i,
      'name' => 'newsletter_article_' . $i,
      'type' => 'group',
      'layout' => 'block',
      'sub_fields' => array_merge(array(
        array(
          'key' => 'field_avf_article_id_' . $i,
          'label' => 'Anchor ID (optional)',
          'name' => 'article_id',
          'type' => 'text',
          'instructions' => 'Example: market-update. Leave empty to auto-generate from title.',
        ),
        array(
          'key' => 'field_avf_article_title_' . $i,
          'label' => 'Article Title',
          'name' => 'article_title',
          'type' => 'text',
        ),
        array(
          'key' => 'field_avf_article_author_name_' . $i,
          'label' => 'Author Name',
          'name' => 'author_name',
          'type' => 'text',
        ),
        array(
          'key' => 'field_avf_article_author_title_' . $i,
          'label' => 'Author Title',
          'name' => 'author_title',
          'type' => 'text',
          'instructions' => 'Example: Chair of the AVF',
        ),
        array(
          'key' => 'field_avf_article_author_credentials_' . $i,
          'label' => 'Author Credentials',
          'name' => 'author_credentials',
          'type' => 'text',
          'instructions' => 'Example: CFP, CFA, Senior Portfolio Manager',
        ),
        array(
          'key' => 'field_avf_article_author_image_' . $i,
          'label' => 'Author Image',
          'name' => 'author_image',
          'type' => 'image',
          'return_format' => 'array',
          'preview_size' => 'thumbnail',
        ),
        array(
          'key' => 'field_avf_article_author_2_name_' . $i,
          'label' => 'Author 2 Name',
          'name' => 'author_2_name',
          'type' => 'text',
        ),
        array(
          'key' => 'field_avf_article_author_2_title_' . $i,
          'label' => 'Author 2 Title',
          'name' => 'author_2_title',
          'type' => 'text',
          'instructions' => 'Example: Chair of the AVF',
        ),
        array(
          'key' => 'field_avf_article_author_2_credentials_' . $i,
          'label' => 'Author 2 Credentials',
          'name' => 'author_2_credentials',
          'type' => 'text',
        ),
        array(
          'key' => 'field_avf_article_author_2_image_' . $i,
          'label' => 'Author 2 Image',
          'name' => 'author_2_image',
          'type' => 'image',
          'return_format' => 'array',
          'preview_size' => 'thumbnail',
        ),
        array(
          'key' => 'field_avf_article_author_3_name_' . $i,
          'label' => 'Author 3 Name',
          'name' => 'author_3_name',
          'type' => 'text',
        ),
        array(
          'key' => 'field_avf_article_author_3_title_' . $i,
          'label' => 'Author 3 Title',
          'name' => 'author_3_title',
          'type' => 'text',
          'instructions' => 'Example: Chair of the AVF',
        ),
        array(
          'key' => 'field_avf_article_author_3_credentials_' . $i,
          'label' => 'Author 3 Credentials',
          'name' => 'author_3_credentials',
          'type' => 'text',
        ),
        array(
          'key' => 'field_avf_article_author_3_image_' . $i,
          'label' => 'Author 3 Image',
          'name' => 'author_3_image',
          'type' => 'image',
          'return_format' => 'array',
          'preview_size' => 'thumbnail',
        ),
        array(
          'key' => 'field_avf_article_content_' . $i,
          'label' => 'Article Content',
          'name' => 'article_content',
          'type' => 'wysiwyg',
          'tabs' => 'all',
          'toolbar' => 'full',
          'media_upload' => 1,
        ),
        array(
          'key' => 'field_avf_article_image_1_' . $i,
          'label' => 'Article Image 1',
          'name' => 'article_image_1',
          'type' => 'image',
          'return_format' => 'array',
          'preview_size' => 'medium',
        ),
        array(
          'key' => 'field_avf_article_image_2_' . $i,
          'label' => 'Article Image 2',
          'name' => 'article_image_2',
          'type' => 'image',
          'return_format' => 'array',
          'preview_size' => 'medium',
        ),
        array(
          'key' => 'field_avf_article_image_3_' . $i,
          'label' => 'Article Image 3',
          'name' => 'article_image_3',
          'type' => 'image',
          'return_format' => 'array',
          'preview_size' => 'medium',
        ),
        array(
          'key' => 'field_avf_article_additional_images_' . $i,
          'label' => 'Additional Images (Unlimited)',
          'name' => 'article_additional_images',
          'type' => 'relationship',
          'instructions' => 'Select any number of media library images. Drag to reorder.',
          'post_type' => array('attachment'),
          'filters' => array('search'),
          'elements' => array('featured_image'),
          'return_format' => 'id',
        ),
      ), $extra_image_fields),
    );

    $article_group_index = count($article_groups) - 1;
    $article_sub_fields = $article_groups[$article_group_index]['sub_fields'];
    $extra_field_count = count($extra_image_fields);

    if ($extra_field_count > 0) {
      $article_sub_fields = array_slice($article_sub_fields, 0, -$extra_field_count);
    }

    $additional_images_index = count($article_sub_fields);
    foreach ($article_sub_fields as $field_index => $article_sub_field) {
      if (($article_sub_field['name'] ?? '') === 'article_additional_images') {
        $additional_images_index = $field_index;
        break;
      }
    }

    array_splice(
      $article_sub_fields,
      $additional_images_index,
      0,
      $extra_image_fields
    );
    $article_groups[$article_group_index]['sub_fields'] = $article_sub_fields;
  }

  $cpt_extra_image_fields = array();
  for ($image_number = 1; $image_number <= 8; $image_number++) {
    if ($image_number > 3) {
      $cpt_extra_image_fields[] = array(
        'key' => 'field_avf_cpt_article_image_' . $image_number,
        'label' => 'Article Image ' . $image_number,
        'name' => 'article_image_' . $image_number,
        'type' => 'image',
        'return_format' => 'array',
        'preview_size' => 'medium',
      );
    }

    $cpt_extra_image_fields[] = array(
      'key' => 'field_avf_cpt_article_image_' . $image_number . '_caption',
      'label' => 'Article Image ' . $image_number . ' Caption',
      'name' => 'article_image_' . $image_number . '_caption',
      'type' => 'textarea',
      'rows' => 2,
      'new_lines' => 'br',
    );
    $cpt_extra_image_fields[] = array(
      'key' => 'field_avf_cpt_article_image_' . $image_number . '_alt',
      'label' => 'Article Image ' . $image_number . ' Alt Text',
      'name' => 'article_image_' . $image_number . '_alt',
      'type' => 'text',
      'instructions' => 'Describe the image for visitors using screen readers.',
    );
  }

  acf_add_local_field_group(array(
    'key' => 'group_avf_newsletter',
    'title' => 'Newsletter Fields',
    'fields' => array_merge(array(
      array(
        'key' => 'field_avf_newsletter_title',
        'label' => 'Newsletter Title',
        'name' => 'newsletter_title',
        'type' => 'text',
      ),
      array(
        'key' => 'field_avf_newsletter_issue',
        'label' => 'Issue',
        'name' => 'newsletter_issue',
        'type' => 'text',
      ),
      array(
        'key' => 'field_avf_newsletter_subtitle',
        'label' => 'Subtitle',
        'name' => 'newsletter_subtitle',
        'type' => 'text',
      ),
      array(
        'key' => 'field_avf_newsletter_cover_image',
        'label' => 'Cover Image',
        'name' => 'newsletter_cover_image',
        'type' => 'image',
        'return_format' => 'array',
        'preview_size' => 'large',
      ),
      array(
        'key' => 'field_avf_newsletter_selected_articles',
        'label' => 'Selected Articles',
        'name' => 'newsletter_selected_articles',
        'type' => 'relationship',
        'instructions' => 'Select and drag to reorder posts created under Newsletter Articles.',
        'post_type' => array('newsletter_article'),
        'filters' => array('search'),
        'elements' => array('featured_image'),
        'return_format' => 'id',
      ),
    ), $article_groups),
    'location' => array(
      array(
        array(
          'param' => 'page_template',
          'operator' => '==',
          'value' => 'page-newsletter.php',
        ),
      ),
    ),
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'active' => true,
  ));

  acf_add_local_field_group(array(
    'key' => 'group_avf_newsletter_article_fields',
    'title' => 'Newsletter Article Details',
    'fields' => array_merge(array(
      array(
        'key' => 'field_avf_cpt_article_id',
        'label' => 'Anchor ID (optional)',
        'name' => 'article_id',
        'type' => 'text',
        'instructions' => 'Example: market-update. Leave empty to auto-generate from the post title.',
      ),
      array(
        'key' => 'field_avf_cpt_article_chronology_date',
        'label' => 'Chronology Date',
        'name' => 'article_chronology_date',
        'type' => 'date_picker',
        'instructions' => 'Used for newsletter ordering. Earlier date appears first.',
        'display_format' => 'F j, Y',
        'return_format' => 'Ymd',
        'first_day' => 0,
      ),
      array(
        'key' => 'field_avf_cpt_author_name',
        'label' => 'Author Name',
        'name' => 'author_name',
        'type' => 'text',
      ),
      array(
        'key' => 'field_avf_cpt_author_title',
        'label' => 'Author Title',
        'name' => 'author_title',
        'type' => 'text',
        'instructions' => 'Example: Chair of the AVF',
      ),
      array(
        'key' => 'field_avf_cpt_author_credentials',
        'label' => 'Author Credentials',
        'name' => 'author_credentials',
        'type' => 'text',
      ),
      array(
        'key' => 'field_avf_cpt_author_image',
        'label' => 'Author Image',
        'name' => 'author_image',
        'type' => 'image',
        'return_format' => 'array',
        'preview_size' => 'thumbnail',
      ),
      array(
        'key' => 'field_avf_cpt_author_2_name',
        'label' => 'Author 2 Name',
        'name' => 'author_2_name',
        'type' => 'text',
      ),
      array(
        'key' => 'field_avf_cpt_author_2_title',
        'label' => 'Author 2 Title',
        'name' => 'author_2_title',
        'type' => 'text',
        'instructions' => 'Example: Chair of the AVF',
      ),
      array(
        'key' => 'field_avf_cpt_author_2_credentials',
        'label' => 'Author 2 Credentials',
        'name' => 'author_2_credentials',
        'type' => 'text',
      ),
      array(
        'key' => 'field_avf_cpt_author_2_image',
        'label' => 'Author 2 Image',
        'name' => 'author_2_image',
        'type' => 'image',
        'return_format' => 'array',
        'preview_size' => 'thumbnail',
      ),
      array(
        'key' => 'field_avf_cpt_author_3_name',
        'label' => 'Author 3 Name',
        'name' => 'author_3_name',
        'type' => 'text',
      ),
      array(
        'key' => 'field_avf_cpt_author_3_title',
        'label' => 'Author 3 Title',
        'name' => 'author_3_title',
        'type' => 'text',
        'instructions' => 'Example: Chair of the AVF',
      ),
      array(
        'key' => 'field_avf_cpt_author_3_credentials',
        'label' => 'Author 3 Credentials',
        'name' => 'author_3_credentials',
        'type' => 'text',
      ),
      array(
        'key' => 'field_avf_cpt_author_3_image',
        'label' => 'Author 3 Image',
        'name' => 'author_3_image',
        'type' => 'image',
        'return_format' => 'array',
        'preview_size' => 'thumbnail',
      ),
      array(
        'key' => 'field_avf_cpt_article_content',
        'label' => 'Article Content (optional)',
        'name' => 'article_content',
        'type' => 'wysiwyg',
        'instructions' => 'Use this or the WordPress content editor above.',
        'tabs' => 'all',
        'toolbar' => 'full',
        'media_upload' => 1,
      ),
      array(
        'key' => 'field_avf_cpt_article_image_1',
        'label' => 'Article Image 1',
        'name' => 'article_image_1',
        'type' => 'image',
        'return_format' => 'array',
        'preview_size' => 'medium',
      ),
      array(
        'key' => 'field_avf_cpt_article_image_2',
        'label' => 'Article Image 2',
        'name' => 'article_image_2',
        'type' => 'image',
        'return_format' => 'array',
        'preview_size' => 'medium',
      ),
      array(
        'key' => 'field_avf_cpt_article_image_3',
        'label' => 'Article Image 3',
        'name' => 'article_image_3',
        'type' => 'image',
        'return_format' => 'array',
        'preview_size' => 'medium',
      ),
      array(
        'key' => 'field_avf_cpt_article_additional_images',
        'label' => 'Additional Images (Unlimited)',
        'name' => 'article_additional_images',
        'type' => 'relationship',
        'instructions' => 'Select any number of media library images. Drag to reorder.',
        'post_type' => array('attachment'),
        'filters' => array('search'),
        'elements' => array('featured_image'),
        'return_format' => 'id',
      ),
    ), $cpt_extra_image_fields),
    'location' => array(
      array(
        array(
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'newsletter_article',
        ),
      ),
    ),
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'active' => true,
  ));
}
add_action('acf/init', 'avf_register_newsletter_acf_fields');
