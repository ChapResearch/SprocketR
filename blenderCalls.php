<?php
/* ------------------------------blenderCalls.php--------------------------------------
   all methods to interface with blender running on the server, as well as
   call those methods based on POST data (to allow for asyncronous execution).
   Utility methods are at the top, with blender functions in the middle and
   the calling of those functions at the end.
*/

/* formatParams() - formats the associative array $params as a space separated
   string with a leading space, and 0 for placeholders. Used
   to generate the parameter list for the call to blender
   scripts. The exact composition depends on which "phase" of
   sprocket generation the user is on. Since "numTeeth" is the
   default refinement option (aka way by which to generate 
   slightly different sprockets), its presence means $params
   contains the user inputted data and therefore the names of
   the fields are different (which is horrible, I know).
*/
function formatParams($params)
{
  $paramList = '';

  if (!empty($params["gearRatioField"])){ // using user parameters
    $paramList .= ' ' . $params["gearRatioField"];
  } else if (!empty($params["littleTeeth"])){ // using blender returned values
    $paramList .= ' ' . $params["littleTeeth"] . ':' . $params["bigTeeth"];
  } else { // no user parameter specified
    $paramList .= ' ' . $params["leftSprocketTeeth#Field"] . ':' . $params["rightSprocketTeeth#Field"];
  }

  if (!empty($params["centerToCenterField"])){ // using user parameters
    $paramList .= ' ' . $params["centerToCenterField"];
  } else if (!empty($params["Center To Center"])){ // using blender returned values
    $paramList .= ' ' . $params["Center To Center"];
  } else { // no user parameter specified
    $paramList .= ' 0';
  }

  if (!empty($params["chainSizeList"])){ // using user parameters
    $paramList .= ' ' . $params["chainSizeList"];
  } else { // using blender returned values
    //    $paramList .= ' ' . $params["chainSize"];
    $paramList .= ' chain25';
  }

  if (!empty($params["leftSprocketTeeth#Field"])){ // using user parameters
    $paramList .= ' ' . $params["leftSprocketTeeth#Field"];
  } else { // using blender returned values (or not specified)
    $paramList .= ' 0';
  }

  if (!empty($params["rightSprocketTeeth#Field"])){ // using user parameters
    $paramList .= ' ' . $params["rightSprocketTeeth#Field"];
  } else { // using blender returned values (or not specified)
    $paramList .= ' 0';
  }

  if (isset($params["desiredSlackField"]) && $params["desiredSlackField"] != ""){ // using user parameters
    $paramList .= ' ' . $params["desiredSlackField"];
  } else if (isset($params["desiredSlack"]) && $params["desiredSlack"] != ""){ // using blender returned values
    $paramList .= ' ' . $params["desiredSlack"];
  } else { // not specified
    $paramList .= ' 1';
  }

  $paramList .= ' 0'; // placeholder from input that used to be passed

  if (array_key_exists("holeOption_versaHub", $params)){
    $paramList .= ' ' . $params["holeOption_versaHub"];
  } else {
    $paramList .= ' ' . "off";
  }
  if (array_key_exists("holeOption_tetrixHub", $params)){
    $paramList .= ' ' . $params["holeOption_tetrixHub"];
  } else {
    $paramList .= ' ' . "off";
  }
  if (array_key_exists("holeOption_versaBearingHole", $params)){
    $paramList .= ' ' . $params["holeOption_versaBearingHole"];
  } else {
    $paramList .= ' ' . "off";
  }
  if (isset($params["option"])){
    $paramList .= ' ' . $params["option"];
  } else {
    $paramList .= ' placeHolder';
  }

  return $paramList;
}  

/* returnFileNames() - a function to return the filenames of the files written by the blender script.
   This is accomplished by searching for the "keyphrase" the blender script will
   use, as well as getting rid of the $base_path of the file (which would have
   caused confusion when trying to use the output of this function in an <img>
   tag. Note: this function returns an array!
*/
function returnFileNames($output, $type, $keyphrase = "Saved: ", $base_path = "/var/www-chapresearch/")
{
  $fileNames = array();

  foreach($output as $value){ // loop through the blender output
    $start = stripos($value, $keyphrase); // find the beginning of the keyphrase
    if (is_int($start)){ // if it exists
      $start += strlen($keyphrase) + strlen($base_path); // move pointer past phrase and basepath
      $end = strrpos($value,$type) + strlen($type); // find the last instance of the filetype and move past it
      $fileName = substr($value, $start, $end - $start); // take just what's between the beginning and end
      array_push($fileNames,$fileName);
    }
  }
  if (sizeof($fileNames) != 0){
    return $fileNames;
  }
  return false;
}

