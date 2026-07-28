<?php
/**
 * Template Name: Newsletter Template
 * Template Post Type: page
 */

get_header();

$newsletter = [
  'title'       => avf_get_field('newsletter_title'),
  'issue'       => avf_get_field('newsletter_issue'),
  'subtitle'    => avf_get_field('newsletter_subtitle'),
  'cover_image' => avf_get_field('newsletter_cover_image'),
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

$build_authors = function ($source, $is_post_id = false) {
  $authors = [];

  for ($slot = 1; $slot <= 3; $slot++) {
    $name_key = $slot === 1 ? 'author_name' : 'author_' . $slot . '_name';
    $title_key = $slot === 1 ? 'author_title' : 'author_' . $slot . '_title';
    $credentials_key = $slot === 1 ? 'author_credentials' : 'author_' . $slot . '_credentials';
    $image_key = $slot === 1 ? 'author_image' : 'author_' . $slot . '_image';

    if ($is_post_id) {
      $name = trim((string) avf_get_field($name_key, $source));
      $title = trim((string) avf_get_field($title_key, $source));
      $credentials = trim((string) avf_get_field($credentials_key, $source));
      $image = avf_get_field($image_key, $source);
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

for ($i = 1; $i <= 8; $i++) {
    $article = avf_get_field('newsletter_article_' . $i);

    if (!is_array($article)) {
      continue;
    }

    $title = trim((string) ($article['article_title'] ?? ''));
    if ($title === '') {
      continue;
    }

    $images = [];
    for ($j = 1; $j <= 3; $j++) {
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
    $raw_order = trim((string) ($article['article_order'] ?? ''));
    $article['article_order'] = is_numeric($raw_order) ? (int) $raw_order : $i;
    $article['article_slot'] = $i;
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

usort($articles, function ($a, $b) {
  $order_a = (int) ($a['article_order'] ?? ($a['article_slot'] ?? 0));
  $order_b = (int) ($b['article_order'] ?? ($b['article_slot'] ?? 0));

  if ($order_a !== $order_b) {
    return $order_a <=> $order_b;
  }

  $slot_a = (int) ($a['article_slot'] ?? 0);
  $slot_b = (int) ($b['article_slot'] ?? 0);

  if ($slot_a !== $slot_b) {
    return $slot_a <=> $slot_b;
  }

  return strcasecmp((string) ($a['article_title'] ?? ''), (string) ($b['article_title'] ?? ''));
});

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

$get_newsletter_page_url = function ($page) use ($articles, $articles_per_page) {
  $page = max(1, (int) $page);
  $page_url = $page === 1
    ? remove_query_arg('nl_page', get_permalink())
    : add_query_arg('nl_page', $page, get_permalink());

  $first_article_index = ($page - 1) * $articles_per_page;
  $first_article_anchor = $articles[$first_article_index]['article_anchor_id'] ?? '';

  if ($first_article_anchor !== '') {
    $page_url .= '#' . $first_article_anchor;
  }

  return $page_url;
};

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
    $prev_url = $get_newsletter_page_url($prev_page);
    echo '<a class="newsletter-page-link newsletter-page-prev" href="' . esc_url($prev_url) . '">Previous</a>';
  }

  for ($page = 1; $page <= $total_pages; $page++) {
    $page_url = $get_newsletter_page_url($page);
    $is_current = $page === $current_page;

    echo '<a class="newsletter-page-link' . ($is_current ? ' is-current' : '') . '" href="' . esc_url($page_url) . '"' . ($is_current ? ' aria-current="page"' : '') . '>' . esc_html((string) $page) . '</a>';
  }

  if ($current_page < $total_pages) {
    $next_page = $current_page + 1;
    $next_url = $get_newsletter_page_url($next_page);
    echo '<a class="newsletter-page-link newsletter-page-next" href="' . esc_url($next_url) . '">Next</a>';
  }

  echo '</nav>';
}

get_template_part('template-parts/newsletter/footer-nav');

get_footer();
