<?php
class heydayWebPush_Heyday_push_Plugin_menue
{
    private static $settings = [];
    private static $randPassword = '';

    public static function heydayWebPush_enqueue_custom_admin_style() 
    {
        wp_enqueue_style('heydayWebPush_heyday-push-main-fonts', 'https://cdn.heyday.io/heyday.io/css/fonts.css');
        wp_enqueue_style('heydayWebPush_heyday-push-main-login', 'https://cdn.heyday.io/heyday.io/css/wp_login_style.css');
    }

    public static function heydayWebPush_enqueue_custom_admin_script() 
    {
        wp_enqueue_script('heydayWebPush_heyday-push-main', 'https://cdn.heyday.io/heyday.io/js/wp_login_script.js');
    }

    public static function heydayWebPush_print_inline_reactivatio_script() 
    {
        wp_add_inline_script('heydayWebPush_heyday-push-main', 'heyday_reactivationSuccess();');
    }

    public static function heydayWebPush_print_inline_init_script() 
    {
        /*just for having short vars*/
        $randPassword = heydayWebPush_Heyday_push_Plugin_menue::$randPassword;
        $settings = heydayWebPush_Heyday_push_Plugin_menue::$settings;
        $randPassword_esc = esc_html(heydayWebPush_Heyday_push_Plugin_menue::$randPassword);
        $heyDaySettings = ["admin_email" => $settings["admin_email"], "swPath" => $settings["swPath"]];
        if(isset($settings["affId"]))
            $heyDaySettings["affId"] = $settings["affId"];
        $heyDaySettings = json_encode(array_map('esc_html', $heyDaySettings));
        
        $heyday_queryParams = [];
        if(isset($_GET['accessToken']))
        {
            $heyday_queryParams['accessToken'] = $_GET['accessToken'];
            if(!preg_match("/^\d+\-\d+\-\d+\-\d+_\d+$/", $heyday_queryParams['accessToken']))
                return;
        }


        if(isset($_GET['globalErr']))
            $heyday_queryParams['globalErr'] = intval($_GET['globalErr']);

        $heyday_queryParams = json_encode(array_map('esc_attr', $heyday_queryParams));
        $blogname = esc_html(get_option('blogname'));
        $r = parse_url(plugins_url('heydaysw.php', __FILE__ ));
        $host = 'https://'.$r['host']; // no need for escape

        /*output js script & code */
        $jsCode = "window.heyday_randPassword = '{$randPassword}';
        window.heyday_randPassword_esc = '{$randPassword_esc}';
        window.blogname = '{$blogname}';
        window.wpHost = '{$host}';
        window.heyDaySettings = {$heyDaySettings};
        window.heyday_queryParams = {$heyday_queryParams};
        heyday_mannageAccount();";
        
        wp_add_inline_script('heydayWebPush_heyday-push-main', $jsCode);
    }

    public static function heydayWebPush_heyday_settings()
    {
        add_action('admin_print_styles', ['heydayWebPush_Heyday_push_Plugin_menue', 'heydayWebPush_enqueue_custom_admin_style']);
        do_action('admin_print_styles');
        add_action('admin_enqueue_scripts', ['heydayWebPush_Heyday_push_Plugin_menue', 'heydayWebPush_enqueue_custom_admin_script']);
        do_action('admin_enqueue_scripts');
        heydayWebPush_Heyday_push_Plugin_menue::$settings = get_option(heydayWebPush_HEYDAY_OPTIONS, []);

        /*heyDayAffId must be int bigger than zero*/
        $heyDayAffId = (isset($_GET['heyDayAffId']) && (int)$_GET['heyDayAffId'] > 0) ? (int)$_GET['heyDayAffId'] : false;
        if(isset(heydayWebPush_Heyday_push_Plugin_menue::$settings['affId']) && $heyDayAffId === false)
        {
            heydayWebPush_Heyday_push_Plugin_menue::heydayWebPush_reactivationSuccess();
            return;
        }
        if($heyDayAffId !== false)
        {
            heydayWebPush_Heyday_push_Plugin_menue::$settings['affId'] = $heyDayAffId;
            update_option(heydayWebPush_HEYDAY_OPTIONS, heydayWebPush_Heyday_push_Plugin_menue::$settings);
        }

        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';        
        if(!$_GET['randPassword'])
        {
            $max = strlen($keyspace)-1;
            for($i=0;$i<8;$i++)
            {
                heydayWebPush_Heyday_push_Plugin_menue::$randPassword .= $keyspace[ rand (0 , $max ) ];
            }
        }
        else
        {
            heydayWebPush_Heyday_push_Plugin_menue::$randPassword = $_GET['randPassword'];
            /*doing custom validation since we do not want to escape the password for the js var. 
            incase of failing the validation, simply return with no output since this can happen only due to misuse
            */
            if(strlen(heydayWebPush_Heyday_push_Plugin_menue::$randPassword) != 8)
            {
                return;
            }
            $charSet = array_flip(str_split($keyspace));
            for($i=0;$i<8;$i++)
            {
                if(!isset($charSet[heydayWebPush_Heyday_push_Plugin_menue::$randPassword[$i]]))
                {
                    return;
                }
            }
        }
        add_action('admin_print_scripts', ['heydayWebPush_Heyday_push_Plugin_menue', 'heydayWebPush_print_inline_init_script']);
        do_action('admin_print_scripts');
    }

    private static function heydayWebPush_reactivationSuccess()
    {
        add_action('admin_print_scripts', ['heydayWebPush_Heyday_push_Plugin_menue', 'heydayWebPush_print_inline_reactivatio_script']);
        do_action('admin_print_scripts');
    }
}
