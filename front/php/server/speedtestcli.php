<?php
require dirname(__FILE__).'/../server/init.php';

//------------------------------------------------------------------------------
// check if authenticated
require_once  $_SERVER['DOCUMENT_ROOT'] . '/php/templates/security.php';

exec('../../../back/speedtest-cli --secure --simple', $output);

echo '<h4>'. lang('Speedtest_Results') .'</h4>';
echo '<pre style="border: none;">'; 
foreach($output as $line){
    echo $line . "\n";
}
echo '</pre>';
?>
