<?php 


class Z_IO
{
	public static function getFileExtension($filePath)
	{
    	$path_info = pathinfo($filePath);
    	
    	return $path_info['extension'];		
	}
}