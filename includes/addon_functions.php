<?php
/*************************************************************/
/*           Add, Update, Delete Add-On                      */
/*                    Functions                              */
/*************************************************************/

/*
 d888b  db       .d88b.  d8888b.  .d8b.  db      
88' Y8b 88      .8P  Y8. 88  `8D d8' `8b 88      
88      88      88    88 88oooY' 88ooo88 88      
88  ooo 88      88    88 88~~~b. 88~~~88 88      
88. ~8~ 88booo. `8b  d8' 88   8D 88   88 88booo. 
 Y888P  Y88888P  `Y88P'  Y8888P' YP   YP Y88888P 
*/

function has_addon($page_id)
{
    //returns true or false, whether or not page has any addons
    if(!empty($page_id))
    {
        $sql = "SELECT * FROM pages_addons WHERE page_id = ".intval($page_id);
        $addon_res = SRPCore()->query($sql);
        if($addon_res->num_rows()!=0)
        {
            return true;
        }
        else
            return false;
    }
    else
    {
        return false;
    }
}

function add_addon($page_id, $addon_id)
{
    if(!empty($page_id) && !empty($addon_id))
    {
        if(has_addon($page_id))
        {
            //see if this page already has this addon
            $addon_res = SRPCore()->query("SELECT * FROM pages_addons WHERE page_id = ".intval($page_id)." AND addon_id = ".intval($addon_id));
            if($addon_res->num_rows()==0)
            {
                //not already on the page, so let's add it
                //get sort_order
                $sql = "SELECT MAX(sort_order) FROM pages_addons WHERE page_id=".intval($page_id);
                $result = SRPCore()->query($sql);
                if($result->num_rows() != 0) 
                {
                    $cur_sort = $result->fetch_item();
                    $sort_order = $cur_sort + 1;
                }
                else
                {
                    $sort_order = 1;
                } 
                //add addon
                $sql = "INSERT INTO pages_addons (page_id, addon_id, sort_order)
                    VALUES ('$page_id', '$addon_id', '$sort_order')";
                SRPCore()->query($sql);
            }
        }
        else
        {
            //no addons yet! Add option for page text
            $sql = "INSERT INTO pages_addons (page_id, addon_id, sort_order)
                VALUES ('$page_id', '0', '1')";
            SRPCore()->query($sql);
            //add addon
            $sql = "INSERT INTO pages_addons (page_id, addon_id, sort_order)
                VALUES ('$page_id', '$addon_id', '2')";
            SRPCore()->query($sql);
        }
    }
}

function delete_addon($page_id, $addon_id)
{
    if(!empty($page_id) && !empty($addon_id))
    {
        $sql = "DELETE FROM pages_addons WHERE page_id = ".intval($page_id)." AND addon_id=".intval($addon_id);
        SRPCore()->query($sql);

        //get rid of all files & other table data
        $sql = "SELECT * FROM addons WHERE addon_id = ".intval($addon_id);
        $addon_res = SRPCore()->query($sql);
        $addon = $addon_res->fetch();
        $sql = "SELECT * FROM `".$addon['db_table']."` WHERE page_id = ".intval($page_id);
        $pg_addon_res = SRPCore()->query($sql);
        while($pg_addon = $pg_addon_res->fetch())
        {
            //call delete function for this addon
            $function = "delete_".$pg_addon['db_table'];
            $function($pg_addon['id_field']);
        }

    }
}

function get_page_addons($page_id)
{
    //returns an array of all page's addons
    if(!empty($page_id))
    {
        if(has_addon($page_id))
        {
            $addons_array = array();
            $sql = "SELECT * FROM pages_addons WHERE page_id = ".intval($page_id)." ORDER BY sort_order";
            $addons_res = SRPCore()->query($sql);
            while($addons = $addons_res->fetch())
            {
                //will be addon_id of 0 somewhere in array (equals page text)
                $addons_array[] = $addons['addon_id'];
            }
            return $addons_array;
        }
        else
        {
            return 0;
        }
    }
    else
        return 0;
}

/*
 d888b   .d8b.  db      db      d88888b d8888b. d888888b d88888b .d8888. 
88' Y8b d8' `8b 88      88      88'     88  `8D   `88'   88'     88'  YP 
88      88ooo88 88      88      88ooooo 88oobY'    88    88ooooo `8bo.   
88  ooo 88~~~88 88      88      88~~~~~ 88`8b      88    88~~~~~   `Y8b. 
88. ~8~ 88   88 88booo. 88booo. 88.     88 `88.   .88.   88.     db   8D 
 Y888P  YP   YP Y88888P Y88888P Y88888P 88   YD Y888888P Y88888P `8888Y' 
*/

