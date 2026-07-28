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

// Hybrid field getter: uses ACF if available, falls back to post meta
function avf_get_field($field_name, $post_id = false) {
  if (function_exists('get_field')) {
    $value = get_field($field_name, $post_id);
    if ($value !== null && $value !== '') {
      return $value;
    }
  }

  if (!$post_id) {
    $post_id = get_queried_object_id();
  }

  if (!$post_id) {
    return '';
  }

  return get_post_meta($post_id, $field_name, true);
}

function avf_newsletter_template_file() {
  return get_stylesheet_directory() . '/page-newsletter.php';
}

function avf_is_newsletter_template_value($template_value) {
  $template_value = trim((string) $template_value);
  if ($template_value === '') {
    return false;
  }

  $template_value = str_replace('\\', '/', $template_value);

  return basename($template_value) === 'page-newsletter.php';
}

function avf_is_newsletter_page() {
  if (!is_singular('page')) {
    return false;
  }

  $post_id = get_queried_object_id();
  if (!$post_id) {
    return false;
  }

  $raw_template = get_post_meta($post_id, '_wp_page_template', true);
  if (avf_is_newsletter_template_value($raw_template)) {
    return true;
  }

  $slug_template = get_page_template_slug($post_id);
  return avf_is_newsletter_template_value($slug_template);
}

function avf_register_newsletter_template($templates) {
  if (file_exists(avf_newsletter_template_file())) {
    $templates['page-newsletter.php'] = 'Newsletter Template';
  }

  return $templates;
}
add_filter('theme_page_templates', 'avf_register_newsletter_template');

function avf_force_newsletter_template($template) {
  if (!is_singular('page')) {
    return $template;
  }

  if (avf_is_newsletter_page() && file_exists(avf_newsletter_template_file())) {
    return avf_newsletter_template_file();
  }

  return $template;
}
add_filter('template_include', 'avf_force_newsletter_template', 9999);

function avf_newsletter_assets() {
  if (!avf_is_newsletter_page()) {
    return;
  }

  $newsletter_css_path = get_stylesheet_directory() . '/assets/css/newsletter.css';
  if (file_exists($newsletter_css_path)) {
    $newsletter_css_version = (string) filemtime($newsletter_css_path);
    wp_enqueue_style(
      'avf-newsletter-css',
      get_stylesheet_directory_uri() . '/assets/css/newsletter.css',
      array(),
      $newsletter_css_version
    );
  }

  $newsletter_js_path = get_stylesheet_directory() . '/assets/js/newsletter.js';
  if (file_exists($newsletter_js_path)) {
    wp_enqueue_script('jquery');
    wp_enqueue_script(
      'avf-newsletter-js',
      get_stylesheet_directory_uri() . '/assets/js/newsletter.js',
      array('jquery'),
      (string) filemtime($newsletter_js_path),
      true
    );
  }
}
add_action('wp_enqueue_scripts', 'avf_newsletter_assets');

function avf_newsletter_body_class($classes) {
  if (avf_is_newsletter_page()) {
    $classes[] = 'page-template-page-newsletter';
  }
  return $classes;
}
add_filter('body_class', 'avf_newsletter_body_class');

