<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

/**
 * This is the default template for one vertical result
 *
 * You should rather make a copy of this in this folder and use that,
 * instead of modifying this one.
 * It's also a good idea to use the actions to insert content instead of modifications.
 *
 * WARNING: Modifying anything in this file might result in search malfunctioning,
 * so be careful and use your test environment.
 *
 * You can use any WordPress function here.
 * Variables to mention:
 *      Object() $r - holding the result details
 *      Array[]  $s_options - holding the search options
 *
 * I DO NOT RECOMMEND PUTTING ANYTHING BEFORE OR AFTER THE
 * <div class='item'>..</div><div class="asp_spacer"></div> structure
 *
 * You can leave empty lines for better visibility, they are cleared before output.
 *
 * MORE INFO: https://wp-dreams.com/knowledge-base/result-templating/
 *
 * @since: 4.0
 */
?>
<div class='item asp_result_<?php echo $r->content_type; ?>'>

    <?php do_action('asp_res_vertical_begin_item'); ?>

    <div class='content'>


        <?php if (!empty($r->image)): ?>

            <?php do_action('asp_res_vertical_before_image'); ?>

            <div class='image'>
                <img src='<?php echo $r->image; ?>'>
                <div class='void'></div>
            </div>

            <?php do_action('asp_res_vertical_after_image'); ?>

        <?php endif; ?>



        <h3><a href='<?php echo $r->link; ?>'<?php echo ($s_options['results_click_blank'])?" target='_blank'":""; ?>>
                <?php echo $r->title; ?>
                <?php if ($s_options['resultareaclickable'] == 1): ?>
                <span class='overlap'></span>
                <?php endif; ?>
        </a></h3>


        <?php if ( !empty($r->date) || !empty($r->author) ): ?>

        <div class='etc'>

            <?php if ( $s_options['showauthor'] == 1 && !empty($r->author) ): ?>
            <span class='author'><?php echo $r->author; ?></span>
            <?php endif; ?>

            <?php if ( $s_options['showdate'] == 1 && !empty($r->date) ): ?>
            <span class='date'><?php echo $r->date; ?></span>
            <?php endif; ?>

        </div>

        <?php endif; ?>

        <?php if ($s_options['showdescription'] == 1): ?>
        <!--<p class='desc'>-->
            <?php echo $r->content; ?>
        <!--</p>-->
        <?php endif; ?>

    </div>

    <?php do_action('asp_res_vertical_after_content'); ?>

    <div class='clear'></div>

    <?php do_action('asp_res_vertical_end_item'); ?>

</div>
<div class="asp_spacer"></div>