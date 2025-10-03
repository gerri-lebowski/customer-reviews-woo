<?php
/**
 * Plugin Name: Custom Product Reviews
 * Description: Sistema di recensioni personalizzato per WooCommerce - permette recensioni multiple per prodotto
 * Version: 1.0.0
 * Author: Il Tuo Nome
 * Text Domain: custom-product-reviews
 */

// Security path
if (!defined('ABSPATH')) {
    exit;
}

// Constants
define('CPR_VERSION', '1.1.0');
define('CPR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CPR_PLUGIN_URL', plugin_dir_url(__FILE__));
// Enable auto-approve in development by defining CPR_AUTO_APPROVE in wp-config.php or here.
if (!defined('CPR_AUTO_APPROVE')) {
    define('CPR_AUTO_APPROVE', false);
}

// Main Class
class Custom_Product_Reviews {
    
    // Singleton
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Constructor
    private function __construct() {
        // Create db
        register_activation_hook(__FILE__, array($this, 'activate_plugin'));
        
        // Clean when deactivating
        register_deactivation_hook(__FILE__, array($this, 'deactivate_plugin'));
        
        // Dependencies
        $this->load_dependencies();
        
        // Functionality
        $this->init_hooks();
    }
    
    // Upload files
    private function load_dependencies() {
        require_once CPR_PLUGIN_DIR . 'includes/database.php';
        require_once CPR_PLUGIN_DIR . 'includes/form-handler.php';
        require_once CPR_PLUGIN_DIR . 'includes/display.php';
        require_once CPR_PLUGIN_DIR . 'includes/admin.php';
    }
    
    // Wordpress Hooks
    private function init_hooks() {
        // Shortcode
        add_shortcode('custom_reviews', array($this, 'render_shortcode'));
        
        // Styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));

        // Schema upgrades (idempotent)
        add_action('plugins_loaded', function(){
            if (class_exists('CPR_Database')) {
                CPR_Database::maybe_upgrade();
            }
        });

        // Admin (initialized in CPR_Admin)
        if (class_exists('CPR_Admin')) {
            CPR_Admin::init();
        }
    }
    
    // Called when plugin is activated: Database
    public function activate_plugin() {
        // Create table
        CPR_Database::create_table();
        
        // Save plugin version
        update_option('cpr_version', CPR_VERSION);
    }
    
    // Called when plugin is deactivated
    public function deactivate_plugin() {
        // Per ora non facciamo nulla
        // Potremmo aggiungere pulizia cache, ecc.
    }
    
    // Upload styles
    public function enqueue_styles() {
        wp_enqueue_style(
            'custom-product-reviews',
            CPR_PLUGIN_URL . 'assets/style.css',
            array(),
            CPR_VERSION
        );

        // Front-end behavior (stars + accordion)
        wp_enqueue_script(
            'custom-product-reviews-frontend',
            CPR_PLUGIN_URL . 'assets/frontend.js',
            array('jquery'),
            CPR_VERSION,
            true
        );
    }
    
    // Rendering shortcode
    public function render_shortcode($atts) {
        // Id current product
        global $post;
        
        // If we're not on a product page, we show nothing.
        if (!is_singular('product')) {
            return '<p>Questo shortcode funziona solo sulle pagine prodotto.</p>';
        }
        
        $product_id = $post->ID;
        
        // Generate HTML
        return CPR_Display::render($product_id);
    }

}

// Avviamo il plugin
Custom_Product_Reviews::get_instance();
