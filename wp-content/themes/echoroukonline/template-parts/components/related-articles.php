<?php

/**
 * Related articles.
 *
 * @package EchouroukOnline
 */

defined('ABSPATH') || exit;

$posts = echorouk_get_related_posts(get_the_ID(), 3);

if (empty($posts)) {
	return;
}
?>
<section class="section-block related-articles">
	<h2 class="section-title"><?php esc_html_e('Related articles', 'echoroukonline'); ?></h2>
	<div class="archive-grid">
		<?php foreach ($posts as $post) : ?>
			<?php echorouk_news_card($post->ID); ?>
		<?php endforeach; ?>
	</div>
</section>