<div class="item">
    <p class='infoMsg'>Nor recommended if you have more than 500 categories! (the HTML output will get too big)</p>
    <?php
    $o = new wpdreamsYesNo("showsearchincategories", "Show the categories selectors?", wpdreams_setval_or_getoption($sd, 'showsearchincategories', $_dk));
    $params[$o->getName()] = $o->getData();
    ?></div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("showuncategorised", "Show the uncategorised category?", wpdreams_setval_or_getoption($sd, 'showuncategorised', $_dk));
    $params[$o->getName()] = $o->getData();
    ?></div>
<div class="item"><?php
    $o = new wpdreamsCategories("exsearchincategories", "Select which categories exclude", wpdreams_setval_or_getoption($sd, 'exsearchincategories', $_dk));
    $params[$o->getName()] = $o->getData();
    $params['selected-'.$o->getName()] = $o->getSelected();
    ?></div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("showsearchintaxonomies", "Show the taxonomy selectors?", wpdreams_setval_or_getoption($sd, 'showsearchintaxonomies', $_dk));
    $params[$o->getName()] = $o->getData();
    ?></div>
<div class="item">
    <?php
    $o = new wpdreamsCustomTaxonomyTerm("showterms", "Show the following taxonomy term selectors on frontend", array("value"=>wpdreams_setval_or_getoption($sd, 'showterms', $_dk), "type"=>"include"));
    $params[$o->getName()] = $o->getData();
    $params['selected-'.$o->getName()] = $o->getSelected();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsText("exsearchincategoriesheight", "Filter boxes max-height (0 for auto, default 200)", wpdreams_setval_or_getoption($sd, 'exsearchincategoriesheight', $_dk));
    $params[$o->getName()] = $o->getData();
    ?></div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("showseparatefilterboxes", "Show separate filter boxes for each taxonomy?", wpdreams_setval_or_getoption($sd, 'showseparatefilterboxes', $_dk));
    $params[$o->getName()] = $o->getData();
    ?></div>
<div class="item">
    <?php
    $o = new wpdreamsText("exsearchincategoriestext", "Categories filter box header text", wpdreams_setval_or_getoption($sd, 'exsearchincategoriestext', $_dk));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsText("exsearchintaxonomiestext", "Taxonomies filter box header text", wpdreams_setval_or_getoption($sd, 'exsearchintaxonomiestext', $_dk));
    ?>{taxonomy name}<?php
    $params[$o->getName()] = $o->getData();
    ?>
</div>