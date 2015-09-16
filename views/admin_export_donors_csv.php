<?php

define('CSV_SEPARATOR', ',');



// A file handle to PHP output stream
$fp = fopen('php://output', 'w');

// List of columns
$columns = array_keys((array)$posts[0]);

// GET RID OF 'SYLK' ERROR ON MACS! 
// http://support.microsoft.com/kb/215591
foreach($columns as $k=>$v){
  $columns[$k] = strtolower($v);
}


foreach ($posts as $key => $row) {
  $row = (array)$row;
  // Write columns
  if ($key == 0) {
    fputcsv($fp, $columns, CSV_SEPARATOR);
  }
  // Write data
  fputcsv($fp, $row, CSV_SEPARATOR);
}
fclose($fp);


?>