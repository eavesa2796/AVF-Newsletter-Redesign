<?php
$newsletter = $args['newsletter'] ?? [];

$cover_image = $newsletter['cover_image'] ?? '';
$cover_image_url = '';
if (is_array($cover_image)) {
  $cover_image_url = $cover_image['url'] ?? '';
} elseif (is_numeric($cover_image)) {
  $cover_image_url = wp_get_attachment_image_url((int) $cover_image, 'large');
} else {
  $cover_image_url = $cover_image;
}
?>

<section class="newsletter-header" id="top">
  <div class="newsletter-header-inner">
    <div class="newsletter-header-content">
      <span class="newsletter-label">Newsletter</span>

      <?php if (!empty($newsletter['issue'] ?? '')) : ?>
        <p class="newsletter-issue"><?php echo esc_html($newsletter['issue']); ?></p>
      <?php endif; ?>

      <h1><?php echo esc_html($newsletter['title'] ?? 'Newsletter'); ?></h1>

      <?php if (!empty($newsletter['subtitle'] ?? '')) : ?>
        <p class="newsletter-subtitle"><?php echo esc_html($newsletter['subtitle']); ?></p>
      <?php endif; ?>

      <a href="#table-of-contents" class="newsletter-button">
        Table of Contents
      </a>
    </div>

    <?php if (!empty($cover_image_url)) : ?>
      <div class="newsletter-header-image-wrap">
        <img
          class="newsletter-header-image"
          src="<?php echo esc_url($cover_image_url); ?>"
          alt="<?php echo esc_attr($newsletter['title'] ?? 'Newsletter cover image'); ?>"
        >
      </div>
    <?php else : ?>
      <div class="newsletter-header-image-wrap newsletter-header-image-placeholder" aria-hidden="true"></div>
    <?php endif; ?>
  </div>
</section>