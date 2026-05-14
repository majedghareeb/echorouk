<?php
/**
 * SEO and schema helpers.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

/**
 * Track rendered category post IDs for listing schema.
 *
 * @var array<int,int>
 */
$GLOBALS['echorouk_listing_schema_post_ids'] = array();

/**
 * Detect whether a dedicated SEO plugin already controls metadata.
 *
 * @return bool
 */
function echorouk_seo_plugin_active() {
	if ( class_exists( 'WPSEO_Frontend' ) || defined( 'WPSEO_VERSION' ) ) {
		return true;
	}

	if ( defined( 'RANK_MATH_VERSION' ) ) {
		return true;
	}

	if ( defined( 'AIOSEO_VERSION' ) || class_exists( '\\AIOSEO\\Plugin\\Common\\Main' ) ) {
		return true;
	}

	if ( defined( 'SEOPRESS_VERSION' ) ) {
		return true;
	}

	return false;
}

/**
 * Register SEO post and term meta fields.
 *
 * @return void
 */
function echorouk_register_seo_meta() {
	$post_meta_keys = array(
		'echorouk_meta_title'       => 'sanitize_text_field',
		'echorouk_meta_description' => 'sanitize_textarea_field',
		'echorouk_canonical_url'    => 'esc_url_raw',
		'echorouk_robots_noindex'   => 'echorouk_sanitize_bool',
		'echorouk_robots_nofollow'  => 'echorouk_sanitize_bool',
		'echorouk_social_image_id'  => 'absint',
	);

	foreach ( array_keys( get_post_types( array( 'public' => true ) ) ) as $post_type ) {
		foreach ( $post_meta_keys as $key => $sanitize_callback ) {
			register_post_meta(
				$post_type,
				$key,
				array(
					'single'            => true,
					'type'              => in_array( $key, array( 'echorouk_robots_noindex', 'echorouk_robots_nofollow' ), true ) ? 'boolean' : ( 'echorouk_social_image_id' === $key ? 'integer' : 'string' ),
					'show_in_rest'      => true,
					'sanitize_callback' => $sanitize_callback,
					'auth_callback'     => 'echorouk_meta_auth_callback',
				)
			);
		}
	}

	$term_meta_keys = array(
		'echorouk_meta_title'       => 'sanitize_text_field',
		'echorouk_meta_description' => 'sanitize_textarea_field',
		'echorouk_canonical_url'    => 'esc_url_raw',
		'echorouk_robots_noindex'   => 'echorouk_sanitize_bool',
		'echorouk_robots_nofollow'  => 'echorouk_sanitize_bool',
		'echorouk_social_image_id'  => 'absint',
	);

	foreach ( $term_meta_keys as $key => $sanitize_callback ) {
		register_term_meta(
			'category',
			$key,
			array(
				'single'            => true,
				'type'              => in_array( $key, array( 'echorouk_robots_noindex', 'echorouk_robots_nofollow' ), true ) ? 'boolean' : ( 'echorouk_social_image_id' === $key ? 'integer' : 'string' ),
				'show_in_rest'      => true,
				'sanitize_callback' => $sanitize_callback,
				'auth_callback'     => static function () {
					return current_user_can( 'manage_categories' );
				},
			)
		);
	}
}
add_action( 'init', 'echorouk_register_seo_meta' );

/**
 * Determine whether current view is a news article.
 *
 * @return bool
 */
function echorouk_is_news_article_view() {
	if ( ! is_singular() ) {
		return false;
	}

	$post_type = get_post_type( get_queried_object_id() );
	$news_types = echorouk_news_post_types();
	if ( ! in_array( 'live_coverage', $news_types, true ) ) {
		$news_types[] = 'live_coverage';
	}

	return in_array( $post_type, $news_types, true );
}

/**
 * Get current singular post ID.
 *
 * @return int
 */
function echorouk_get_current_post_id() {
	if ( ! is_singular() ) {
		return 0;
	}

	return absint( get_queried_object_id() );
}

/**
 * Get requested page number.
 *
 * @return int
 */
function echorouk_get_current_paged() {
	return max( 1, (int) get_query_var( 'paged', 1 ), (int) get_query_var( 'page', 1 ) );
}

/**
 * Determine if current page is an About page.
 *
 * @return bool
 */
function echorouk_is_about_page() {
	if ( ! is_page() ) {
		return false;
	}

	$page = get_post( get_queried_object_id() );
	if ( ! ( $page instanceof WP_Post ) ) {
		return false;
	}

	$slug = (string) $page->post_name;
	if ( in_array( $slug, array( 'about', 'about-us', 'aboutus', 'who-we-are', 'about-echorouk' ), true ) ) {
		return true;
	}

	$title = wp_strip_all_tags( get_the_title( $page ) );
	$contains_about = function_exists( 'mb_stripos' ) ? ( false !== mb_stripos( $title, 'about' ) ) : ( false !== stripos( $title, 'about' ) );
	if ( $contains_about ) {
		return true;
	}

	$contains_arabic_about = function_exists( 'mb_stripos' )
		? ( false !== mb_stripos( $title, 'من نحن' ) || false !== mb_stripos( $title, 'عن الشروق' ) )
		: ( false !== strpos( $title, 'من نحن' ) || false !== strpos( $title, 'عن الشروق' ) );

	if ( $contains_arabic_about ) {
		return true;
	}

	return false;
}

