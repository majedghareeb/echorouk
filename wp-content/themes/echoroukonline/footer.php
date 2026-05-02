<?php

/**
 * Site footer.
 *
 * @package EchouroukOnline
 */

defined('ABSPATH') || exit;

$footer_contact_address = trim((string) echorouk_get_option('footer_contact_address', ''));
$footer_contact_phone   = trim((string) echorouk_get_option('footer_contact_phone', '023713990-023713982'));
$footer_contact_email   = sanitize_email((string) echorouk_get_option('footer_contact_email', 'info@echorouk.net'));
?>
<footer class="site-footer echorouk-footer" role="contentinfo">
    <?php echorouk_the_ad_slot('footer_ad'); ?>
    <div class="<?php echo esc_attr(echorouk_container_class()); ?>">
        <div class="echorouk-footer-shell">
            <div class="echorouk-footer-main">
                <div class="echorouk-footer-grid">
                    <section class="echorouk-footer-col echorouk-footer-about">
                        <a class="echorouk-footer-brand" href="<?php echo esc_url(home_url('/')); ?>">
                            <div class="" id="echorouk-logo-white"></div>
                        </a>
                        <p><?php esc_html_e('منذ سنة 2005 أطلقت مؤسسة الشروق للإعلام والنشر واحدا من أولى المواقع الإخبارية في الجزائر والعالم العربي من أجل توفير تغطية آنية ومستمرّة للأحداث على مدار الساعة بالعربية والإنجليزية والفرنسية.', 'echoroukonline'); ?>
                        </p>
                        <a class="echorouk-footer-more" href="#"><span
                                aria-hidden="true">&rsaquo;</span><?php esc_html_e('المزيد', 'echoroukonline'); ?>
                        </a>
                    </section>

                    <section class="echorouk-footer-col echorouk-footer-sections">
                        <?php
						$menu_locations  = get_nav_menu_locations();
						$primary_menu_id = isset($menu_locations['primary']) ? (int) $menu_locations['primary'] : 0;
						$primary_items   = array();

						if ($primary_menu_id) {
							$menu_items = wp_get_nav_menu_items($primary_menu_id);
							if (is_array($menu_items)) {
								foreach ($menu_items as $item) {
									if ((int) $item->menu_item_parent > 0) {
										continue;
									}
									$primary_items[] = $item;
								}
							}
						}

						if (! empty($primary_items)) :
							$split_index = (int) ceil(count($primary_items) / 2);
							$first_col   = array_slice($primary_items, 0, $split_index);
							$second_col  = array_slice($primary_items, $split_index);
						?>
                        <ul class="echorouk-footer-links-col">
                            <?php foreach ($first_col as $item) : ?>
                            <li><a href="<?php echo esc_url($item->url); ?>"><?php echo esc_html($item->title); ?></a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <ul class="echorouk-footer-links-col">
                            <?php foreach ($second_col as $item) : ?>
                            <li><a href="<?php echo esc_url($item->url); ?>"><?php echo esc_html($item->title); ?></a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php else : ?>
                        <ul class="echorouk-footer-links-col">
                            <li><a href="#"><?php esc_html_e('الرئيسية', 'echoroukonline'); ?></a></li>
                            <li><a href="#"><?php esc_html_e('الجزائر', 'echoroukonline'); ?></a></li>
                            <li><a href="#"><?php esc_html_e('العالم', 'echoroukonline'); ?></a></li>
                            <li><a href="#"><?php esc_html_e('اقتصاد', 'echoroukonline'); ?></a></li>
                            <li><a href="#"><?php esc_html_e('رياضة', 'echoroukonline'); ?></a></li>
                        </ul>
                        <ul class="echorouk-footer-links-col">
                            <li><a href="#"><?php esc_html_e('الرأي', 'echoroukonline'); ?></a></li>
                            <li><a href="#"><?php esc_html_e('جواهر', 'echoroukonline'); ?></a></li>
                            <li><a href="#"><?php esc_html_e('منوعات', 'echoroukonline'); ?></a></li>
                            <li><a href="#"><?php esc_html_e('قناة الشروق', 'echoroukonline'); ?></a></li>
                            <li><a href="#"><?php esc_html_e('العامة', 'echoroukonline'); ?></a></li>
                        </ul>
                        <?php endif; ?>
                    </section>

                    <section class="echorouk-footer-col">
                        <div class="echorouk-footer-contact">
                            <h6><?php esc_html_e('Contact us', 'echoroukonline'); ?></h6>
                            <p>
                                <?php if ($footer_contact_address) : ?>
                                <?php echo nl2br(esc_html($footer_contact_address)); ?><br>
                                <?php endif; ?>
                                <?php if ($footer_contact_phone) : ?>
                                <?php echo esc_html($footer_contact_phone); ?><br>
                                <?php endif; ?>
                                <?php if ($footer_contact_email) : ?>
                                <a href="mailto:<?php echo esc_attr($footer_contact_email); ?>"><?php echo esc_html($footer_contact_email); ?></a>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="echorouk-footer-page-links">
                            <a href="#"><span
                                    aria-hidden="true">&rsaquo;</span><?php esc_html_e('Live Streaming', 'echoroukonline'); ?>
                            </a>
                            <a href="#"><span
                                    aria-hidden="true">&rsaquo;</span><?php esc_html_e('Privacy Policy', 'echoroukonline'); ?>
                            </a>
                            <a href="#"><span
                                    aria-hidden="true">&rsaquo;</span><?php esc_html_e('Newsletter', 'echoroukonline'); ?>
                            </a>
                            <a href="#"><span
                                    aria-hidden="true">&rsaquo;</span><?php esc_html_e('Advertising', 'echoroukonline'); ?>
                            </a>
                        </div>
                    </section>
                </div>
            </div>

            <div class="echorouk-footer-sub">
                <div class="echorouk-footer-social">
                    <?php
					$social = array(
						'facebook'  => '<img src="' . ECHOROUK_THEME_URI . '/assets/icons/facebook-01-stroke-rounded.svg"></object>',
						'twitter'  => '<img src="' . ECHOROUK_THEME_URI . '/assets/icons/new-twitter-rectangle-stroke-rounded.svg"></object>',
						'instagram'  => '<img src="' . ECHOROUK_THEME_URI . '/assets/icons/instagram-stroke-rounded.svg"></object>',
						'youtube'  => '<img src="' . ECHOROUK_THEME_URI . '/assets/icons/youtube-stroke-rounded.svg"></object>',
					);
					foreach ($social as $network => $label) :
						$url = echorouk_get_option($network, '');
						if (! $url) {
							continue;
						}
					?>
                    <a class="echorouk-social-link echorouk-social-link--<?php echo esc_attr($network); ?>"
                        href="<?php echo esc_url($url); ?>" rel="me noopener" target="_blank"
                        aria-label="<?php echo esc_attr(ucfirst(str_replace('twitter', 'x', $network))); ?>">
                        <?php echo $label; ?></a>
                    <?php endforeach; ?>
                </div>
                <div class="echorouk-footer-copy">
                    <?php esc_html_e('Copyright is reserved', 'echoroukonline'); ?> -
                    <?php echo esc_html(gmdate('Y')); ?></div>
            </div>
        </div>
        <div class="echorouk-footer-network" aria-hidden="true">
            <span>
                <a href="#" target="_blank">
                    <div class="" id="ech-arabi-logo"></div>
                </a>
            </span>
            <span><a href="#" target="_blank">
                    <div class="" id="ech-jawahir-logo"></div>
                </a>
            </span>
            <span><a href="#" target="_blank">
                    <div class="" id="ech-news-logo"></div>
                </a>
            </span>
            <span><a href="#" target="_blank">
                    <div class="" id="ech-tv-logo"></div>
                </a>
            </span>
            <span><a href="#" target="_blank">
                    <div class="" id="ech-montadayat-logo"></div>
                </a>
            </span>
        </div>
        <div class="echorouk-footer-rule" aria-hidden="true"></div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>

</html>