function avf_register_newsletter_acf_fields() {
  if (!function_exists('acf_add_local_field_group')) {
    return;
  }

  $article_groups = array();
  for ($i = 1; $i <= 8; $i++) {
    $extra_image_fields = array();
    for ($image_number = 4; $image_number <= 8; $image_number++) {
      $extra_image_fields[] = array(
        'key' => 'field_avf_article_image_' . $image_number . '_' . $i,
        'label' => 'Article Image ' . $image_number,
        'name' => 'article_image_' . $image_number,
        'type' => 'image',
        'return_format' => 'array',
        'preview_size' => 'medium',
      );
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
          'key' => 'field_avf_article_order_' . $i,
          'label' => 'Article Order',
          'name' => 'article_order',
          'type' => 'number',
          'instructions' => 'Lower numbers appear first. Leave blank to use the default Article ' . $i . ' position.',
          'min' => 1,
          'step' => 1,
          'wrapper' => array(
            'width' => '25',
          ),
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
          'key' => 'field_avf_article_image_1_caption_' . $i,
          'label' => 'Article Image 1 Caption',
          'name' => 'article_image_1_caption',
          'type' => 'textarea',
          'rows' => 2,
          'new_lines' => 'br',
        ),
        array(
          'key' => 'field_avf_article_image_1_alt_' . $i,
          'label' => 'Article Image 1 Alt Text',
          'name' => 'article_image_1_alt',
          'type' => 'text',
          'instructions' => 'Describe the image for visitors using screen readers.',
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
          'key' => 'field_avf_article_image_2_caption_' . $i,
          'label' => 'Article Image 2 Caption',
          'name' => 'article_image_2_caption',
          'type' => 'textarea',
          'rows' => 2,
          'new_lines' => 'br',
        ),
        array(
          'key' => 'field_avf_article_image_2_alt_' . $i,
          'label' => 'Article Image 2 Alt Text',
          'name' => 'article_image_2_alt',
          'type' => 'text',
          'instructions' => 'Describe the image for visitors using screen readers.',
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
          'key' => 'field_avf_article_image_3_caption_' . $i,
          'label' => 'Article Image 3 Caption',
          'name' => 'article_image_3_caption',
          'type' => 'textarea',
          'rows' => 2,
          'new_lines' => 'br',
        ),
        array(
          'key' => 'field_avf_article_image_3_alt_' . $i,
          'label' => 'Article Image 3 Alt Text',
          'name' => 'article_image_3_alt',
          'type' => 'text',
          'instructions' => 'Describe the image for visitors using screen readers.',
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
        array(
          'key' => 'field_avf_article_show_bottom_banner_' . $i,
          'label' => 'Show Bottom Ad Banner',
          'name' => 'article_show_bottom_banner_ad',
          'type' => 'true_false',
          'ui' => 1,
        ),
        array(
          'key' => 'field_avf_article_bottom_banner_image_' . $i,
          'label' => 'Bottom Ad Banner Image',
          'name' => 'article_bottom_banner_ad_image',
          'type' => 'image',
          'return_format' => 'array',
          'preview_size' => 'medium',
          'conditional_logic' => array(
            array(
              array(
                'field' => 'field_avf_article_show_bottom_banner_' . $i,
                'operator' => '==',
                'value' => '1',
              ),
            ),
          ),
        ),
        array(
          'key' => 'field_avf_article_bottom_banner_url_' . $i,
          'label' => 'Bottom Ad Destination URL',
          'name' => 'article_bottom_banner_ad_url',
          'type' => 'url',
          'conditional_logic' => array(
            array(
              array(
                'field' => 'field_avf_article_show_bottom_banner_' . $i,
                'operator' => '==',
                'value' => '1',
              ),
            ),
          ),
        ),
        array(
          'key' => 'field_avf_article_bottom_banner_alt_' . $i,
          'label' => 'Bottom Ad Alt Text',
          'name' => 'article_bottom_banner_ad_alt',
          'type' => 'text',
          'default_value' => 'Advertisement',
          'conditional_logic' => array(
            array(
              array(
                'field' => 'field_avf_article_show_bottom_banner_' . $i,
                'operator' => '==',
                'value' => '1',
              ),
            ),
          ),
        ),
        array(
          'key' => 'field_avf_article_custom_css_' . $i,
          'label' => 'Custom CSS',
          'name' => 'article_custom_css',
          'type' => 'textarea',
          'instructions' => 'Enter custom CSS rules for this article. Target the text with `.newsletter-article-body`. Example: .newsletter-article-body p { font-size: 1.25rem; }',
          'rows' => 6,
        ),
        array(
          'key' => 'field_avf_article_text_classes_' . $i,
          'label' => 'Plain Text CSS Classes',
          'name' => 'article_text_classes',
          'type' => 'text',
          'instructions' => 'Optional: add one or more class names (space-separated) to the article text container.',
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
    ), $article_groups),
    'location' => array(
      array(
        array(
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'page',
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

function avf_newsletter_admin_template_toggle($hook) {
  if (!in_array($hook, array('post.php', 'post-new.php'), true)) {
    return;
  }

  $screen = function_exists('get_current_screen') ? get_current_screen() : null;
  if (!$screen || $screen->post_type !== 'page') {
    return;
  }

  wp_enqueue_script('jquery');

  $script = <<<'JS'
jQuery(function ($) {
  var newsletterGroupSelector = '#acf-group_avf_newsletter, .acf-postbox[data-key="group_avf_newsletter"]';

  function isNewsletterTemplate(value) {
    value = (value || '').replace(/\\/g, '/');
    return value.split('/').pop() === 'page-newsletter.php';
  }

  function getTemplateSelect() {
    return $('select[name="page_template"], select#page_template').first();
  }

  function updateNewsletterFields() {
    var $group = $(newsletterGroupSelector);
    var $select = getTemplateSelect();

    if (!$group.length || !$select.length) {
      return;
    }

    if (isNewsletterTemplate($select.val())) {
      $group.show();
    } else {
      $group.hide();
    }
  }

  $(document).on('change', 'select[name="page_template"], select#page_template', updateNewsletterFields);
  updateNewsletterFields();
  window.setTimeout(updateNewsletterFields, 500);
  window.setTimeout(updateNewsletterFields, 1500);
});
JS;

  wp_add_inline_script('jquery', $script);
}
add_action('admin_enqueue_scripts', 'avf_newsletter_admin_template_toggle');

// Fallback: Simple metabox for newsletter fields when ACF is not available
function avf_register_newsletter_metabox() {
  if (function_exists('acf_add_local_field_group')) {
    return; // ACF is active, use ACF instead
  }

  add_meta_box(
    'avf_newsletter_fields',
    'Newsletter Fields',
    'avf_newsletter_metabox_callback',
    'page',
    'normal',
    'high',
    array('show_in_rest' => true)
  );
}
add_action('add_meta_boxes', 'avf_register_newsletter_metabox');

function avf_newsletter_metabox_callback($post) {
  wp_nonce_field('avf_newsletter_nonce', 'avf_newsletter_nonce');

  $title = get_post_meta($post->ID, 'newsletter_title', true);
  $issue = get_post_meta($post->ID, 'newsletter_issue', true);
  $subtitle = get_post_meta($post->ID, 'newsletter_subtitle', true);

  echo '<div id="avf-newsletter-metabox" style="display:none;">';
  
  echo '<div style="background: #f5f5f5; padding: 15px; border-radius: 4px; margin-bottom: 20px;">';
  echo '<p style="margin: 0 0 10px; font-weight: bold;">✓ Newsletter Template Selected</p>';
  echo '<p style="margin: 0; color: #666; font-size: 12px;">Fill in the newsletter details below.</p>';
  echo '</div>';

  echo '<div style="margin-bottom: 15px;">';
  echo '<label><strong>Newsletter Title</strong></label><br>';
  echo '<input type="text" name="newsletter_title" value="' . esc_attr($title) . '" style="width:100%; padding:8px;" />';
  echo '</div>';

  echo '<div style="margin-bottom: 15px;">';
  echo '<label><strong>Issue (e.g., "May/June 2026")</strong></label><br>';
  echo '<input type="text" name="newsletter_issue" value="' . esc_attr($issue) . '" style="width:100%; padding:8px;" />';
  echo '</div>';

  echo '<div style="margin-bottom: 15px;">';
  echo '<label><strong>Subtitle</strong></label><br>';
  echo '<textarea name="newsletter_subtitle" style="width:100%; padding:8px; height:80px;">' . esc_textarea($subtitle) . '</textarea>';
  echo '</div>';

  echo '<p><em><strong>For Article Fields:</strong> Go to each article and use the fields below the article content, or edit post meta directly via a plugin like <a href="https://wordpress.org/plugins/meta-box/" target="_blank">Meta Box</a>.</em></p>';
  
  echo '</div>';

  echo '<div id="avf-newsletter-prompt" style="color: #999; font-style: italic;"></div>';

  echo '<script>
  (function() {
    function checkTemplateAndToggle() {
      const templateSelector = document.querySelector("select[name=\"page_template\"]");
      const metabox = document.getElementById("avf-newsletter-metabox");
      const prompt = document.getElementById("avf-newsletter-prompt");
      
      if (!templateSelector || !metabox || !prompt) return;
      
      const selectedValue = templateSelector.value || templateSelector.options[templateSelector.selectedIndex]?.value || "";
      const selectedText = templateSelector.options[templateSelector.selectedIndex]?.text || "";
      
      console.log("Template selector value:", selectedValue);
      console.log("Template selector text:", selectedText);
      
      const isNewsletter = (
        selectedValue === "page-newsletter.php" || 
        selectedValue.includes("page-newsletter.php") ||
        selectedText.includes("Newsletter Template")
      );
      
      if (isNewsletter) {
        metabox.style.display = "block";
        prompt.style.display = "none";
      } else {
        metabox.style.display = "none";
        prompt.innerHTML = "Newsletter fields appear when you select <strong>\"Newsletter Template\"</strong> from the Page Attributes panel.";
      }
    }
    
    // Check on page load
    setTimeout(checkTemplateAndToggle, 100);
    
    // Check when template selector changes
    const templateSelector = document.querySelector("select[name=\"page_template\"]");
    if (templateSelector) {
      templateSelector.addEventListener("change", function() {
        setTimeout(checkTemplateAndToggle, 100);
      });
    }
    
    // Also check periodically in case Divi/page builder dynamically adds the selector
    setInterval(checkTemplateAndToggle, 1000);
  })();
  </script>';
}

function avf_save_newsletter_metabox($post_id) {
  if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
    return;
  }

  if (!isset($_POST['avf_newsletter_nonce']) || !wp_verify_nonce($_POST['avf_newsletter_nonce'], 'avf_newsletter_nonce')) {
    return;
  }

  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
    return;
  }

  if (!current_user_can('edit_page', $post_id)) {
    return;
  }

  if (isset($_POST['newsletter_title'])) {
    update_post_meta($post_id, 'newsletter_title', sanitize_text_field($_POST['newsletter_title']));
  }
  if (isset($_POST['newsletter_issue'])) {
    update_post_meta($post_id, 'newsletter_issue', sanitize_text_field($_POST['newsletter_issue']));
  }
  if (isset($_POST['newsletter_subtitle'])) {
    update_post_meta($post_id, 'newsletter_subtitle', sanitize_textarea_field($_POST['newsletter_subtitle']));
  }
}
add_action('save_post_page', 'avf_save_newsletter_metabox');
