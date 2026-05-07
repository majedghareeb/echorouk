<?php

/**
 * Single article content.
 *
 * @package EchouroukOnline
 */

defined('ABSPATH') || exit;

$post_id      = get_the_ID();
$format       = isset($args['format']) ? sanitize_key($args['format']) : get_post_type();
$ai_summary   = get_post_meta($post_id, 'ai_summary', true);
$source_name  = get_post_meta($post_id, 'source_name', true);
$source_url   = get_post_meta($post_id, 'source_url', true);
$sponsored    = get_post_meta($post_id, 'sponsored_label', true);
$schema_type  = 'https://schema.org/NewsArticle';
$author       = echorouk_get_post_author_data($post_id);
$reading_time = echorouk_reading_time($post_id);
$has_rail     = 'none' !== echorouk_get_option('sidebar_position', 'right');
$show_tts     = echorouk_get_option('show_tts_player', true);
$show_comments = ! echorouk_get_option('disable_comment_box', true);
$share_url    = rawurlencode(get_permalink($post_id));
$deck_text    = has_excerpt($post_id) ? get_the_excerpt($post_id) : wp_trim_words(wp_strip_all_tags(get_post_field('post_content', $post_id)), 25, '...');
$icon_base    = trailingslashit(ECHOROUK_THEME_URI . '/assets/icons');
?>
<main id="primary" class="site-main single-article-main">
    <?php get_template_part('template-parts/components/reading-progress'); ?>
    <div class="<?php echo esc_attr(echorouk_container_class()); ?>">
        <article id="post-<?php the_ID(); ?>" <?php post_class('single-article single-article--feature'); ?> itemscope
            itemtype="<?php echo esc_url($schema_type); ?>">
            <div class="single-article__lead">
                <figure class="single-article__media" itemprop="image">
                    <?php echo echorouk_post_image_html($post_id, 'echorouk-hero', 'single-article__image', true); ?>
                </figure>
                <header class="single-article__header">
                    <!-- <div class="single-article__badges">
                        <?php //echorouk_the_category_badge($post_id); 
                        ?>
                        <?php //if ($sponsored) : 
                        ?>
                            <span class="sponsored-label"><?php //echo esc_html($sponsored); 
                                                            ?></span>
                        <?php //endif; 
                        ?>
                    </div> -->
                    <h1 class="single-article__title" itemprop="headline"><?php the_title(); ?></h1>
                    <?php if ($deck_text) : ?>
                        <p class="single-article__deck"><?php echo esc_html($deck_text); ?></p>
                    <?php endif; ?>
                    <div class="single-article__author-line">
                        <a class="single-article__author-avatar" href="<?php echo esc_url($author['url']); ?>"
                            aria-label="<?php echo esc_attr($author['name']); ?>">
                            <?php echo $author['avatar']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                            ?>
                        </a>
                        <div class="single-article__author-copy">
                            <a class="single-article__author-name"
                                href="<?php echo esc_url($author['url']); ?>"><?php echo esc_html($author['name']); ?></a>
                            <time
                                datetime="<?php echo esc_attr(get_the_date(DATE_W3C, $post_id)); ?>"><?php echo esc_html(get_the_date('', $post_id)); ?></time>
                        </div>
                    </div>
                </header>
            </div>

            <div class="single-article__divider"></div>

            <div
                class="single-article__body-layout<?php echo $has_rail ? '' : ' single-article__body-layout--no-rail'; ?>">
                <?php if ($has_rail) : ?>
                    <?php get_sidebar(); ?>
                <?php endif; ?>
                <div class="single-article__body">
                    <div class="single-article__actions">
                        <div class="single-article__actions-main">
                            <?php if (echorouk_get_option('show_social_share', true)) : ?>
                                <a class="single-article__action"
                                    href="<?php echo esc_url('https://www.facebook.com/sharer/sharer.php?u=' . $share_url); ?>"
                                    target="_blank" rel="noopener">
                                    <img class="single-article__action-icon"
                                        src="<?php echo esc_url($icon_base . 'share-01-stroke-rounded.svg'); ?>" alt="">
                                    <span><?php esc_html_e('share', 'echoroukonline'); ?></span>
                                </a>
                            <?php endif; ?>
                            <?php if ($show_tts) : ?>
                                <a class="single-article__action" href="#single-article-ai-player-area" data-tts-toggle
                                    aria-controls="single-article-ai-player-area" aria-expanded="false">
                                    <img class="single-article__action-icon"
                                        src="<?php echo esc_url($icon_base . 'headphones-stroke-rounded.svg'); ?>" alt="">
                                    <span><?php esc_html_e('Listen to Summary', 'echoroukonline'); ?></span>
                                </a>
                            <?php endif; ?>
                            <span class="single-article__action single-article__action--muted">
                                <img class="single-article__action-icon"
                                    src="<?php echo esc_url($icon_base . 'clock-03-stroke-rounded.svg'); ?>" alt="">
                                <span><?php echo esc_html(sprintf(__('%d min read', 'echoroukonline'), $reading_time)); ?></span>
                            </span>
                        </div>
                        <?php if (echorouk_get_option('show_ai_summary', true)) : ?>
                            <a class="single-article__action single-article__action--summary" href="#single-article-summary"
                                data-summary-toggle aria-controls="single-article-summary" aria-expanded="false">
                                <img class="single-article__action-icon"
                                    src="<?php echo esc_url($icon_base . 'ai-audio-stroke-rounded.svg'); ?>" alt="">
                                <span><?php esc_html_e('show_ai_aummary', 'echoroukonline'); ?></span>
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php echorouk_the_ad_slot('article_top_ad'); ?>

                    <?php
                    if ('video' === $format) {
                        get_template_part('template-parts/components/video-embed');
                    } elseif ('audio' === $format) {
                        get_template_part('template-parts/components/audio-player');
                    } elseif ('document' === $format) {
                        get_template_part('template-parts/components/pdf-block');
                    } elseif ('gallery' === $format) {
                        get_template_part('template-parts/components/gallery-block');
                    }
                    ?>

                    <?php if (echorouk_get_option('show_ai_summary', true)) : ?>
                        <aside id="single-article-summary" class="ai-summary" hidden>
                            <h2><?php esc_html_e('Summary', 'echoroukonline'); ?></h2>
                            <?php if ($ai_summary) : ?>
                                <div><?php echo wp_kses_post(wpautop($ai_summary)); ?></div>
                            <?php else : ?>
                                <div><?php esc_html_e('No AI summary available at the moment.', 'echoroukonline'); ?></div>
                            <?php endif; ?>
                        </aside>
                    <?php endif; ?>

                    <?php if ($show_tts) : ?>
                        <section id="single-article-ai-player-area" class="single-article__ai-player-area" hidden></section>
                    <?php endif; ?>

                    <div class="entry-content" itemprop="articleBody">
                        <?php the_content(); ?>
                        <?php wp_link_pages(); ?>
                    </div>

                    <?php echorouk_the_ad_slot('article_middle_ad'); ?>

                    <?php if ($source_name || $source_url) : ?>
                        <p class="article-source">
                            <?php esc_html_e('Source:', 'echoroukonline'); ?>
                            <?php if ($source_url) : ?>
                                <a href="<?php echo esc_url($source_url); ?>" rel="nofollow noopener"
                                    target="_blank"><?php echo esc_html($source_name ? $source_name : $source_url); ?></a>
                            <?php else : ?>
                                <?php echo esc_html($source_name); ?>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>

                    <footer class="single-article__footer">
                        <?php the_tags('<div class="tag-list">', '', '</div>'); ?>
                        <?php get_template_part('template-parts/components/voting-placeholder'); ?>
                    </footer>

                    <?php if ($show_comments && (comments_open() || get_comments_number())) : ?>
                        <?php comments_template(); ?>
                    <?php endif; ?>
                </div>
            </div>
        </article>

        <?php if (echorouk_get_option('show_author_box', true)) : ?>
            <?php get_template_part('template-parts/components/author-box'); ?>
        <?php endif; ?>

        <?php if (echorouk_get_option('show_related_articles', true)) : ?>
            <?php get_template_part('template-parts/components/related-articles'); ?>
        <?php endif; ?>
    </div>
</main>