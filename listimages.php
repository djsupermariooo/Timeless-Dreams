<?php

$files = array();

$dir = opendir($_SERVER['DOCUMENT_ROOT'].'/images/uploads');
while ($file = readdir($dir)) {
    if ($file == '.' || $file == '..') {
        continue;
    }
    
    $files[] = $file;
}

header('Content-type: application/json');
echo json_encode($files);

?>