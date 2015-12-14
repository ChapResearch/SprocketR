<?php

/* -------------------------------------sprocketr-form.php--------------------------------------------------
   this is simply the php file to generate the fields used to create a sprocket. It will be called from
   sprocketr.php and sprocket-options.php
*/

include("/var/www-chapresearch/wp-content/themes/accesspress-lite/htmlFunctionsSprocketr.php"); //HTML Funtions
include("/var/www-thechapr/htmlFunctions.php");
include("/var/www-chapresearch/wp-content/themes/accesspress-lite/helperFunctions.php");
include("/var/www-chapresearch/wp-content/themes/accesspress-lite/sprocketr.inc");

/* displayTitle() - displays the title used in both phases
 */
function displayTitle()
{
  echo('<h1><p style="text-align: center;"><strong><span style="color: #4169ff;">');
  echo('<big><big><big>Sprocket</big></big></big></span><span style="color:#e03812;">');
  echo('<big><big><big>R</big></big></big></span></strong>_beta</p></h1>');
}
/* createFormForTuning() - paints the form for the tuning of parameters, preview of the
                           sprockets and download of the final .stls.
 */
function createPhase2Form()
{
  echo '<h3 style="text-align:center">Select Parameter by Which to Vary New Options:</h3>';
  echo "<fieldset style=\"border: 1px solid black; margin: auto; width: 50%\">";
  centeredFormHeader("","void", "100%", "0px");
  tableRow(array(tableData(radioButton($_GET, "option", "numTeeth", true, " # of Teeth")),
		 tableData(radioButton($_GET, "option", "ratio", "", " Ratio")),
		 tableData(radioButton($_GET, "option", "c2c", "", " Center to Center")),
		 tableData("<input type=\"submit\" class=\"sprocketrButton\" value=\"Submit!\">", "center", "", "2")));
  echo hiddenField("page_id", $_GET["page_id"]);
  formFooter("tuningOptions");
  echo "</fieldset>";

  echo('<table class="form" style="width: 50%; margin: auto; margin-top: 10px; text-align:center"><tr><td>');
  echo('<form action="" method="get">');
  echo(hiddenField("page_id", $_GET["page_id"]));
  echo(hiddenField("output_type", "png"));
  echo("<input type=\"submit\" class=\"sprocketrButton\" value=\"Preview\">");
  echo('</form></td>');

  echo('<td style="text-align:center">');
  echo('<form action="" method="get">');
  echo(hiddenField("page_id", $_GET["page_id"]));
  echo(hiddenField("output_type", "stl"));
  echo("<input type=\"submit\" class=\"sprocketrButton\" value=\"Download\">");
  echo('</form></td>');

  echo('<td style="text-align:center">');
  echo('<form action="" method="get">');
  echo(hiddenField("page_id", $_GET["page_id"]));
  echo(hiddenField("cmd", "reset"));
  echo("<input type=\"submit\" class=\"sprocketrButton\" value=\"Reset to Original Options\">");
  echo('</form></td>');

  echo('<td style="text-align:center;width=40%">');
  if ($_GET["page_id"]==PAGE_2){ // on original page 2
    echo('<a href="?page_id=' . PAGE_1 . '"><button class="sprocketrButton">Edit Original Input</button></a>');
  } else if ($_GET["page_id"]==PAGE_2_ALT){ // on alternate page 2
    echo('<a href="?page_id=' . PAGE_1_ALT . '"><button class="sprocketrButton">Edit Original Input</button></a>');
  }
  echo('</td>');

  echo('<table>');
}

