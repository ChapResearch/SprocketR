<?php

function centeredFormHeader($action, $frame, $width, $margin = "auto")
{
  echo "<div style=\"margin:auto;width:$width\">
          <form action=\"$action\" method=\"get\">
              <table  frame=\"$frame\" class=\"form\" style=\"margin:$margin\">";
}

function centeredFormFooter($name)
{
  echo "</table>  <input type=\"hidden\" name=\"$name\" value=\"true\"> </form></div>";
}

function promptWithError($prompt, $isRed = "", $id = "", $hoverText = "", $errorText = "", $style = "")
  {
    $retString = "<p id=\"$id\" style=";
    if ($isRed){
      $retString .= "\"color:red\"";
    }
    if ($style != ""){
      $retString .= "\"$style\"";
    }
    $retString .= ">";
    if ($hoverText != ""){
        $retString .= "<span title=\"$hoverText\">$prompt</span>";
      }else{
        $retString .= "$prompt";
      }
    if ($isRed){
        $retString .= "<br> $errorText";
      }
    $retString .= "</p>";
    return($retString);
  }

function lockIcon($name = "")
{
  echo('<label class="lock">
        <input type="checkbox" name="' . $name . '" value="lock">
        <img src="http://www.clipartbest.com/cliparts/9cR/RGb/9cRRGbK7i.png" alt="Lock (click me!)" width="25" height="25"
        </label>');
}

function approxIcon($name = "")
{
  echo('<label class="approx">
        <input type="checkbox" name="' . $name . '" value="approx">
        <img src="http://etc.usf.edu/clipart/41600/41697/fc_approx_41697_lg.gif" alt="Approximate (click me!)" width="25" height="25">
        </label>');
}

function tableDataWidth($data, $align = "",$valign = "",$colspan = "",$id = "")
{
  $retString = "<td";
  if ($align != ""){
    $retString .= " align=\"$align\"";
  }
  if ($valign != ""){
    $retString .= " style=\"vertical-align:$valign\"";
  }
  if ($colspan != ""){
    $retString .= " colspan=\"$colspan\"";
  }
  if ($id != ""){
    $retString .= " id=\"$id\"";
  }
  $retString .= ">$data</td>\n";
  return $retString;
}

function optionsTableHeader($data, $width = "")
{
  $retString = "<th";
  if ($width != "") {
    $retString .= " style=\"width:$width" . "px;\"";
  }
  $retString .= ">$data</th>\n";

}

//
/* highlightableTableRow() - formats the given data inside the table row html tags and prints it.
                             Will also light up when a user mouses over.
 */
function highlightableTableRow($data, $function = "", $id = "")
{
  print("<tr");
  print(" class=\"highlightable\"");
  if ($function != ""){
      print(" onclick=\"$function\"");
  }
  if ($id != ""){
    print(" id=\"$id\"");
  }
  print(">\n");

  foreach($data as $datum){
    print($datum);
  }
  print("</tr>\n\n");
}

//holeCheckBox() - returns a customized checkbox with a 100x100 image and checked based on whether the data includes it
function holeCheckBox($src, $data, $name, $text)
{
      if($data["$name"] == "on") {
	echo('<td class="form"><img src="' . $src . '" style="height:100px; max-width:100px"><br><input type="checkbox" class="input_checkbox" name="' . $name . '" checked >' . $text . '<br /></td>');
      }
  else {
    echo('<td class="form"><img src="' . $src . '" style="height:100px; max-width:100px"><br><input type="checkbox" class="input_checkbox" name="' . $name . '" >
' . $text . '<br /></td>');
  }
}



?>