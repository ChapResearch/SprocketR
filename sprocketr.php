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

/* let's ignore that pedantic interpreter....
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);*/


// include the files with form calls and blender calls
include '/var/www-chapresearch/wp-content/themes/accesspress-lite/sprocketr-form.php';
include '/var/www-chapresearch/wp-content/themes/accesspress-lite/blenderCalls.php';

if(array_key_exists('sprocketrForm', $_GET)){ 

  $params = $_GET; // makes sure we don't edit the $_GET array!
  $badFields = formValidate(&$params); // return names of all fields that are "bad" (and edits data)

  // execute python script for blender on server if no errors in field info
  if (count($badFields) == 0){
    array_pop($params); // remove the hidden fields
    array_pop($params);
    $_SESSION["user_params"] = $params;
    echo '<h1 style="text-align:center"><br><br><br><br><br>Calculating...</h1>';
    if ($_GET["page_id"]==PAGE_1){ // on original page 1
      echo "<script>window.location = 'http://chapresearch.com/?page_id=".PAGE_2."'</script>";
    } else if ($_GET["page_id"]==PAGE_1_ALT){ // on alternate page 1
      echo "<script>window.location = 'http://chapresearch.com/?page_id=".PAGE_2_ALT."'</script>";
    }
      
  } else {
    displayForm($params, $badFields); // redisplay form with "bad" fields marked (if present)
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