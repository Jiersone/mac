<div class="item">
    <?php
    $o = new wpdreamsYesNo("runshortcode", "Run shortcodes found in post content", wpdreams_setval_or_getoption($sd, 'runshortcode', $_dk));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("stripshortcode", "Strip shortcodes from post content", wpdreams_setval_or_getoption($sd, 'stripshortcode', $_dk));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <p class='infoMsg'>If you have a plugin/tweak which enables categories on pages, then you should turn this on.</p>
    <?php
    $o = new wpdreamsYesNo("pageswithcategories", "Enable pages with categories/tags", wpdreams_setval_or_getoption($sd, 'pageswithcategories', $_dk));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsText("striptagsexclude", "HTML Tags exclude from stripping content", wpdreams_setval_or_getoption($sd, 'striptagsexclude', $_dk));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsCustomFSelect("titlefield", "Title Field", array(
        'selects'=>wpdreams_setval_or_getoption($sd, 'titlefield_def', $_dk),
        'value'=>wpdreams_setval_or_getoption($sd, 'titlefield', $_dk)
    ));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsCustomFSelect("descriptionfield", "Description Field", array(
        'selects'=>wpdreams_setval_or_getoption($sd, 'descriptionfield_def', $_dk),
        'value'=>wpdreams_setval_or_getoption($sd, 'descriptionfield', $_dk)
    ));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<fieldset>
    <legend>Advanced fields</legend>
    <p class='infoMsg'>Example: <b>{titlefield} - {_price}</b> will show the title and price for a woocommerce product. More info in the documentation.</p>
    <div class="item">
        <?php
        $o = new wpdreamsText("advtitlefield", "Advanced Title Field (default: {titlefield})", wpdreams_setval_or_getoption($sd, 'advtitlefield', $_dk));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsText("advdescriptionfield", "Advanced Description Field (default: {descriptionfield})", wpdreams_setval_or_getoption($sd, 'advdescriptionfield', $_dk));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
</fieldset>
<div class="item">
    <?php
    $o = new wpdreamsCategories("excludecategories", "Exclude categories", wpdreams_setval_or_getoption($sd, 'excludecategories', $_dk));
    $params[$o->getName()] = $o->getData();
    $params['selected-'.$o->getName()] = $o->getSelected();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsCustomTaxonomyTerm("excludeterms", "Exclude taxonomy terms", array(
        "value"=>wpdreams_setval_or_getoption($sd, 'excludeterms', $_dk),
        "type"=>"exclude"));
    $params[$o->getName()] = $o->getData();
    $params['selected-'.$o->getName()] = $o->getSelected();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsTextarea("excludeposts", "Exclude Posts by ID's (comma separated post ID-s)", wpdreams_setval_or_getoption($sd, 'excludeposts', $_dk));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsCustomSelect("groupby", "Group results by", array(
        'selects'=>wpdreams_setval_or_getoption($sd, 'groupby_def', $_dk),
        'value'=>wpdreams_setval_or_getoption($sd, 'groupby', $_dk)) );
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsText("groupbytext", "Group by default text (%GROUP% is changed into the current cateogry/post type name)", wpdreams_setval_or_getoption($sd, 'groupbytext', $_dk));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsText("bbpressreplytext", "BBPress replies results group default text", wpdreams_setval_or_getoption($sd, 'bbpressreplytext', $_dk));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsText("bbpressgroupstext", "BBPress group results group default text", wpdreams_setval_or_getoption($sd, 'bbpressgroupstext', $_dk));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsText("bbpressuserstext", "BBPress user results group default text", wpdreams_setval_or_getoption($sd, 'bbpressuserstext', $_dk));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsText("commentstext", "Comments results group default text", wpdreams_setval_or_getoption($sd, 'commentstext', $_dk));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsText("uncategorizedtext", "Uncategorized group text", wpdreams_setval_or_getoption($sd, 'uncategorizedtext', $_dk));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("showpostnumber", "Show Post Numbering", wpdreams_setval_or_getoption($sd, 'showpostnumber', $_dk));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("wpml_compatibility", "WPML compatibility", wpdreams_setval_or_getoption($sd, 'wpml_compatibility', $_dk));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <input type="hidden" name='asp_submit' value=1 />
    <input name="submit_<?php echo $search['id']; ?>" type="submit" value="Save this search!" />
</div>