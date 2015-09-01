<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

class aspInit {

    function ajaxsearchpro_activate() {

        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $table_name = $wpdb->prefix . "ajaxsearchpro";
        $query = "
        CREATE TABLE IF NOT EXISTS `$table_name` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` text NOT NULL,
          `data` text NOT NULL,
          PRIMARY KEY (`id`)
        ) DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
      ";
        dbDelta($query);
        $table_name = $wpdb->prefix . "ajaxsearchpro_statistics";
        $query = "
        CREATE TABLE IF NOT EXISTS `$table_name` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `search_id` int(11) NOT NULL,
          `keyword` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
          `num` int(11) NOT NULL,
          `last_date` int(11) NOT NULL,
          PRIMARY KEY (`id`)
        ) DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
      ";
        dbDelta($query);
         
        $query = "ALTER TABLE `$table_name` MODIFY `keyword` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL";
        dbDelta($query);   
        $wpdb->query($query);
        
        $table_name = $wpdb->prefix . "ajaxsearchpro_priorities";
        $query = "
        CREATE TABLE IF NOT EXISTS `$table_name` (
          `post_id` int(11) NOT NULL,
          `blog_id` int(11) NOT NULL,
          `priority` int(11) NOT NULL,
          PRIMARY KEY (`post_id`, `blog_id`)
        ) DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
      ";
        dbDelta($query);

        $this->fulltext();   
        $this->chmod();
    }

    function navigation_menu() {
        if (current_user_can('manage_options'))  {
            if (!defined("EMU2_I18N_DOMAIN")) define('EMU2_I18N_DOMAIN', "");
            add_menu_page(
                __('Ajax Search Pro', EMU2_I18N_DOMAIN),
                __('Ajax Search Pro', EMU2_I18N_DOMAIN),
                'manage_options',
                ASP_DIR.'/backend/settings.php',
                '',
                ASP_URL.'icon.png',
                "207.9"
            );
            add_submenu_page(
                ASP_DIR.'/backend/settings.php',
                __("Ajax Search Pro", EMU2_I18N_DOMAIN),
                __("Priorities", EMU2_I18N_DOMAIN),
                'manage_options',
                ASP_DIR.'/backend/priorities.php'
            );
            add_submenu_page(
                ASP_DIR.'/backend/settings.php',
                __("Ajax Search Pro", EMU2_I18N_DOMAIN),
                __("Search Statistics", EMU2_I18N_DOMAIN),
                'manage_options',
                ASP_DIR.'/backend/statistics.php'
            );
            add_submenu_page(
                ASP_DIR.'/backend/settings.php',
                __("Ajax Search Pro", EMU2_I18N_DOMAIN),
                __("Analytics Integration", EMU2_I18N_DOMAIN),
                'manage_options',
                ASP_DIR.'/backend/analytics.php'
            );
            add_submenu_page(
                ASP_DIR.'/backend/settings.php',
                __("Ajax Search Pro", EMU2_I18N_DOMAIN),
                __("Error check", EMU2_I18N_DOMAIN),
                'manage_options',
                ASP_DIR.'/backend/comp_check.php'
            );
            add_submenu_page(
                ASP_DIR.'/backend/settings.php',
                __("Ajax Search Pro", EMU2_I18N_DOMAIN),
                __("Fulltext search Settings", EMU2_I18N_DOMAIN),
                'manage_options',
                ASP_DIR.'/backend/fulltext.php'
            );
            add_submenu_page(
                ASP_DIR.'/backend/settings.php',
                __("Ajax Search Pro", EMU2_I18N_DOMAIN),
                __("Cache Settings", EMU2_I18N_DOMAIN),
                'manage_options',
                ASP_DIR.'/backend/cache_settings.php'
            );
            add_submenu_page(
                ASP_DIR.'/backend/settings.php',
                __("Ajax Search Pro", EMU2_I18N_DOMAIN),
                __("Performance tracking", EMU2_I18N_DOMAIN),
                'manage_options',
                ASP_DIR.'/backend/performance.php'
            );
            add_submenu_page(
                ASP_DIR.'/backend/settings.php',
                __("Ajax Search Pro", EMU2_I18N_DOMAIN),
                __("Compatibility Settings", EMU2_I18N_DOMAIN),
                'manage_options',
                ASP_DIR.'/backend/compatibility_settings.php'
            );
            add_submenu_page(
                ASP_DIR.'/backend/settings.php',
                __("Ajax Search Pro", EMU2_I18N_DOMAIN),
                __("Export/Import", EMU2_I18N_DOMAIN),
                'manage_options',
                ASP_DIR.'/backend/export_import.php'
            );
        }
    }

    function styles() {

    }

    function scripts() {
        global $wp_version;
        //Load special jQuery for avoiding conflicts :)
        //jQuery 1.7.2 in aspjQuery object :)
        $comp_settings = get_option('asp_compatibility');
        //var_dump($comp_settings);die();
        if ($comp_settings !== false && isset($comp_settings['loadpolaroidjs']) && $comp_settings['loadpolaroidjs']==0) {
            ;
        } else {
            wp_register_script('wpdreams-modernizr',  ASP_URL.'js/nomin/modernizr.min.js');
            wp_enqueue_script('wpdreams-modernizr');
            wp_register_script('wpdreams-classie',  ASP_URL.'js/nomin/classie.js');
            wp_enqueue_script('wpdreams-classie');
            wp_register_script('wpdreams-photostack',  ASP_URL.'js/nomin/photostack.js');
            wp_enqueue_script('wpdreams-photostack');
        }

        $js_source = w_isset_def($comp_settings['js_source'], 'min-scoped');
        $load_noui = w_isset_def($comp_settings['load_noui_js'], 1);
        $load_isotope = w_isset_def($comp_settings['load_isotope_js'], 1);
        $minify_string = (($load_noui == 1) ? '-noui' : '' ) . (($load_isotope == 1) ? '-isotope' : '' );

        if (ASP_DEBUG) $js_source = 'nomin';
        if ($js_source == 'nomin' || $js_source == 'nomin-scoped') {
            $prereq = "jquery";
            if ($js_source == "nomin-scoped") {
                $prereq = "wpdreams-aspjquery";
                wp_register_script('wpdreams-aspjquery',  ASP_URL.'js/'.$js_source.'/aspjquery.js');
                wp_enqueue_script('wpdreams-aspjquery');
            }
            wp_register_script('wpdreams-gestures', ASP_URL.'js/'.$js_source.'/jquery.gestures.js', array($prereq));
            wp_enqueue_script('wpdreams-gestures');
            wp_register_script('wpdreams-easing', ASP_URL.'js/'.$js_source.'/jquery.easing.js', array($prereq));
            wp_enqueue_script('wpdreams-easing');
            wp_register_script('wpdreams-mousewheel',ASP_URL.'js/'.$js_source.'/jquery.mousewheel.js', array($prereq));
            wp_enqueue_script('wpdreams-mousewheel');
            wp_register_script('wpdreams-scroll', ASP_URL.'js/'.$js_source.'/jquery.mCustomScrollbar.js', array($prereq, 'wpdreams-mousewheel'));
            wp_enqueue_script('wpdreams-scroll');
            wp_register_script('wpdreams-highlight', ASP_URL.'js/'.$js_source.'/jquery.highlight.js', array($prereq));
            wp_enqueue_script('wpdreams-highlight');
            if ($load_noui) {
                wp_register_script('wpdreams-nouislider', ASP_URL.'js/'.$js_source.'/jquery.nouislider.all.js', array($prereq));
                wp_enqueue_script('wpdreams-nouislider');
            }
            if ($load_isotope) {
                wp_register_script('wpdreams-rpp-isotope', ASP_URL.'js/'.$js_source.'/rpp_isotope.js', array($prereq));
                wp_enqueue_script('wpdreams-rpp-isotope');
            }
            wp_register_script('wpdreams-ajaxsearchpro', ASP_URL.'js/'.$js_source.'/jquery.ajaxsearchpro.js', array($prereq, "wpdreams-scroll"));
            wp_enqueue_script('wpdreams-ajaxsearchpro');
        } else {
            wp_enqueue_script('jquery');
            wp_register_script('wpdreams-ajaxsearchpro', ASP_URL."js/".$js_source."/jquery.ajaxsearchpro".$minify_string.".min.js");
            wp_enqueue_script('wpdreams-ajaxsearchpro');
        }
        
        $ajax_url = admin_url( 'admin-ajax.php');
        if (w_isset_def($comp_settings['usecustomajaxhandler'], 0) == 1) {
          $ajax_url = ASP_URL.'ajax_search.php';
        }
        
        wp_localize_script( 'wpdreams-ajaxsearchpro', 'ajaxsearchpro', array(
            'ajaxurl' => $ajax_url,
            'backend_ajaxurl' => admin_url( 'admin-ajax.php')
        ));

    }

    function fulltext() {
        global $wpdb;           
        $fulltext = wpdreams_fulltext::getInstance();  
        $tables = array('posts');
        $blogs = wpdreams_get_blog_list(0, 'all');

        update_option('asp_fulltext', 0);
        update_option('asp_fulltext_indexed', 0);

        if (is_multisite() && is_array($blogs) && count($blogs)) {
            foreach($blogs as $k=>$blog) {
                switch_to_blog($blog['blog_id']);
                if($fulltext->check($tables)) {
                    update_option('asp_fulltext', 1);
                }
            }
            restore_current_blog();
        } else {
            if($fulltext->check($tables)) {
                update_option('asp_fulltext', 1);
            }
        }
    }

    function chmod() {
        if (@chmod(ASP_CSS_PATH, 0777) == false)
            @chmod(ASP_CSS_PATH, 0755);
        if (@chmod(ASP_CACHE_PATH, 0777) == false)
            @chmod(ASP_CACHE_PATH, 0755);
        if (@chmod(ASP_TT_CACHE_PATH, 0777) == false)
            @chmod(ASP_TT_CACHE_PATH, 0755);
    }

    function footer() {

    }
}