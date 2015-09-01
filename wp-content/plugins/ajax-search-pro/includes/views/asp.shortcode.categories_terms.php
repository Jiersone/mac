<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

$cat_content = '';
$term_content = '';

if ($style['showsearchincategories']) {
    ob_start();
?>
    <div class='categoryfilter asp_sett_scroll'>
    <?php
    /* Categories */
    if (!isset($style['selected-exsearchincategories']) || !is_array($style['selected-exsearchincategories']))
        $style['selected-exsearchincategories'] = array();
    if (!isset($style['selected-excludecategories']) || !is_array($style['selected-excludecategories']))
        $style['selected-excludecategories'] = array();
    //$_all_cat = get_all_category_ids();
    $_all_cat = get_terms('category', array('fields' => 'ids'));
    $_needed_cat = array_diff($_all_cat, $style['selected-exsearchincategories']);
    foreach ($_needed_cat as $k => $v) {
        $selected = !in_array($v, $style['selected-excludecategories']);
        $cat = get_category($v);
        $val = $cat->name;
        $hidden = (($style['showsearchincategories']) == 0 ? " hiddend" : "");
        if ($style['showuncategorised'] == 0 && $v == 1) {
            $hidden = ' hiddend';
        }
        ?>
        <div class="option<?php echo $hidden; ?>">
            <input type="checkbox" value="<?php echo $v; ?>"
                   id="<?php echo $id; ?>categoryset_<?php echo $v; ?>"
                   name="categoryset[]" <?php echo(($selected) ? 'checked="checked"' : ''); ?>/>
            <label for="<?php echo $id; ?>categoryset_<?php echo $v; ?>"></label>
        </div>
        <div class="label<?php echo $hidden; ?>">
            <?php echo $val; ?>
        </div>
    <?php
    }
    ?>
</div>
<?php
    $cat_content = ob_get_clean();
}
?>


<?php do_action('asp_layout_settings_after_last_item', $id); ?>
<?php

/* Terms */
if ($style['showsearchintaxonomies'] == 1) {
    if (!isset($style['selected-excludeterms']) || !is_array($style['selected-excludeterms']))
        $style['selected-excludeterms'] = array();
    if (!isset($style['selected-showterms']) || !is_array($style['selected-showterms']))
        $style['selected-showterms'] = array();

    $_all_term_ids = wpdreams_get_all_term_ids();

    $_needed_terms = array_diff($_all_term_ids, $style['selected-excludeterms']);
    $_invisible_terms = array_diff($_needed_terms, $style['selected-showterms']);
//$counter = 0;

    $_close_fieldset = false;

    $_terms = array();
    $visible_terms = array();

    ob_start();

    foreach ($style['selected-showterms'] as $taxonomy => $terms) {
        if (is_array($terms)) {

            if ($style['showseparatefilterboxes'] != 0) {
                $_x_term = get_taxonomies(array("name" => $taxonomy), "objects");
                //var_dump($_x_term);
                if (isset($_x_term[$taxonomy]))
                    $_tax_name = $_x_term[$taxonomy]->label;
                ?>
                <fieldset>
                <legend><?php echo $style['exsearchintaxonomiestext'] . " " . $_tax_name; ?></legend>
                <div class='categoryfilter'>
            <?php
            }

            foreach ($terms as $k => $term) {
                $checked = wd_in_array_r($term, $style['selected-excludeterms']) ? '' : 'checked="checked"';
                ?>
                <div class="option">
                    <input type="checkbox" value="<?php echo $term; ?>" id="<?php echo $id; ?>termset_<?php echo $term; ?>"
                           name="termset[]" <?php echo $checked; ?>/>
                    <label for="<?php echo $id; ?>termset_<?php echo $term; ?>"></label>
                </div>
                <div class="label">
                    <?php
                    $tterm = get_term($term, $taxonomy);
                    echo $tterm->name;
                    ?>
                </div>
                <?php
                //$counter++;
            }

            if ($style['showseparatefilterboxes'] != 0) {
                ?>
                </div>
                </fieldset>
            <?php
            }

        }
    }

    $term_content = ob_get_clean();
}
?>
<fieldset<?php echo $cat_content == '' ? ' class="hiddend"' : ''; ?>>
    <?php if ($style['exsearchincategoriestext'] != ""): ?>
        <legend><?php echo $style['exsearchincategoriestext']; ?></legend>
        <?php echo $cat_content; ?>
    <?php endif; ?>
</fieldset>

<?php if ($style['showseparatefilterboxes'] == 0): ?>
    <fieldset<?php echo count($style['selected-showterms']) > 0 ? '' : ' class="hiddend"'; ?>>
        <div class='categoryfilter'>
        <?php echo $term_content; ?>
        </div>
    </fieldset>
<?php else: ?>
    <?php echo $term_content; ?>
<?php endif; ?>
