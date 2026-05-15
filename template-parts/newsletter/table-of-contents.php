<?php
$articles = $args['articles'] ?? [];
$article_count = count($articles);
$newsletter_url = get_permalink();
?>

<section class="newsletter-toc" id="table-of-contents">
  <div class="newsletter-container">
    <details class="newsletter-toc-toggle" open>
      <summary class="newsletter-toc-summary">
        <span class="newsletter-toc-summary-main">
          <span class="newsletter-toc-title">Table of Contents</span>
          <span class="newsletter-toc-count"><?php echo esc_html($article_count . ' ' . ($article_count === 1 ? 'Article' : 'Articles')); ?></span>
        </span>
        <span class="newsletter-toc-chevron" aria-hidden="true"></span>
      </summary>

      <ol class="newsletter-toc-list">
        <?php foreach ($articles as $index => $article) : 
          $title = $article['article_title'] ?? '';
          $authors = $article['authors'] ?? [];
          if (!is_array($authors) || empty($authors)) {
            $authors = array(
              array(
                'name' => trim((string) ($article['author_name'] ?? '')),
                'credentials' => trim((string) ($article['author_credentials'] ?? '')),
                'image' => $article['author_image'] ?? '',
              ),
            );
          }

          $author_items = [];

          foreach ($authors as $author_item) {
            $name = trim((string) ($author_item['name'] ?? ''));
            $credentials = trim((string) ($author_item['credentials'] ?? ''));
            $image = $author_item['image'] ?? '';

            $line = '';
            if ($name !== '' && $credentials !== '') {
              $line = $name . ', ' . $credentials;
            } elseif ($name !== '') {
              $line = $name;
            } elseif ($credentials !== '') {
              $line = $credentials;
            }

            if ($line === '') {
              continue;
            }

            $image_url = '';
            if (is_array($image)) {
              $image_url = $image['sizes']['thumbnail'] ?? ($image['url'] ?? '');
            } elseif (is_numeric($image)) {
              $image_url = wp_get_attachment_image_url((int) $image, 'thumbnail');
            } else {
              $image_url = (string) $image;
            }

            $author_items[] = [
              'line' => $line,
              'image_url' => $image_url,
            ];
          }

          $id = $article['article_anchor_id'] ?? sanitize_title(trim((string) $title));
          $article_page = max(1, (int) ($article['article_page'] ?? 1));
          $page_url = $article_page === 1
            ? remove_query_arg('nl_page', $newsletter_url)
            : add_query_arg('nl_page', $article_page, $newsletter_url);
          $article_url = $page_url . '#' . $id;
        ?>
          <li>
            <a href="<?php echo esc_url($article_url); ?>">
              <span class="newsletter-toc-number"><?php echo esc_html(str_pad($index + 1, 2, '0', STR_PAD_LEFT)); ?></span>
              <strong class="newsletter-toc-title-row"><?php echo esc_html($title); ?></strong>
              <?php if (!empty($author_items)) : ?>
                <span class="newsletter-toc-authors">
                  <?php foreach ($author_items as $author_index => $toc_author_item) : ?>
                    <span class="newsletter-toc-author-item">
                      <?php if (!empty($toc_author_item['image_url'])) : ?>
                        <img
                          class="newsletter-toc-author-image"
                          src="<?php echo esc_url($toc_author_item['image_url']); ?>"
                          alt="<?php echo esc_attr($toc_author_item['line']); ?>"
                        >
                      <?php else : ?>
                        <span class="newsletter-toc-author-image newsletter-toc-author-placeholder" aria-hidden="true"></span>
                      <?php endif; ?>
                      <em class="newsletter-toc-byline"><?php echo esc_html($toc_author_item['line']); ?></em>
                    </span>

                    <?php if ($author_index === 0 && count($author_items) > 1) : ?>
                      <span class="newsletter-toc-separator" aria-hidden="true">|</span>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </span>
              <?php endif; ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ol>
    </details>
  </div>
</section>