/**
 * Resolve custom meta title override.
 *
 * @return string
 */
function echorouk_get_meta_title_override() {
	if ( is_singular() ) {
		$post_id = echorouk_get_current_post_id();
		if ( ! $post_id ) {
			return '';
		}

		$title = trim( (string) get_post_meta( $post_id, 'echorouk_meta_title', true ) );
		return $title;
	}

	if ( is_category() ) {
		$term_id = (int) get_queried_object_id();
		if ( $term_id > 0 ) {
			return trim( (string) get_term_meta( $term_id, 'echorouk_meta_title', true ) );
		}
	}

	return '';
}

/**
 * Filter the rendered document title.
 *
 * @param string $title Existing title.
 * @return string
 */
function echorouk_filter_document_title( $title ) {
	if ( echorouk_seo_plugin_active() ) {
		return $title;
	}

	$override = echorouk_get_meta_title_override();

	return '' !== $override ? $override : $title;
}
add_filter( 'pre_get_document_title', 'echorouk_filter_document_title', 20 );

/**
 * Resolve meta description for current request.
 *
 * @return string
 */
function echorouk_get_meta_description() {
	if ( is_singular() ) {
		$post_id = echorouk_get_current_post_id();
		if ( ! $post_id ) {
			return '';
		}

		$custom = trim( (string) get_post_meta( $post_id, 'echorouk_meta_description', true ) );
		if ( '' !== $custom ) {
			return $custom;
		}

		if ( has_excerpt( $post_id ) ) {
			return wp_strip_all_tags( get_the_excerpt( $post_id ) );
		}

		$content = wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) );
		return wp_trim_words( $content, 28, '...' );
	}

	if ( is_home() || is_front_page() ) {
		$description = trim( (string) get_bloginfo( 'description' ) );
		if ( '' !== $description ) {
			return $description;
		}

		return wp_strip_all_tags( wp_get_document_title() );
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$custom = trim( (string) get_term_meta( $term->term_id, 'echorouk_meta_description', true ) );
			if ( '' !== $custom ) {
				return $custom;
			}

			$description = term_description( $term );
			if ( '' !== trim( wp_strip_all_tags( $description ) ) ) {
				return wp_strip_all_tags( $description );
			}
		}
	}

	if ( is_author() ) {
		$author = get_queried_object();
		if ( $author instanceof WP_User ) {
			$description = trim( (string) get_the_author_meta( 'description', $author->ID ) );
			if ( '' !== $description ) {
				return $description;
			}
		}
	}

	return wp_strip_all_tags( wp_get_document_title() );
}

/**
 * Resolve canonical URL.
 *
 * @return string
 */
function echorouk_get_canonical_url() {
	if ( is_singular() ) {
		$post_id = echorouk_get_current_post_id();
		if ( ! $post_id ) {
			return '';
		}

		$custom = trim( (string) get_post_meta( $post_id, 'echorouk_canonical_url', true ) );
		if ( '' !== $custom ) {
			return esc_url_raw( $custom );
		}

		return (string) get_permalink( $post_id );
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$custom = trim( (string) get_term_meta( $term->term_id, 'echorouk_canonical_url', true ) );
			if ( '' !== $custom ) {
				return esc_url_raw( $custom );
			}

			$link = get_term_link( $term );
			if ( ! is_wp_error( $link ) ) {
				$canonical = (string) $link;
				if ( echorouk_get_current_paged() > 1 ) {
					$canonical = (string) get_pagenum_link( echorouk_get_current_paged() );
				}

				return $canonical;
			}
		}
	}

	if ( is_post_type_archive() ) {
		$link = get_post_type_archive_link( get_post_type() );
		if ( $link ) {
			if ( echorouk_get_current_paged() > 1 ) {
				return (string) get_pagenum_link( echorouk_get_current_paged() );
			}

			return (string) $link;
		}
	}

	if ( is_home() || is_front_page() ) {
		return home_url( '/' );
	}

	if ( is_search() ) {
		return esc_url_raw( get_search_link( get_search_query() ) );
	}

	return esc_url_raw( echorouk_current_url() );
}

/**
 * Resolve the social preview image URL.
 *
 * @return string
 */
function echorouk_get_social_image_url() {
	$image_id = 0;

	if ( is_singular() ) {
		$post_id  = echorouk_get_current_post_id();
		$image_id = absint( get_post_meta( $post_id, 'echorouk_social_image_id', true ) );
		if ( ! $image_id ) {
			$image_id = get_post_thumbnail_id( $post_id );
		}
	} elseif ( is_category() ) {
		$term_id  = (int) get_queried_object_id();
		$image_id = absint( get_term_meta( $term_id, 'echorouk_social_image_id', true ) );
	}

	if ( $image_id ) {
		$image_url = wp_get_attachment_image_url( $image_id, 'echorouk-hero' );
		if ( $image_url ) {
			return (string) $image_url;
		}
	}

	$default_id = echorouk_get_media_option_id( 'default_post_image' );
	if ( $default_id ) {
		$image_url = wp_get_attachment_image_url( $default_id, 'echorouk-hero' );
		if ( $image_url ) {
			return (string) $image_url;
		}
	}

	$default_url = echorouk_get_media_option_url( 'default_post_image' );
	if ( $default_url ) {
		return (string) $default_url;
	}

	return '';
}

