<?php
/**
 * Template Name: Newsletter Template
 */

get_header();

$newsletter = [
  'title'       => get_field('newsletter_title'),
  'issue'       => get_field('newsletter_issue'),
  'subtitle'    => get_field('newsletter_subtitle'),
  'cover_image' => get_field('newsletter_cover_image'),
];

$articles = [];

$normalize_post_id = function ($value) {
  if ($value instanceof WP_Post) {
    return (int) $value->ID;
  }

  if (is_object($value) && isset($value->ID)) {
    return (int) $value->ID;
  }

  if (is_array($value)) {
    $possible_id = $value['ID'] ?? ($value['id'] ?? null);
    return is_numeric($possible_id) ? (int) $possible_id : 0;
  }

  return is_numeric($value) ? (int) $value : 0;
};

$normalize_post_ids = function ($values) use ($normalize_post_id) {
  if (empty($values)) {
    return [];
  }

  if (!is_array($values)) {
    $values = [$values];
  }

  $post_ids = [];
  foreach ($values as $value) {
    $post_id = $normalize_post_id($value);
    if ($post_id > 0 && !in_array($post_id, $post_ids, true)) {
      $post_ids[] = $post_id;
    }
  }

  return $post_ids;
};

$selected_article_ids = $normalize_post_ids(get_field('newsletter_selected_articles'));

$build_authors = function ($source, $is_post_id = false) {
  $authors = [];

  for ($slot = 1; $slot <= 3; $slot++) {
    $name_key = $slot === 1 ? 'author_name' : 'author_' . $slot . '_name';
    $title_key = $slot === 1 ? 'author_title' : 'author_' . $slot . '_title';
    $credentials_key = $slot === 1 ? 'author_credentials' : 'author_' . $slot . '_credentials';
    $image_key = $slot === 1 ? 'author_image' : 'author_' . $slot . '_image';

    if ($is_post_id) {
      $name = trim((string) get_field($name_key, $source));
      $title = trim((string) get_field($title_key, $source));
      $credentials = trim((string) get_field($credentials_key, $source));
      $image = get_field($image_key, $source);
    } else {
      $name = trim((string) ($source[$name_key] ?? ''));
      $title = trim((string) ($source[$title_key] ?? ''));
      $credentials = trim((string) ($source[$credentials_key] ?? ''));
      $image = $source[$image_key] ?? '';
    }

    if ($name === '' && $title === '' && $credentials === '' && empty($image)) {
      continue;
    }

    $authors[] = [
      'name' => $name,
      'title' => $title,
      'credentials' => $credentials,
      'image' => $image,
    ];
  }

  return $authors;
};

if (is_array($selected_article_ids) && !empty($selected_article_ids)) {
  foreach ($selected_article_ids as $article_id) {
    $post_obj = get_post($article_id);
    if (!$post_obj || $post_obj->post_type !== 'newsletter_article') {
      continue;
    }

    if ($post_obj->post_status !== 'publish' && !current_user_can('read_post', $article_id)) {
      continue;
    }

    $images = [];
    for ($j = 1; $j <= 8; $j++) {
      $image = get_field('article_image_' . $j, $article_id);
      if (!empty($image)) {
        if (is_array($image)) {
          $custom_caption = trim((string) get_field('article_image_' . $j . '_caption', $article_id));
          $custom_alt = trim((string) get_field('article_image_' . $j . '_alt', $article_id));

          if ($custom_caption !== '') {
            $image['caption'] = $custom_caption;
          }

          if ($custom_alt !== '') {
            $image['alt'] = $custom_alt;
          }
        }

        $images[] = $image;
      }
    }

    $additional_image_ids = $normalize_post_ids(get_field('article_additional_images', $article_id));
    if (!empty($additional_image_ids)) {
      foreach ($additional_image_ids as $additional_image_id) {
        if ($additional_image_id <= 0) {
          continue;
        }

        $image_url = wp_get_attachment_image_url($additional_image_id, 'large');
        if (empty($image_url)) {
          continue;
        }

        $images[] = [
          'url' => $image_url,
          'caption' => wp_get_attachment_caption($additional_image_id) ?: '',
          'alt' => get_post_meta($additional_image_id, '_wp_attachment_image_alt', true) ?: '',
        ];
      }
    }

    $acf_content = get_field('article_content', $article_id);
    $wp_editor_content = $post_obj->post_content;
    $published_timestamp = (int) get_post_time('U', true, $post_obj);
    $chronology_date = (string) get_field('article_chronology_date', $article_id);
    $manual_timestamp = $chronology_date !== '' ? strtotime($chronology_date) : false;
    $article_timestamp = $manual_timestamp !== false ? (int) $manual_timestamp : $published_timestamp;
    $authors = $build_authors($article_id, true);
    $primary_author = $authors[0] ?? [
      'name' => '',
      'title' => '',
      'credentials' => '',
      'image' => '',
    ];

    $articles[] = [
      'article_id' => get_field('article_id', $article_id) ?: sanitize_title($post_obj->post_title),
      'article_title' => $post_obj->post_title,
      'author_name' => $primary_author['name'],
      'author_title' => $primary_author['title'],
      'author_credentials' => $primary_author['credentials'],
      'author_image' => $primary_author['image'],
      'authors' => $authors,
      'article_content' => !empty($wp_editor_content) ? apply_filters('the_content', $wp_editor_content) : $acf_content,
      'article_images' => $images,
      'article_timestamp' => $article_timestamp,
    ];
  }
} else {
  // Fallback for existing page-level article fields.
  for ($i = 1; $i <= 8; $i++) {
    $article = get_field('newsletter_article_' . $i);

    if (!is_array($article)) {
      continue;
    }

    $title = trim((string) ($article['article_title'] ?? ''));
    if ($title === '') {
      continue;
    }

    $images = [];
    for ($j = 1; $j <= 8; $j++) {
      $image = $article['article_image_' . $j] ?? null;
      if (!empty($image)) {
        if (is_array($image)) {
          $custom_caption = trim((string) ($article['article_image_' . $j . '_caption'] ?? ''));
          $custom_alt = trim((string) ($article['article_image_' . $j . '_alt'] ?? ''));

          if ($custom_caption !== '') {
            $image['caption'] = $custom_caption;
          }

          if ($custom_alt !== '') {
            $image['alt'] = $custom_alt;
          }
        }

        $images[] = $image;
      }
    }

    $additional_image_ids = $normalize_post_ids($article['article_additional_images'] ?? []);
    if (!empty($additional_image_ids)) {
      foreach ($additional_image_ids as $additional_image_id) {
        if ($additional_image_id <= 0) {
          continue;
        }

        $image_url = wp_get_attachment_image_url($additional_image_id, 'large');
        if (empty($image_url)) {
          continue;
        }

        $images[] = [
          'url' => $image_url,
          'caption' => wp_get_attachment_caption($additional_image_id) ?: '',
          'alt' => get_post_meta($additional_image_id, '_wp_attachment_image_alt', true) ?: '',
        ];
      }
    }

    $article['article_images'] = $images;
    $article['article_timestamp'] = $i;
    $authors = $build_authors($article, false);
    $primary_author = $authors[0] ?? [
      'name' => '',
      'title' => '',
      'credentials' => '',
      'image' => '',
    ];
    $article['author_name'] = $primary_author['name'];
    $article['author_title'] = $primary_author['title'];
    $article['author_credentials'] = $primary_author['credentials'];
    $article['author_image'] = $primary_author['image'];
    $article['authors'] = $authors;
    $articles[] = $article;
  }
}

