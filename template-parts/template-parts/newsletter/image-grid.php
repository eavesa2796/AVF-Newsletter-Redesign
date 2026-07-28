<?php
$images = $args['images'] ?? [];
?>

<?php if (!empty($images)) : ?>
  <div class="newsletter-image-grid">
    <?php foreach ($images as $image) : 
      $url = $image['url'] ?? '';
      $caption = $image['caption'] ?? '';
      $alt = $image['alt'] ?? $caption;
    ?>

      <?php if (!empty($url)) : ?>
        <figure class="newsletter-image-card">
          <img
            src="<?php echo esc_url($url); ?>"
            alt="<?php echo esc_attr($alt); ?>"
          >

          <?php if (!empty($caption)) : ?>
            <figcaption><?php echo wp_kses_post($caption); ?></figcaption>
          <?php endif; ?>
        </figure>
      <?php endif; ?>

    <?php endforeach; ?>
  </div>
<?php endif; ?>
