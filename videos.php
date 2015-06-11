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
        delete_videos($id);
        header("Location: videos.php?page_id=".$page_id);
    	exit;
    }
    //generate a form if its add or edit
    else if($_GET['action'] == 'add' || $_GET['action'] == 'edit')
    {
        include('includes/header_alt.php');

        if($_GET['action'] == 'edit')
        {
            $row = SRPCore()
                ->query("SELECT * FROM videos WHERE video_id = $id")
                ->fetch();
            $page_id = $row['page_id'];
        }
?>
<div class="menu-bar">
	<h1>Video Gallery</h1>
</div>
<div class="table">
    <div class="main main-no-pad">
		<div class="main-scroll">
			<div class="page">
                <form method="post" action="videos.php?action=submit" enctype="multipart/form-data">
                <h1><?php if($_GET['action'] == 'add') echo 'Add'; else echo 'Edit'; ?> Video</h1>
                    
                    <input type="text" id="title" name="title" class="form-field-text" value="<?php echo db_output($row['title']); ?>" />
                    <label class="form-field-name">Title</label>
                    <input type="text" id="embed_code" name="embed_code" class="form-field-text" value="<?php echo db_output($row['embed_code']); ?>" />
                    <label class="form-field-name">Share URL</label>
					<br>
                    <!--hidden and aux stuffs -->
                    <input type="hidden" name="video_id" value="<?php echo $id; ?>" />
                    <input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
                    <input type="submit" name="<?php echo $_GET['action']; ?>" class="form-field-submit" value="Save"/>
                    <a href="videos.php?page_id=<?php echo $page_id; ?>" class="small-modal-cancel">Cancel</a>
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
        
        $video_id = $_POST['video_id'];
        $page_id = $_POST['page_id'];
        $embed_code = $_POST['embed_code'];

        //do the proper type of update
        if(isset($_POST['add']))
        {
            //set post array
            $post_array = array("page_id"=>$page_id,"title"=>$title,"embed_code"=>$embed_code);
            //method to update db
            add_videos($post_array);
            header('Location: videos.php?page_id='.$page_id);
            exit;
        }
        else
        {
            //set post array
            $post_array = array("video_id"=>$video_id,"page_id"=>$page_id,"title"=>$title,"embed_code"=>$embed_code);
            //method to update db
            edit_videos($post_array);
            header('Location: videos.php?page_id='.$page_id);
            exit;
        }

        header("Location: videos.php?page_id=".$page_id);
        exit;
    }
    else
    {
        SRPCore()->log_error($_GET['action'].' is not a legit action.');
        header("Location: videos.php?page_id=".$page_id);
        exit;
    }
}
include('includes/header_alt.php');
?>
<div class="menu-bar">
	<h1>Video Gallery</h1>
</div>
<div class="table">

	<div class="main main-no-pad">
		<div class="main-scroll">
			<div class="page">
				<a href="videos.php?action=add&page_id=<?php echo $page_id; ?>" class="button "><i class="fa fa-plus"></i> Add Video</a>
				<table width="100%">
					<tr>
                        <th>Title</th>
                        <th>Share URL</th>
						<th>Updated</th>
						<th>Manage</th>
					</tr>
					
<?php
		$res = SRPCore()->query("SELECT * FROM videos WHERE page_id = '".db_input($_GET['page_id'])."' ORDER BY sort_order ASC");
		while($row = $res->fetch())
		{
			
?>
					<tr class="sortable">
                        <td><?php echo db_output($row['title']); ?></td>
						<td><?php echo db_output($row['embed_code']); ?></td>
						<td><?php echo date('m/d/Y g:i a', strtotime($row['last_updated'])); ?></td>
						<td><a href="videos.php?action=edit&id=<?php echo $row['video_id']; ?>" class="button"><i class="fa fa-pencil"></i> Edit</a> <a href="videos.php?action=delete&id=<?php echo $row['video_id']; ?>&page_id=<?php echo $row['page_id']; ?>" class="button delete"><i class="fa fa-remove"></i> Delete</a></td>
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