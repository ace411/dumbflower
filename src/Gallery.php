<?php 

/**
 * DumbFlower is a PHP package built by Lochemem Bruno Michael
 * The package is anchored on the PHP GD Library (Check your GD details with gd_info())
 *
 * @package DumbFlower
 * @author Lochemem Bruno Michael
 *
 *
 * Features include: 
	 - Image uploads (single and multiple) 
	 - Image resizing (quality and size constraints considered) 
	 - Image gallery generation 
	 - Generating image filters (Eight filters)
	 - Screen capture (screenshot functionality)
 *
 *	 
 * If you need any assistance, send an email to lochbm@gmail.com
 */

namespace DumbFlower;

class Gallery extends UploadFiles
{
	protected $applyFilter;
	protected $filterdir;
	protected $url;
	
	private function scanDirectory($dir)
	{
		if(is_dir($dir)){
			return scandir($dir);
		}
	}
	
	public function setLocation($location = null)
	{
		return parent::setLocation($location);
	}
	
	public function setExts($ext = array('png', 'jpg', 'gif'))
	{
		return parent::setExtensions($ext);
	}
	
	public function setResizedLocation($location = null)
	{
		if(isset($location)){
			if(is_dir($location)){
				$this->resized_location = $location;	
			}else {
				return "Please set a proper location for the resized images";
			}
		}else {
			$default = str_replace('\\', '/', dirname(__DIR__) . '/resized/'); 
			if(!file_exists($default)){
				mkdir($default, 0777);	
			}
			$this->resized_location = $default;
		}
		
		return $this->resized_location;
	}
	
	public function setURL($http = true, $location)
	{	
		if(isset($http) && isset($location)){
			switch($http){
				case true:
					$this->url = str_replace($_SERVER['DOCUMENT_ROOT'], 'http://' . $_SERVER['SERVER_NAME'], $location);
					break;
				
				case false:
					$this->url = str_replace($_SERVER['DOCUMENT_ROOT'], 'https://' . $_SERVER['SERVER_NAME'], $location);
					break;
			}					
		}else {
			return 'Please provide a valid location';
		}
		
		return $this->url;
	}

	public function createFilterDir($dir = null)
	{
		if($this->applyFilter === true){
			if(isset($dir) && is_dir($dir)){
				$this->filterdir = $dir;
			}else {
				$default = str_replace('\\', '/', dirname(__DIR__) . '/filter/'); 
				if(!file_exists($default)){
					mkdir($default, 0777);
				}
				$this->filterdir = $default;
			}	
		}
		
		return $this->filterdir;
	}
		
	public function setFilter($filter_status = false)
	{
		if(is_bool($filter_status)){
			$this->applyFilter = $filter_status;
		}else {
			return 'Error! Please set a boolean value';
		}
		
		return $this->applyFilter ? $this->applyFilter = $filter_status : false;
	}
	
	public function splitImg($img)
	{
		if(!empty($img) || isset($img)){
			list($filename, $ext) = explode('.', $img);
			return strtolower($ext);
		}else {
			return false;
		}		
	}

	public function extMatch($img)
	{
		$ext = explode('.', $img);
		$ext = $ext[1];
		$image = '';
		switch($ext){
			case 'jpg':
				$image = imagecreatefromjpeg($img);
				break;
			
			case 'png':
				$image = imagecreatefrompng($img);
				break;
			
			case 'gif':
				$image = imagecreatefromgif($img);
				break;
		}
		return $image;
	}

	public function filterEffectImg($img, $filter = "smooth")
	{
		$image = "";
		if(isset($this->applyFilter) && $this->applyFilter === true){
			switch($filter){
				case 'smooth':
					$image = imagefilter($img, IMG_FILTER_SMOOTH, 100);
					break;

				case 'gaussian':
					$image = imagefilter($img, IMG_FILTER_GAUSSIAN_BLUR);
					break;

				case 'grayscale':
					$image = imagefilter($img, IMG_FILTER_GRAYSCALE);
					break;
					
				case 'green':
					$image = imagefilter($img, IMG_FILTER_COLORIZE, 0, 255, 0);
					break;
					
				case 'red':
					$image = imagefilter($img, IMG_FILTER_COLORIZE, 255, 0, 0);
					break;
					
				case 'blue':
					$image = imagefilter($img, IMG_FILTER_COLORIZE, 0, 0, 255);
					break;
					
				case 'sketchy':
					$image = imagefilter($img, IMG_FILTER_MEAN_REMOVAL);
					break;
					
				case 'invert':
					$image = imagefilter($img, IMG_FILTER_NEGATE);
					break;
			}	
		}else {
			return 'Select a filter effect';
		}		
		return $image;
	}

	public function setDestroy($orig, $resource)
	{
		$ext = $this->splitImg($orig);
		$image = str_replace($this->location, $this->filterdir, $orig);
		switch($ext){
			case 'jpg':
				imagejpeg($resource, $image);
				imagedestroy($resource);
				break;
			
			case 'png':
				imagepng($resource, $image);
				imagedestroy($resource);
				break;
			
			case 'gif':
				imagegif($resource, $image);
				imagedestroy($resource);
				break;	
		}
		$converted = $this->setURL(true, $image);
		
		return $converted;
	}

	public function displayImages($path = "plain", $extensions = array('jpg', 'png', 'gif', 'webp'))
	{
		if(!isset($path)){
			$directory = $this->location;
		}else {
			switch($path){
				case "plain":
					$directory = $this->location;
					break;
					
				case "filtered":
					if(!isset($this->filterdir)){
						return 'Please select a filter directory';
					}else {
						$directory = $this->filterdir;	
					}
					break;
					
				case "resized":
					$directory = $this->resized_location;
					break;
			}
		}
		if(substr($directory, -1) === '/'){
			$directory = substr($directory, 0, -1);
		}
		$images = $this->scanDirectory($directory);
		
		foreach($images as $index => $img){
			$ext = $this->splitImg($img);
			if(!in_array($ext, $this->setExts($extensions))){
				unset($images[$index]);
			}else{
				$images[$index] = array(
					'root' => $directory . '/' . $img,
					'browser' => $this->setURL(true, $directory) . '/' . $img
				);
			}
		}		
		return count($images) ? $images : false;
	}
	
	public function takeScreenshot($format)
	{
		$time = time();
		$screenshot_dir = str_replace('\\', '/', dirname(__DIR__)). '/dumbflower_screens/';
		if(!file_exists($screenshot_dir)){
			mkdir($screenshot_dir, 0777);
		}
		$img = imagegrabscreen(); 
		switch($format){
			case 'png':
				imagepng($img, $screenshot_dir . "{$time}_screen.png");
				imagedestroy($img);
				break;
			
			case 'jpg':
				imagejpeg($img, $screenshot_dir . "{$time}_screen.jpg");
				imagedestroy($img);
				break;
				
			case 'gif':
				imagegif($img, $screenshot_dir . "{$time}_screen.gif");
				imagedestroy($img);
				break;
		}
		return $screenshot_dir . "{$time}_screen.{$format}";
	}

}