function cleanOptions()
{
  $msg = "";

  $_SESSION["options"] = array_map("unserialize", array_unique(array_map("serialize", $_SESSION["options"]))); // delete duplicates
  $_SESSION["options"] = array_values($_SESSION["options"]); // reindex array to remove gaps
  foreach ($_SESSION["options"] as $i => $option){
    if ($option["littleTeeth"]/$option["ratio_little"] != $option["bigTeeth"]/$option["ratio_big"]){
      unset($_SESSION["options"][$i]);
      continue;
    }
    if (($option["littleTeeth"] < VERSA_HUB_MIN || $option["bigTeeth"] < VERSA_HUB_MIN) 
	&& isset($option["holeOption_versaHub"])){
      $msg = "Options with fewer than " . VERSA_HUB_MIN . " teeth are too small for the versa hub and have been removed";
      unset($_SESSION["options"][$i]);
      continue;
    }
    else if (($option["littleTeeth"] < VERSA_BEARING_HOLE_MIN || $option["bigTeeth"] < VERSA_BEARING_HOLE_MIN) 
	     && isset($option["holeOption_versaBearingHole"])){
      $msg = "Options with fewer than " . VERSA_BEARING_HOLE_MIN . " teeth are too small for the versa bearing hole and have been removed";
      unset($_SESSION["options"][$i]);
      continue;
    }
    else if (($option["littleTeeth"] < TETRIX_HUB_MIN || $option["bigTeeth"] < TETRIX_HUB_MIN) 
	      && isset($option["holeOption_tetrixHub"])){
      $msg = "Options with fewer than " . TETRIX_HUB_MIN . " teeth are too small for the tetrix hub and have been removed";
      unset($_SESSION["options"][$i]);
      continue;
    }
  }
  return $msg;
}