/**
 * Output canonical and social meta tags.
 *
 * @return void
 */
function echorouk_output_seo_meta_tags() {
	if ( echorouk_seo_plugin_active() ) {
		return;
	}

	$title       = wp_strip_all_tags( wp_get_document_title() );
	$description = echorouk_get_meta_description();
	$canonical   = echorouk_get_canonical_url();
	$image_url   = echorouk_get_social_image_url();
	$locale      = str_replace( '_', '-', get_locale() );
	$site_name   = get_bloginfo( 'name' );
	$is_article  = echorouk_is_news_article_view();
	$og_type     = $is_article ? 'article' : ( is_author() ? 'profile' : 'website' );

	if ( '' !== $description ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
	}

	if ( '' !== $canonical ) {
		printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $canonical ) );
	}

	printf( '<meta property="og:locale" content="%s">' . "\n", esc_attr( $locale ) );
	printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $og_type ) );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );

	if ( '' !== $description ) {
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $description ) );
	}

	if ( '' !== $canonical ) {
		printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $canonical ) );
	}

	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( $site_name ) );

	if ( '' !== $image_url ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image_url ) );
	}

	$twitter_card = '' !== $image_url ? 'summary_large_image' : 'summary';
	printf( '<meta name="twitter:card" content="%s">' . "\n", esc_attr( $twitter_card ) );
	printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $title ) );

	if ( '' !== $description ) {
		printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $description ) );
	}

	if ( '' !== $image_url ) {
		printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $image_url ) );
	}

	if ( $is_article ) {
		$post_id = echorouk_get_current_post_id();
		if ( $post_id ) {
			printf( '<meta property="article:published_time" content="%s">' . "\n", esc_attr( get_post_time( DATE_W3C, true, $post_id ) ) );
			printf( '<meta property="article:modified_time" content="%s">' . "\n", esc_attr( get_post_modified_time( DATE_W3C, true, $post_id ) ) );
		}
	}
}
add_action( 'wp_head', 'echorouk_output_seo_meta_tags', 5 );

/**
 * Apply robots control rules.
 *
 * @param array<string,bool> $robots Existing robots directives.
 * @return array<string,bool>
 */
function echorouk_filter_wp_robots( $robots ) {
	if ( echorouk_seo_plugin_active() ) {
		return $robots;
	}

	if ( is_404() || is_search() ) {
		$robots['noindex'] = true;
	}

	if ( is_singular() ) {
		$post_id = echorouk_get_current_post_id();
		if ( $post_id ) {
			if ( (bool) get_post_meta( $post_id, 'echorouk_robots_noindex', true ) ) {
				$robots['noindex'] = true;
			}

			if ( (bool) get_post_meta( $post_id, 'echorouk_robots_nofollow', true ) ) {
				$robots['nofollow'] = true;
			}
		}
	}

	if ( is_category() ) {
		$term_id = (int) get_queried_object_id();
		if ( $term_id > 0 ) {
			if ( (bool) get_term_meta( $term_id, 'echorouk_robots_noindex', true ) ) {
				$robots['noindex'] = true;
			}

			if ( (bool) get_term_meta( $term_id, 'echorouk_robots_nofollow', true ) ) {
				$robots['nofollow'] = true;
			}
		}
	}

	return $robots;
}
add_filter( 'wp_robots', 'echorouk_filter_wp_robots', 20 );

/**
 * Enforce large image previews for search snippets.
 *
 * @param array<string,bool|string> $robots Existing robots directives.
 * @return array<string,bool|string>
 */
function echorouk_set_max_image_preview_robots( $robots ) {
	if ( ! is_array( $robots ) ) {
		$robots = array();
	}

	if ( ! echorouk_is_news_article_view() ) {
		return $robots;
	}

	$robots['max-image-preview'] = 'large';

	return $robots;
}
add_filter( 'wp_robots', 'echorouk_set_max_image_preview_robots', 100 );

/**
 * Send X-Robots-Tag HTTP header for news articles.
 *
 * @return void
 */
function echorouk_send_x_robots_tag_header() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || headers_sent() ) {
		return;
	}

	if ( ! echorouk_is_news_article_view() ) {
		return;
	}

	$directives = array( 'max-image-preview:large' );
	$post_id    = echorouk_get_current_post_id();

	if ( $post_id ) {
		if ( (bool) get_post_meta( $post_id, 'echorouk_robots_noindex', true ) ) {
			$directives[] = 'noindex';
		}

		if ( (bool) get_post_meta( $post_id, 'echorouk_robots_nofollow', true ) ) {
			$directives[] = 'nofollow';
		}
	}

	$directives = array_values( array_unique( array_filter( $directives ) ) );
	if ( empty( $directives ) ) {
		return;
	}

	header( 'X-Robots-Tag: ' . implode( ', ', $directives ), true );
}
add_action( 'send_headers', 'echorouk_send_x_robots_tag_header', 20 );

/**
 * Set post IDs displayed in current listing for schema generation.
 *
 * @param array<int,int> $post_ids Post IDs.
 * @return void
 */
function echorouk_set_listing_schema_post_ids( $post_ids ) {
	$post_ids = array_map( 'absint', (array) $post_ids );
	$post_ids = array_values( array_filter( $post_ids ) );

	$GLOBALS['echorouk_listing_schema_post_ids'] = $post_ids;
}

