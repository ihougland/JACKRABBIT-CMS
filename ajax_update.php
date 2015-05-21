<?php
require_once('includes/application_top.php');
//updates various things via AJAX
$db = SRPCore();

//update page
if($_POST['type'] == 'pageUpdate')
{
    $id = $_POST['id'];
    $title = db_input($_POST['title']);
    $text = db_input($_POST['text']);
    $meta_description = db_input($_POST['meta_description']);
    $meta_title = db_input($_POST['meta_title']);
    $last_updated = date("Y-m-d H:i:s");

    //update the page
    $sql = "UPDATE pages SET `title`='$title', `text`='$text', meta_description='$meta_description', meta_title='$meta_title', last_updated='$last_updated' WHERE page_id = ".intval($id);
    SRPCore()->query($sql);
    //set last updated date for display
    $data_array = array("last_updated"=>date('m/d/Y g:i A', strtotime($last_updated)));
    echo json_encode($data_array);
}
elseif($_POST['type'] == 'pageAdd')
{
    $title = db_input($_POST['title']);
    $last_updated = date("Y-m-d H:i:s");
    //get max sort for existing pages
    $cursort = SRPCore()->query("SELECT MAX(sort_order) FROM pages WHERE parent_id = 0")->fetch_item();
    $cursort++;
    //insert the page
    SRPCore()->query("INSERT INTO pages (parent_id, title, sort_order, last_updated) VALUES ('0', '$title', '$cursort', '$last_updated')");
    //get inserted page id
    $page_id = SRPCore()->last_inserted();
    $data_array = array("page_id"=>$page_id);
    echo json_encode($data_array);
}
elseif($_POST['type'] == 'pageDelete')
{
    $id = $_POST['id'];

    //check to see if page can be deleted
    $page_res = SRPCore()->query("SELECT parent_id, disable_delete FROM pages WHERE page_id = ".intval($id));
    $page = $page_res->fetch();
    if($page['disable_delete'] == 1)
    {
        //this page cannot be deleted, show alert message
        $data_array = array("disallow"=>"yes");
    }
    else
    {
        //this page can be deleted
        //check for all documents to delete
        //check for all images to delete
        //delete the page record
        SRPCore()->query("DELETE FROM pages WHERE page_id = ".intval($id));
        //check for all page addons
        $addons_res = SRPCore()->query("SELECT * FROM pages_addons WHERE page_id = ".intval($id));
        while($addons = $addons_res->fetch())
        {
            //check table for addon & see if we have any images or documents to delete

        }
        //delete the addons for this page
        SRPCore()->query("DELETE FROM pages_addons WHERE page_id = ".intval($id));
        //check for sub pages; make parent id the deleted page's parent id
        $subpages_res = SRPCore()->query("SELECT page_id, sort_order FROM pages WHERE parent_id = ".intval($id)." ORDER BY sort_order");
        if($subpages_res->num_rows()!=0)
        {
            //get max sort from deleted page's parent
            $cursort = SRPCore()->query("SELECT MAX(sort_order) FROM pages WHERE parent_id = ".$page['parent_id'])->fetch_item();
            $cursort++;
            //loop through subpages
            while($subpages = $subpages_res->fetch())
            {
                //update parent_id & sort_order
                SRPCore()->query("UPDATE pages SET parent_id = ".$page['parent_id'].", sort_order=".$cursort." WHERE page_id=".$subpages['page_id']);
                //increment cursort
                $cursort++;
            }
        }
        
        $data_array = array("disallow"=>"no");
    }
    echo json_encode($data_array);
}
if($_POST['list'])
{
    order($_POST['list']);
}/*
//update settings
else if($_POST['type'] == 'setting')
{
    //get values
    //id is key
    $id = $_POST['id'];
    $value = db_input($_POST['value']);
    $cfg_res = $db->query("SELECT * FROM configuration WHERE `key` = '".$id."'");
    $cfg = $cfg_res->fetch();
    
    //now update that particular config item
    $sql = "UPDATE configuration SET value='$value' WHERE `key` = '".$id."'";
    $db->query($sql);
    //mail('derrek@scaredrabbit.com', 'TEST', $sql);
}
else if($_POST['type'] == 'library')
{
    $id = $_POST['id'];
    $title = db_input($_POST['value']);
    $sql = "UPDATE library SET `title`='$title' WHERE file_id = ".intval($id);
    $db->query($sql);
}*/
?>