<?php
/**
 * Manages the push_subscriptions custom DB table.
 */

if (! defined('ABSPATH')) exit;

class Echorouk_Push_Subscription_DB {

    const TABLE_VERSION = 1;
    const OPT_VERSION   = 'echorouk_push_db_version';

    public static function get_table_name(): string {
        global $wpdb;
        return $wpdb->prefix . 'push_subscriptions';
    }

    public static function create_table(): void {
        global $wpdb;

        if ((int) get_option(self::OPT_VERSION) >= self::TABLE_VERSION) {
            return;
        }

        $table      = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            endpoint    TEXT NOT NULL,
            p256dh      VARCHAR(512) NOT NULL,
            auth        VARCHAR(256) NOT NULL,
            browser     VARCHAR(64)  DEFAULT NULL,
            user_agent  VARCHAR(512) DEFAULT NULL,
            created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            active      TINYINT(1)   NOT NULL DEFAULT 1,
            PRIMARY KEY (id),
            UNIQUE KEY endpoint_hash (endpoint(191))
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        update_option(self::OPT_VERSION, self::TABLE_VERSION);
    }

    /**
     * Save or update a subscription (upsert by endpoint).
     */
    public static function save(array $data): bool {
        global $wpdb;

        $table = self::get_table_name();

        $existing = $wpdb->get_var(
            $wpdb->prepare("SELECT id FROM {$table} WHERE endpoint = %s", $data['endpoint'])
        );

        $row = [
            'endpoint'   => $data['endpoint'],
            'p256dh'     => $data['p256dh'] ?? '',
            'auth'       => $data['auth'] ?? '',
            'browser'    => $data['browser'] ?? '',
            'user_agent' => $data['user_agent'] ?? '',
            'active'     => 1,
        ];

        if ($existing) {
            $wpdb->update($table, $row, ['id' => $existing], ['%s','%s','%s','%s','%s','%d'], ['%d']);
        } else {
            $row['created_at'] = current_time('mysql', true);
            $wpdb->insert($table, $row, ['%s','%s','%s','%s','%s','%s','%d']);
        }

        return $wpdb->last_error === '';
    }

    /**
     * Soft-delete a subscription by endpoint.
     */
    public static function delete_by_endpoint(string $endpoint): void {
        global $wpdb;
        $wpdb->update(
            self::get_table_name(),
            ['active' => 0],
            ['endpoint' => $endpoint],
            ['%d'],
            ['%s']
        );
    }

    /**
     * Permanently remove a subscription by endpoint (called on 410 responses).
     */
    public static function remove_by_endpoint(string $endpoint): void {
        global $wpdb;
        $wpdb->delete(self::get_table_name(), ['endpoint' => $endpoint], ['%s']);
    }

    /**
     * Get all active subscriptions.
     *
     * @return array<int, object>
     */
    public static function get_all_active(): array {
        global $wpdb;
        return $wpdb->get_results(
            "SELECT * FROM " . self::get_table_name() . " WHERE active = 1"
        ) ?: [];
    }

    /** Total active subscribers. */
    public static function count_active(): int {
        global $wpdb;
        return (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM " . self::get_table_name() . " WHERE active = 1"
        );
    }

    /** Count active subscribers per browser (grouped). */
    public static function count_by_browser(): array {
        global $wpdb;
        return $wpdb->get_results(
            "SELECT browser, COUNT(*) as total FROM " . self::get_table_name() . " WHERE active = 1 GROUP BY browser ORDER BY total DESC"
        ) ?: [];
    }
}
