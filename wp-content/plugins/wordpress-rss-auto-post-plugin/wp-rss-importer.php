<?php
/**
 * Plugin Name: Echorouk RSS to Post Importer
 * Description: Automatically fetches RSS feed from Echorouk Online, creates posts, and downloads images to media library.
 * Version: 1.0
 * Author: AI PHP Developer
 */

if (!defined('ABSPATH')) {
    exit;
}

class Echorouk_RSS_Importer {
    private $rss_url = 'https://www.echoroukonline.com/feed';

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function add_admin_menu() {
        add_menu_page(
            'RSS Importer',
            'RSS Importer',
            'manage_options',
            'echorouk-rss-importer',
            array($this, 'admin_page_contents'),
            'dashicons-rss',
            20
        );
    }

    public function admin_page_contents() {
        ?>
        <div class="wrap">
            <h1>Echorouk RSS Importer</h1>
            <p>Fetch the latest news from Echorouk Online and convert them to WordPress posts.</p>
            
            <form method="post">
                <?php wp_nonce_field('run_echorouk_importer', 'echorouk_nonce'); ?>
                <input type="submit" name="run_importer" class="button button-primary" value="Run Manual Sync Now">
            </form>

            <?php
            if (isset($_POST['run_importer']) && check_admin_referer('run_echorouk_importer', 'echorouk_nonce')) {
                $this->run_import();
            }
            ?>
        </div>
        <?php
    }

    public function run_import() {
        echo '<div class="notice notice-info is-dismissible"><p>Starting import process...</p></div>';

        $rss = fetch_feed($this->rss_url);

        if (is_wp_error($rss)) {
            echo '<div class="notice notice-error"><p>Error fetching RSS: ' . $rss->get_error_message() . '</p></div>';
            return;
        }

        $items = $rss->get_items(0, 10); // Get latest 10 items
        $count = 0;

        foreach ($items as $item) {
            $title = $item->get_title();
            $content = $item->get_content();
            $link = $item->get_permalink();
            $date = $item->get_date('Y-m-d H:i:s');
            $guid = $item->get_id();

            // Check if post already exists
            if ($this->post_exists($guid)) {
                continue;
            }

            $post_id = wp_insert_post(array(
                'post_title'   => $title,
                'post_content' => $content,
                'post_status'  => 'publish',
                'post_author'  => 1,
                'post_date'    => $date,
                'meta_input'   => array(
                    '_echorouk_guid' => $guid,
                    '_source_url'    => $link
                )
            ));

            if ($post_id) {
                $this->handle_featured_image($item, $post_id);
                $count++;
            }
        }

        echo '<div class="notice notice-success"><p>Import completed! Created ' . $count . ' new posts.</p></div>';
    }

    private function post_exists($guid) {
        $args = array(
            'post_type'  => 'post',
            'meta_key'   => '_echorouk_guid',
            'meta_value' => $guid,
            'posts_per_page' => 1
        );
        $posts = get_posts($args);
        return !empty($posts);
    }

    private function handle_featured_image($item, $post_id) {
        // Find image in enclosures or content
        $image_url = '';
        
        // Try enclosures
        $enclosures = $item->get_enclosures();
        if (!empty($enclosures)) {
            foreach ($enclosures as $enclosure) {
                if (strpos($enclosure->get_type(), 'image') !== false) {
                    $image_url = $enclosure->get_link();
                    break;
                }
            }
        }

        // Try to find image in content if not found in enclosure
        if (empty($image_url)) {
            preg_match('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $item->get_content(), $matches);
            if (!empty($matches[1])) {
                $image_url = $matches[1];
            }
        }

        if (!empty($image_url)) {
            $this->upload_image_to_media($image_url, $post_id);
        }
    }

    private function upload_image_to_media($image_url, $post_id) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $desc = "Featured image for post " . $post_id;
        $attachment_id = media_sideload_image($image_url, $post_id, $desc, 'id');

        if (!is_wp_error($attachment_id)) {
            set_post_thumbnail($post_id, $attachment_id);
        }
    }
}

new Echorouk_RSS_Importer();
