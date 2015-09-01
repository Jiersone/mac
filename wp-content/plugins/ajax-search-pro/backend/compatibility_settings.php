<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

$com_options = get_option('asp_compatibility');
?>

<div id="wpdreams" class='wpdreams wrap'>
<div class="wpdreams-box">
  <?php ob_start(); ?>


    <div id="content" class='tabscontent'>
        <div tabid="1">
            <fieldset>
                <legend>CSS and JS compatibility</legend>

                <?php include(ASP_PATH . "backend/tabs/compatibility/cssjs_options.php"); ?>

            </fieldset>
        </div>
        <div tabid="2">
            <fieldset>
                <legend>Query compatibility options</legend>

                <?php include(ASP_PATH . "backend/tabs/compatibility/query_options.php"); ?>

            </fieldset>
        </div>
    </div>


  <?php $_r = ob_get_clean(); ?>
  

  <?php
    $updated = false;
    if (isset($_POST) && isset($_POST['asp_compatibility']) && (wpdreamsType::getErrorNum()==0)) {
        $values = array(
            // CSS and JS
            "js_source" => $_POST['js_source'],
            "css_compatibility_level" => $_POST['css_compatibility_level'],
            "forceinlinestyles" => $_POST['forceinlinestyles'],
            "loadpolaroidjs" => $_POST['loadpolaroidjs'],
            "load_noui_js" => $_POST['load_noui_js'],
            "load_isotope_js" => $_POST['load_isotope_js'],
            "usecustomajaxhandler" => $_POST['usecustomajaxhandler'],
            // Query options
            "db_force_case" => $_POST['db_force_case'],
            "db_force_unicode" => $_POST['db_force_unicode'],
            "db_force_utf8_like" => $_POST['db_force_utf8_like'],
        );
        update_option('asp_compatibility', $values);
        $updated = true;
        asp_generate_the_css();
    }
  ?>
  <?php
  $_comp = wpdreamsCompatibility::Instance();
  if ($_comp->has_errors()): 
  ?>
  <div class="wpdreams-slider errorbox">
          <p class='errors'>Possible incompatibility! Please go to the <a href="<?php echo get_admin_url()."admin.php?page=ajax-search-pro/backend/comp_check.php"; ?>">error check</a> page to see the details and solutions!</p> 
  </div>
  <?php endif; ?>
  <div class='wpdreams-slider'>
  <form name='caching' method='post'>
    <?php if($updated): ?><div class='successMsg'>Search caching settings successfuly updated!</div><?php endif; ?>

      <ul id="tabs" class='tabs'>
          <li><a tabid="1" class='current multisite'>CSS and JS compatibility</a></li>
          <li><a tabid="2" class='general'>Query compatibility options</a></li>
      </ul>

      <?php print $_r; ?>

       <div class="item">
            <input type='submit' class='submit' value='Save options'/>
       </div>
      <input type='hidden' name='asp_compatibility' value='1' />

  </form>
  </div>        
</div>
</div>
<script>
    // Simulate a click on the first element to initialize the tabs
    jQuery(function($){
        $('.tabs a[tabid=1]').click();
    });
</script>