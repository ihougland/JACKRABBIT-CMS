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
        delete_accordion($id);
        header("Location: accordion.php?page_id=".$page_id);
    	exit;
    }
    //generate a form if its add or edit
    else if($_GET['action'] == 'add' || $_GET['action'] == 'edit')
    {
        include('includes/header_alt.php');

        if($_GET['action'] == 'edit')
        {
            $row = SRPCore()
                ->query("SELECT * FROM accordion WHERE accordion_id = $id")
                ->fetch();
            $page_id = $row['page_id'];
        }
?>
<div class="menu-bar">
	<h1>Accordion</h1>
</div>
<div class="table">
    <div class="main main-no-pad">
		<div class="main-scroll">
			<div class="page">
                <form method="post" action="accordion.php?action=submit" enctype="multipart/form-data">
                <h1><?php if($_GET['action'] == 'add') echo 'Add'; else echo 'Edit'; ?> Accordion</h1>
                    
                    <textarea id="text_1" name="text_1" class="form-field-textarea" ><?php echo db_output($row['text_1']); ?></textarea>
                    <label class="form-field-name">Topic</label>
                    <textarea id="text_2" name="text_2" class="form-field-textarea"><?php echo db_output($row['text_2']); ?></textarea>
                    <label class="form-field-name">Text</label>
					<br>
                    <!--hidden and aux stuffs -->
                    <input type="hidden" name="accordion_id" value="<?php echo $id; ?>" />
                    <input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
                    <input type="submit" name="<?php echo $_GET['action']; ?>" class="form-field-submit" value="Save"/>
                    <a href="accordion.php?page_id=<?php echo $page_id; ?>" class="small-modal-cancel">Cancel</a>
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
        
        $accordion_id = $_POST['accordion_id'];
        $page_id = $_POST['page_id'];
        $text_1 = $_POST['text_1'];
        $text_2 = $_POST['text_2'];

        //do the proper type of update
        if(isset($_POST['add']))
        {
            //set post array
            $post_array = array("page_id"=>$page_id,"text_1"=>$text_1,"text_2"=>$text_2);
            //method to update db
            add_accordion($post_array);
            header('Location: accordion.php?page_id='.$page_id);
            exit;
        }
        else
        {
            //set post array
            $post_array = array("accordion_id"=>$accordion_id,"page_id"=>$page_id,"text_1"=>$text_1,"text_2"=>$text_2);
            //method to update db
            edit_accordion($post_array);
            header('Location: accordion.php?page_id='.$page_id);
            exit;
        }
    }
    else
    {
        SRPCore()->log_error($_GET['action'].' is not a legit action.');
        header("Location: accordion.php?page_id=".$page_id);
        exit;
    }
}
include('includes/header_alt.php');
?>
<div class="menu-bar">
	<h1>Accordion</h1>
</div>
<div class="table">

	<div class="main main-no-pad">
		<div class="main-scroll">
			<div class="page">
				<a href="accordion.php?action=add&page_id=<?php echo $page_id; ?>" class="button "><i class="fa fa-plus"></i> Add Accordion</a>
				<table width="100%">
					<tr>
                        <th>Topic</th>
						<th>Updated</th>
						<th>Manage</th>
					</tr>
					
<?php
		$res = SRPCore()->query("SELECT * FROM accordion WHERE page_id = '".db_input($_GET['page_id'])."' ORDER BY sort_order ASC");
		while($row = $res->fetch())
		{
			
?>
					<tr class="sortable">
                        <td><?php echo db_output($row['text_1']); ?></td>
						<td><?php echo date('m/d/Y g:i a', strtotime($row['last_updated'])); ?></td>
						<td><a href="accordion.php?action=edit&id=<?php echo $row['accordion_id']; ?>" class="button"><i class="fa fa-pencil"></i> Edit</a> <a href="accordion.php?action=delete&id=<?php echo $row['accordion_id']; ?>&page_id=<?php echo $row['page_id']; ?>" class="button delete"><i class="fa fa-remove"></i> Delete</a></td>
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