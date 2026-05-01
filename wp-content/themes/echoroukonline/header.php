<?php

/**
 * Site header.
 *
 * @package EchouroukOnline
 */

defined('ABSPATH') || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<a class="skip-link" href="#primary"><?php esc_html_e('Skip to content', 'echoroukonline'); ?></a>

	<header class="site-header echorouk-header" role="banner">
		<?php echorouk_the_ad_slot('header_ad'); ?>
		<div class="<?php echo esc_attr(echorouk_container_class()); ?>">
			<div class="echorouk-header-meta">
				<div class="echorouk-meta-date">
					<?php
					printf(
						/* translators: 1: formatted date, 2: formatted time */
						esc_html__('%1$s - %2$s', 'echoroukonline'),
						esc_html(wp_date('j F Y')),
						esc_html(wp_date('H:i'))
					);
					?>
				</div>
				<div class="echorouk-meta-tools">
					<a class="echorouk-alert-link"
						href="#"><?php esc_html_e('تفعيل الإشعارات', 'echoroukonline'); ?></a>
					<a class="echorouk-live-pill" href="#"><?php esc_html_e('البث الحي (0)', 'echoroukonline'); ?></a>
				</div>
			</div>

			<div class="echorouk-header-logo">
				<div class="site-branding">
					<a class="site-branding__link echorouk-brand-link" href="<?php echo esc_url(home_url('/')); ?>"
						rel="home">
						<?php
						$logo_url      = echorouk_get_media_option_url('site_logo');
						$dark_logo_url = echorouk_get_media_option_url('dark_logo');
						$site_logo      = site_url('wp-content/themes/echoroukonline/assets/images/logo/ech-logo.svg');
						if ($logo_url) :
						?>
							<img class="site-logo site-logo--light echorouk-brand-logo"
								src="<?php echo esc_url($logo_url); ?>" width="260" height="76"
								alt="<?php echo esc_attr(get_bloginfo('name')); ?>" decoding="async">
							<?php if ($dark_logo_url) : ?>
								<img class="site-logo site-logo--dark echorouk-brand-logo"
									src="<?php echo esc_url($dark_logo_url); ?>" width="260" height="76"
									alt="<?php echo esc_attr(get_bloginfo('name')); ?>" decoding="async">
							<?php endif; ?>
						<?php else : ?>
							<div class="" id="echorouk-logo-dark"></div>
						<?php endif; ?>
					</a>
				</div>
			</div>

			<div class="echorouk-nav-row">
				<button class="nav-toggle echorouk-menu-button" type="button" data-nav-toggle aria-expanded="false"
					aria-controls="primary-menu">
					<?php echo echorouk_svg_icon('menu'); ?>
					<span class="screen-reader-text"><?php esc_html_e('Menu', 'echoroukonline'); ?></span>
				</button>

				<nav class="primary-navigation echorouk-primary-navigation" id="primary-menu"
					aria-label="<?php esc_attr_e('Primary menu', 'echoroukonline'); ?>" data-primary-menu>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'container'      => false,
							'menu_class'     => 'primary-menu echorouk-primary-menu',
							'fallback_cb'    => false,
							'depth'          => 1,
						)
					);
					?>
				</nav>
			</div>
		</div>
		<?php get_template_part('template-parts/components/breaking-news'); ?>
	</header>