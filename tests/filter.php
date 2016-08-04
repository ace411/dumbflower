<?php

use DumbFlower\Gallery;

require_once __DIR__ . '/../vendor/autoload.php';

$gallery = new Gallery();

$gallery->setLocation();

$gallery->setFilter(true);

$gallery->createFilterDir();

$images = $gallery->displayImages();

foreach($images as $img){
	$created = $gallery->extMatch($img['root']);
	if($created && $gallery->filterEffectImg($created, 'red')){
		$conv = $gallery->setDestroy($img['root'], $created);
		echo "<img src={$conv}>";
	}
}