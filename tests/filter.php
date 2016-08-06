<?php

use DumbFlower\Gallery;

require_once __DIR__ . '/../vendor/autoload.php';

$gallery = new Gallery();

//Fetch the information from the default location
$gallery->setLocation();

//Allow a filter to be set
$gallery->setFilter(true);

//Create a directory for the filtered images
$gallery->createFilterDir();

//Return all the images in a directory 
//Options can be specified
//plain is the default option and returns the non-filtered images
//filtered returns the images from the filter folder
//resized returns all the resized images
$images = $gallery->displayImages();

foreach($images as $img){
	//create an image resource
	$created = $gallery->extMatch($img['root']);
	//create the image with a red color enhancement and save it to the filter directory
	if($created && $gallery->filterEffectImg($created, 'red')){
		$conv = $gallery->setDestroy($img['root'], $created);
		//display the filtered images
		echo "<img src={$conv}>";
	}
}
