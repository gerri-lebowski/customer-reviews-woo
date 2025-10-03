<?php 

if (!defined('ABSPATH')) { exit; }

class CPR_Admin {

	public static function init() {
		add_action('admin_menu', array(__CLASS__, 'register_admin_menu'));
		add_action('admin_post_cpr_approve', array(__CLASS__, 'handle_admin_approve'));
	}

	public static function capability() {
		$cap = current_user_can('manage_woocommerce') ? 'manage_woocommerce' : 'manage_options';
		return apply_filters('cpr_admin_capability', $cap);
	}

	public static function register_admin_menu() {
		add_menu_page(
			__('Recensioni Prodotti', 'custom-product-reviews'),
			__('Recensioni', 'custom-product-reviews'),
			self::capability(),
			'cpr-reviews',
			array(__CLASS__, 'render_admin_page'),
			'dashicons-star-half',
			56
		);
	}

	public static function render_admin_page() {
		if (!current_user_can(self::capability())) {
			wp_die(__('Non hai i permessi per visualizzare questa pagina.', 'custom-product-reviews'));
		}

		global $wpdb;
		$table = $wpdb->prefix . 'cpr_reviews';
		$reviews = $wpdb->get_results("SELECT id, product_id, name, email, rating, review_text, status, created_at FROM {$table} ORDER BY created_at DESC LIMIT 200");

		echo '<div class="wrap"><h1>' . esc_html__('Recensioni Prodotti', 'custom-product-reviews') . '</h1>';

		if (isset($_GET['cpr_admin_success'])) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html(rawurldecode((string) $_GET['cpr_admin_success'])) . '</p></div>';
		}
		if (isset($_GET['cpr_admin_error'])) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html(rawurldecode((string) $_GET['cpr_admin_error'])) . '</p></div>';
		}

		echo '<table class="widefat fixed striped"><thead><tr>';
		echo '<th>' . esc_html__('ID', 'custom-product-reviews') . '</th>';
		echo '<th>' . esc_html__('Prodotto', 'custom-product-reviews') . '</th>';
		echo '<th>' . esc_html__('Nome', 'custom-product-reviews') . '</th>';
		echo '<th>' . esc_html__('Email', 'custom-product-reviews') . '</th>';
		echo '<th>' . esc_html__('Rating', 'custom-product-reviews') . '</th>';
		echo '<th>' . esc_html__('Testo', 'custom-product-reviews') . '</th>';
		echo '<th>' . esc_html__('Stato', 'custom-product-reviews') . '</th>';
		echo '<th>' . esc_html__('Data', 'custom-product-reviews') . '</th>';
		echo '<th>' . esc_html__('Azioni', 'custom-product-reviews') . '</th>';
		echo '</tr></thead><tbody>';

		if ($reviews) {
			foreach ($reviews as $r) {
				$approve_url = wp_nonce_url(admin_url('admin-post.php?action=cpr_approve&review_id=' . (int) $r->id), 'cpr_approve_' . (int) $r->id);
				echo '<tr>';
				echo '<td>' . (int) $r->id . '</td>';
				echo '<td>#' . (int) $r->product_id . '</td>';
				echo '<td>' . esc_html($r->name) . '</td>';
				echo '<td>' . esc_html($r->email) . '</td>';
				echo '<td>' . (int) $r->rating . '</td>';
				echo '<td>' . esc_html($r->review_text) . '</td>';
				echo '<td>' . esc_html($r->status) . '</td>';
				echo '<td>' . esc_html($r->created_at) . '</td>';
				echo '<td>';
				if ($r->status !== 'approved') {
					echo '<a class="button button-primary" href="' . esc_url($approve_url) . '">' . esc_html__('Approva', 'custom-product-reviews') . '</a>';
				} else {
					echo '<span class="dashicons dashicons-yes"></span>';
				}
				echo '</td>';
				echo '</tr>';
			}
		} else {
			echo '<tr><td colspan="9">' . esc_html__('Nessuna recensione trovata.', 'custom-product-reviews') . '</td></tr>';
		}

		echo '</tbody></table></div>';
	}

	public static function handle_admin_approve() {
		if (!current_user_can(self::capability())) {
			wp_die(__('Permesso negato', 'custom-product-reviews'));
		}
		$review_id = isset($_GET['review_id']) ? (int) $_GET['review_id'] : 0;
		if ($review_id <= 0) {
			wp_safe_redirect(add_query_arg('cpr_admin_error', rawurlencode(__('ID non valido', 'custom-product-reviews')), admin_url('admin.php?page=cpr-reviews')));
			exit;
		}
		if (!wp_verify_nonce(isset($_GET['_wpnonce']) ? (string) $_GET['_wpnonce'] : '', 'cpr_approve_' . $review_id)) {
			wp_safe_redirect(add_query_arg('cpr_admin_error', rawurlencode(__('Nonce non valido', 'custom-product-reviews')), admin_url('admin.php?page=cpr-reviews')));
			exit;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'cpr_reviews';
		$ok = $wpdb->update($table, array('status' => 'approved'), array('id' => $review_id), array('%s'), array('%d'));
		if ($ok === false) {
			wp_safe_redirect(add_query_arg('cpr_admin_error', rawurlencode(__('Aggiornamento fallito', 'custom-product-reviews')), admin_url('admin.php?page=cpr-reviews')));
			exit;
		}
		wp_safe_redirect(add_query_arg('cpr_admin_success', rawurlencode(__('Recensione approvata', 'custom-product-reviews')), admin_url('admin.php?page=cpr-reviews')));
		exit;
	}
}


