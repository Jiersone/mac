<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

if (!class_exists('wpd_tagsKeywordSuggest')) {
    class wpd_tagsKeywordSuggest extends wpd_keywordSuggest {

        function __construct($maxCount = 10, $maxCharsPerWord = 25) {
            $this->maxCount = $maxCount;
            $this->maxCharsPerWord = $maxCharsPerWord;
        }

        function getKeywords($q) {
            $res = array();
            $tags = get_tags(array('search' => $q, 'number'=>$this->maxCount));
            foreach ($tags as $tag) {
                $t = strtolower($tag->name);
                if ($t != $q && ('' != $str = wd_substr_at_word($t, $this->maxCharsPerWord)) )
                    $res[] = $str;
            }
            return $res;
        }

    }
}