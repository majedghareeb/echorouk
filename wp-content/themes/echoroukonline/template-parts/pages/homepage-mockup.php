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
                <aside class="col-lg-3 order-3 order-lg-3 hero-col-right">
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


                <section class="col-lg-5 order-1 order-lg-2 hero-col-center">
                    <article class="hero-lead hero-col-card">
                        <div class="hero-lead-media position-relative">
                            <?php if ($hero_main) : ?>
                                <span class="tag"><?php echo esc_html($hero_tag ? $hero_tag : 'العالم'); ?></span>
                                <a
                                    href="<?php echo esc_url(get_permalink($hero_main)); ?>"><?php echo echorouk_post_image_html($hero_main->ID, 'echorouk-hero'); ?></a>
                                <a href="<?php echo esc_url(get_permalink($hero_main)); ?>" class="hero-play-center"
                                    aria-label="قراءة الخبر">
                                    <img
                                        src="<?php echo ECHOROUK_THEME_URI; ?>/assets/icons/play-circle-stroke-rounded-white.svg"></img></a>
                                <a href="#" class="hero-lead-icon">
                                </a>
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
                <aside class="col-lg-4 order-2 order-lg-1 hero-col-left">
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

            </div>
        </section>
        <div class="ad-box my-4">مساحة إعلانية</div>
        <hr class="section-divider my-4">
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
        <hr class="section-divider my-4">
        <section class="grid-border">
            <header class="most-read-header">
                <h5 class="section-title"><span>الأكثر قراءة</span></h5>
                <h5 class="most-read-tabs">
                    <ul class="most-read-time-filters" role="tablist" aria-label="تصفية الأكثر قراءة حسب المدة">
                        <li role="presentation">
                            <button type="button" class="most-read-time-filter" data-time-range="day" role="tab"
                                aria-selected="false" aria-pressed="false">
                                اليوم
                            </button>
                        </li>
                        <li role="presentation">
                            <button type="button" class="most-read-time-filter" data-time-range="week" role="tab"
                                aria-selected="false" aria-pressed="false">
                                هذا الأسبوع
                            </button>
                        </li>
                        <li role="presentation">
                            <button type="button" class="most-read-time-filter is-active" data-time-range="month"
                                role="tab" aria-selected="true" aria-pressed="true">
                                هذا الشهر
                            </button>
                        </li>
                    </ul>
                </h5>
            </header>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=500&q=80"
                            alt="cooperation" loading="lazy" decoding="async">
                        <div class="most-read-mini-date">2026/04/05</div>
                        <h3 class="small-headline mt-2">لقاء وزاري لبحث التعاون</h3>
                    </article>
                </div>
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=500&q=80"
                            alt="work market" loading="lazy" decoding="async">
                        <div class="most-read-mini-date">2026/04/05</div>

                        <h3 class="small-headline mt-2">تقرير حول سوق العمل</h3>
                    </article>
                </div>
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1518005020951-eccb494ad742?auto=format&fit=crop&w=500&q=80"
                            alt="roads" loading="lazy" decoding="async">
                        <div class="most-read-mini-date">2026/04/05</div>

                        <h3 class="small-headline mt-2">تطوير شبكة الطرق</h3>
                    </article>
                </div>
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1529107386315-e1a2ed48a620?auto=format&fit=crop&w=500&q=80"
                            alt="conference" loading="lazy" decoding="async">
                        <div class="most-read-mini-date">2026/04/05</div>

                        <h3 class="small-headline mt-2">مؤتمر صحفي مرتقب</h3>
                    </article>
                </div>
            </div>
        </section>
        <hr class="section-divider my-4">
        <section class="video-showcase grid-border">
            <div class="video-showcase-grid">
                <aside class="col-lg-3 video-showcase-side">
                    <div class="video-side-ad">إعلان<br>300/250</div>

                    <div class="video-side-most">
                        <div class="video-side-title-wrap">
                            <h3 class="video-side-title">الأكثر مشاهدة</h3>
                            <img
                                src="<?php echo ECHOROUK_THEME_URI; ?>/assets/icons/arrow-left-double-stroke-rounded.svg"></img>
                        </div>

                        <article class="video-side-feature">
                            <div class="video-side-thumb">
                                <img src="https://images.unsplash.com/photo-1624727828489-a1e03b79bba8?auto=format&fit=crop&w=500&q=80"
                                    alt="most watched" loading="lazy" decoding="async">
                                <span class="video-thumb-play" aria-hidden="true">
                                    <img src="<?php echo ECHOROUK_THEME_URI; ?>/assets/icons/play-stroke-rounded-2.svg"
                                        alt="">
                                </span>
                            </div>
                            <div class="video-side-date">2026/04/01</div>
                            <h4>سعيود يستقبل رئيس المجلس الوطني للأقاليم والجهات التونسي</h4>
                        </article>

                        <div class="video-side-list">
                            <article class="video-side-item">
                                <div class="video-side-thumb">
                                    <img class="video-side-thumb-img"
                                        src="https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=180&q=80"
                                        alt="thumb 1" loading="lazy" decoding="async">
                                    <span class="video-thumb-play" aria-hidden="true">
                                        <img src="<?php echo ECHOROUK_THEME_URI; ?>/assets/icons/play-stroke-rounded-2.svg"
                                            alt="">
                                    </span>
                                </div>

                                <div>
                                    <h5>الاتحاد الإسباني يدين هتافات عنصرية ضد المسلمين في ودية مصر</h5>
                                </div>

                            </article>
                            <article class="video-side-item">
                                <img class="video-side-thumb-img"
                                    src="https://images.unsplash.com/photo-1461896836934-ffe607ba8211?auto=format&fit=crop&w=180&q=80"
                                    alt="thumb 2" loading="lazy" decoding="async">
                                <div>
                                    <h5>مواجهة قوية مرتقبة بين كيليا نيمور والصينية تشيو شييوان</h5>
                                </div>
                            </article>
                            <article class="video-side-item">
                                <img class="video-side-thumb-img"
                                    src="https://images.unsplash.com/photo-1529107386315-e1a2ed48a620?auto=format&fit=crop&w=180&q=80"
                                    alt="thumb 3" loading="lazy" decoding="async">
                                <div>
                                    <h5>محركتنا مفتوحة ضد الهيمنة الأمريكية</h5>
                                </div>
                            </article>
                        </div>
                    </div>
                </aside>

                <section class="col-lg-9 video-showcase-main">
                    <header class="video-main-header">
                        <div class="video-main-logo-wrap">
                            <div class="video-main-kicker">فيديوهات</div>
                            <div class="video-main-logo" id="echorouk-logo-white"></div>
                        </div>
                        <a href="#" class="video-main-all">كل الفيديوهات <span aria-hidden="true"><img
                                    style="transform: matrix(-1, 0, 0, -1, 0, 0);"
                                    src="<?php echo ECHOROUK_THEME_URI; ?>/assets/icons/play-stroke-rounded.svg"></img></span></a>
                    </header>

                    <article class="video-main-feature">
                        <img src="https://images.unsplash.com/photo-1518091043644-c1d4457512c6?auto=format&fit=crop&w=1200&q=80"
                            alt="featured video" loading="lazy" decoding="async">
                        <div class="video-main-overlay">
                            <div class="video-main-feature-date">15/03/2025</div>

                            <div class="video-main-feature-title">
                                <img
                                    src="<?php echo ECHOROUK_THEME_URI; ?>/assets/icons/play-circle-stroke-rounded.svg">
                                <h3>البرازيل تكبل أنشيلوتي</h3>
                            </div>
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
        <hr class="section-divider my-4">
        <section class="grid-border sports-section">
            <div class="video-sports-logo-wrap">
                <div class="video-sports-logo" id="echorouk-sports-logo-dark"></div>
            </div>
            <div class="row g-4 align-items-center sports-main-grid">
                <div class="col-lg-4 sports-main-article">
                    <h3>توقعات بنمو قطاعات حيوية خلال العام الحالي</h3>
                    <p class="summary">خبراء يؤكدون أن إجراءات الإصلاح ودعم الاستثمار تساهم في تحريك النشاط الاقتصادي.
                    </p>
                </div>
                <div class="col-lg-8 sports-main-media"><img
                        src="https://images.unsplash.com/photo-1556745757-8d76bdb6984b?auto=format&fit=crop&w=900&q=80"
                        class="img-fluid" alt="economy" loading="lazy" decoding="async"></div>
            </div>
            <div class="row g-3 mt-2 sports-sub-grid">
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1526304640581-d334cdbbf45e?auto=format&fit=crop&w=500&q=80"
                            alt="currencies" loading="lazy" decoding="async">
                        <div class="mini-date">2026/04/05</div>
                        <h3 class="small-headline mt-2">أسعار العملات اليوم</h3>
                    </article>
                </div>
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&w=500&q=80"
                            alt="investments" loading="lazy" decoding="async">
                        <div class="mini-date">2026/04/05</div>
                        <h3 class="small-headline mt-2">خطة لجذب الاستثمارات</h3>
                    </article>
                </div>
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=500&q=80"
                            alt="real estate" loading="lazy" decoding="async">
                        <div class="mini-date">2026/04/05</div>
                        <h3 class="small-headline mt-2">التمويل العقاري يتصدر</h3>
                    </article>
                </div>
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1579621970563-ebec7560ff3e?auto=format&fit=crop&w=500&q=80"
                            alt="trade" loading="lazy" decoding="async">
                        <div class="mini-date">2026/04/05</div>
                        <h3 class="small-headline mt-2">مؤشرات إيجابية للتجارة</h3>
                    </article>
                </div>
            </div>
        </section>
        <hr class="section-divider my-4">
        <section class="grid-border economy-section">
            <h5 class="section-title"><span>اقتصاد</span></h5>
            <div class="row g-4 align-items-center economy-main-grid">
                <div class="col-lg-4 economy-main-article">
                    <h3 class="headline">توقعات بنمو قطاعات حيوية خلال العام الحالي</h3>
                    <p class="summary">خبراء يؤكدون أن إجراءات الإصلاح ودعم الاستثمار تساهم في تحريك النشاط الاقتصادي.
                    </p>
                </div>
                <div class="col-lg-8 economy-main-media"><img
                        src="https://images.unsplash.com/photo-1556745757-8d76bdb6984b?auto=format&fit=crop&w=900&q=80"
                        class="img-fluid" alt="economy" loading="lazy" decoding="async"></div>
            </div>
            <div class="row g-3 mt-2 economy-sub-grid">
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1526304640581-d334cdbbf45e?auto=format&fit=crop&w=500&q=80"
                            alt="currencies" loading="lazy" decoding="async">
                        <div class="mini-date">2026/04/05</div>
                        <h3 class="small-headline mt-2">أسعار العملات اليوم</h3>
                    </article>
                </div>
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&w=500&q=80"
                            alt="investments" loading="lazy" decoding="async">
                        <div class="mini-date">2026/04/05</div>
                        <h3 class="small-headline mt-2">خطة لجذب الاستثمارات</h3>
                    </article>
                </div>
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=500&q=80"
                            alt="real estate" loading="lazy" decoding="async">
                        <div class="mini-date">2026/04/05</div>
                        <h3 class="small-headline mt-2">التمويل العقاري يتصدر</h3>
                    </article>
                </div>
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1579621970563-ebec7560ff3e?auto=format&fit=crop&w=500&q=80"
                            alt="trade" loading="lazy" decoding="async">
                        <div class="mini-date">2026/04/05</div>
                        <h3 class="small-headline mt-2">مؤشرات إيجابية للتجارة</h3>
                    </article>
                </div>
            </div>
        </section>
        <hr class="section-divider my-4">
        <section class="daily-boxes grid-border">
            <div class="row g-3 daily-boxes-grid">
                <div class="col-lg-6 col-md-6 col-12">
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
                            <div class="daily-print-cover-wrap">
                                <img src="https://images.unsplash.com/photo-1495020689067-958852a7765e?auto=format&fit=crop&w=400&q=80"
                                    alt="newspaper cover" loading="lazy" decoding="async">
                            </div>
                            <div class="daily-print-meta">
                                <div class="daily-print-date">الخميس 16 أفريل 2026</div>
                                <div class="daily-print-downloads">تحميل 1569</div>
                                <div class="download-button">
                                    <a href="#" class="daily-print-download" aria-label="تحميل النسخة">
                                        <img
                                            src="<?php echo ECHOROUK_THEME_URI; ?>/assets/icons/download-03-stroke-rounded.svg"></img>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </article>
                </div>
                <div class="col-lg-6 col-md-6 col-12">
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
                                <input type="radio" name="poll_player" value="riyad-mahrez">
                                <span>رياض محرز</span>
                            </label>
                            <label class="daily-poll-option">
                                <input type="radio" name="poll_player" value="lakhdar-belloumi">
                                <span>لخضر بلومي</span>
                            </label>
                            <label class="daily-poll-option">
                                <input type="radio" name="poll_player" value="rabah-madjer">
                                <span>رابح ماجر</span>
                            </label>
                            <button class="daily-poll-submit" type="submit">تصويت</button>
                        </form>
                    </article>
                </div>
            </div>
        </section>
        <hr class="section-divider my-4">
        <section class="blue-panel mb-5 opinion-panel">
            <div class="opinion-main-logo-wrap">
                <div class="opinion-main-kicker">أقلام</div>
                <div class="opinion-main-logo" id="echorouk-logo-white"></div>
            </div>
            <?php
            $opinion_samples = array(
                array(
                    'thumb'       => 'https://images.unsplash.com/photo-1505245208761-ba872912fac0?auto=format&fit=crop&w=800&q=80',
                    'author_name' => 'لعلى بشطولة',
                    'author_img'  => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=100&q=80',
                    'date'        => '2026/04/07',
                    'title'       => 'الإمبراطورية التي تصرخ.. تظهر وتسير على أجنحة الفراغ',
                ),
                array(
                    'thumb'       => 'https://images.unsplash.com/photo-1517423440428-a5a00ad493e8?auto=format&fit=crop&w=800&q=80',
                    'author_name' => 'رياض رمضان بن وادن',
                    'author_img'  => 'https://images.unsplash.com/photo-1568602471122-7832951cc4c5?auto=format&fit=crop&w=100&q=80',
                    'date'        => '2026/04/07',
                    'title'       => 'العنف الخفي في خطاب الذات!!',
                ),
                array(
                    'thumb'       => 'https://images.unsplash.com/photo-1505245208761-ba872912fac0?auto=format&fit=crop&w=800&q=80',
                    'author_name' => 'حسين لقرع',
                    'author_img'  => 'https://images.unsplash.com/photo-1506277886164-e25aa3f4ef7f?auto=format&fit=crop&w=100&q=80',
                    'date'        => '2026/04/07',
                    'title'       => 'حملة حطّ اسمها الإمارات',
                ),
                array(
                    'thumb'       => 'https://images.unsplash.com/photo-1521295121783-8a321d551ad2?auto=format&fit=crop&w=800&q=80',
                    'author_name' => 'محمد سليم قلالة',
                    'author_img'  => 'https://images.unsplash.com/photo-1521119989659-a83eee488004?auto=format&fit=crop&w=100&q=80',
                    'date'        => '2026/04/07',
                    'title'       => 'بعض ما علمتنا الحرب على إيران',
                ),
                array(
                    'thumb'       => 'https://images.unsplash.com/photo-1521295121783-8a321d551ad2?auto=format&fit=crop&w=800&q=80',
                    'author_name' => 'لعلى بشطولة',
                    'author_img'  => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=100&q=80',
                    'date'        => '2026/04/08',
                    'title'       => 'مآلات السردية الكبرى في زمن ما بعد اليقين',
                ),
                array(
                    'thumb'       => 'https://images.unsplash.com/photo-1517423440428-a5a00ad493e8?auto=format&fit=crop&w=800&q=80',
                    'author_name' => 'رياض رمضان بن وادن',
                    'author_img'  => 'https://images.unsplash.com/photo-1568602471122-7832951cc4c5?auto=format&fit=crop&w=100&q=80',
                    'date'        => '2026/04/08',
                    'title'       => 'قراءة ثانية في خطاب الأزمة ومجازاتها',
                ),
                array(
                    'thumb'       => 'https://images.unsplash.com/photo-1505245208761-ba872912fac0?auto=format&fit=crop&w=800&q=80',
                    'author_name' => 'حسين لقرع',
                    'author_img'  => 'https://images.unsplash.com/photo-1506277886164-e25aa3f4ef7f?auto=format&fit=crop&w=100&q=80',
                    'date'        => '2026/04/08',
                    'title'       => 'الإقليم بين صخب الدعاية وهدوء الوقائع',
                ),
                array(
                    'thumb'       => 'https://images.unsplash.com/photo-1521295121783-8a321d551ad2?auto=format&fit=crop&w=800&q=80',
                    'author_name' => 'محمد سليم قلالة',
                    'author_img'  => 'https://images.unsplash.com/photo-1521119989659-a83eee488004?auto=format&fit=crop&w=100&q=80',
                    'date'        => '2026/04/08',
                    'title'       => 'إيران والخرائط الجديدة لموازين القوى',
                ),
            );
            ?>
            <div class="row g-0 opinion-grid">
                <?php foreach ($opinion_samples as $sample) : ?>
                    <div class="col-lg-3 col-md-6 col-12">
                        <article class="opinion-card">
                            <img class="opinion-card-thumb" src="<?php echo esc_url($sample['thumb']); ?>"
                                alt="<?php echo esc_attr($sample['author_name']); ?>" loading="lazy" decoding="async">
                            <div class="opinion-card-meta">
                                <div class="opinion-card-author">
                                    <div class="author-name-date">
                                        <span
                                            class="opinion-card-author-name"><?php echo esc_html($sample['author_name']); ?></span>
                                        <div class="opinion-card-date"><?php echo esc_html($sample['date']); ?></div>
                                    </div>

                                    <img class="avatar" src="<?php echo esc_url($sample['author_img']); ?>"
                                        alt="<?php echo esc_attr($sample['author_name']); ?>" loading="lazy"
                                        decoding="async">
                                </div>
                            </div>
                            <h3 class="opinion-card-title"><?php echo esc_html($sample['title']); ?></h3>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <hr class="section-divider my-4">
        <section class="diplomacy-spotlight grid-border">
            <div class="row g-0 align-items-start diplomacy-spotlight-top">

                <div class="col-lg-3 col-12 diplomacy-top-col diplomacy-top-main">
                    <article class="diplomacy-main-story">
                        <h3>زيارة البابا إلى الجزائر محطة دبلوماسية رفيعة</h3>
                        <div class="diplomacy-main-date">2026/04/12</div>
                    </article>
                </div>
                <div class="col-lg-6 col-12 diplomacy-top-col diplomacy-top-feature">
                    <article class="diplomacy-feature-media">
                        <img src="https://images.unsplash.com/photo-1533035350251-aa8b8e208d95?auto=format&fit=crop&w=1000&q=80"
                            alt="diplomatic visit" loading="lazy" decoding="async">
                    </article>
                </div>
                <div class="col-lg-3 col-12 diplomacy-top-col diplomacy-top-side">
                    <aside class="diplomacy-side-list">
                        <article class="diplomacy-side-item">
                            <div class="diplomacy-side-date">2026/04/12</div>
                            <h4>إنذارات المستوى الثاني: أمطار غزيرة جدا عبر 30 ولاية</h4>
                        </article>
                        <article class="diplomacy-side-item">
                            <div class="diplomacy-side-date">2026/04/12</div>
                            <h4>بالفيديو.. الحكم يرفض هدفين لاتحاد العاصمة بمباراة ممثل بلاد مراكش</h4>
                        </article>
                        <article class="diplomacy-side-item">
                            <div class="diplomacy-side-date">2026/04/12</div>
                            <h4>الجوية الجزائرية تجري أكبر توسعة في تاريخها!</h4>
                        </article>
                    </aside>
                </div>

            </div>

            <div class="row g-0 diplomacy-spotlight-bottom">
                <div class="col-lg-4 col-12 diplomacy-bottom-col">
                    <article class="diplomacy-bottom-item">
                        <div class="diplomacy-bottom-date">2026/04/12</div>
                        <h4>3 نقاط خلاف رئيسية تفشل محادثات أمريكا وإيران.. التفاصيل</h4>
                    </article>
                </div>
                <div class="col-lg-4 col-12 diplomacy-bottom-col">
                    <article class="diplomacy-bottom-item">
                        <div class="diplomacy-bottom-date">2026/04/12</div>
                        <h4>السفارة الأمريكية بالجزائر تفتح باب التدريب أمام هذه الفئة</h4>
                    </article>
                </div>
                <div class="col-lg-4 col-12 diplomacy-bottom-col">
                    <article class="diplomacy-bottom-item">
                        <div class="diplomacy-bottom-date">2026/04/12</div>
                        <h4>قاليباف: واشنطن فشلت في كسب ثقة وفدنا المفاوض</h4>
                    </article>
                </div>
            </div>
        </section>
        <hr class="section-divider my-4">
        <section class="podcast-section mb-5">
            <header class="podcast-section-head">
                <h5 class="podcast-section-title">بودكاست</h5>
            </header>
            <div class="row g-3 podcast-grid align-items-stretch">
                <div class="col-lg-3 col-12">
                    <aside class="podcast-follow-card">
                        <p class="podcast-follow-title">تابع بودكاست الشروق على منصاتنا المختلفة</p>
                        <div class="podcast-follow-platforms" aria-label="منصات البودكاست">
                            <a href="#" aria-label="Apple Podcast"><img class="podcast-follow-platforms-icon"
                                    src="<?php echo ECHOROUK_THEME_URI; ?>/assets/icons/music-note-04-stroke-rounded.svg"></a>
                            <a href="#" aria-label="Podcast"><img class="podcast-follow-platforms-icon"
                                    src="<?php echo ECHOROUK_THEME_URI; ?>/assets/icons/podcast-stroke-rounded-2.svg"></a>
                            <a href="#" aria-label="SoundCloud"><img class="podcast-follow-platforms-icon"
                                    src="<?php echo ECHOROUK_THEME_URI; ?>/assets/icons/soundcloud-stroke-rounded.svg"></a>
                        </div>
                        <div class="podcast-box-link">
                            <a href="#">المزيد <img
                                    src="<?php echo ECHOROUK_THEME_URI; ?>/assets/icons/podcast-stroke-rounded-2.svg"></a>
                        </div>

                    </aside>
                </div>

                <div class="col-lg-4 col-12">
                    <section class="podcast-center">
                        <div class="podcast-list">
                            <article class="podcast-list-item">
                                <a href="#" class="podcast-list-thumb">
                                    <img src="https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=700&q=80"
                                        alt="podcast guest" loading="lazy" decoding="async">
                                    <span class="podcast-icon"><img
                                            src="<?php echo ECHOROUK_THEME_URI; ?>/assets/icons/podcast-stroke-rounded-2.svg"></span>
                                </a>
                                <div class="podcast-list-copy">
                                    <time datetime="2026-06-16">16/06/2026</time>
                                    <h3><a href="#">4 مشاريع عملية تخص تخزين المنتجات التجارية ومراقبتها</a></h3>
                                </div>
                            </article>
                            <article class="podcast-list-item">
                                <a href="#" class="podcast-list-thumb">
                                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=700&q=80"
                                        alt="podcast guest" loading="lazy" decoding="async">
                                    <span class="podcast-icon"><img
                                            src="<?php echo ECHOROUK_THEME_URI; ?>/assets/icons/podcast-stroke-rounded-2.svg"></span>
                                </a>
                                <div class="podcast-list-copy">
                                    <time datetime="2026-06-16">16/06/2026</time>
                                    <h3><a href="#">الأمين التنفيذي لمنظمة أمريكا اللاتينية للطاقة في زيارة عمل إلى
                                            الجزائر</a></h3>
                                </div>
                            </article>
                            <article class="podcast-list-item">
                                <a href="#" class="podcast-list-thumb">
                                    <img src="https://images.unsplash.com/photo-1473448912268-2022ce9509d8?auto=format&fit=crop&w=700&q=80"
                                        alt="podcast guest" loading="lazy" decoding="async">
                                    <span class="podcast-icon"><img
                                            src="<?php echo ECHOROUK_THEME_URI; ?>/assets/icons/podcast-stroke-rounded-2.svg"></span>
                                </a>
                                <div class="podcast-list-copy">
                                    <time datetime="2026-06-16">16/06/2026</time>
                                    <h3><a href="#">الصيد الجائر والتهريب ينذران بزوال ثروات طبيعية نادرة في الجزائر</a>
                                    </h3>
                                </div>
                            </article>
                        </div>
                    </section>
                </div>

                <div class="col-lg-5 col-12">
                    <article class="podcast-feature">
                        <img src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=1200&q=80"
                            alt="podcast feature" loading="lazy" decoding="async">
                        <span class="podcast-icon"><img
                                src="<?php echo ECHOROUK_THEME_URI; ?>/assets/icons/podcast-stroke-rounded-2.svg"></span>
                        <div class="podcast-feature-body">
                            <time datetime="2026-06-16">16/06/2026</time>
                            <h3><a href="#">هكذا يتم تسديد رسوم المرقّي العقاري وضريبة السكن</a></h3>
                        </div>
                    </article>
                </div>
            </div>
        </section>
        <hr class="section-divider my-4">
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
        <hr class="section-divider my-4">

        <section class="grid-border jawaher-section">
            <div class="jawaher-logo-wrap">
                <div class="jawaher-logo" id="echorouk-jawahir-logo-dark"></div>
            </div>
            <div class="row g-4 align-items-center jawaher-main-grid">
                <div class="col-lg-4 jawaher-main-article">
                    <h3>توقعات بنمو قطاعات حيوية خلال العام الحالي</h3>
                    <p class="summary">خبراء يؤكدون أن إجراءات الإصلاح ودعم الاستثمار تساهم في تحريك النشاط الاقتصادي.
                    </p>
                </div>
                <div class="col-lg-8 jawaher-main-media"><img
                        src="https://images.unsplash.com/photo-1556745757-8d76bdb6984b?auto=format&fit=crop&w=900&q=80"
                        class="img-fluid" alt="jawaher" loading="lazy" decoding="async"></div>
            </div>
            <div class="row g-3 mt-2 jawaher-sub-grid">
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1526304640581-d334cdbbf45e?auto=format&fit=crop&w=500&q=80"
                            alt="currencies" loading="lazy" decoding="async">
                        <div class="jawaher-mini-date">2026/04/05</div>
                        <h3 class="small-headline mt-2">أسعار العملات اليوم</h3>
                    </article>
                </div>
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&w=500&q=80"
                            alt="investments" loading="lazy" decoding="async">
                        <div class="jawaher-mini-date">2026/04/05</div>
                        <h3 class="small-headline mt-2">خطة لجذب الاستثمارات</h3>
                    </article>
                </div>
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=500&q=80"
                            alt="real estate" loading="lazy" decoding="async">
                        <div class="jawaher-mini-date">2026/04/05</div>
                        <h3 class="small-headline mt-2">التمويل العقاري يتصدر</h3>
                    </article>
                </div>
                <div class="col-6 col-md-3">
                    <article class="news-card"><img
                            src="https://images.unsplash.com/photo-1579621970563-ebec7560ff3e?auto=format&fit=crop&w=500&q=80"
                            alt="trade" loading="lazy" decoding="async">
                        <div class="jawaher-mini-date">2026/04/05</div>
                        <h3 class="small-headline mt-2">مؤشرات إيجابية للتجارة</h3>
                    </article>
                </div>
            </div>
        </section>
        <hr class="section-divider my-4">
        <section class="other-languages-section grid-border mb-5">
            <div class="row g-0">
                <div class="col-lg-6 col-md-6 col-12 other-lang-col">
                    <h3 class="other-lang-title">Français</h3>
                    <article class="other-lang-item">
                        <img src="https://images.unsplash.com/photo-1473448912268-2022ce9509d8?auto=format&fit=crop&w=500&q=80"
                            alt="fr article 1" loading="lazy" decoding="async">
                        <div class="other-lang-copy">
                            <time datetime="2027-06-16">16/06/2027</time>
                            <h4>AADL 3: Les ordres de versements de la 1ère tranche mis en ligne</h4>
                        </div>
                    </article>
                    <article class="other-lang-item">
                        <img src="https://images.unsplash.com/photo-1516467508483-a7212febe31a?auto=format&fit=crop&w=500&q=80"
                            alt="fr article 2" loading="lazy" decoding="async">
                        <div class="other-lang-copy">
                            <time datetime="2027-06-16">16/06/2027</time>
                            <h4>Port d’Alger: Arrivée d’un nouveau chargement de moutons importés en prévision de l’Aïd
                                al-Adha</h4>
                        </div>
                    </article>
                    <article class="other-lang-item">
                        <img src="https://images.unsplash.com/photo-1531384370597-8590413be50a?auto=format&fit=crop&w=500&q=80"
                            alt="fr article 3" loading="lazy" decoding="async">
                        <div class="other-lang-copy">
                            <time datetime="2027-06-16">16/06/2027</time>
                            <h4>5 ans de prison ferme pour l’ancien ministre Ali Aoun</h4>
                        </div>
                    </article>
                </div>
                <div class="col-lg-6 col-md-6 col-12 other-lang-col">
                    <h3 class="other-lang-title">English</h3>
                    <article class="other-lang-item">
                        <img src="https://images.unsplash.com/photo-1473448912268-2022ce9509d8?auto=format&fit=crop&w=500&q=80"
                            alt="en article 1" loading="lazy" decoding="async">
                        <div class="other-lang-copy">
                            <time datetime="2027-06-16">16/06/2027</time>
                            <h4>AADL 3: First installment payment orders are now online</h4>
                        </div>
                    </article>
                    <article class="other-lang-item">
                        <img src="https://images.unsplash.com/photo-1516467508483-a7212febe31a?auto=format&fit=crop&w=500&q=80"
                            alt="en article 2" loading="lazy" decoding="async">
                        <div class="other-lang-copy">
                            <time datetime="2027-06-16">16/06/2027</time>
                            <h4>Port of Algiers receives new shipment of imported sheep ahead of Eid al-Adha</h4>
                        </div>
                    </article>
                    <article class="other-lang-item">
                        <img src="https://images.unsplash.com/photo-1531384370597-8590413be50a?auto=format&fit=crop&w=500&q=80"
                            alt="en article 3" loading="lazy" decoding="async">
                        <div class="other-lang-copy">
                            <time datetime="2027-06-16">16/06/2027</time>
                            <h4>Former minister Ali Aoun sentenced to five years in prison</h4>
                        </div>
                    </article>
                </div>
            </div>
        </section>
        <hr class="section-divider my-4">
        <div class="ad-box">مساحة إعلانية</div>
    </div>
</main>
<script>
    document.addEventListener('click', function(event) {
        var closeButton = event.target.closest('.hero-floating-close');
        if (!closeButton) {
            var mostReadFilter = event.target.closest('.most-read-time-filter');
            if (!mostReadFilter) {
                return;
            }

            var filterGroup = mostReadFilter.closest('.most-read-time-filters');
            if (!filterGroup) {
                return;
            }

            var filters = filterGroup.querySelectorAll('.most-read-time-filter');
            filters.forEach(function(filter) {
                filter.classList.remove('is-active');
                filter.setAttribute('aria-selected', 'false');
                filter.setAttribute('aria-pressed', 'false');
            });

            mostReadFilter.classList.add('is-active');
            mostReadFilter.setAttribute('aria-selected', 'true');
            mostReadFilter.setAttribute('aria-pressed', 'true');
            return;
        }
        var floatingVideo = closeButton.closest('.hero-floating-video');
        if (floatingVideo) {
            floatingVideo.style.display = 'none';
        }
    });
</script>