<?php
/**
 * Plugin Name: CreativeLink AI Search
 * Plugin URI: https://github.com/jfx1026/CreativeLink-AI-Search
 * Description: AI-powered chat widget to search through the Weekly Creative Links archive.
 * Version: 1.0.0
 * Author: John Freeborn
 * Author URI: https://johnfreeborn.com
 * License: MIT
 * Text Domain: creativelink-ai-search
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CreativeLink_AI_Search {

    /**
     * Plugin version
     */
    const VERSION = '1.0.0';

    /**
     * Worker URL for the AI backend
     */
    private $worker_url = 'https://design-links-chat.jfx1026.workers.dev';

    /**
     * Initialize the plugin
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'render_widget'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Enqueue plugin scripts and styles
     */
    public function enqueue_scripts() {
        // Only load on frontend
        if (is_admin()) {
            return;
        }

        wp_enqueue_script(
            'creativelink-ai-search',
            plugin_dir_url(__FILE__) . 'js/chat-widget.js',
            array(),
            self::VERSION,
            true
        );

        wp_enqueue_style(
            'creativelink-ai-search',
            plugin_dir_url(__FILE__) . 'css/chat-widget.css',
            array(),
            self::VERSION
        );

        // Pass settings to JavaScript
        wp_localize_script('creativelink-ai-search', 'creativeLinkAI', array(
            'workerUrl' => $this->get_worker_url(),
            'buttonColor' => $this->get_option('button_color', '#2563EB'),
        ));
    }

    /**
     * Render the widget HTML in the footer
     */
    public function render_widget() {
        if (is_admin()) {
            return;
        }
        ?>
        <div id="cl-chat-widget">
            <button class="cl-chat-trigger" id="cl-chat-trigger" aria-label="Open AI Search">
                <svg class="cl-icon-chat" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <svg class="cl-icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>

            <div class="cl-chat-panel" id="cl-chat-panel">
                <div class="cl-chat-header">
                    <h3>AI Search</h3>
                    <button class="cl-chat-close" id="cl-chat-close" aria-label="Close">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                <div class="cl-chat-messages" id="cl-chat-messages">
                    <div class="cl-chat-welcome" id="cl-chat-welcome">
                        <div class="cl-chat-welcome-icon">
                            <svg viewBox="0 0 512 512" width="48" height="48">
                                <rect x="96" y="96" width="320" height="320" rx="32" fill="#cbd5e1"/>
                                <rect x="144" y="144" width="224" height="224" rx="16" fill="#2563EB"/>
                                <text x="256" y="290" text-anchor="middle" font-family="Arial, sans-serif" font-size="120" font-weight="bold" fill="white">AI</text>
                                <rect x="136" y="32" width="24" height="64" rx="4" fill="#64748b"/>
                                <rect x="216" y="32" width="24" height="64" rx="4" fill="#64748b"/>
                                <rect x="272" y="32" width="24" height="64" rx="4" fill="#64748b"/>
                                <rect x="352" y="32" width="24" height="64" rx="4" fill="#64748b"/>
                                <rect x="136" y="416" width="24" height="64" rx="4" fill="#64748b"/>
                                <rect x="216" y="416" width="24" height="64" rx="4" fill="#64748b"/>
                                <rect x="272" y="416" width="24" height="64" rx="4" fill="#64748b"/>
                                <rect x="352" y="416" width="24" height="64" rx="4" fill="#64748b"/>
                                <rect x="32" y="136" width="64" height="24" rx="4" fill="#64748b"/>
                                <rect x="32" y="216" width="64" height="24" rx="4" fill="#64748b"/>
                                <rect x="32" y="272" width="64" height="24" rx="4" fill="#64748b"/>
                                <rect x="32" y="352" width="64" height="24" rx="4" fill="#64748b"/>
                                <rect x="416" y="136" width="64" height="24" rx="4" fill="#64748b"/>
                                <rect x="416" y="216" width="64" height="24" rx="4" fill="#64748b"/>
                                <rect x="416" y="272" width="64" height="24" rx="4" fill="#64748b"/>
                                <rect x="416" y="352" width="64" height="24" rx="4" fill="#64748b"/>
                            </svg>
                        </div>
                        <p>Use AI chat to search through over 3500 links from the Weekly Creative Links archive.</p>
                        <div class="cl-chat-suggestions">
                            <button class="cl-chat-suggestion">Typography resources</button>
                            <button class="cl-chat-suggestion">Design systems</button>
                            <button class="cl-chat-suggestion">Pain Points</button>
                        </div>
                    </div>
                </div>
                <div class="cl-chat-input-area">
                    <form class="cl-chat-input-form" id="cl-chat-form">
                        <textarea class="cl-chat-input" id="cl-chat-input" placeholder="Ask about design resources..." rows="1"></textarea>
                        <button type="submit" class="cl-chat-send" id="cl-chat-send" aria-label="Send">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            'CreativeLink AI Search',
            'CreativeLink AI',
            'manage_options',
            'creativelink-ai-search',
            array($this, 'render_admin_page')
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('creativelink_ai_settings', 'creativelink_ai_options');

        add_settings_section(
            'creativelink_ai_main',
            'Widget Settings',
            null,
            'creativelink-ai-search'
        );

        add_settings_field(
            'button_color',
            'Button Color',
            array($this, 'render_color_field'),
            'creativelink-ai-search',
            'creativelink_ai_main'
        );
    }

    /**
     * Render color picker field
     */
    public function render_color_field() {
        $color = $this->get_option('button_color', '#2563EB');
        echo '<input type="color" name="creativelink_ai_options[button_color]" value="' . esc_attr($color) . '">';
    }

    /**
     * Render admin settings page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1>CreativeLink AI Search Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('creativelink_ai_settings');
                do_settings_sections('creativelink-ai-search');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Get plugin option
     */
    private function get_option($key, $default = '') {
        $options = get_option('creativelink_ai_options', array());
        return isset($options[$key]) ? $options[$key] : $default;
    }

    /**
     * Get worker URL
     */
    private function get_worker_url() {
        return $this->worker_url;
    }
}

// Initialize the plugin
new CreativeLink_AI_Search();
