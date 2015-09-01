<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

// Override the default search hook, posts_results 

function asp_search_filter_posts($posts, $wp_query) {

    // Not working on paginated results, multisite
    /*if (!$wp_query->is_search())
        return $posts;*/

    if (empty($wp_query->query_vars['s'])) return $posts;

    if (isset($_POST['p_asp_data']) && $_POST['p_asp_data'] != '') {
        $_method = &$_POST;
        parse_str($_method['p_asp_data'], $s_data);
        $_POST['asid'] = $_POST['p_asid'];
    } else if (isset($_GET['asp_data']) && $_GET['asp_data'] != '') {
        $_method = &$_GET;
        parse_str(base64_decode($_method['asp_data']), $s_data);
        $_POST['asid'] = $s_data['asid'];
    } else {
        return $posts;
    }


    $_POST['options'] = $s_data;
    $_POST['aspp'] = $_GET['s'];
    $_POST['asp_get_as_array'] = 1;


    $post_ids = array();
    $blog_ids = array();

    require_once(ASP_PATH . '/search.php');

    $res = ajaxsearchpro_search(is_multisite());
    if (isset($res['_multi'])) {
        foreach ($res['_multi'] as $k => $v) {
            // Map the result ID's with the blog id
            if (isset($v->post_type)) {
                $post_ids[] = $v->id;
                $blog_ids[] = $v->blogid;
            }
        }
    } else {
        foreach ($res as $k => $v) {
            if (isset($v->post_type))
                $post_ids[] = $v->id;
        }
    }

    $paged = (get_query_var('paged') != 0) ? get_query_var('paged') : 1;
    $posts_per_page = (int)get_option('posts_per_page');

    //var_dump($paged);die();

    $mod_post_ids = array_slice($post_ids, (($paged-1) * $posts_per_page), $posts_per_page);
    if (count($blog_ids) > 0)
        $mod_blog_ids = array_slice($blog_ids, (($paged-1) * $posts_per_page), $posts_per_page);

    if (empty($mod_post_ids))
        $mod_post_ids[] = -123;

    /**
     * We need to gather the post types, because apparently, this is not working
     * correctly with the "any" argument in some cases...
     */
    $_post_types = array();

    if (isset($s_data['set_inposts']))
        $_post_types[] = 'post';
    if (isset($s_data['set_inpages']))
        $_post_types[] = 'page';
    if (isset($s_data['customset']) && is_array($s_data['customset']))
        $_post_types = array_merge($_post_types, $s_data['customset']);

    if (count($blog_ids) > 0) {
        $n_posts = array();
        foreach($mod_post_ids as $key=>$postid) {
            switch_to_blog($mod_blog_ids[$key]);
            $m_posts = get_posts(array(
                'posts_per_page' => $posts_per_page,
                'post__in' => array($postid),
                'orderby' => 'post__in',
                'ignore_sticky_posts' => true,
                'post_type' => $_post_types
            ));
            // Save the right permalink
            foreach($m_posts as $kk=>$vv) {
                $m_posts[$kk]->asp_guid =  apply_filters('the_permalink', $vv->guid);
            }
            if (is_array($m_posts) && count($m_posts)>0)
                $n_posts = array_merge($n_posts, $m_posts);
            restore_current_blog();
        }

    } else {
        $n_posts = get_posts(array(
            'posts_per_page' => $posts_per_page,
            'post__in' => $mod_post_ids,
            'orderby' => 'post__in',
            'ignore_sticky_posts' => true,
            'post_type' => 'any'
        ));
    }

    $wp_query->found_posts = count($post_ids);
    if (($wp_query->found_posts / $posts_per_page) > 1)
        $wp_query->max_num_pages = floor($wp_query->found_posts / $posts_per_page) + 1;
    else
        $wp_query->max_num_pages = 0;

    return $n_posts;
}

/* Fix the correct permalink */
function asp_fix_multisite_link( $url, $post, $leavename ) {
    if (isset($post->asp_guid))
        return $post->asp_guid;
    return $url;
}

add_filter('post_link', 'asp_fix_multisite_link', 10, 3 );
add_filter('posts_results', 'asp_search_filter_posts', 1, 2);


function asp_order_posts($posts, $order) {
    $result = array();
    foreach ($order as $id) {
        $i = 0;
        foreach ($posts as $post) {
            if ($post->ID == $id) {
                array_push($result, $post);
                unset($posts[$i]);
                $posts = array_values($posts);
            }
            $i++;
        }
    }
    return $result;
}

function wpdreams_asp_echo_out() {
    global $asp_head_out;
    ?>
    <style type="text/css" xmlns="http://www.w3.org/1999/html">
        <?php echo $asp_head_out; ?>
    </style>
<?php
}

function search_stylesheets() {
    global $wpdb;
    global $asp_head_out;

    if (function_exists('get_current_screen')) {
        $screen = get_current_screen();
        if (isset($screen) && isset($screen->id) && $screen->id == 'widgets')
            return;
    }

    if (isset($wpdb->base_prefix)) {
        $_prefix = $wpdb->base_prefix;
    } else {
        $_prefix = $wpdb->prefix;
    }
    $search = $wpdb->get_results("SELECT * FROM " . $_prefix . "ajaxsearchpro", ARRAY_A);
    if (!is_array($search) || count($search)<=0)
        return;

    $comp_settings = get_option('asp_compatibility');
    $force_inline = w_isset_def($comp_settings['forceinlinestyles'], false);

    wp_enqueue_style('wpdreams_animations', plugins_url('css/animations.css', dirname(__FILE__)), false);
    wp_register_style('wpdreams-asp-basic', ASP_URL . 'css/style.basic.css', true);
    wp_enqueue_style('wpdreams-asp-basic');

    if (ASP_DEBUG == 1) {

        asp_generate_the_css();
        $css = get_option('asp_styles_base64');
        $asp_head_out = base64_decode($css);
        add_action('wp_head', 'wpdreams_asp_echo_out', 10, 0);
        return;

    } else if ($force_inline == 1) {

        $css = get_option('asp_styles_base64');
        if ($css === false || $css == '') {
            asp_generate_the_css();
            $css = get_option('asp_styles_base64');
            // If it's still false, we have a problem
            if ($css === false || $css == '') return;
        }

        $asp_head_out = base64_decode($css);
        add_action('wp_head', 'wpdreams_asp_echo_out', 10, 0);
        return;

    } else if (!file_exists(ASP_CSS_PATH . "/style.instances.css") || @filesize(ASP_CSS_PATH . "/style.instances.css")<1025) {
        /* Check if the CSS exists, if not, then try to force-create it */
        asp_generate_the_css();
        // Check again, if doesn't exist, we need to force inline styles
        if (!file_exists(ASP_CSS_PATH . "/style.instances.css") || @filesize(ASP_CSS_PATH . "/style.instances.css")<1025) {
            $css = get_option('asp_styles_base64');
            // Still no CSS? Problem.
            if ($css === false || $css == '')
                return;
            $asp_head_out = base64_decode($css);
            add_action('wp_head', 'wpdreams_asp_echo_out', 10, 0);

            // Save the force inline
            $comp_settings['forceinlinestyles'] = 1;
            update_option('asp_compatibility', $comp_settings);

            return;
        }
    }

    wp_enqueue_style('wpdreams-ajaxsearchpro-instances', ASP_URL . 'css/style.instances.css', false);

}

add_action('wp_enqueue_scripts', 'search_stylesheets');