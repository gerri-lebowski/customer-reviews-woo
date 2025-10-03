<?php 

class CPR_Database {


    public static function create_table() {
        // Create the table with this fields: id, product_id, email, name, rating, review_text, status, created_at (data)
        global $wpdb;

        $table_name = $wpdb->prefix . 'cpr_reviews';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id BIGINT(20) UNSIGNED NOT NULL,
            email VARCHAR(100) NOT NULL,
            name VARCHAR(100) NOT NULL,
            rating TINYINT(3) UNSIGNED NOT NULL,
            review_text TEXT NOT NULL,
            privacy_consent TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            marketing_consent TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY product_id (product_id),
            KEY status (status)
        ) {$charset_collate};";

		// Ensure WordPress context and dbDelta are available
		if (defined('ABSPATH')) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			if (function_exists('dbDelta')) {
				dbDelta($sql);
			} else {
				// Fallback in unlikely case dbDelta is unavailable
				$wpdb->query($sql);
			}
		}

        // Ensure columns exist on update
        self::ensure_columns($table_name);
    }

    private static function ensure_columns($table_name) {
        global $wpdb;
        $existing = $wpdb->get_results("SHOW COLUMNS FROM {$table_name}");
        $column_names = array();
        if (is_array($existing)) {
            foreach ($existing as $col) {
                if (isset($col->Field)) {
                    $column_names[] = (string) $col->Field;
                }
            }
        }
        if (!in_array('privacy_consent', $column_names, true)) {
            $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN privacy_consent TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER review_text");
        }
        if (!in_array('marketing_consent', $column_names, true)) {
            $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN marketing_consent TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER privacy_consent");
        }
    }

    public static function maybe_upgrade() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cpr_reviews';
        self::ensure_columns($table_name);
    }

    public static function insert_review($product_id, $name, $email, $rating, $review_text, $privacy_consent, $marketing_consent) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cpr_reviews';

        $review_status = defined('CPR_AUTO_APPROVE') && CPR_AUTO_APPROVE ? 'approved' : 'pending';
        $data = array(
            'product_id' => (int) $product_id,
            'email' => $email,
            'name' => $name,
            'rating' => (int) $rating,
            'review_text' => $review_text,
            'privacy_consent' => (int) $privacy_consent,
            'marketing_consent' => (int) $marketing_consent,
            'status' => $review_status,
            'created_at' => current_time('mysql')
        );

        $formats = array('%d', '%s', '%s', '%d', '%s', '%d', '%d', '%s', '%s');

        return (bool) $wpdb->insert($table_name, $data, $formats);
    }
}