/**
 * Resolve breadcrumb items matching theme output.
 *
 * @return array<int,array<string,string>>
 */
function echorouk_get_breadcrumb_items() {
	if ( is_front_page() || ! echorouk_get_option( 'enable_breadcrumbs', true ) ) {
		return array();
	}

	$items   = array();
	$items[] = array(
		'name' => esc_html__( 'Home', 'echoroukonline' ),
		'url'  => home_url( '/' ),
	);

	if ( is_singular() ) {
		$category = echorouk_get_primary_category();
		if ( $category instanceof WP_Term ) {
			$items[] = array(
				'name' => $category->name,
				'url'  => get_category_link( $category ),
			);
		} elseif ( 'post' !== get_post_type() ) {
			$post_type = get_post_type();
			$object    = get_post_type_object( $post_type );
			$archive   = $post_type ? get_post_type_archive_link( $post_type ) : '';
			if ( $object && $archive ) {
				$items[] = array(
					'name' => $object->labels->name,
					'url'  => $archive,
				);
			}
		}

		$items[] = array(
			'name' => wp_strip_all_tags( get_the_title( get_queried_object_id() ) ),
			'url'  => '',
		);

		return $items;
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$items[] = array(
			'name' => wp_strip_all_tags( single_term_title( '', false ) ),
			'url'  => '',
		);
		return $items;
	}

	if ( is_search() ) {
		$items[] = array(
			'name' => esc_html__( 'Search', 'echoroukonline' ),
			'url'  => '',
		);
		return $items;
	}

	if ( is_post_type_archive() ) {
		$items[] = array(
			'name' => wp_strip_all_tags( post_type_archive_title( '', false ) ),
			'url'  => '',
		);
		return $items;
	}

	$items[] = array(
		'name' => wp_strip_all_tags( get_the_archive_title() ),
		'url'  => '',
	);

	return $items;
}

/**
 * Resolve current article author as a schema Person object.
 *
 * @param int $post_id Post ID.
 * @return array<string,mixed>
 */
function echorouk_get_article_author_schema( $post_id ) {
	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		return array();
	}

	$author = echorouk_get_post_author_data( $post_id );
	if ( empty( $author['name'] ) ) {
		return array();
	}

	$person = array(
		'@type' => 'Person',
		'name'  => (string) $author['name'],
	);

	if ( ! empty( $author['url'] ) ) {
		$person['url'] = esc_url_raw( (string) $author['url'] );
	}

	if ( ! empty( $author['job_title'] ) ) {
		$person['jobTitle'] = (string) $author['job_title'];
	}

	return $person;
}

/**
 * Get current author profile schema graph.
 *
 * @return array<string,mixed>
 */
function echorouk_get_author_profile_schema() {
	if ( ! is_author() ) {
		return array();
	}

	$person = array();
	$url    = '';

	$author = get_queried_object();
	if ( $author instanceof WP_User ) {
		$url = get_author_posts_url( $author->ID );

		$person = array(
			'@type' => 'Person',
			'name'  => (string) get_the_author_meta( 'display_name', $author->ID ),
			'url'   => $url,
		);

		$description = trim( (string) get_the_author_meta( 'description', $author->ID ) );
		if ( '' !== $description ) {
			$person['description'] = $description;
		}

		$avatar = get_avatar_url( $author->ID, array( 'size' => 256 ) );
		if ( $avatar ) {
			$person['image'] = esc_url_raw( $avatar );
		}
	} else {
		$author_slug = (string) get_query_var( 'author_name' );
		$guest       = $author_slug ? echorouk_find_guest_author_by_slug( $author_slug ) : null;

		if ( $guest instanceof WP_Post ) {
			$url = echorouk_get_guest_author_public_url( $guest->ID );

			$person = array(
				'@type' => 'Person',
				'name'  => get_the_title( $guest ),
				'url'   => $url,
			);

			$bio = trim( wp_strip_all_tags( (string) get_post_field( 'post_content', $guest->ID ) ) );
			if ( '' !== $bio ) {
				$person['description'] = wp_trim_words( $bio, 60, '...' );
			}

			$image = get_the_post_thumbnail_url( $guest->ID, 'medium_large' );
			if ( $image ) {
				$person['image'] = esc_url_raw( $image );
			}
		}
	}

	if ( empty( $person['name'] ) ) {
		return array();
	}

	if ( '' === $url ) {
		$url = echorouk_get_canonical_url();
	}

	return array(
		'@context'    => 'https://schema.org',
		'@type'       => 'ProfilePage',
		'mainEntity'  => $person,
		'url'         => esc_url_raw( $url ),
		'name'        => wp_strip_all_tags( wp_get_document_title() ),
		'description' => echorouk_get_meta_description(),
	);
}

/**
 * Get website organization schema.
 *
 * @return array<string,mixed>
 */