function add_galleries($posted_array)
{
    if(is_array($posted_array))
    {
        $title = db_input($posted_array['title']);
        $page_id = intval($posted_array['page_id']);

        //get sort_order
        $sql = "SELECT MAX(sort_order) FROM galleries WHERE page_id=".$page_id;
        $result = SRPCore()->query($sql);
        if($result->num_rows() != 0) 
        {
            $cur_sort = $result->fetch_item();
            $sort_order = $cur_sort + 1;
        }
        else
        {
            $sort_order = 1;
        }  
        
        //insert the row
        $sql = "INSERT INTO galleries (page_id, title, sort_order, last_updated)
                VALUES ('$page_id', '$title', '$sort_order', now())";
        SRPCore()->query($sql);
    }
}

function edit_galleries($posted_array)
{
    if(is_array($posted_array))
    {
        $gallery_id = $posted_array['gallery_id'];
        $title = db_input($posted_array['title']);
        if(!empty($gallery_id))
        {
            //update the row
            $sql = "UPDATE galleries SET `title`='$title' WHERE gallery_id = ".intval($gallery_id);
            SRPCore()->query($sql);
        }
    }
}

function delete_galleries($gallery_id)
{
    if(!empty($gallery_id))
    {
        //delete images from gallery
        $sql = "SELECT filename FROM gallery_images WHERE gallery_id=".intval($gallery_id);
        $result = SRPCore()->query($sql);
        while($filename = $result->fetch_item()) 
        {
            if(!empty($filename))
            {
                if(file_exists("../files_uploaded/".$filename)) unlink("../files_uploaded/".$filename);
                if(file_exists("../files_uploaded/thumbs/".$filename)) unlink("../files_uploaded/thumbs/".$filename);
            }
        }

        //delete the image entries
        SRPCore()->query("DELETE FROM gallery_images WHERE gallery_id = ".intval($gallery_id));

        //delete the row
        SRPCore()->query("DELETE FROM galleries WHERE gallery_id = ".intval($gallery_id));
    }
}

function add_gallery_image($posted_array)
{
    if(is_array($posted_array))
    {
        $caption = db_input($posted_array['caption']);
        $gallery_id = $posted_array['gallery_id'];
        $filename = $posted_array['filename'];

        //get sort_order
        $sql = "SELECT MAX(sort_order) FROM gallery_images WHERE gallery_id=".intval($gallery_id);
        $result = SRPCore()->query($sql);
        if($result->num_rows() != 0) 
        {
            $cur_sort = $result->fetch_item();
            $sort_order = $cur_sort + 1;
        }
        else
        {
            $sort_order = 1;
        }
        //insert the row
        $sql = "INSERT INTO gallery_images (filename, caption, gallery_id, sort_order, last_updated)
                    VALUES ('$filename', '$caption', '$gallery_id', '$sort_order', now())";
        SRPCore()->query($sql);
    }
}

function edit_gallery_image($posted_array)
{
    if(is_array($posted_array))
    {
        $image_id = $posted_array['image_id'];
        $caption = db_input($posted_array['caption']);
        $filename = $posted_array['filename'];
        if(!empty($image_id))
        {
            if(!empty($filename))
            {
                //update the row
                $sql = "UPDATE gallery_images SET `filename`='$filename', caption='$caption', last_updated=now() WHERE image_id = ".intval($image_id);
                SRPCore()->query($sql);
            }
            else
            {
                $sql = "UPDATE gallery_images SET caption='$caption', last_updated=now() WHERE image_id = ".intval($image_id);
                SRPCore()->query($sql);
            }
        }
    }
}

function delete_gallery_image($image_id)
{
    if(!empty($image_id))
    {
        //delete image from gallery
        $sql = "SELECT filename FROM gallery_images WHERE image_id=".intval($image_id);
        $result = SRPCore()->query($sql);
        $filename = $result->fetch_item(); 
        
        if(!empty($filename))
        {
            if(file_exists("../files_uploaded/".$filename)) unlink("../files_uploaded/".$filename);
            if(file_exists("../files_uploaded/thumbs/".$filename)) unlink("../files_uploaded/thumbs/".$filename);
        }
        
        //delete the image entries
        SRPCore()->query("DELETE FROM gallery_images WHERE image_id = ".intval($image_id));
    }
}

