<?php

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Full Content Template
 *
Template Name:  SprocketR Page
 *
 * @file           sprocketr.php
 * @package        AccessPress-Lite
 * @author         Rachel Gardner
 * @copyright      WESTA
 * @license        license.txt
 * @version        Release: 1.0
 * @filesource     wp-content/themes/accesspress-lite/sprocketr.php
 * @link           
 * @since          
 */

get_header(); ?>

<div id="content-full" class="grid col-940">

<?php
/*--------------------------------sprocketr.php------------------------------------------------
  the first of two "phases" of generating a sprocket, this page allows the user to input the 
  desired characteristics of the sprocket, including "advanced settings"
 */

// let's ignore that pedantic interpreter....
/*ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);*/


// include the files with form calls and blender calls
include '/var/www-chapresearch/wp-content/themes/accesspress-lite/sprocketr-form.php';
include '/var/www-chapresearch/wp-content/themes/accesspress-lite/blenderCalls.php';

if(array_key_exists('sprocketrForm', $_GET)){ 

  $badFields = formValidate($_GET); // return names of all fields that are "bad"
  $params = $_GET;
  $params = checkSpecialCases($params);
  if($params["teethWarn"])
    {
      if(in_array("gearRatioField",$badFields))
	{
	  unset($badFields[0]);
	}
    }
  // execute python script for blender on server if no errors in field info
  // note: one field, the "teethWarn", is indicated as "bad", but will still allow the script
  // to progress. This is simply the 
  if (count($badFields) == 0){
    //$params = checkSpecialCases($params);
    array_pop($params); // remove the hidden fields
    array_pop($params);
    $_SESSION["user_params"] = $params;
    echo '<h1 style="text-align:center"><br><br><br><br><br>Calculating...</h1>';
    echo "<script>window.location = 'http://chapresearch.com/?page_id=1020'</script>";
  } else {
    displayForm($_GET, $badFields); // redisplay form with "bad" fields marked (if present)
   
  }
} else {
  if (isset($_SESSION["user_params"])){
    displayForm($_SESSION["user_params"], array()); 
  } else {
    displayForm(array(), array()); // send empty arrays as both existing data and the bad fields
  }
  // clear any previous session variables
  //session_start();
  $_SESSION = array();
  session_destroy();
}

?>

</div><!-- end of #content-full -->

<?php get_footer(); ?>