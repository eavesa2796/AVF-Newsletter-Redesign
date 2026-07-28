<?php
$images = $args['images'] ?? [];
$lightbox_group = $args['lightbox_group'] ?? 'newsletter';
?>

<?php if (!empty($images)) : ?>
  <div class="newsletter-image-grid">
    <?php
    $rendered_image_index = 0;
    foreach ($images as $image) :
      $url = '';
      $caption = '';
      $alt = '';

      if (is_array($image)) {
        $url = $image['url'] ?? '';
        $caption = $image['caption'] ?? '';
        $alt = $image['alt'] ?? $caption;
      } elseif (is_numeric($image)) {
        $attachment_id = (int) $image;
        $url = wp_get_attachment_image_url($attachment_id, 'large');
        $caption = wp_get_attachment_caption($attachment_id) ?: '';
        $alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true) ?: $caption;
      } else {
        $url = (string) $image;
        $alt = $caption;
      }
    ?>

      <?php if (!empty($url)) : ?>
        <figure class="newsletter-image-card">
          <a
            class="newsletter-lightbox-trigger"
            href="<?php echo esc_url($url); ?>"
            data-lightbox-group="<?php echo esc_attr($lightbox_group); ?>"
            data-lightbox-index="<?php echo esc_attr((string) $rendered_image_index); ?>"
            data-lightbox-caption="<?php echo esc_attr($caption); ?>"
          >
            <img
              src="<?php echo esc_url($url); ?>"
              alt="<?php echo esc_attr($alt); ?>"
            >
          </a>

          <?php if (!empty($caption)) : ?>
            <figcaption><?php echo wp_kses_post($caption); ?></figcaption>
          <?php endif; ?>
        </figure>
        <?php $rendered_image_index++; ?>
      <?php endif; ?>

    <?php endforeach; ?>
  </div>
<?php endif; ?>