function get_num_galleries($page_id)
{
    $num_galleries = 0;
    if(!empty($page_id))
    {
        $galleries_res = SRPCore()->query("SELECT g.gallery_id, COUNT(i.image_id) as image_count FROM galleries g LEFT JOIN gallery_images i ON g.gallery_id=i.gallery_id WHERE g.page_id = '".intval($page_id)."' GROUP BY g.gallery_id HAVING image_count>0");
        $num_galleries = $galleries_res->num_rows();
    }
    return $num_galleries;
}

function get_num_gallery_images($gallery_id)
{
    $num_gallery_images = 0;
    if(!empty($gallery_id))
    {
        $gallery_images_res = SRPCore()->query("SELECT image_id FROM gallery_images WHERE gallery_id = ".intval($gallery_id));
        $num_gallery_images = $gallery_images_res->num_rows();
    }
    return $num_gallery_images;
}

function get_gallery_title($gallery_id)
{
    $title = "";
    if(!empty($gallery_id))
    {
        $title = SRPCore()->query("SELECT title FROM galleries WHERE gallery_id = ".intval($gallery_id))->fetch_item();
    }
    return $title;
}

/*
db    db d888888b d8888b. d88888b  .d88b.  .d8888. 
88    88   `88'   88  `8D 88'     .8P  Y8. 88'  YP 
Y8    8P    88    88   88 88ooooo 88    88 `8bo.   
`8b  d8'    88    88   88 88~~~~~ 88    88   `Y8b. 
 `8bd8'    .88.   88  .8D 88.     `8b  d8' db   8D 
   YP    Y888888P Y8888D' Y88888P  `Y88P'  `8888Y' 
*/

function add_videos($posted_array)
{
    if(is_array($posted_array))
    {
        $title = db_input($posted_array['title']);
        $embed_code = db_input($posted_array['embed_code']);
        $page_id = intval($posted_array['page_id']);
        //get sort_order
        $sql = "SELECT MAX(sort_order) FROM videos WHERE page_id=".$page_id;
        $result = SRPCore()->query($sql);
        if($result->num_rows() != 0) 
        {
            $cur_sort = $result->fetch_item();
            $sort_order = $cur_sort + 1;
        }
        else
        {
            $sort_order = 1;
        }  
        
        //insert the row
        $sql = "INSERT INTO videos (page_id, title, embed_code, sort_order, last_updated)
                VALUES ('$page_id', '$title', '$embed_code', '$sort_order', now())";
        SRPCore()->query($sql);
    }
}

function edit_videos($posted_array)
{
    if(is_array($posted_array))
    {
        $video_id = $posted_array['video_id'];
        $title = db_input($posted_array['title']);
        $embed_code = db_input($posted_array['embed_code']);
        if(!empty($video_id))
        {
            //update the row
            $sql = "UPDATE videos SET `embed_code`='$embed_code', `title`='$title', last_updated=now() WHERE video_id = ".intval($video_id);
            SRPCore()->query($sql);
        }
    }
}

function delete_videos($video_id)
{
    if(!empty($video_id))
    {
        //delete the row
        SRPCore()->query("DELETE FROM videos WHERE video_id = ".intval($video_id));
    }
}

/*
.d8888. d888888b  .d8b.  d88888b d88888b 
88'  YP `~~88~~' d8' `8b 88'     88'     
`8bo.      88    88ooo88 88ooo   88ooo   
  `Y8b.    88    88~~~88 88~~~   88~~~   
db   8D    88    88   88 88      88      
`8888Y'    YP    YP   YP YP      YP 
*/

function add_staff($posted_array)
{
    if(is_array($posted_array))
    {
        $page_id = $posted_array['page_id'];
        $name = db_input($posted_array['name']);
        $title = db_input($posted_array['title']);
        $email = db_input($posted_array['email']);
        $phone = db_input($posted_array['phone']);
        $cell_phone = db_input($posted_array['cell_phone']);
        $bio = db_input($posted_array['bio']);
        $filename = $posted_array['filename'];
        
        //sort_order
        //get sort_order
        $sql = "SELECT MAX(sort_order) FROM staff WHERE page_id=".intval($page_id);
        $result = SRPCore()->query($sql);
        if($result->num_rows() != 0) 
        {
            $cur_sort = $result->fetch_item();
            $sort_order = $cur_sort + 1;
        }
        else
        {
            $sort_order = 1;
        }

        //insert the row
        $sql = "INSERT INTO staff (page_id, name, title, email, phone, cell_phone, bio, filename, sort_order, last_updated)
                    VALUES ('$page_id', '$name', '$title', '$email', '$phone', '$cell_phone', '$bio', '$filename', '$sort_order', now())";
        SRPCore()->query($sql);
    }
}

