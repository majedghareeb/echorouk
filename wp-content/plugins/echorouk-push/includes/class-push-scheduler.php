<?php
/**
 * WP-Cron scheduler for async broadcast of large subscriber lists.
 */

if (! defined('ABSPATH')) exit;

class Echorouk_Push_Scheduler {

    private static $instance = null;
    const HOOK = 'echorouk_push_process_batch';

    public static function instance(): self {
        if (null === self::$instance) self::$instance = new self();
        return self::$instance;
    }

    public function init(): void {
        add_action(self::HOOK, [$this, 'process_batch']);
    }

    /**
     * Schedule an async broadcast. Stores notification data in transient.
     */
    public static function schedule_broadcast(array $notification): void {
        $key = 'ep_batch_' . wp_generate_password(12, false);
        set_transient($key, $notification, 3600);
        wp_schedule_single_event(time() + 5, self::HOOK, [$key]);
    }

    /** Called by WP-Cron to process the batch. */
    public function process_batch(string $transient_key): void {
        $notification = get_transient($transient_key);
        if (! $notification) return;
        delete_transient($transient_key);
        Echorouk_Push_Sender::broadcast($notification);
    }
}
