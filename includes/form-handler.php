<?php 

if (!defined('ABSPATH')) {
    exit;
}

class CPR_Form_Handler {

    public static function init() {
        // Handle POST synchronously on template load
        add_action('template_redirect', array(__CLASS__, 'maybe_handle_submission'));
    }

    public static function maybe_handle_submission() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $action = isset($_POST['cpr_action']) ? sanitize_text_field((string) $_POST['cpr_action']) : '';
        if ($action !== 'submit_review') {
            return;
        }

        // Verify nonce
        if (!isset($_POST['cpr_nonce']) || !wp_verify_nonce((string) $_POST['cpr_nonce'], 'cpr_submit_review')) {
            self::redirect_with_message('cpr_error', __('Sicurezza non valida. Riprova.', 'custom-product-reviews'));
        }

        $product_id = isset($_POST['cpr_product_id']) ? (int) $_POST['cpr_product_id'] : 0;
        if ($product_id <= 0) {
            self::redirect_with_message('cpr_error', __('Prodotto non valido.', 'custom-product-reviews'));
        }

        // Sanitize inputs
        $name = isset($_POST['cpr_name']) ? sanitize_text_field((string) $_POST['cpr_name']) : '';
        $email = isset($_POST['cpr_email']) ? sanitize_email((string) $_POST['cpr_email']) : '';
        $rating = isset($_POST['cpr_rating']) ? (int) $_POST['cpr_rating'] : 0;
        $review_text = isset($_POST['cpr_review']) ? wp_kses_post((string) $_POST['cpr_review']) : '';
        $privacy_consent = isset($_POST['cpr_privacy']) && (string) $_POST['cpr_privacy'] === '1' ? 1 : 0;
        $marketing_consent = isset($_POST['cpr_marketing']) && (string) $_POST['cpr_marketing'] === '1' ? 1 : 0;

        // Validate
        $errors = array();
        if ($name === '' || mb_strlen($name) > 100) {
            $errors[] = __('Nome non valido.', 'custom-product-reviews');
        }
        if ($email === '' || !is_email($email)) {
            $errors[] = __('Email non valida.', 'custom-product-reviews');
        }
        if ($rating < 1 || $rating > 5) {
            $errors[] = __('Valutazione non valida.', 'custom-product-reviews');
        }
        if ($review_text === '') {
            $errors[] = __('Il commento è obbligatorio.', 'custom-product-reviews');
        }
        if ($privacy_consent !== 1) {
            $errors[] = __('Devi accettare l\'informativa sulla privacy.', 'custom-product-reviews');
        }

        if (!empty($errors)) {
            self::redirect_with_message('cpr_error', implode(' ', $errors));
        }

        // Save
        if (!class_exists('CPR_Database')) {
            require_once CPR_PLUGIN_DIR . 'includes/database.php';
        }

        $ok = CPR_Database::insert_review($product_id, $name, $email, $rating, $review_text, $privacy_consent, $marketing_consent);
        if (!$ok) {
            self::redirect_with_message('cpr_error', __('Si è verificato un errore durante il salvataggio.', 'custom-product-reviews'));
        }

        self::redirect_with_message('cpr_success', __('Recensione inviata! In attesa di approvazione.', 'custom-product-reviews'));
    }

    private static function redirect_with_message($key, $message) {
        $url = add_query_arg(array(
            $key => rawurlencode($message)
        ), remove_query_arg(array('cpr_error', 'cpr_success')));

        wp_safe_redirect($url);
        exit;
    }
}

// bootstrap
add_action('init', array('CPR_Form_Handler', 'init'));



