<?php

/**
 * Homepage mockup body content.
 *
 * @package EchouroukOnline
 */

$hero_section = function_exists('echorouk_homepage_get_section') ? echorouk_homepage_get_section('hero') : null;
$hero_meta    = is_array($hero_section) && isset($hero_section['meta']) && is_array($hero_section['meta']) ? $hero_section['meta'] : array();

$hero_main = null;
if (! empty($hero_meta['main_post_id'])) {
    $hero_main = get_post(absint($hero_meta['main_post_id']));
}
if (! $hero_main || 'publish' !== $hero_main->post_status) {
    $hero_main = null;
}

$hero_feed = function_exists('echorouk_homepage_get_posts_for_section') ? echorouk_homepage_get_posts_for_section('hero', 4) : array();
if (! $hero_main && ! empty($hero_feed)) {
    $hero_main = $hero_feed[0];
}

$live_enabled = ! empty($hero_meta['live_coverage_enabled']);
$live_id      = ! empty($hero_meta['live_post_id']) ? absint($hero_meta['live_post_id']) : 0;
$live_post    = ($live_enabled && $live_id) ? get_post($live_id) : null;
$side_ids     = ! empty($hero_meta['side_post_ids']) && is_array($hero_meta['side_post_ids']) ? array_map('absint', $hero_meta['side_post_ids']) : array();
$fallback_ids = ! empty($hero_meta['fallback_post_ids']) && is_array($hero_meta['fallback_post_ids']) ? array_map('absint', $hero_meta['fallback_post_ids']) : array();
$right_ids    = ($live_post && 'publish' === $live_post->post_status) ? $side_ids : $fallback_ids;
$hero_right   = ! empty($right_ids) ? get_posts(
    array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'post__in'       => $right_ids,
        'orderby'        => 'post__in',
        'posts_per_page' => 3,
    )
) : array();

if (empty($hero_right) && ! empty($hero_feed)) {
    $hero_right = array_slice($hero_feed, $hero_main ? 1 : 0, 3);
}

$ticker_posts = function_exists('echorouk_homepage_get_posts_for_section') ? echorouk_homepage_get_posts_for_section('news_ticker', 6) : array();

$hero_tag = '';
if ($hero_main) {
    $hero_categories = get_the_category($hero_main->ID);
    $hero_tag        = ! empty($hero_categories) ? $hero_categories[0]->name : '';
}

