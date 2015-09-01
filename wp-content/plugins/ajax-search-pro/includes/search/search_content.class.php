<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

if (!class_exists('wpdreams_searchContent')) {
    class wpdreams_searchContent extends wpdreams_search {

        protected $parts = array();
        protected $cf_parts = array();
        protected $remaining_limit;
        protected $query;

        protected function do_search() {
            global $wpdb;
            global $q_config;

            $options = $this->options;
            $comp_options = get_option('asp_compatibility');
            $searchData = $this->searchData;

            // General variables
            $parts = array();
            $relevance_parts = array();
            $types = array();
            $post_types = "";
            $term_query = "";
            $post_statuses = "";
            $all_pageposts = array();

            // Prefixes and suffixes
            $pre_field = '';
            $suf_field = '';
            $pre_like = '';
            $suf_like = '';

            /**
             *  On forced case sensitivity: Let's add BINARY keyword before the LIKE
             *  On forced case in-sensitivity: Append the lower() function around each field
             */
            if (w_isset_def($comp_options['db_force_case'], 'none') == 'sensitivity') {
                $pre_like = 'BINARY ';
            } else if (w_isset_def($comp_options['db_force_case'], 'none') == 'insensitivity') {
                if (function_exists('mb_convert_case'))
                    $this->s = mb_convert_case($this->s, MB_CASE_LOWER, "UTF-8");
                else
                    $this->s = strtoupper($this->s); // if no mb_ functions :(
                $this->_s = explode(" ", $this->s);

                $pre_field .= 'lower(';
                $suf_field .= ')';
            }

            /**
             *  Check if utf8 is forced on LIKE
             */
            if (w_isset_def($comp_options['db_force_utf8_like'], 0) == 1) {
                $pre_like .= '_utf8';
            }

            /**
             *  Check if unicode is forced on LIKE, but only apply if utf8 is not
             */
            if (w_isset_def($comp_options['db_force_unicode'], 0) == 1
             && w_isset_def($comp_options['db_force_utf8_like'], 0) == 0) {
                $pre_like .= 'N';
            }


            $kw_logic = w_isset_def($searchData['keyword_logic'], 'or');
            $q_config['language'] = $options['qtranslate_lang'];

            $s = $this->s; // full keyword
            $_s = $this->_s; // array of keywords


            if (isset($wpdb->base_prefix)) {
                $_prefix = $wpdb->base_prefix;
            } else {
                $_prefix = $wpdb->prefix;
            }

            $this->remaining_limit = $searchData['maxresults'];

            /**
             *  Use separate queries if we have more than 1000 rows.
             */
            $wpdb->get_results("SELECT COUNT(*) FROM $wpdb->posts");
            $use_separate_queries = $wpdb->num_rows > 1000;

            /*------------------------- Statuses ----------------------------*/
            $statuses = array('publish');
            if ($searchData['searchinpending'])
                $statuses[] = 'pending';
            if ($searchData['searchindrafts'])
                $statuses[] = 'draft';
            $words = implode('|', $statuses);
            $post_statuses = "(".$pre_field.$wpdb->posts.".post_status".$suf_field." REGEXP '$words')";
            /*---------------------------------------------------------------*/

            /*----------------------- Gather Types --------------------------*/
            if ($options['set_inposts'] == 1)
                $types[] = "post";
            if ($options['set_inpages'])
                $types[] = "page";
            if (isset($options['customset']) && count($options['customset']) > 0)
                $types = array_merge($types, $options['customset']);
            if (count($types) < 1) {
                return '';
            } else {
                $words = implode('[[:>:]]|[[:<:]]', $types);
                $post_types = "($wpdb->posts.post_type REGEXP '[[:<:]]" . $words . "[[:>:]]')";
            }
            /*---------------------------------------------------------------*/


            // ------------------------ Categories/taxonomies ----------------------
            if (!isset($options['categoryset']) || $options['categoryset'] == "")
                $options['categoryset'] = array();
            if (!isset($options['termset']) || $options['termset'] == "")
                $options['termset'] = array();

            /*
                By default it's 'AND', so all the categories must fit in order to show
                that result.
            */
            $term_logic = w_isset_def($searchData['term_logic'], 'and');

            $exclude_categories = array();
            $searchData['selected-exsearchincategories'] = w_isset_def($searchData['selected-exsearchincategories'], array());
            $searchData['selected-excludecategories'] = w_isset_def($searchData['selected-excludecategories'], array());

            if (count($searchData['selected-exsearchincategories']) > 0 ||
                count($searchData['selected-excludecategories']) > 0 ||
                count($options['categoryset']) > 0
            ) {

                aspDebug::start('--searchContent-categories');

                $_all_cat = get_terms('category', array('fields' => 'ids'));
                $_needed_cat = array_diff($_all_cat, $searchData['selected-exsearchincategories']);
                $_needed_cat = !is_array($_needed_cat) ? array() : $_needed_cat;

                if ($term_logic == 'and')
                    $exclude_categories = array_diff(array_merge($_needed_cat, $searchData['selected-excludecategories']), $options['categoryset']);
                else
                    $exclude_categories = $options['categoryset'];

                aspDebug::stop('--searchContent-categories');
            }

            $exclude_terms = array();
            $exclude_showterms = array();
            $searchData['selected-showterms'] = w_isset_def($searchData['selected-showterms'], array());
            $searchData['selected-excludeterms'] = w_isset_def($searchData['selected-excludeterms'], array());

            if (count($searchData['selected-showterms']) > 0 ||
                count($searchData['selected-excludeterms']) > 0 ||
                count($options['termset']) > 0
            ) {

                aspDebug::start('--searchContent-terms');

                foreach ($searchData['selected-excludeterms'] as $tax => $terms) {
                    $exclude_terms = array_merge($exclude_terms, $terms);
                }
                foreach ($searchData['selected-showterms'] as $tax => $terms) {
                    $exclude_showterms = array_merge($exclude_showterms, $terms);
                }

                if ($term_logic == 'and')
                    $exclude_terms = array_diff(array_merge($exclude_terms, $exclude_showterms), $options['termset']);
                else
                    $exclude_terms = $options['termset'];

                aspDebug::stop('--searchContent-terms');

            }

            $all_terms = array();
            $all_terms = array_merge($exclude_categories, $exclude_terms);

            if (count($all_terms) > 0) {
                $words = '--' . implode('--|--', $all_terms) . '--';
                if ($term_logic == 'and')
                    $term_query = "HAVING (ttid NOT REGEXP '$words')";
                else
                    $term_query = "HAVING (ttid REGEXP '$words')";
            }
            // ---------------------------------------------------------------------


            /*------------- Custom Fields with Custom selectors -------------*/
            if (isset($options['aspf']) && isset($options['aspfdata'])) {

                aspDebug::start('--searchContent-cf');

                $parts = array();

                foreach ($options['aspfdata'] as $u_data) {
                    $data = json_decode(base64_decode($u_data));
                    $posted = $options['aspf'][$data->asp_f_field];

                    $ll_like = "";
                    $rr_like = "";

                    if (isset($data->asp_f_operator)) {
                        switch ($data->asp_f_operator) {
                            case 'eq':
                                $operator = "=";
                                break;
                            case 'neq':
                                $operator = "<>";
                                break;
                            case 'lt':
                                $operator = "<";
                                break;
                            case 'gt':
                                $operator = ">";
                                break;
                            case 'elike':
                                $operator = "=";
                                $ll_like = "'";
                                $rr_like = "'";
                                break;
                            case 'like':
                                $operator = "LIKE";
                                $ll_like = "'%";
                                $rr_like = "%'";
                                break;
                            default:
                                $operator = "=";
                                break;
                        }
                    }

                    //var_dump($data);
                    /*if (w_isset_def($searchData['cf_null_values'], 1) == 1) {
                        $cf_key_is_null .= " OR $wpdb->postmeta.meta_key IS NULL";
                        $cf_val_is_null .= " OR $wpdb->postmeta.meta_value IS NULL";
                    }*/

                    if ($data->asp_f_type == 'range' && isset($posted['lower'])) {
                        $parts[] = " ( ($wpdb->postmeta.meta_key='$data->asp_f_field') AND
                                     ($wpdb->postmeta.meta_value BETWEEN " . $posted['lower'] . " AND " . $posted['upper'] . " ) )";
                    } else if ($data->asp_f_type == 'slider' && isset($posted)) {
                        $parts[] = " ( ($wpdb->postmeta.meta_key='$data->asp_f_field') AND
                                     ($wpdb->postmeta.meta_value $operator $posted  ) )";
                    } else if ($data->asp_f_type == 'radio' && isset($posted)) {
                        $parts[] = " ( ($wpdb->postmeta.meta_key='$data->asp_f_field') AND
                                     ($wpdb->postmeta.meta_value $operator " . $ll_like . $posted . $rr_like . " ) )";
                    } else if ($data->asp_f_type == 'dropdown' && isset($posted)) {
                        if (isset($data->asp_f_dropdown_multi) && count($posted) > 0) {
                            // The AND logic doesn't make any sense
                            $logic = 'OR';
                            $values = '';
                            foreach ($posted as $v) {
                                if ($values != '')
                                    $values .= " $logic $wpdb->postmeta.meta_value $operator " . $ll_like . $v . $rr_like;
                                else
                                    $values .= "$wpdb->postmeta.meta_value $operator " . $ll_like . $v . $rr_like;
                            }

                            $values = $values == '' ? '0' : $values;
                            $parts[] = "( ($wpdb->postmeta.meta_key='$data->asp_f_field') AND ($values) )";
                        } else {
                            $parts[] = "( ($wpdb->postmeta.meta_key='$data->asp_f_field') AND
                                        ($wpdb->postmeta.meta_value $operator " . $ll_like . $posted . $rr_like . " ) )";
                        }

                    } else if ($data->asp_f_type == 'checkboxes' && isset($posted)) {

                        $logic = $data->asp_f_checkboxes_logic;
                        $values = '';
                        foreach ($posted as $v => $vv) {
                            if ($values != '')
                                $values .= " $logic $wpdb->postmeta.meta_value $operator " . $ll_like . $v . $rr_like;
                            else
                                $values .= "$wpdb->postmeta.meta_value $operator " . $ll_like . $v . $rr_like;
                        }
                        $values = $values == '' ? '0' : $values;
                        $parts[] = "( ($wpdb->postmeta.meta_key='$data->asp_f_field') AND ($values) )";

                    }

                }

                $this->cf_parts = $parts;

                aspDebug::stop('--searchContent-cf');
            }

            $meta_count = count($this->cf_parts);

            $cf_query = implode(" OR ", $this->cf_parts);
            if ($cf_query == "") {
                $cf_select = "0";
                $cf_having = "";
            } else {
                $cf_select = "
                SELECT COUNT(*) FROM $wpdb->postmeta WHERE
                    $wpdb->postmeta.post_id = $wpdb->posts.ID
                AND
                    ($cf_query)";
                if (w_isset_def($searchData['cf_logic'], 'AND') == 'AND')
                    $cf_having = "meta_matches >= " . $meta_count;
                else
                    $cf_having = "meta_matches >= 1";
                if ($term_query != '')
                    $cf_having = " AND " . $cf_having;
                else
                    $cf_having = "HAVING " . $cf_having;
            }
            /*---------------------------------------------------------------*/


            /*------------------------ Exclude id's -------------------------*/
            if (isset($searchData['excludeposts']) && $searchData['excludeposts'] != "") {
                $exclude_posts = "($wpdb->posts.ID NOT IN (" . $searchData['excludeposts'] . "))";
            } else {
                $exclude_posts = "($wpdb->posts.ID NOT IN (-55))";
            }
            /*---------------------------------------------------------------*/


            /*------------------------- WPML filter -------------------------*/
            $wpml_join = "";
            if (defined('ICL_LANGUAGE_CODE')
                && ICL_LANGUAGE_CODE != ''
                && defined('ICL_SITEPRESS_VERSION')
                && w_isset_def($searchData['wpml_compatibility'], 1) == 1
            )
                $wpml_join = "RIGHT JOIN " . $wpdb->base_prefix . "icl_translations ON (
                    $wpdb->posts.ID = " . $wpdb->base_prefix . "icl_translations.element_id AND
                    " . $wpdb->base_prefix . "icl_translations.language_code = '" . ICL_LANGUAGE_CODE . "' AND
                    " . $wpdb->base_prefix . "icl_translations.element_type LIKE '%post_%'
                )";
            /*---------------------------------------------------------------*/


            /**
             * This is the main query.
             *
             * The ttid field is a bit tricky as the term_taxonomy_id doesn't always equal term_id,
             * so we need the LEFT JOINS :(
             */
            $orderby = w_isset_def($searchData['orderby'], "post_date DESC");
            $this->query = "
    		SELECT 
            $wpdb->posts.post_title as title,
            $wpdb->posts.ID as id,
            $wpdb->posts.post_date as date,
            $wpdb->posts.post_content as content,
            $wpdb->posts.post_excerpt as excerpt,
            $wpdb->posts.post_type as post_type,
            'pagepost' as content_type,
            $wpdb->users.user_nicename as author,
            ($cf_select) as meta_matches,
            (SELECT DISTINCT CONCAT('--', GROUP_CONCAT( $wpdb->term_taxonomy.term_id SEPARATOR '----' ), '--') FROM $wpdb->term_taxonomy
                LEFT JOIN $wpdb->term_relationships ON $wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id
                WHERE ($wpdb->term_relationships.object_id = $wpdb->posts.ID) ) as ttid,
            $wpdb->posts.post_type as post_type,
            CASE WHEN " . $_prefix . "ajaxsearchpro_priorities.priority IS NULL
                   THEN 100
                   ELSE " . $_prefix . "ajaxsearchpro_priorities.priority
            END AS priority,
            {relevance_query} as relevance
            FROM $wpdb->posts
            LEFT JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id = $wpdb->posts.ID
            LEFT JOIN " . $_prefix . "ajaxsearchpro_priorities ON (" . $_prefix . "ajaxsearchpro_priorities.post_id = $wpdb->posts.ID AND " . $_prefix . "ajaxsearchpro_priorities.blog_id = " . get_current_blog_id() . ")
            LEFT JOIN $wpdb->users ON $wpdb->users.ID = $wpdb->posts.post_author
            LEFT JOIN $wpdb->term_relationships ON $wpdb->posts.ID = $wpdb->term_relationships.object_id
            LEFT JOIN $wpdb->term_taxonomy ON $wpdb->term_taxonomy.term_taxonomy_id = $wpdb->term_relationships.term_taxonomy_id
            LEFT JOIN $wpdb->terms ON $wpdb->term_taxonomy.term_id = $wpdb->terms.term_id
            $wpml_join
            WHERE
                    $post_types
                AND $post_statuses
                AND {like_query}
                AND $exclude_posts
            GROUP BY
                $wpdb->posts.ID
                $term_query
                $cf_having
            ORDER BY priority DESC, relevance DESC, " . $wpdb->posts . "." . $orderby . "
            LIMIT {remaining_limit}";


            $words = $options['set_exactonly'] == 1 ? array($s) : $_s;
            $regexp_words = count($_s > 0) ? implode('|', $_s) : $s;

            /*----------------------- Title query ---------------------------*/
            if ($options['set_intitle']) {
                $parts = array();
                $relevance_parts = array();

                if ($kw_logic == 'or' || $kw_logic == 'and') {
                    $op = strtoupper($kw_logic);
                    if (count($_s) > 0)
                        $_like = implode("%'$suf_like " . $op . " ".$pre_field.$wpdb->posts.".post_title".$suf_field." LIKE $pre_like'%", $words);
                    else
                        $_like = $s;
                    $parts[] = "( ".$pre_field.$wpdb->posts.".post_title".$suf_field." LIKE $pre_like'%" . $_like . "%'$suf_like )";
                } else {
                    $_like = array();
                    $op = $kw_logic == 'andex' ? 'AND' : 'OR';
                    foreach ($words as $word) {
                        $_like[] = "
                           ( ".$pre_field.$wpdb->posts.".post_title".$suf_field." LIKE $pre_like'% " . $word . " %'$suf_like
                        OR  ".$pre_field.$wpdb->posts.".post_title".$suf_field." LIKE $pre_like'" . $word . " %'$suf_like
                        OR  ".$pre_field.$wpdb->posts.".post_title".$suf_field." LIKE $pre_like'% " . $word . "'$suf_like
                        OR  ".$pre_field.$wpdb->posts.".post_title".$suf_field." = '" . $word . "')";
                    }
                    $parts[] = "(" . implode(' ' . $op . ' ', $_like) . ")";
                }

                /*$relevance_parts[] = "(case when
                (".$pre_field.$wpdb->posts.".post_title".$suf_field." REGEXP '$regexp_words')
                 then $searchData[titleweight] else 0 end)";*/
                $relevance_parts[] = "(case when
                (".$pre_field.$wpdb->posts.".post_title".$suf_field." LIKE '%$s%')
                 then $searchData[etitleweight] else 0 end)";

                // The first word relevance is higher
                if (count($_s) > 0)
                    $relevance_parts[] = "(case when
                  (".$pre_field.$wpdb->posts.".post_title".$suf_field." LIKE '%" . $_s[0] . "%')
                   then $searchData[etitleweight] else 0 end)";

                $this->parts[] = array($parts, $relevance_parts);
            }
            /*---------------------------------------------------------------*/

            /*---------------------- Content query --------------------------*/
            if ($options['set_incontent']) {
                $parts = array();
                $relevance_parts = array();

                if ($kw_logic == 'or' || $kw_logic == 'and') {
                    $op = strtoupper($kw_logic);
                    if (count($_s) > 0)
                        $_like = implode("%'$suf_like " . $op . " ".$pre_field.$wpdb->posts.".post_content".$suf_field." LIKE $pre_like'%", $words);
                    else
                        $_like = $s;
                    $parts[] = "( ".$pre_field.$wpdb->posts.".post_content".$suf_field." LIKE $pre_like'%" . $_like . "%'$suf_like )";
                } else {
                    $_like = array();
                    $op = $kw_logic == 'andex' ? 'AND' : 'OR';
                    foreach ($words as $word) {
                        $_like[] = "
                           (".$pre_field.$wpdb->posts.".post_content".$suf_field." LIKE $pre_like'% " . $word . " %'$suf_like
                        OR  ".$pre_field.$wpdb->posts.".post_content".$suf_field." LIKE $pre_like'" . $word . " %'$suf_like
                        OR  ".$pre_field.$wpdb->posts.".post_content".$suf_field." LIKE $pre_like'% " . $word . "'$suf_like
                        OR  ".$pre_field.$wpdb->posts.".post_content".$suf_field." = '" . $word . "')";
                    }
                    $parts[] = "(" . implode(' ' . $op . ' ', $_like) . ")";
                }

                if (count($_s) > 0)
                    $relevance_parts[] = "(case when
                    (".$pre_field.$wpdb->posts.".post_content".$suf_field." LIKE '%".$_s[0]."%')
                     then $searchData[contentweight] else 0 end)";
                $relevance_parts[] = "(case when
                (".$pre_field.$wpdb->posts.".post_content".$suf_field." LIKE '%$s%')
                 then $searchData[econtentweight] else 0 end)";

                $this->parts[] = array($parts, $relevance_parts);
            }
            /*---------------------------------------------------------------*/

            /*---------------------- Excerpt query --------------------------*/
            if ($options['set_inexcerpt']) {
                $parts = array();
                $relevance_parts = array();

                if ($kw_logic == 'or' || $kw_logic == 'and') {
                    $op = strtoupper($kw_logic);
                    if (count($_s) > 0)
                        $_like = implode("%'$suf_like " . $op . " ".$pre_field.$wpdb->posts.".post_excerpt".$suf_field." LIKE $pre_like'%", $words);
                    else
                        $_like = $s;
                    $parts[] = "( ".$pre_field.$wpdb->posts.".post_excerpt".$suf_field." LIKE $pre_like'%" . $_like . "%'$suf_like )";
                } else {
                    $_like = array();
                    $op = $kw_logic == 'andex' ? 'AND' : 'OR';
                    foreach ($words as $word) {
                        $_like[] = "
                           (".$pre_field.$wpdb->posts.".post_excerpt".$suf_field." LIKE $pre_like'% " . $word . " %'$suf_like
                        OR  ".$pre_field.$wpdb->posts.".post_excerpt".$suf_field." LIKE $pre_like'" . $word . " %'$suf_like
                        OR  ".$pre_field.$wpdb->posts.".post_excerpt".$suf_field." LIKE $pre_like'% " . $word . "'$suf_like
                        OR  ".$pre_field.$wpdb->posts.".post_excerpt".$suf_field." = '" . $word . "')";
                    }
                    $parts[] = "(" . implode(' ' . $op . ' ', $_like) . ")";
                }

                if (count($_s) > 0)
                    $relevance_parts[] = "(case when
                    (".$pre_field.$wpdb->posts.".post_excerpt".$suf_field." LIKE '%".$_s[0]."%')
                     then $searchData[excerptweight] else 0 end)";
                $relevance_parts[] = "(case when
                (".$pre_field.$wpdb->posts.".post_excerpt".$suf_field." LIKE '%$s%')
                 then $searchData[eexcerptweight] else 0 end)";

                $this->parts[] = array($parts, $relevance_parts);
            }
            /*---------------------------------------------------------------*/

            /*------------------------ Term query ---------------------------*/
            if ($options['searchinterms']) {
                $parts = array();
                $relevance_parts = array();

                if ($kw_logic == 'or' || $kw_logic == 'and') {
                    $op = strtoupper($kw_logic);
                    if (count($_s) > 0)
                        $_like = implode("%'$suf_like " . $op . " ".$pre_field.$wpdb->terms.".name".$suf_field." LIKE $pre_like'%", $words);
                    else
                        $_like = $s;
                    $parts[] = "( ".$pre_field.$wpdb->terms.".name".$suf_field." LIKE $pre_like'%" . $_like . "%'$suf_like )";
                } else {
                    $_like = array();
                    $op = $kw_logic == 'andex' ? 'AND' : 'OR';
                    foreach ($words as $word) {
                        $_like[] = "
                           (".$pre_field.$wpdb->terms.".name".$suf_field." LIKE $pre_like'% " . $word . " %'$suf_like
                        OR  ".$pre_field.$wpdb->terms.".name".$suf_field." LIKE $pre_like'" . $word . " %'$suf_like
                        OR  ".$pre_field.$wpdb->terms.".name".$suf_field." LIKE $pre_like'% " . $word . "'$suf_like
                        OR  ".$pre_field.$wpdb->terms.".name".$suf_field." = '" . $word . "')";
                    }
                    $parts[] = "(" . implode(' ' . $op . ' ', $_like) . ")";
                }


                /*$relevance_parts[] = "(case when
                (".$pre_field.$wpdb->terms.".name".$suf_field." REGEXP '$regexp_words')
                 then $searchData[termsweight] else 0 end)";*/
                $relevance_parts[] = "(case when
                (".$pre_field.$wpdb->terms.".name".$suf_field." = '$s')
                 then $searchData[etermsweight] else 0 end)";

                $this->parts[] = array($parts, $relevance_parts);
            }
            /*---------------------------------------------------------------*/

            /*---------------------- Custom Fields --------------------------*/
            if (isset($searchData['selected-customfields'])) {
                $parts = array();
                $relevance_parts = array();

                $selected_customfields = $searchData['selected-customfields'];
                if (is_array($selected_customfields) && count($selected_customfields) > 0) {
                    foreach ($selected_customfields as $cfield) {
                        if ($kw_logic == 'or' || $kw_logic == 'and') {
                            $op = strtoupper($kw_logic);
                            if (count($_s) > 0)
                                $_like = implode("%'$suf_like " . $op . " ".$pre_field.$wpdb->postmeta.".meta_value".$suf_field." LIKE $pre_like'%", $words);
                            else
                                $_like = $s;
                            $parts[] = "( $wpdb->postmeta.meta_key='$cfield' AND ".$pre_field.$wpdb->postmeta.".meta_value".$suf_field." LIKE $pre_like'%" . $_like . "%'$suf_like )";
                        } else {
                            $_like = array();
                            $op = $kw_logic == 'andex' ? 'AND' : 'OR';
                            foreach ($words as $word) {
                                $_like[] = "
                           (".$pre_field.$wpdb->postmeta.".meta_value".$suf_field." LIKE $pre_like'% " . $word . " %'$suf_like
                        OR  ".$pre_field.$wpdb->postmeta.".meta_value".$suf_field." LIKE $pre_like'" . $word . " %'$suf_like
                        OR  ".$pre_field.$wpdb->postmeta.".meta_value".$suf_field." LIKE $pre_like'% " . $word . "'$suf_like
                        OR  ".$pre_field.$wpdb->postmeta.".meta_value".$suf_field." = '" . $word . "')";
                            }
                            $parts[] = "( $wpdb->postmeta.meta_key='$cfield' AND (" . implode(' ' . $op . ' ', $_like) . ") )";
                        }

                    }
                }
                $this->parts[] = array($parts, $relevance_parts);
            }
            /*---------------------------------------------------------------*/


            if ($use_separate_queries) {
                $i = 1;
                foreach ($this->parts as $part) {
                    $querystr = $this->build_query($part);
                    //var_dump($querystr); die();
                    aspDebug::start('--searchContent-query' . $i);
                    $pageposts = $wpdb->get_results($querystr, OBJECT);
                    aspDebug::stop('--searchContent-query' . $i);
                    $diff = array_udiff($pageposts, $all_pageposts, array($this, 'compare_posts'));
                    //$diff = array_diff(, $all_pageposts);
                    $all_pageposts = array_merge($all_pageposts, $diff);

                    $this->remaining_limit -= count($pageposts);
                    if ($this->remaining_limit <= 0) break;
                    $i++;
                }
            } else if (count($this->parts) > 0) {
                //var_dump($this->parts);
                $querystr = $this->build_query($this->parts, true);
                //var_dump($querystr);die("!!");
                $all_pageposts = $wpdb->get_results($querystr, OBJECT);
            }

            if (count($all_pageposts) > 0) {
                if (w_isset_def($searchData['userelevance'], 1) == 1) {
                    usort($all_pageposts, array($this, 'compare_by_rp'));
                } else if ($orderby == 'post_date DESC') {
                    usort($all_pageposts, array($this, 'compare_by_rd_desc'));
                } else if ($orderby == 'post_date ASC') {
                    usort($all_pageposts, array($this, 'compare_by_rd_asc'));
                } else if ($orderby == 'post_title DESC') {
                    usort($all_pageposts, array($this, 'compare_by_title_desc'));
                } else {
                    usort($all_pageposts, array($this, 'compare_by_title_asc'));
                }
            }

            $this->results = $all_pageposts;

            return $all_pageposts;

        }

        function compare_posts($obj_a, $obj_b) {
            return $obj_a->id - $obj_b->id;
        }

        function compare_by_rp($a, $b) {
            if ($a->priority === $b->priority) {
                if ($a->relevance === $b->relevance)
                    if ($a->date != null && $a->date != "")
                        return strtotime($b->date) - strtotime($a->date);
                    else
                        return strcmp($a->title, $b->title);
                else
                    return $b->relevance - $a->relevance;
            }
            return $b->priority - $a->priority;
        }

        function compare_by_rd_asc($a, $b) {
            if ($a->priority === $b->priority) {
                return strtotime($a->date) - strtotime($b->date);
            }
            return $b->priority - $a->priority;
        }

        function compare_by_rd_desc($a, $b) {
            if ($a->priority === $b->priority) {
                return strtotime($b->date) - strtotime($a->date);
            }
            return $b->priority - $a->priority;
        }

        function compare_by_title_desc($a, $b) {
            return strcmp($b->title, $a->title);
        }

        function compare_by_title_asc($a, $b) {
            return strcmp($a->title, $b->title);
        }


        protected function build_query($parts, $is_multi = false) {

            $searchData = $this->searchData;

            $l_parts = array();
            $r_parts = array();

            if ($is_multi == true) {
                foreach ($parts as $part) {
                    $l_parts = array_merge($l_parts, $part[0]);
                    $r_parts = array_merge($r_parts, $part[1]);
                }
            } else {
                $l_parts = $parts[0];
                $r_parts = $parts[1];
            }

            //var_dump($l_parts);var_dump($r_parts);

            /*------------------------- Build like --------------------------*/
            $like_query = implode(' OR ', $l_parts);
            if ($like_query == "")
                $like_query = "(1)";
            else {
                $like_query = "($like_query)";
            }
            /*---------------------------------------------------------------*/

            /*---------------------- Build relevance ------------------------*/
            $relevance = implode(' + ', $r_parts);
            if ($searchData['userelevance'] != 1 || $relevance == "")
                $relevance = "(1)";
            else {
                $relevance = "($relevance)";
            }
            /*---------------------------------------------------------------*/


            return str_replace(
                array("{relevance_query}", "{like_query}", "{remaining_limit}"),
                array($relevance, $like_query, $this->remaining_limit),
                $this->query
            );

        }

        protected function post_process() {

            $pageposts = is_array($this->results) ? $this->results : array();
            $options = $this->options;
            $searchData = $this->searchData;
            $s = $this->s;
            $_s = $this->_s;


            if (is_multisite()) {
                $home_url = network_home_url();
            } else {
                $home_url = home_url();
            }

            foreach ($pageposts as $k => $v) {
                $r = & $pageposts[$k];
                $r->title = w_isset_def($r->title, null);
                $r->content = w_isset_def($r->content, null);
                $r->image = w_isset_def($r->image, null);
                $r->author = w_isset_def($r->author, null);
                $r->date = w_isset_def($r->date, null);
            }

            aspDebug::start('--searchContent-posptrocess');

            /* Images, title, desc */
            foreach ($pageposts as $k => $v) {

                // Let's simplify things
                $r = & $pageposts[$k];


                $r = apply_filters('asp_result_before_prostproc', $r);
                $r->title = apply_filters('asp_result_title_before_prostproc', $r->title, $r->id);
                $r->content = apply_filters('asp_result_content_before_prostproc', $r->content, $r->id);
                $r->image = apply_filters('asp_result_image_before_prostproc', $r->image, $r->id);
                $r->author = apply_filters('asp_result_author_before_prostproc', $r->author, $r->id);
                $r->date = apply_filters('asp_result_date_before_prostproc', $r->date, $r->id);

                $r->link = get_permalink($v->id);

                $caching_options = w_false_def(get_option('asp_caching'), get_option('asp_caching_def'));

                $use_bfi = w_isset_def($caching_options['use_bfi_thumb'], 1);

                $image_settings = $searchData['image_options'];

                if ($image_settings['show_images'] != 0) {

                    if ($use_bfi == 0) {
                        $im = $this->getCachedImage($r);
                        if ($im != '')
                            $r->image = $im;
                    } else {
                        $im = $this->getBFIimage($r);
                        if ($im != '' && strpos($im, "mshots/v1") === false) {
                            if (w_isset_def($image_settings['image_transparency'], 1) == 1)
                                $bfi_params = array('width' => $image_settings['image_width'], 'height' => $image_settings['image_height'], 'crop' => true);
                            else
                                $bfi_params = array('width' => $image_settings['image_width'], 'height' => $image_settings['image_height'], 'crop' => true, 'color' => wpdreams_rgb2hex($image_settings['image_bg_color']));

                            $r->image = bfi_thumb($im, $bfi_params);
                        } else {
                            $r->image = $im;
                        }
                    }
                }


                if (!isset($searchData['titlefield']) || $searchData['titlefield'] == "0" || is_array($searchData['titlefield'])) {
                    $r->title = get_the_title($r->id);
                } else {
                    if ($searchData['titlefield'] == "1") {
                        if (strlen($r->excerpt) >= 200)
                            $r->title = wd_substr_at_word($r->excerpt, 200);
                        else
                            $r->title = $r->excerpt;
                    } else {
                        $mykey_values = get_post_custom_values($searchData['titlefield'], $r->id);
                        if (isset($mykey_values[0])) {
                            $r->title = $mykey_values[0];
                        } else {
                            $r->title = get_the_title($r->id);
                        }
                    }
                }

                if (!isset($searchData['striptagsexclude'])) $searchData['striptagsexclude'] = "<a><span>";

                if (!isset($searchData['descriptionfield']) || $searchData['descriptionfield'] == "0" || is_array($searchData['descriptionfield'])) {
                    if (function_exists('qtrans_getLanguage'))
                        $r->content = apply_filters('the_content', $r->content);
                    $_content = strip_tags($r->content, $searchData['striptagsexclude']);
                } else {
                    if ($searchData['descriptionfield'] == "1") {
                        $_content = strip_tags($r->excerpt, $searchData['striptagsexclude']);
                    } else if ($searchData['descriptionfield'] == "2") {
                        $_content = strip_tags(get_the_title($r->id), $searchData['striptagsexclude']);
                    } else {
                        $mykey_values = get_post_custom_values($searchData['descriptionfield'], $r->id);
                        if (isset($mykey_values[0])) {
                            $_content = strip_tags($mykey_values[0], $searchData['striptagsexclude']);
                        } else {
                            $_content = strip_tags($r->content, $searchData['striptagsexclude']);
                        }
                    }
                }
                if ($_content == "") $_content = $r->content;
                if (isset($searchData['runshortcode']) && $searchData['runshortcode'] == 1) {
                    if ($_content != "") $_content = apply_filters('the_content', $_content);
                    if ($_content != "") $_content = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $_content);
                }
                if (isset($searchData['stripshortcode']) && $searchData['stripshortcode'] == 1) {
                    if ($_content != "") $_content = strip_shortcodes($_content);
                }
                $_content = strip_tags($_content, $searchData['striptagsexclude']);

                if ($_content != '' && (strlen($_content) > $searchData['descriptionlength']))
                    $r->content = wd_substr_at_word($_content, $searchData['descriptionlength']) . "...";
                else
                    $r->content = $_content . "...";

                $r->content = wd_closetags($r->content);

                $r = apply_filters('asp_result_after_prostproc', $r);
                $r->title = apply_filters('asp_result_title_after_prostproc', $r->title, $r->id);
                $r->content = apply_filters('asp_result_content_after_prostproc', $r->content, $r->id);
                $r->image = apply_filters('asp_result_image_after_prostproc', $r->image, $r->id);
                $r->author = apply_filters('asp_result_author_after_prostproc', $r->author, $r->id);
                $r->date = apply_filters('asp_result_date_after_prostproc', $r->date, $r->id);
            }

            aspDebug::stop('--searchContent-posptrocess');

            /* !Images, title, desc */
            //var_dump($pageposts); die();
            $this->results = $pageposts;
            return $pageposts;

        }

        protected function group() {

            $pageposts = $this->results;
            $options = $this->options;
            $searchData = $this->searchData;
            $allpageposts = array();
            $s = $this->s;
            $_s = $this->_s;

            if ($options['do_group'] == false) return;

            // Need a suffix to separate blogs
            if (isset($blog)) {
                $_key_suff = "_" . $blog;
            } else {
                $_key_suff = "";
            }
            /* Regrouping */
            // By category
            if ($searchData['groupby'] == 1 && count($pageposts) > 0) {
                $_pageposts = array();
                foreach ($pageposts as $k => $v) {
                    if ($v->ttid == "" || ($v->post_type != 'post' && $searchData['pageswithcategories'] != 1)) {
                        $_pageposts['99999']['data'][] = $v;
                        continue;
                    }
                    $_term_ids = str_replace('----', ',', $v->ttid);
                    $_term_ids = str_replace('--', '', $_term_ids);
                    $_term_ids = explode(',', $_term_ids);
                    if (count($_term_ids) <= 0) $_term_ids = array($v->term_id);
                    foreach ($_term_ids as $_term_id) {
                        $cat = get_category($_term_id);
                        if (is_object($cat) && trim($cat->name) != "") {
                            $_pageposts[$_term_id]['data'][] = $v;
                        }
                    }
                }

                foreach ($_pageposts as $k => $v) {
                    if ($searchData['showpostnumber'] == 1) {
                        $num = " (" . count($_pageposts[$k]['data']) . ")";
                    } else {
                        $num = "";
                    }
                    if ($k != 99999) {
                        $cat = get_category($k);
                        $_pageposts[$k]['name'] = str_replace('%GROUP%', $cat->name, $searchData['groupbytext']) . $num;
                    } else {
                        $_pageposts[$k]['name'] = $searchData['uncategorizedtext'] . $num;
                    }
                }

                $pageposts = null;
                $pageposts['grouped'] = 1;
                $pageposts['items'] = $_pageposts;
                ksort($pageposts['items']);
                if ($_key_suff != "") {
                    foreach ($pageposts['items'] as $k => $v) {
                        $pageposts['items'][$k . $_key_suff] = $v;
                        unset($pageposts['items'][$k]);
                    }
                }
                // By post type
            } else if ($searchData['groupby'] == 2 && count($pageposts) > 0) {
                foreach ($pageposts as $k => $v) {
                    $_pageposts[$v->post_type]['data'][] = $v;
                }
                foreach ($_pageposts as $k => $v) {
                    if ($searchData['showpostnumber'] == 1) {
                        $num = " (" . count($_pageposts[$k]['data']) . ")";
                    } else {
                        $num = "";
                    }
                    $obj = get_post_type_object($k);
                    $_pageposts[$k]['name'] = str_replace('%GROUP%', $obj->labels->singular_name, $searchData['groupbytext']) . $num;
                }
                $pageposts = null;
                $pageposts['grouped'] = 1;
                $pageposts['items'] = $_pageposts;
                ksort($pageposts['items']);
            }

            if (($searchData['groupby'] == 1 || $searchData['groupby'] == 2) && count($pageposts) > 0 && count($allpageposts) > 0)
                $allpageposts['items'] = array_merge($allpageposts['items'], $pageposts['items']);
            else
                $allpageposts = array_merge($allpageposts, $pageposts);

            $this->results = $allpageposts;
            return $this->results;

        }

        /**
         * Fetches an image with the imageCache class
         */
        function getCachedImage($post) {
            if ($post->image == null) {
                $i = 1;
                $im = "";
                for ($i == 1; $i < 6; $i++) {
                    switch ($this->imageSettings['image_source' . $i]) {
                        case "featured":
                            $__im = wp_get_attachment_url(get_post_thumbnail_id($post->id));
                            if ($__im != "") {
                                $img = new wpdreamsImageCache(
                                    $__im, "post" . $post->id,
                                    ASP_PATH . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR,
                                    $this->imageSettings['image_width'], $this->imageSettings['image_height'],
                                    -1, $this->imageSettings['image_bg_color']
                                );
                                $_im = $img->get_image();
                                if ($_im != '') {
                                    $im = plugins_url('/ajax-search-pro/cache/' . $_im);
                                }
                            }
                            break;
                        case "content":
                            $img = new wpdreamsImageCache(
                                $post->content, "post" . $post->id,
                                ASP_PATH . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR,
                                $this->imageSettings['image_width'], $this->imageSettings['image_height'],
                                1, $this->imageSettings['image_bg_color']
                            );
                            $_im = $img->get_image();
                            if ($_im != '') {
                                $im = plugins_url('/ajax-search-pro/cache/' . $_im);
                            }
                            break;
                        case "excerpt":
                            $img = new wpdreamsImageCache(
                                $post->excerpt, "post" . $post->id,
                                ASP_PATH . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR,
                                $this->imageSettings['image_width'], $this->imageSettings['image_height'],
                                1, $this->imageSettings['image_bg_color']
                            );
                            $_im = $img->get_image();
                            if ($_im != '') {
                                $im = plugins_url('/ajax-search-pro/cache/' . $_im);
                            }
                            break;
                        case "screenshot":
                            $im = 'http://s.wordpress.com/mshots/v1/' . urlencode(get_permalink($post->id)) .
                                '?w=' . $this->imageSettings['image_width'] . '&h=' . $this->imageSettings['image_height'];
                            break;
                        case "custom":
                            if ($this->imageSettings['image_custom_field'] != "") {
                                $val = get_post_meta($post->id, $this->imageSettings['image_custom_field'], true);
                                if ($val != null && $val != "")
                                    $im = $val;
                            }
                            break;
                        case "default":
                            if ($this->imageSettings['image_default'] != "")
                                $im = $this->imageSettings['image_default'];
                            break;
                        default:
                            $im = "";
                            break;
                    }
                    if ($im != null && $im != '') break;
                }
                return $im;
            } else {
                return $post->image;
            }
        }

        /**
         * Fetches an image for bfi class
         */
        function getBFIimage($post) {
            if (!isset($post->image) || $post->image == null) {
                $home_url = network_home_url();
                $home_url = home_url();

                if (!isset($post->id)) return "";
                $i = 1;
                $im = "";
                for ($i == 1; $i < 6; $i++) {
                    switch ($this->imageSettings['image_source' . $i]) {
                        case "featured":
                            $im = wp_get_attachment_url(get_post_thumbnail_id($post->id));
                            if (is_multisite())
                                $im = str_replace(home_url(), network_home_url(), $im);
                            break;
                        case "content":
                            $im = wpdreams_get_image_from_content($post->content, 1);
                            if (is_multisite())
                                $im = str_replace(home_url(), network_home_url(), $im);
                            break;
                        case "excerpt":
                            $im = wpdreams_get_image_from_content($post->excerpt, 1);
                            if (is_multisite())
                                $im = str_replace(home_url(), network_home_url(), $im);
                            break;
                        case "screenshot":
                            $im = 'http://s.wordpress.com/mshots/v1/' . urlencode(get_permalink($post->id)) .
                                '?w=' . $this->imageSettings['image_width'] . '&h=' . $this->imageSettings['image_height'];
                            break;
                        case "custom":
                            if ($this->imageSettings['image_custom_field'] != "") {
                                $val = get_post_meta($post->id, $this->imageSettings['image_custom_field'], true);
                                if ($val != null && $val != "")
                                    $im = $val;
                            }
                            break;
                        case "default":
                            if ($this->imageSettings['image_default'] != "")
                                $im = $this->imageSettings['image_default'];
                            break;
                        default:
                            $im = "";
                            break;
                    }
                    if ($im != null && $im != '') break;
                }
                return $im;
            } else {
                return $post->image;
            }
        }

    }
}
?>