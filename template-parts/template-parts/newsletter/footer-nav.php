<?php
$links = $args['links'] ?? [
  [
    'label' => 'Back to Top',
    'url' => '#top',
    'class' => 'newsletter-btn-primary',
  ],
  [
    'label' => 'Table of Contents',
    'url' => '#table-of-contents',
    'class' => 'newsletter-btn-secondary',
  ],
];
?>

<nav class="newsletter-footer-nav" aria-label="Newsletter navigation">
  <?php foreach ($links as $link) : 
    $label = $link['label'] ?? '';
    $url = $link['url'] ?? '#';
    $class = $link['class'] ?? 'newsletter-btn-secondary';
  ?>

    <?php if (!empty($label)) : ?>
      <a
        href="<?php echo esc_url($url); ?>"
        class="newsletter-btn <?php echo esc_attr($class); ?>"
      >
        <?php echo esc_html($label); ?>
      </a>
    <?php endif; ?>

  <?php endforeach; ?>
</nav>