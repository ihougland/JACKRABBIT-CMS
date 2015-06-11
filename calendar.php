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
        delete_calendar($id);
        header("Location: calendar.php?page_id=".$page_id);
    	exit;
    }
    //generate a form if its add or edit
    else if($_GET['action'] == 'add' || $_GET['action'] == 'edit')
    {
        include('includes/header_alt.php');

        if($_GET['action'] == 'edit')
        {
            $row = SRPCore()
                ->query("SELECT * FROM calendar WHERE calendar_id = $id")
                ->fetch();
            $page_id = $row['page_id'];
        }
?>
<div class="menu-bar">
	<h1>Calendar</h1>
</div>
<div class="table">
    <div class="main main-no-pad">
		<div class="main-scroll">
			<div class="page">
                <form method="post" action="calendar.php?action=submit" enctype="multipart/form-data">
                <h1><?php if($_GET['action'] == 'add') echo 'Add'; else echo 'Edit'; ?> Event</h1>
                    <input type="text" id="title" name="title" class="form-field-text" value="<?php echo db_output($row['title']); ?>" />
                    <label class="form-field-name">Title</label>
                    <input type="text" id="start_date" name="start_date" class="form-field-text datepicker" value="<?php echo db_output($row['start_date']); ?>" />
                    <label class="form-field-name">Start Date</label>
                    <input type="text" id="end_date" name="end_date" class="form-field-text datepicker" value="<?php echo db_output($row['end_date']); ?>" />
                    <label class="form-field-name">End Date</label>
                    <input type="text" id="start_time" name="start_time" class="form-field-text" value="<?php echo db_output($row['start_time']); ?>" />
                    <label class="form-field-name">Start Time</label>
                    <input type="text" id="end_time" name="end_time" class="form-field-text" value="<?php echo db_output($row['end_time']); ?>" />
                    <label class="form-field-name">End Time</label>
                    <textarea id="text" name="text" class="form-field-textarea"><?php echo db_output($row['text']); ?></textarea>
                    <label class="form-field-name">Text</label>
					<br>
                    <!--hidden and aux stuffs -->
                    <input type="hidden" name="calendar_id" value="<?php echo $id; ?>" />
                    <input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
                    <input type="submit" name="<?php echo $_GET['action']; ?>" class="form-field-submit" value="Save"/>
                    <a href="calendar.php?page_id=<?php echo $page_id; ?>" class="small-modal-cancel">Cancel</a>
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
        
        $calendar_id = $_POST['calendar_id'];
        $page_id = $_POST['page_id'];
        $title = $_POST['title'];
        $start_date = (!empty($_POST['start_date']))?date('Y-m-d', strtotime($_POST['start_date'])):'';
        $end_date = (!empty($_POST['end_date']))?date('Y-m-d', strtotime($_POST['end_date'])):'';
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];

        //do the proper type of update
        if(isset($_POST['add']))
        {
            //set post array
            $post_array = array("page_id"=>$page_id,"title"=>$title,"start_date"=>$start_date,"end_date"=>$end_date,"start_time"=>$start_time,"end_time"=>$end_time,"text"=>$text);
            //method to update db
            add_calendar($post_array);
        }
        else
        {
            //set post array
            $post_array = array("page_id"=>$page_id,"calendar_id"=>$calendar_id,"title"=>$title,"start_date"=>$start_date,"end_date"=>$end_date,"start_time"=>$start_time,"end_time"=>$end_time,"text"=>$text);
            //method to update db
            edit_calendar($post_array);
        }

        header("Location: calendar.php?page_id=".$page_id);
        exit;
    }
    else
    {
        SRPCore()->log_error($_GET['action'].' is not a legit action.');
        header("Location: calendar.php?page_id=".$page_id);
        exit;
    }
}
include('includes/header_alt.php');
?>
<div class="menu-bar">
	<h1>Calendar</h1>
</div>
<div class="table">

	<div class="main main-no-pad">
		<div class="main-scroll">
			<div class="page">
				<a href="calendar.php?action=add&page_id=<?php echo $page_id; ?>" class="button "><i class="fa fa-plus"></i> Add Event</a>
				<table width="100%">
					<tr>
                        <th>Date(s)</th>
                        <th>Title</th>
						<th>Updated</th>
						<th>Manage</th>
					</tr>
					
<?php
		$res = SRPCore()->query("SELECT * FROM calendar WHERE page_id = '".db_input($_GET['page_id'])."' ORDER BY start_date ASC");
		while($row = $res->fetch())
		{
			
?>
					<tr class="sortable">
                        <td><?php echo date('m/d/Y', strtotime($row['start_date'])).(($row['end_date']!='0000-00-00')?' - '.date('m/d/Y', strtotime($row['end_date'])):''); ?></td>
                        <td><?php echo db_output($row['title']); ?></td>
						<td><?php echo date('m/d/Y g:i a', strtotime($row['last_updated'])); ?></td>
						<td><a href="calendar.php?action=edit&id=<?php echo $row['calendar_id']; ?>" class="button"><i class="fa fa-pencil"></i> Edit</a> <a href="calendar.php?action=delete&id=<?php echo $row['calendar_id']; ?>&page_id=<?php echo $row['page_id']; ?>" class="button delete"><i class="fa fa-remove"></i> Delete</a></td>
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