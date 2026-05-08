/**
 * Echorouk Push — Admin JS
 * Handles: post search picker, live preview, send campaign, char counters.
 */

(function () {
    'use strict';

    const cfg = window.EchoroukPushAdmin || {};
    let selectedPost = null;

    // --- Send Campaign Page ---

    const sendPage = document.getElementById('ep-send-btn');
    if (sendPage) initCampaignPage();

    function initCampaignPage() {
        // Char counters
        document.querySelectorAll('[data-max]').forEach(el => {
            const inputId = el.previousElementSibling?.id;
            if (!inputId) return;
            const input = document.getElementById(inputId);
            if (!input) return;
            updateCounter(el, input.value.length);
            input.addEventListener('input', () => updateCounter(el, input.value.length));
        });

        // Live preview sync
        const fields = ['ep-title', 'ep-body', 'ep-icon', 'ep-image'];
        fields.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', updatePreview);
        });
        updatePreview();

        // Post search
        initPostSearch();

        // Send button
        document.getElementById('ep-send-btn').addEventListener('click', sendNotification);
    }

    function updateCounter(counterEl, len) {
        const max = parseInt(counterEl.dataset.max, 10);
        counterEl.textContent = `${len} / ${max}`;
        counterEl.classList.toggle('ep-char-count--warn', len > max * 0.9);
    }

    function updatePreview() {
        const title = document.getElementById('ep-title')?.value || 'عنوان الإشعار';
        const body  = document.getElementById('ep-body')?.value  || 'نص الإشعار...';
        const icon  = document.getElementById('ep-icon')?.value  || '';
        const image = document.getElementById('ep-image')?.value || '';

        const prevTitle = document.getElementById('ep-preview-title');
        const prevBody  = document.getElementById('ep-preview-body');
        const prevIcon  = document.getElementById('ep-preview-icon');
        const prevImg   = document.getElementById('ep-preview-image');

        if (prevTitle) prevTitle.textContent = title;
        if (prevBody)  prevBody.textContent  = body;
        if (prevIcon) {
            if (icon) {
                prevIcon.src = icon;
                prevIcon.style.display = 'block';
            } else {
                prevIcon.src = '';
                prevIcon.style.display = 'none';
            }
        }
        if (prevImg) {
            prevImg.src = image;
            prevImg.style.display = image ? 'block' : 'none';
        }
    }

    // --- Post Search ---

    function initPostSearch() {
        const input   = document.getElementById('ep-post-search');
        const results = document.getElementById('ep-post-results');
        const clear   = document.getElementById('ep-clear-post');

        if (!input || !results) return;

        let timer;
        input.addEventListener('input', () => {
            clearTimeout(timer);
            const q = input.value.trim();
            if (q.length < 2) { results.hidden = true; return; }
            timer = setTimeout(() => searchPosts(q, results), 300);
        });

        if (clear) {
            clear.addEventListener('click', () => {
                selectedPost = null;
                document.getElementById('ep-selected-post').hidden = true;
                input.value = '';
                results.hidden = true;
                document.getElementById('ep-url').value = '';
                document.getElementById('ep-title').value = '';
                document.getElementById('ep-body').value = '';
                document.getElementById('ep-image').value = '';
                updatePreview();
            });
        }

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.ep-post-search-wrap')) results.hidden = true;
        });
    }

    async function searchPosts(q, resultsEl) {
        const apiRoot = (cfg.wpApiRoot || '/wp-json/').replace(/\/$/, '');
        const url = `${apiRoot}/wp/v2/posts?search=${encodeURIComponent(q)}&per_page=6&_fields=id,title,rendered,link,featured_media`;
        try {
            const res   = await fetch(url, { headers: { 'X-WP-Nonce': cfg.nonce } });
            const posts = await res.json();
            renderPostResults(posts, resultsEl);
        } catch (e) {
            resultsEl.hidden = true;
        }
    }

    function renderPostResults(posts, el) {
        if (!Array.isArray(posts) || !posts.length) { el.hidden = true; return; }

        el.innerHTML = posts.map(p => {
            const title = p.title?.rendered || p.title || '';
            return `<div class="ep-result-item" data-id="${p.id}" data-title="${escAttr(title)}" data-url="${escAttr(p.link)}">
                <span class="ep-result-title">${title}</span>
            </div>`;
        }).join('');

        el.querySelectorAll('.ep-result-item').forEach(item => {
            item.addEventListener('click', () => selectPost(item, el));
        });

        el.hidden = false;
    }

    function selectPost(item, resultsEl) {
        const id    = item.dataset.id;
        const title = item.dataset.title;
        const url   = item.dataset.url;

        selectedPost = { id, title, url };
        resultsEl.hidden = true;
        document.getElementById('ep-post-search').value = '';

        document.getElementById('ep-title').value = title;
        document.getElementById('ep-url').value   = url;

        document.getElementById('ep-post-title-display').textContent = title;
        document.getElementById('ep-selected-post').hidden = false;

        // Fetch featured image
        const thumb = document.getElementById('ep-post-thumbnail');
        if (thumb) { thumb.src = ''; thumb.style.display = 'none'; }

        const apiRoot = (cfg.wpApiRoot || '/wp-json/').replace(/\/$/, '');
        fetch(`${apiRoot}/wp/v2/posts/${id}?_fields=featured_media`, {
            headers: { 'X-WP-Nonce': cfg.nonce }
        })
        .then(r => r.json())
        .then(p => {
            if (p.featured_media) {
                return fetch(`${apiRoot}/wp/v2/media/${p.featured_media}?_fields=source_url`, {
                    headers: { 'X-WP-Nonce': cfg.nonce }
                });
            }
        })
        .then(r => r?.json())
        .then(m => {
            if (m?.source_url) {
                document.getElementById('ep-image').value = m.source_url;
                const thumb = document.getElementById('ep-post-thumbnail');
                if (thumb) { thumb.src = m.source_url; thumb.style.display = 'block'; }
                updatePreview();
            }
        })
        .catch(() => {});

        updatePreview();
    }

    // --- Send Notification ---

    async function sendNotification() {
        const btn    = document.getElementById('ep-send-btn');
        const label  = btn.querySelector('.ep-btn-label');
        const spinner= btn.querySelector('.ep-btn-spinner');
        const result = document.getElementById('ep-send-result');

        const title  = document.getElementById('ep-title')?.value.trim();
        if (!title) { alert('الرجاء إدخال عنوان الإشعار.'); return; }

        btn.disabled    = true;
        label.hidden    = true;
        spinner.hidden  = false;
        result.hidden   = true;

        const payload = {
            title: title,
            body:  document.getElementById('ep-body')?.value.trim() || '',
            url:   document.getElementById('ep-url')?.value.trim()  || '/',
            icon:  document.getElementById('ep-icon')?.value.trim() || '',
            image: document.getElementById('ep-image')?.value.trim()|| '',
        };

        try {
            const res  = await fetch(`${cfg.restUrl}/send`, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce },
                body:    JSON.stringify(payload),
            });
            const data = await res.json();

            result.className = 'ep-send-result ep-send-result--success';
            result.textContent = `✅ تم الإرسال | نجح: ${data.sent} | فشل: ${data.failed} | الإجمالي: ${data.total}`;
            result.hidden = false;
        } catch (e) {
            result.className = 'ep-send-result ep-send-result--error';
            result.textContent = '❌ حدث خطأ أثناء الإرسال.';
            result.hidden = false;
        }

        btn.disabled   = false;
        label.hidden   = false;
        spinner.hidden = true;
    }

    function escAttr(str) {
        return String(str).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

})();
