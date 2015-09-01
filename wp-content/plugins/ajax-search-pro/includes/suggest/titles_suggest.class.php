<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

if (!class_exists('wpd_titlesKeywordSuggest')) {
    class wpd_titlesKeywordSuggest extends wpd_keywordSuggest {

        private $res = array();

        function __construct($maxCount = 10, $maxCharsPerWord = 25) {
            $this->maxCount = $maxCount;
            $this->maxCharsPerWord = $maxCharsPerWord;
        }

        function getKeywords($q) {
            if (strlen($q) > 3) {
                $this->getResults($q);
                $this->getResults(substr($q, 0, -1));
                $this->getResults(substr($q, 1));
            } else {
                $this->getResults($q);
            }

            return $this->res;
        }

        function getResults($q) {
            $count = $this->maxCount - count($this->res);
            if ($count <= 0) return;
            $args = array('s' => "%".$q, 'exact'=>1, 'posts_per_page' => $count);
            $the_query = new WP_Query( $args );
            // The Loop
            if ( $the_query->have_posts() ) {
                while ( $the_query->have_posts() ) {
                    $the_query->the_post();
                    $t = strtolower(get_the_title());
                    if (
                        $q != $t  &&
                        !in_array($t, $this->res) &&
                        ('' != $str = wd_substr_at_word($t, $this->maxCharsPerWord))
                    )
                        $this->res[] = $str;
                }
            }
        }

    }
}