<ul id="subtabs"  class='tabs'>
    <li><a tabid="501" class='subtheme current'>Autocomplete</a></li>
    <li><a tabid="502" class='subtheme'>Keyword suggestions</a></li>
</ul>
<div class='tabscontent'>
    <div tabid="501">
        <fieldset>
            <legend>Autocomplete</legend>
            <p class="infoMsg">
                Autocomplete feature will try to help the user finish what is being typed into the search box.
            </p>
            <?php include(ASP_PATH."backend/tabs/instance/suggest/autocomplete.php"); ?>
        </fieldset>
    </div>
    <div tabid="502">
        <fieldset>
            <legend>Keyword suggestions</legend>
            <p class="infoMsg">
                Keyword suggestions appear when no results match the keyword.
            </p>
            <?php include(ASP_PATH."backend/tabs/instance/suggest/keywords.php"); ?>
        </fieldset>
    </div>
</div>
<div class="item">
    <input name="submit_<?php echo $search['id']; ?>" type="submit" value="Save all tabs!" />
</div>