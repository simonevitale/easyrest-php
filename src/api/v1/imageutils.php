<?
// This function will proportionally resize image 
function resizeImage($CurWidth, $CurHeight, $MaxSize, $DestFolder, $SrcImage, $Quality, $ImageType) {
	//Check Image size is not 0
	if($CurWidth <= 0 || $CurHeight <= 0) {
		return false;
	}
	
	//Construct a proportional size of new image
	$ImageScale = min($MaxSize/$CurWidth, $MaxSize/$CurHeight); 
	$NewWidth   = ceil($ImageScale*$CurWidth);
	$NewHeight  = ceil($ImageScale*$CurHeight);
	$NewCanvas  = imagecreatetruecolor($NewWidth, $NewHeight);
	
	if(strtolower($ImageType) == 'image/png') {
		// Saves Alpha
		imagealphablending($NewCanvas, false);
		imagesavealpha($NewCanvas,true);
		$transparent = imagecolorallocatealpha($NewCanvas, 255, 255, 255, 127);
		imagefilledrectangle($NewCanvas, 0, 0, $NewWidth, $NewHeight, $transparent);
	}
	
	// Resize Image
	if(imagecopyresampled($NewCanvas, $SrcImage, 0, 0, 0, 0, $NewWidth, $NewHeight, $CurWidth, $CurHeight)) {
		switch(strtolower($ImageType)) {
			case 'image/png':
				imagepng($NewCanvas, $DestFolder);
				break;
			case 'image/gif':
				imagegif($NewCanvas, $DestFolder);
				break;			
			case 'image/jpeg':
			case 'image/pjpeg':
				imagejpeg($NewCanvas, $DestFolder, $Quality);
				break;
			default:
				return false;
		}
		//Destroy image, frees memory	
		if(is_resource($NewCanvas)) { imagedestroy($NewCanvas);} 
		return true;
	}
}

// This function corps image to create exact square images, no matter what its original size!
function cropImage($CurWidth, $CurHeight, $iSize, $DestFolder, $SrcImage, $Quality, $ImageType) { 
	//Check Image size is not 0
	if($CurWidth <= 0 || $CurHeight <= 0) 
	{
		return false;
	}
	
	//abeautifulsite.net has excellent article about "Cropping an Image to Make Square bit.ly/1gTwXW9
	if($CurWidth>$CurHeight)
	{
		$y_offset = 0;
		$x_offset = ($CurWidth - $CurHeight) / 2;
		$square_size 	= $CurWidth - ($x_offset * 2);
	}else{
		$x_offset = 0;
		$y_offset = ($CurHeight - $CurWidth) / 2;
		$square_size = $CurHeight - ($y_offset * 2);
	}
	
	$NewCanvas 	= imagecreatetruecolor($iSize, $iSize);	
	
	if(strtolower($ImageType) == 'image/png') {
		// Saves Alpha
		imagealphablending($NewCanvas, false);
		imagesavealpha($NewCanvas,true);
		$transparent = imagecolorallocatealpha($NewCanvas, 255, 255, 255, 127);
		imagefilledrectangle($NewCanvas, 0, 0, $square_size, $square_size, $transparent);
	}
	
	if(imagecopyresampled($NewCanvas, $SrcImage, 0, 0, $x_offset, $y_offset, $iSize, $iSize, $square_size, $square_size))
	{
		switch(strtolower($ImageType))
		{
			case 'image/png':
				imagepng($NewCanvas,$DestFolder);
				break;
			case 'image/gif':
				imagegif($NewCanvas,$DestFolder);
				break;			
			case 'image/jpeg':
			case 'image/pjpeg':
				imagejpeg($NewCanvas,$DestFolder,$Quality);
				break;
			default:
				return false;
		}
		
		//Destroy image, frees memory	
		if(is_resource($NewCanvas)) {imagedestroy($NewCanvas);} 
		return true;
	} 
}

?>