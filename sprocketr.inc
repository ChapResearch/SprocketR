<?php

// indicates page IDs for phase 1 and 2

define("PAGE_1_ALT", 2162);
define("PAGE_2_ALT", 2164);
define("PAGE_1", 189);
define("PAGE_2", 1020);

// defines name of script to use (within blender folder)

define("SCRIPT", "sprocketr.py");
define("SCRIPT_ALT", "SprocketR-RELEASE.py");

// note that the following values are all inclusive (aka they are allowed)

define("RATIO_MAX", 8); // maximum ratio (int2/int1)

define("NUM_TEETH_MAX", 64); // largest number of teeth for any sprocket
define("NUM_TEETH_MIN", 8);  // smallest number of teeth for any sprocket

define("TETRIX_HUB_MIN", 12);        // minimum teeth to be used with tetrix hub
define("VERSA_BEARING_HOLE_MIN", 18); // minimums are experimentally determined
define("VERSA_HUB_MIN", 30);         // values are chosen to make sure the holes don't cut through the sprocket

define("C2C_MIN", 15); // minimum c2c in mm

define("SLACK_DEFAULT", 1); // measured in percent

// conversion factors

define("MM_2_IN", 0.0393701);
?>