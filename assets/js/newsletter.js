(function ($) {
  var activeGroup = "";
  var activeIndex = 0;
  var lastFocusedElement = null;

  var lightboxMarkup =
    '<div class="newsletter-lightbox" aria-hidden="true" role="dialog" aria-modal="true">' +
    '  <button type="button" class="newsletter-lightbox-close" aria-label="Close image">\u00d7</button>' +
    '  <button type="button" class="newsletter-lightbox-nav newsletter-lightbox-prev" aria-label="Previous image">\u2039</button>' +
    '  <button type="button" class="newsletter-lightbox-nav newsletter-lightbox-next" aria-label="Next image">\u203a</button>' +
    '  <div class="newsletter-lightbox-content">' +
    '    <img class="newsletter-lightbox-image" src="" alt="">' +
    '    <div class="newsletter-lightbox-caption"></div>' +
    "  </div>" +
    "</div>";

  function ensureLightbox() {
    var $existing = $(".newsletter-lightbox");
    if ($existing.length) {
      return $existing;
    }

    $("body").append(lightboxMarkup);
    return $(".newsletter-lightbox");
  }

  function getGroupItems(group) {
    return $(".newsletter-lightbox-trigger").filter(function () {
      return ($(this).attr("data-lightbox-group") || "newsletter") === group;
    });
  }

  function isImageUrl(url) {
    return /\.(?:jpe?g|png|gif|webp|avif|bmp|svg)(?:[?#].*)?$/i.test(url || "");
  }

  function getImageCaption($img) {
    var caption = $img.closest("figure").find("figcaption").first().text();
    return $.trim(caption || $img.attr("alt") || "");
  }

  function enhanceEditorImages() {
    $(".newsletter-article-body img").each(function () {
      var $img = $(this);
      var $existingTrigger = $img.closest(".newsletter-lightbox-trigger");
      if ($existingTrigger.length) {
        return;
      }

      var $article = $img.closest(".newsletter-article");
      var group = ($article.attr("id") || "newsletter") + "-content";
      var caption = getImageCaption($img);
      var $parentLink = $img.parent("a");

      if ($parentLink.length) {
        if (!isImageUrl($parentLink.attr("href"))) {
          return;
        }

        $parentLink
          .addClass("newsletter-lightbox-trigger")
          .attr("data-lightbox-group", group)
          .attr("data-lightbox-caption", caption);
        return;
      }

      var src = $img.attr("src") || "";
      if (!src) {
        return;
      }

      $img.wrap(
        $("<a>", {
          "class": "newsletter-lightbox-trigger",
          href: src,
          "data-lightbox-group": group,
          "data-lightbox-caption": caption,
        })
      );
    });
  }

  function renderActiveItem() {
    var $lightbox = ensureLightbox();
    var $items = getGroupItems(activeGroup);
    var total = $items.length;

    if (!total) {
      return;
    }

    if (activeIndex < 0) {
      activeIndex = total - 1;
    }
    if (activeIndex >= total) {
      activeIndex = 0;
    }

    var $item = $items.eq(activeIndex);
    var src = $item.attr("href") || "";
    var $img = $item.find("img").first();
    var alt = $img.attr("alt") || "";
    var caption = $item.attr("data-lightbox-caption") || "";

    $lightbox
      .find(".newsletter-lightbox-image")
      .attr("src", src)
      .attr("alt", alt);

    $lightbox.find(".newsletter-lightbox-caption").text(caption);

    $lightbox
      .find(".newsletter-lightbox-prev, .newsletter-lightbox-next")
      .toggle(total > 1);
  }

  function openLightbox(group, index) {
    var $lightbox = ensureLightbox();
    lastFocusedElement = document.activeElement;
    activeGroup = group;
    activeIndex = index;
    renderActiveItem();
    $lightbox.addClass("is-open").attr("aria-hidden", "false");
    $("body").css("overflow", "hidden");
    $lightbox.find(".newsletter-lightbox-close").trigger("focus");
  }

  function closeLightbox() {
    var $lightbox = $(".newsletter-lightbox");
    if (!$lightbox.length) {
      return;
    }

    $lightbox.removeClass("is-open").attr("aria-hidden", "true");
    $lightbox.find(".newsletter-lightbox-image").attr("src", "").attr("alt", "");
    $("body").css("overflow", "");

    if (lastFocusedElement && document.contains(lastFocusedElement)) {
      $(lastFocusedElement).trigger("focus");
    }
  }

  function navigate(step) {
    var $lightbox = $(".newsletter-lightbox");
    if (!$lightbox.length || !$lightbox.hasClass("is-open")) {
      return;
    }

    activeIndex += step;
    renderActiveItem();
  }

  $(document).on("click", ".newsletter-lightbox-trigger", function (event) {
    event.preventDefault();

    var $trigger = $(this);
    var group = $trigger.attr("data-lightbox-group") || "newsletter";
    var index = getGroupItems(group).index($trigger);
    if (index < 0) {
      index = 0;
    }

    openLightbox(group, index);
  });

  $(enhanceEditorImages);

  $(document).on("click", ".newsletter-lightbox-close", function () {
    closeLightbox();
  });

  $(document).on("click", ".newsletter-lightbox-prev", function (event) {
    event.stopPropagation();
    navigate(-1);
  });

  $(document).on("click", ".newsletter-lightbox-next", function (event) {
    event.stopPropagation();
    navigate(1);
  });

  $(document).on("click", ".newsletter-lightbox", function (event) {
    if ($(event.target).is(".newsletter-lightbox")) {
      closeLightbox();
    }
  });

  $(document).on("keydown", function (event) {
    if (event.key === "Escape") {
      closeLightbox();
      return;
    }

    if (event.key === "ArrowLeft") {
      navigate(-1);
      return;
    }

    if (event.key === "ArrowRight") {
      navigate(1);
    }
  });
})(jQuery);
