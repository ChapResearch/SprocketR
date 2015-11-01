<?php

/* -------------------------------------sprocketr-form.php--------------------------------------------------
   this is simply the php file to generate the fields used to create a sprocket. It will be called from
   sprocketr.php and sprocket-options.php
*/

include("/var/www-chapresearch/wp-content/themes/accesspress-lite/htmlFunctionsSprocketr.php"); //HTML Funtions
include("/var/www-thechapr/htmlFunctions.php");

/* displayTitle() - displays the title used in both phases
 */
function displayTitle()
{
  echo('<h1><p style="text-align: center;"><strong><span style="color: #4169ff;">');
  echo('<big><big><big>Sprocket</big></big></big></span><span style="color:#e03812;">');
  echo('<big><big><big>R</big></big></big></span></strong></p></h1>');
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
		 tableData(submit("Submit!"), "center", "", "2")));
  echo hiddenField("page_id", "1020");
  formFooter("tuningOptions");
  echo "</fieldset>";
  echo '<script>
         document.getElementsByName("numTeeth").style.marginBottom = 0;
         document.getElementsByName("ratio").style.marginBottom = 0;
         document.getElementsByName("c2c").style.color = "red";
        </script>';

  echo('<table class="form" style="width: 50%; margin: auto; margin-top: 10px; text-align:center"><tr><td>');
  echo('<form action="" method="get">');
  echo(hiddenField("page_id", "1020"));
  echo(hiddenField("output_type", "png"));
  echo(submit("Preview"));
  echo('</form></td>');

  echo('<td style="text-align:center">');
  echo('<form action="" method="get">');
  echo(hiddenField("page_id", "1020"));
  echo(hiddenField("output_type", "stl"));
  echo(submit("Download"));
  echo('</form></td>');

  echo('<td style="text-align:center">');
  echo('<form action="" method="get">');
  echo(hiddenField("page_id", "1020"));
  echo(hiddenField("cmd", "reset"));
  echo(submit("Reset to Original Options"));
  echo('</form></td>');

  echo('<td style="text-align:center;width=40%">');
  echo('<a href="?page_id=189"><button>Edit Original Input</button></a>');
  echo('</td>');

  echo('<table>');
}

