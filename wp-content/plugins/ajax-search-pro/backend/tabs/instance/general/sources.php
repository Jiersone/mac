<div class="item">
    <?php
    $o = new wpdreamsYesNo("searchinposts", "Search in posts?",
        wpdreams_setval_or_getoption($sd, "searchinposts", $_dk));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("searchinpages", "Search in pages?",
        wpdreams_setval_or_getoption($sd, 'searchinpages', $_dk));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsCustomPostTypes("customtypes", "Search in custom post types",
        wpdreams_setval_or_getoption($sd, 'customtypes', $_dk));
    $params[$o->getName()] = $o->getData();
    $params['selected-'.$o->getName()] = $o->getSelected();
    ?></div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("searchintitle", "Search in title?",
        wpdreams_setval_or_getoption($sd, 'searchintitle', $_dk));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("searchincontent", "Search in content?",
        wpdreams_setval_or_getoption($sd, 'searchincontent', $_dk));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("searchincomments", "Search in comments?",
        wpdreams_setval_or_getoption($sd, 'searchincomments', $_dk));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("searchinexcerpt", "Search in post excerpts?",
        wpdreams_setval_or_getoption($sd, 'searchinexcerpt', $_dk));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsCustomFields("customfields", "Search in custom fields",
        wpdreams_setval_or_getoption($sd, 'customfields', $_dk));
    $params[$o->getName()] = $o->getData();
    $params['selected-'.$o->getName()] = $o->getSelected();
    ?>
</div>

<div class="item">
    <?php
    $o = new wpdreamsYesNo("searchindrafts", "Search in draft posts?",
        wpdreams_setval_or_getoption($sd, 'searchindrafts', $_dk));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("searchinpending", "Search in pending posts?",
        wpdreams_setval_or_getoption($sd, 'searchinpending', $_dk));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("exactonly", "Show exact matches only?",
        wpdreams_setval_or_getoption($sd, 'exactonly', $_dk));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("searchinterms", "Search in terms? (categories, tags)",
        wpdreams_setval_or_getoption($sd, 'searchinterms', $_dk));
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">Will search in terms (categories, tags) related to posts.</p>
    <p class="errorMsg">WARNING: <strong>Search in terms</strong> can be database heavy operation. Not recommended for big databases.</p>
</div>