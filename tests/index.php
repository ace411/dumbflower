<?php 

require_once __DIR__ . '/../vendor/autoload.php';

use DumbFlower\UploadFiles;

$upload = new UploadFiles();

$upload->setLocation();

$dirname = str_replace("\\", "/", dirname(__DIR__) . '/special/');

$upload->shouldResize(true, $dirname);

$upload->setPrefix('mike_', 'resized_mike');

if(!empty($_FILES['file'])){
	$file = isset($_FILES['file']) ? $_FILES['file'] : false;
	$push = $upload->uploadImage($file);
	var_dump($push);
}

?>
<form action="index.php" method="post" enctype="multipart/form-data">
	<input type="file" name="file">
	<input type="submit" value="Upload">
</form>