function echorouk_get_organization_schema() {
	$site_url = home_url( '/' );
	$logo_url = '';

	$logo_id = get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$logo_url = wp_get_attachment_image_url( $logo_id, 'full' );
	}

	if ( ! $logo_url ) {
		$logo_url = echorouk_get_media_option_url( 'site_logo' );
	}

	$same_as = array();
	foreach ( array( 'facebook', 'twitter', 'instagram', 'youtube', 'tiktok', 'telegram' ) as $network ) {
		$url = echorouk_get_option( $network, '' );
		if ( $url ) {
			$same_as[] = esc_url_raw( $url );
		}
	}

	$schema = array(
		'@context'     => 'https://schema.org',
		'@type'        => 'NewsMediaOrganization',
		'@id'          => trailingslashit( $site_url ) . '#organization',
		'name'         => get_bloginfo( 'name' ),
		'url'          => esc_url_raw( $site_url ),
		'description'  => get_bloginfo( 'description' ),
	);

	$about_page = get_page_by_path( 'about-us' );
	if ( $about_page instanceof WP_Post ) {
		$schema['publishingPrinciples'] = esc_url_raw( get_permalink( $about_page ) );
	}

	if ( $logo_url ) {
		$schema['logo'] = array(
			'@type' => 'ImageObject',
			'url'   => esc_url_raw( $logo_url ),
		);
	}

	if ( ! empty( $same_as ) ) {
		$schema['sameAs'] = array_values( array_unique( $same_as ) );
	}

	return $schema;
}

/**
 * Output JSON-LD schema tags.
 *
 * @return void
 */
function echorouk_output_json_ld_schemas() {
	if ( echorouk_seo_plugin_active() ) {
		return;
	}

	$schemas = array();

	if ( is_front_page() || echorouk_is_about_page() ) {
		$schemas[] = echorouk_get_organization_schema();
	}

	if ( echorouk_is_news_article_view() ) {
		$schemas[] = echorouk_get_organization_schema();
	}

	$breadcrumbs = echorouk_get_breadcrumb_items();
	if ( count( $breadcrumbs ) > 1 ) {
		$breadcrumb_items = array();
		$position         = 1;
		$canonical        = echorouk_get_canonical_url();

		foreach ( $breadcrumbs as $crumb ) {
			$name = isset( $crumb['name'] ) ? trim( (string) $crumb['name'] ) : '';
			if ( '' === $name ) {
				continue;
			}

			$item_url = isset( $crumb['url'] ) ? (string) $crumb['url'] : '';
			if ( '' === $item_url && $canonical ) {
				$item_url = $canonical;
			}

			$breadcrumb_items[] = array(
				'@type'    => 'ListItem',
				'position' => $position,
				'name'     => $name,
				'item'     => esc_url_raw( $item_url ),
			);
			++$position;
		}

		if ( ! empty( $breadcrumb_items ) ) {
			$schemas[] = array(
				'@context'        => 'https://schema.org',
				'@type'           => 'BreadcrumbList',
				'itemListElement' => $breadcrumb_items,
			);
		}
	}

	if ( echorouk_is_news_article_view() ) {
		$post_id      = echorouk_get_current_post_id();
		$author       = echorouk_get_article_author_schema( $post_id );
		$image_url    = get_the_post_thumbnail_url( $post_id, 'full' );
		$category     = echorouk_get_primary_category( $post_id );
		$tags         = wp_get_post_tags( $post_id, array( 'fields' => 'names' ) );
		$description  = echorouk_get_meta_description();
		$article_data = array(
			'@context'          => 'https://schema.org',
			'@type'             => 'NewsArticle',
			'mainEntityOfPage'  => esc_url_raw( get_permalink( $post_id ) ),
			'headline'          => wp_strip_all_tags( get_the_title( $post_id ) ),
			'datePublished'     => get_post_time( DATE_W3C, true, $post_id ),
			'dateModified'      => get_post_modified_time( DATE_W3C, true, $post_id ),
			'isAccessibleForFree' => true,
			'inLanguage'        => get_bloginfo( 'language' ),
			'publisher'         => array(
				'@id' => trailingslashit( home_url( '/' ) ) . '#organization',
			),
		);

		if ( ! empty( $author ) ) {
			$article_data['author'] = $author;
		}

		if ( $description ) {
			$article_data['description'] = $description;
		}

		if ( $image_url ) {
			$article_data['image'] = array( esc_url_raw( $image_url ) );
		}

		if ( $category instanceof WP_Term ) {
			$article_data['articleSection'] = $category->name;
		}

		if ( is_array( $tags ) && ! empty( $tags ) ) {
			$article_data['keywords'] = implode( ', ', array_map( 'sanitize_text_field', $tags ) );
		}

		$schemas[] = $article_data;
	}

	if ( is_category() ) {
		$post_ids = array();
		if ( ! empty( $GLOBALS['echorouk_listing_schema_post_ids'] ) && is_array( $GLOBALS['echorouk_listing_schema_post_ids'] ) ) {
			$post_ids = array_map( 'absint', $GLOBALS['echorouk_listing_schema_post_ids'] );
		} else {
			global $wp_query;
			if ( $wp_query instanceof WP_Query && is_array( $wp_query->posts ) ) {
				$post_ids = array_values(
					array_filter(
						array_map(
							static function ( $post ) {
								return $post instanceof WP_Post ? (int) $post->ID : 0;
							},
							$wp_query->posts
						)
					)
				);
			}
		}

		if ( ! empty( $post_ids ) ) {
			$elements = array();
			foreach ( array_values( $post_ids ) as $index => $post_id ) {
				$permalink = get_permalink( $post_id );
				if ( ! $permalink ) {
					continue;
				}

				$elements[] = array(
					'@type'    => 'ListItem',
					'position' => (int) $index + 1,
					'url'      => esc_url_raw( $permalink ),
					'name'     => wp_strip_all_tags( get_the_title( $post_id ) ),
				);
			}

			if ( ! empty( $elements ) ) {
				$schemas[] = array(
					'@context'        => 'https://schema.org',
					'@type'           => 'ItemList',
					'name'            => wp_strip_all_tags( single_cat_title( '', false ) ),
					'numberOfItems'   => count( $elements ),
					'itemListElement' => $elements,
				);
			}
		}
	}

	if ( is_author() ) {
		$author_schema = echorouk_get_author_profile_schema();
		if ( ! empty( $author_schema ) ) {
			$schemas[] = $author_schema;
		}
	}

	foreach ( $schemas as $schema ) {
		if ( empty( $schema ) || ! is_array( $schema ) ) {
			continue;
		}

		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}
}
add_action( 'wp_head', 'echorouk_output_json_ld_schemas', 40 );