function edit_staff($posted_array)
{
    if(is_array($posted_array))
    {
        $staff_id = $posted_array['staff_id'];
        $name = db_input($posted_array['name']);
        $title = db_input($posted_array['title']);
        $email = db_input($posted_array['email']);
        $phone = db_input($posted_array['phone']);
        $cell_phone = db_input($posted_array['cell_phone']);
        $bio = db_input($posted_array['bio']);
        $filename = $posted_array['filename'];
        if(!empty($staff_id))
        {
            //update the row
            $sql = "UPDATE staff SET name='$name', title='$title', email='$email', phone='$phone', cell_phone='$cell_phone', bio='$bio', `last_updated`=now() WHERE staff_id=".intval($staff_id);
            SRPCore()->query($sql);

            if(!empty($filename))
            {
                //update the filename
                $sql = "UPDATE staff SET filename='$filename', `last_updated`=now() WHERE staff_id=".intval($staff_id);
                SRPCore()->query($sql);
            }
        }
    }
}

function delete_staff($staff_id)
{
    if(!empty($staff_id))
    {
        //delete image
        $sql = "SELECT filename FROM staff WHERE staff_id=".intval($staff_id);
        $result = SRPCore()->query($sql);
        $filename = $result->fetch_item(); 
        
        if(!empty($filename))
        {
            if(file_exists("../files_uploaded/".$filename)) unlink("../files_uploaded/".$filename);
            if(file_exists("../files_uploaded/thumbs/".$filename)) unlink("../files_uploaded/thumbs/".$filename);
        }
        //delete the row
        SRPCore()->query("DELETE FROM staff WHERE staff_id = ".intval($staff_id));
    }
}

/*
 .d8b.   .o88b.  .o88b.  .d88b.  d8888b. d8888b. d888888b  .d88b.  d8b   db 
d8' `8b d8P  Y8 d8P  Y8 .8P  Y8. 88  `8D 88  `8D   `88'   .8P  Y8. 888o  88 
88ooo88 8P      8P      88    88 88oobY' 88   88    88    88    88 88V8o 88 
88~~~88 8b      8b      88    88 88`8b   88   88    88    88    88 88 V8o88 
88   88 Y8b  d8 Y8b  d8 `8b  d8' 88 `88. 88  .8D   .88.   `8b  d8' 88  V888 
YP   YP  `Y88P'  `Y88P'  `Y88P'  88   YD Y8888D' Y888888P  `Y88P'  VP   V8P 
*/

function add_accordion($posted_array)
{
    if(is_array($posted_array))
    {
        $page_id = $posted_array['page_id'];
        $text_1 = db_input($posted_array['text_1']);
        $text_2 = db_input($posted_array['text_2']);
        
        //get sort_order
        $sql = "SELECT MAX(sort_order) FROM accordion WHERE page_id=".intval($page_id);
        $result = SRPCore()->query($sql);
        if($result->num_rows() != 0) 
        {
            $cur_sort = $result->fetch_item();
            $sort_order = $cur_sort + 1;
        }
        else
        {
            $sort_order = 1;
        }
        
        //insert the row
        $sql = "INSERT INTO accordion (page_id, text_1, text_2, sort_order, last_updated)
                    VALUES ('$page_id', '$text_1', '$text_2', '$sort_order', now())";
        SRPCore()->query($sql);
    }
}

function edit_accordion($posted_array)
{
    if(is_array($posted_array))
    {
        $accordion_id = $posted_array['accordion_id'];
        $text_1 = db_input($posted_array['text_1']);
        $text_2 = db_input($posted_array['text_2']);
        
        if(!empty($accordion_id))
        {
            //update the row
            $sql = "UPDATE accordion SET text_1='$text_1', text_2='$text_2', `last_updated`=now() WHERE accordion_id=".intval($accordion_id);
            SRPCore()->query($sql);
        }
    }
}

function delete_accordion($accordion_id)
{
    if(!empty($accordion_id))
    {
        //delete the row
        SRPCore()->query("DELETE FROM accordion WHERE accordion_id = ".intval($accordion_id));
    }
}

/*
d88888b  .d88b.  d8888b. .88b  d88. .d8888. 
88'     .8P  Y8. 88  `8D 88'YbdP`88 88'  YP 
88ooo   88    88 88oobY' 88  88  88 `8bo.   
88~~~   88    88 88`8b   88  88  88   `Y8b. 
88      `8b  d8' 88 `88. 88  88  88 db   8D 
YP       `Y88P'  88   YD YP  YP  YP `8888Y' 
*/

