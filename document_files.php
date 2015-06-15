<?php
include('includes/application_top.php');
include('includes/session_check.php');
include('includes/addon_functions.php');
//ADD MULTIPLE UPLOAD FUNCTIONALITY
$db = SRPCore();
$list_id = $_GET['list_id'];
$page_id = $_GET['page_id'];
if(isset($_GET['action']))
{
    $id = intval($_GET['id']);
    $page_id = $_GET['page_id'];
    if($_GET['action'] == 'delete')
    {
        delete_documents($id);
        header("Location: document_files.php?list_id=".$list_id."&page_id=".$page_id);
    	exit;
    }
    //generate a form if its add or edit
    else if($_GET['action'] == 'add' || $_GET['action'] == 'edit')
    {
        include('includes/header_alt.php');

        if($_GET['action'] == 'edit')
        {
            $row = SRPCore()
                ->query("SELECT * FROM documents WHERE document_id = $id")
                ->fetch();
            $list_id = $row['list_id'];
        }
        else
        	$list_id = $_GET['list_id'];

?>
<div class="menu-bar">
	<h1>Documents</h1>
</div>
<div class="table">
    <div class="main main-no-pad">
		<div class="main-scroll">
			<div class="page">
                <?php
                if($_GET['action']=='add')
                {
                ?>
                <h1>Add Document</h1>
                <a href="document_files.php?list_id=<?php echo $list_id; ?>&page_id=<?php echo $page_id; ?>" class="small-modal-cancel">Cancel</a>
                <br><br>
                Drag & drop files into the box below (supported in Chrome and Firefox). Or, click "Choose Files" to select the file(s) you want to upload.<br><br>
                <div id="file-uploader-demo1">      
                    <noscript>
                        <!-- or put a simple form for upload here -->
                        <form method="post" action="document_files.php?action=submit" enctype="multipart/form-data">
		                	
		                    <input type="file" id="file" name="file" class="form-field-text" />
		                    <label class="form-field-name">File</label>
		                    <input type="text" id="title" name="title" class="form-field-text" value="" />
		                    <label class="form-field-name">Title</label>

							<br>
		                    <!--hidden and aux stuffs -->
		                    <input type="hidden" name="document_id" value="<?php echo $document_id; ?>" />
							<input type="hidden" name="list_id" value="<?php echo $list_id; ?>" />
                            <input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
		                    <input type="submit" name="<?php echo $_GET['action']; ?>" class="button" value="Save"/>
                            <a href="document_files.php?list_id=<?php echo $list_id; ?>&page_id=<?php echo $page_id; ?>" class="small-modal-cancel">Cancel</a>
		                </form>
                    </noscript>         
                </div>
				<br>
                <script>
                function createUploader(){            
		            var uploader = new qq.FileUploader({
		                element: document.getElementById('file-uploader-demo1'),
		                action: 'upload_multiple.php?type=documents&id=<?php echo $list_id; ?>',
		                debug: true,
		                onSubmit: function(id, fileName){},
		                onProgress: function(id, fileName, loaded, total){},
		                onComplete: function(id, fileName, responseJSON){
		                	if(uploader.getInProgress() == 0) {
						     //direct to document_files.php
		                	 window.location.href = "document_files.php?list_id=<?php echo $list_id; ?>&page_id=<?php echo $page_id; ?>";
						   }
		                	
		                },
		                onCancel: function(id, fileName){
		                	if(uploader.getInProgress() == 0) {
						     //direct to document_files.php
		                	 window.location.href = "document_files.php?list_id=<?php echo $list_id; ?>&page_id=<?php echo $page_id; ?>";
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
                <h1>Edit Document</h1>
                <form method="post" action="document_files.php?action=submit" enctype="multipart/form-data">
                	<?php
                	if(!empty($row['filename']))
                	{
                	?>
                	<a href="../files_uploaded/<?php echo $row['filename']; ?>" title="<?php echo db_output($row['title']); ?>" target="_blank">View Document</a><br>
                	<?php
                	}
                	?>
                    <input type="file" id="file" name="file" class="form-field-text" />
                    <label class="form-field-name">File</label>
                    <input type="text" id="title" name="title" class="form-field-text" value="<?php echo db_output($row['title']); ?>" />
                    <label class="form-field-name">Title</label>

					<br>
                    <!--hidden and aux stuffs -->
                    <input type="hidden" name="document_id" value="<?php echo $id; ?>" />
					<input type="hidden" name="list_id" value="<?php echo $list_id; ?>" />
                    <input type="hidden" name="page_id" value="<?php echo $page_id; ?>" /> 
                    <input type="submit" name="<?php echo $_GET['action']; ?>" class="form-field-submit" value="Save"/>
                    <a href="document_files.php?list_id=<?php echo $list_id; ?>&page_id=<?php echo $page_id; ?>" class="small-modal-cancel">Cancel</a>
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
        //include('includes/classes/smart_resize.php');
        //get the values from the form
        $document_id = $_POST['document_id'];
        $list_id = $_POST['list_id'];
        $page_id = $_POST['page_id'];
        $title = $_POST['title'];

        //file uploader
        $file_uploader = new uploader('../files_uploaded/');
        $file_uploader->addAllowedFileType(array('.doc', '.docx', '.pdf', '.xls', '.xlsx', '.txt', '.rtf', '.zip'));

        //do the proper type of update
        if(isset($_POST['add']))
        {
            //file
            try 
            {
                $filename = $file_uploader -> uploadFile('file');
                if($filename)
                {
                    //set post array
                    $post_array = array("list_id"=>$list_id,"title"=>$title,"document_id"=>$document_id,"filename"=>$filename);
                    //method to insert into db
                    add_documents($post_array);
                }
                else
                {
                    $_SESSION['upload_error'] = 'No file found.';
                    header('Location: document_files.php?action=add&list_id='.$_POST['list_id'].'&page_id='.$page_id);
                    exit;
                }
                
            } 
            catch (Exception $e) 
            {
                $_SESSION['upload_error'] = $e->getMessage();
                header('Location: document_files.php?action=add&list_id='.$_POST['list_id'].'&page_id='.$page_id);
                exit;
            }
        }
        else
        {
            try 
            {
                $filename = $file_uploader -> uploadFile('file');
                if($filename)
                {
                    //delete old file
                    $old_filename = SRPCore()->query("SELECT filename FROM documents WHERE document_id = ".intval($document_id))->fetch_item();
                    if(!empty($old_filename))
                    {
                        if(file_exists('../files_uploaded/'.$old_filename)) unlink('../files_uploaded/'.$old_filename);
                    }
            
                    //set post array
                    $post_array = array("list_id"=>$list_id,"title"=>$title,"document_id"=>$document_id,"filename"=>$filename);
                    //method to update db
                    edit_documents($post_array);
                }
                else
                {
                    //set post array
                    $post_array = array("list_id"=>$list_id,"title"=>$title,"document_id"=>$document_id);
                    //method to update db
                    edit_documents($post_array);
                    header('Location: document_files.php?list_id='.$_POST['list_id'].'&page_id='.$page_id);
                    exit;
                }
                
            } 
            catch (Exception $e) 
            {
                $_SESSION['upload_error'] = $e->getMessage();
                header('Location: document_files.php?action=edit&list_id='.$_POST['list_id']);
                exit;
            }
        }
    }
    else
    {
        SRPCore()->log_error($_GET['action'].' is not a legit action.');
        header("Location: document_files.php?list_id=".$list_id."&page_id=".$page_id);
    	exit;
    }
    
}
include('includes/header_alt.php');
?>
<div class="menu-bar">
	<h1><?php echo db_output(get_list_title($list_id)); ?></h1>
</div>
<div class="table">

	<div class="main main-no-pad">
		<div class="main-scroll">
			<div class="page">
				<a href="documents.php?page_id=<?php echo $_GET['page_id']; ?>" class="button"><i class="fa fa-arrow-left"></i> Go Back</a> <a href="document_files.php?action=add&list_id=<?php echo $list_id; ?>&page_id=<?php echo $_GET['page_id']; ?>" class="button "><i class="fa fa-plus"></i> Add Document</a>
				<table width="100%">
					<tr>
						<th>Document</th>
						<th>Title</th>
						<th>Updated</th>
						<th>Manage</th>
					</tr>
					
<?php
		$res = SRPCore()->query("SELECT * FROM documents WHERE list_id = '".db_input($_GET['list_id'])."' ORDER BY sort_order ASC");
		while($row = $res->fetch())
		{			
?>
					<tr class="sortable">
						<td><a href="../files_uploaded/<?php echo $row['filename']; ?>" title="<?php echo db_output($row['title']); ?>" target="_blank">View File (<?php echo $row['filename']; ?>)</a></td>
						<td><input type="text" name="<?php echo $row['document_id']; ?>" value="<?php echo db_output($row['title']); ?>" class="form-field-text document-title" /></td>
						<td><?php echo date('m/d/Y g:i a', strtotime($row['last_updated'])); ?></td>
						<td><a href="document_files.php?action=edit&id=<?php echo $row['document_id']; ?>&page_id=<?php echo $_GET['page_id']; ?>" class="button"><i class="fa fa-pencil"></i> Edit</a> <a href="document_files.php?action=delete&id=<?php echo $row['document_id']; ?>&list_id=<?php echo $_GET['list_id']; ?>&page_id=<?php echo $_GET['page_id']; ?>" class="button delete"><i class="fa fa-remove"></i> Delete</a></td>
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