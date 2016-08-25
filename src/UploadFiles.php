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

class UploadFiles
{	
	protected $location;
	protected $resized_location;
	protected $max_size;
	protected $extensions;
	protected $resize;
	protected $rwidth;	
	protected $rheight;	
	protected $newname;	
	protected $resizedname;
	protected $rquality;
	
	function __construct()
	{		
		$this->max_size = 1048576;
		$this->rwidth = 450;
		$this->rheight = 350;
		$this->resize = false;
		$this->newname = 'dumbflower_';
		$this->resizedname = 'resized_dumbflower_';		
		$this->rquality = 80;
		$this->extensions = array('gif', 'png', 'jpg', 'webp');
		$this->location = str_replace('\\', '/', dirname(__DIR__) . '/img/');
		$this->resized_location = str_replace('\\', '/', dirname(__DIR__) . '/resized/');
	}

	public function setLocation($location = null)
	{
		if(isset($location)){
			if(is_dir($location)){
				$this->location = $location;	
			}			
		}else {
			$default = str_replace('\\', '/', dirname(__DIR__) . '/img/'); 
			try{
				if(!file_exists($default)){
					mkdir($default, 0777);	
				}				
				if(is_dir($default)){
					$this->location = $default;
				}
			}catch(Exception $e){
				return 'The directory exists';
			}
		}				
		return $this->location;
	}

	public function setSize($size)
	{
		if(is_int($size)){
			if($size >= 102400){
				$this->max_size = $size;
			}			
		}else {
			return false;
		}
		
		return $this->max_size ? $this->max_size = $size : 1048576;
	}

	public function setExtensions($ext = array('png', 'jpg', 'gif', 'webp'))
	{
		$this->extensions = $ext;
		
		return $this->extensions ? $this->extensions = $ext : array('png', 'jpg', 'gif', 'webp');
	}
	
	public function shouldResize($scale = false, $location = null, $w = 450, $h = 350)
	{
		if(is_bool($scale)){
			$this->resize = $scale;
			switch($this->resize){
				case true:
					if(is_int($w) && is_int($h) && $w > 0 && $h > 0){
						$this->rwidth = $w;
						$this->rheight = $h;	
					}
					break;
					
				case false:
					$this->rwidth = 0;
					$this->rheight = 0;
					break;
			}
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
		}else {
			return "Cannot resize!";
		}
		
		$fresize = $this->resize ? $this->resize = $scale : false;
					
		return array(
			'resize' => $fresize,
			'resized_location' => $this->resized_location,
			'resize_width' => $this->rwidth,
			'resize_height' => $this->rheight
		);
	}

	public function setResizedImageQuality($quality)
	{
		if(is_int($quality)){
			if($quality > 0 && $quality <= 100){
				$this->rquality = $quality;	
			}				
		}else {
			return "Please set a proper image quality";
		}
		
		return $this->rquality ? $this->rquality = $quality : 80;
	}
		
	public function setPrefix($new, $resized)
	{
		if($this->resize === true){
			$this->newname = $new;
			$this->resizedname = $resized;
		}
		
		$fname =  $this->newname ? $this->newname = $new : 'dumbflower_';
		$fresizename = $this->resizedname ? $this->resizedname = $resized : 'resized_dumbflower_';
		
		return array(
			'new_name' => $fname,
			'resized_name' => $fresizename
		);
	}
	
	public function uploadImage($img)
	{
		if(!empty($img)){
			$tmp = $img["tmp_name"];
			$name = basename($img["name"]);
			$mime = $img["type"];
			$error = $img["error"];
			$size = $img["size"];
			$ext = explode('.', $name);
			$ext = $ext[1];
			if(isset($this->location, $this->max_size)){
				if(is_uploaded_file($tmp)){
					if(!empty($name)){
						if($size <= $this->max_size){
							if(in_array($ext, $this->extensions)){
								$real = $this->location . $this->newname . $name;
								if($this->resize === true){
									$resized = $this->resized_location . $this->resizedname . $name;
									if(move_uploaded_file($tmp, $real)){
										$this->resizeImage($real, $resized, $ext);
										return array(
											'thumbnail' => $resized,
											'large' => $real
										);
									}else {
										return 'Failed to upload image';
									}
								}else {
									if(move_uploaded_file($tmp, $real)){
										return $real;
									}else {
										return 'Failed to upload image';
									}
								}
							}else {
								return "Please upload a file of the appropriate extension";
							}
						}else {
							return "The file is too large";
						}
					}else {
						return "Please upload an image";
					}
				}else{
					return 'Please upload an image';
				}
			}
		}	
	}
	
	public function uploadMultiple($image)
	{
		$uploaded = array();
		$failed = array();
		foreach($image["name"] as $index => $img){
			$tmp = $image['tmp_name'][$index];
			$name = $image['name'][$index];
			$error = $image['error'][$index];
			$ext = explode(".", $name);
			$ext = strtolower($ext[1]);
			$size = $image['size'][$index];
			if(is_uploaded_file($tmp) && $error == 0){
				if($size <= $this->max_size){
					if(in_array($ext, $this->extensions)){
						$real = $this->location . $this->newname . $name;
						if($this->resize === true){
							$resized = $this->resized_location . $this->resizedname . $name;
							if(move_uploaded_file($tmp, $real)){
								$this->resizeImage($real, $resized, $ext);
								$uploaded[] = [$real, $resized];
							}else {
								$failed[$index] = "[{$img}] cannot be uploaded";
							}							
						}else {
							if(move_uploaded_file($tmp, $real)){
								$uploaded[$index] = $real;
							}else {
								$failed[$index] = "[{$img}] cannot be uploaded";
							}							
						}
					}else {
						$failed[$index] = "[{$img}] is not of the right format";
						var_dump($ext);
					}
				}else {
					$failed[$index] = "[{$img}] is too large";	
				}	
			}else {
				return "Please upload a file";
			}			
		}
		if(!empty($failed)){
			return $failed;
		}else {
			return $uploaded;
		}
		if(!empty($uploaded)){
			return $uploaded;
		}else{
			return $failed;
		}
	}

	public function resizeImage($img, $resized, $ext)
	{
		list($worig, $horig) = getimagesize($img);
		$aspect_ratio = $worig / $horig;
		if(($this->rwidth / $this->rheight) > $aspect_ratio){
			$this->rwidth = $this->rheight * $aspect_ratio;
		}else {
			$this->rheight = $this->rwidth / $aspect_ratio;
		}
		
		$resized_img = '';
		if($ext == 'GIF' || $ext == 'gif'){
			$resized_img = imagecreatefromgif($img);
		}else if($ext == 'PNG' || $ext == 'png'){
			$resized_img = imagecreatefrompng($img);
		}else {
			$resized_img = imagecreatefromjpeg($img);
		}
		
		$color = imagecreatetruecolor($this->rwidth, $this->rheight);
		imagecopyresampled($color, $resized_img, 0, 0, 0, 0, $this->rwidth, $this->rheight, $worig, $horig);
		imagejpeg($color, $resized, $this->rquality);
	}
	
}
