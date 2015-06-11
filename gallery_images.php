<?php
include('includes/application_top.php');
include('includes/session_check.php');
include('includes/addon_functions.php');
//ADD MULTIPLE UPLOAD FUNCTIONALITY
$db = SRPCore();
$gallery_id = $_GET['gallery_id'];
$page_id = $_GET['page_id'];
if(isset($_GET['action']))
{
    $id = intval($_GET['id']);
    $page_id = $_GET['page_id'];
    if($_GET['action'] == 'delete')
    {
        delete_gallery_image($id);
        header("Location: gallery_images.php?gallery_id=".$gallery_id."&page_id=".$page_id);
    	exit;
    }
    //generate a form if its add or edit
    else if($_GET['action'] == 'add' || $_GET['action'] == 'edit')
    {
        include('includes/header_alt.php');

        if($_GET['action'] == 'edit')
        {
            $row = SRPCore()
                ->query("SELECT * FROM gallery_images WHERE image_id = $id")
                ->fetch();
            $gallery_id = $row['gallery_id'];
        }
        else
        	$gallery_id = $_GET['gallery_id'];

?>
<div class="menu-bar">
	<h1>Galleries</h1>
</div>
<div class="table">
    <div class="main main-no-pad">
		<div class="main-scroll">
			<div class="page">
                <?php
                if($_GET['action']=='add')
                {
                ?>
                <h1>Add Gallery Images</h1>
                <a href="gallery_images.php?gallery_id=<?php echo $gallery_id; ?>&page_id=<?php echo $page_id; ?>" class="small-modal-cancel">Cancel</a>
                <br><br>
                Drag & drop files into the box below (supported in Chrome and Firefox). Or, click "Choose Files" to select the file(s) you want to upload.<br><br>
                <div id="file-uploader-demo1">      
                    <noscript>
                        <!-- or put a simple form for upload here -->
                        <form method="post" action="gallery_images.php?action=submit" enctype="multipart/form-data">
		                	
		                    <input type="file" id="image" name="image" class="form-field-text" />
		                    <label class="form-field-name">Image</label>
		                    <input type="text" id="caption" name="caption" class="form-field-text" value="" />
		                    <label class="form-field-name">Caption</label>

							<br>
		                    <!--hidden and aux stuffs -->
		                    <input type="hidden" name="image_id" value="<?php echo $image_id; ?>" />
							<input type="hidden" name="gallery_id" value="<?php echo $gallery_id; ?>" />
                            <input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
		                    <input type="submit" name="<?php echo $_GET['action']; ?>" class="button" value="Save"/>
                            <a href="gallery_images.php?gallery_id=<?php echo $gallery_id; ?>&page_id=<?php echo $page_id; ?>" class="small-modal-cancel">Cancel</a>
		                </form>
                    </noscript>         
                </div>
				<br>
                <script>
                function createUploader(){            
		            var uploader = new qq.FileUploader({
		                element: document.getElementById('file-uploader-demo1'),
		                action: 'upload_multiple.php?type=gallery_images&id=<?php echo $gallery_id; ?>',
		                debug: true,
		                onSubmit: function(id, fileName){},
		                onProgress: function(id, fileName, loaded, total){},
		                onComplete: function(id, fileName, responseJSON){
		                	if(uploader.getInProgress() == 0) {
						     //direct to gallery_images.php
		                	 window.location.href = "gallery_images.php?gallery_id=<?php echo $gallery_id; ?>&page_id=<?php echo $page_id; ?>";
						   }
		                	
		                },
		                onCancel: function(id, fileName){
		                	if(uploader.getInProgress() == 0) {
						     //direct to gallery_images.php
		                	 window.location.href = "gallery_images.php?gallery_id=<?php echo $gallery_id; ?>&page_id=<?php echo $page_id; ?>";
						   }
		                },
		                // messages                
		                messages: {
		                    typeError: "{file} has invalid extension. Only {extensions} are allowed.",
		                    sizeError: "{file} is too large, maximum file size is {sizeLimit}.",
		                    minSizeError: "{file} is too small, minimum file size is {minSizeLimit}.",
		                    emptyError: "{file} is empty, please select files again without it.",
		                    onLeave: "The files are being uploaded, if you leave now the upload will be cancelled."            
		                },
		                showMessage: function(message){
		                    alert(message);
		                }    
		            });           
		        }
		        
		        // in your app create uploader as soon as the DOM is ready
		        // don't wait for the window to load  
		        window.onload = createUploader;
                </script>
                <?php
                }
                else
                {
                ?>
                <h1>Edit Gallery Image</h1>
                <form method="post" action="gallery_images.php?action=submit" enctype="multipart/form-data">
                	<?php
                	if(!empty($row['filename']))
                	{
                	?>
                	<img src="../files_uploaded/thumbs/<?php echo $row['filename']; ?>" alt="<?php echo db_output($row['caption']); ?>" /><br>
                	<?php
                	}
                	?>
                    <input type="file" id="image" name="image" class="form-field-text" />
                    <label class="form-field-name">Image</label>
                    <input type="text" id="caption" name="caption" class="form-field-text" value="<?php echo db_output($row['caption']); ?>" />
                    <label class="form-field-name">Caption</label>

					<br>
                    <!--hidden and aux stuffs -->
                    <input type="hidden" name="image_id" value="<?php echo $id; ?>" />
					<input type="hidden" name="gallery_id" value="<?php echo $gallery_id; ?>" />
                    <input type="hidden" name="page_id" value="<?php echo $page_id; ?>" /> 
                    <input type="submit" name="<?php echo $_GET['action']; ?>" class="form-field-submit" value="Save"/>
                    <a href="gallery_images.php?gallery_id=<?php echo $gallery_id; ?>&page_id=<?php echo $page_id; ?>" class="small-modal-cancel">Cancel</a>
                </form>
                <?php
                }
                ?>
                
            </div>
		</div><!-- end .main-scroll-->
	</div><!-- end .main -->
</div><!-- end .table -->
<?php
        include('includes/footer_alt.php');
    }
    else if($_GET['action'] == 'submit')
    {
        include('includes/classes/upload.php');
        include('includes/classes/smart_resize.php');
        //get the values from the form
        $image_id = $_POST['image_id'];
        $gallery_id = $_POST['gallery_id'];
        $caption = $_POST['caption'];

        //set max, crop, thumb w & h
        $upload_max = SRPCore()->cfg("UPLOAD_MAX");
        $thumb_w = SRPCore()->cfg("GALLERY_WIDTH");
        $thumb_h = SRPCore()->cfg("GALLERY_HEIGHT");
        //file uploader
        $file_uploader = new uploader('../files_uploaded/');
        $file_uploader->addAllowedFileType(array('.jpeg','.jpg','.png'));

        //do the proper type of update
        if(isset($_POST['add']))
        {
            //image
            try 
            {
                $filename = $file_uploader -> uploadFile('image');
                if($filename)
                {
                    $image_src = "../files_uploaded/" . stripslashes($filename);
                    $image_size = getimagesize($image_src);
                    if($image_size[0]>=$thumb_w && $image_size[1]>=$thumb_h)
                    {
                        if($image_size[0]>$upload_max)
                        {
                            smart_resize_image($image_src, $image_src, $upload_max, $upload_max, true, 'file', false, false);
                            $image_size = getimagesize($image_src);
                        }
                        $thumb_src = "../files_uploaded/thumbs/" . stripslashes($filename);
                        //resize thumb in case user exits out of cropping tool before saving
                        smart_resize_image($image_src, $thumb_src, $thumb_w, $thumb_h, true, 'file', false, false);
                
                        //set post array
                        $post_array = array("gallery_id"=>$gallery_id,"caption"=>$caption,"image_id"=>$image_id,"filename"=>$filename);
                        //method to insert into db
                        add_gallery_image($post_array);

                        //drop user off at cropper if picture is not correct size
                        if($image_size[0]==$thumb_w && $image_size[1]==$thumb_h)
                        {
                            //no need to crop, take them back
                            header("Location: gallery_images.php?gallery_id=".$gallery_id."&page_id=".$page_id);
                            exit();
                        }
                        else
                        {
                            //crop!
                            header("Location: crop_image.php?in_img=../files_uploaded/$filename&out_img=../files_uploaded/thumbs/$filename&w=$thumb_w&h=$thumb_h&landing=".urlencode('gallery_images.php?gallery_id='.$gallery_id.'&page_id='.$page_id));
                            exit;
                        }
                    }
                    else
                    {
                        if(file_exists('../files_uploaded/thumbs/'.$filename)) unlink('../files_uploaded/thumbs/'.$filename);
                        if(file_exists('../files_uploaded/'.$filename)) unlink('../files_uploaded/'.$filename);
                        $_SESSION['upload_error'] = "Image uploaded is too small. Please choose an image that is at least ".$thumb_w." px wide by ".$thumb_h." px tall.";
                        header('Location: gallery_images.php?action=add&gallery_id='.$gallery_id.'&page_id='.$page_id);
                        //quit before output
                        exit;
                    }
                }
                else
                {
                    $_SESSION['upload_error'] = 'No file found.';
                    header('Location: gallery_images.php?action=add&gallery_id='.$_POST['gallery_id'].'&page_id='.$page_id);
                    exit;
                }
                
            } 
            catch (Exception $e) 
            {
                $_SESSION['upload_error'] = $e->getMessage();
                header('Location: gallery_images.php?action=add&gallery_id='.$_POST['gallery_id'].'&page_id='.$page_id);
                exit;
            }
        }
        else
        {
            try 
            {
                $filename = $file_uploader -> uploadFile('image');
                if($filename)
                {
                    $image_src = "../files_uploaded/" . stripslashes($filename);
                    $image_size = getimagesize($image_src);
                    if($image_size[0]>=$thumb_w && $image_size[1]>=$thumb_h)
                    {
                        if($image_size[0]>$upload_max)
                        {
                            smart_resize_image($image_src, $image_src, $upload_max, $upload_max, true, 'file', false, false);
                            $image_size = getimagesize($image_src);
                        }
                        $thumb_src = "../files_uploaded/thumbs/" . stripslashes($filename);
                        //resize thumb in case user exits out of cropping tool before saving
                        smart_resize_image($image_src, $thumb_src, $thumb_w, $thumb_h, true, 'file', false, false);

                        //delete old image
                        $old_filename = SRPCore()->query("SELECT filename FROM gallery_images WHERE image_id = ".intval($image_id))->fetch_item();
                        if(!empty($old_filename))
                        {
                            if(file_exists('../files_uploaded/thumbs/'.$old_filename)) unlink('../files_uploaded/thumbs/'.$old_filename);
                            if(file_exists('../files_uploaded/'.$old_filename)) unlink('../files_uploaded/'.$old_filename);
                        }
                
                        //set post array
                        $post_array = array("gallery_id"=>$gallery_id,"caption"=>$caption,"image_id"=>$image_id,"filename"=>$filename);
                        //method to update db
                        edit_gallery_image($post_array);

                        //drop user off at cropper if picture is not correct size
                        if($image_size[0]==$thumb_w && $image_size[1]==$thumb_h)
                        {
                            //no need to crop, take them back
                            header("Location: gallery_images.php?gallery_id=".$gallery_id."&page_id=".$page_id);
                            exit();
                        }
                        else
                        {
                            //crop!
                            header("Location: crop_image.php?in_img=../files_uploaded/$filename&out_img=../files_uploaded/thumbs/$filename&w=$thumb_w&h=$thumb_h&landing=".urlencode('gallery_images.php?gallery_id='.$gallery_id.'&page_id='.$page_id));
                            exit;
                        }
                    }
                    else
                    {
                        if(file_exists('../files_uploaded/thumbs/'.$filename)) unlink('../files_uploaded/thumbs/'.$filename);
                        if(file_exists('../files_uploaded/'.$filename)) unlink('../files_uploaded/'.$filename);
                        $_SESSION['upload_error'] = "Image uploaded is too small. Please choose an image that is at least ".$thumb_w." px wide by ".$thumb_h." px tall.";
                        header('Location: gallery_images.php?action=edit&gallery_id='.$gallery_id.'&page_id='.$page_id);
                        //quit before output
                        exit;
                    }
                }
                else
                {
                    //set post array
                    $post_array = array("gallery_id"=>$gallery_id,"caption"=>$caption,"image_id"=>$image_id);
                    //method to update db
                    edit_gallery_image($post_array);
                    header('Location: gallery_images.php?gallery_id='.$_POST['gallery_id'].'&page_id='.$page_id);
                    exit;
                }
                
            } 
            catch (Exception $e) 
            {
                $_SESSION['upload_error'] = $e->getMessage();
                header('Location: gallery_images.php?action=edit&gallery_id='.$_POST['gallery_id']);
                exit;
            }
        }
    }
    else
    {
        SRPCore()->log_error($_GET['action'].' is not a legit action.');
        header("Location: gallery_images.php?gallery_id=".$gallery_id."&page_id=".$page_id);
    	exit;
    }
    
}
include('includes/header_alt.php');
?>
<div class="menu-bar">
	<h1><?php echo db_output(get_gallery_title($gallery_id)); ?></h1>