/**
 * Ensure attachment images always have meaningful alt text.
 *
 * @param array<string,string> $attr Image attributes.
 * @param WP_Post              $attachment Attachment object.
 * @return array<string,string>
 */
function echorouk_attachment_alt_fallback( $attr, $attachment ) {
	if ( ! ( $attachment instanceof WP_Post ) ) {
		return $attr;
	}

	$existing_alt = isset( $attr['alt'] ) ? trim( (string) $attr['alt'] ) : '';
	if ( '' !== $existing_alt ) {
		return $attr;
	}

	$alt = trim( (string) get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ) );
	if ( '' === $alt ) {
		$alt = trim( wp_strip_all_tags( (string) $attachment->post_excerpt ) );
	}

	if ( '' === $alt ) {
		$alt = trim( wp_strip_all_tags( (string) $attachment->post_title ) );
	}

	if ( '' === $alt && ! empty( $attachment->post_parent ) ) {
		$alt = trim( wp_strip_all_tags( get_the_title( (int) $attachment->post_parent ) ) );
	}

	if ( '' === $alt ) {
		$alt = get_bloginfo( 'name' );
	}

	$attr['alt'] = $alt;

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'echorouk_attachment_alt_fallback', 30, 2 );

/**
 * Enable WebP uploads in all environments.
 *
 * @param array<string,string> $mimes Allowed mime types.
 * @return array<string,string>
 */
function echorouk_allow_webp_uploads( $mimes ) {
	$mimes['webp'] = 'image/webp';

	return $mimes;
}
add_filter( 'upload_mimes', 'echorouk_allow_webp_uploads' );

/**
 * Return post types that should expose SEO controls.
 *
 * @return array<int,string>
 */
function echorouk_seo_meta_box_post_types() {
	$post_types = get_post_types(
		array(
			'public'  => true,
			'show_ui' => true,
		),
		'names'
	);

	$excluded = array( 'attachment', 'revision', 'nav_menu_item' );
	return array_values( array_diff( (array) $post_types, $excluded ) );
}

/**
 * Register SEO meta box for public content types.
 *
 * @return void
 */
