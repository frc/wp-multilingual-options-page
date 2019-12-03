<?php
/*
 * Plugin Name: Frc Multilingual Options Page
 * Description: Create ACF Options Pages for all languages
 * Version: 0.0.1
 * Author: Sanna Nygård / Frantic Oy
 * Author URI: http://www.frantic.com
 * Text Domain: frc-multilingual-options-page
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class FrcMultilingualOptionsPage {
    private static $instance = null;
    protected $plugin_dir = '';
    protected $plugin_url = '';
    protected $plugin_name = '';
    private $plugin_textdomain = 'frc-multilingual-options-page';
    private $options_pages = [];

    public function __construct() {
        $this->plugin_dir  = untrailingslashit( plugin_dir_path(__FILE__));
        $this->plugin_url  = plugin_dir_url(__FILE__);
        $this->plugin_name = plugin_basename(__FILE__);

        load_plugin_textdomain($this->plugin_textdomain, false, basename(dirname(__FILE__)) . '/languages');

        add_filter('acf/location/rule_types', [$this, 'acf_location_rule_type']);
        add_filter('acf/location/rule_values/multilingual_options_page', [$this, 'acf_location_rules_values']);
        add_filter('acf/location/rule_match/multilingual_options_page', [$this, 'acf_location_rules_match'], 10, 3);
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new FrcMultilingualOptionsPage();
        }

        return self::$instance;
    }

    private function check_for_active_plugins() {
        $network_active_plugins = is_multisite() ? array_keys(get_site_option('active_sitewide_plugins')) : [];
        $active_plugins = array_merge(get_option('active_plugins'), $network_active_plugins);

        $acf_pro = 'advanced-custom-fields-pro/acf.php';

        return in_array($acf_pro, $active_plugins);
    }

    public function add_page($title, $parent_slug) {
        if (!$this->check_for_active_plugins() || !function_exists('acf_add_options_sub_page')) {
            return false;
        }

        $langs = function_exists('pll_languages_list') ? pll_languages_list() : [substr(get_locale(), 0, 2)];

        $this->options_pages[$parent_slug . '_page_acf-options-' . sanitize_title($title)] = $title;

        foreach ($langs as $lang) {
            acf_add_options_sub_page([
                'page_title'    => $title . ' ' . strtoupper($lang),
                'parent_slug'   => $parent_slug,
                'post_id'       => 'options-' . $lang,
            ]);
        }
    }

    public function acf_location_rule_type($choices) {
        $choices['Forms']['multilingual_options_page'] = __('Multilingual Options Page', $this->plugin_textdomain);

        return $choices;
    }

    public function acf_location_rules_values($choices) {
        return $this->options_pages;
    }

    public function acf_location_rules_match($match, $rule, $options) {
        $selected_options_page = $rule['value'];
        $current_screen_id = get_current_screen()->id;

        if ($rule['operator'] == "==") {
            $match = strpos($current_screen_id, $selected_options_page) !== false;
        } elseif ($rule['operator'] == "!=") {
            $match = strpos($current_screen_id, $selected_options_page) == false;
        }

        return $match;
    }
}
