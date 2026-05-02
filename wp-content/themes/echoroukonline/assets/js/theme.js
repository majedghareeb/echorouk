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

  var progress = document.querySelector('[data-reading-progress]');
  if (!progress) {
    return;
  }

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
})();
