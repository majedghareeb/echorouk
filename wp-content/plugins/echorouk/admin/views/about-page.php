<?php
if (! defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap echorouk-homepage-editor-wrap">
    <h1><?php esc_html_e('Homepage Theme Integration', 'echorouk-homepage'); ?></h1>
    <p class="description">
        <?php esc_html_e('Use these functions in your theme templates to read editor-selected homepage content.', 'echorouk-homepage'); ?>
    </p>

    <div class="ehp-doc-card">
        <h2>1) Available Theme Functions</h2>
        <ul>
            <li><code>echorouk_homepage_get_config()</code>: returns full homepage config array.</li>
            <li><code>echorouk_homepage_get_section($id)</code>: returns one section object by ID.</li>
            <li><code>echorouk_homepage_get_posts_for_section($id, $fallback_limit)</code>: returns <code>WP_Post[]</code> for that section based on manual/latest selection.</li>
        </ul>

        <h3>Section IDs</h3>
        <p><code>news_ticker</code>, <code>hero</code>, <code>world</code>, <code>video</code>, <code>sport</code>, <code>economy</code>, <code>opinion</code>, <code>last</code>, <code>jawaher</code>, <code>floating_video</code></p>
    </div>

    <div class="ehp-doc-card">
        <h2>2) Basic Usage In Theme</h2>
<pre><code>&lt;?php
$hero_posts = echorouk_homepage_get_posts_for_section('hero', 4);

if (! empty($hero_posts)) {
    foreach ($hero_posts as $post) {
        setup_postdata($post);
        ?&gt;
        &lt;article&gt;
            &lt;a href="&lt;?php the_permalink(); ?&gt;"&gt;&lt;?php the_title(); ?&gt;&lt;/a&gt;
        &lt;/article&gt;
        &lt;?php
    }
    wp_reset_postdata();
}
?&gt;</code></pre>
    </div>

    <div class="ehp-doc-card">
        <h2>3) Hero Section (Main + Live + Side/Fallback)</h2>
<pre><code>&lt;?php
$hero = echorouk_homepage_get_section('hero');

if ($hero && ! empty($hero['enabled'])) {
    $meta = isset($hero['meta']) && is_array($hero['meta']) ? $hero['meta'] : [];

    $main_id = ! empty($meta['main_post_id']) ? (int) $meta['main_post_id'] : 0;
    $live_enabled = ! empty($meta['live_coverage_enabled']);
    $live_id = ! empty($meta['live_post_id']) ? (int) $meta['live_post_id'] : 0;
    $side_ids = ! empty($meta['side_post_ids']) && is_array($meta['side_post_ids']) ? array_map('absint', $meta['side_post_ids']) : [];
    $fallback_ids = ! empty($meta['fallback_post_ids']) && is_array($meta['fallback_post_ids']) ? array_map('absint', $meta['fallback_post_ids']) : [];

    $main_post = $main_id ? get_post($main_id) : null;
    $live_post = ($live_enabled && $live_id) ? get_post($live_id) : null;

    // If no live coverage article, use fallback posts in right column.
    $right_column_ids = ! empty($live_post) ? $side_ids : $fallback_ids;
    $right_posts = ! empty($right_column_ids) ? get_posts([
        'post_type' =&gt; 'post',
        'post_status' =&gt; 'publish',
        'post__in' =&gt; $right_column_ids,
        'orderby' =&gt; 'post__in',
        'posts_per_page' =&gt; 3,
    ]) : [];
}
?&gt;</code></pre>
    </div>

    <div class="ehp-doc-card">
        <h2>4) World Section (Main + Secondary)</h2>
<pre><code>&lt;?php
$world = echorouk_homepage_get_section('world');

if ($world && ! empty($world['enabled'])) {
    $meta = isset($world['meta']) && is_array($world['meta']) ? $world['meta'] : [];

    $main_post = ! empty($meta['main_post_id']) ? get_post((int) $meta['main_post_id']) : null;

    $secondary_ids = ! empty($meta['secondary_post_ids']) && is_array($meta['secondary_post_ids'])
        ? array_map('absint', $meta['secondary_post_ids'])
        : [];

    $secondary_posts = ! empty($secondary_ids) ? get_posts([
        'post_type' =&gt; 'post',
        'post_status' =&gt; 'publish',
        'post__in' =&gt; $secondary_ids,
        'orderby' =&gt; 'post__in',
        'posts_per_page' =&gt; 6,
    ]) : [];
}
?&gt;</code></pre>
    </div>

    <div class="ehp-doc-card">
        <h2>5) Floating Video</h2>
<pre><code>&lt;?php
$floating = echorouk_homepage_get_section('floating_video');

if ($floating && ! empty($floating['enabled'])) {
    $meta = isset($floating['meta']) && is_array($floating['meta']) ? $floating['meta'] : [];
    $video_url = ! empty($meta['video_url']) ? esc_url($meta['video_url']) : '';
    $autoplay = ! empty($meta['autoplay']);

    if ($video_url) {
        // Render your floating player.
        // Example: &lt;iframe src="..." allow="autoplay"&gt;&lt;/iframe&gt;
    }
}
?&gt;</code></pre>
    </div>

    <div class="ehp-doc-card">
        <h2>6) Generic Sections (Video/Sport/Economy/Opinion/Last/Jawaher)</h2>
<pre><code>&lt;?php
$section_posts = echorouk_homepage_get_posts_for_section('economy', 6);

foreach ($section_posts as $post) {
    setup_postdata($post);
    // Render card.
}
wp_reset_postdata();
?&gt;</code></pre>
    </div>
</div>
