(function () {
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
