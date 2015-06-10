<?php
include('includes/application_top.php');
include('includes/session_check.php');
include('includes/addon_functions.php');
$db = SRPCore();
$page_id = $_GET['page_id'];
if(isset($_GET['action']))
{
    $id = intval($_GET['id']);
    $page_id = $_GET['page_id'];
    if($_GET['action'] == 'delete')
    {
        delete_galleries($id);
        header("Location: galleries.php?page_id=".$page_id);
    	exit;
    }
    //generate a form if its add or edit
    else if($_GET['action'] == 'add' || $_GET['action'] == 'edit')
    {
        include('includes/header_alt.php');

        if($_GET['action'] == 'edit')
        {
            $row = SRPCore()
                ->query("SELECT * FROM galleries WHERE gallery_id = $id")
                ->fetch();
            $page_id = $row['page_id'];
        }
?>
<div class="menu-bar">
	<h1>Galleries</h1>
</div>
<div class="table">
    <div class="main main-no-pad">
		<div class="main-scroll">
			<div class="page">
                <form method="post" action="galleries.php?action=submit" enctype="multipart/form-data">
                <h1><?php if($_GET['action'] == 'add') echo 'Add'; else echo 'Edit'; ?> Gallery</h1>
                    
                    <input type="text" id="title" name="title" class="form-field-text" value="<?php echo $row['title']; ?>" />
                    <label class="form-field-name">Title</label>

					<br>
                    <!--hidden and aux stuffs -->
                    <input type="hidden" name="gallery_id" value="<?php echo $id; ?>" />
                    <input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
                    <input type="submit" name="<?php echo $_GET['action']; ?>" class="form-field-submit" value="Save"/>
                    <a href="galleries.php?page_id=<?php echo $page_id; ?>" class="small-modal-cancel">Cancel</a>
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
        
        $gallery_id = $_POST['gallery_id'];
        $page_id = $_POST['page_id'];
        $title = $_POST['title'];
        $post_array = array("page_id"=>$page_id,"title"=>$title,"gallery_id"=>$gallery_id);

        //do the proper type of update
        if(isset($_POST['add']))
        {
        	add_galleries($post_array);
        }
        else
            edit_galleries($post_array);

        header("Location: galleries.php?page_id=".$page_id);
        exit;
    }
    else
    {
        SRPCore()->log_error($_GET['action'].' is not a legit action.');
        header("Location: galleries.php?page_id=".$page_id);
        exit;
    }
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
				<a href="galleries.php?action=add&page_id=<?php echo $page_id; ?>" class="button "><i class="fa fa-plus"></i> Add</a>
				<table width="100%">
					<tr>
						<th>Title</th>
						<th># Images</th>
						<th>Updated</th>
						<th>Manage</th>
					</tr>
					
<?php
		$res = SRPCore()->query("SELECT * FROM galleries WHERE page_id = '".db_input($_GET['page_id'])."' ORDER BY sort_order ASC");
		while($row = $res->fetch())
		{
			$num_images = get_num_gallery_images($row['gallery_id']);
			
?>
					<tr class="sortable">
						<td><?php echo db_output($row['title']); ?></td>
						<td><?php echo $num_images; ?></td>
						<td><?php echo date('m/d/Y g:i a', strtotime($row['last_updated'])); ?></td>
						<td><a href="gallery_images.php?gallery_id=<?php echo $row['gallery_id']; ?>&page_id=<?php echo $_GET['page_id']; ?>" class="button"><i class="fa fa-picture-o"></i> Images</a> <a href="galleries.php?action=edit&id=<?php echo $row['gallery_id']; ?>" class="button"><i class="fa fa-pencil"></i> Edit</a> <a href="galleries.php?action=delete&id=<?php echo $row['gallery_id']; ?>&page_id=<?php echo $row['page_id']; ?>" class="button delete"><i class="fa fa-remove"></i> Delete</a></td>
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