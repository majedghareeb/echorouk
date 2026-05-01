<?php
/**
 * Shared helpers.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

function echorouk_theme_default_options() {
	return apply_filters( 'echorouk/default_options', array(
		'site_logo'                    => '',
		'dark_logo'                    => '',
		'favicon'                      => '',
		'default_post_image'           => '',
		'enable_dark_mode'             => false,
		'enable_sticky_header'         => true,
		'container_width'              => 'container-xl',
		'header_layout'                => 'default',
		'footer_layout'                => 'columns',
		'sidebar_position'             => 'right',
		'enable_breadcrumbs'           => true,
		'hero_layout'                  => 'lead-grid',
		'hero_post_id'                 => 0,
		'featured_section_enabled'     => true,
		'latest_news_enabled'          => true,
		'most_read_enabled'            => true,
		'editorial_section_enabled'    => true,
		'video_section_enabled'        => true,
		'newsletter_enabled'           => true,
		'latest_news_count'            => 8,
		'show_ai_summary'              => true,
		'show_tts_player'              => true,
		'show_reading_time'            => true,
		'show_author_box'              => true,
		'show_related_articles'        => true,
		'show_social_share'            => true,
		'enable_sticky_ad_sidebar'     => true,
		'disable_bootstrap_js'         => true,
		'lazy_load_images'             => true,
		'preload_main_font'            => false,
		'enable_critical_css'          => false,
		'disable_emojis'               => true,
		'disable_embeds'               => false,
		'disable_block_library_css'    => true,
		'header_ad'                    => '',
		'article_top_ad'               => '',
		'article_middle_ad'            => '',
		'sidebar_ad'                   => '',
		'footer_ad'                    => '',
		'facebook'                     => '',
		'twitter'                      => '',
		'instagram'                    => '',
		'youtube'                      => '',
		'tiktok'                       => '',
		'whatsapp'                     => '',
		'telegram'                     => '',
		'rss'                          => get_bloginfo( 'rss2_url' ),
	) );
}

function echorouk_get_option( $key, $default = null ) {
	$defaults = echorouk_theme_default_options();

	if ( null === $default && array_key_exists( $key, $defaults ) ) {
		$default = $defaults[ $key ];
	}

	if ( class_exists( 'Redux' ) ) {
		$value = Redux::get_option( 'echorouk_options', $key );
		if ( null !== $value && '' !== $value ) {
			return $value;
		}
	}

	global $echorouk_options;
	if ( is_array( $echorouk_options ) && array_key_exists( $key, $echorouk_options ) && '' !== $echorouk_options[ $key ] ) {
		return $echorouk_options[ $key ];
	}

	return $default;
}

function echorouk_news_post_types() {
	return apply_filters( 'echorouk/news_post_types', array( 'post', 'video', 'gallery', 'audio', 'document' ) );
}

function echorouk_article_post_types() {
	return apply_filters( 'echorouk/article_post_types', echorouk_news_post_types() );
}

function echorouk_asset_version( $relative_path ) {
	$path = ECHOROUK_THEME_DIR . '/' . ltrim( $relative_path, '/' );

	return file_exists( $path ) ? (string) filemtime( $path ) : ECHOROUK_THEME_VERSION;
}

function echorouk_get_media_option_url( $key ) {
	$value = echorouk_get_option( $key, '' );

	if ( is_array( $value ) && ! empty( $value['url'] ) ) {
		return esc_url_raw( $value['url'] );
	}

	if ( is_string( $value ) ) {
		return esc_url_raw( $value );
	}

	return '';
}

function echorouk_get_media_option_id( $key ) {
	$value = echorouk_get_option( $key, '' );

	return is_array( $value ) && ! empty( $value['id'] ) ? absint( $value['id'] ) : 0;
}

function echorouk_container_class() {
	$allowed = array( 'container', 'container-sm', 'container-md', 'container-lg', 'container-xl', 'container-xxl', 'container-fluid' );
	$value   = echorouk_get_option( 'container_width', 'container-xl' );

	$container = in_array( $value, $allowed, true ) ? $value : 'container-xl';

	return apply_filters( 'echorouk/container_class', $container );
}

function echorouk_has_sidebar() {
	return 'none' !== echorouk_get_option( 'sidebar_position', 'right' );
}

function echorouk_is_arabic_locale() {
	return 0 === strpos( determine_locale(), 'ar' );
}

function echorouk_sanitize_bool( $value ) {
	return (bool) $value;
}

function echorouk_sanitize_kses_post( $value ) {
	return wp_kses_post( $value );
}

function echorouk_meta_auth_callback() {
	return current_user_can( 'edit_posts' );
}

function echorouk_reading_time( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$stored  = absint( get_post_meta( $post_id, 'reading_time', true ) );

	if ( $stored > 0 ) {
		return $stored;
	}

	$content = wp_strip_all_tags( get_post_field( 'post_content', $post_id ) );
	$words   = preg_split( '/\s+/u', trim( $content ) );
	$count   = is_array( $words ) && '' !== trim( $content ) ? count( $words ) : 0;

	return max( 1, (int) ceil( $count / 200 ) );
}

function echorouk_get_primary_category( $post_id = 0 ) {
	$post_id    = $post_id ? absint( $post_id ) : get_the_ID();
	$categories = get_the_category( $post_id );

	return ! empty( $categories ) ? $categories[0] : null;
}

function echorouk_get_cached_post_ids( $cache_key, $args, $ttl = 300 ) {
	$args = wp_parse_args(
		$args,
		array(
			'post_type'           => echorouk_news_post_types(),
			'post_status'         => 'publish',
			'posts_per_page'      => 6,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);

	$args['fields'] = 'ids';
	$key            = 'echorouk_' . sanitize_key( $cache_key ) . '_' . md5( wp_json_encode( $args ) );
	$ids            = get_transient( $key );

	if ( false !== $ids && is_array( $ids ) ) {
		return array_map( 'absint', $ids );
	}

	$query = new WP_Query( $args );
	$ids   = array_map( 'absint', $query->posts );

	set_transient( $key, $ids, absint( $ttl ) );

	return $ids;
}

function echorouk_get_cached_posts( $cache_key, $args, $ttl = 300 ) {
	$ids = echorouk_get_cached_post_ids( $cache_key, $args, $ttl );

	if ( empty( $ids ) ) {
		return array();
	}

	return get_posts(
		array(
			'post_type'      => echorouk_news_post_types(),
			'post__in'       => $ids,
			'orderby'        => 'post__in',
			'posts_per_page' => count( $ids ),
		)
	);
}

function echorouk_post_image_html( $post_id = 0, $size = 'medium_large', $class = '', $is_lcp = false ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$class   = trim( 'echorouk-post-image ' . $class );
	$attr    = array(
		'class'    => $class,
		'decoding' => 'async',
	);

	if ( $is_lcp ) {
		$attr['fetchpriority'] = 'high';
		$attr['loading']       = false;
	} elseif ( echorouk_get_option( 'lazy_load_images', true ) ) {
		$attr['loading'] = 'lazy';
	}

	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail( $post_id, $size, $attr );
	}

	$default_id = echorouk_get_media_option_id( 'default_post_image' );
	if ( $default_id ) {
		return wp_get_attachment_image( $default_id, $size, false, $attr );
	}

	$default_url = echorouk_get_media_option_url( 'default_post_image' );
	if ( $default_url ) {
		$loading = ! empty( $attr['loading'] ) ? ' loading="' . esc_attr( $attr['loading'] ) . '"' : '';
		$fetch   = ! empty( $attr['fetchpriority'] ) ? ' fetchpriority="' . esc_attr( $attr['fetchpriority'] ) . '"' : '';

		return sprintf(
			'<img src="%1$s" class="%2$s" width="1200" height="675" alt="%3$s" decoding="async"%4$s%5$s>',
			esc_url( $default_url ),
			esc_attr( $class ),
			esc_attr( get_the_title( $post_id ) ),
			$loading,
			$fetch
		);
	}

	return '<div class="' . esc_attr( $class . ' echorouk-image-placeholder' ) . '" aria-hidden="true"></div>';
}

function echorouk_current_url() {
	$scheme = is_ssl() ? 'https://' : 'http://';
	$host   = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
	$uri    = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

	return esc_url_raw( $scheme . $host . $uri );
}

function echorouk_svg_icon( $name, $class = '' ) {
	$icons = array(
		'search' => '<path d="m21 21-4.35-4.35m1.35-5.15a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z"/>',
		'menu'   => '<path d="M4 6h16M4 12h16M4 18h16"/>',
		'close'  => '<path d="m6 6 12 12M18 6 6 18"/>',
		'play'   => '<path d="M8 5v14l11-7-11-7Z"/>',
		'audio'  => '<path d="M9 18V6l8-2v12M9 18a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm8-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>',
		'file'   => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6Z"/><path d="M14 3v6h6"/>',
		'share'  => '<path d="M18 8a3 3 0 1 0-2.83-4H15a3 3 0 0 0 .17 1L8.9 8.63a3 3 0 1 0 0 4.74l6.27 3.64A3 3 0 1 0 16 15.27L9.73 11.6a3 3 0 0 0 0-1.2L16 6.73A3 3 0 0 0 18 8Z"/>',
		'mail'   => '<path d="M4 5h16v14H4z"/><path d="m4 7 8 6 8-6"/>',
		'user'   => '<path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/>',
	);

	if ( empty( $icons[ $name ] ) ) {
		return '';
	}

	return sprintf(
		'<svg class="icon %1$s" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">%2$s</svg>',
		esc_attr( $class ),
		$icons[ $name ]
	);
}
