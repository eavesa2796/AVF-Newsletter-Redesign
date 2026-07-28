<?php
$article = $args['article'] ?? [];
$article_index = (int) ($args['article_index'] ?? 0);

$title = $article['article_title'] ?? '';
$fallback_id = sanitize_title(trim((string) $title));
if ($fallback_id === '') {
  $fallback_id = 'article-' . ($article_index + 1);
}

$id = $article['article_anchor_id'] ?? $fallback_id;
$author_name = $article['author_name'] ?? '';
$author_credentials = $article['author_credentials'] ?? '';
$author_image = $article['author_image'] ?? '';
$authors = $article['authors'] ?? [];
$article_content = $article['article_content'] ?? '';
$article_images = $article['article_images'] ?? [];
$show_bottom_banner = !empty($article['article_show_bottom_banner_ad']);
$bottom_banner_image = $article['article_bottom_banner_ad_image'] ?? [];
$bottom_banner_url = trim((string) ($article['article_bottom_banner_ad_url'] ?? ''));
$bottom_banner_alt = trim((string) ($article['article_bottom_banner_ad_alt'] ?? ''));

if ($bottom_banner_alt === '' && is_array($bottom_banner_image)) {
  $bottom_banner_alt = trim((string) ($bottom_banner_image['alt'] ?? ''));
}

if ($bottom_banner_alt === '') {
  $bottom_banner_alt = 'Advertisement';
}
?>

<?php if (!empty($title)) : ?>
  <article class="newsletter-article" id="<?php echo esc_attr($id); ?>">
    <header class="newsletter-article-header">
      <h2><?php echo esc_html($title); ?></h2>
    </header>

    <?php
    get_template_part('template-parts/newsletter/author-card', null, array(
      'name' => $author_name,
      'credentials' => $author_credentials,
      'image' => $author_image,
      'authors' => $authors,
    ));
    ?>

    <?php if (!empty($article_content)) : ?>
      <div class="newsletter-article-body">
        <?php echo wp_kses_post($article_content); ?>
      </div>
    <?php endif; ?>

    <?php
    get_template_part('template-parts/newsletter/image-grid', null, array(
      'images' => $article_images,
      'lightbox_group' => $id,
    ));
    ?>

    <?php if ($show_bottom_banner && !empty($bottom_banner_image['url'])) : ?>
      <aside class="newsletter-article-banner" aria-label="Advertisement">
        <?php if ($bottom_banner_url !== '') : ?>
          <a
            href="<?php echo esc_url($bottom_banner_url); ?>"
            target="_blank"
            rel="sponsored noopener noreferrer"
          >
        <?php endif; ?>

        <img
          src="<?php echo esc_url($bottom_banner_image['url']); ?>"
          alt="<?php echo esc_attr($bottom_banner_alt); ?>"
          loading="lazy"
        >

        <?php if ($bottom_banner_url !== '') : ?>
          </a>
        <?php endif; ?>
      </aside>
    <?php endif; ?>
  </article>
<?php endif; ?>
