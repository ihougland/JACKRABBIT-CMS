<?php
require_once('includes/application_top.php');
//updates various things via AJAX
$db = SRPCore();

//update page
if($_POST['type'] == 'page')
{
    $id = $_POST['id'];
    $title = db_input($_POST['title']);
    $text = db_input($_POST['text']);
    $meta_description = db_input($_POST['meta_description']);
    $meta_title = db_input($_POST['meta_title']);
    $last_updated = date("Y-m-d H:i:s");

    $sql = "UPDATE pages SET `title`='$title', `text`='$text', meta_description='$meta_description', meta_title='$meta_title', last_updated='$last_updated' WHERE page_id = ".intval($id);
    SRPCore()->query($sql);

    $data_array = array("last_updated"=>date('m/d/Y g:i A', strtotime($last_updated)));
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