function add_forms($posted_array)
{
    if(is_array($posted_array))
    {
        //get the values from the form
        $page_id = $posted_array['page_id'];
        $name = db_input($posted_array['name']);
        $email = db_input($posted_array['email']);
        $text = db_input($posted_array['text']);
        $secret = md5(date('YmdHis'));

        //insert the row
        $sql = "INSERT INTO forms (page_id, name, email, `text`, secret)
                VALUES ('$page_id', '$name', '$email', '$text', '$secret')";
        SRPCore()->query($sql);
    }
}

function edit_forms($posted_array)
{
    if(is_array($posted_array))
    {
        //get the values from the form
        $form_id = $posted_array['form_id'];
        $name = db_input($posted_array['name']);
        $email = db_input($posted_array['email']);
        $text = db_input($posted_array['text']);
        $secret = md5(date('YmdHis'));
        if(!empty($form_id))
        {
            //update the row
            $sql = "UPDATE forms SET name='$name', email='$email', `text`='$text', secret='$secret' WHERE form_id=".intval($form_id);
            SRPCore()->query($sql);
        }
    }
}

function delete_forms($form_id)
{
    if(!empty($form_id))
    {
        //delete form
        $field_res = SRPCore()->query("SELECT field_id FROM fields WHERE form_id = ".intval($form_id));
        while($field_id = $field_res->fetch_item())
        {
            //delete options
            SRPCore()->query("DELETE FROM field_options WHERE field_id = $field_id");
            //delete field
            SRPCore()->query("DELETE FROM fields WHERE field_id = $field_id");
        }
        //delete form
        SRPCore()->query("DELETE FROM forms WHERE form_id = ".intval($form_id));
    }
}

function add_fields($posted_array)
{
    if(is_array($posted_array))
    {
        $form_id = intval($posted_array['form_id']);
        $title = db_input($posted_array['title']);
        $field_type = db_input($posted_array['field_type']);
        $logical_type = db_input($posted_array['logical_type']);
        $required = (isset($posted_array['required']))?1:0;
        //get sort
        $sql = "SELECT MAX(sort_order) FROM `fields` WHERE form_id=".intval($form_id);
        $result = SRPCore()->query($sql);
        if($result->num_rows() != 0) 
        {
            $cur_sort = $result->fetch_item();
            $sort_order = $cur_sort + 1;
        }
        else
        {
            $sort_order = 1;
        }
        
        //insert the row
        $sql = "INSERT INTO `fields` (form_id, logical_type, title, field_type, required, sort_order)
                    VALUES ('$form_id', '$logical_type', '$title', '$field_type', '$required', '$sort_order', now())";
        SRPCore()->query($sql);
    }
}

function edit_fields($posted_array)
{
    if(is_array($posted_array))
    {
        $field_id = intval($posted_array['field_id']);
        $title = db_input($posted_array['title']);
        $field_type = db_input($posted_array['field_type']);
        $logical_type = db_input($posted_array['logical_type']);
        $required = (isset($posted_array['required']))?1:0;

        $sql = "UPDATE `fields` SET title='$title', field_type='$field_type', logical_type='$logical_type', required='$required' WHERE field_id=$field_id";
        SRPCore()->query($sql);
    }
        
}

function delete_fields($field_id)
{
    if(!empty($field_id))
    {
        //delete options
        SRPCore()->query("DELETE FROM field_options WHERE field_id = ".intval($field_id));
        //delete field
        SRPCore()->query("DELETE FROM fields WHERE field_id = ".intval($field_id));
    }
}

function add_field_options($posted_array)
{
    if(is_array($posted_array))
    {
        $field_id = intval($posted_array['field_id']);
        $text = db_input($posted_array['text']);
        $value = db_input($posted_array['value']);
        $default = (isset($posted_array['default']))?1:0;
        //get sort_order
        $sql = "SELECT MAX(sort_order) FROM field_options WHERE field_id = $field_id";
        $result = SRPCore()->query($sql);
        if($result->num_rows() != 0) 
        {
            $cur_sort = $result->fetch_item();
            $sort_order = $cur_sort + 1;
        }
        else
        {
            $sort_order = 1;
        }

        $sql = "INSERT INTO field_options (field_id, value, `text`, `default`, sort_order)
            VALUES
            ('$field_id', '$value', '$text', '$default', '$sort_order')";

        SRPCore()->query($sql);
    }
}

function edit_field_options($posted_array)
{
    if(is_array($posted_array))
    {
        $field_option_id = intval($posted_array['field_option_id']);
        $value = $posted_array['value'];
        $text = $posted_array['text'];
        $default = ($posted_array['default'] == 'true')?1:0;

        $sql = "UPDATE field_options SET value='$value', `text`='$text', `default`=$default WHERE field_option_id=$field_option_id";
        SRPCore()->query($sql);
    }
}

