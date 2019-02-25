<?
$file = '../../uploads/1/assets/aegee.png';
$type = 'image/jpeg';
header('Content-Type:'.$type);
header('Content-Length: ' . filesize($file));
readfile($file);
?>