/* createOptionsTable() - creates and populate the table of sprocket possibilities. Also
                          creates the scripts to deal with the reordering of the table
			  based on user clicks. Note that the "best" option is simply
			  the first option in the $options array.
*/
function createOptionsTable(&$options)
{

  echo('<td style="width:70%;vertical-align:middle">');
  echo('<p style="text-align:center;color:red">'.cleanOptions().'</p>');
  echo('<div style="overflow:scroll;height:500px">');
  if (sizeof($options) == 1){
    echo('<h3 style="text-align:center">');
    echo('No further options for current settings. Select "Ratio" or "Center to Center" to generate more!</h3>');
  }
  echo("<table style=\"margin-left:auto;margin-right:auto;\" id=\"optionsTable\">");
  tableRow(array('<th style="border:none;"></th>',
		 tableHeader("Ratio", "center", "middle"),
		 tableHeader("Numbers of Teeth", "center", "middle"),
		 tableHeader("Sprocket Diameters (mm)", "center", "middle"),
		 tableHeader("C2C Distance (mm)", "center", "middle"),
		 tableHeader("Chain Length (chain links)", "center", "middle"),
		 tableHeader("Slack (chain links)", "center", "middle"),
		 tableHeader("Distance From Desired Slack (chain links)", "center", "middle")),"th");
  echo("<tbody>");

  foreach ($options as $option){

    // create hover text to show ratio as a fraction (to better compare with original input)
    $originalRatio = explode(":",$_SESSION["user_params"]["gearRatioField"]);
    $div = $option["littleTeeth"]/$originalRatio[0]; // divide the smaller number of teeth by the smaller # of ratio
    $bigTeeth = round($option["bigTeeth"]/$div,3);
    $span_title_ratio = $originalRatio[0] . " : $bigTeeth";

    // create hover text to convert all measurements to inches
    $span_title_diameter = round($option["littleDiameter"]*MM_2_IN,3) . " : " . round($option["bigDiameter"]*MM_2_IN,3) . " in";
    $span_title_c2c = round($option["Center To Center"]*MM_2_IN,3) . " in"; // convert to inches

    highlightableTableRow(
    array('<td style="border:none;text-align:right;" id="marker' . $i . '"></td>',
	  tableData("<span title=\"$span_title_ratio\">".$option["ratio_little"]." : ".$option["ratio_big"]."</span>", "center"),
	  tableData($option["littleTeeth"]." : ".$option["bigTeeth"], "center"),
	  tableData("<span title=\"$span_title_diameter\">".$option["littleDiameter"]." : ".$option["bigDiameter"]."</span>","center"),
	  tableData("<span title=\"$span_title_c2c\">".$option["Center To Center"]."</span>", "center"),
	  tableData($option["length"], "center"),
	  tableData($option["slack"], "center"),
	  tableData($option["distFromDesiredSlack"], "center")),"", "$i");
  }
  echo("</div></tbody></table></td>");
  // create script to reorder table
  echo('
       <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
       <script type="text/javascript">
       $(document).ready(function(){
          $("#optionsTable tr").eq(1).children().eq(0).html("Selected:");
             $("#optionsTable tr").eq(1).css("background-color", "#ADC2EB");
          $("#optionsTable tr").click(function(){
             var index = $("#optionsTable tr").index(this) - 1;
             $("#th").after(this);
             $("#optionsTable tr td:first-child").html("");
             $("#optionsTable tr").css("background-color", "white");
             $("#optionsTable tr").eq(1).children().eq(0).html("Selected:");
             $("#optionsTable tr").eq(1).css("background-color", "#ADC2EB");
             var fileName = "reorder.php";
             $.post(fileName, {"option_id" : index});
           });
       });
      </script>');
}

/* displayForm() - a function to display the fields of the form. It takes
                   in the $data array in order to show the existing data,
                   as well as using $badFields to know which field prompts
                   should be highlighted as "bad" (aka need to be fixed)
*/
function displayForm($data, $badFields)
{
  displayTitle();
echo("<p style=\"text-align:center;margin:auto;width:80%\">");
echo ('<b>About SprocketR: </b>SprocketR is a web application to produce custom sprocket pairs based upon the parameters entered. The final output is a zip file of two 3D-printable STL files to download and test. During the process, the application will optimize the sprockets to create an appropriate amount of chain slack. In addition, it will give you the opportunity to change and balance your parameters to get the best sprockets for your application.</p>');
echo('<table style="text-align:center;margin:auto;width:20%"><tr><td style="text-align:left;border:none">');
echo('<a href="?page_id=2065"><button class="sprocketrButton">How It Was Created</button></a>');
echo('</td><td style="text-align:right;border:none">');
echo('<a href="?page_id=2090"><button class="sprocketrButton">How To Use</button></a>');
echo('</td></tr></table>');
echo('<div style="margin:auto;width:35%"><b>Instructions: </b>');
echo('<ul><li>Start by entering the desired "gear ratio" (if necessary) and the center-to-center distance of the two sprockets.</li>');
echo('<li>You may also enter the number of the teeth for one or both of the sprockets if needed.</li>');
echo('<li>Choose the chain type desired.</li>');
echo('<li>Choose the hub mounting holes for the sprockets (if any).</li>');
echo('<li>Click "Generate Sprocket Options".</li>');
echo('<li>The results page will provide the opportunity to change parameters to get the sprockets to best fit your application.</li>');
echo('</ul></div>');

echo("<p style=\"text-align:center;margin:auto;width:80%\">");

//start form and table
echo"<form action=\"\" class=\"sprocketrForm\" method=\"get\">
     <table frame=\"void\" border=\"none\" class=\"form\" style=\"width:50%\" align=\"center\">
     "; //add class="form" when ready to remove gridlines

//gearRatioField
if(array_key_exists("gearRatioField",$data)) { //check if gear ratio entered if so show in field
  tableRow(array(tableData(promptWithError("gearRatioField", "<b>Gear Ratio*: </b>", $badFields, "Must be in the form of #:#"),"left","center"), //table row initialization and gear ratio prompt
	       tableData('<input
                               style="text-align: center;"
                               size="5"
                               type="text"
                               placeholder="#:#"
                               name="gearRatioField"
                               value="' . $data["gearRatioField"] . '"
                               title="Gear ratio of your sprockets, must be formatted like #:#"
                             />
                          ', "left","center"))); //gear ratio field
} else { //if not entered show without data (see above comments, they still apply below)
  tableRow(array(tableData(promptWithError("gearRatioField", "<b>Gear Ratio*: </b>", $badFields, "Must be in the form of #:#"),"left","center"),
		 tableDataWidth('<input
                               style="text-align: center;"
                               size="5"
                               type="text"
                               placeholder="#:#"
                               name="gearRatioField"
                               title="Gear ratio of your sprockets, must be formatted like #:#"
                                                             />
                          ', "left","center",1,"gRatFieldRow")));
}

//centerToCenterField
tableRow(array(tableData(promptWithError("centerToCenterField", "<b>Center to Center Distance (mm)*:</b>", $badFields, "Must be a number with optional decimal part"),"left","center",1),
	       tableData(text($data,"centerToCenterField"),"left","center",2)));

//chainSizeList
tableRow(array(tableData(prompt("<b>Chain Size*: </b>"), "", "", "", "margin:0px"),
	       tableData(dropDown($data, "chainSizeList", array ("25" => "chain25", "35" => "chain35")))),"chainSize");

//leftSprocketTeeth#Field
tableRow(array(tableData(promptWithError("leftSprocketTeeth#Field", '<b>Number of Teeth on Sprocket 1: </b><span style="color:red"> (optional)</span>', $badFields, "Must be an integer less than " . NUM_TEETH_MAX . " or left blank"),"left","center",1),
	       tableData(text($data,"leftSprocketTeeth#Field"),"left","center",1)),"leftSprocketTeeth");

//rightSprocketTeeth#Field
tableRow(array(tableData(promptWithError("rightSprocketTeeth#Field", '<b>Number of Teeth on Sprocket 2: </b><span style="color:red"> (optional)</span>', $badFields, "Must be an integer less than " . NUM_TEETH_MAX . " divisible by the gear ratio or left blank"),"left", "center",1),
	       tableData(text($data,"rightSprocketTeeth#Field"),"left", "center", 1)), "rightSprocketTeeth");

//desiredSlackField
tableRow(array(tableData(promptWithError("desiredSlackField", "<b>Desired Slack (default 1%): </b>",$badFields, "Must be a decimal or left blank")),
               tableData(text($data,"desiredSlackField"))), "desiredSlack");

//holeOptionLabels
echo ("<tr><td class = \"form\"><b>Hub Choices:</b></td><td>");
echo ('<table><tr>');
holeCheckBox("Versa Hub (small).png",$data,"holeOption_versaHub"," Versa Hub");
holeCheckBox("Tetrix Hub (small).png",$data,"holeOption_tetrixHub"," Tetrix Hub");
holeCheckBox("Versa Bearing Hole (small).png",$data,"holeOption_versaBearingHole"," Versa Bearing Hole");
echo('</tr></table></td></tr>');

// submit button & hidden field
tableRow(array(tableData(hiddenField("page_id",$_GET["page_id"]))));

echo '<tr><td class="centerCell" colspan=2 align="center">' . "<input type=\"submit\" class=\"sprocketrButton\" value=\"Generate Sprocket Options\">"  . '</td></tr>';
formFooter("sprocketrForm");

}

/* formValidate() - checks if the contents of the various fields are valid. If not,
                    they are added to the array of "badFields".
 */
function formValidate($data)
{
  $badFields = array();
  
  // validate gear ratio
  if ($data["gearRatioField"] != ""){
    $pieces = explode(":",$data["gearRatioField"]);
    $left = min($pieces);
    $right = max($pieces);
    if (is_numeric($left) && is_numeric($right) && count($pieces)==2){
      if (($left == 0) || ($right == 0)) {
	$badFields["gearRatioField"] = "Gear ratios cannot contain 0";
      }
      $ratio = convertToIntegerRatio($left, $right);
      $int1 = $ratio[0];
      $int2 = $ratio[1];
      if ($int1 > NUM_TEETH_MAX || $int2 > NUM_TEETH_MAX){
	$badFields["gearRatioField"] = "Sprockets would be too large!";
      }
      $data["gearRatioField"] = "$int1:$int2";
      if (( ($int2/$int1) > RATIO_MAX ) && 
	  empty($data["leftSprocketTeeth#Field"]) && empty($data["rightSprocketTeeth#Field"])) {
	$badFields["gearRatioField"] = "Gear ratio too large";
      }
    } else { // not numeric
      $badFields["gearRatioField"] = "Not in the form \"#:#\"";
    }
  } else { // no gear ratio specified and no teeth specified
    if (empty($data["leftSprocketTeeth#Field"]) || empty($data["rightSprocketTeeth#Field"])){
      $badFields["gearRatioField"] = "Please enter a gear ratio";
    }
  }

  if(!empty($data["leftSprocketTeeth#Field"]) && !empty($data["rightSprocketTeeth#Field"])){
      unset($data["gearRatioField"]);
      // swap values if the # of teeth on the left > # teeth on the right
      if($data["leftSprocketTeeth#Field"] > $data["rightSprocketTeeth#Field"]){
	$temp = $data["leftSprocketTeeth#Field"];
	$data["leftSprocketTeeth#Field"] = $data["rightSprocketTeeth#Field"];
	$data["rightSprocketTeeth#Field"] = $temp;
	}
  }

  // validate center to center field
  if (!is_numeric($data["centerToCenterField"])){
    $badFields["centerToCenterField"] = "Center to center must be a number";
  }
  if (empty($data["centerToCenterField"])){
    $badFields["centerToCenterField"] = "Center to center cannot be zero";
  }
  if ($data["centerToCenterField"] < 15){
    $badFields["centerToCenterField"] = "Center to center must be at least " . C2C_MIN . " mm";
  }

  // validate number of left sprocket teeth
  if ($data["leftSprocketTeeth#Field"] != ""){
    if (intval($data["leftSprocketTeeth#Field"]) == 0){
      $badFields["leftSprocketTeeth#Field"] = "Number of teeth cannot be 0!";
    }
    if ($data["leftSprocketTeeth#Field"] < NUM_TEETH_MIN || $data["leftSprocketTeeth#Field"] > NUM_TEETH_MAX){
      $badFields["leftSprocketTeeth#Field"] = "Must have between " . NUM_TEETH_MIN . " and " . NUM_TEETH_MAX . " teeth";
    }
    if ($data["leftSprocketTeeth#Field"] < TETRIX_HUB_MIN && isset($data["holeOption_tetrixHub"])){
      $badFields["leftSprocketTeeth#Field"] = "Sprockets need to have " . TETRIX_HUB_MIN . " or more teeth to fit tetrix hubs";
    }
    if ($data["leftSprocketTeeth#Field"] < VERSA_BEARING_HOLE_MIN && isset($data["holeOption_versaBearingHole"])){
      $badFields["leftSprocketTeeth#Field"] = "Sprockets need to have " . VERSA_BEARING_HOLE_MIN . " or more teeth to fit versa bearing holes";
    }
    if ($data["leftSprocketTeeth#Field"] < VERSA_HUB_MIN && isset($data["holeOption_versaHub"])){
      $badFields["leftSprocketTeeth#Field"] = "Sprockets need to have " . VERSA_HUB_MIN . " or more teeth to fit versa hubs";
    }
    if (isset($data["gearRatioField"]) && isset($int2) && ($data["leftSprocketTeeth#Field"]/$int1)*$int2 > NUM_TEETH_MAX){
      $badFields["leftSprocketTeeth#Field"] = "This would generate a matching sprocket that is too large!";
    }
  }

  // validate number of right sprocket teeth
  if ($data["rightSprocketTeeth#Field"] != ""){
    if (intval($data["rightSprocketTeeth#Field"]) == 0){
      $badFields["rightSprocketTeeth#Field"] = "Number of teeth cannot be 0!";
    }
    else if ($data["rightSprocketTeeth#Field"] < NUM_TEETH_MIN || $data["rightSprocketTeeth#Field"] > NUM_TEETH_MAX){
    $badFields["rightSprocketTeeth#Field"] = "Must have between " . NUM_TEETH_MIN . " and " . NUM_TEETH_MAX . " teeth";
    }
    else if ($data["rightSprocketTeeth#Field"] < VERSA_HUB_MIN && isset($data["holeOption_versaHub"])){
      $badFields["rightSprocketTeeth#Field"] = "Sprockets need to have " . VERSA_HUB_MIN . " or more teeth to fit versa hubs";
    }
    else if ($data["rightSprocketTeeth#Field"] < VERSA_BEARING_HOLE_MIN && isset($data["holeOption_versaBearingHole"])){
      $badFields["rightSprocketTeeth#Field"] = "Sprockets need to have " . VERSA_BEARING_HOLE_MIN . " or more teeth to fit versa bearing holes";
    }
    else if ($data["rightSprocketTeeth#Field"] < TETRIX_HUB_MIN && isset($data["holeOption_tetrixHub"])){
      $badFields["rightSprocketTeeth#Field"] = "Sprockets need to have " . TETRIX_HUB_MIN . " or more teeth to fit tetrix hubs";
    }
    else if (isset($data["gearRatioField"]) && isset($int2) && ($data["rightSprocketTeeth#Field"]/$int2)*$int1 < NUM_TEETH_MIN){
      $badFields["rightSprocketTeeth#Field"] = "This would generate a matching sprocket that is too small!";
    }
  }

  if (empty($data["leftSprocketTeeth#Field"]) && (( $data["rightSprocketTeeth#Field"] * $int1) % $int2 != 0)){
    $badFields["rightSprocketTeeth#Field"] = "Incompatible with gear ratio";
  }

   // validate desired slack
  if ($data["desiredSlackField"] != ""){
    if (!is_numeric($data["desiredSlackField"])){
      $badFields["desiredSlackField"] = "Slack must be a number";
    }
    if (intval($data["desiredSlackField"]) < 0){
      $badFields["desiredSlackField"] = "Slack can't be negative!";
    }
  }
  
  return $badFields;

 return array();
}

/*  showDataSummary() - shows all the data the user intered in a non-changable list.
                        Also shows if gear ratio is overwritten
*/
function showDataSummary($data)
{
  echo('<table class="form" style="width:70%; margin:auto">');
  echo('<tr><td style="width:30%">');
  echo('<h3 style="text-align:center">Original Input:</h3>');
  echo('<table class="form" style="border:1px solid black;">');
  //Gear Ratio
  if(!empty($data["leftSprocketTeeth#Field"]) && !empty($data["rightSprocketTeeth#Field"]) && !empty($data["gearRatioField"])){
    tableRow(array(tableData(promptWithError("<b>Gear Ratio: </b>",true,"","","Gear Ratio overwritten due to both left and right sprocket teeth filled in")),tableData('-')));
  }
  else if (empty($data["gearRatioField"])){
    tableRow(array(tableData(prompt("<b>Gear Ratio: </b>")),tableData("(not specified)")));
  } else {
    tableRow(array(tableData(prompt("<b>Gear Ratio: </b>",false)),tableData($data["gearRatioField"],"", "", "", "width:20%")));
  }
  //Center to Center
  tableRow(array(tableData(prompt("<b>Center to Center Distance (mm): </b>")),tableData($data["centerToCenterField"],"", "", "", "width:20%")));
  //Chain Size
  tableRow(array(tableData(prompt("<b>Chain Size: </b>")),tableData(substr($data["chainSizeList"],5,2), "width:20%"))); 
  //Left Sprocket Teeth
  if(empty($data["leftSprocketTeeth#Field"])) {
    tableRow(array(tableData(prompt("<b>Number of Teeth on Sprocket 1: </b>")),tableData("(not specified)")));
  } else {
    tableRow(array(tableData(prompt("<b>Number of Teeth on Sprocket 1: </b>")),tableData($data["leftSprocketTeeth#Field"],"", "", "", "width:20%")));}
  //Right Sprocket Teeth
  if(empty($data["rightSprocketTeeth#Field"])){
    tableRow(array(tableData(prompt("<b>Number of Teeth on Sprocket 2: </b>")),tableData("(not specified)")));
  } else {
    tableRow(array(tableData(prompt("<b>Number of Teeth on Sprocket 2: </b>")),tableData($data["rightSprocketTeeth#Field"],"", "", "", "width:20%")));}
  //Desired Slack
  if($data["desiredSlackField"] == ""){
    tableRow(array(tableData(prompt("<b>Desired Slack (%): </b>")),tableData("(default is 1)"))); 
  } else {
    tableRow(array(tableData(prompt("<b>Desired Slack (%): </b>")),
		   tableData($data["desiredSlackField"],"", "", "", "width:20%")));
  }

  //Holes
  
  $holeString = "";
 $keys =   array_keys($data);
 $phrase = "holeOption";
  foreach($keys as $key) {
    $pos = strpos($key, $phrase);
    if($pos !== false)
      {
	$holeString .= substr($key,$pos+strlen($phrase) + 1) . ", ";
      }
  }
  $holeString = substr($holeString, 0, -2);
  if(strcmp($holeString,"") == 0){ tableRow(array(tableData(prompt("<b>Hub Options: </b>")),tableData("(none specified)"))); } else {
    tableRow(array(tableData(prompt("<b>Hub Options: </b>")),tableData($holeString))); }
  echo('</table></td>');
}

/* showPhase2HelpText() - displays the paragraph at the top of the phase 2 page
 */
function showPhase2HelpText()
{
  echo("<p style=\"text-align:center;margin:auto;width:70%\">");
  echo('Below are the options you just inputted as well as a table of possible sprocket pairs that can be made from the options you selected. You can hover over the ratios to view them in decimal form. If you would like to further refine those choices, select one of the tuning options below. The option you select will then be varied to try to produce the least possible amount of slack. When you are finished, you can hit "preview" or "download" to view or download your sprockets you have selected.</p>');
echo('<table style="text-align:center;margin:auto;width:20%"><tr><td style="text-align:left;border:none">');
echo('<a href="?page_id=2065"><button class="sprocketrButton">How It Was Created</button></a>');
echo('</td><td style="text-align:right;border:none">');
echo('<a href="?page_id=2090"><button class="sprocketrButton">How To Use</button></a>');
echo('</td></tr></table>');
}
?>