function delete_field_options($field_option_id)
{
    if(!empty($field_option_id))
    {
        $sql = "DELETE FROM field_options WHERE field_option_id = ".intval($option_id);
        SRPCore()->query($sql);
    }
}

/*
db       .d88b.   d888b   .d88b.  .d8888. 
88      .8P  Y8. 88' Y8b .8P  Y8. 88'  YP 
88      88    88 88      88    88 `8bo.   
88      88    88 88  ooo 88    88   `Y8b. 
88booo. `8b  d8' 88. ~8~ `8b  d8' db   8D 
Y88888P  `Y88P'   Y888P   `Y88P'  `8888Y'
*/

function add_logos($posted_array)
{
    if(is_array($posted_array))
    {
        $page_id = $posted_array['page_id'];
        $name = db_input($posted_array['name']);
        $url = db_input($posted_array['url']);
        $filename = $posted_array['filename'];

        //get sort_order
        $sql = "SELECT MAX(sort_order) FROM logos WHERE page_id=".intval($page_id);
        $result = SRPCore()->query($sql);
        if($result->num_rows() != 0) 
        {
            $cur_sort = $result->fetch_item();
            $sort_order = $cur_sort + 1;
        }
        else
        {
            $sort_order = 1;
        }
        //insert the row
        $sql = "INSERT INTO logos (page_id, filename, name, url, sort_order, last_updated)
                    VALUES ('$page_id', '$filename', '$name', '$url', '$sort_order', now())";
        SRPCore()->query($sql);
    }
}

function edit_logos($posted_array)
{
    if(is_array($posted_array))
    {
        $logo_id = $posted_array['logo_id'];
        $name = db_input($posted_array['name']);
        $url = db_input($posted_array['url']);
        $filename = $posted_array['filename'];
        if(!empty($logo_id))
        {
            if(!empty($filename))
            {
                //update the row
                $sql = "UPDATE logos SET `filename`='$filename', name='$name', url='$url', last_updated=now() WHERE logo_id = ".intval($logo_id);
                SRPCore()->query($sql);
            }
            else
            {
                $sql = "UPDATE logos SET name='$name', url='$url', last_updated=now() WHERE logo_id = ".intval($logo_id);
                SRPCore()->query($sql);
            }
        }
    }
}

function delete_logos($logo_id)
{
    if(!empty($logo_id))
    {
        //delete image
        $sql = "SELECT filename FROM logos WHERE logo_id=".intval($logo_id);
        $result = SRPCore()->query($sql);
        $filename = $result->fetch_item(); 
        
        if(!empty($filename))
        {
            if(file_exists("../files_uploaded/".$filename)) unlink("../files_uploaded/".$filename);
            if(file_exists("../files_uploaded/thumbs/".$filename)) unlink("../files_uploaded/thumbs/".$filename);
        }
        
        //delete the image entries
        SRPCore()->query("DELETE FROM logos WHERE logo_id = ".intval($logo_id));
    }
}

/*
.d8888. db      d888888b d8888b. d88888b .d8888. db   db  .d88b.  db   d8b   db 
88'  YP 88        `88'   88  `8D 88'     88'  YP 88   88 .8P  Y8. 88   I8I   88 
`8bo.   88         88    88   88 88ooooo `8bo.   88ooo88 88    88 88   I8I   88 
  `Y8b. 88         88    88   88 88~~~~~   `Y8b. 88~~~88 88    88 Y8   I8I   88 
db   8D 88booo.   .88.   88  .8D 88.     db   8D 88   88 `8b  d8' `8b d8'8b d8' 
`8888Y' Y88888P Y888888P Y8888D' Y88888P `8888Y' YP   YP  `Y88P'   `8b8' `8d8'  
*/

function add_slideshow($posted_array)
{
    if(is_array($posted_array))
    {
        $page_id = $posted_array['page_id'];
        $caption = db_input($posted_array['caption']);
        $filename = $posted_array['filename'];

        //get sort_order
        $sql = "SELECT MAX(sort_order) FROM slideshow WHERE page_id=".intval($page_id);
        $result = SRPCore()->query($sql);
        if($result->num_rows() != 0) 
        {
            $cur_sort = $result->fetch_item();
            $sort_order = $cur_sort + 1;
        }
        else
        {
            $sort_order = 1;
        }
        //insert the row
        $sql = "INSERT INTO slideshow (page_id, filename, caption, sort_order, last_updated)
                    VALUES ('$page_id', '$filename', '$caption', '$sort_order', now())";
        SRPCore()->query($sql);
    }
}

