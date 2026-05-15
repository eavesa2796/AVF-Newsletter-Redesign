<?php
$authors = $args['authors'] ?? [];

if (!is_array($authors) || empty($authors)) {
  $authors = array(
    array(
      'name' => $args['name'] ?? ($args['author'] ?? ''),
      'title' => $args['author_title'] ?? '',
      'credentials' => $args['credentials'] ?? ($args['role'] ?? ''),
      'image' => $args['image'] ?? ($args['headshot'] ?? ''),
    ),
  );
}
?>

<?php if (!empty($authors)) : ?>
  <div class="newsletter-author-cards">
    <?php foreach ($authors as $author_item) :
      $name = trim((string) ($author_item['name'] ?? ''));
      $title = trim((string) ($author_item['title'] ?? ''));
      $credentials = trim((string) ($author_item['credentials'] ?? ''));
      $image = $author_item['image'] ?? '';

      $image_url = '';
      if (is_array($image)) {
        $image_url = $image['sizes']['thumbnail'] ?? ($image['url'] ?? '');
      } elseif (is_numeric($image)) {
        $image_url = wp_get_attachment_image_url((int) $image, 'thumbnail');
      } else {
        $image_url = $image;
      }

      if ($name === '' && $title === '' && $credentials === '' && empty($image_url)) {
        continue;
      }
    ?>
      <div class="newsletter-author-card">
        <?php if (!empty($image_url)) : ?>
          <img
            class="newsletter-author-image"
            src="<?php echo esc_url($image_url); ?>"
            alt="<?php echo esc_attr($name); ?>"
          >
        <?php endif; ?>

        <div class="newsletter-author-meta">
          <?php if (!empty($name)) : ?>
            <strong><?php echo esc_html($name); ?></strong>
          <?php endif; ?>

          <?php if (!empty($title)) : ?>
            <span><?php echo esc_html($title); ?></span>
          <?php endif; ?>

          <?php if (!empty($credentials)) : ?>
            <span><?php echo esc_html($credentials); ?></span>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>