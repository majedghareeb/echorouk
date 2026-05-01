<?php
/**
 * Compact news card wrapper.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

echorouk_news_card( isset( $args['post_id'] ) ? absint( $args['post_id'] ) : get_the_ID(), 'compact' );

