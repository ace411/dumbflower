<?php

use DumbFlower\Gallery;

require_once __DIR__ . '/../vendor/autoload.php';

$gallery = new Gallery();

$gallery->setLocation();

$dirname = str_replace("\\", "/", dirname(__DIR__) . '/special/');

$gallery->setResizedLocation($dirname);

$images = $gallery->displayImages('resized');

foreach($images as $img){
	echo "
		<img src='{$img['browser']}'>
	";
}