</div>
<div class="table">

	<div class="main main-no-pad">
		<div class="main-scroll">
			<div class="page">
				<a href="galleries.php?page_id=<?php echo $_GET['page_id']; ?>" class="button"><i class="fa fa-arrow-left"></i> Go Back</a> <a href="gallery_images.php?action=add&gallery_id=<?php echo $gallery_id; ?>&page_id=<?php echo $_GET['page_id']; ?>" class="button "><i class="fa fa-plus"></i> Add Image</a>
				<table width="100%">
					<tr>
						<th>Image</th>
						<th>Caption</th>
						<th>Updated</th>
						<th>Manage</th>
					</tr>
					
<?php
		$res = SRPCore()->query("SELECT * FROM gallery_images WHERE gallery_id = '".db_input($_GET['gallery_id'])."' ORDER BY sort_order ASC");
		while($row = $res->fetch())
		{			
?>
					<tr class="sortable">
						<td><img src="../files_uploaded/thumbs/<?php echo $row['filename']; ?>" alt="<?php echo db_output($row['caption']); ?>" /></td>
						<td><input type="text" name="<?php echo $row['image_id']; ?>" value="<?php echo db_output($row['caption']); ?>" class="form-field-text" /></td>
						<td><?php echo date('m/d/Y g:i a', strtotime($row['last_updated'])); ?></td>
						<td><a href="gallery_images.php?action=edit&id=<?php echo $row['image_id']; ?>&page_id=<?php echo $_GET['page_id']; ?>" class="button"><i class="fa fa-pencil"></i> Edit</a> <a href="gallery_images.php?action=delete&id=<?php echo $row['image_id']; ?>&gallery_id=<?php echo $_GET['gallery_id']; ?>&page_id=<?php echo $_GET['page_id']; ?>" class="button delete"><i class="fa fa-remove"></i> Delete</a></td>
					</tr>
<?php
		}
?>
				</table>
			</div>
		</div><!-- end .main-scroll-->
	</div><!-- end .main -->
</div><!-- end .table -->
<?php
include('includes/footer_alt.php');
?>