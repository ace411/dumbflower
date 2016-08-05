# dumbflower
Simple image manipulation library for PHP built atop the PHP GD Library

##Requirements
-PHP GD Library which is usually installed by default (Check your GD information using the gd_info() method)

-PHP 5.5 or higher

##Capabilities

Since this package is subject to constant revision, it will continually be updated. The features on the list below are the core functionalities of dumbflower.

-Image uploads (single and multiple) 

-Image resizing (quality and size constraints considered) 

-Screen capture (screenshot functionality)

-Image gallery generation 

-Filtered image creation

##Samples

__Upload single image__

```php
require_once __DIR__ . '/../vendor/autoload.php';

use DumbFlower\UploadFiles;

$upload = new UploadFiles();

//set the location of the file to be uploaded
//if not specified, a directory will be automatically created
$upload->setLocation();

//set the maximum size of the uploaded file
//1MB by default
$upload->setSize(614400);

//special name for folder of resized images
$dirname = str_replace("\\", "/", dirname(__DIR__) . '/special/');

//allow uploaded image to be automatically resized (optional feature)
//set the width and height of the resized images (aspect ratio is maintained)
$upload->shouldResize(true, $dirname, 450, 350);

//set the prefixes for the new names of the uploaded images (optional)
//dumbflower_ and resized_dumbflower_ are the default prefixes
$upload->setPrefix('mike_', 'resized_mike');

if(!empty($_FILES['file'])){
	$file = isset($_FILES['file']) ? $_FILES['file'] : false;
	$push = $upload->uploadImage($file);
	var_dump($push);
}
```
```html
<form action="index.php" method="post" enctype="multipart/form-data">
	<input type="file" name="file">
	<input type="submit" value="Upload">
</form>
```

Please check out the tests directory for more code samples

##Dealing with problems

Endeavor to create an issue on GitHub when the need arises or send an email to lochbm@gmail.com