// Preserve drag-and-drop order from the Selected Articles relationship field.
// Only apply chronological sorting when using fallback page-level article groups.
if (empty($selected_article_ids)) {
  usort($articles, function ($a, $b) {
    $timestamp_a = (int) ($a['article_timestamp'] ?? 0);
    $timestamp_b = (int) ($b['article_timestamp'] ?? 0);

    if ($timestamp_a === $timestamp_b) {
      return strcasecmp((string) ($a['article_title'] ?? ''), (string) ($b['article_title'] ?? ''));
    }

    return $timestamp_a <=> $timestamp_b;
  });
}

// Create a stable, unique anchor ID for each article so TOC links always match.
$used_anchors = [];
foreach ($articles as $index => $article) {
  $raw_id = trim((string) ($article['article_id'] ?? ''));
  $raw_title = trim((string) ($article['article_title'] ?? ''));

  $base_anchor = $raw_id !== '' ? sanitize_title($raw_id) : sanitize_title($raw_title);
  if ($base_anchor === '') {
    $base_anchor = 'article-' . ($index + 1);
  }

  $anchor = $base_anchor;
  $suffix = 2;
  while (in_array($anchor, $used_anchors, true)) {
    $anchor = $base_anchor . '-' . $suffix;
    $suffix++;
  }

  $used_anchors[] = $anchor;
  $articles[$index]['article_anchor_id'] = $anchor;
}

$total_articles = count($articles);
$max_pages = 4;
$articles_per_page = $total_articles > 0 ? max(1, (int) ceil($total_articles / $max_pages)) : 1;
$total_pages = max(1, (int) ceil($total_articles / $articles_per_page));
$current_page = isset($_GET['nl_page']) ? max(1, absint($_GET['nl_page'])) : 1;
$current_page = min($current_page, $total_pages);

foreach ($articles as $index => $article) {
  $articles[$index]['article_page'] = (int) floor($index / $articles_per_page) + 1;
}

$page_offset = ($current_page - 1) * $articles_per_page;
$visible_articles = array_slice($articles, $page_offset, $articles_per_page);

get_template_part('template-parts/newsletter/header', null, [
  'newsletter' => $newsletter,
]);

get_template_part('template-parts/newsletter/table-of-contents', null, [
  'articles' => $articles,
]);

foreach ($visible_articles as $index => $article) {
  get_template_part('template-parts/newsletter/article', null, [
    'article' => $article,
    'article_index' => $page_offset + $index,
  ]);
}

if ($total_pages > 1) {
  echo '<nav class="newsletter-pagination" aria-label="Newsletter pages">';

  if ($current_page > 1) {
    $prev_page = $current_page - 1;
    $prev_url = $prev_page === 1
      ? remove_query_arg('nl_page', get_permalink())
      : add_query_arg('nl_page', $prev_page, get_permalink());
    echo '<a class="newsletter-page-link newsletter-page-prev" href="' . esc_url($prev_url) . '">Previous</a>';
  }

  for ($page = 1; $page <= $total_pages; $page++) {
    $page_url = $page === 1
      ? remove_query_arg('nl_page', get_permalink())
      : add_query_arg('nl_page', $page, get_permalink());
    $is_current = $page === $current_page;

    echo '<a class="newsletter-page-link' . ($is_current ? ' is-current' : '') . '" href="' . esc_url($page_url) . '"' . ($is_current ? ' aria-current="page"' : '') . '>' . esc_html((string) $page) . '</a>';
  }

  if ($current_page < $total_pages) {
    $next_page = $current_page + 1;
    $next_url = add_query_arg('nl_page', $next_page, get_permalink());
    echo '<a class="newsletter-page-link newsletter-page-next" href="' . esc_url($next_url) . '">Next</a>';
  }

  echo '</nav>';
}

get_template_part('template-parts/newsletter/footer-nav');

get_footer();
