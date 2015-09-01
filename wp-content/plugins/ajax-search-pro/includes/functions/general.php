<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

/**
 * General functions regarding Ajax Search Pro
 */

function get_search_instances() {
    global $wpdb;
    if (isset($wpdb->base_prefix)) {
        $_prefix = $wpdb->base_prefix;
    } else {
        $_prefix = $wpdb->prefix;
    }
    return $wpdb->get_results("SELECT * FROM " . $_prefix . "ajaxsearchpro", ARRAY_A);
}

function get_search_instances_and_data() {
    $instances = get_search_instances();
    foreach ($instances as $k => $instance) {
        $instances[$k]['data'] = json_decode($instance['data'], true);
    }
    return $instances;
}

/**
 * Converts the results array to HTML code
 *
 * Since ASP 4.0 the results are returned as plain HTML codes instead of JSON
 * to allow templating. This function includes the needed template files
 * to generate the correct HTML code. Supports grouping.
 *
 * @since 4.0
 * @param $results
 * @param $s_options
 * @param string $theme
 * @return string
 */
function asp_generate_html_results($results, $s_options, $theme='vertical') {
    $html = "";
    if (empty($results) || !empty($results['nores'])) {
        if (!empty($results['keywords'])) {
            $s_keywords = $results['keywords'];
            // Get the keyword suggestions template
            ob_start();
            include(ASP_INCLUDES_PATH . "views/results/keyword-suggestions.php");
            $html .= ob_get_clean();
        } else {
            // No results at all.
            ob_start();
            include(ASP_INCLUDES_PATH . "views/results/no-results.php");
            $html .= ob_get_clean();
        }
    } else {
        if (isset($results['grouped'])) {
            foreach($results['items'] as $k=>$g) {
                $group_name = $g['name'];

                // Get the group headers
                ob_start();
                include(ASP_INCLUDES_PATH . "views/results/group-header.php");
                $html .= ob_get_clean();

                // Get the item HTML
                foreach($g['data'] as $kk=>$r) {
                    ob_start();
                    include(ASP_INCLUDES_PATH . "views/results/" . $theme . ".php");
                    $html .= ob_get_clean();
                }

                // Get the gorup footers
                ob_start();
                include(ASP_INCLUDES_PATH . "views/results/group-footer.php");
                $html .= ob_get_clean();
            }
        } else {
            // Get the item HTML
            foreach($results as $k=>$r) {
                ob_start();
                include(ASP_INCLUDES_PATH . "views/results/" . $theme . ".php");
                $html .= ob_get_clean();
            }
        }
    }
    return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $html);
}