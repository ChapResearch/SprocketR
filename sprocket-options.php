<?php

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Full Content Template
 *
Template Name:  SprocketR Page 2
 *
 * @file           sprocket-options.php
 * @package        AccessPress-Lite
 * @author         Rachel Gardner
 * @copyright      Chap Research
 * @license        license.txt
 * @version        Release: 1.0
 * @filesource     wp-content/themes/accesspress-lite/sprocket-options.php
 * @link           
 * @since          
 */
get_header(); ?>

<div id="content-full" class="grid col-940">

<?php
/*-----------------------------------sprocket-options.php--------------------------------------------
  the second and final "phase" of generating a sprocket, this page allows the user to refine the 
  originally inputted parameters and then preview and export the sprocket. 
*/

// let's ignore that pedantic interpreter....
/*ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);*/

include '/var/www-chapresearch/wp-content/themes/accesspress-lite/sprocketr-form.php';
include '/var/www-chapresearch/wp-content/themes/accesspress-lite/blenderCalls.php';

displayTitle();
showPhase2HelpText();

showDataSummary($_SESSION["user_params"]);

if(isset($_GET["tuningOptions"])){ // if this is not the first time through
  if(empty($_SESSION["options"])){
    echo('<h1 style="text-align:center">You don\'t have any options to tune!</h1>');
  }
  $params = array_merge($_SESSION["options"][0],array_slice($_GET,0,-2));
} else {
  // sets the tuning option to the default (aka simply changing the number of teeth)
  // this occurs if this is the first time through, or if "tuningOptions" isn't set (because "reset" is)
  $params = array_merge($_SESSION["user_params"],array("option"=>"numTeeth"));
}
//----------- ------------------------PNG or STL is being generated------------------------------------------
if (isset($_GET["output_type"])){ 
  // adds in the output type (png or stl) from the $_GET array
  $thing_params = array_merge((array)$_SESSION["options"][0],array("output_type" => $_GET["output_type"]));
  $fileNames = generateThing($thing_params);
  if ($fileNames != false){
    switch($_GET["output_type"]){
    case "png":
      echo("<div style=\"margin:auto;width:50%;text-align:center\">");
      echo("<img src=\"$fileNames[0]\" style=\"margin:auto\">");
      echo("</div>");
      break;
    case "stl":
      $filename = "/var/www-chapresearch/SprocketR_Output/" . uniqId() . ".zip";
      zipSprockets($filename, $fileNames);
      $filename = substr($filename, strlen("/var/www-chapresearch/SprocketR_Output/"));
      echo ("<h1 style=\"text-align:center\">");
      echo('<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>');
      echo("<script type=\"text/javascript\">
               $.get('download.php?fileName=$filename', function(response, success){
               window.location = 'download.php?fileName=$filename';
               });
               </script>");
      break;
    }
  } else {
      echo ("<h1 style=\"text-align:center\">");
    echo("<strong>Unable to generate file!</strong></h1>");
  }
} else { // -----------------------------------options are being created--------------------------------------
  $options = generateOptions($params);

  if ($options !== false){
    $_SESSION["options"] = $options;
    foreach (array_keys($_SESSION["user_params"]) as $key){
      if (strpos("holeOption", $key) !== false){
	foreach ($_SESSION["options"] as $option) {
	  $option[$key] = $_SESSION["user_params"][$key];
	}
      }
    }

    // sets all of the hole options properly
    foreach(array_keys($_SESSION["user_params"]) as $key){
      $pos = strpos($key, "holeOption");
      if (is_int($pos)){
	$i = 0;
	foreach($_SESSION["options"] as $option){
	  $option[$key] = $_SESSION["user_params"][$key];
	  $_SESSION["options"][$i] = $option;
	  $i++;
	}
      }
    } // end of foreach

  } else {
    echo ("<h1 style=\"text-align:center\">");
    echo('<strong>Unable to generate further options! Click <a href="?page_id=2090">here</a> for help!</strong></h1>');
  }
} //--------------------------------------------------------------------------------------------------------

if(!empty($_SESSION["options"])){
  createOptionsTable(&$_SESSION["options"]);
}

echo ("</table>");
createPhase2Form();

?>