/* createOptionsTable() - creates and populate the table of sprocket possibilities. Also
                          creates the scripts to deal with the reordering of the table
			  based on user clicks. Note that the "best" option is simply
			  the first option in the $options array.
*/
function createOptionsTable($options)
{
  echo('<td style="width:70%;vertical-align:middle">');
  echo("<table style=\"margin-left:auto;margin-right:auto;\" id=\"optionsTable\">");
  tableRow(array('<th style="border:none;"></th>',
		 tableHeader("Ratio", "center", "middle"),
		 tableHeader("Numbers of Teeth", "center", "middle"),
		 tableHeader("Sprocket Diameters (mm)", "center", "middle"),
		 tableHeader("C2C Distance (mm)", "center", "middle"),
		 tableHeader("Chain Length (mm)", "center", "middle"),
		 tableHeader("Slack (chain links)", "center", "middle"),
		 tableHeader("Distance From Desired Slack (chain links)", "center", "middle")),"th");
  for ($i = 0; $i < sizeof($options); $i++){
    $decimal_ratio = round($options[$i]["bigTeeth"]/$options[$i]["littleTeeth"],2);
    $span_title_ratio= "1 : $decimal_ratio";
    $span_title_c2c = round($options[$i]["Center To Center"]*0.0393701,3) . " in"; // convert to inches
    $span_title_length = round($options[$i]["length"]*0.0393701,3) . " in"; // convert to inches
    highlightableTableRow(
    array('<td style="border:none;text-align:right;" id="marker' . $i . '"></td>',
	  tableData("<span title=\"$span_title_ratio\">".$options[$i]["ratio_little"]." : ".$options[$i]["ratio_big"]."</span>", "center"),
	  tableData($options[$i]["littleTeeth"]." : ".$options[$i]["bigTeeth"], "center"),
	  tableData($options[$i]["littleDiameter"]." : ".$options[$i]["bigDiameter"],"center"),
	  tableData("<span title=\"$span_title_c2c\">".$options[$i]["Center To Center"]."</span>", "center"),
	  tableData("<span title =\"$span_title_length\">".$options[$i]["length"]."</span>", "center"),
	  tableData($options[$i]["slack"], "center"),
	  tableData($options[$i]["distFromDesiredSlack"], "center")),"", "$i");
  }
  echo("</table></td>");
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

  if (sizeof($options) == 1){
    echo('<p style="text-align:center">');
    echo('No further options for current settings. Select "Ratio" or "Center to Center" to generate more</p>');
  }
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
echo ('<b>About SprocketR: </b>SprocketR is a web application to product custom sprocket pairs based upon the parameters that you enter. The final output is a zip file of two 3D-printable .STL files for you to download and test. During the process, the application will try to optimize the sprockets for an appropriate amount of chain slack.  It will also give you the opportunity to change and balance your parameters to get the best sprockets to fit your application. Click <a href="?page_id=189">here</a> for more information on how it was created!</p>');
echo("<p style=\"text-align:center;margin:auto;width:80%\">");
echo('<b>Instructions: </b>Start by entering the "Gear Ratio" that you want and the center-to-center distance between the two sprockets. You can also enter one or both of the number of teeth for the sprockets, but note that we will automatically put the lesser into the left teeth and the greater into the right teeth. Then choose the chain type you want, and the mounting holes for the sprockets. When you are done, simply click "generate" to go to the next page, where you will have the opportunity to change parameters to get the sprockets that fit your application the best.</p>');
//echo("<p style=\"text-align:center;margin:auto;width:80%\">");
//echo('<b>Features: </b><br><button onclick="showFeatures()" type=button>Show Feature List</button><br><ul></ul>');

//start form and table
echo"<form action=\"\" class=\"sprocketrForm\" method=\"get\">
     <table frame=\"void\" border=\"none\" class=\"form\" style=\"width:50%\" align=\"center\">
     "; //add class="form" when ready to remove gridlines

//gearRatioField
if(array_key_exists("gearRatioField",$data)) { //check if gear ratio entered if so show in field
  tableRow(array(tableData(promptWithError("<b>Gear Ratio*: </b>", in_array("gearRatioField",$badFields), "", "", "Must be in the form of #:#"),"left","center"), //table row initialization and gear ratio prompt
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
  tableRow(array(tableData(promptWithError("<b>Gear Ratio*: </b>", in_array("gearRatioField",$badFields), "", "", "Must be in the form of #:#", "margin:0px"),"left","center"),
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
tableRow(array(tableData(promptWithError("<b>Center to Center Distance in mm*:</b>", in_array("centerToCenterField",$badFields),"","","Must be a number with optional decimal part", "margin:0px"),"left","center",1),
	       tableData(text($data,"centerToCenterField"),"left","center",2)));

//chainSizeList
tableRow(array(tableData(prompt("<b>Chain Size*: </b>"), "", "", "", "margin:0px"),
	       tableData(dropDown($data, "chainSizeList", array ("25" => "chain25", "35" => "chain35")))),"chainSize");

//leftSprocketTeeth#Field
tableRow(array(tableData(promptWithError("<b>Number of Left Sprocket Teeth: </b>",in_array("leftSprocketTeeth#Field",$badFields),"","","Must be an integer less than 64 or left blank", "margin:0px"),"left","center",1),
	       tableData(text($data,"leftSprocketTeeth#Field"),"left","center",1)
	       ),"leftSprocketTeeth");

//rightSprocketTeeth#Field
tableRow(array(tableData(promptWithError("<b>Number of Right Sprocket Teeth: </b>",in_array("rightSprocketTeeth#Field",$badFields),"","","Must be an integer less than 64 divisible by the gear ratio or left blank", "margin:0px")),
               tableData(text($data,"rightSprocketTeeth#Field"))), "rightSprocketTeeth");

//desiredSlackField
tableRow(array(tableData(promptWithError("<b>Desired Slack: </b>",in_array("desiredSlackField",$badFields),"","","Must be a decimal or left blank", "margin:0px")),
               tableData(text($data,"desiredSlackField"))), "desiredSlack");

//holeOptionLabels
echo ("<tr><td class = \"form\"><b>Hub Choices:</b></td><td>");
echo ('<table><tr>');
holeCheckBox("Versa Hub (small).png",$data,"holeOption_versaHub"," Versa Hub");
holeCheckBox("Tetrix Hub (small).png",$data,"holeOption_tetrixHub"," Tetrix Hub");
holeCheckBox("Versa Bearing Hole (small).png",$data,"holeOption_versaBearingHole"," Versa Bearing Hole");

/*echo('
<td class="form">
  <img src="Versa Hub (small).png" style="height:100px; max-width:100px"><br>
  <input type="checkbox" class="input_checkbox" name="holeOption_versaHub"/> Versa Hub
  </td>'); 
echo('
<td class="form">
  <img src="Tetrix Hub (small).png" style="height:100px; max-width:100px"><br>
  <input type="checkbox" name="holeOption_tetrixHub" />Tetrix Hub<br />
</td>
<td class="form">
  <img src="Versa Bearing Hole (small).png" style="height:100px; max-width:100px"><br>
  <input type="checkbox" name="holeOption_versaBearingHole" />Versa Bearing Hole<br />
  </td>'); */
echo('</tr></table></td></tr>');

//Spot to tell user if left and right sprocket teeth are both filled in, thus conflicting with gear ratio
//no longer in use: displayed in phaase 2
/* if(in_array("teethWarn",$badFields))
  {
    echo '<tr><td><b><p id="teethWarn" style="color:orange">Ratio is overwritten</p></b></tr></td>';
    } */
// submit button & hidden field
tableRow(array(tableData(hiddenField("page_id","189"))));

echo '<tr><td class="centerCell" colspan=2 align="center">' . "<input type=\"submit\" class=\"sprocketrButton\" value=\"Generate Sprocket Options\">"  . '</td></tr>';
formFooter("sprocketrForm");
}

/* formValidate() - checks if the contents of the various fields are valid. If not,
                    they are added to the array of "badFields".
 */
function formValidate($data)
{

  $badFields = array();
  
  //validate gear ratio
  $txt=$data["gearRatioField"];
  $re1='(\\d+)';// Integer Number 1
  $re2='(:)'; // Colon
  $re3='(\\d+)';// Integer Number 2
  $gearRatioValid = true;
  //regex: if its in the form integer:integer
  if (preg_match_all ("/".$re1.$re2.$re3."/is", $txt, $matches) == 1)
  {
    $int1=$matches[1][0];
    $int2=$matches[3][0];
  } else {
    $gearRatioValid = false;
  }
  if(($int1 == 0) || ($int2 == 0)) {$gearRatioValid = false; }
  if(!$gearRatioValid)
    {
      $badFields[] = "gearRatioField";
    }
 
  if(!is_numeric($data["centerToCenterField"]) ||  empty($data["centerToCenterField"]))
    {
      $badFields[] = "centerToCenterField";
    }

  $leftTeethValid = true;
  // check the value isn't 0
  if($data["leftSprocketTeeth#Field"] != "" && (intval($data["leftSprocketTeeth#Field"]) == 0))
    $leftTeethValid = false;
  // check if the value is between 7 and 65
  if (($data["leftSprocketTeeth#Field"] != "" && $data["leftSprocketTeeth#Field"] < 8) || $data["leftSprocketTeeth#Field"] > 64)
    $leftTeethValid = false;
  // add the field to the "bad fields" array if invalid
  if(!$leftTeethValid)
    $badFields[] = "leftSprocketTeeth#Field";

  
  $rightTeethValid = true;
  // check the value isn't 0
  if($data["rightSprocketTeeth#Field"] != "" && intval($data["rightSprocketTeeth#Field"]) == 0)
    $rightTeethValid = false;
  // check if the value is between 7 and 65
  if (($data["rightSprocketTeeth#Field"] != "" && $data["rightSprocketTeeth#Field"] < 8) || $data["rightSprocketTeeth#Field"] > 64)
    $rightTeethValid = false;
  // check the value is can be used at the "big" value in the given ratio inputted
  // for example, a ratio of 1:5 with a right teeth value of 3 won't work!
  if(( $data["rightSprocketTeeth#Field"] * $int1) % $int2 != 0)
     $rightTeethValid = false;
  // add the field to the "bad fields" array if invalid
  if(!$rightTeethValid)
    $badFields[] = "rightSprocketTeeth#Field";
  
   // TODO - validate desired slack
  
  return $badFields;

 return array();
}

function checkSpecialCases($data)
{
  $simpRatio = array(1,1);
  $wasSimplified = true;
  $switchedBigLittle = false;
  if(!empty($data["leftSprocketTeeth#Field"]) && !empty($data["rightSprocketTeeth#Field"]))
    {
      $data["teethWarn"] = true;

    }
  //check if big:little and switch to little:big
  if(  preg_match_all("/(\\d+)(:)(\\d+)/is",$data["gearRatioField"],$matches) == 1) //splits #:#
  if($matches[1][0] > $matches[3][0])
    {
      $simpRatio = simplify($matches[3][0],$matches[1][0]);
      if($simpRatio[0] == $matches[3][0] && $simpRatio[1] == $matches[1][0]) { $wasSimplifed = false; }
      $data["gearRatioField"] = $simpRatio[0] . ":" . $simpRatio[1];
      $switchedBigLittle = true;
    } else {
  //simplify gear ratio
  $simpRatio = simplify($matches[1][0], $matches[3][0]);
  if($simpRatio[0] == $matches[1][0] && $simpRatio[1] == $matches[3][0]) { $wasSimplifed = false; }
  $data["gearRatioField"] = $simpRatio[0] . ":" . $simpRatio[1];
  }
  echo "WAS SIMPLIFIED";
  echo $wasSimplified;
  /*  
  //unreduced gear ratio shortcut
  //takes 5:10 for example and puts 5 into left teeth and 10 into right teeth
  if($wasSimplified)
    {
      if($switchedBigLittle)
	{
      $data["leftSprocketTeeth#Field"] = $matches[3][0];
      $data["rightSprocketTeeth#Field"] = $matches[1][0];
	} else {
	$data["leftSprocketTeeth#Field"] = $matches[1][0];
	$data["rightSprocketTeeth#Field"] = $matches[3][0];

      }
    }
  */
  return $data;
  
}

function gcd($a,$b) {
  $a = abs($a); $b = abs($b);
  if( $a < $b) list($b,$a) = Array($a,$b);
  if( $b == 0) return $a;
  $r = $a % $b;
  while($r > 0) {
    $a = $b;
    $b = $r;
    $r = $a % $b;
  }
  return $b;
}

function simplify($num,$den) {
  $g = gcd($num,$den);
  return Array($num/$g,$den/$g);
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
  if($data["teethWarn"])
    {
      tableRow(array(tableData(promptWithError("<b>Gear Ratio: </b>",true,"","","Gear Ratio overwritten due to both left and right sprocket teeth filled in")),tableData('-')));
    }
  else
    {
      tableRow(array(tableData(prompt("<b>Gear Ratio: </b>",false)),tableData($data["gearRatioField"],"", "", "", "width:20%")));
    }
  //Center to Center
  tableRow(array(tableData(prompt("<b>Center to Center Distance in mm: </b>")),tableData($data["centerToCenterField"],"", "", "", "width:20%")));
  //Chain Size
  tableRow(array(tableData(prompt("<b>Chain Size: </b>")),tableData(substr($data["chainSizeList"],5,2), "width:20%"))); 
  //Left Sprocket Teeth
  if(empty($data["leftSprocketTeeth#Field"])) { tableRow(array(tableData(prompt("<b>Number of Left Sprocket Teeth: </b>")),tableData("(not specified)"))); } else {
    tableRow(array(tableData(prompt("<b>Number of Left Sprocket Teeth: </b>")),tableData($data["leftSprocketTeeth#Field"],"", "", "", "width:20%")));}
  //Right Sprocket Teeth
  if(empty($data["rightSprocketTeeth#Field"])){ tableRow(array(tableData(prompt("<b>Number of Right Sprocket Teeth: </b>")),tableData("(not specified)"))); } else {
    tableRow(array(tableData(prompt("<b>Number of Right Sprocket Teeth: </b>")),tableData($data["rightSprocketTeeth#Field"],"", "", "", "width:20%")));}
  //Desired Slack
  if(empty($data["desiredSlackField"])){
    tableRow(array(tableData(prompt("<b>Desired Slack: </b>")),tableData("(not specified)"))); 
  } else {
    tableRow(array(tableData(prompt("<b>Desired Slack: </b>")),
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
  echo('Below are the options you just inputted as well as a table of possible sprocket pairs that can be made from the options you selected. You can hover over the ratios to view them in decimal form. If you would like to further refine those choices, select one of the tuning options below. The option you select will then be varied to try to produce the least possible amount of slack. When you are finished, you can hit "preview" or "download" to view or download your sprockets you have selected. Note: the hole options you have selected will not show up in the preview image, but they will be present in the stl.</p>');
}

?>
