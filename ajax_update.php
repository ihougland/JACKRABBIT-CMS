<?php
require_once('includes/application_top.php');
include("includes/addon_functions.php");
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
    $sql = "UPDATE pages SET `title`='$title', `text`='$text', meta_description='$meta_description', meta_title='$meta_title', external_url='$external_url', last_updated='$last_updated' WHERE page_id = ".intval($id);
    SRPCore()->query($sql);
    //set last updated date for display
    $data_array = array("last_updated"=>date('m/d/Y g:i A', strtotime($last_updated)), "page_title"=>$title);
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
elseif($_POST['type'] == 'pageType')
{
    $id = db_input($_POST['id']);
    $type = db_input($_POST['page_type']);
    $last_updated = date("Y-m-d H:i:s");
    //insert the page
    $sql = "UPDATE pages SET type='$type', last_updated='$last_updated' WHERE page_id = ".intval($id);
    SRPCore()->query($sql);
   
    $data_array = array("page_id"=>$id);
    echo json_encode($data_array);
}
elseif($_POST['type'] == 'pageDelete')
{
    $id = $_POST['id'];
    $files_array = $_POST['files_array'];

    //check to see if page can be deleted
    $page_res = SRPCore()->query("SELECT * FROM pages WHERE page_id = ".intval($id));
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
        if(!empty($page['filename']))
        {
            //see if file exists
            if(file_exists("../files_uploaded/".$page['filename']))
            {
                //delete the file
                unlink("../files_uploaded/".$page['filename']);
            }
        }
        //check for all images & documents to delete
        if(is_array($files_array))
        {
            if(count($files_array)>0)
            {
                //loop through & delete files
                foreach($files_array as $file)
                {
                    $filename = array_pop(explode('/', $file));
                    if(file_exists("../files_uploaded/".$filename))
                    {
                        unlink("../files_uploaded/".$filename);
                    }
                    if(file_exists("../files_uploaded/thumbs/".$filename))
                    {
                        unlink("../files_uploaded/thumbs/".$filename);
                    }
                }
            }
        }
        //delete the page record
        SRPCore()->query("DELETE FROM pages WHERE page_id = ".intval($id));
        //check for all page addons
        $addons_res = SRPCore()->query("SELECT * FROM pages_addons WHERE page_id = ".intval($id));
        while($addons = $addons_res->fetch())
        {
            //check table for addon & delete
            delete_addon($id, $addons['addon_id']);
        }
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
elseif($_POST['type'] == 'setting')
{
    //get values
    //id is key
    $id = $_POST['id'];
    $value = db_input($_POST['value']);
    $cfg_res = SRPCore()->query("SELECT * FROM configuration WHERE `key` = '".$id."'");
    $cfg = $cfg_res->fetch();
    
    //now update that particular config item
    $sql = "UPDATE configuration SET value='$value' WHERE `key` = '".$id."'";
    SRPCore()->query($sql);
}
elseif($_POST['type'] == 'seoData')
{
    $page_id = $_POST["page_id"];
    $fieldname = $_POST['fieldname'];
    $value = db_input($_POST['value']);

    //update SEO data
    if(!empty($page_id) && !empty($fieldname))
    {
        $sql = "UPDATE `pages` SET `".$fieldname."` = '".$value."' WHERE page_id=".intval($page_id);
        SRPCore()->query($sql);
    }
}
elseif($_POST['type']=='fileDelete')
{
    if(!empty($_POST['filename']))
    { 
        //separate filename from full src
        $filename = array_pop(explode('/', $_POST['filename']));
        if(file_exists("../files_uploaded/".$filename))
        {
            unlink("../files_uploaded/".$filename);
        }
        if(file_exists("../files_uploaded/thumbs/".$filename))
        {
            unlink("../files_uploaded/thumbs/".$filename);
        }
        $data_array = array('error_msg' => '');
    }
    else
    {
        $data_array = array('error_msg' => 'No file selected.');
    }
    echo json_encode($data_array);
}
elseif($_POST['type']=='addAddon')
{
    $page_id = $_POST['page_id'];
    $addon_id = $_POST['addon_id'];
    add_addon($page_id, $addon_id);
}
elseif($_POST['type']=='deleteAddon')
{
    $page_id = $_POST['page_id'];
    $addon_id = $_POST['addon_id'];
    delete_addon($page_id, $addon_id);
}
elseif($_POST['type']=='sortAddons')
{
    $sort = $_POST['addonSort'];
    $page_id = $_POST['page_id'];
    $addon_id = '';
    $sort_order = 1;
    if(is_array($sort))
    {
        foreach($sort as $item)
        {
            $array = explode("_", $item);
            $addon_id = array_pop($array);
            $sql = "UPDATE pages_addons SET sort_order='".$sort_order."' WHERE page_id='".intval($page_id)."' AND addon_id=".$addon_id;
            SRPCore()->query($sql); 
            // increment the sort order for this level
            $sort_order++;
        }
        echo json_encode(array('error_msg'=>""));
    }
    else
    {
        echo json_encode(array('error_msg'=>"oops! not an array!"));
    }
}
elseif($_POST['type'] == 'galleryCaption')
{
    $image_id = $_POST["id"];
    $caption = db_input($_POST['value']);

    //update Gallery image caption data
    if(!empty($image_id))
    {
        $sql = "UPDATE `gallery_images` SET `caption` = '".$caption."' WHERE image_id=".intval($image_id);
        SRPCore()->query($sql);
    }
}
elseif($_POST['type'] == 'documentTitle')
{
    $document_id = $_POST["id"];
    $title = db_input($_POST['value']);

    //update Gallery image caption data
    if(!empty($document_id))
    {
        $sql = "UPDATE `documents` SET `title` = '".$title."' WHERE document_id=".intval($document_id);
        SRPCore()->query($sql);
    }
}
if($_POST['list'])
{
    order($_POST['list']);
}
?>