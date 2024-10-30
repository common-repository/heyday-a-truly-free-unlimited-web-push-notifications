<?php
defined( 'ABSPATH' ) OR exit;

/*
 * Plugin Name: Heyday - A truly free unlimited Web Push Notifications
 * Plugin URI: https://heyday.io/
 * Description: HeyDay Native Push notification is unlimted and truly free now and forever.
 * Version: 1.0.0
 * Author: Socal Media
 * Author URI: https://heyday.io/about.html
 * Text Domain: heyday
 * License: GPL v2 or later
 * License URI: https://heyday.io/terms.html
 */

define('heydayWebPush_HEYDAY_OPTIONS', 'heyday-push');
include plugin_dir_path(__FILE__) . '/admin/'.heydayWebPush_HEYDAY_OPTIONS.'.php';
register_activation_hook(__FILE__, ['heydayWebPush_Heyday_push_Plugin', 'on_activation']);
add_action('activated_plugin', ['heydayWebPush_Heyday_push_Plugin', 'redir'] );
register_uninstall_hook(__FILE__, ['heydayWebPush_Heyday_push_Plugin', 'on_uninstall']);
add_action('plugins_loaded', ['heydayWebPush_Heyday_push_Plugin', 'init' ]);

class heydayWebPush_Heyday_push_Plugin
{
    public static $instance;
    private $affId=-1;

    private function __construct()
    {
        add_action('admin_menu', [$this, 'set_admin_pages']);
        $settings = get_option(heydayWebPush_HEYDAY_OPTIONS, []);
        if(isset($settings['affId']))
        {
            $this->affId = $settings['affId']; 
            add_action('wp_head', [$this, 'inject_head_tag']);
        }
    }

    public static function init()
    {
        if(self::$instance == null)
        {
            self::$instance = new heydayWebPush_Heyday_push_Plugin();
        }
        return self::$instance;
    }

    public static function set_admin_pages()
    {
        add_menu_page('HayDay Push Notifications Settings', 'HeyDay-push', 'manage_options', heydayWebPush_HEYDAY_OPTIONS, ['heydayWebPush_Heyday_push_Plugin_menue', 'heydayWebPush_heyday_settings']);
    }

    public static function inject_head_tag()
    {
        wp_enqueue_script('heydayWebPush_heyday-push-main', 'https://heyday.io/cstmst/heyDayMain.js?affId='.$this->affId);
    }

    public static function on_activation()
    {
        $r = parse_url(plugins_url('heydaysw.php', __FILE__ ));
        $settings = get_option(heydayWebPush_HEYDAY_OPTIONS, []);
        $settings["swPath"] = $r['path'];
        $settings["admin_email"] = get_option('admin_email');
        $settings["blogname"] = get_option('blogname');
        update_option(heydayWebPush_HEYDAY_OPTIONS, $settings);
    }
    
    public static function redir()
    {
        exit( wp_redirect( admin_url( 'admin.php?page='.heydayWebPush_HEYDAY_OPTIONS ) ) );
    }

    public static function on_uninstall()
    {
        delete_option(heydayWebPush_HEYDAY_OPTIONS);
    }
}