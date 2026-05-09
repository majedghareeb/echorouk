(function($) {
  'use strict';

  var appState = {
    config: null,
    registry: {},
    postCache: {},
    latestPosts: [],
    searchTimer: null,
  };

  var selectors = {
    app: '#echorouk-homepage-editor-app'
  };

  function i18n(key, fallback) {
    if (window.EchoroukHomepageEditor && window.EchoroukHomepageEditor.i18n && window.EchoroukHomepageEditor.i18n[key]) {
      return window.EchoroukHomepageEditor.i18n[key];
    }
    return fallback || key;
  }

  function ajaxRequest(action, data, method) {
    var payload = $.extend({}, data || {}, {
      action: action,
      nonce: window.EchoroukHomepageEditor.nonce
    });

    return $.ajax({
      url: window.EchoroukHomepageEditor.ajaxUrl,
      method: method || 'POST',
      dataType: 'json',
      data: payload
    });
  }

  function ensureArray(value) {
    return Array.isArray(value) ? value : [];
  }

  function mapRegistry(registryList) {
    var map = {};
    (registryList || []).forEach(function(item) {
      if (item && item.id) {
        map[item.id] = item;
      }
    });
    return map;
  }

  function renderLoading() {
    $(selectors.app).html('<p class="echorouk-loading">' + i18n('loading', 'Loading...') + '</p>');
  }

  function renderError(message) {
    $(selectors.app).html('<div class="notice notice-error inline"><p>' + message + '</p></div>');
  }

  function renderApp() {
    var $app = $(selectors.app);

    if (!appState.config || !Array.isArray(appState.config.sections)) {
      renderError('Invalid configuration.');
      return;
    }

    $app.empty();

    var $toolbar = $('<div class="ehp-toolbar" />');
    var $save = $('<button type="button" class="button button-primary ehp-save" />').text(i18n('save', 'Save Changes'));
    var $reset = $('<button type="button" class="button ehp-reset" />').text(i18n('reset', 'Reset Defaults'));
    var $status = $('<span class="ehp-status" aria-live="polite" />');

    $toolbar.append($save, $reset, $status);
    $app.append($toolbar);

    var $sections = $('<div id="ehp-sections" class="ehp-sections" />');

    appState.config.sections.forEach(function(section) {
      $sections.append(renderSectionCard(section));
    });

    $app.append($sections);

    initSortable($sections);

    $save.on('click', function() {
      saveConfig($save, $status);
    });

    $reset.on('click', function() {
      if (!window.confirm(i18n('resetConfirm', 'Reset to defaults?'))) {
        return;
      }
      resetConfig($status);
    });
  }

  function initSortable($container) {
    $container.sortable({
      handle: '.ehp-section-handle',
      placeholder: 'ehp-sort-placeholder',
      update: function() {
        var ordered = [];
        $container.find('.ehp-section-card').each(function() {
          var id = $(this).data('sectionId');
          var section = findSectionById(id);
          if (section) {
            ordered.push(section);
          }
        });
        appState.config.sections = ordered;
      }
    });
  }

  function findSectionById(id) {
    var sections = ensureArray(appState.config.sections);
    for (var i = 0; i < sections.length; i++) {
      if (sections[i] && sections[i].id === id) {
        return sections[i];
      }
    }
    return null;
  }

  function renderSectionCard(section) {
    var registry = appState.registry[section.id] || {};

    var $card = $('<section class="ehp-section-card" />').attr('data-section-id', section.id).data('sectionId', section.id);

    var $header = $('<div class="ehp-section-header" />');
    var $titleWrap = $('<div class="ehp-section-title-wrap" />');
    var $handle = $('<span class="ehp-section-handle dashicons dashicons-move" title="Drag" />');
    var $title = $('<h2 class="ehp-section-title" />').text(section.label || registry.label || section.id);
    $titleWrap.append($handle, $title);

    var $controls = $('<div class="ehp-section-controls" />');
    var $enabledLabel = $('<label class="ehp-toggle" />');
    var $enabled = $('<input type="checkbox" class="ehp-enabled" />').prop('checked', !!section.enabled);
    $enabledLabel.append($enabled, $('<span />').text(i18n('enabled', 'Enabled')));
    $controls.append($enabledLabel);

    $header.append($titleWrap, $controls);

    var $body = $('<div class="ehp-section-body" />');

    $body.append(renderTextField(section, 'label', 'Section Label'));
    $body.append(renderSourceField(section));
    $body.append(renderLimitField(section));

    if (registry.supports_posts) {
      $body.append(renderPostPicker(section, section, 'post_ids', {
        title: 'Selected Posts',
        max: 30,
        multi: true
      }));
    }

    if (section.id === 'news_ticker') {
      $body.append(renderNewsTickerFields(section));
    }

    if (section.id === 'hero') {
      $body.append(renderHeroFields(section));
    }

    if (section.id === 'world') {
      $body.append(renderWorldFields(section));
    }

    if (section.id === 'video') {
      $body.append(renderVideoFields(section));
    }

    if (section.id === 'floating_video') {
      $body.append(renderFloatingVideoFields(section));
    }

    $enabled.on('change', function() {
      section.enabled = $(this).is(':checked');
      $card.toggleClass('is-disabled', !section.enabled);
    });

    if (!section.enabled) {
      $card.addClass('is-disabled');
    }

    $card.append($header, $body);
    return $card;
  }

  function renderFieldShell(label) {
    var $wrap = $('<div class="ehp-field" />');
    $wrap.append($('<label class="ehp-label" />').text(label));
    return $wrap;
  }

  function renderTextField(target, key, label) {
    var $wrap = renderFieldShell(label);
    var $input = $('<input type="text" class="regular-text" />').val(target[key] || '');
    $input.on('input', function() {
      target[key] = $(this).val();
    });
    $wrap.append($input);
    return $wrap;
  }

  function renderSourceField(section) {
    var $wrap = renderFieldShell(i18n('source', 'Content Source'));
    var $select = $('<select class="ehp-source"><option value="manual">' + i18n('manualSelection', 'Manual Selection') + '</option><option value="latest">' + i18n('latestPosts', 'Latest Posts') + '</option></select>');
    $select.val(section.source || 'manual');
    $select.on('change', function() {
      section.source = $(this).val();
    });
    $wrap.append($select);
    return $wrap;
  }

  function renderLimitField(section) {
    var $wrap = renderFieldShell(i18n('limit', 'Items Limit'));
    var $input = $('<input type="number" min="0" max="30" class="small-text" />').val(section.limit || 0);
    $input.on('input', function() {
      var value = parseInt($(this).val(), 10);
      if (Number.isNaN(value) || value < 0) {
        value = 0;
      }
      if (value > 30) {
        value = 30;
      }
      section.limit = value;
      $(this).val(value);
    });
    $wrap.append($input);
    return $wrap;
  }

  function ensureMeta(section) {
    if (!section.meta || typeof section.meta !== 'object') {
      section.meta = {};
    }
  }

  function renderNewsTickerFields(section) {
    ensureMeta(section);

    var $wrap = $('<div class="ehp-group" />');
    $wrap.append($('<h3 />').text('Ticker Options'));

    var $latest = $('<label class="ehp-toggle" />');
    var $latestInput = $('<input type="checkbox" />').prop('checked', !!section.meta.show_latest);
    $latest.append($latestInput, $('<span />').text('Show Latest News'));

    var $breaking = $('<label class="ehp-toggle" />');
    var $breakingInput = $('<input type="checkbox" />').prop('checked', !!section.meta.show_breaking);
    $breaking.append($breakingInput, $('<span />').text('Show Breaking News'));

    $latestInput.on('change', function() {
      section.meta.show_latest = $(this).is(':checked');
    });
    $breakingInput.on('change', function() {
      section.meta.show_breaking = $(this).is(':checked');
    });

    $wrap.append($latest, $breaking);
    return $wrap;
  }

  function renderHeroFields(section) {
    ensureMeta(section);

    var $wrap = $('<div class="ehp-group" />');
    $wrap.append($('<h3 />').text('Hero Layout'));

    $wrap.append(renderSinglePostPicker(section, section.meta, 'main_post_id', {
      title: i18n('mainArticle', 'Main Article')
    }));

    var $liveToggle = $('<label class="ehp-toggle" />');
    var $liveToggleInput = $('<input type="checkbox" />').prop('checked', !!section.meta.live_coverage_enabled);
    $liveToggle.append($liveToggleInput, $('<span />').text(i18n('liveCoverage', 'Live Coverage')));

    $liveToggleInput.on('change', function() {
      section.meta.live_coverage_enabled = $(this).is(':checked');
    });

    $wrap.append($liveToggle);

    $wrap.append(renderSinglePostPicker(section, section.meta, 'live_post_id', {
      title: 'Live Coverage Article',
      postTypes: ['live_coverage']
    }));

    $wrap.append(renderPostPicker(section, section.meta, 'side_post_ids', {
      title: i18n('sideArticles', 'Side Articles'),
      max: 5,
      multi: true
    }));

    $wrap.append(renderPostPicker(section, section.meta, 'fallback_post_ids', {
      title: i18n('fallbackArticles', 'Fallback Articles'),
      max: 5,
      multi: true
    }));

    return $wrap;
  }

  function renderWorldFields(section) {
    ensureMeta(section);

    var $wrap = $('<div class="ehp-group" />');
    $wrap.append($('<h3 />').text('World Layout'));

    $wrap.append(renderSinglePostPicker(section, section.meta, 'main_post_id', {
      title: i18n('mainArticle', 'Main Article')
    }));

    $wrap.append(renderPostPicker(section, section.meta, 'secondary_post_ids', {
      title: 'Secondary Articles',
      max: 6,
      multi: true
    }));

    return $wrap;
  }

  function renderVideoFields(section) {
    ensureMeta(section);

    var $wrap = $('<div class="ehp-group" />');
    $wrap.append($('<h3 />').text('Video Options'));
    $wrap.append(renderTextInputMeta(section.meta, 'video_url', i18n('videoUrl', 'Video URL')));
    return $wrap;
  }

  function renderFloatingVideoFields(section) {
    ensureMeta(section);

    var $wrap = $('<div class="ehp-group" />');
    $wrap.append($('<h3 />').text('Floating Video'));

    $wrap.append(renderTextInputMeta(section.meta, 'video_url', i18n('videoUrl', 'Video URL')));

    var $autoplay = $('<label class="ehp-toggle" />');
    var $autoplayInput = $('<input type="checkbox" />').prop('checked', !!section.meta.autoplay);
    $autoplay.append($autoplayInput, $('<span />').text(i18n('autoplay', 'Autoplay')));

    $autoplayInput.on('change', function() {
      section.meta.autoplay = $(this).is(':checked');
    });

    $wrap.append($autoplay);

    return $wrap;
  }

  function renderTextInputMeta(target, key, label) {
    var $wrap = renderFieldShell(label);
    var $input = $('<input type="url" class="regular-text code" />').val(target[key] || '');
    $input.on('input', function() {
      target[key] = $(this).val();
    });
    $wrap.append($input);
    return $wrap;
  }

  function renderSinglePostPicker(section, target, key, options) {
    var current = target[key] ? [target[key]] : [];
    var $picker = renderPostPicker(section, { __proxy: current }, '__proxy', {
      title: (options && options.title) ? options.title : 'Article',
      max: 1,
      multi: false,
      postTypes: (options && Array.isArray(options.postTypes)) ? options.postTypes : null
    });

    $picker.on('ehp:selectionChanged', function(event, selectedIds) {
      target[key] = selectedIds.length ? selectedIds[0] : 0;
    });

    return $picker;
  }

  function renderPostPicker(section, target, key, options) {
    var opts = $.extend({
      title: 'Posts',
      max: 30,
      multi: true,
      postTypes: null
    }, options || {});
    var pickerLatestPosts = [];

    if (!Array.isArray(target[key])) {
      target[key] = ensureArray(target[key]);
    }

    var selectedIds = ensureArray(target[key]).map(function(id) {
      return parseInt(id, 10);
    }).filter(function(id) {
      return id > 0;
    });

    target[key] = selectedIds;

    var $wrap = $('<div class="ehp-post-picker" />');
    var $title = $('<h4 class="ehp-post-picker-title" />').text(opts.title);
    var $presetRow = $('<div class="ehp-preset-row" />');
    var $presetSelect = $('<select class="ehp-preset-select"><option value="">Latest 10 Posts</option></select>');
    var $presetAdd = $('<button type="button" class="button ehp-preset-add" />').text(i18n('add', 'Add'));
    var $search = $('<input type="search" class="ehp-post-search" autocomplete="off" />').attr('placeholder', i18n('searchPlaceholder', 'Search posts...'));
    var $results = $('<div class="ehp-search-results" />');
    var $list = $('<ul class="ehp-selected-posts" />');

    $presetRow.append($presetSelect, $presetAdd);
    $wrap.append($title, $presetRow, $search, $results, $list);

    function drawSelected() {
      $list.empty();
      target[key].forEach(function(id) {
        var post = appState.postCache[String(id)] || { id: id, title: '#' + id, date: '' };
        var $item = $('<li class="ehp-selected-post" />').attr('data-post-id', id);
        var $label = $('<span class="ehp-selected-post-label" />').text(post.title + (post.date ? ' (' + post.date + ')' : ''));
        var $remove = $('<button type="button" class="button-link-delete" />').text(i18n('remove', 'Remove'));

        $remove.on('click', function() {
          target[key] = target[key].filter(function(currentId) {
            return currentId !== id;
          });
          drawSelected();
        });

        $item.append($label, $remove);
        $list.append($item);
      });

      $list.sortable({
        axis: 'y',
        update: function() {
          var reordered = [];
          $list.find('.ehp-selected-post').each(function() {
            reordered.push(parseInt($(this).attr('data-post-id'), 10));
          });
          target[key] = reordered.filter(function(id) { return id > 0; });
          $wrap.trigger('ehp:selectionChanged', [target[key]]);
        }
      });

      $wrap.trigger('ehp:selectionChanged', [target[key]]);
    }

    function isAllowedPostType(item) {
      if (!item || !opts.postTypes || !opts.postTypes.length) {
        return true;
      }
      return opts.postTypes.indexOf(item.post_type) !== -1;
    }

    function renderLatestPreset() {
      $presetSelect.empty();
      $presetSelect.append($('<option value="" />').text('Latest 10 Posts'));

      ensureArray(pickerLatestPosts.length ? pickerLatestPosts : appState.latestPosts).forEach(function(item) {
        if (!item || !item.id) {
          return;
        }
        if (!isAllowedPostType(item)) {
          return;
        }
        appState.postCache[String(item.id)] = item;
        $presetSelect.append(
          $('<option />')
            .attr('value', item.id)
            .text(item.title + (item.date ? ' (' + item.date + ')' : ''))
        );
      });
    }

    function addPost(item) {
      var id = parseInt(item.id, 10);
      if (!id || id < 1) {
        return;
      }

      appState.postCache[String(id)] = item;

      if (!opts.multi || opts.max === 1) {
        target[key] = [id];
      } else if (target[key].indexOf(id) === -1) {
        if (target[key].length >= opts.max) {
          return;
        }
        target[key].push(id);
      }

      drawSelected();
    }

    function drawSearchResults(items) {
      $results.empty();

      if (!items.length) {
        $results.append($('<p class="ehp-search-empty" />').text(i18n('searchNoResults', 'No posts found.')));
        return;
      }

      items.forEach(function(item) {
        appState.postCache[String(item.id)] = item;

        var $row = $('<button type="button" class="ehp-search-result" />');
        $row.append($('<span class="ehp-search-result-title" />').text(item.title));
        $row.append($('<span class="ehp-search-result-date" />').text(item.date || ''));

        $row.on('click', function() {
          addPost(item);
          $results.empty();
          $search.val('');
        });

        $results.append($row);
      });
    }

    $search.on('input', function() {
      var term = $(this).val().trim();

      window.clearTimeout(appState.searchTimer);

      if (term.length < 2) {
        $results.empty();
        return;
      }

      appState.searchTimer = window.setTimeout(function() {
        ajaxRequest('echorouk_homepage_search_posts', {
          q: term,
          per_page: 12,
          recent_hours: 48,
          post_types: opts.postTypes && opts.postTypes.length ? opts.postTypes.join(',') : ''
        }, 'GET').done(function(response) {
          if (!response || !response.success || !response.data || !Array.isArray(response.data.items)) {
            drawSearchResults([]);
            return;
          }
          drawSearchResults(response.data.items);
        }).fail(function() {
          drawSearchResults([]);
        });
      }, 250);
    });

    $presetAdd.on('click', function() {
      var id = parseInt($presetSelect.val(), 10);
      if (!id || id < 1) {
        return;
      }
      var item = appState.postCache[String(id)] || null;
      if (!item) {
        return;
      }
      addPost(item);
      $presetSelect.val('');
    });

    if (opts.postTypes && opts.postTypes.length) {
      ajaxRequest('echorouk_homepage_latest_posts', {
        per_page: 20,
        post_types: opts.postTypes.join(',')
      }, 'GET').done(function(response) {
        if (response && response.success && response.data && Array.isArray(response.data.items)) {
          pickerLatestPosts = response.data.items;
          pickerLatestPosts.forEach(function(item) {
            if (item && item.id) {
              appState.postCache[String(item.id)] = item;
            }
          });
        }
      }).always(function() {
        renderLatestPreset();
      });
    } else {
      renderLatestPreset();
    }
    drawSelected();
    return $wrap;
  }

  function saveConfig($button, $status) {
    $button.prop('disabled', true).text(i18n('saving', 'Saving...'));
    $status.removeClass('is-error is-success').text('');

    ajaxRequest('echorouk_homepage_save_config', {
      config: JSON.stringify(appState.config)
    }, 'POST').done(function(response) {
      if (!response || !response.success || !response.data) {
        $status.addClass('is-error').text(i18n('saveError', 'Unable to save configuration.'));
        return;
      }

      if (response.data.config) {
        appState.config = response.data.config;
      }
      if (response.data.posts) {
        appState.postCache = $.extend({}, appState.postCache, response.data.posts);
      }

      $status.addClass('is-success').text(response.data.message || i18n('saveSuccess', 'Saved successfully.'));
      renderApp();
    }).fail(function(xhr) {
      var message = i18n('saveError', 'Unable to save configuration.');
      if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
        message = xhr.responseJSON.data.message;
      }
      $status.addClass('is-error').text(message);
    }).always(function() {
      $button.prop('disabled', false).text(i18n('save', 'Save Changes'));
    });
  }

  function resetConfig($status) {
    $status.removeClass('is-error is-success').text('');

    ajaxRequest('echorouk_homepage_reset_config', {}, 'POST').done(function(response) {
      if (!response || !response.success || !response.data || !response.data.config) {
        $status.addClass('is-error').text(i18n('saveError', 'Unable to save configuration.'));
        return;
      }

      appState.config = response.data.config;
      appState.postCache = {};
      $status.addClass('is-success').text(response.data.message || 'Reset complete.');
      renderApp();
    }).fail(function() {
      $status.addClass('is-error').text(i18n('saveError', 'Unable to save configuration.'));
    });
  }

  function loadConfig() {
    renderLoading();

    ajaxRequest('echorouk_homepage_get_config', {}, 'GET').done(function(response) {
      if (!response || !response.success || !response.data) {
        renderError('Failed to load configuration.');
        return;
      }

      appState.config = response.data.config || null;
      appState.registry = mapRegistry(response.data.registry || []);
      appState.postCache = response.data.posts || {};

      loadLatestPosts(renderApp);
    }).fail(function(xhr) {
      var message = 'Unable to load configuration.';
      if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
        message = xhr.responseJSON.data.message;
      }
      renderError(message);
    });
  }

  function loadLatestPosts(callback) {
    ajaxRequest('echorouk_homepage_latest_posts', {
      per_page: 10
    }, 'GET').done(function(response) {
      if (response && response.success && response.data && Array.isArray(response.data.items)) {
        appState.latestPosts = response.data.items;
        response.data.items.forEach(function(item) {
          if (item && item.id) {
            appState.postCache[String(item.id)] = item;
          }
        });
      } else {
        appState.latestPosts = [];
      }
    }).fail(function() {
      appState.latestPosts = [];
    }).always(function() {
      if (typeof callback === 'function') {
        callback();
      }
    });
  }

  $(function() {
    loadConfig();
  });

})(jQuery);
