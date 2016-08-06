<?php

use DumbFlower\Gallery;

require_once __DIR__ . '/../vendor/autoload.php';

$gallery = new Gallery();

//Take a jpg screenshot
$screen = $gallery->takeScreenshot('jpg');

if($screen){
  //output a success message
  echo 'Screenshot successfully captured';
}else {
  //output a failure message
  echo 'Screenshot not captured';
}  
