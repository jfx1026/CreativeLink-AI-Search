<?php
/**
 * Plugin Name: AI Search
 * Plugin URI: https://github.com/jfx1026/ai-search
 * Description: AI-powered chat widget to search through your site's content using natural language.
 * Version: 1.0.0
 * Author: John Freeborn
 * Author URI: https://johnfreeborn.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ai-search
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
     * Default Worker URL for the AI backend
     */
    const DEFAULT_WORKER_URL = '';

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
        // Only load on frontend when API endpoint is configured
        if (is_admin() || empty($this->get_worker_url())) {
            return;
        }

        wp_enqueue_script(
            'ai-search',
            plugin_dir_url(__FILE__) . 'js/chat-widget.js',
            array(),
            self::VERSION,
            true
        );

        wp_enqueue_style(
            'ai-search',
            plugin_dir_url(__FILE__) . 'css/chat-widget.css',
            array(),
            self::VERSION
        );

        // Pass settings to JavaScript
        wp_localize_script('ai-search', 'creativeLinkAI', array(
            'workerUrl' => $this->get_worker_url(),
            'buttonColor' => $this->get_option('button_color', '#2563EB'),
            'searchContext' => $this->get_search_context(),
            'searchScope' => $this->get_search_scope_label(),
            'placeholderText' => $this->get_option('placeholder_text', 'Ask a question...'),
        ));
    }

    /**
     * Render the widget HTML in the footer
     */
    public function render_widget() {
        // Only render when API endpoint is configured
        if (is_admin() || empty($this->get_worker_url())) {
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
                            <svg viewBox="0 0 200 213.79" width="64" height="68">
                                <defs><style>.st0{fill:#1b1464}.st1{fill:#58595b}.st2{fill:#d1d3d4}.st3{fill:#464547}.st4{fill:#231f20}.st5{fill:#0066b3}.st6{fill:#9095b2}.st7{fill:#080046}.st8{fill:#fff}.st9{fill:#7b7f98}.st10{fill:#b7c1ce}.st11{fill:#393839}.st12{fill:#b1b3b6}.st13{fill:#97999c}.st14{fill:#fff200}.st15{fill:#283895}</style></defs>
                                <rect class="st1" x="34.48" y="144.83" width="6.9" height="6.9"/><rect class="st1" x="34.48" y="151.72" width="6.9" height="6.9"/><rect class="st1" x="34.48" y="158.62" width="6.9" height="6.9"/><rect class="st1" x="34.48" y="165.52" width="6.9" height="6.9"/><rect class="st1" x="34.48" y="172.41" width="6.9" height="6.9"/><rect class="st1" x="34.48" y="179.31" width="6.9" height="6.9"/><rect class="st1" x="34.48" y="186.21" width="6.9" height="6.9"/><rect class="st1" x="34.48" y="193.1" width="6.9" height="6.9"/><rect class="st1" x="34.48" y="200" width="6.9" height="6.9"/><rect class="st1" x="41.38" y="137.93" width="6.9" height="6.9"/><rect class="st3" x="41.38" y="144.83" width="6.9" height="6.9"/><rect class="st3" x="41.38" y="151.72" width="6.9" height="6.9"/><rect class="st3" x="41.38" y="158.62" width="6.9" height="6.9"/><rect class="st3" x="41.38" y="165.52" width="6.9" height="6.9"/><rect class="st3" x="41.38" y="172.41" width="6.9" height="6.9"/><rect class="st3" x="41.38" y="179.31" width="6.9" height="6.9"/><rect class="st3" x="41.38" y="186.21" width="6.9" height="6.9"/><rect class="st3" x="41.38" y="193.1" width="6.9" height="6.9"/><rect class="st3" x="41.38" y="200" width="6.9" height="6.9"/><rect class="st11" x="41.38" y="206.9" width="6.9" height="6.9"/><rect class="st1" x="48.28" y="137.93" width="6.9" height="6.9"/><rect class="st3" x="48.28" y="144.83" width="6.9" height="6.9"/><rect class="st3" x="48.28" y="151.72" width="6.9" height="6.9"/><rect class="st3" x="48.28" y="158.62" width="6.9" height="6.9"/><rect class="st3" x="48.28" y="165.52" width="6.9" height="6.9"/><rect class="st3" x="48.28" y="172.41" width="6.9" height="6.9"/><rect class="st3" x="48.28" y="179.31" width="6.9" height="6.9"/><rect class="st3" x="48.28" y="186.21" width="6.9" height="6.9"/><rect class="st3" x="48.28" y="193.1" width="6.9" height="6.9"/><rect class="st3" x="48.28" y="200" width="6.9" height="6.9"/><rect class="st11" x="48.28" y="206.9" width="6.9" height="6.9"/><rect class="st1" x="55.17" y="137.93" width="6.9" height="6.9"/><rect class="st3" x="55.17" y="144.83" width="6.9" height="6.9"/><rect class="st3" x="55.17" y="151.72" width="6.9" height="6.9"/><rect class="st3" x="55.17" y="158.62" width="6.9" height="6.9"/><rect class="st3" x="55.17" y="165.52" width="6.9" height="6.9"/><rect class="st3" x="55.17" y="172.41" width="6.9" height="6.9"/><rect class="st3" x="55.17" y="179.31" width="6.9" height="6.9"/><rect class="st3" x="55.17" y="186.21" width="6.9" height="6.9"/><rect class="st3" x="55.17" y="193.1" width="6.9" height="6.9"/><rect class="st3" x="55.17" y="200" width="6.9" height="6.9"/><rect class="st11" x="55.17" y="206.9" width="6.9" height="6.9"/><rect class="st1" x="62.07" y="137.93" width="6.9" height="6.9"/><rect class="st3" x="62.07" y="144.83" width="6.9" height="6.9"/><rect class="st3" x="62.07" y="151.72" width="6.9" height="6.9"/><rect class="st3" x="62.07" y="158.62" width="6.9" height="6.9"/><rect class="st3" x="62.07" y="165.52" width="6.9" height="6.9"/><rect class="st3" x="62.07" y="172.41" width="6.9" height="6.9"/><rect class="st3" x="62.07" y="179.31" width="6.9" height="6.9"/><rect class="st3" x="62.07" y="186.21" width="6.9" height="6.9"/><rect class="st3" x="62.07" y="193.1" width="6.9" height="6.9"/><rect class="st3" x="62.07" y="200" width="6.9" height="6.9"/><rect class="st11" x="62.07" y="206.9" width="6.9" height="6.9"/><rect class="st1" x="68.97" y="137.93" width="6.9" height="6.9"/><rect class="st3" x="68.97" y="144.83" width="6.9" height="6.9"/><rect class="st3" x="68.97" y="151.72" width="6.9" height="6.9"/><rect class="st3" x="68.97" y="158.62" width="6.9" height="6.9"/><rect class="st3" x="68.97" y="165.52" width="6.9" height="6.9"/><rect class="st3" x="68.97" y="172.41" width="6.9" height="6.9"/><rect class="st3" x="68.97" y="179.31" width="6.9" height="6.9"/><rect class="st3" x="68.97" y="186.21" width="6.9" height="6.9"/><rect class="st3" x="68.97" y="193.1" width="6.9" height="6.9"/><rect class="st3" x="68.97" y="200" width="6.9" height="6.9"/><rect class="st11" x="68.97" y="206.9" width="6.9" height="6.9"/><rect class="st1" x="75.86" y="137.93" width="6.9" height="6.9"/><rect class="st3" x="75.86" y="144.83" width="6.9" height="6.9"/><rect class="st3" x="75.86" y="151.72" width="6.9" height="6.9"/><rect class="st3" x="75.86" y="158.62" width="6.9" height="6.9"/><rect class="st3" x="75.86" y="165.52" width="6.9" height="6.9"/><rect class="st3" x="75.86" y="172.41" width="6.9" height="6.9"/><rect class="st3" x="75.86" y="179.31" width="6.9" height="6.9"/><rect class="st3" x="75.86" y="186.21" width="6.9" height="6.9"/><rect class="st3" x="75.86" y="193.1" width="6.9" height="6.9"/><rect class="st3" x="75.86" y="200" width="6.9" height="6.9"/><rect class="st11" x="75.86" y="206.9" width="6.9" height="6.9"/><rect class="st1" x="82.76" y="137.93" width="6.9" height="6.9"/><rect class="st3" x="82.76" y="144.83" width="6.9" height="6.9"/><rect class="st3" x="82.76" y="151.72" width="6.9" height="6.9"/><rect class="st3" x="82.76" y="158.62" width="6.9" height="6.9"/><rect class="st3" x="82.76" y="165.52" width="6.9" height="6.9"/><rect class="st3" x="82.76" y="172.41" width="6.9" height="6.9"/><rect class="st3" x="82.76" y="179.31" width="6.9" height="6.9"/><rect class="st3" x="82.76" y="186.21" width="6.9" height="6.9"/><rect class="st3" x="82.76" y="193.1" width="6.9" height="6.9"/><rect class="st3" x="82.76" y="200" width="6.9" height="6.9"/><rect class="st11" x="82.76" y="206.9" width="6.9" height="6.9"/><rect class="st1" x="89.66" y="137.93" width="6.9" height="6.9"/><rect class="st3" x="89.66" y="144.83" width="6.9" height="6.9"/><rect class="st3" x="89.66" y="151.72" width="6.9" height="6.9"/><rect class="st3" x="89.66" y="158.62" width="6.9" height="6.9"/><rect class="st14" x="89.66" y="165.52" width="6.9" height="6.9"/><rect class="st3" x="89.66" y="172.41" width="6.9" height="6.9"/><rect class="st14" x="89.66" y="179.31" width="6.9" height="6.9"/><rect class="st3" x="89.66" y="186.21" width="6.9" height="6.9"/><rect class="st3" x="89.66" y="193.1" width="6.9" height="6.9"/><rect class="st3" x="89.66" y="200" width="6.9" height="6.9"/><rect class="st11" x="89.66" y="206.9" width="6.9" height="6.9"/><rect class="st1" x="96.55" y="137.93" width="6.9" height="6.9"/><rect class="st3" x="96.55" y="144.83" width="6.9" height="6.9"/><rect class="st3" x="96.55" y="151.72" width="6.9" height="6.9"/><rect class="st3" x="96.55" y="158.62" width="6.9" height="6.9"/><rect class="st3" x="96.55" y="165.52" width="6.9" height="6.9"/><rect class="st3" x="96.55" y="172.41" width="6.9" height="6.9"/><rect class="st14" x="96.55" y="179.31" width="6.9" height="6.9"/><rect class="st3" x="96.55" y="186.21" width="6.9" height="6.9"/><rect class="st3" x="96.55" y="193.1" width="6.9" height="6.9"/><rect class="st3" x="96.55" y="200" width="6.9" height="6.9"/><rect class="st11" x="96.55" y="206.9" width="6.9" height="6.9"/><rect class="st1" x="103.45" y="137.93" width="6.9" height="6.9"/><rect class="st3" x="103.45" y="144.83" width="6.9" height="6.9"/><rect class="st3" x="103.45" y="151.72" width="6.9" height="6.9"/><rect class="st3" x="103.45" y="158.62" width="6.9" height="6.9"/><rect class="st14" x="103.45" y="165.52" width="6.9" height="6.9"/><rect class="st3" x="103.45" y="172.41" width="6.9" height="6.9"/><rect class="st14" x="103.45" y="179.31" width="6.9" height="6.9"/><rect class="st3" x="103.45" y="186.21" width="6.9" height="6.9"/><rect class="st3" x="103.45" y="193.1" width="6.9" height="6.9"/><rect class="st3" x="103.45" y="200" width="6.9" height="6.9"/><rect class="st11" x="103.45" y="206.9" width="6.9" height="6.9"/><rect class="st1" x="110.35" y="137.93" width="6.9" height="6.9"/><rect class="st3" x="110.35" y="144.83" width="6.9" height="6.9"/><rect class="st3" x="110.35" y="151.72" width="6.9" height="6.9"/><rect class="st3" x="110.35" y="158.62" width="6.9" height="6.9"/><rect class="st3" x="110.35" y="165.52" width="6.9" height="6.9"/><rect class="st3" x="110.35" y="172.41" width="6.9" height="6.9"/><rect class="st3" x="110.35" y="179.31" width="6.9" height="6.9"/><rect class="st3" x="110.35" y="186.21" width="6.9" height="6.9"/><rect class="st3" x="110.35" y="193.1" width="6.9" height="6.9"/><rect class="st3" x="110.35" y="200" width="6.9" height="6.9"/><rect class="st11" x="110.35" y="206.9" width="6.9" height="6.9"/><rect class="st1" x="117.24" y="137.93" width="6.9" height="6.9"/><rect class="st3" x="117.24" y="144.83" width="6.9" height="6.9"/><rect class="st3" x="117.24" y="151.72" width="6.9" height="6.9"/><rect class="st3" x="117.24" y="158.62" width="6.9" height="6.9"/><rect class="st3" x="117.24" y="165.52" width="6.9" height="6.9"/><rect class="st3" x="117.24" y="172.41" width="6.9" height="6.9"/><rect class="st3" x="117.24" y="179.31" width="6.9" height="6.9"/><rect class="st3" x="117.24" y="186.21" width="6.9" height="6.9"/><rect class="st3" x="117.24" y="193.1" width="6.9" height="6.9"/><rect class="st3" x="117.24" y="200" width="6.9" height="6.9"/><rect class="st11" x="117.24" y="206.9" width="6.9" height="6.9"/><rect class="st1" x="124.14" y="137.93" width="6.9" height="6.9"/><rect class="st3" x="124.14" y="144.83" width="6.9" height="6.9"/><rect class="st3" x="124.14" y="151.72" width="6.9" height="6.9"/><rect class="st3" x="124.14" y="158.62" width="6.9" height="6.9"/><rect class="st3" x="124.14" y="165.52" width="6.9" height="6.9"/><rect class="st3" x="124.14" y="172.41" width="6.9" height="6.9"/><rect class="st3" x="124.14" y="179.31" width="6.9" height="6.9"/><rect class="st3" x="124.14" y="186.21" width="6.9" height="6.9"/><rect class="st3" x="124.14" y="193.1" width="6.9" height="6.9"/><rect class="st3" x="124.14" y="200" width="6.9" height="6.9"/><rect class="st11" x="124.14" y="206.9" width="6.9" height="6.9"/><rect class="st15" x="62.07" width="6.9" height="6.9"/><rect class="st15" x="62.07" y="6.9" width="6.9" height="6.9"/><rect class="st15" x="62.07" y="13.79" width="6.9" height="6.9"/><rect class="st15" x="62.07" y="20.69" width="6.9" height="6.9"/><rect class="st15" x="62.07" y="27.59" width="6.9" height="6.9"/><rect class="st15" x="62.07" y="34.48" width="6.9" height="6.9"/><rect class="st5" x="62.07" y="41.38" width="6.9" height="6.9"/><rect class="st5" x="62.07" y="48.28" width="6.9" height="6.9"/><rect class="st5" x="62.07" y="55.17" width="6.9" height="6.9"/><rect class="st5" x="62.07" y="62.07" width="6.9" height="6.9"/><rect class="st15" x="55.17" width="6.9" height="6.9"/><rect class="st0" x="55.17" y="6.9" width="6.9" height="6.9"/><rect class="st0" x="55.17" y="13.79" width="6.9" height="6.9"/><rect class="st0" x="55.17" y="20.69" width="6.9" height="6.9"/><rect class="st15" x="55.17" y="27.59" width="6.9" height="6.9"/><rect class="st15" x="55.17" y="34.48" width="6.9" height="6.9"/><rect class="st15" x="55.17" y="41.38" width="6.9" height="6.9"/><rect class="st5" x="55.17" y="48.28" width="6.9" height="6.9"/><rect class="st5" x="55.17" y="55.17" width="6.9" height="6.9"/><rect class="st15" x="55.17" y="62.07" width="6.9" height="6.9"/><rect class="st15" x="62.07" y="68.96" width="6.9" height="6.9"/><rect class="st15" x="68.97" y="6.9" width="6.9" height="6.9"/><rect class="st15" x="68.97" y="13.79" width="6.9" height="6.9"/><rect class="st5" x="68.97" y="20.69" width="6.9" height="6.9"/><rect class="st5" x="68.97" y="27.59" width="6.9" height="6.9"/><rect class="st5" x="68.97" y="34.48" width="6.9" height="6.9"/><rect class="st8" x="68.97" y="41.38" width="6.9" height="6.9"/><rect class="st5" x="68.97" y="48.28" width="6.9" height="6.9"/><rect class="st5" x="68.97" y="55.17" width="6.9" height="6.9"/><rect class="st5" x="68.97" y="62.07" width="6.9" height="6.9"/><rect class="st5" x="68.97" y="68.96" width="6.9" height="6.9"/><rect class="st15" x="68.97" y="75.86" width="6.9" height="6.9"/><rect class="st15" x="75.86" y="20.69" width="6.9" height="6.9"/><rect class="st15" x="75.86" y="27.59" width="6.9" height="6.9"/><rect class="st15" x="75.86" y="34.48" width="6.9" height="6.9"/><rect class="st5" x="75.86" y="41.38" width="6.9" height="6.9"/><rect class="st8" x="75.86" y="48.28" width="6.9" height="6.9"/><rect class="st5" x="75.86" y="55.17" width="6.9" height="6.9"/><rect class="st5" x="75.86" y="62.07" width="6.9" height="6.9"/><rect class="st5" x="75.86" y="68.96" width="6.9" height="6.9"/><rect class="st5" x="75.86" y="75.86" width="6.9" height="6.9"/><rect class="st15" x="75.86" y="82.76" width="6.9" height="6.9"/><rect class="st15" x="82.76" y="13.79" width="6.9" height="6.9"/><rect class="st15" x="82.76" y="20.69" width="6.9" height="6.9"/><rect class="st15" x="82.76" y="27.59" width="6.9" height="6.9"/><rect class="st15" x="82.76" y="34.48" width="6.9" height="6.9"/><rect class="st15" x="82.76" y="41.38" width="6.9" height="6.9"/><rect class="st8" x="82.76" y="48.28" width="6.9" height="6.9"/><rect class="st5" x="82.76" y="55.17" width="6.9" height="6.9"/><rect class="st2" x="82.76" y="62.07" width="6.9" height="6.9"/><rect class="st4" x="82.76" y="68.96" width="6.9" height="6.9"/><rect class="st2" x="82.76" y="75.86" width="6.9" height="6.9"/><rect class="st15" x="82.76" y="82.76" width="6.9" height="6.9"/><rect class="st15" x="89.66" y="13.79" width="6.9" height="6.9"/><rect class="st15" x="89.66" y="20.69" width="6.9" height="6.9"/><rect class="st12" x="89.66" y="27.59" width="6.9" height="6.9"/><rect class="st12" x="89.66" y="34.48" width="6.9" height="6.9"/><rect class="st15" x="89.66" y="41.38" width="6.9" height="6.9"/><rect class="st8" x="89.66" y="48.28" width="6.9" height="6.9"/><rect class="st5" x="89.66" y="55.17" width="6.9" height="6.9"/><rect class="st2" x="89.66" y="62.07" width="6.9" height="6.9"/><rect class="st2" x="89.66" y="68.96" width="6.9" height="6.9"/><rect class="st2" x="89.66" y="75.86" width="6.9" height="6.9"/><rect class="st15" x="89.66" y="82.76" width="6.9" height="6.9"/><rect class="st15" x="89.66" y="89.65" width="6.9" height="6.9"/><rect class="st12" x="96.55" y="6.9" width="6.9" height="6.9"/><rect class="st13" x="96.55" y="13.79" width="6.9" height="6.9"/><rect class="st12" x="96.55" y="20.69" width="6.9" height="6.9"/><rect class="st13" x="96.55" y="27.59" width="6.9" height="6.9"/><rect class="st12" x="96.55" y="34.48" width="6.9" height="6.9"/><rect class="st13" x="96.55" y="41.38" width="6.9" height="6.9"/><rect class="st5" x="96.55" y="48.28" width="6.9" height="6.9"/><rect class="st5" x="96.55" y="55.17" width="6.9" height="6.9"/><rect class="st2" x="96.55" y="62.07" width="6.9" height="6.9"/><rect class="st4" x="96.55" y="68.96" width="6.9" height="6.9"/><rect class="st2" x="96.55" y="75.86" width="6.9" height="6.9"/><rect class="st2" x="96.55" y="82.76" width="6.9" height="6.9"/><rect class="st15" x="96.55" y="89.65" width="6.9" height="6.9"/><rect class="st15" x="103.45" y="13.79" width="6.9" height="6.9"/><rect class="st15" x="103.45" y="20.69" width="6.9" height="6.9"/><rect class="st12" x="103.45" y="27.59" width="6.9" height="6.9"/><rect class="st12" x="103.45" y="34.48" width="6.9" height="6.9"/><rect class="st15" x="103.45" y="41.38" width="6.9" height="6.9"/><rect class="st8" x="103.45" y="48.28" width="6.9" height="6.9"/><rect class="st5" x="103.45" y="55.17" width="6.9" height="6.9"/><rect class="st2" x="103.45" y="62.07" width="6.9" height="6.9"/><rect class="st2" x="103.45" y="68.96" width="6.9" height="6.9"/><rect class="st2" x="103.45" y="75.86" width="6.9" height="6.9"/><rect class="st15" x="103.45" y="82.76" width="6.9" height="6.9"/><rect class="st15" x="103.45" y="89.65" width="6.9" height="6.9"/><rect class="st15" x="110.35" y="13.79" width="6.9" height="6.9"/><rect class="st15" x="110.35" y="20.69" width="6.9" height="6.9"/><rect class="st15" x="110.35" y="27.59" width="6.9" height="6.9"/><rect class="st15" x="110.35" y="34.48" width="6.9" height="6.9"/><rect class="st15" x="110.35" y="41.38" width="6.9" height="6.9"/><rect class="st8" x="110.35" y="48.28" width="6.9" height="6.9"/><rect class="st5" x="110.35" y="55.17" width="6.9" height="6.9"/><rect class="st2" x="110.35" y="62.07" width="6.9" height="6.9"/><rect class="st4" x="110.35" y="68.96" width="6.9" height="6.9"/><rect class="st2" x="110.35" y="75.86" width="6.9" height="6.9"/><rect class="st15" x="110.35" y="82.76" width="6.9" height="6.9"/><rect class="st15" x="117.24" y="20.69" width="6.9" height="6.9"/><rect class="st15" x="117.24" y="27.59" width="6.9" height="6.9"/><rect class="st15" x="117.24" y="34.48" width="6.9" height="6.9"/><rect class="st5" x="117.24" y="41.38" width="6.9" height="6.9"/><rect class="st8" x="117.24" y="48.28" width="6.9" height="6.9"/><rect class="st5" x="117.24" y="55.17" width="6.9" height="6.9"/><rect class="st5" x="117.24" y="62.07" width="6.9" height="6.9"/><rect class="st5" x="117.24" y="68.96" width="6.9" height="6.9"/><rect class="st5" x="117.24" y="75.86" width="6.9" height="6.9"/><rect class="st15" x="117.24" y="82.76" width="6.9" height="6.9"/><rect class="st15" x="124.14" y="6.9" width="6.9" height="6.9"/><rect class="st15" x="124.14" y="13.79" width="6.9" height="6.9"/><rect class="st5" x="124.14" y="20.69" width="6.9" height="6.9"/><rect class="st5" x="124.14" y="27.59" width="6.9" height="6.9"/><rect class="st5" x="124.14" y="34.48" width="6.9" height="6.9"/><rect class="st5" x="124.14" y="41.38" width="6.9" height="6.9"/><rect class="st5" x="124.14" y="48.28" width="6.9" height="6.9"/><rect class="st8" x="124.14" y="55.17" width="6.9" height="6.9"/><rect class="st5" x="124.14" y="62.07" width="6.9" height="6.9"/><rect class="st5" x="124.14" y="68.96" width="6.9" height="6.9"/><rect class="st15" x="124.14" y="75.86" width="6.9" height="6.9"/><rect class="st15" x="131.04" width="6.9" height="6.9"/><rect class="st15" x="131.04" y="6.9" width="6.9" height="6.9"/><rect class="st15" x="131.04" y="13.79" width="6.9" height="6.9"/><rect class="st15" x="131.04" y="20.69" width="6.9" height="6.9"/><rect class="st15" x="131.04" y="27.59" width="6.9" height="6.9"/><rect class="st15" x="131.04" y="34.48" width="6.9" height="6.9"/><rect class="st5" x="131.04" y="41.38" width="6.9" height="6.9"/><rect class="st5" x="131.04" y="48.28" width="6.9" height="6.9"/><rect class="st5" x="131.04" y="55.17" width="6.9" height="6.9"/><rect class="st5" x="131.04" y="62.07" width="6.9" height="6.9"/><rect class="st15" x="137.93" width="6.9" height="6.9"/><rect class="st0" x="137.93" y="6.9" width="6.9" height="6.9"/><rect class="st0" x="137.93" y="13.79" width="6.9" height="6.9"/><rect class="st0" x="137.93" y="20.69" width="6.9" height="6.9"/><rect class="st15" x="137.93" y="27.59" width="6.9" height="6.9"/><rect class="st15" x="137.93" y="34.48" width="6.9" height="6.9"/><rect class="st15" x="137.93" y="41.38" width="6.9" height="6.9"/><rect class="st5" x="137.93" y="48.28" width="6.9" height="6.9"/><rect class="st5" x="137.93" y="55.17" width="6.9" height="6.9"/><rect class="st15" x="137.93" y="62.07" width="6.9" height="6.9"/><rect class="st15" x="131.04" y="68.96" width="6.9" height="6.9"/><rect class="st1" x="131.04" y="137.93" width="6.9" height="6.9"/><rect class="st3" x="131.04" y="144.83" width="6.9" height="6.9"/><rect class="st3" x="131.04" y="151.72" width="6.9" height="6.9"/><rect class="st3" x="131.04" y="158.62" width="6.9" height="6.9"/><rect class="st3" x="131.04" y="165.52" width="6.9" height="6.9"/><rect class="st3" x="131.04" y="172.41" width="6.9" height="6.9"/><rect class="st3" x="131.04" y="179.31" width="6.9" height="6.9"/><rect class="st3" x="131.04" y="186.21" width="6.9" height="6.9"/><rect class="st3" x="131.04" y="193.1" width="6.9" height="6.9"/><rect class="st3" x="131.04" y="200" width="6.9" height="6.9"/><rect class="st11" x="131.04" y="206.9" width="6.9" height="6.9"/><rect class="st1" x="137.93" y="137.93" width="6.9" height="6.9"/><rect class="st3" x="137.93" y="144.83" width="6.9" height="6.9"/><rect class="st3" x="137.93" y="151.72" width="6.9" height="6.9"/><rect class="st3" x="137.93" y="158.62" width="6.9" height="6.9"/><rect class="st3" x="137.93" y="165.52" width="6.9" height="6.9"/><rect class="st3" x="137.93" y="172.41" width="6.9" height="6.9"/><rect class="st3" x="137.93" y="179.31" width="6.9" height="6.9"/><rect class="st3" x="137.93" y="186.21" width="6.9" height="6.9"/><rect class="st3" x="137.93" y="193.1" width="6.9" height="6.9"/><rect class="st3" x="137.93" y="200" width="6.9" height="6.9"/><rect class="st11" x="137.93" y="206.9" width="6.9" height="6.9"/><rect class="st1" x="144.83" y="137.93" width="6.9" height="6.9"/><rect class="st3" x="144.83" y="144.83" width="6.9" height="6.9"/><rect class="st3" x="144.83" y="151.72" width="6.9" height="6.9"/><rect class="st3" x="144.83" y="158.62" width="6.9" height="6.9"/><rect class="st3" x="144.83" y="165.52" width="6.9" height="6.9"/><rect class="st3" x="144.83" y="172.41" width="6.9" height="6.9"/><rect class="st3" x="144.83" y="179.31" width="6.9" height="6.9"/><rect class="st3" x="144.83" y="186.21" width="6.9" height="6.9"/><rect class="st3" x="144.83" y="193.1" width="6.9" height="6.9"/><rect class="st3" x="144.83" y="200" width="6.9" height="6.9"/><rect class="st11" x="144.83" y="206.9" width="6.9" height="6.9"/><rect class="st1" x="151.73" y="137.93" width="6.9" height="6.9"/><rect class="st3" x="151.73" y="144.83" width="6.9" height="6.9"/><rect class="st3" x="151.73" y="151.72" width="6.9" height="6.9"/><rect class="st3" x="151.73" y="158.62" width="6.9" height="6.9"/><rect class="st3" x="151.73" y="165.52" width="6.9" height="6.9"/><rect class="st3" x="151.73" y="172.41" width="6.9" height="6.9"/><rect class="st3" x="151.73" y="179.31" width="6.9" height="6.9"/><rect class="st3" x="151.73" y="186.21" width="6.9" height="6.9"/><rect class="st3" x="151.73" y="193.1" width="6.9" height="6.9"/><rect class="st3" x="151.73" y="200" width="6.9" height="6.9"/><rect class="st11" x="151.73" y="206.9" width="6.9" height="6.9"/><rect class="st3" x="158.62" y="144.83" width="6.9" height="6.9"/><rect class="st3" x="158.62" y="151.72" width="6.9" height="6.9"/><rect class="st3" x="158.62" y="158.62" width="6.9" height="6.9"/><rect class="st3" x="158.62" y="165.52" width="6.9" height="6.9"/><rect class="st3" x="158.62" y="172.41" width="6.9" height="6.9"/><rect class="st3" x="158.62" y="179.31" width="6.9" height="6.9"/><rect class="st3" x="158.62" y="186.21" width="6.9" height="6.9"/><rect class="st3" x="158.62" y="193.1" width="6.9" height="6.9"/><rect class="st3" x="158.62" y="200" width="6.9" height="6.9"/><rect class="st6" x="34.48" y="96.55" width="6.9" height="6.9"/><rect class="st12" x="34.48" y="103.45" width="6.9" height="6.9"/><rect class="st6" x="34.48" y="110.34" width="6.9" height="6.9"/><rect class="st6" x="34.48" y="117.24" width="6.9" height="6.9"/><rect class="st6" x="34.48" y="124.14" width="6.9" height="6.9"/><rect class="st6" x="34.48" y="131.03" width="6.9" height="6.9"/><rect class="st6" x="41.38" y="96.55" width="6.9" height="6.9"/><rect class="st6" x="41.38" y="103.45" width="6.9" height="6.9"/><rect class="st6" x="41.38" y="110.34" width="6.9" height="6.9"/><rect class="st6" x="41.38" y="117.24" width="6.9" height="6.9"/><rect class="st6" x="41.38" y="124.14" width="6.9" height="6.9"/><rect class="st5" x="41.38" y="131.03" width="6.9" height="6.9"/><rect class="st6" x="48.28" y="96.55" width="6.9" height="6.9"/><rect class="st10" x="34.48" y="89.65" width="6.9" height="6.9"/><rect class="st10" x="48.28" y="89.65" width="6.9" height="6.9"/><rect class="st12" x="48.28" y="103.45" width="6.9" height="6.9"/><rect class="st6" x="48.28" y="110.34" width="6.9" height="6.9"/><rect class="st6" x="48.28" y="117.24" width="6.9" height="6.9"/><rect class="st5" x="48.28" y="124.14" width="6.9" height="6.9"/><rect class="st6" x="48.28" y="131.03" width="6.9" height="6.9"/><rect class="st6" x="55.17" y="96.55" width="6.9" height="6.9"/><rect class="st6" x="55.17" y="103.45" width="6.9" height="6.9"/><rect class="st6" x="55.17" y="110.34" width="6.9" height="6.9"/><rect class="st6" x="55.17" y="117.24" width="6.9" height="6.9"/><rect class="st6" x="55.17" y="124.14" width="6.9" height="6.9"/><rect class="st6" x="55.17" y="131.03" width="6.9" height="6.9"/><rect class="st6" x="62.07" y="103.45" width="6.9" height="6.9"/><rect class="st6" x="62.07" y="110.34" width="6.9" height="6.9"/><rect class="st6" x="62.07" y="117.24" width="6.9" height="6.9"/><rect class="st6" x="62.07" y="124.14" width="6.9" height="6.9"/><rect class="st6" x="62.07" y="131.03" width="6.9" height="6.9"/><rect class="st6" x="68.97" y="103.45" width="6.9" height="6.9"/><rect class="st6" x="68.97" y="110.34" width="6.9" height="6.9"/><rect class="st6" x="68.97" y="117.24" width="6.9" height="6.9"/><rect class="st6" x="68.97" y="124.14" width="6.9" height="6.9"/><rect class="st6" x="68.97" y="131.03" width="6.9" height="6.9"/><rect class="st12" x="75.86" y="96.55" width="6.9" height="6.9"/><rect class="st5" x="75.86" y="103.45" width="6.9" height="6.9"/><rect class="st6" x="75.86" y="110.34" width="6.9" height="6.9"/><rect class="st6" x="75.86" y="117.24" width="6.9" height="6.9"/><rect class="st5" x="75.86" y="124.14" width="6.9" height="6.9"/><rect class="st6" x="75.86" y="131.03" width="6.9" height="6.9"/><rect class="st12" x="82.76" y="96.55" width="6.9" height="6.9"/><rect class="st12" x="82.76" y="103.45" width="6.9" height="6.9"/><rect class="st5" x="82.76" y="110.34" width="6.9" height="6.9"/><rect class="st6" x="82.76" y="117.24" width="6.9" height="6.9"/><rect class="st6" x="82.76" y="124.14" width="6.9" height="6.9"/><rect class="st6" x="82.76" y="131.03" width="6.9" height="6.9"/><rect class="st13" x="89.66" y="96.55" width="6.9" height="6.9"/><rect class="st12" x="89.66" y="103.45" width="6.9" height="6.9"/><rect class="st12" x="89.66" y="110.34" width="6.9" height="6.9"/><rect class="st5" x="89.66" y="117.24" width="6.9" height="6.9"/><rect class="st6" x="89.66" y="124.14" width="6.9" height="6.9"/><rect class="st6" x="89.66" y="131.03" width="6.9" height="6.9"/><rect class="st13" x="96.55" y="96.55" width="6.9" height="6.9"/><rect class="st5" x="96.55" y="103.45" width="6.9" height="6.9"/><rect class="st13" x="96.55" y="110.34" width="6.9" height="6.9"/><rect class="st5" x="96.55" y="117.24" width="6.9" height="6.9"/><rect class="st6" x="96.55" y="124.14" width="6.9" height="6.9"/><rect class="st5" x="96.55" y="131.03" width="6.9" height="6.9"/><rect class="st13" x="103.45" y="96.55" width="6.9" height="6.9"/><rect class="st12" x="103.45" y="103.45" width="6.9" height="6.9"/><rect class="st12" x="103.45" y="110.34" width="6.9" height="6.9"/><rect class="st5" x="103.45" y="117.24" width="6.9" height="6.9"/><rect class="st6" x="103.45" y="124.14" width="6.9" height="6.9"/><rect class="st6" x="103.45" y="131.03" width="6.9" height="6.9"/><rect class="st12" x="110.35" y="96.55" width="6.9" height="6.9"/><rect class="st12" x="110.35" y="103.45" width="6.9" height="6.9"/><rect class="st5" x="110.35" y="110.34" width="6.9" height="6.9"/><rect class="st6" x="110.35" y="117.24" width="6.9" height="6.9"/><rect class="st6" x="110.35" y="124.14" width="6.9" height="6.9"/><rect class="st6" x="110.35" y="131.03" width="6.9" height="6.9"/><rect class="st12" x="117.24" y="96.55" width="6.9" height="6.9"/><rect class="st5" x="117.24" y="103.45" width="6.9" height="6.9"/><rect class="st6" x="117.24" y="110.34" width="6.9" height="6.9"/><rect class="st6" x="117.24" y="117.24" width="6.9" height="6.9"/><rect class="st5" x="117.24" y="124.14" width="6.9" height="6.9"/><rect class="st6" x="117.24" y="131.03" width="6.9" height="6.9"/><rect class="st12" x="68.97" y="89.65" width="6.9" height="6.9"/><rect class="st13" x="75.86" y="89.65" width="6.9" height="6.9"/><rect class="st13" x="82.76" y="89.65" width="6.9" height="6.9"/><rect class="st13" x="110.35" y="89.65" width="6.9" height="6.9"/><rect class="st13" x="117.24" y="89.65" width="6.9" height="6.9"/><rect class="st12" x="124.14" y="89.65" width="6.9" height="6.9"/><rect class="st13" x="68.97" y="82.76" width="6.9" height="6.9"/><rect class="st13" x="124.14" y="82.76" width="6.9" height="6.9"/><rect class="st6" x="124.14" y="103.45" width="6.9" height="6.9"/><rect class="st6" x="124.14" y="110.34" width="6.9" height="6.9"/><rect class="st6" x="124.14" y="117.24" width="6.9" height="6.9"/><rect class="st6" x="124.14" y="124.14" width="6.9" height="6.9"/><rect class="st6" x="124.14" y="131.03" width="6.9" height="6.9"/><rect class="st6" x="131.04" y="103.45" width="6.9" height="6.9"/><rect class="st6" x="131.04" y="110.34" width="6.9" height="6.9"/><rect class="st6" x="131.04" y="117.24" width="6.9" height="6.9"/><rect class="st6" x="131.04" y="124.14" width="6.9" height="6.9"/><rect class="st6" x="131.04" y="131.03" width="6.9" height="6.9"/><rect class="st6" x="137.93" y="96.55" width="6.9" height="6.9"/><rect class="st6" x="137.93" y="103.45" width="6.9" height="6.9"/><rect class="st6" x="137.93" y="110.34" width="6.9" height="6.9"/><rect class="st6" x="137.93" y="117.24" width="6.9" height="6.9"/><rect class="st6" x="137.93" y="124.14" width="6.9" height="6.9"/><rect class="st6" x="137.93" y="131.03" width="6.9" height="6.9"/><rect class="st6" x="144.83" y="96.55" width="6.9" height="6.9"/><rect class="st12" x="144.83" y="103.45" width="6.9" height="6.9"/><rect class="st6" x="144.83" y="110.34" width="6.9" height="6.9"/><rect class="st6" x="144.83" y="117.24" width="6.9" height="6.9"/><rect class="st5" x="144.83" y="124.14" width="6.9" height="6.9"/><rect class="st6" x="144.83" y="131.03" width="6.9" height="6.9"/><rect class="st6" x="151.73" y="96.55" width="6.9" height="6.9"/><rect class="st6" x="151.73" y="103.45" width="6.9" height="6.9"/><rect class="st6" x="151.73" y="110.34" width="6.9" height="6.9"/><rect class="st6" x="151.73" y="117.24" width="6.9" height="6.9"/><rect class="st6" x="151.73" y="124.14" width="6.9" height="6.9"/><rect class="st5" x="151.73" y="131.03" width="6.9" height="6.9"/><rect class="st6" x="158.62" y="96.55" width="6.9" height="6.9"/><rect class="st12" x="158.62" y="103.45" width="6.9" height="6.9"/><rect class="st6" x="158.62" y="110.34" width="6.9" height="6.9"/><rect class="st6" x="158.62" y="117.24" width="6.9" height="6.9"/><rect class="st6" x="158.62" y="124.14" width="6.9" height="6.9"/><rect class="st6" x="158.62" y="131.03" width="6.9" height="6.9"/><rect class="st6" x="34.48" y="137.93" width="6.9" height="6.9"/><rect class="st6" x="158.62" y="137.93" width="6.9" height="6.9"/><rect class="st6" x="165.52" y="96.55" width="6.9" height="6.9"/><rect class="st6" x="165.52" y="103.45" width="6.9" height="6.9"/><rect class="st7" x="165.52" y="110.34" width="6.9" height="6.9"/><rect class="st0" x="165.52" y="117.24" width="6.9" height="6.9"/><rect class="st0" x="165.52" y="124.14" width="6.9" height="6.9"/><rect class="st15" x="165.52" y="131.03" width="6.9" height="6.9"/><rect class="st6" x="172.41" y="96.55" width="6.9" height="6.9"/><rect class="st6" x="144.83" y="89.65" width="6.9" height="6.9"/><rect class="st6" x="158.62" y="89.65" width="6.9" height="6.9"/><rect class="st6" x="172.41" y="89.65" width="6.9" height="6.9"/><rect class="st7" x="172.41" y="103.45" width="6.9" height="6.9"/><rect class="st0" x="172.41" y="110.34" width="6.9" height="6.9"/><rect class="st0" x="172.41" y="117.24" width="6.9" height="6.9"/><rect class="st6" x="172.41" y="124.14" width="6.9" height="6.9"/><rect class="st6" x="172.41" y="131.03" width="6.9" height="6.9"/><rect class="st6" x="179.31" y="96.55" width="6.9" height="6.9"/><rect class="st7" x="179.31" y="103.45" width="6.9" height="6.9"/><rect class="st0" x="179.31" y="110.34" width="6.9" height="6.9"/><rect class="st6" x="179.31" y="117.24" width="6.9" height="6.9"/><rect class="st6" x="179.31" y="124.14" width="6.9" height="6.9"/><rect class="st6" x="179.31" y="131.03" width="6.9" height="6.9"/><rect class="st0" x="186.21" y="103.45" width="6.9" height="6.9"/><rect class="st0" x="186.21" y="110.34" width="6.9" height="6.9"/><rect class="st6" x="186.21" y="117.24" width="6.9" height="6.9"/><rect class="st5" x="186.21" y="124.14" width="6.9" height="6.9"/><rect class="st5" x="186.21" y="131.03" width="6.9" height="6.9"/><rect class="st0" x="193.1" y="110.34" width="6.9" height="6.9"/><rect class="st0" x="193.1" y="117.24" width="6.9" height="6.9"/><rect class="st9" x="193.1" y="124.14" width="6.9" height="6.9"/><rect class="st9" x="193.1" y="131.03" width="6.9" height="6.9"/><rect class="st15" x="165.52" y="137.93" width="6.9" height="6.9"/><rect class="st6" x="165.52" y="144.83" width="6.9" height="6.9"/><rect class="st15" x="165.52" y="151.72" width="6.9" height="6.9"/><rect class="st6" x="165.52" y="158.62" width="6.9" height="6.9"/><rect class="st6" x="172.41" y="137.93" width="6.9" height="6.9"/><rect class="st6" x="172.41" y="144.83" width="6.9" height="6.9"/><rect class="st6" x="172.41" y="151.72" width="6.9" height="6.9"/><rect class="st0" x="172.41" y="158.62" width="6.9" height="6.9"/><rect class="st6" x="179.31" y="137.93" width="6.9" height="6.9"/><rect class="st6" x="179.31" y="144.83" width="6.9" height="6.9"/><rect class="st6" x="179.31" y="151.72" width="6.9" height="6.9"/><rect class="st6" x="179.31" y="158.62" width="6.9" height="6.9"/><rect class="st6" x="186.21" y="137.93" width="6.9" height="6.9"/><rect class="st6" x="186.21" y="144.83" width="6.9" height="6.9"/><rect class="st9" x="186.21" y="151.72" width="6.9" height="6.9"/><rect class="st9" x="186.21" y="158.62" width="6.9" height="6.9"/><rect class="st9" x="193.1" y="137.93" width="6.9" height="6.9"/><rect class="st9" x="193.1" y="144.83" width="6.9" height="6.9"/><rect class="st6" x="165.52" y="165.52" width="6.9" height="6.9"/><rect class="st6" x="165.52" y="172.41" width="6.9" height="6.9"/><rect class="st9" x="165.52" y="179.31" width="6.9" height="6.9"/><rect class="st6" x="172.41" y="165.52" width="6.9" height="6.9"/><rect class="st9" x="172.41" y="172.41" width="6.9" height="6.9"/><rect class="st0" x="179.31" y="165.52" width="6.9" height="6.9"/><rect class="st6" x="27.58" y="96.55" width="6.9" height="6.9"/><rect class="st6" x="27.58" y="103.44" width="6.9" height="6.9"/><rect class="st7" x="27.58" y="110.34" width="6.9" height="6.9"/><rect class="st0" x="27.58" y="117.24" width="6.9" height="6.9"/><rect class="st0" x="27.58" y="124.13" width="6.9" height="6.9"/><rect class="st15" x="27.58" y="131.03" width="6.9" height="6.9"/><rect class="st6" x="20.69" y="96.55" width="6.9" height="6.9"/><rect class="st10" x="20.69" y="89.66" width="6.9" height="6.9"/><rect class="st7" x="20.69" y="103.44" width="6.9" height="6.9"/><rect class="st0" x="20.69" y="110.34" width="6.9" height="6.9"/><rect class="st0" x="20.69" y="117.24" width="6.9" height="6.9"/><rect class="st6" x="20.69" y="124.13" width="6.9" height="6.9"/><rect class="st6" x="20.69" y="131.03" width="6.9" height="6.9"/><rect class="st10" x="13.79" y="96.55" width="6.9" height="6.9"/><rect class="st7" x="13.79" y="103.44" width="6.9" height="6.9"/><rect class="st0" x="13.79" y="110.34" width="6.9" height="6.9"/><rect class="st6" x="13.79" y="117.24" width="6.9" height="6.9"/><rect class="st6" x="13.79" y="124.13" width="6.9" height="6.9"/><rect class="st6" x="13.79" y="131.03" width="6.9" height="6.9"/><rect class="st0" x="6.89" y="103.44" width="6.9" height="6.9"/><rect class="st0" x="6.89" y="110.34" width="6.9" height="6.9"/><rect class="st6" x="6.89" y="117.24" width="6.9" height="6.9"/><rect class="st5" x="6.89" y="124.13" width="6.9" height="6.9"/><rect class="st5" x="6.89" y="131.03" width="6.9" height="6.9"/><rect class="st0" y="110.34" width="6.9" height="6.9"/><rect class="st0" y="117.24" width="6.9" height="6.9"/><rect class="st10" y="124.13" width="6.9" height="6.9"/><rect class="st10" y="131.03" width="6.9" height="6.9"/><rect class="st15" x="27.58" y="137.93" width="6.9" height="6.9"/><rect class="st6" x="27.58" y="144.82" width="6.9" height="6.9"/><rect class="st15" x="27.58" y="151.72" width="6.9" height="6.9"/><rect class="st6" x="27.58" y="158.62" width="6.9" height="6.9"/><rect class="st6" x="20.69" y="137.93" width="6.9" height="6.9"/><rect class="st6" x="20.69" y="144.82" width="6.9" height="6.9"/><rect class="st6" x="20.69" y="151.72" width="6.9" height="6.9"/><rect class="st0" x="20.69" y="158.62" width="6.9" height="6.9"/><rect class="st6" x="13.79" y="137.93" width="6.9" height="6.9"/><rect class="st6" x="13.79" y="144.82" width="6.9" height="6.9"/><rect class="st6" x="13.79" y="151.72" width="6.9" height="6.9"/><rect class="st6" x="13.79" y="158.62" width="6.9" height="6.9"/><rect class="st6" x="6.89" y="137.93" width="6.9" height="6.9"/><rect class="st6" x="6.89" y="144.82" width="6.9" height="6.9"/><rect class="st10" x="6.89" y="151.72" width="6.9" height="6.9"/><rect class="st10" x="6.89" y="158.62" width="6.9" height="6.9"/><rect class="st10" y="137.93" width="6.9" height="6.9"/><rect class="st10" y="144.82" width="6.9" height="6.9"/><rect class="st6" x="27.58" y="165.51" width="6.9" height="6.9"/><rect class="st6" x="27.58" y="172.41" width="6.9" height="6.9"/><rect class="st9" x="27.58" y="179.31" width="6.9" height="6.9"/><rect class="st6" x="20.69" y="165.51" width="6.9" height="6.9"/><rect class="st9" x="20.69" y="172.41" width="6.9" height="6.9"/><rect class="st0" x="13.79" y="165.51" width="6.9" height="6.9"/>
                            </svg>
                        </div>
                        <p><?php echo esc_html($this->get_option('welcome_text', 'Ask questions about our content and get AI-powered answers.')); ?></p>
                        <?php
                        $suggestion_1 = $this->get_option('suggestion_1', '');
                        $suggestion_2 = $this->get_option('suggestion_2', '');
                        $suggestion_3 = $this->get_option('suggestion_3', '');
                        if ($suggestion_1 || $suggestion_2 || $suggestion_3) : ?>
                        <div class="cl-chat-suggestions">
                            <?php if ($suggestion_1) : ?><button class="cl-chat-suggestion"><?php echo esc_html($suggestion_1); ?></button><?php endif; ?>
                            <?php if ($suggestion_2) : ?><button class="cl-chat-suggestion"><?php echo esc_html($suggestion_2); ?></button><?php endif; ?>
                            <?php if ($suggestion_3) : ?><button class="cl-chat-suggestion"><?php echo esc_html($suggestion_3); ?></button><?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="cl-chat-input-area">
                    <form class="cl-chat-input-form" id="cl-chat-form">
                        <textarea class="cl-chat-input" id="cl-chat-input" placeholder="<?php echo esc_attr($this->get_option('placeholder_text', 'Ask a question...')); ?>" rows="1"></textarea>
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
            'AI Search Settings',
            'AI Search',
            'manage_options',
            'ai-search',
            array($this, 'render_admin_page')
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting(
            'creativelink_ai_settings',
            'creativelink_ai_options',
            array($this, 'sanitize_options')
        );

        // Widget Settings Section
        add_settings_section(
            'creativelink_ai_main',
            'Widget Settings',
            null,
            'ai-search'
        );

        add_settings_field(
            'button_color',
            'Button Color',
            array($this, 'render_color_field'),
            'ai-search',
            'creativelink_ai_main'
        );

        add_settings_field(
            'api_endpoint',
            'API Endpoint',
            array($this, 'render_api_endpoint_field'),
            'ai-search',
            'creativelink_ai_main'
        );

        // Search Settings Section
        add_settings_section(
            'creativelink_ai_search',
            'Search Settings',
            null,
            'ai-search'
        );

        add_settings_field(
            'search_scope',
            'Search Scope',
            array($this, 'render_search_scope_field'),
            'ai-search',
            'creativelink_ai_search'
        );

        add_settings_field(
            'search_category',
            'Category',
            array($this, 'render_category_field'),
            'ai-search',
            'creativelink_ai_search'
        );

        add_settings_field(
            'search_tag',
            'Tag',
            array($this, 'render_tag_field'),
            'ai-search',
            'creativelink_ai_search'
        );

        add_settings_field(
            'search_keyword',
            'Keyword',
            array($this, 'render_keyword_field'),
            'ai-search',
            'creativelink_ai_search'
        );

        add_settings_field(
            'max_posts',
            'Max Posts',
            array($this, 'render_max_posts_field'),
            'ai-search',
            'creativelink_ai_search'
        );

        // Widget Content Section
        add_settings_section(
            'creativelink_ai_content',
            'Widget Content',
            null,
            'ai-search'
        );

        add_settings_field(
            'welcome_text',
            'Welcome Text',
            array($this, 'render_welcome_text_field'),
            'ai-search',
            'creativelink_ai_content'
        );

        add_settings_field(
            'placeholder_text',
            'Input Placeholder',
            array($this, 'render_placeholder_text_field'),
            'ai-search',
            'creativelink_ai_content'
        );

        add_settings_field(
            'suggestion_1',
            'Suggestion 1',
            array($this, 'render_suggestion_1_field'),
            'ai-search',
            'creativelink_ai_content'
        );

        add_settings_field(
            'suggestion_2',
            'Suggestion 2',
            array($this, 'render_suggestion_2_field'),
            'ai-search',
            'creativelink_ai_content'
        );

        add_settings_field(
            'suggestion_3',
            'Suggestion 3',
            array($this, 'render_suggestion_3_field'),
            'ai-search',
            'creativelink_ai_content'
        );
    }

    /**
     * Sanitize and validate options
     */
    public function sanitize_options($input) {
        $sanitized = array();

        // API endpoint - validate URL (allow empty)
        if (isset($input['api_endpoint']) && !empty($input['api_endpoint'])) {
            $url = esc_url_raw($input['api_endpoint'], array('https'));
            $sanitized['api_endpoint'] = $url ? $url : '';
        } else {
            $sanitized['api_endpoint'] = '';
        }

        // Button color - validate hex color
        if (isset($input['button_color'])) {
            $color = sanitize_hex_color($input['button_color']);
            $sanitized['button_color'] = $color ? $color : '#2563EB';
        }

        // Search scope - validate against allowed values
        $allowed_scopes = array('whole_site', 'category', 'tag', 'keyword');
        if (isset($input['search_scope']) && in_array($input['search_scope'], $allowed_scopes, true)) {
            $sanitized['search_scope'] = $input['search_scope'];
        } else {
            $sanitized['search_scope'] = 'whole_site';
        }

        // Search category - validate it exists
        if (isset($input['search_category']) && !empty($input['search_category'])) {
            $cat_id = absint($input['search_category']);
            $category = get_category($cat_id);
            $sanitized['search_category'] = $category ? $cat_id : '';
        } else {
            $sanitized['search_category'] = '';
        }

        // Search tag - validate it exists
        if (isset($input['search_tag']) && !empty($input['search_tag'])) {
            $tag_id = absint($input['search_tag']);
            $tag = get_tag($tag_id);
            $sanitized['search_tag'] = $tag ? $tag_id : '';
        } else {
            $sanitized['search_tag'] = '';
        }

        // Search keyword - sanitize text
        if (isset($input['search_keyword'])) {
            $sanitized['search_keyword'] = sanitize_text_field($input['search_keyword']);
        }

        // Max posts - validate range 1-500
        if (isset($input['max_posts'])) {
            $max_posts = absint($input['max_posts']);
            $sanitized['max_posts'] = max(1, min(500, $max_posts));
        } else {
            $sanitized['max_posts'] = 50;
        }

        // Text fields - sanitize
        $text_fields = array('welcome_text', 'placeholder_text', 'suggestion_1', 'suggestion_2', 'suggestion_3');
        foreach ($text_fields as $field) {
            if (isset($input[$field])) {
                $sanitized[$field] = sanitize_text_field($input[$field]);
            }
        }

        return $sanitized;
    }

    /**
     * Render color picker field
     */
    public function render_color_field() {
        $color = $this->get_option('button_color', '#2563EB');
        echo '<input type="color" name="creativelink_ai_options[button_color]" value="' . esc_attr($color) . '">';
    }

    /**
     * Render API endpoint field
     */
    public function render_api_endpoint_field() {
        $url = $this->get_option('api_endpoint', self::DEFAULT_WORKER_URL);
        echo '<input type="url" name="creativelink_ai_options[api_endpoint]" value="' . esc_attr($url) . '" class="large-text" placeholder="https://your-worker.your-subdomain.workers.dev">';
        echo '<p class="description"><strong>Required.</strong> The URL of your AI backend service (must use HTTPS).</p>';
        echo '<p class="description">Your backend should accept POST requests to <code>/chat</code> with JSON body containing <code>messages</code> (conversation history), <code>context</code> (post data), and <code>scope</code> (search scope label).</p>';
        echo '<p class="description">Responses should be Server-Sent Events with <code>data: {"response": "text"}</code> for streaming text, and optionally <code>data: {"results": [{"title": "...", "url": "...", "excerpt": "..."}]}</code> for structured results.</p>';
        if (empty($url)) {
            echo '<p class="description" style="color: #d63638;"><strong>Note:</strong> The chat widget will not function until an API endpoint is configured.</p>';
        }
    }

    /**
     * Render search scope radio buttons
     */
    public function render_search_scope_field() {
        $scope = $this->get_option('search_scope', 'whole_site');
        $options = array(
            'whole_site' => 'Whole site',
            'category' => 'Specific category',
            'tag' => 'Specific tag',
            'keyword' => 'Keyword filter'
        );

        foreach ($options as $value => $label) {
            $checked = checked($scope, $value, false);
            echo '<label style="display: block; margin-bottom: 8px;">';
            echo '<input type="radio" name="creativelink_ai_options[search_scope]" value="' . esc_attr($value) . '"' . $checked . '> ';
            echo esc_html($label);
            echo '</label>';
        }
        echo '<p class="description">Select what content the AI should search through.</p>';
    }

    /**
     * Render category dropdown
     */
    public function render_category_field() {
        $selected = $this->get_option('search_category', '');
        $categories = get_categories(array('hide_empty' => false));

        echo '<select name="creativelink_ai_options[search_category]" id="creativelink-search-category">';
        echo '<option value="">-- Select Category --</option>';
        foreach ($categories as $category) {
            $selected_attr = selected($selected, $category->term_id, false);
            echo '<option value="' . esc_attr($category->term_id) . '"' . $selected_attr . '>' . esc_html($category->name) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">Only posts in this category will be searched.</p>';
    }

    /**
     * Render tag dropdown
     */
    public function render_tag_field() {
        $selected = $this->get_option('search_tag', '');
        $tags = get_tags(array('hide_empty' => false));

        echo '<select name="creativelink_ai_options[search_tag]" id="creativelink-search-tag">';
        echo '<option value="">-- Select Tag --</option>';
        foreach ($tags as $tag) {
            $selected_attr = selected($selected, $tag->term_id, false);
            echo '<option value="' . esc_attr($tag->term_id) . '"' . $selected_attr . '>' . esc_html($tag->name) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">Only posts with this tag will be searched.</p>';
    }

    /**
     * Render keyword text field
     */
    public function render_keyword_field() {
        $keyword = $this->get_option('search_keyword', '');
        echo '<input type="text" name="creativelink_ai_options[search_keyword]" id="creativelink-search-keyword" value="' . esc_attr($keyword) . '" class="regular-text">';
        echo '<p class="description">Only posts containing this keyword in title or content will be searched.</p>';
    }

    /**
     * Render max posts number field
     */
    public function render_max_posts_field() {
        $max_posts = $this->get_option('max_posts', 50);
        echo '<input type="number" name="creativelink_ai_options[max_posts]" value="' . esc_attr($max_posts) . '" min="1" max="500" style="width: 80px;">';
        echo '<p class="description">Maximum number of posts to include in search context (1-500).</p>';
    }

    /**
     * Render welcome text field
     */
    public function render_welcome_text_field() {
        $text = $this->get_option('welcome_text', 'Ask questions about our content and get AI-powered answers.');
        echo '<input type="text" name="creativelink_ai_options[welcome_text]" value="' . esc_attr($text) . '" class="large-text">';
        echo '<p class="description">Text displayed below the icon in the chat welcome screen.</p>';
    }

    /**
     * Render placeholder text field
     */
    public function render_placeholder_text_field() {
        $text = $this->get_option('placeholder_text', 'Ask a question...');
        echo '<input type="text" name="creativelink_ai_options[placeholder_text]" value="' . esc_attr($text) . '" class="regular-text">';
        echo '<p class="description">Placeholder text shown in the chat input field.</p>';
    }

    /**
     * Render suggestion 1 field
     */
    public function render_suggestion_1_field() {
        $text = $this->get_option('suggestion_1', '');
        echo '<input type="text" name="creativelink_ai_options[suggestion_1]" value="' . esc_attr($text) . '" class="regular-text">';
        echo '<p class="description">First example search suggestion. Leave empty to hide.</p>';
    }

    /**
     * Render suggestion 2 field
     */
    public function render_suggestion_2_field() {
        $text = $this->get_option('suggestion_2', '');
        echo '<input type="text" name="creativelink_ai_options[suggestion_2]" value="' . esc_attr($text) . '" class="regular-text">';
        echo '<p class="description">Second example search suggestion. Leave empty to hide.</p>';
    }

    /**
     * Render suggestion 3 field
     */
    public function render_suggestion_3_field() {
        $text = $this->get_option('suggestion_3', '');
        echo '<input type="text" name="creativelink_ai_options[suggestion_3]" value="' . esc_attr($text) . '" class="regular-text">';
        echo '<p class="description">Third example search suggestion. Leave empty to hide.</p>';
    }

    /**
     * Render admin settings page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1>AI Search Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('creativelink_ai_settings');
                do_settings_sections('ai-search');
                submit_button();
                ?>
            </form>
        </div>
        <script>
        (function() {
            function updateFieldVisibility() {
                var scope = document.querySelector('input[name="creativelink_ai_options[search_scope]"]:checked');
                var scopeValue = scope ? scope.value : 'whole_site';

                var categoryRow = document.getElementById('creativelink-search-category');
                var tagRow = document.getElementById('creativelink-search-tag');
                var keywordRow = document.getElementById('creativelink-search-keyword');

                if (categoryRow) categoryRow.closest('tr').style.display = scopeValue === 'category' ? '' : 'none';
                if (tagRow) tagRow.closest('tr').style.display = scopeValue === 'tag' ? '' : 'none';
                if (keywordRow) keywordRow.closest('tr').style.display = scopeValue === 'keyword' ? '' : 'none';
            }

            // Run on page load
            document.addEventListener('DOMContentLoaded', updateFieldVisibility);

            // Run when scope changes
            document.querySelectorAll('input[name="creativelink_ai_options[search_scope]"]').forEach(function(radio) {
                radio.addEventListener('change', updateFieldVisibility);
            });
        })();
        </script>
        <?php
    }

    /**
     * Get search context based on settings
     */
    private function get_search_context() {
        $scope = $this->get_option('search_scope', 'whole_site');
        $max_posts = intval($this->get_option('max_posts', 50));

        // Build query args based on scope
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $max_posts,
            'orderby' => 'date',
            'order' => 'DESC'
        );

        switch ($scope) {
            case 'category':
                $category_id = $this->get_option('search_category', '');
                if ($category_id) {
                    $args['cat'] = intval($category_id);
                }
                break;

            case 'tag':
                $tag_id = $this->get_option('search_tag', '');
                if ($tag_id) {
                    $args['tag_id'] = intval($tag_id);
                }
                break;

            case 'keyword':
                $keyword = $this->get_option('search_keyword', '');
                if ($keyword) {
                    $args['s'] = sanitize_text_field($keyword);
                }
                break;

            case 'whole_site':
            default:
                // No additional filtering needed
                break;
        }

        $query = new WP_Query($args);
        $posts_data = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $posts_data[] = array(
                    'title' => get_the_title(),
                    'excerpt' => wp_strip_all_tags(get_the_excerpt()),
                    'url' => get_permalink(),
                    'date' => get_the_date('Y-m-d')
                );
            }
            wp_reset_postdata();
        }

        return $posts_data;
    }

    /**
     * Get search scope label for display
     */
    private function get_search_scope_label() {
        $scope = $this->get_option('search_scope', 'whole_site');

        switch ($scope) {
            case 'category':
                $cat_id = $this->get_option('search_category', '');
                if ($cat_id) {
                    $category = get_category($cat_id);
                    return $category ? 'Category: ' . $category->name : 'Category';
                }
                return 'Category';

            case 'tag':
                $tag_id = $this->get_option('search_tag', '');
                if ($tag_id) {
                    $tag = get_tag($tag_id);
                    return $tag ? 'Tag: ' . $tag->name : 'Tag';
                }
                return 'Tag';

            case 'keyword':
                $keyword = $this->get_option('search_keyword', '');
                return $keyword ? 'Keyword: ' . $keyword : 'Keyword';

            case 'whole_site':
            default:
                return 'Whole site';
        }
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
        return $this->get_option('api_endpoint', self::DEFAULT_WORKER_URL);
    }
}

// Initialize the plugin
new CreativeLink_AI_Search();
