<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

if (!class_exists('wpd_statisticsKeywordSuggest')) {
    class wpd_statisticsKeywordSuggest extends wpd_keywordSuggest {

        function __construct($maxCount = 10, $maxCharsPerWord = 25) {
            $this->maxCount = $maxCount;
            $this->maxCharsPerWord = $maxCharsPerWord;
        }

        function getKeywords($q) {
            global $wpdb;
            $keywords = array();
            $res = array();

            $_keywords = $wpdb->get_results("SELECT keyword FROM ".$wpdb->base_prefix."ajaxsearchpro_statistics WHERE keyword LIKE '".$q."%' ORDER BY num desc", ARRAY_A);

            foreach($_keywords as $k=>$v) {
                $keywords[] = $v['keyword'];
            }

            foreach ($keywords as $keyword) {
                $t = strtolower($keyword);
                if ($t != $q && ('' != $str = wd_substr_at_word($t, $this->maxCharsPerWord)) )
                    $res[] = $str;
            }

            return $res;
        }

    }
}