function edit_slideshow($posted_array)
{
    if(is_array($posted_array))
    {
        $image_id = $posted_array['image_id'];
        $caption = db_input($posted_array['caption']);
        $filename = $posted_array['filename'];
        if(!empty($image_id))
        {
            if(!empty($filename))
            {
                //update the row
                $sql = "UPDATE slideshow SET `filename`='$filename', caption='$caption', last_updated=now() WHERE image_id = ".intval($image_id);
                SRPCore()->query($sql);
            }
            else
            {
                $sql = "UPDATE slideshow SET caption='$caption', last_updated=now() WHERE image_id = ".intval($image_id);
                SRPCore()->query($sql);
            }
        }
    }
}

function delete_slideshow($image_id)
{
    if(!empty($image_id))
    {
        //delete image
        $sql = "SELECT filename FROM slideshow WHERE image_id=".intval($image_id);
        $result = SRPCore()->query($sql);
        $filename = $result->fetch_item(); 
        
        if(!empty($filename))
        {
            if(file_exists("../files_uploaded/".$filename)) unlink("../files_uploaded/".$filename);
            if(file_exists("../files_uploaded/thumbs/".$filename)) unlink("../files_uploaded/thumbs/".$filename);
        }
        
        //delete the image entries
        SRPCore()->query("DELETE FROM slideshow WHERE image_id = ".intval($image_id));
    }
}

/*
 .o88b.  .d8b.  db      d88888b d8b   db d8888b.  .d8b.  d8888b. 
d8P  Y8 d8' `8b 88      88'     888o  88 88  `8D d8' `8b 88  `8D 
8P      88ooo88 88      88ooooo 88V8o 88 88   88 88ooo88 88oobY' 
8b      88~~~88 88      88~~~~~ 88 V8o88 88   88 88~~~88 88`8b   
Y8b  d8 88   88 88booo. 88.     88  V888 88  .8D 88   88 88 `88. 
 `Y88P' YP   YP Y88888P Y88888P VP   V8P Y8888D' YP   YP 88   YD 
*/

function add_calendar($posted_array)
{
    if(is_array($posted_array))
    {
        $page_id = $posted_array['page_id'];
        $title = db_input($posted_array['title']);
        $text = db_input($posted_array['text']);
        $start_date = db_input($posted_array['start_date']);
        $start_time = db_input($posted_array['start_time']);
        $end_date = db_input($posted_array['end_date']);
        $end_time = db_input($posted_array['end_time']);
        
        $sql = "INSERT INTO calendar (page_id, title, `text`, start_date, start_time, end_date, end_time, last_updated)
                    VALUES ('$page_id', '$title', '$text', '$start_date', '$start_time', '$end_date', '$end_time', now())";
        SRPCore()->query($sql);
    }
}

function edit_calendar($posted_array)
{
    if(is_array($posted_array))
    {
        $calendar_id = $posted_array['calendar_id'];
        $title = db_input($posted_array['title']);
        $text = db_input($posted_array['text']);
        $start_date = db_input($posted_array['start_date']);
        $start_time = db_input($posted_array['start_time']);
        $end_date = db_input($posted_array['end_date']);
        $end_time = db_input($posted_array['end_time']);

        $sql = "UPDATE calendar SET title='$title', `text`='$text', `start_date`='$start_date', `end_date`='$end_date', `last_updated`=now() WHERE calendar_id=$calendar_id";
        SRPCore()->query($sql);
        if(is_null($start_time))
        {
            $sql = "UPDATE calendar SET start_time=NULL, `last_updated`=now() WHERE calendar_id=$calendar_id";
            //execute the update
            SRPCore()->query($sql);
        }
        else
        {
            $sql = "UPDATE calendar SET start_time='$start_time', `last_updated`=now() WHERE calendar_id=$calendar_id";
            //execute the update
            SRPCore()->query($sql);
        }
        if(is_null($end_time))
        {
            $sql = "UPDATE calendar SET end_time=NULL, `last_updated`=now() WHERE calendar_id=$calendar_id";
            //execute the update
            SRPCore()->query($sql);
        }
        else
        {
            $sql = "UPDATE calendar SET end_time='$end_time', `last_updated`=now() WHERE calendar_id=$calendar_id";
            //execute the update
            SRPCore()->query($sql);
        }
        
    }
}

function delete_calendar($calendar_id)
{
    if(!empty($calendar_id))
    {
        //delete the event
        SRPCore()->query("DELETE FROM calendar WHERE calendar_id = ".intval($calendar_id));
    }
}

