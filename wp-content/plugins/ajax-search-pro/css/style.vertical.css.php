<?php if ($use_compatibility == true): ?>
    <?php echo $asp_res_ids1; ?>.vertical,
    <?php echo $asp_res_ids2; ?>.vertical,
<?php endif; ?>
<?php echo $asp_res_ids; ?>.vertical {
    padding: 4px;
    background: <?php echo $style['resultsbackground']; ?>;
    border-radius: 3px;
    <?php echo $style['resultsborder']; ?>
    <?php echo $style['resultshadow']; ?>
    visibility: hidden;
    display: none;
}

<?php if ($use_compatibility == true): ?>
    <?php echo $asp_res_ids1; ?>.vertical .item h3,
    <?php echo $asp_res_ids2; ?>.vertical .item h3,
<?php endif; ?>
<?php echo $asp_res_ids; ?>.vertical .item h3 {
    display: inline;
}

<?php if ($use_compatibility == true): ?>
    <?php echo $asp_res_ids1; ?>.vertical .results .item .content,
    <?php echo $asp_res_ids2; ?>.vertical .results .item .content,
<?php endif; ?>
<?php echo $asp_res_ids; ?>.vertical .results .item .content {
    overflow: hidden;
    width: 50%;
    height: <?php echo $style['resultitemheight']; ?>;
    background: transparent;
    margin: 0;
    padding: 0 10px;
}

<?php if ($use_compatibility == true): ?>
    <?php echo $asp_res_ids1; ?>.vertical .results .item .image,
    <?php echo $asp_res_ids2; ?>.vertical .results .item .image,
<?php endif; ?>
<?php echo $asp_res_ids; ?>.vertical .results .item .image {
    width: <?php echo $style['image_width']; ?>px;
    height: <?php echo $style['image_height']; ?>px;
    padding: 0 8px 0px 0;
}

<?php if ($use_compatibility == true): ?>
    <?php echo $asp_res_ids1; ?>.vertical .results .asp_spacer,
    <?php echo $asp_res_ids2; ?>.vertical .results .asp_spacer,
<?php endif; ?>
<?php echo $asp_res_ids; ?>.vertical .results .asp_spacer {
    background: <?php echo $style['spacercolor']; ?>;
}

<?php if ($use_compatibility == true): ?>
    <?php echo $asp_res_ids1; ?> .mCSB_scrollTools .mCSB_dragger .mCSB_dragger_bar,
    <?php echo $asp_res_ids2; ?> .mCSB_scrollTools .mCSB_dragger .mCSB_dragger_bar,
<?php endif; ?>
<?php echo $asp_res_ids; ?> .mCSB_scrollTools .mCSB_dragger .mCSB_dragger_bar {
    background:#fff; /* rgba fallback */
    background:rgba(<?php echo wpdreams_hex2rgb($style['overflowcolor']); ?>,0.9);
    filter:"alpha(opacity=90)"; -ms-filter:"alpha(opacity=90)"; /* old ie */
}

<?php if ($use_compatibility == true): ?>
    <?php echo $asp_res_ids1; ?> .mCSB_scrollTools .mCSB_dragger:hover .mCSB_dragger_bar,
    <?php echo $asp_res_ids2; ?> .mCSB_scrollTools .mCSB_dragger:hover .mCSB_dragger_bar,
<?php endif; ?>
<?php echo $asp_res_ids; ?> .mCSB_scrollTools .mCSB_dragger:hover .mCSB_dragger_bar {
    background:rgba(<?php echo wpdreams_hex2rgb($style['overflowcolor']); ?>,0.95);
    filter:"alpha(opacity=95)"; -ms-filter:"alpha(opacity=95)"; /* old ie */
}

<?php echo $asp_res_ids; ?> .mCSB_scrollTools .mCSB_dragger:active .mCSB_dragger_bar,
<?php echo $asp_res_ids; ?> .mCSB_scrollTools .mCSB_dragger.mCSB_dragger_onDrag .mCSB_dragger_bar{
    background:rgba(<?php echo wpdreams_hex2rgb($style['overflowcolor']); ?>,1);
    filter:"alpha(opacity=100)"; -ms-filter:"alpha(opacity=100)"; /* old ie */
}

<?php echo $asp_res_ids; ?>.horizontal .mCSB_scrollTools .mCSB_dragger .mCSB_dragger_bar{
    background:#fff; /* rgba fallback */
    background:<?php echo $style['hoverflowcolor']; ?>;
    opacity: 0.9;
    filter:"alpha(opacity=90)"; -ms-filter:"alpha(opacity=90)"; /* old ie */
}
<?php echo $asp_res_ids; ?>.horizontal .mCSB_scrollTools .mCSB_dragger:hover .mCSB_dragger_bar{
    background:<?php echo $style['hoverflowcolor']; ?>;
    opacilty: 0.95;
    filter:"alpha(opacity=95)"; -ms-filter:"alpha(opacity=95)"; /* old ie */
}

<?php echo $asp_res_ids; ?>.horizontal .mCSB_scrollTools .mCSB_dragger:active .mCSB_dragger_bar,
<?php echo $asp_res_ids; ?>.horizontal .mCSB_scrollTools .mCSB_dragger.mCSB_dragger_onDrag .mCSB_dragger_bar{
    background: <?php echo $style['hoverflowcolor']; ?>;
    filter:"alpha(opacity=100)"; -ms-filter:"alpha(opacity=100)"; /* old ie */
}

<?php echo $asp_res_ids; ?> .mCSB_scrollTools .mCSB_buttonDown:after { border-color: rgba(136, 183, 213, 0); border-top-color: <?php echo $style['arrowcolor']; ?>; border-width: 6px; left: 50%; margin-left: -6px; }
<?php echo $asp_res_ids; ?> .mCSB_scrollTools .mCSB_buttonUp:after { border-color: rgba(136, 183, 213, 0); border-bottom-color:  <?php echo $style['arrowcolor']; ?>; border-width: 6px; left: 50%; margin-left: -6px; }
