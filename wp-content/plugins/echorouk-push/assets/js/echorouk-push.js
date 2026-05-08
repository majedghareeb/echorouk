/**
 * Echorouk Push — Frontend Subscription Script
 * Drives the `.echorouk-alert-link` header button in the theme.
 * Falls back to a banner when the button is not present.
 */

(function () {
    'use strict';

    const cfg = window.EchoroukPush || {};
    if (!cfg.vapidPublicKey || !cfg.swUrl) return;

    // Browser support check
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;

    const i18n     = cfg.i18n || {};
    const ICON_OFF = cfg.iconOff || '';  // set via wp_localize_script
    const ICON_ON  = cfg.iconOn  || '';

    let swReg      = null;

    // -------------------------------------------------------------------
    // Utilities
    // -------------------------------------------------------------------

    function urlBase64ToUint8Array(b64) {
        const pad = '='.repeat((4 - (b64.length % 4)) % 4);
        const raw = atob((b64 + pad).replace(/-/g, '+').replace(/_/g, '/'));
        return Uint8Array.from([...raw].map(c => c.charCodeAt(0)));
    }

    function detectBrowser() {
        const ua = navigator.userAgent;
        if (/Firefox/i.test(ua)) return 'Firefox';
        if (/Edg/i.test(ua))     return 'Edge';
        if (/Chrome/i.test(ua))  return 'Chrome';
        if (/Safari/i.test(ua))  return 'Safari';
        return 'Unknown';
    }

    // -------------------------------------------------------------------
    // Header button state
    // -------------------------------------------------------------------

    function getHeaderBtn() {
        return document.querySelector('.echorouk-alert-link');
    }

    /**
     * Set the header button to "subscribed" or "unsubscribed" appearance.
     * @param {'subscribed'|'unsubscribed'|'loading'|'denied'} state
     */
    function setButtonState(state) {
        const btn = getHeaderBtn();
        if (!btn) return;

        btn.classList.remove('ep-state-subscribed', 'ep-state-unsubscribed', 'ep-state-loading', 'ep-state-denied');
        btn.classList.add('ep-state-' + state);

        const label = btn.querySelector('.ep-btn-text');
        const icon  = btn.querySelector('object');

        switch (state) {
            case 'subscribed':
                if (label) label.textContent = i18n.subscribed   || 'مشترك في الإشعارات';
                btn.title = i18n.unsubscribe || 'إلغاء الاشتراك';
                if (icon && ICON_ON)  icon.data = ICON_ON;
                break;
            case 'unsubscribed':
                if (label) label.textContent = i18n.bannerTitle  || 'اشترك في الإشعارات';
                btn.title = i18n.allowButton || 'اشتراك';
                if (icon && ICON_OFF) icon.data = ICON_OFF;
                break;
            case 'loading':
                if (label) label.textContent = '...';
                break;
            case 'denied':
                if (label) label.textContent = i18n.denied || 'الإشعارات محظورة';
                btn.title = '';
                if (icon && ICON_OFF) icon.data = ICON_OFF;
                break;
        }
    }

    // -------------------------------------------------------------------
    // Patch the header button markup so we can control the text node
    // The original anchor contains a raw text node — we wrap it in a span.
    // -------------------------------------------------------------------

    function patchHeaderBtn() {
        const btn = getHeaderBtn();
        if (!btn) return;

        // Wrap any bare text nodes in a <span class="ep-btn-text">
        btn.childNodes.forEach(node => {
            if (node.nodeType === Node.TEXT_NODE && node.textContent.trim()) {
                const span = document.createElement('span');
                span.className = 'ep-btn-text';
                span.textContent = node.textContent.trim();
                btn.replaceChild(span, node);
            }
        });

        btn.setAttribute('href', '#');
        btn.addEventListener('click', onButtonClick);
        // Prevent page jump
        btn.addEventListener('click', e => e.preventDefault());
    }

    // -------------------------------------------------------------------
    // Subscribe / Unsubscribe
    // -------------------------------------------------------------------

    async function subscribe() {
        setButtonState('loading');

        let permission = Notification.permission;
        if (permission === 'default') {
            permission = await Notification.requestPermission();
        }

        if (permission === 'denied') {
            setButtonState('denied');
            return;
        }

        if (permission !== 'granted') {
            setButtonState('unsubscribed');
            return;
        }

        try {
            const sub = await swReg.pushManager.subscribe({
                userVisibleOnly:      true,
                applicationServerKey: urlBase64ToUint8Array(cfg.vapidPublicKey),
            });

            const json = sub.toJSON();

            const res = await fetch(cfg.restUrl + '/subscribe', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce },
                body:    JSON.stringify({
                    endpoint: json.endpoint,
                    p256dh:   json.keys.p256dh,
                    auth:     json.keys.auth,
                    browser:  detectBrowser(),
                }),
            });

            if (res.ok) {
                localStorage.setItem('ep_subscribed', '1');
                setButtonState('subscribed');
                hideBanner();
            } else {
                setButtonState('unsubscribed');
            }
        } catch (e) {
            console.warn('[echorouk-push] Subscribe failed:', e);
            setButtonState('unsubscribed');
        }
    }

    async function unsubscribe() {
        setButtonState('loading');
        try {
            const sub = await swReg.pushManager.getSubscription();
            if (sub) {
                await fetch(cfg.restUrl + '/unsubscribe', {
                    method:  'DELETE',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce },
                    body:    JSON.stringify({ endpoint: sub.endpoint }),
                });
                await sub.unsubscribe();
            }
            localStorage.removeItem('ep_subscribed');
            setButtonState('unsubscribed');
        } catch (e) {
            console.warn('[echorouk-push] Unsubscribe failed:', e);
            setButtonState('subscribed'); // revert
        }
    }

    async function onButtonClick() {
        if (!swReg) return;
        const sub = await swReg.pushManager.getSubscription();
        if (sub) {
            unsubscribe();
        } else {
            subscribe();
        }
    }

    // -------------------------------------------------------------------
    // Fallback banner (shown when header button is absent)
    // -------------------------------------------------------------------

    function showBanner() {
        if (getHeaderBtn()) return; // header button takes priority
        if (document.getElementById('ep-push-banner')) return;
        if (localStorage.getItem('ep_dismissed') &&
            Date.now() - parseInt(localStorage.getItem('ep_dismissed'), 10) < 7 * 864e5) return;

        const banner = document.createElement('div');
        banner.id  = 'ep-push-banner';
        banner.dir = 'rtl';
        banner.innerHTML = `
            <div class="ep-banner__inner">
                <div class="ep-banner__icon">🔔</div>
                <div class="ep-banner__text">
                    <strong class="ep-banner__title">${escHtml(i18n.bannerTitle || 'اشترك في الإشعارات')}</strong>
                    <span class="ep-banner__body">${escHtml(i18n.bannerBody || 'احصل على آخر الأخبار فور نشرها')}</span>
                </div>
                <div class="ep-banner__actions">
                    <button id="ep-allow-btn" class="ep-btn ep-btn--allow">${escHtml(i18n.allowButton || 'اشتراك')}</button>
                    <button id="ep-deny-btn"  class="ep-btn ep-btn--deny">${escHtml(i18n.denyButton  || 'لاحقاً')}</button>
                </div>
                <button id="ep-close-btn" class="ep-btn ep-btn--close" aria-label="إغلاق">✕</button>
            </div>`;

        document.body.appendChild(banner);
        requestAnimationFrame(() => banner.classList.add('ep-banner--visible'));

        document.getElementById('ep-allow-btn').addEventListener('click', () => { subscribe(); hideBanner(); });
        document.getElementById('ep-deny-btn').addEventListener('click',  () => hideBanner(true));
        document.getElementById('ep-close-btn').addEventListener('click', () => hideBanner(true));
    }

    function hideBanner(dismiss = false) {
        const b = document.getElementById('ep-push-banner');
        if (b) {
            b.classList.remove('ep-banner--visible');
            b.classList.add('ep-banner--hiding');
            setTimeout(() => b.remove(), 400);
        }
        if (dismiss) localStorage.setItem('ep_dismissed', Date.now().toString());
    }

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // -------------------------------------------------------------------
    // Init
    // -------------------------------------------------------------------

    async function init() {
        try {
            swReg = await navigator.serviceWorker.register(cfg.swUrl, { scope: '/' });
        } catch (e) {
            console.warn('[echorouk-push] SW registration failed:', e);
            return;
        }

        patchHeaderBtn();

        const existing = await swReg.pushManager.getSubscription();

        if (existing) {
            localStorage.setItem('ep_subscribed', '1');
            setButtonState('subscribed');
            return;
        }

        if (Notification.permission === 'denied') {
            setButtonState('denied');
            return;
        }

        setButtonState('unsubscribed');

        // Show fallback banner after delay only if no header button
        if (!getHeaderBtn()) {
            setTimeout(showBanner, 3000);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
