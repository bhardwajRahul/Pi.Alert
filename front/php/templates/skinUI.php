<?php

// ###################################
// ## GUI settings processing start
// ###################################

if (file_exists('/home/pi/pialert/db/setting_darkmode')) {
    $ENABLED_DARKMODE = True;
}
foreach (glob("/home/pi/pialert/db/setting_skin*") as $filename) {
    $pia_skin_selected = str_replace('setting_','',basename($filename));
}
if (isset($pia_skin_selected) == FALSE or (strlen($pia_skin_selected) == 0)) {$pia_skin_selected = 'skin-blue';}

// ###################################
// ## GUI settings processing end
// ###################################

?>