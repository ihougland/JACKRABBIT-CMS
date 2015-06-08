<?php
include('includes/application_top.php');
include('includes/session_check.php');
include('includes/addon_functions.php');
//ADD MULTIPLE UPLOAD FUNCTIONALITY
$db = SRPCore();
$gallery_id = $_GET['gallery_id'];
if(isset($_GET['action']))
{
    $id = intval($_GET['id']);

    if($_GET['action'] == 'delete')
    {
        delete_gallery_image($id);
        header("Location: gallery_images.php?gallery_id=".$gallery_id);
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
?>
<div class="menu-bar">
	<h1>Galleries</h1>
</div>
<div class="table">
    <div class="main main-no-pad">
		<div class="main-scroll">
			<div class="page">
                <form method="post" action="gallery_images.php?action=submit" enctype="multipart/form-data">
                <?php
                if($_GET['action']=='add')
                {
                ?>
                <h1>Add Gallery Images</h1>
                
                    Add multiple upload here!!

					<br>
                    
                <?php
                }
                else
                {
                ?>
                <h1>Edit Gallery Image</h1>
                
                    <input type="file" id="image" name="image" class="form-field-text" />
                    <label class="form-field-name">Image</label>
                    <input type="text" id="caption" name="caption" class="form-field-text" value="<?php echo $row['caption']; ?>" />
                    <label class="form-field-name">Caption</label>

					<br>
                    <!--hidden and aux stuffs -->
                    <input type="hidden" name="image_id" value="<?php echo $image_id; ?>" />
                <?php
                }
                ?>
                <!--hidden and aux stuffs -->
                    <input type="hidden" name="gallery_id" value="<?php echo $id; ?>" />
                    <input type="submit" name="<?php echo $_GET['action']; ?>" class="button" value="Save"/>
                </form>
            </div>
		</div><!-- end .main-scroll-->
	</div><!-- end .main -->
</div><!-- end .table -->
<?php
        include('includes/footer_alt.php');
    }
    else if($_GET['action'] == 'submit')
    {
        //get the values from the form
        
        $image_id = $_POST['image_id'];
        $gallery_id = $_POST['gallery_id'];
        $caption = $_POST['caption'];
        $post_array = array("gallery_id"=>$gallery_id,"caption"=>$caption,"image_id"=>$image_id);

        //do the proper type of update
        if(isset($_POST['add']))
        {
        	add_gallery_image($post_array);
        }
        else
            edit_gallery_image($post_array);
    }
    else
    {
        SRPCore()->log_error($_GET['action'].' is not a legit action.');
    }
    header("Location: gallery_images.php?gallery_id=".$gallery_id);
    exit;
}
include('includes/header_alt.php');
?>
<div class="menu-bar">
	<h1>Galleries</h1>
</div>
<div class="table">

	<div class="main main-no-pad">
		<div class="main-scroll">
			<div class="page">
				<a href="gallery_images.php?action=add&gallery_id=<?php echo $gallery_id; ?>" class="button "><i class="fa fa-plus"></i> Add</a>
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
						<td><?php echo db_output($row['caption']); ?></td>
						<td><?php echo date('m/d/Y g:i a', strtotime($row['last_updated'])); ?></td>
						<td><a href="gallery_images.php?gallery_id=<?php echo $row['gallery_id']; ?>" class="button"><i class="fa fa-picture-o"></i> Images</a> <a href="galleries.php?action=edit&id=<?php echo $row['gallery_id']; ?>" class="button"><i class="fa fa-pencil"></i> Edit</a> <a href="#" class="button"><i class="fa fa-remove"></i> Delete</a></td>
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