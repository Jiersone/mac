<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

  $_comp = wpdreamsCompatibility::Instance();
  $_comp->check_dir_w(
    ASP_PATH.'css'.DIRECTORY_SEPARATOR,
    "You might not be able to change the search style."
  );
  $_comp->check_dir_w(
    ASP_PATH.'cache'.DIRECTORY_SEPARATOR,
    "Images may not show in results."
  );
  $_comp->can_open_url("Images may not show in results.");
?>