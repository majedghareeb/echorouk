(function () {
  var SAVED_ARTICLES_STORAGE_KEY = 'echorouk_saved_article_links';

  function normalizeSavedArticleUrl(url) {
    if (typeof url !== 'string') {
      return '';
    }

    var value = url.trim();
    if (!value) {
      return '';
    }

    try {
      value = decodeURIComponent(value);
    } catch (error) {
      // Keep original value when not URI-encoded.
    }

    try {
      return new URL(value, window.location.origin).href;
    } catch (error) {
      return value;
    }
  }

  function normalizeSavedArticleLinks(list) {
    if (!Array.isArray(list)) {
      return [];
    }

    var links = [];
    for (var i = 0; i < list.length; i += 1) {
      var item = list[i];
      if (typeof item === 'string' && item) {
        var normalized = normalizeSavedArticleUrl(item);
        if (normalized) {
          links.push(normalized);
        }
      } else if (item && typeof item === 'object' && typeof item.url === 'string' && item.url) {
        var normalizedObjectUrl = normalizeSavedArticleUrl(item.url);
        if (normalizedObjectUrl) {
          links.push(normalizedObjectUrl);
        }
      }
    }

    return Array.from(new Set(links));
  }

  function getSavedArticleLinks() {
    try {
      var raw = window.localStorage.getItem(SAVED_ARTICLES_STORAGE_KEY);
      var parsed = raw ? JSON.parse(raw) : [];
      return normalizeSavedArticleLinks(parsed);
    } catch (error) {
      return [];
    }
  }

  function setSavedArticleLinks(links) {
    try {
      window.localStorage.setItem(SAVED_ARTICLES_STORAGE_KEY, JSON.stringify(links));
    } catch (error) {
      // Ignore storage exceptions.
    }
  }

  function dispatchSavedArticlesChanged(links) {
    document.dispatchEvent(
      new CustomEvent('echorouk:saved-articles-changed', {
        detail: { count: Array.isArray(links) ? links.length : 0 }
      })
    );
  }

  var toggle = document.querySelector('[data-nav-toggle]');
  var menu = document.querySelector('[data-primary-menu]');

  if (toggle && menu) {
    toggle.addEventListener('click', function () {
      var isOpen = menu.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
  }

  var summaryToggle = document.querySelector('[data-summary-toggle]');
  var summaryPanel = document.getElementById('single-article-summary');

  if (summaryToggle && summaryPanel) {
    summaryToggle.addEventListener('click', function (event) {
      event.preventDefault();

      var isHidden = summaryPanel.hidden;
      summaryPanel.hidden = !isHidden;
      summaryToggle.setAttribute('aria-expanded', isHidden ? 'true' : 'false');

      if (isHidden) {
        summaryPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  }

  var ttsToggle = document.querySelector('[data-tts-toggle]');
  var ttsPanel = document.getElementById('single-article-ai-player-area');

  if (ttsToggle && ttsPanel) {
    ttsToggle.addEventListener('click', function (event) {
      event.preventDefault();

      var isHidden = ttsPanel.hidden;
      ttsPanel.hidden = !isHidden;
      ttsToggle.setAttribute('aria-expanded', isHidden ? 'true' : 'false');

      if (isHidden) {
        ttsPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  }

  function initNewsTickerRotation() {
    var tickerList = document.querySelector('[data-news-ticker-list]');
    if (!tickerList) {
      return;
    }

    var items = Array.prototype.slice.call(tickerList.querySelectorAll('.breaking-bar__item'));
    if (items.length < 2) {
      return;
    }

    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      return;
    }

    var interval = parseInt(tickerList.getAttribute('data-ticker-interval'), 10);
    if (!interval || interval < 1500) {
      interval = 4500;
    }

    var currentIndex = 0;
    var isPaused = false;

    function setActive(index) {
      for (var i = 0; i < items.length; i += 1) {
        var item = items[i];
        var active = i === index;
        item.classList.toggle('is-active', active);
        if (!active) {
          item.classList.remove('is-leaving');
        }
        item.setAttribute('aria-hidden', active ? 'false' : 'true');
      }
    }

    function moveNext() {
      if (isPaused) {
        return;
      }

      var current = items[currentIndex];
      var nextIndex = (currentIndex + 1) % items.length;
      var next = items[nextIndex];

      current.classList.remove('is-active');
      current.classList.add('is-leaving');
      current.setAttribute('aria-hidden', 'true');

      next.classList.remove('is-leaving');
      next.classList.add('is-active');
      next.setAttribute('aria-hidden', 'false');

      window.setTimeout(function () {
        current.classList.remove('is-leaving');
      }, 460);

      currentIndex = nextIndex;
    }

    setActive(0);
    window.setInterval(moveNext, interval);

    tickerList.addEventListener('mouseenter', function () {
      isPaused = true;
    });

    tickerList.addEventListener('mouseleave', function () {
      isPaused = false;
    });

    tickerList.addEventListener('focusin', function () {
      isPaused = true;
    });

    tickerList.addEventListener('focusout', function (event) {
      if (!tickerList.contains(event.relatedTarget)) {
        isPaused = false;
      }
    });
  }

  initNewsTickerRotation();

  function copyToClipboard(text) {
    if (!text) {
      return Promise.reject(new Error('Empty text'));
    }

    if (navigator.clipboard && window.isSecureContext) {
      return navigator.clipboard.writeText(text);
    }

    return new Promise(function (resolve, reject) {
      var input = document.createElement('textarea');
      input.value = text;
      input.setAttribute('readonly', 'readonly');
      input.style.position = 'fixed';
      input.style.top = '-9999px';
      input.style.left = '-9999px';
      document.body.appendChild(input);
      input.focus();
      input.select();

      try {
        var copied = document.execCommand('copy');
        document.body.removeChild(input);
        if (copied) {
          resolve();
        } else {
          reject(new Error('Copy command failed'));
        }
      } catch (error) {
        document.body.removeChild(input);
        reject(error);
      }
    });
  }

  function setActionLabel(button, label) {
    if (!button || !label) {
      return;
    }

    var textNode = button.querySelector('span');
    if (textNode) {
      textNode.textContent = label;
    }
  }

  function initCopyShareActions() {
    var copyButtons = document.querySelectorAll('[data-share-copy]');
    if (!copyButtons.length) {
      return;
    }

    Array.prototype.forEach.call(copyButtons, function (button) {
      button.addEventListener('click', function () {
        var url = button.getAttribute('data-share-url') || window.location.href;
        var defaultLabel = button.getAttribute('data-label-default') || '';
        var copiedLabel = button.getAttribute('data-label-copied') || defaultLabel;

        copyToClipboard(url).then(function () {
          setActionLabel(button, copiedLabel);
          window.setTimeout(function () {
            setActionLabel(button, defaultLabel);
          }, 1400);
        });
      });
    });
  }

  function initSaveArticleActions() {
    var saveButtons = document.querySelectorAll('[data-save-article]');
    if (!saveButtons.length) {
      return;
    }

    function updateState(button, isSaved) {
      var defaultLabel = button.getAttribute('data-label-default') || '';
      var savedLabel = button.getAttribute('data-label-saved') || defaultLabel;
      button.setAttribute('aria-pressed', isSaved ? 'true' : 'false');
      button.classList.toggle('is-saved', !!isSaved);
      setActionLabel(button, isSaved ? savedLabel : defaultLabel);
    }

    var savedLinks = getSavedArticleLinks();

    Array.prototype.forEach.call(saveButtons, function (button) {
      var postUrl = normalizeSavedArticleUrl(button.getAttribute('data-post-url') || window.location.href);

      updateState(button, savedLinks.indexOf(postUrl) !== -1);

      button.addEventListener('click', function () {
        var index = savedLinks.indexOf(postUrl);
        if (index === -1) {
          savedLinks.push(postUrl);
          updateState(button, true);
        } else {
          savedLinks.splice(index, 1);
          updateState(button, false);
        }

        setSavedArticleLinks(savedLinks);
        dispatchSavedArticlesChanged(savedLinks);
      });
    });
  }

  function initSavedArticlesBadge() {
    var badge = document.querySelector('[data-saved-articles-badge]');
    if (!badge) {
      return;
    }

    function render(count) {
      var total = typeof count === 'number' ? count : 0;
      if (total < 1) {
        badge.hidden = true;
        badge.textContent = '0';
        return;
      }

      badge.hidden = false;
      badge.textContent = total > 99 ? '99+' : String(total);
    }

    render(getSavedArticleLinks().length);

    document.addEventListener('echorouk:saved-articles-changed', function (event) {
      var count = event && event.detail && typeof event.detail.count === 'number' ? event.detail.count : 0;
      render(count);
    });
  }

  function initSavedArticlesPage() {
    var page = document.querySelector('[data-saved-articles-page]');
    if (!page) {
      return;
    }

    var list = page.querySelector('[data-saved-articles-list]');
    var empty = page.querySelector('[data-saved-articles-empty]');
    if (!list || !empty) {
      return;
    }
    var wrap = page.querySelector('[data-remove-label]');
    var removeLabel = wrap ? wrap.getAttribute('data-remove-label') : 'Remove';

    function render() {
      var links = getSavedArticleLinks();
      list.innerHTML = '';

      if (!links.length) {
        empty.hidden = false;
        return;
      }

      empty.hidden = true;

      function getLabelFromUrl(url) {
        try {
          var parsed = new URL(url, window.location.origin);
          var path = parsed.pathname.replace(/\/+$/, '');
          var segment = path.split('/').pop() || parsed.hostname;
          var text = segment.replace(/[-_]+/g, ' ').trim();
          return text ? text : parsed.hostname;
        } catch (error) {
          return url;
        }
      }

      function maybeHydrateFromOEmbed(card, url) {
        var sameOrigin = false;
        try {
          sameOrigin = new URL(url, window.location.origin).origin === window.location.origin;
        } catch (error) {
          sameOrigin = false;
        }

        if (!sameOrigin || !window.fetch) {
          return;
        }

        var endpoint = window.location.origin + '/wp-json/oembed/1.0/embed?url=' + encodeURIComponent(url);

        fetch(endpoint, { credentials: 'same-origin' })
          .then(function (response) {
            if (!response.ok) {
              return null;
            }
            return response.json();
          })
          .then(function (data) {
            if (!data || !card.isConnected) {
              return;
            }

            var titleNode = card.querySelector('[data-saved-card-title]');
            if (titleNode && data.title) {
              titleNode.textContent = data.title;
            }

            var imageNode = card.querySelector('[data-saved-card-image]');
            if (imageNode && data.thumbnail_url) {
              imageNode.src = data.thumbnail_url;
              imageNode.alt = data.title || '';
            }
          })
          .catch(function () {
            // Keep fallback card data when oEmbed is unavailable.
          });
      }

      links.forEach(function (url) {
        var item = document.createElement('li');
        item.className = 'saved-articles-list__item';

        var card = document.createElement('article');
        card.className = 'news-card news-card--compact saved-news-card';

        var imageLink = document.createElement('a');
        imageLink.className = 'news-card__image-link';
        imageLink.href = url;

        var image = document.createElement('img');
        image.className = 'news-card__image';
        image.setAttribute('data-saved-card-image', '1');
        image.src = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 640 360%22%3E%3Crect width=%22640%22 height=%22360%22 fill=%22%23f0f4f8%22/%3E%3C/svg%3E';
        image.alt = '';
        image.loading = 'lazy';
        image.decoding = 'async';
        imageLink.appendChild(image);

        var body = document.createElement('div');
        body.className = 'news-card__body';

        var title = document.createElement('h2');
        title.className = 'news-card__title';
        var titleLink = document.createElement('a');
        titleLink.href = url;
        titleLink.setAttribute('data-saved-card-title', '1');
        titleLink.textContent = getLabelFromUrl(url);
        title.appendChild(titleLink);

        var bodyInner = document.createElement('div');
        bodyInner.className = 'saved-news-card__content';
        bodyInner.appendChild(title);

        var removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'saved-news-card__remove';
        removeButton.textContent = removeLabel || 'Remove';
        removeButton.addEventListener('click', function (event) {
          event.preventDefault();
          var updatedLinks = getSavedArticleLinks().filter(function (savedUrl) {
            return savedUrl !== url;
          });
          setSavedArticleLinks(updatedLinks);
          dispatchSavedArticlesChanged(updatedLinks);
        });

        body.appendChild(bodyInner);
        body.appendChild(removeButton);

        card.appendChild(imageLink);
        card.appendChild(body);
        item.appendChild(card);
        list.appendChild(item);

        maybeHydrateFromOEmbed(card, url);
      });
    }

    render();

    document.addEventListener('echorouk:saved-articles-changed', render);
  }

  function initShareDropdowns() {
    var dropdowns = document.querySelectorAll('[data-share-dropdown]');
    if (!dropdowns.length) {
      return;
    }

    function closeAll(except) {
      Array.prototype.forEach.call(dropdowns, function (dropdown) {
        if (except && dropdown === except) {
          return;
        }
        var toggle = dropdown.querySelector('[data-share-toggle]');
        var menu = dropdown.querySelector('.single-article__share-menu');
        if (toggle) {
          toggle.setAttribute('aria-expanded', 'false');
        }
        if (menu) {
          menu.hidden = true;
        }
      });
    }

    Array.prototype.forEach.call(dropdowns, function (dropdown) {
      var toggle = dropdown.querySelector('[data-share-toggle]');
      var menu = dropdown.querySelector('.single-article__share-menu');
      if (!toggle || !menu) {
        return;
      }

      toggle.addEventListener('click', function (event) {
        event.preventDefault();
        var open = toggle.getAttribute('aria-expanded') === 'true';
        closeAll(dropdown);
        toggle.setAttribute('aria-expanded', open ? 'false' : 'true');
        menu.hidden = open;
      });
    });

    document.addEventListener('click', function (event) {
      var inside = event.target && event.target.closest('[data-share-dropdown]');
      if (!inside) {
        closeAll();
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        closeAll();
      }
    });
  }

  initShareDropdowns();
  initCopyShareActions();
  initSaveArticleActions();
  initSavedArticlesBadge();
  initSavedArticlesPage();

  var progress = document.querySelector('[data-reading-progress]');
  if (progress) {
    function updateProgress() {
      var doc = document.documentElement;
      var scrollTop = doc.scrollTop || document.body.scrollTop;
      var scrollHeight = doc.scrollHeight - doc.clientHeight;
      var value = scrollHeight > 0 ? (scrollTop / scrollHeight) * 100 : 0;
      progress.style.width = value.toFixed(2) + '%';
    }

    updateProgress();
    window.addEventListener('scroll', updateProgress, { passive: true });
    window.addEventListener('resize', updateProgress);
  }
})();