function echorouk_register_seo_meta_box() {
	foreach ( echorouk_seo_meta_box_post_types() as $post_type ) {
		add_meta_box(
			'echorouk_seo_settings',
			esc_html__( 'SEO Settings', 'echoroukonline' ),
			'echorouk_render_seo_meta_box',
			$post_type,
			'side',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'echorouk_register_seo_meta_box' );

/**
 * Render SEO meta box fields.
 *
 * @param WP_Post $post Current post.
 * @return void
 */
function echorouk_render_seo_meta_box( $post ) {
	if ( ! ( $post instanceof WP_Post ) ) {
		return;
	}

	wp_nonce_field( 'echorouk_save_seo_meta_box', 'echorouk_seo_meta_box_nonce' );

	$meta_title       = (string) get_post_meta( $post->ID, 'echorouk_meta_title', true );
	$meta_description = (string) get_post_meta( $post->ID, 'echorouk_meta_description', true );
	$canonical_url    = (string) get_post_meta( $post->ID, 'echorouk_canonical_url', true );
	$social_image_id  = (int) get_post_meta( $post->ID, 'echorouk_social_image_id', true );
	$robots_noindex   = (bool) get_post_meta( $post->ID, 'echorouk_robots_noindex', true );
	$robots_nofollow  = (bool) get_post_meta( $post->ID, 'echorouk_robots_nofollow', true );
	?>
	<p>
		<label for="echorouk_meta_title"><strong><?php esc_html_e( 'Meta Title', 'echoroukonline' ); ?></strong></label>
		<input type="text" id="echorouk_meta_title" name="echorouk_seo_meta[echorouk_meta_title]" class="widefat" value="<?php echo esc_attr( $meta_title ); ?>">
	</p>
	<p>
		<label for="echorouk_meta_description"><strong><?php esc_html_e( 'Meta Description', 'echoroukonline' ); ?></strong></label>
		<textarea id="echorouk_meta_description" name="echorouk_seo_meta[echorouk_meta_description]" class="widefat" rows="4"><?php echo esc_textarea( $meta_description ); ?></textarea>
	</p>
	<p>
		<label for="echorouk_canonical_url"><strong><?php esc_html_e( 'Canonical URL', 'echoroukonline' ); ?></strong></label>
		<input type="url" id="echorouk_canonical_url" name="echorouk_seo_meta[echorouk_canonical_url]" class="widefat" value="<?php echo esc_attr( $canonical_url ); ?>">
	</p>
	<p>
		<label for="echorouk_social_image_id"><strong><?php esc_html_e( 'Social Image Attachment ID', 'echoroukonline' ); ?></strong></label>
		<input type="number" min="0" id="echorouk_social_image_id" name="echorouk_seo_meta[echorouk_social_image_id]" class="widefat" value="<?php echo esc_attr( (string) $social_image_id ); ?>">
	</p>
	<p>
		<label>
			<input type="checkbox" name="echorouk_seo_meta[echorouk_robots_noindex]" value="1" <?php checked( $robots_noindex ); ?>>
			<?php esc_html_e( 'Noindex this content', 'echoroukonline' ); ?>
		</label>
		<br>
		<label>
			<input type="checkbox" name="echorouk_seo_meta[echorouk_robots_nofollow]" value="1" <?php checked( $robots_nofollow ); ?>>
			<?php esc_html_e( 'Nofollow links on this content', 'echoroukonline' ); ?>
		</label>
	</p>
	<?php
}

/**
 * Save SEO meta box fields.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function echorouk_save_seo_meta_box( $post_id ) {
	if ( ! in_array( get_post_type( $post_id ), echorouk_seo_meta_box_post_types(), true ) ) {
		return;
	}

	if ( ! isset( $_POST['echorouk_seo_meta_box_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['echorouk_seo_meta_box_nonce'] ) ), 'echorouk_save_seo_meta_box' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! echorouk_can_edit_post( $post_id ) ) {
		return;
	}

	$posted = isset( $_POST['echorouk_seo_meta'] ) && is_array( $_POST['echorouk_seo_meta'] ) ? wp_unslash( $_POST['echorouk_seo_meta'] ) : array();

	$text_fields = array(
		'echorouk_meta_title'       => 'sanitize_text_field',
		'echorouk_meta_description' => 'sanitize_textarea_field',
		'echorouk_canonical_url'    => 'esc_url_raw',
	);

	foreach ( $text_fields as $key => $sanitize_callback ) {
		if ( ! isset( $posted[ $key ] ) ) {
			continue;
		}

		$value = call_user_func( $sanitize_callback, $posted[ $key ] );
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}

	$social_image_id = isset( $posted['echorouk_social_image_id'] ) ? absint( $posted['echorouk_social_image_id'] ) : 0;
	if ( $social_image_id > 0 ) {
		update_post_meta( $post_id, 'echorouk_social_image_id', $social_image_id );
	} else {
		delete_post_meta( $post_id, 'echorouk_social_image_id' );
	}

	update_post_meta( $post_id, 'echorouk_robots_noindex', ! empty( $posted['echorouk_robots_noindex'] ) ? 1 : 0 );
	update_post_meta( $post_id, 'echorouk_robots_nofollow', ! empty( $posted['echorouk_robots_nofollow'] ) ? 1 : 0 );
}
add_action( 'save_post', 'echorouk_save_seo_meta_box' );

/**
 * Render category SEO fields in add form.
 *
 * @return void
 */
function echorouk_category_seo_add_fields() {
	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}

	wp_nonce_field( 'echorouk_save_category_seo_meta', 'echorouk_category_seo_meta_nonce' );
	?>
	<div class="form-field term-seo-meta-title-wrap">
		<label for="echorouk_meta_title"><?php esc_html_e( 'SEO Meta Title', 'echoroukonline' ); ?></label>
		<input type="text" name="echorouk_seo_meta[echorouk_meta_title]" id="echorouk_meta_title" value="">
	</div>
	<div class="form-field term-seo-meta-description-wrap">
		<label for="echorouk_meta_description"><?php esc_html_e( 'SEO Meta Description', 'echoroukonline' ); ?></label>
		<textarea name="echorouk_seo_meta[echorouk_meta_description]" id="echorouk_meta_description" rows="5"></textarea>
	</div>
	<div class="form-field term-seo-canonical-wrap">
		<label for="echorouk_canonical_url"><?php esc_html_e( 'Canonical URL', 'echoroukonline' ); ?></label>
		<input type="url" name="echorouk_seo_meta[echorouk_canonical_url]" id="echorouk_canonical_url" value="">
	</div>
	<div class="form-field term-seo-image-wrap">
		<label for="echorouk_social_image_id"><?php esc_html_e( 'Social Image Attachment ID', 'echoroukonline' ); ?></label>
		<input type="number" min="0" name="echorouk_seo_meta[echorouk_social_image_id]" id="echorouk_social_image_id" value="0">
	</div>
	<div class="form-field term-seo-robots-wrap">
		<label>
			<input type="checkbox" name="echorouk_seo_meta[echorouk_robots_noindex]" value="1">
			<?php esc_html_e( 'Noindex this category', 'echoroukonline' ); ?>
		</label>
		<label>
			<input type="checkbox" name="echorouk_seo_meta[echorouk_robots_nofollow]" value="1">
			<?php esc_html_e( 'Nofollow links on this category', 'echoroukonline' ); ?>
		</label>
	</div>
	<?php
}
add_action( 'category_add_form_fields', 'echorouk_category_seo_add_fields' );

/**
 * Render category SEO fields in edit form.
 *
 * @param WP_Term $term Current term.
 * @return void
 */
function echorouk_category_seo_edit_fields( $term ) {
	if ( ! ( $term instanceof WP_Term ) || ! current_user_can( 'manage_categories' ) ) {
		return;
	}

	wp_nonce_field( 'echorouk_save_category_seo_meta', 'echorouk_category_seo_meta_nonce' );

	$meta_title       = (string) get_term_meta( $term->term_id, 'echorouk_meta_title', true );
	$meta_description = (string) get_term_meta( $term->term_id, 'echorouk_meta_description', true );
	$canonical_url    = (string) get_term_meta( $term->term_id, 'echorouk_canonical_url', true );
	$social_image_id  = (int) get_term_meta( $term->term_id, 'echorouk_social_image_id', true );
	$robots_noindex   = (bool) get_term_meta( $term->term_id, 'echorouk_robots_noindex', true );
	$robots_nofollow  = (bool) get_term_meta( $term->term_id, 'echorouk_robots_nofollow', true );
	?>
	<tr class="form-field term-seo-meta-title-wrap">
		<th scope="row"><label for="echorouk_meta_title"><?php esc_html_e( 'SEO Meta Title', 'echoroukonline' ); ?></label></th>
		<td><input type="text" name="echorouk_seo_meta[echorouk_meta_title]" id="echorouk_meta_title" value="<?php echo esc_attr( $meta_title ); ?>" class="regular-text"></td>
	</tr>
	<tr class="form-field term-seo-meta-description-wrap">
		<th scope="row"><label for="echorouk_meta_description"><?php esc_html_e( 'SEO Meta Description', 'echoroukonline' ); ?></label></th>
		<td><textarea name="echorouk_seo_meta[echorouk_meta_description]" id="echorouk_meta_description" rows="5" class="large-text"><?php echo esc_textarea( $meta_description ); ?></textarea></td>
	</tr>
	<tr class="form-field term-seo-canonical-wrap">
		<th scope="row"><label for="echorouk_canonical_url"><?php esc_html_e( 'Canonical URL', 'echoroukonline' ); ?></label></th>
		<td><input type="url" name="echorouk_seo_meta[echorouk_canonical_url]" id="echorouk_canonical_url" value="<?php echo esc_attr( $canonical_url ); ?>" class="regular-text"></td>
	</tr>
	<tr class="form-field term-seo-social-image-wrap">
		<th scope="row"><label for="echorouk_social_image_id"><?php esc_html_e( 'Social Image Attachment ID', 'echoroukonline' ); ?></label></th>
		<td><input type="number" min="0" name="echorouk_seo_meta[echorouk_social_image_id]" id="echorouk_social_image_id" value="<?php echo esc_attr( (string) $social_image_id ); ?>" class="small-text"></td>
	</tr>
	<tr class="form-field term-seo-robots-wrap">
		<th scope="row"><?php esc_html_e( 'Robots Meta', 'echoroukonline' ); ?></th>
		<td>
			<label>
				<input type="checkbox" name="echorouk_seo_meta[echorouk_robots_noindex]" value="1" <?php checked( $robots_noindex ); ?>>
				<?php esc_html_e( 'Noindex this category', 'echoroukonline' ); ?>
			</label>
			<br>
			<label>
				<input type="checkbox" name="echorouk_seo_meta[echorouk_robots_nofollow]" value="1" <?php checked( $robots_nofollow ); ?>>
				<?php esc_html_e( 'Nofollow links on this category', 'echoroukonline' ); ?>
			</label>
		</td>
	</tr>
	<?php
}
add_action( 'category_edit_form_fields', 'echorouk_category_seo_edit_fields' );

/**
 * Save SEO fields for category terms.
 *
 * @param int $term_id Term ID.
 * @return void
 */
function echorouk_save_category_seo_meta( $term_id ) {
	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}

	if ( ! isset( $_POST['echorouk_category_seo_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['echorouk_category_seo_meta_nonce'] ) ), 'echorouk_save_category_seo_meta' ) ) {
		return;
	}

	$posted = isset( $_POST['echorouk_seo_meta'] ) && is_array( $_POST['echorouk_seo_meta'] ) ? wp_unslash( $_POST['echorouk_seo_meta'] ) : array();

	$text_keys = array(
		'echorouk_meta_title'       => 'sanitize_text_field',
		'echorouk_meta_description' => 'sanitize_textarea_field',
		'echorouk_canonical_url'    => 'esc_url_raw',
		'echorouk_social_image_id'  => 'absint',
	);

	foreach ( $text_keys as $key => $callback ) {
		$value = isset( $posted[ $key ] ) ? call_user_func( $callback, $posted[ $key ] ) : '';
		if ( '' === (string) $value || 0 === (int) $value ) {
			delete_term_meta( $term_id, $key );
		} else {
			update_term_meta( $term_id, $key, $value );
		}
	}

	$robots_keys = array( 'echorouk_robots_noindex', 'echorouk_robots_nofollow' );
	foreach ( $robots_keys as $key ) {
		update_term_meta( $term_id, $key, ! empty( $posted[ $key ] ) ? 1 : 0 );
	}
}
add_action( 'created_category', 'echorouk_save_category_seo_meta' );
add_action( 'edited_category', 'echorouk_save_category_seo_meta' );
