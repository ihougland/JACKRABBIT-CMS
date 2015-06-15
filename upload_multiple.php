<?php

/**
 * Handle file uploads via XMLHttpRequest
 */
class qqUploadedFileXhr {
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path, $filename, $extension, $type)
    {     
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        
        if ($realSize != $this->getSize()){            
            return false;
        }
            
        $target = fopen($path."/".$filename.".".$extension, "w"); 
        
              
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);

        //resize full image if needed
        if($extension == 'jpeg' || $extension == 'jpg' || $extension == 'gif' || $extension == 'png')
	    {
	    	$max_upload = SRPCore()->cfg("UPLOAD_MAX");
	    	if($type == "gallery_images")
	    	{
	    		$thumb_width = SRPCore()->cfg("GALLERY_WIDTH");
	    		$thumb_height = SRPCore()->cfg("GALLERY_HEIGHT");
	    	}
	    	elseif($type == "logos")
	    	{
	    		$thumb_width = SRPCore()->cfg("LOGO_WIDTH");
	    		$thumb_height = SRPCore()->cfg("LOGO_HEIGHT");
	    	}
	    	else
	    	{
	    		$thumb_width = 0;
	    		$thumb_height = 0;
	    	}
	    	
	        $image_src = "../files_uploaded/" . stripslashes($filename.".".$extension);
	        $image_size = getimagesize($image_src);
            if($image_size[0]>=$thumb_w && $image_size[1]>=$thumb_h)
            {
    	        if($image_size[0]>$max_upload)
    	        {
    	            smart_resize_image($image_src, $image_src, $max_upload, 0, true, 'file', false, false);
                    $image_size = getimagesize($image_src);
    	        }
    	        if($thumb_width > 0 || $thumb_height > 0)
    	        {
    	        	//create a thumbnail
    		        $thumb_src = "../files_uploaded/thumbs/" . stripslashes($filename.".".$extension);
    		        if($image_size[0]>$thumb_width||$image_size[1]>$thumb_height)
    		        {
    		            smart_resize_image($image_src, $thumb_src, $thumb_width, $thumb_height, true, 'file', false, false);
    		        }
    		        else
    		        {
    		            copy($image_src, $thumb_src);
    		        }
    		    }
            }
            else
            {
                if(file_exists('../files_uploaded/thumbs/'.$filename.".".$extension)) unlink('../files_uploaded/thumbs/'.$filename.".".$extension);
                if(file_exists('../files_uploaded/'.$filename.".".$extension)) unlink('../files_uploaded/'.$filename.".".$extension);
                return false;
            }
	    }
        
        return true;
    }
    
    function getName() {
        return $_GET['qqfile'];
    }
    function getSize() {
        if (isset($_SERVER["CONTENT_LENGTH"])){
            return (int)$_SERVER["CONTENT_LENGTH"];            
        } else {
            throw new Exception('Getting content length is not supported.');
        }      
    }   
}

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class qqUploadedFileForm {  
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {
        if(!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)){
            return false;
        }   
        return true;
        
    }
    function getName() {
        return $_FILES['qqfile']['name'];
    }
    function getSize() {
        return $_FILES['qqfile']['size'];
    }
}

class qqFileUploader {
    private $allowedExtensions = array();
    private $sizeLimit = 31457280;
    private $file;

    function __construct(array $allowedExtensions = array(), $sizeLimit = 31457280){        
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
            
        $this->allowedExtensions = $allowedExtensions;        
        $this->sizeLimit = $sizeLimit;
        
        $this->checkServerSettings();       

        if (isset($_GET['qqfile'])) {
            $this->file = new qqUploadedFileXhr();
        } elseif (isset($_FILES['qqfile'])) {
            $this->file = new qqUploadedFileForm();
        } else {
            $this->file = false; 
        }
    }
    
    private function checkServerSettings(){        
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));        
        
        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';             
            die("{'error':'increase post_max_size and upload_max_filesize to $size'}");    
        }        
    }
    
    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;        
        }
        return $val;
    }
    
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory, $replaceOldFile = FALSE, $type){
        if (!is_writable($uploadDirectory)){
            return array('error' => "Server error. Upload directory isn't writable.");
        }
        
        if (!$this->file){
            return array('error' => 'No files were uploaded.');
        }
        
        $size = $this->file->getSize();
        
        if ($size == 0) {
            return array('error' => 'File is empty');
        }
        
        if ($size > $this->sizeLimit) {
            return array('error' => 'File is too large');
        }
        
        $pathinfo = pathinfo($this->file->getName());
        $filename = $pathinfo['filename'];

        //$filename = md5(uniqid());
        $ext = $pathinfo['extension'];

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }
        
        if(!$replaceOldFile){
            /// don't overwrite previous files that were uploaded
            $counter = 1;
            $new_filename = $filename;
            while (file_exists($uploadDirectory . $new_filename . '.' . strtolower($ext)))
            {
                $new_filename = $filename.'_'.$counter;
                $counter++;
            }
            $filename = $new_filename;
        }
        
        if ($this->file->save($uploadDirectory, $filename, strtolower($ext), $type)){
            return array('success'=>true,'filename'=>$filename.'.'.strtolower($ext));
            
        } else {
            return array('error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }
        
    }    
}
include('includes/application_top.php');
//a stupid simple way to make compatible with old style
$db = SRPCore();
include('includes/classes/upload.php');
include('includes/classes/smart_resize.php');

$type = $_GET['type'];
$id = $_GET['id'];
$file_path = "../files_uploaded/";

// list of valid extensions, ex. array("jpeg", "xml", "bmp")
if($type=="documents")
{
    $allowedExtensions = array("jpeg", "jpg", 'gif', 'png', 'doc', 'docx', 'pdf', 'xls', 'xlsx', 'txt', 'rtf', 'zip');
}
else
{
    $allowedExtensions = array("jpeg", "jpg", 'gif', 'png');
}
// max file size in bytes
$sizeLimit = 5 * 1024 * 1024;

$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);

$result = $uploader->handleUpload($file_path, FALSE, $type);
// to pass data through iframe you will need to encode all html tags

$result_length = count($result);
$sql = "SELECT MAX(sort_order) FROM `".$type."`";
if($type=="gallery_images")
{
	$sql .= " WHERE gallery_id = ".intval($id);
	$parent_field = "gallery_id";
}
elseif($type=="documents")
{
	$sql .= " WHERE list_id = ".intval($id);
	$parent_field = "list_id";
}
else
{
	$sql .= " WHERE page_id = ".intval($id);
	$parent_field = "page_id";
}
$sort_result = SRPCore()->query($sql);
$sort_order = $sort_result->fetch_item();
$sort_order = $sort_order + 1;
foreach($result as $key=>$item)
{
    if($key=='filename')
    {
        $sql = "INSERT INTO `".$type."` (`".$parent_field."`, filename, sort_order, last_updated) VALUES ('".$id."', '$item', '$sort_order', now())";
        SRPCore()->query($sql);
        $sort_order++;
    }
}
//echo htmlspecialchars($encoded, ENT_NOQUOTES);

echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);