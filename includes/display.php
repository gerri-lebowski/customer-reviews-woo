<?php 

class CPR_Display {

    public static function render($product_id) {
        // Recupera e renderizza le recensioni approvate per il prodotto
        global $wpdb;

        $table_name = $wpdb->prefix . 'cpr_reviews';
        $reviews = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT name, rating, review_text, created_at FROM {$table_name} WHERE product_id = %d AND status = %s ORDER BY created_at DESC",
                (int) $product_id,
                'approved'
            )
        );

        ob_start();

		// Render success/error messages from query string
		$success_msg = isset($_GET['cpr_success']) ? (string) $_GET['cpr_success'] : '';
		$error_msg = isset($_GET['cpr_error']) ? (string) $_GET['cpr_error'] : '';
		if ($success_msg !== '') {
			echo '<div class="cpr-alert cpr-alert-success">' . self::h(rawurldecode($success_msg)) . '</div>';
		}
		if ($error_msg !== '') {
			echo '<div class="cpr-alert cpr-alert-error">' . self::h(rawurldecode($error_msg)) . '</div>';
		}

		// Form di inserimento recensione (in accordion)
		echo '<div class="cpr-review-form-wrapper">';
		echo '<button type="button" class="cpr-accordion-toggle" aria-expanded="false" aria-controls="cpr-accordion-content">' . self::th('Clicca per lasciare la tua recensione', 'custom-product-reviews') . '</button>';
		echo '<div id="cpr-accordion-content" class="cpr-accordion-content" hidden>';
		echo '<h3 class="cpr-form-title">' . self::th('Lascia una recensione', 'custom-product-reviews') . '</h3>';
		echo '<form class="cpr-review-form" method="post" novalidate>'; 
        echo '<div class="cpr-form-row">';
        echo '<label for="cpr_name">' . self::th('Nome', 'custom-product-reviews') . '</label>';
        echo '<input type="text" id="cpr_name" name="cpr_name" required maxlength="100" />';
        echo '</div>';
        echo '<div class="cpr-form-row">';
        echo '<label for="cpr_email">' . self::th('Email', 'custom-product-reviews') . '</label>';
        echo '<input type="email" id="cpr_email" name="cpr_email" required maxlength="100" />';
        echo '</div>';
		echo '<div class="cpr-form-row">';
		echo '<span class="cpr-rating-label">' . self::th('Valutazione', 'custom-product-reviews') . '</span>';
		echo '<div class="cpr-rating-input" role="radiogroup" aria-label="' . self::attr(self::t('Seleziona valutazione', 'custom-product-reviews')) . '">';
		for ($i = 1; $i <= 5; $i++) {
			$aria = sprintf(self::t('%d stella', 'custom-product-reviews'), $i);
			echo '<button type="button" class="cpr-star" data-value="' . (int) $i . '" role="radio" aria-label="' . self::attr($aria) . '" aria-checked="false">☆</button>';
		}
		echo '<input type="hidden" id="cpr_rating" name="cpr_rating" value="" required />';
		echo '</div>';
		echo '</div>';
        echo '<div class="cpr-form-row">';
        echo '<label for="cpr_review">' . self::th('Commento', 'custom-product-reviews') . '</label>';
        echo '<textarea id="cpr_review" name="cpr_review" rows="5" required></textarea>';
        echo '</div>';
		// Consensi privacy e marketing
		echo '<div class="cpr-form-row cpr-consents">';
		echo '<label class="cpr-consent-item">';
		echo '<input type="checkbox" id="cpr_privacy" name="cpr_privacy" value="1" required /> ';
		echo '<span>' . self::th('Accetto l\'informativa sulla privacy', 'custom-product-reviews') . '</span>';
		echo '</label>';
		echo '<label class="cpr-consent-item">';
		echo '<input type="checkbox" id="cpr_marketing" name="cpr_marketing" value="1" /> ';
		echo '<span>' . self::th('Acconsento a ricevere comunicazioni di marketing', 'custom-product-reviews') . '</span>';
		echo '</label>';
		echo '</div>';
        if (function_exists('wp_nonce_field')) {
            wp_nonce_field('cpr_submit_review', 'cpr_nonce');
        }
        echo '<input type="hidden" name="cpr_product_id" value="' . (int) $product_id . '" />';
        echo '<input type="hidden" name="cpr_action" value="submit_review" />';
        echo '<button type="submit" class="cpr-submit">' . self::th('Invia recensione', 'custom-product-reviews') . '</button>';
		echo '</form>';
		echo '</div>'; // end accordion content
		echo '</div>';

        echo '<div class="cpr-reviews">';
        if (empty($reviews)) {
            echo '<p>' . self::th('Nessuna recensione disponibile.', 'custom-product-reviews') . '</p>';
            echo '</div>';
            return ob_get_clean();
        }

        echo '<ul class="cpr-review-list">';
        foreach ($reviews as $review) {
            $name = self::h($review->name);
            $rating = (int) $review->rating;
            $text = self::h($review->review_text);
            $date = self::h(self::formatDate($review->created_at));

            echo '<li class="cpr-review-item">';
            echo '<div class="cpr-review-header">';
            echo '<span class="cpr-review-name">' . $name . '</span>';
            $aria = sprintf(self::t('Valutazione: %d su 5', 'custom-product-reviews'), $rating);
            echo '<span class="cpr-review-rating" aria-label="' . self::attr($aria) . '">';
            echo self::render_stars($rating);
            echo '</span>';
            echo '</div>';
            echo '<div class="cpr-review-text">' . $text . '</div>';
            echo '<div class="cpr-review-date">' . $date . '</div>';
            echo '</li>';
        }
        echo '</ul>';
        echo '</div>';

        return ob_get_clean();
    }

	private static function render_stars($rating) {
		$rating = max(0, min(5, (int) $rating));
		$full = str_repeat('★', $rating);
		$empty = str_repeat('☆', 5 - $rating);
		return '<span class="cpr-stars">' . self::h($full . $empty) . '</span>';
	}

	private static function h($text) {
		if (function_exists('esc_html')) {
			return esc_html($text);
		}
		return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
	}

	private static function attr($text) {
		if (function_exists('esc_attr')) {
			return esc_attr($text);
		}
		return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
	}

	private static function t($text, $domain) {
		if (function_exists('__')) {
			return __($text, $domain);
		}
		return $text;
	}

	private static function th($text, $domain) {
		if (function_exists('esc_html__')) {
			return esc_html__($text, $domain);
		}
		return self::h($text);
	}

	private static function formatDate($mysqlDate) {
		$timestamp = is_numeric($mysqlDate) ? (int) $mysqlDate : strtotime((string) $mysqlDate);
		$format = 'F j, Y';
		if (function_exists('get_option')) {
			$opt = get_option('date_format');
			if (is_string($opt) && $opt !== '') {
				$format = $opt;
			}
		}
		if (function_exists('date_i18n')) {
			return date_i18n($format, $timestamp);
		}
		return date($format, $timestamp);
	}
}