?>
<main id="primary" class="site-main echorouk-homepage-mockup">
    <div class="container-xl echorouk-homepage-wrap py-4">
        <section class="hero grid-border">
            <div class="row g-4 align-items-stretch hero-layout">
                <aside class="col-lg-3 order-2 order-lg-1 hero-col hero-col-left">
                    <div class="hero-live hero-col-card">
                        <div class="hero-live-title"><span>تغطية
                                حية</span><img
                                src="<?php echo ECHOROUK_THEME_URI; ?>/assets/icons/arrow-left-01-stroke-rounded.svg"></img>
                        </div>
                        <ul class="hero-live-timeline">
                            <?php if (! empty($ticker_posts)) : ?>
                                <?php foreach ($ticker_posts as $ticker_post) : ?>
                                    <li><time class="hero-live-time"
                                            datetime="<?php echo esc_attr(get_post_time(DATE_W3C, false, $ticker_post)); ?>"><?php echo esc_html(get_the_time('H:i', $ticker_post)); ?></time><span><a
                                                href="<?php echo esc_url(get_permalink($ticker_post)); ?>"><?php echo esc_html(get_the_title($ticker_post)); ?></a></span>
                                    </li>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <li><time class="hero-live-time" datetime="2026-05-01T14:30:00+03:00">الآن</time><span>سعيود
                                        يعرض مشروع قانون الانتخابات أمام مجلس الأمة</span></li>
                                <li><time class="hero-live-time"
                                        datetime="2026-05-01T13:22:00+03:00">13:22</time><span>فالڤيردي: توقعت طردي من ريال
                                        مدريد بسبب لوكا زيدان (فيديو)</span></li>
                                <li><time class="hero-live-time"
                                        datetime="2026-05-01T11:15:00+03:00">11:15</time><span>زوجته تعامله بشكل سيئ.. ترامب
                                        يسخر من ماكرون مجددا!</span></li>
                                <li><time class="hero-live-time"
                                        datetime="2026-05-01T10:45:00+03:00">10:45</time><span>ميناء وهران.. رسو باخرة ثالثة
                                        محملة بـ 7 آلاف رأس غنم مستورد</span></li>
                                <li><time class="hero-live-time" datetime="2026-05-01T09:22:00+03:00">09:22</time><span>فيفا
                                        يرفع أسعار تذاكر نهائي كأس العالم 2026</span></li>
                                <li><time class="hero-live-time"
                                        datetime="2026-05-01T09:12:00+03:00">09:12</time><span>مرشحة محتملة للرئاسة
                                        الأمريكية تتعهد بمراجعة السياسة الخارجية</span></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </aside>

                <section class="col-lg-6 order-1 order-lg-2 hero-col hero-col-center">
                    <article class="hero-lead hero-col-card">
                        <div class="hero-lead-media position-relative">
                            <?php if ($hero_main) : ?>
                                <span class="tag"><?php echo esc_html($hero_tag ? $hero_tag : 'العالم'); ?></span>
                                <a
                                    href="<?php echo esc_url(get_permalink($hero_main)); ?>"><?php echo echorouk_post_image_html($hero_main->ID, 'echorouk-hero'); ?></a>
                                <a href="<?php echo esc_url(get_permalink($hero_main)); ?>" class="hero-play-center"
                                    aria-label="قراءة الخبر"><i class="bi bi-play-fill"></i></a>
                            <?php else : ?>
                                <span class="tag">العالم</span>
                                <img src="https://images.unsplash.com/photo-1529107386315-e1a2ed48a620?auto=format&fit=crop&w=900&q=80"
                                    alt="main news" loading="lazy" decoding="async">
                                <a href="#" class="hero-play-center" aria-label="تشغيل الفيديو"><i
                                        class="bi bi-play-fill"></i></a>
                            <?php endif; ?>
                            <aside class="hero-floating-video" aria-label="نافذة فيديو عائمة">
                                <div class="hero-floating-head">
                                    <button class="hero-floating-close" type="button"
                                        aria-label="إغلاق الفيديو العائم">×</button>
                                    <span class="hero-floating-live">البث الحي</span>
                                </div>
                                <div class="hero-floating-frame">
                                    <img src="https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=260&q=80"
                                        alt="floating video" loading="lazy" decoding="async">
                                    <span class="hero-floating-label">Episode 172</span>
                                </div>
                            </aside>
                        </div>

                        <div class="hero-lead-box-wrap">
                            <div class="hero-lead-text-box">
                                <?php if ($hero_main) : ?>
                                    <h1 class="headline"><a
                                            href="<?php echo esc_url(get_permalink($hero_main)); ?>"><?php echo esc_html(get_the_title($hero_main)); ?></a>
                                    </h1>
                                    <div class="hero-meta-line"><?php echo esc_html(get_the_date('', $hero_main)); ?>
                                    </div>
                                    <p class="summary mb-0">
                                        <?php echo esc_html(wp_trim_words(wp_strip_all_tags(get_the_excerpt($hero_main) ? get_the_excerpt($hero_main) : get_post_field('post_content', $hero_main)), 28)); ?>
                                    </p>
                                <?php else : ?>
                                    <h1 class="headline">ترامب يهاجم بريطانيا وفرنسا.. هذا ما قاله</h1>
                                    <div class="hero-meta-line">السبت 22 مارس 2026</div>
                                    <p class="summary mb-0">قال ترامب في منشور عبر منصة "تروث سوشيال" إن بريطانيا من بين
                                        الدول التي لم تعد قادرة على الحصول على وقود الطائرات بسبب إغلاق مضيق هرمز.</p>
                                <?php endif; ?>
                            </div>
                            <div class="hero-lead-icons-box" aria-label="إجراءات الخبر">
                                <a href="#" class="hero-lead-icon"><img
                                        src="<?php echo ECHOROUK_THEME_URI; ?>/assets/icons/share-08-stroke-rounded.svg"></img></a>
                                <a href="#" class="hero-lead-icon"><img
                                        src="<?php echo ECHOROUK_THEME_URI; ?>/assets/icons/menu-01-stroke-rounded.svg"></img></a>
                                <a href="#" class="hero-lead-icon is-active"><img
                                        src="<?php echo ECHOROUK_THEME_URI; ?>/assets/icons/headphones-stroke-rounded.svg"></img></a>
                                <a href="#" class="hero-lead-icon"><img
                                        src="<?php echo ECHOROUK_THEME_URI; ?>/assets/icons/bookmark-02-stroke-rounded.svg"></img></a>
                            </div>
                        </div>
                    </article>
                </section>

                <aside class="col-lg-3 order-3 order-lg-3 hero-col hero-col-right">
                    <div class="hero-latest-panel hero-col-card">
                        <?php if (! empty($hero_right)) : ?>
                            <?php $feature = $hero_right[0]; ?>
                            <article class="hero-latest-feature">
                                <a
                                    href="<?php echo esc_url(get_permalink($feature)); ?>"><?php echo echorouk_post_image_html($feature->ID, 'large'); ?></a>
                                <div class="hero-latest-date"><?php echo esc_html(get_the_date('Y/m/d', $feature)); ?>
                                </div>
                                <h3><a
                                        href="<?php echo esc_url(get_permalink($feature)); ?>"><?php echo esc_html(get_the_title($feature)); ?></a>
                                </h3>
                            </article>

                            <?php foreach (array_slice($hero_right, 1, 2) as $hero_side_post) : ?>
                                <article class="hero-latest-item">
                                    <a
                                        href="<?php echo esc_url(get_permalink($hero_side_post)); ?>"><?php echo echorouk_post_image_html($hero_side_post->ID, 'thumbnail'); ?></a>
                                    <div>
                                        <h4><a
                                                href="<?php echo esc_url(get_permalink($hero_side_post)); ?>"><?php echo esc_html(get_the_title($hero_side_post)); ?></a>
                                        </h4>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <article class="hero-latest-feature">
                                <img src="https://images.unsplash.com/photo-1624727828489-a1e03b79bba8?auto=format&fit=crop&w=600&q=80"
                                    alt="latest big" loading="lazy" decoding="async">
                                <div class="hero-latest-date">2026/04/01</div>
                                <h3>سعيود يستقبل رئيس المجلس الوطني للأقاليم والجهات التونسي</h3>
                            </article>

                            <article class="hero-latest-item">
                                <img src="https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=220&q=80"
                                    alt="latest small 1" loading="lazy" decoding="async">
                                <div>
                                    <h4>الاتحاد الإسباني يدين هتافات عنصرية ضد المسلمين في ديربي مصر</h4>
                                </div>
                            </article>

                            <article class="hero-latest-item">
                                <img src="https://images.unsplash.com/photo-1461896836934-ffe607ba8211?auto=format&fit=crop&w=220&q=80"
                                    alt="latest small 2" loading="lazy" decoding="async">
                                <div>
                                    <h4>مواجهة قوية مرتقبة بين كندا وصربيا والصينية تشينغ تشيوان</h4>
                                </div>
                            </article>
                        <?php endif; ?>
                    </div>
                </aside>
            </div>
        </section>

        <div class="ad-box my-4">مساحة إعلانية</div>

        <section class="world-spotlight grid-border">
            <div class="row g-4 align-items-stretch world-spotlight-grid">
                <aside class="col-lg-3 world-side">
                    <article class="world-mini-card">
                        <div class="world-mini-media">
                            <img src="https://images.unsplash.com/photo-1521334884684-d80222895322?auto=format&fit=crop&w=500&q=80"
                                alt="macron" loading="lazy" decoding="async">
                            <span class="world-mini-tag">إيران</span>
                        </div>
                        <div class="world-mini-date">2026/04/05</div>
                        <h3>هكذا رد ماكرون على سخرية ترامب منه ومن زوجته</h3>
                    </article>
                    <article class="world-mini-card">
                        <div class="world-mini-media">
                            <img src="https://images.unsplash.com/photo-1446776811953-b23d57bd21aa?auto=format&fit=crop&w=500&q=80"
                                alt="flights" loading="lazy" decoding="async">
                            <span class="world-mini-tag">الجزائر</span>
                        </div>
                        <div class="world-mini-date">2026/04/05</div>
                        <h3>بعد توقف دام سنوات.. عودة الرحلات الجوية نحو هذه الولاية</h3>
                    </article>
                </aside>

                <section class="col-lg-6">
                    <article class="world-feature-card">
                        <div class="world-feature-media">
                            <img src="https://images.unsplash.com/photo-1517849845537-4d257902454a?auto=format&fit=crop&w=1000&q=80"
                                alt="featured world" loading="lazy" decoding="async">
                            <span class="world-feature-tag">العالم</span>
                        </div>
                        <div class="world-feature-body">
                            <div class="world-feature-date">2026/04/05</div>
                            <h3>بعد شهرين من استدعائه.. عودة السفير الإيطالي إلى سويسرا</h3>
                            <p>ذكرت وزارة الخارجية السويسرية أن سفير إيطاليا سيعود إلى مهامه، وذلك بعد أكثر من شهرين من
                                استدعائه احتجاجا على إجراءات السلطات السويسرية بشأن حريق اندلع في حانة وادى إلى مصرع
                                إيطاليين.</p>
                        </div>
                    </article>
                </section>

                <aside class="col-lg-3 world-side">
                    <article class="world-mini-card">
                        <div class="world-mini-media">
                            <img src="https://images.unsplash.com/photo-1579202673506-ca3ce28943ef?auto=format&fit=crop&w=500&q=80"
                                alt="usa flag" loading="lazy" decoding="async">
                            <span class="world-mini-tag">إيران</span>
                        </div>
                        <div class="world-mini-date">2026/04/05</div>
                        <h3>السلطات الأمريكية تعلن توقيف قريبتين لقاسم سليماني.. وطهران تنفي</h3>
                    </article>
                    <article class="world-mini-card">
                        <div class="world-mini-media">
                            <img src="https://images.unsplash.com/photo-1450101499163-c8848c66ca85?auto=format&fit=crop&w=500&q=80"
                                alt="police" loading="lazy" decoding="async">
                            <span class="world-mini-tag">الجزائر</span>
                        </div>
                        <div class="world-mini-date">2026/04/05</div>
                        <h3>ترقيات استثنائية لأعوان الشرطة الحاصلين على شهادات جامعية</h3>
                    </article>
                </aside>
            </div>
        </section>

        <section class="video-showcase grid-border">
            <div class="video-showcase-grid">
                <aside class="video-showcase-side">
                    <div class="video-side-ad">إعلان<br>300/250</div>

                    <div class="video-side-most">
                        <div class="video-side-title-wrap">
                            <h3 class="video-side-title">الأكثر مشاهدة</h3>
                            <span class="video-side-arrow">&laquo;</span>
                        </div>

                        <article class="video-side-feature">
                            <img src="https://images.unsplash.com/photo-1624727828489-a1e03b79bba8?auto=format&fit=crop&w=500&q=80"
                                alt="most watched" loading="lazy" decoding="async">
                            <div class="video-side-date">2026/04/01</div>
                            <h4>سعيود يستقبل رئيس المجلس الوطني للأقاليم والجهات التونسي</h4>
                        </article>

                        <div class="video-side-list">
                            <article class="video-side-item">
                                <div>
                                    <h5>الاتحاد الإسباني يدين هتافات عنصرية ضد المسلمين في ودية مصر</h5>
                                </div>
                                <img src="https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=180&q=80"
                                    alt="thumb 1" loading="lazy" decoding="async">
                            </article>
                            <article class="video-side-item">
                                <div>
                                    <h5>مواجهة قوية مرتقبة بين كيليا نيمور والصينية تشيو شييوان</h5>
                                </div>
                                <img src="https://images.unsplash.com/photo-1461896836934-ffe607ba8211?auto=format&fit=crop&w=180&q=80"
                                    alt="thumb 2" loading="lazy" decoding="async">
                            </article>
                            <article class="video-side-item">
                                <div>
                                    <h5>محركتنا مفتوحة ضد الهيمنة الأمريكية</h5>
                                </div>
                                <img src="https://images.unsplash.com/photo-1529107386315-e1a2ed48a620?auto=format&fit=crop&w=180&q=80"
                                    alt="thumb 3" loading="lazy" decoding="async">
                            </article>
                        </div>
                    </div>
                </aside>

                <section class="video-showcase-main">
                    <header class="video-main-header">
                        <div class="video-main-logo-wrap">
                            <div class="video-main-kicker">فيديوهات</div>
                            <div class="video-main-logo" id="echorouk-logo-white"></div>
                        </div>
                        <a href="#" class="video-main-all">كل الفيديوهات <span aria-hidden="true"><img
                                    src="<?php echo ECHOROUK_THEME_URI; ?>/assets/icons/play-stroke-rounded.svg"></img></span></a>
                    </header>

                    <article class="video-main-feature">
                        <img src="https://images.unsplash.com/photo-1518091043644-c1d4457512c6?auto=format&fit=crop&w=1200&q=80"
                            alt="featured video" loading="lazy" decoding="async">
                        <div class="video-main-overlay">
                            <div class="video-main-feature-date">15/03/2025</div>
                            <h3>البرازيل تكبل أنشيلوتي</h3>
                        </div>
                    </article>

                    <div class="video-main-bottom">
                        <article class="video-bottom-card">
                            <img src="https://images.unsplash.com/photo-1507537297725-24a1c029d3ca?auto=format&fit=crop&w=700&q=80"
                                alt="video bottom 1" loading="lazy" decoding="async">
                            <div class="video-bottom-date">25/03/2026</div>
                            <h4>الجزائر.. موقفها مع موريتانيا الشقيقة</h4>
                        </article>
                        <article class="video-bottom-card">
                            <img src="https://images.unsplash.com/photo-1529107386315-e1a2ed48a620?auto=format&fit=crop&w=700&q=80"
                                alt="video bottom 2" loading="lazy" decoding="async">
                            <div class="video-bottom-date">25/03/2026</div>
                            <h4>روسيا والصين تفتحان مشروع "أوراسيا" حول الوضع في الشرق الأوسط</h4>
                        </article>
                    </div>
                </section>
            </div>
        </section>

        <section class="daily-boxes grid-border">
            <div class="daily-boxes-grid">
                <article class="daily-box daily-print-box">
                    <header class="daily-box-header">
                        <h3>الشروق اليومي - النسخة المطبوعة</h3>
                        <div class="daily-box-link">
                            <a href="#"> الأرشيف<img
                                    src="<?php echo ECHOROUK_THEME_URI; ?>/assets/icons/archive-02-stroke-rounded.svg"></img></a>
                        </div>

                    </header>
                    <div class="daily-box-divider"></div>
                    <div class="daily-print-content">
                        <div class="daily-print-meta">
                            <div class="daily-print-date">الخميس 16 أفريل 2026</div>
                            <div class="daily-print-downloads">تحميل 1569</div>
                        </div>
                        <div class="daily-print-cover-wrap">
                            <img src="https://images.unsplash.com/photo-1495020689067-958852a7765e?auto=format&fit=crop&w=400&q=80"
                                alt="newspaper cover" loading="lazy" decoding="async">
                        </div>
                    </div>
                    <a href="#" class="daily-print-download" aria-label="تحميل النسخة"><i
                            class="bi bi-download"></i></a>
                </article>
                <article class="daily-box daily-poll-box">
                    <header class="daily-box-header">
                        <h3>تصويت اليوم</h3>
                        <div class="daily-box-link">
                            <a href="#"> كل التصويتات<img
                                    src="<?php echo ECHOROUK_THEME_URI; ?>/assets/icons/check-list-stroke-rounded.svg"></img></a>
                        </div>
                    </header>
                    <div class="daily-box-divider"></div>
                    <form class="daily-poll-form" action="#" method="post">
                        <p class="daily-poll-question">من هو أفضل لاعب كرة قدم لعب للمنتخب الوطني</p>
                        <label class="daily-poll-option">
                            <span>رياض محرز</span>
                            <input type="radio" name="poll_player" value="riyad-mahrez">
                        </label>
                        <label class="daily-poll-option">
                            <span>لخضر بلومي</span>
                            <input type="radio" name="poll_player" value="lakhdar-belloumi">
                        </label>
                        <label class="daily-poll-option">
                            <span>رابح ماجر</span>
                            <input type="radio" name="poll_player" value="rabah-madjer">
                        </label>
                        <button class="daily-poll-submit" type="submit">تصويت</button>
                    </form>
                </article>
            </div>
        </section>

        <div class="ad-box my-4">مساحة إعلانية</div>

        <section class="row g-4 grid-border">
            <div class="col-lg-8">
                <article class="card overlay-card mb-4">
                    <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1000&q=80"
                        class="card-img" alt="city" loading="lazy" decoding="async">
                    <div class="card-img-overlay">
                        <h2 class="h4 headline">جهود حكومية لتحسين الخدمات في العاصمة</h2>
                    </div>
                </article>
                <div class="row g-3">
                    <div class="col-md-4">
                        <article class="news-card"><img
                                src="https://images.unsplash.com/photo-1450101499163-c8848c66ca85?auto=format&fit=crop&w=500&q=80"
                                alt="economy" loading="lazy" decoding="async">
                            <h3 class="small-headline mt-2">البورصة تواصل مكاسبها</h3>
                        </article>
                    </div>
                    <div class="col-md-4">
                        <article class="news-card"><img
                                src="https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=500&q=80"
                                alt="business" loading="lazy" decoding="async">
                            <h3 class="small-headline mt-2">مشروعات صغيرة وفرص عمل</h3>
                        </article>
                    </div>
                    <div class="col-md-4">
                        <article class="news-card"><img
                                src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=500&q=80"
                                alt="youth" loading="lazy" decoding="async">
                            <h3 class="small-headline mt-2">برنامج جديد لدعم الشباب</h3>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid-border">
            <h5 class="section-title"><span>الأكثر قراءة</span></h5>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=500&q=80"
                            alt="cooperation" loading="lazy" decoding="async">
                        <h3 class="small-headline mt-2">لقاء وزاري لبحث التعاون</h3>
                    </article>
                </div>
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=500&q=80"
                            alt="work market" loading="lazy" decoding="async">
                        <h3 class="small-headline mt-2">تقرير حول سوق العمل</h3>
                    </article>
                </div>
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1518005020951-eccb494ad742?auto=format&fit=crop&w=500&q=80"
                            alt="roads" loading="lazy" decoding="async">
                        <h3 class="small-headline mt-2">تطوير شبكة الطرق</h3>
                    </article>
                </div>
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1529107386315-e1a2ed48a620?auto=format&fit=crop&w=500&q=80"
                            alt="conference" loading="lazy" decoding="async">
                        <h3 class="small-headline mt-2">مؤتمر صحفي مرتقب</h3>
                    </article>
                </div>
            </div>
        </section>
        <section class="grid-border">
            <h5 class="section-title"><span>آخر الأخبار</span></h5>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=500&q=80"
                            alt="cooperation" loading="lazy" decoding="async">
                        <h3 class="small-headline mt-2">لقاء وزاري لبحث التعاون</h3>
                    </article>
                </div>
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=500&q=80"
                            alt="work market" loading="lazy" decoding="async">
                        <h3 class="small-headline mt-2">تقرير حول سوق العمل</h3>
                    </article>
                </div>
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1518005020951-eccb494ad742?auto=format&fit=crop&w=500&q=80"
                            alt="roads" loading="lazy" decoding="async">
                        <h3 class="small-headline mt-2">تطوير شبكة الطرق</h3>
                    </article>
                </div>
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1529107386315-e1a2ed48a620?auto=format&fit=crop&w=500&q=80"
                            alt="conference" loading="lazy" decoding="async">
                        <h3 class="small-headline mt-2">مؤتمر صحفي مرتقب</h3>
                    </article>
                </div>
            </div>
        </section>

        <section class="blue-panel mb-4">
            <h5 class="section-title"><span>رياضة</span></h5>
            <div class="row g-4">
                <div class="col-lg-8">
                    <article class="card overlay-card"><img
                            src="https://images.unsplash.com/photo-1518091043644-c1d4457512c6?auto=format&fit=crop&w=900&q=80"
                            alt="sports" loading="lazy" decoding="async">
                        <div class="card-img-overlay">
                            <h2 class="h4 headline">مدرب الفريق يعلن قائمة المباراة المقبلة</h2>
                        </div>
                    </article>
                </div>
                <div class="col-lg-4">
                    <article class="news-card mb-3"><img
                            src="https://images.unsplash.com/photo-1517466787929-bc90951d0974?auto=format&fit=crop&w=600&q=80"
                            alt="training" loading="lazy" decoding="async">
                        <h3 class="small-headline mt-2">نجوم الكرة في تدريبات قوية</h3>
                    </article>
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1522778119026-d647f0596c20?auto=format&fit=crop&w=600&q=80"
                            alt="results" loading="lazy" decoding="async">
                        <h3 class="small-headline mt-2">نتائج الجولة المحلية</h3>
                    </article>
                </div>
            </div>
        </section>

        <section class="grid-border">
            <h5 class="section-title"><span>اقتصاد</span></h5>
            <div class="row g-4 align-items-center">
                <div class="col-lg-4">
                    <h2 class="h5 headline">توقعات بنمو قطاعات حيوية خلال العام الحالي</h2>
                    <p class="summary">خبراء يؤكدون أن إجراءات الإصلاح ودعم الاستثمار تساهم في تحريك النشاط الاقتصادي.
                    </p>
                </div>
                <div class="col-lg-8"><img
                        src="https://images.unsplash.com/photo-1556745757-8d76bdb6984b?auto=format&fit=crop&w=900&q=80"
                        class="img-fluid" alt="economy" loading="lazy" decoding="async"></div>
            </div>
            <div class="row g-3 mt-2">
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1526304640581-d334cdbbf45e?auto=format&fit=crop&w=500&q=80"
                            alt="currencies" loading="lazy" decoding="async">
                        <h3 class="small-headline mt-2">أسعار العملات اليوم</h3>
                    </article>
                </div>
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&w=500&q=80"
                            alt="investments" loading="lazy" decoding="async">
                        <h3 class="small-headline mt-2">خطة لجذب الاستثمارات</h3>
                    </article>
                </div>
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=500&q=80"
                            alt="real estate" loading="lazy" decoding="async">
                        <h3 class="small-headline mt-2">التمويل العقاري يتصدر</h3>
                    </article>
                </div>
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1579621970563-ebec7560ff3e?auto=format&fit=crop&w=500&q=80"
                            alt="trade" loading="lazy" decoding="async">
                        <h3 class="small-headline mt-2">مؤشرات إيجابية للتجارة</h3>
                    </article>
                </div>
            </div>
        </section>

        <section class="blue-panel mb-5">
            <h5 class="section-title"><span>مقالات الرأي</span></h5>
            <div class="row g-3">
                <div class="col-md-3 col-6">
                    <div class="opinion-card"><img class="avatar mb-2"
                            src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100&q=80"
                            alt="author" loading="lazy" decoding="async">
                        <h3 class="small-headline">مستقبل الإعلام الرقمي</h3>
                        <p class="summary text-white-50 mb-0">بقلم كاتب</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="opinion-card"><img class="avatar mb-2"
                            src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=100&q=80"
                            alt="author" loading="lazy" decoding="async">
                        <h3 class="small-headline">قراءة في المشهد الدولي</h3>
                        <p class="summary text-white-50 mb-0">بقلم كاتب</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="opinion-card"><img class="avatar mb-2"
                            src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80"
                            alt="author" loading="lazy" decoding="async">
                        <h3 class="small-headline">الاقتصاد بين الفرص والتحديات</h3>
                        <p class="summary text-white-50 mb-0">بقلم كاتب</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="opinion-card"><img class="avatar mb-2"
                            src="https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=100&q=80"
                            alt="author" loading="lazy" decoding="async">
                        <h3 class="small-headline">أولويات المرحلة المقبلة</h3>
                        <p class="summary text-white-50 mb-0">بقلم كاتب</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="newsletter mb-5">
            <div class="row align-items-center g-3">
                <div class="col-lg-5">
                    <h5 class="headline mb-1">اشترك في النشرة البريدية</h5>
                    <p class="summary mb-0">تابع أهم الأخبار والتحليلات مباشرة في بريدك.</p>
                </div>
                <div class="col-lg-7">
                    <form class="input-group" action="#"><input type="email" class="form-control"
                            placeholder="البريد الإلكتروني"><button class="btn btn-warning text-white"
                            type="button">اشتراك</button></form>
                </div>
            </div>
        </section>

        <div class="ad-box">مساحة إعلانية</div>
    </div>
</main>
<script>
    document.addEventListener('click', function(event) {
        var closeButton = event.target.closest('.hero-floating-close');
        if (!closeButton) {
            return;
        }
        var floatingVideo = closeButton.closest('.hero-floating-video');
        if (floatingVideo) {
            floatingVideo.style.display = 'none';
        }
    });
</script>