/*
d8888b.  .d88b.   .o88b. .d8888.   db      d888888b .d8888. d888888b 
88  `8D .8P  Y8. d8P  Y8 88'  YP   88        `88'   88'  YP `~~88~~' 
88   88 88    88 8P      `8bo.     88         88    `8bo.      88    
88   88 88    88 8b        `Y8b.   88         88      `Y8b.    88    
88  .8D `8b  d8' Y8b  d8 db   8D   88booo.   .88.   db   8D    88    
Y8888D'  `Y88P'   `Y88P' `8888Y'   Y88888P Y888888P `8888Y'    YP    
*/

function add_document_lists($posted_array)
{
    if(is_array($posted_array))
    {
        $title = db_input($posted_array['title']);
        $page_id = intval($posted_array['page_id']);
        //get sort_order
        $sql = "SELECT MAX(sort_order) FROM document_lists WHERE page_id=".$page_id;
        $result = SRPCore()->query($sql);
        if($result->num_rows() != 0) 
        {
            $cur_sort = $result->fetch_item();
            $sort_order = $cur_sort + 1;
        }
        else
        {
            $sort_order = 1;
        }  
        
        //insert the row
        $sql = "INSERT INTO document_lists (page_id, title, sort_order, last_updated)
                VALUES ('$page_id', '$title', '$sort_order', now())";
        SRPCore()->query($sql);
    }
}

function edit_document_lists($posted_array)
{
    if(is_array($posted_array))
    {
        $list_id = $posted_array['list_id'];
        $title = db_input($posted_array['title']);
        if(!empty($list_id))
        {
            //update the row
            $sql = "UPDATE document_lists SET `title`='$title' WHERE list_id = ".intval($list_id);
            SRPCore()->query($sql);
        }
    }
}

function delete_document_lists($list_id)
{
    if(!empty($list_id))
    {
        //delete docs from list
        $sql = "SELECT filename FROM documents WHERE list_id=".intval($list_id);
        $result = SRPCore()->query($sql);
        while($filename = $result->fetch_item()) 
        {
            if(!empty($filename))
            {
                if(file_exists("../files_uploaded/".$filename)) unlink("../files_uploaded/".$filename);
                if(file_exists("../files_uploaded/thumbs/".$filename)) unlink("../files_uploaded/thumbs/".$filename);
            }
        }

        //delete the image entries
        SRPCore()->query("DELETE FROM documents WHERE list_id = ".intval($list_id));

        //delete the row
        SRPCore()->query("DELETE FROM document_lists WHERE list_id = ".intval($list_id));
    }
}

function add_documents($posted_array)
{
    if(is_array($posted_array))
    {
        $list_id = $posted_array['list_id'];
        $title = db_input($posted_array['title']);
        $filename = $posted_array['filename'];

        //get sort_order
        $sql = "SELECT MAX(sort_order) FROM documents WHERE list_id=".intval($list_id);
        $result = SRPCore()->query($sql);
        if($result->num_rows() != 0) 
        {
            $cur_sort = $result->fetch_item();
            $sort_order = $cur_sort + 1;
        }
        else
        {
            $sort_order = 1;
        }
        //insert the row
        $sql = "INSERT INTO documents (list_id, filename, title, sort_order, last_updated)
                    VALUES ('$list_id', '$filename', '$title', '$sort_order', now())";
        SRPCore()->query($sql);
    }
}

function edit_documents($posted_array)
{
    if(is_array($posted_array))
    {
        $document_id = $posted_array['document_id'];
        $title = db_input($posted_array['title']);
        $filename = $posted_array['filename'];
        if(!empty($image_id))
        {
            if(!empty($filename))
            {
                //update the row
                $sql = "UPDATE documents SET `filename`='$filename', title='$title', last_updated=now() WHERE document_id = ".intval($document_id);
                SRPCore()->query($sql);
            }
            else
            {
                $sql = "UPDATE documents SET title='$title', last_updated=now() WHERE document_id = ".intval($document_id);
                SRPCore()->query($sql);
            }
        }
    }
}

function delete_documents($document_id)
{
    if(!empty($document_id))
    {
        //delete image
        $sql = "SELECT filename FROM documents WHERE document_id=".intval($document_id);
        $result = SRPCore()->query($sql);
        $filename = $result->fetch_item(); 
        
        if(!empty($filename))
        {
            if(file_exists("../files_uploaded/".$filename)) unlink("../files_uploaded/".$filename);
            if(file_exists("../files_uploaded/thumbs/".$filename)) unlink("../files_uploaded/thumbs/".$filename);
        }
        
        //delete the image entries
        SRPCore()->query("DELETE FROM documents WHERE document_id = ".intval($document_id));
    }
}

?>