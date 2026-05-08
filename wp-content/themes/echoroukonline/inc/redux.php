<?php
/**
 * Optional Redux Framework options.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

function echorouk_is_redux_available() {
	return class_exists( 'Redux' ) && is_callable( array( 'Redux', 'set_args' ) ) && is_callable( array( 'Redux', 'set_section' ) );
}

function echorouk_register_redux_options() {
	if ( ! echorouk_is_redux_available() ) {
		return;
	}

	$opt_name = 'echorouk_options';

	Redux::set_args(
		$opt_name,
		array(
			'display_name' => esc_html__( 'Echourouk Online', 'echoroukonline' ),
			'menu_title'   => esc_html__( 'Theme Options', 'echoroukonline' ),
			'page_title'   => esc_html__( 'Echourouk Online Options', 'echoroukonline' ),
			'menu_type'    => 'menu',
			'menu_slug'    => 'echorouk-options',
			'dev_mode'     => false,
			'admin_bar'    => true,
		)
	);

	Redux::set_section(
		$opt_name,
		array(
			'title'  => esc_html__( 'General', 'echoroukonline' ),
			'id'     => 'general',
			'fields' => array(
				array( 'id' => 'site_logo', 'type' => 'media', 'title' => esc_html__( 'Site logo', 'echoroukonline' ) ),
				array( 'id' => 'dark_logo', 'type' => 'media', 'title' => esc_html__( 'Dark logo', 'echoroukonline' ) ),
				array( 'id' => 'favicon', 'type' => 'media', 'title' => esc_html__( 'Favicon', 'echoroukonline' ) ),
				array( 'id' => 'default_post_image', 'type' => 'media', 'title' => esc_html__( 'Default post image', 'echoroukonline' ) ),
				array( 'id' => 'enable_dark_mode', 'type' => 'switch', 'title' => esc_html__( 'Enable dark mode', 'echoroukonline' ), 'default' => false ),
				array( 'id' => 'enable_sticky_header', 'type' => 'switch', 'title' => esc_html__( 'Enable sticky header', 'echoroukonline' ), 'default' => true ),
			),
		)
	);

	Redux::set_section(
		$opt_name,
		array(
			'title'  => esc_html__( 'Layout', 'echoroukonline' ),
			'id'     => 'layout',
			'fields' => array(
				array( 'id' => 'container_width', 'type' => 'select', 'title' => esc_html__( 'Container width', 'echoroukonline' ), 'options' => echorouk_redux_container_options(), 'default' => 'container-xl' ),
				array( 'id' => 'header_layout', 'type' => 'select', 'title' => esc_html__( 'Header layout', 'echoroukonline' ), 'options' => echorouk_redux_layout_options(), 'default' => 'default' ),
				array( 'id' => 'footer_layout', 'type' => 'select', 'title' => esc_html__( 'Footer layout', 'echoroukonline' ), 'options' => echorouk_redux_footer_options(), 'default' => 'columns' ),
				array( 'id' => 'sidebar_position', 'type' => 'select', 'title' => esc_html__( 'Sidebar position', 'echoroukonline' ), 'options' => array( 'right' => esc_html__( 'Right', 'echoroukonline' ), 'left' => esc_html__( 'Left', 'echoroukonline' ), 'none' => esc_html__( 'None', 'echoroukonline' ) ), 'default' => 'right' ),
				array( 'id' => 'enable_breadcrumbs', 'type' => 'switch', 'title' => esc_html__( 'Enable breadcrumbs', 'echoroukonline' ), 'default' => true ),
			),
		)
	);

	Redux::set_section(
		$opt_name,
		array(
			'title'  => esc_html__( 'Homepage', 'echoroukonline' ),
			'id'     => 'homepage',
			'fields' => array(
				array( 'id' => 'hero_layout', 'type' => 'select', 'title' => esc_html__( 'Hero layout', 'echoroukonline' ), 'options' => array( 'lead-grid' => esc_html__( 'Lead grid', 'echoroukonline' ), 'single-lead' => esc_html__( 'Single lead', 'echoroukonline' ) ), 'default' => 'lead-grid' ),
				array( 'id' => 'hero_post_id', 'type' => 'select', 'data' => 'posts', 'title' => esc_html__( 'Hero lead story', 'echoroukonline' ), 'subtitle' => esc_html__( 'Editorial team can choose the homepage lead story.', 'echoroukonline' ) ),
				array( 'id' => 'featured_section_enabled', 'type' => 'switch', 'title' => esc_html__( 'Featured section', 'echoroukonline' ), 'default' => true ),
				array( 'id' => 'latest_news_enabled', 'type' => 'switch', 'title' => esc_html__( 'Latest news section', 'echoroukonline' ), 'default' => true ),
				array( 'id' => 'most_read_enabled', 'type' => 'switch', 'title' => esc_html__( 'Most read section', 'echoroukonline' ), 'default' => true ),
				array( 'id' => 'editorial_section_enabled', 'type' => 'switch', 'title' => esc_html__( 'Editorial recommendation section', 'echoroukonline' ), 'default' => true ),
				array( 'id' => 'video_section_enabled', 'type' => 'switch', 'title' => esc_html__( 'Video section', 'echoroukonline' ), 'default' => true ),
				array( 'id' => 'newsletter_enabled', 'type' => 'switch', 'title' => esc_html__( 'Newsletter block', 'echoroukonline' ), 'default' => true ),
				array( 'id' => 'newsletter_external_action_url', 'type' => 'text', 'title' => esc_html__( 'Newsletter endpoint URL', 'echoroukonline' ), 'subtitle' => esc_html__( 'Optional external URL. Leave empty to use the built-in subscribe endpoint.', 'echoroukonline' ) ),
				array( 'id' => 'latest_news_count', 'type' => 'spinner', 'title' => esc_html__( 'Latest news count', 'echoroukonline' ), 'default' => 8, 'min' => 4, 'max' => 24 ),
			),
		)
	);

	Redux::set_section(
		$opt_name,
		array(
			'title'  => esc_html__( 'Article', 'echoroukonline' ),
			'id'     => 'article',
			'fields' => array(
				array( 'id' => 'show_ai_summary', 'type' => 'switch', 'title' => esc_html__( 'Show AI summary', 'echoroukonline' ), 'default' => true ),
				array( 'id' => 'show_tts_player', 'type' => 'switch', 'title' => esc_html__( 'Show TTS player', 'echoroukonline' ), 'default' => true ),
				array( 'id' => 'show_reading_time', 'type' => 'switch', 'title' => esc_html__( 'Show reading time', 'echoroukonline' ), 'default' => true ),
				array( 'id' => 'show_author_box', 'type' => 'switch', 'title' => esc_html__( 'Show author box', 'echoroukonline' ), 'default' => true ),
				array( 'id' => 'show_related_articles', 'type' => 'switch', 'title' => esc_html__( 'Show related articles', 'echoroukonline' ), 'default' => true ),
				array( 'id' => 'show_article_most_read_widget', 'type' => 'switch', 'title' => esc_html__( 'Show Most Read widget in article sidebar', 'echoroukonline' ), 'default' => true ),
				array( 'id' => 'show_social_share', 'type' => 'switch', 'title' => esc_html__( 'Show social share', 'echoroukonline' ), 'default' => true ),
				array( 'id' => 'disable_comment_box', 'type' => 'switch', 'title' => esc_html__( 'Disable comment box', 'echoroukonline' ), 'default' => true ),
				array( 'id' => 'enable_sticky_ad_sidebar', 'type' => 'switch', 'title' => esc_html__( 'Enable sticky ad/sidebar', 'echoroukonline' ), 'default' => true ),
			),
		)
	);

	Redux::set_section(
		$opt_name,
		array(
			'title'  => esc_html__( 'Performance', 'echoroukonline' ),
			'id'     => 'performance',
			'fields' => array(
				array( 'id' => 'disable_bootstrap_js', 'type' => 'switch', 'title' => esc_html__( 'Disable Bootstrap JS components not used', 'echoroukonline' ), 'default' => true ),
				array( 'id' => 'lazy_load_images', 'type' => 'switch', 'title' => esc_html__( 'Lazy-load images', 'echoroukonline' ), 'default' => true ),
				array( 'id' => 'preload_main_font', 'type' => 'switch', 'title' => esc_html__( 'Preload main font', 'echoroukonline' ), 'default' => false ),
				array( 'id' => 'enable_critical_css', 'type' => 'switch', 'title' => esc_html__( 'Enable critical CSS placeholder', 'echoroukonline' ), 'default' => false ),
				array( 'id' => 'disable_emojis', 'type' => 'switch', 'title' => esc_html__( 'Disable emojis', 'echoroukonline' ), 'default' => true ),
				array( 'id' => 'disable_embeds', 'type' => 'switch', 'title' => esc_html__( 'Disable embeds', 'echoroukonline' ), 'default' => false ),
				array( 'id' => 'disable_block_library_css', 'type' => 'switch', 'title' => esc_html__( 'Disable block library CSS when not needed', 'echoroukonline' ), 'default' => true ),
			),
		)
	);

	Redux::set_section(
		$opt_name,
		array(
			'title'  => esc_html__( 'Ads', 'echoroukonline' ),
			'id'     => 'ads',
			'fields' => array(
				array( 'id' => 'header_ad', 'type' => 'textarea', 'title' => esc_html__( 'Header ad', 'echoroukonline' ) ),
				array( 'id' => 'article_top_ad', 'type' => 'textarea', 'title' => esc_html__( 'Article top ad', 'echoroukonline' ) ),
				array( 'id' => 'article_middle_ad', 'type' => 'textarea', 'title' => esc_html__( 'Article middle ad', 'echoroukonline' ) ),
				array( 'id' => 'sidebar_ad', 'type' => 'textarea', 'title' => esc_html__( 'Sidebar ad', 'echoroukonline' ) ),
				array( 'id' => 'footer_ad', 'type' => 'textarea', 'title' => esc_html__( 'Footer ad', 'echoroukonline' ) ),
			),
		)
	);

	Redux::set_section(
		$opt_name,
		array(
			'title'  => esc_html__( 'Footer', 'echoroukonline' ),
			'id'     => 'footer',
			'fields' => array(
				array( 'id' => 'footer_contact_address', 'type' => 'textarea', 'title' => esc_html__( 'Footer address', 'echoroukonline' ) ),
				array( 'id' => 'footer_contact_phone', 'type' => 'text', 'title' => esc_html__( 'Footer phone', 'echoroukonline' ) ),
				array( 'id' => 'footer_contact_email', 'type' => 'text', 'title' => esc_html__( 'Footer email', 'echoroukonline' ) ),
			),
		)
	);

	Redux::set_section(
		$opt_name,
		array(
			'title'  => esc_html__( 'Social', 'echoroukonline' ),
			'id'     => 'social',
			'fields' => array(
				array( 'id' => 'facebook', 'type' => 'text', 'title' => esc_html__( 'Facebook', 'echoroukonline' ) ),
				array( 'id' => 'twitter', 'type' => 'text', 'title' => esc_html__( 'X/Twitter', 'echoroukonline' ) ),
				array( 'id' => 'instagram', 'type' => 'text', 'title' => esc_html__( 'Instagram', 'echoroukonline' ) ),
				array( 'id' => 'youtube', 'type' => 'text', 'title' => esc_html__( 'YouTube', 'echoroukonline' ) ),
				array( 'id' => 'tiktok', 'type' => 'text', 'title' => esc_html__( 'TikTok', 'echoroukonline' ) ),
				array( 'id' => 'whatsapp', 'type' => 'text', 'title' => esc_html__( 'WhatsApp', 'echoroukonline' ) ),
				array( 'id' => 'telegram', 'type' => 'text', 'title' => esc_html__( 'Telegram', 'echoroukonline' ) ),
				array( 'id' => 'rss', 'type' => 'text', 'title' => esc_html__( 'RSS', 'echoroukonline' ), 'default' => get_bloginfo( 'rss2_url' ) ),
				array( 'id' => 'podcast_primary_url', 'type' => 'text', 'title' => esc_html__( 'Podcast primary URL', 'echoroukonline' ) ),
				array( 'id' => 'podcast_secondary_url', 'type' => 'text', 'title' => esc_html__( 'Podcast secondary URL', 'echoroukonline' ) ),
				array( 'id' => 'podcast_soundcloud_url', 'type' => 'text', 'title' => esc_html__( 'Podcast SoundCloud URL', 'echoroukonline' ) ),
				array( 'id' => 'podcast_archive_url', 'type' => 'text', 'title' => esc_html__( 'Podcast archive URL', 'echoroukonline' ) ),
			),
		)
	);
}
add_action( 'after_setup_theme', 'echorouk_register_redux_options', 20 );

function echorouk_redux_admin_notice() {
	if ( echorouk_is_redux_available() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $screen && ! in_array( $screen->base, array( 'appearance_page_echorouk-options', 'themes', 'dashboard' ), true ) ) {
		return;
	}

	printf(
		'<div class="notice notice-info"><p>%s</p></div>',
		esc_html__( 'Echourouk Online uses theme defaults now. Install and activate the Redux Framework plugin only if you need the Theme Options admin panel.', 'echoroukonline' )
	);
}
add_action( 'admin_notices', 'echorouk_redux_admin_notice' );

function echorouk_redux_container_options() {
	return array(
		'container'       => 'container',
		'container-lg'    => 'container-lg',
		'container-xl'    => 'container-xl',
		'container-xxl'   => 'container-xxl',
		'container-fluid' => 'container-fluid',
	);
}

function echorouk_redux_layout_options() {
	return array(
		'default' => esc_html__( 'Default', 'echoroukonline' ),
		'center'  => esc_html__( 'Centered', 'echoroukonline' ),
		'compact' => esc_html__( 'Compact', 'echoroukonline' ),
	);
}

function echorouk_redux_footer_options() {
	return array(
		'columns' => esc_html__( 'Columns', 'echoroukonline' ),
		'compact' => esc_html__( 'Compact', 'echoroukonline' ),
	);
}
