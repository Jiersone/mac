<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

$cache_options = get_option('asp_caching');
?>
<div id="wpdreams" class='wpdreams wrap'>
<div class="wpdreams-box">
  <?php ob_start(); ?>
  <div class="item">
  <p class='infoMsg'>Not recommended, unless you have many search queries per minute.</p>
  <?php $o = new wpdreamsYesNo("caching", "Caching activated", wpdreams_setval_or_getoption($cache_options, 'caching', 'asp_caching_def')); ?>
  </div>
  <div class="item">
  <p class='infoMsg'>Set <b>BFI Thumb</b> to NO if you are experiencing issues with the <b>images</b>, or if the images are <b>not loading</b>!</p>
  <?php $o = new wpdreamsYesNo("use_bfi_thumb", "Use the BFI Thumb library for image caching?",
            wpdreams_setval_or_getoption($cache_options, 'use_bfi_thumb', 'asp_caching_def')); ?>
  </div>
  <div class="item">
  <?php $o = new wpdreamsText("cachinginterval", "Caching interval (in minutes, default 1440, aka. 1 day)",
                 wpdreams_setval_or_getoption($cache_options, 'cachinginterval', 'asp_caching_def'), array( array("func"=>"ctype_digit", "op"=>"eq", "val"=>true)) ); ?>
  </div>
  <div class="item">
    <input type='submit' class='submit' value='Save options'/>
  </div>
  <?php $_r = ob_get_clean(); ?>
  

    <?php
    $updated = false;
    if (isset($_POST) && isset($_POST['asp_caching']) && (wpdreamsType::getErrorNum()==0)) {
        $values = array(
            "caching" => $_POST['caching'],
            "use_bfi_thumb" => $_POST['use_bfi_thumb'],
            "cachinginterval" => $_POST['cachinginterval']
        );
        update_option('asp_caching', $values);
        $updated = true;
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
  <form name='asp_caching' method='post'>
    <?php if($updated): ?><div class='successMsg'>Search caching settings successfuly updated!</div><?php endif; ?>
    <fieldset>
      <legend>Caching Options</legend>
      <?php print $_r; ?> 
      <input type='hidden' name='asp_caching' value='1' />
    </fieldset>
  </form>
  
    <fieldset>
      <legend>Image precache</legend>
      <div class="item">
        <div class='infoMsg'>
        By clicking the <b>Create image precache</b> button a set of ajax requests will start.<br>
        You must have <b>TimThumb disabled</b> to make use of the image cache<br>
        This may take a long time (5 - 60 minutes) - depending on the amount of posts you have.<br>
        After starting, dont close this page for a while
        </div>
        <input type='button' class="red" name='Create Image Cache' id='createimagecache' value='Create image precache!'>
        <div id='console' class='asp_console'></div>
      </div>                                                            
    </fieldset>  
  
    <fieldset>
      <legend>Clear Cache</legend>
      <div class="item">
        <p class='infoMsg'>Will clear all the images and precached search phrases.</p>
        <input type='submit' class="red" name='Clear Cache' id='clearcache' value='Clear the cache!'>
      </div>                                                            
    </fieldset>
  </div> 
  <script>
     jQuery(document).ready((function($) {
        $('#clearcache').on('click', function(){
          var r = confirm('Do you really want to clear the cache?');
          if (r!=true) return;
          var button = $(this);
          var data = {
            action: 'ajaxsearchpro_deletecache'  
          };
          button.attr("disabled", true);
          var oldVal = button.attr("value");
          button.attr("value", "Loading...");
          button.addClass('blink');
          $.post(ajaxsearchpro.ajaxurl, data, function(response) {
             var currentdate = new Date();
             var datetime =  currentdate.getDate() + "/"
                  + (currentdate.getMonth()+1)  + "/" 
                  + currentdate.getFullYear() + " @ "  
                  + currentdate.getHours() + ":"  
                  + currentdate.getMinutes() + ":" 
                  + currentdate.getSeconds();
             button.attr("disabled", false);
             button.removeClass('blink');
             button.attr("value", oldVal);
             button.parent().parent().append('<div class="successMsg">Cache succesfully cleared! '+response+' file(s) deleted at '+datetime+'</div>');
          }, "json"); 
        });
     })(jQuery));
     
    var last_id = 0;
    var affected = 0;
    var button = $('#createimagecache');
    var oldVal = button.attr("value");
           
     function make_post(data) {
          $.post(ajaxsearchpro.ajaxurl, data, function(response) {
             var currentdate = new Date();
             var datetime =  currentdate.getDate() + "/"
                  + (currentdate.getMonth()+1)  + "/" 
                  + currentdate.getFullYear() + " @ "  
                  + currentdate.getHours() + ":"  
                  + currentdate.getMinutes() + ":" 
                  + currentdate.getSeconds();
             affected += response.affected; 
             if (response.affected==0) {
                $('#console').html("<p>Precaching done! Posts checked: " + affected + "</p>");
               button.attr("disabled", false);
               button.removeClass('blink');
               button.attr("value", oldVal);
               last_id = 0;
               affected = 0;
             } else {
                $('#console').html("<p>" + datetime + " status: Working</p><p>Posts checked: " + affected + "</p>");
             }    
             
             if (response.affected>0) {
                var data = {
                  action: 'ajaxsearchpro_precache',
                  from: response.lastid  
                };
               make_post(data);
             }
          }, "json"); 
     }
     
     jQuery(document).ready((function($) {

        $('#createimagecache').on('click', function(){
          var r = confirm('Do you really want to start precaching?');
          if (r!=true) return;
          var button = $(this);
          var data = {
            action: 'ajaxsearchpro_precache',
            from: last_id  
          };
          button.attr("disabled", true);
          
          button.attr("value", "Loading...");
          button.addClass('blink');
          make_post(data);

        });
     })(jQuery));     
     
  </script>        
</div>
</div>