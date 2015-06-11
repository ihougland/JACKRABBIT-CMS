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
        delete_staff($id);
        header("Location: staff.php?page_id=".$page_id);
    	exit;
    }
    //generate a form if its add or edit
    else if($_GET['action'] == 'add' || $_GET['action'] == 'edit')
    {
        include('includes/header_alt.php');

        if($_GET['action'] == 'edit')
        {
            $row = SRPCore()
                ->query("SELECT * FROM staff WHERE staff_id = $id")
                ->fetch();
            $page_id = $row['page_id'];
        }
?>
<div class="menu-bar">
	<h1>Staff</h1>
</div>
<div class="table">
    <div class="main main-no-pad">
		<div class="main-scroll">
			<div class="page">
                <form method="post" action="staff.php?action=submit" enctype="multipart/form-data">
                <h1><?php if($_GET['action'] == 'add') echo 'Add'; else echo 'Edit'; ?> Staff</h1>
                    <?php
                    if(!empty($row['filename']))
                    {
                    ?>
                    <img src="../files_uploaded/thumbs/<?php echo $row['filename']; ?>" alt="<?php echo db_output($row['name']); ?>" /><br>
                    <?php
                    }
                    ?>
                    <input type="file" id="image" name="image" class="form-field-text" value="" />
                    <label class="form-field-name">Image</label>
                    <input type="text" id="name" name="name" class="form-field-text" value="<?php echo $row['name']; ?>" />
                    <label class="form-field-name">Name</label>
                    <input type="text" id="title" name="title" class="form-field-text" value="<?php echo $row['title']; ?>" />
                    <label class="form-field-name">Title</label>
                    <input type="text" id="email" name="email" class="form-field-text" value="<?php echo $row['email']; ?>" />
                    <label class="form-field-name">Email</label>
                    <input type="text" id="phone" name="phone" class="form-field-text" value="<?php echo $row['phone']; ?>" />
                    <label class="form-field-name">Phone</label>
                    <input type="text" id="cell_phone" name="cell_phone" class="form-field-text" value="<?php echo $row['cell_phone']; ?>" />
                    <label class="form-field-name">Cell Phone</label>
                    <textarea id="bio" name="bio" class="form-field-textarea"><?php echo $row['title']; ?></textarea>
                    <label class="form-field-name">Bio</label>
                    
					<br>
                    <!--hidden and aux stuffs -->
                    <input type="hidden" name="staff_id" value="<?php echo $id; ?>" />
                    <input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
                    <input type="submit" name="<?php echo $_GET['action']; ?>" class="form-field-submit" value="Save"/>
                    <a href="staff.php?page_id=<?php echo $page_id; ?>" class="small-modal-cancel">Cancel</a>
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
        
        $staff_id = $_POST['staff_id'];
        $page_id = $_POST['page_id'];
        $name = $_POST['name'];
        $title = $_POST['title'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $cell_phone = $_POST['cell_phone'];
        $bio = $_POST['bio'];

        //set max, crop, thumb w & h
        $upload_max = SRPCore()->cfg("UPLOAD_MAX");
        $thumb_w = SRPCore()->cfg("STAFF_WIDTH");
        $thumb_h = SRPCore()->cfg("STAFF_HEIGHT");
        //file uploader
        $file_uploader = new uploader('../files_uploaded/');
        $file_uploader->addAllowedFileType(array('.jpeg','.jpg','.png'));

        //do the proper type of update
        if(isset($_POST['add']))
        {
            $post_array = array("page_id"=>$page_id,"title"=>$title,"staff_id"=>$staff_id);
        	add_staff($post_array);
        }
        else
        {
            $post_array = array("page_id"=>$page_id,"title"=>$title,"staff_id"=>$staff_id);
            edit_staff($post_array);
        }

        header("Location: staff.php?page_id=".$page_id);
        exit;
    }
    else
    {
        SRPCore()->log_error($_GET['action'].' is not a legit action.');
        header("Location: staff.php?page_id=".$page_id);
        exit;
    }
}
include('includes/header_alt.php');
?>
<div class="menu-bar">
	<h1>Staff</h1>
</div>
<div class="table">

	<div class="main main-no-pad">
		<div class="main-scroll">
			<div class="page">
				<a href="staff.php?action=add&page_id=<?php echo $page_id; ?>" class="button "><i class="fa fa-plus"></i> Add</a>
				<table width="100%">
					<tr>
						<th>Name</th>
						<th>Title</th>
						<th>Updated</th>
						<th>Manage</th>
					</tr>
					
<?php
		$res = SRPCore()->query("SELECT * FROM staff WHERE page_id = '".db_input($_GET['page_id'])."' ORDER BY sort_order ASC");
		while($row = $res->fetch())
		{
			
?>
					<tr class="sortable">
						<td><?php echo db_output($row['name']); ?></td>
						<td><?php echo db_output($row['title']); ?></td>
						<td><?php echo date('m/d/Y g:i a', strtotime($row['last_updated'])); ?></td>
						<td><a href="staff.php?action=edit&id=<?php echo $row['staff_id']; ?>" class="button"><i class="fa fa-pencil"></i> Edit</a> <a href="staff.php?action=delete&id=<?php echo $row['staff_id']; ?>&page_id=<?php echo $row['page_id']; ?>" class="button delete"><i class="fa fa-remove"></i> Delete</a></td>
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