/* parseOutput() - finds and parses the JSON output of the blender script, given that it 
   starts with $starting_string.
*/
function parseOutput($output, $starting_string = "JSON output")
{
  $pos = -1;
  
  for ($i = 0; $i < sizeof($output); $i++){
    $result = strpos($output[$i], $starting_string);
    if($result !== false){
      $pos = $i + 1;
      break;
    }
  }
  if ($pos != -1){
    $json = substr($output[$pos],0,(strpos($output[$pos],"}]")+2));
    $decoded = json_decode($json, true);
    return $decoded;
  } else {
    return false;
  }
}

/* cleanUp() - deletes files from the output folder that are over $expir_date days old,
   regardless of file type. Note: the age is determined by when the file was
   last modified.
*/
function cleanUp($expir_date = 7)
{
  $cmd ='find /var/www-chapresearch/SprocketR_Output -mtime +' . $expir_date . ' -type f -delete';
  exec($cmd);
}

/* generateThing() - a general function to generate various outputs from the blender script (given by
   the value of the key "output_type" in the $params.
*/
function generateThing($params)
{
  $fileName = uniqId() . "." . $params["output_type"];
  $cmd = 'blender -b /usr/lib/blender/scripts/addons/';
  if ($params["output_type"] == "png"){
    $cmd .= 'pic.blend'; // needs a camera object in the blend file to generate a picture
  } else {
    $cmd .= 'blend.blend';
  }

  if ($_GET["page_id"] == PAGE_2){
    $cmd .=  ' -P /usr/lib/blender/scripts/addons/' . SCRIPT . ' -- ';
  } else if ($_GET["page_id"] == PAGE_2_ALT){
    $cmd .=  ' -P /usr/lib/blender/scripts/addons/' . SCRIPT_ALT . ' -- ';
  }

  $cmd .= $fileName . formatParams($params);
  $output = array();
  exec($cmd, $output);
  
  cleanUp();
  
  return returnFileNames($output, $params["output_type"]);
}

/* zipSprockets() - puts the two separate sprocket files into
   a zip file for downloading.
*/
function zipSprockets($nameOfZip, $filesToZip)
{
  $zip = new ZipArchive();

  if ($zip->open($nameOfZip, ZipArchive::CREATE)!==TRUE) {
    return false;
  }
  $base_path = "/var/www-chapresearch/";
  $zip->addFile($base_path . $filesToZip[0], "sprocketA.stl");
  $zip->addFile($base_path . $filesToZip[1], "sprocketB.stl");
  $zip->close();
}

/* compareSlack() - a function to compare the slack between two sprocket options.
   Used for ordering the options within the table from least to most
   slack.
*/
function compareSlack($option1, $option2)
{
  if (abs($option1["distFromDesiredSlack"]) == abs($option2["distFromDesiredSlack"])){
    return 0;
  }
  return ((abs($option1["distFromDesiredSlack"]) < abs($option2["distFromDesiredSlack"])) ? -1:1);
}

/* generateOptions() - a method to return all of the sprocket options possible for the given
   input, whether that be the original user data, or a past option. Note
   only the 5 options with the least slack are returned. That is because
   of the way the blender script operates. This is distinct from 
   generateThing() in that it is called normally (not through AJAX).
*/
function generateOptions($params)
{
  if ($_GET["page_id"] == PAGE_2){
    $cmd = 'blender -b /usr/lib/blender/scripts/addons/blend.blend -P';
    $cmd .= ' /usr/lib/blender/scripts/addons/' . SCRIPT . ' -- ';
  } else if ($_GET["page_id"] == PAGE_2_ALT){
    $cmd = 'blender -b /usr/lib/blender/scripts/addons/blend.blend -P';
    $cmd .= ' /usr/lib/blender/scripts/addons/' . SCRIPT_ALT . ' -- ';
  }

  $cmd .= "options.data " . formatParams($params);

  $output = array();
  exec($cmd, $output);

  $options = parseOutput($output);
  if ($options != false){
    usort($options, 'compareSlack');
  }

  return $options;
}

?>