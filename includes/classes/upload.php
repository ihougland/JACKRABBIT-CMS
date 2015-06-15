<?php
class uploader
{
	var $fileName;
	var $fileType;
	var $fileSize;
	var $allowedFileTypes;
	var $fileSizeLimit=7000000000;
	//destination from web root	
	var $fileDestination;
	
	
	function uploader($destination='../files_uploaded/')
	{
		$this->allowedFileTypes = array();
		$this->setFileDestination($destination);
	}
	
	function setFileDestination($destination)
	{
		$this->fileDestination = $destination;
	}
	
	function getFileDestination()
	{
		return $this->fileDestination;
	}
	
	function getFileExtension($filename)
	{
		 $extension = explode(".",$filename);
		 return '.'.strtolower(array_pop($extension));
	}
	
	function getSizeLimit()
	{
		return $this -> fileSizeLimit;
	}
	
	function setSizeLimit($sizeLimit)
	{
		$this -> fileSizeLimit = $sizeLimit;
	}
	
	function addAllowedFileType($fileType)
	{
		$this->allowedFileTypes = array_merge($this->allowedFileTypes,(array)$fileType);
	}
	
	function uploadFile($fieldName)
	{	
		$error = $_FILES[$fieldName]['error'];
		if ($error == UPLOAD_ERR_OK) 
		{
			//$random = md5(uniqid(mt_rand()));
			//$this->fileName = $random.$this->getFileExtension($_FILES[$fieldName]['name']);
			$this->fileName = $this->makeFileName($_FILES[$fieldName]['name']);
			$this->fileType = $_FILES[$fieldName]['type'];
			$this->fileSize = $_FILES[$fieldName]['size'];
			$tmp_name = $_FILES[$fieldName]['tmp_name'];
			if($this->fileSizeLimit>=$this->fileSize)
			{
				if( empty($this->allowedFileTypes) || in_array( $this->getFileExtension( $this->fileName ), $this->allowedFileTypes ) )
				{
					if(!@move_uploaded_file($tmp_name, $this->fileDestination.$this->fileName))
					{
						throw new Exception('File failed to upload.');
					}
					else
					{
						return $this->fileName;
					}
				}
				else
				{
					throw new Exception($this->getFileExtension($this->fileName)." is not an allowed type.");
				}
			}
			else
			{
				throw new Exception('File is too large.');
			}
			//if is an allowed type or less than allowed size
			/*if((empty($this->allowedFileTypes)||in_array($this->getFileExtension($this->fileName),$this->allowedFileTypes))&&($this -> fileSizeLimit>=$this->fileSize))
			{					
				if(!@move_uploaded_file($tmp_name, $this->fileDestination.$this->fileName))
				{
					SRPCore()->log_error("File failed to upload");
				}
				else
				{
					return $this->fileName;
				}
			}
			else
			{
				SRPCore()->log_notice($this->getFileExtension($this->fileName)." is not an allowed type.");
				throw new Exception('Division by zero.');
			}*/
		}
		else
		{
			if($error!=UPLOAD_ERR_NO_FILE)
			{
				throw new Exception('Error uploading file. '.$_FILES[$fieldName]['error']);
			}
			else
				return false;
		}
		//return false;
	}
	
	function getFileName()
	{
		return $this->fileName;
	}
	
	function getFileType()
	{
		return $this->fileType;
	}
	
	function getFileSize()
	{
		return $this->fileSize;
	}
	
	function getName($name)
	{
	 	$name = explode(".",$name);
		return $name[0];
	}
	function makeFileName($org_filename)
	{
		$new_filename = $org_filename;
		$counter = 1;
		
		while(file_exists($this->fileDestination.$new_filename))
		{
			$file_parts = explode('.',$org_filename);
			$new_filename = $file_parts[0].'_'.$counter;
			for($x=1; $x<count($file_parts); $x++)
			{
				$new_filename .= '.'.$file_parts[$x];
			}
			
			$counter++;
		}
		
		return $new_filename;
	}
	
}
?>