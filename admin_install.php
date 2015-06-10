<?php
include('includes/application_top.php');
//configure Settings & Login
$db = SRPCore();
if(isset($_GET['action']) && $_GET['action']=="submit")
{
    $_SESSION['aderror']= '';
    $error = '';
    $username = db_input($_POST['username']);
    $p = db_input($_POST['p']);
    $p2 = db_input($_POST['p2']);
    $settings_res = SRPCore()->query("SELECT `key` FROM configuration WHERE category = 'Admin' ORDER BY configuration_id ASC");
    $num_settings = $settings_res->num_rows();
    for($i=0;$i<$num_settings;$i++)
    {
        $key[$i] = $settings_res->fetch_item();
        $settings[$i] = db_input($_POST[$key[$i]]);
    }
    $addon_res = SRPCore()->query("SELECT `addon_id` FROM addons ORDER BY sort_order ASC");
    $num_addons = $addon_res->num_rows();
    for($j=0;$j<$num_addons;$j++)
    {
        $addon_id[$j] = $addon_res->fetch_item();
        $addons[$j] = db_input($_POST['addon_'.$addon_id[$j]]);
    }
    if(empty($username))
    {
        $error .= "Username is required!<br>";
    }
    if($p!=$p2)
    {
        //error!
        $error .= "Passwords must match!<br>";
    }
    if(empty($error))
    {
        //update!
        // Create a random salt
        $random_salt = hash('sha512', uniqid(openssl_random_pseudo_bytes(16), TRUE));
        // Create salted password 
        $password = hash('sha512', $p . $random_salt);

        //add username & password to admin table
        $sql = "INSERT INTO admin (username, password, salt) VALUES ('$username', '$password', '$random_salt')";
        SRPCore()->query($sql);
        //echo $sql."<br><br>";

        //update admin config values
        for($i=0;$i<$num_settings;$i++)
        {
            $sql = "UPDATE configuration SET value='".$settings[$i]."' WHERE `key`='".$key[$i]."'";
            SRPCore()->query($sql);
            //echo $sql."<br><br>";
        }
        //update addon values
        for($j=0;$j<$num_addons;$j++)
        {
            $sql = "UPDATE addons SET inactive='".$addons[$j]."' WHERE `addon_id`='".$addon_id[$j]."'";
            SRPCore()->query($sql);
            //echo $sql."<br><br>";
        }
        header("Location: index.php");
        exit;
    }
    else
    {
        $_SESSION['aderror'] = $error;
        header("Location: admin_install.php");
        exit;
    }
}
    
include('includes/header_alt.php');
?>
<script type="text/JavaScript" src="js/sha512.js"></script> 
<script type="text/JavaScript" src="js/forms.js"></script>
<?php
    if(!empty($_SESSION['aderror']))
    {
?>
<script>
    message('<?php echo $_SESSION["aderror"]; ?>', "error");
</script>
<?php
    }
?>
<div class="menu-bar">
	<h1>Admin Install</h1>
</div>
<div class="table">
    <div class="main main-no-pad">
		<div class="main-scroll">
			<div class="page">
                <form method="post" action="admin_install.php?action=submit" enctype="multipart/form-data">
                <h1>Configure Website</h1>
                    <h2>Login</h2>
                    <input type="text" id="username" name="username" class="form-field-text" value="" />
                    <label class="form-field-name">Username</label>
                    <input type="password" id="password" name="password" class="form-field-text" value="" />
                    <label class="form-field-name">Password</label>
                    <input type="password" id="re_password" name="re_password" class="form-field-text" value="" />
                    <label class="form-field-name">Re-Type Password</label>
                    <h2>Settings</h2>
                    <?php
                    $settings_res = SRPCore()->query("SELECT * FROM configuration WHERE category = 'Admin' ORDER BY configuration_id ASC");
                    while($settings = $settings_res->fetch())
                    {
                        //see if a set_function exists
                        if(!empty($settings['set_function'])) 
                        {
                            $set_function = $settings['set_function'];
                            if(ereg('->', $set_function)) 
                            {
                                $class_method = explode('->', $set_function);
                                if(!is_object(${$class_method[0]})) 
                                {
                                  include("includes/classes/" . $class_method[0] . '.php');
                                  ${$class_method[0]} = new $class_method[0]();
                                }
                                $cfgValue = tep_call_function($class_method[1], $settings['value'], ${$class_method[0]});
                            } else {
                                if(empty($settings['function_options']))
                                {
                                    $val = 'echo '
                                        .$set_function
                                        ."'"
                                        .addslashes($settings['value'])
                                        ."', '"
                                        .$settings['key']
                                        ."');";
                                    eval($val);
                                }
                                else
                                {
                                    eval('echo '.$set_function."'".addslashes($settings['value'])."'".", '".$settings['key']."', '".$settings['function_options']."');");
                                }
                            }
                        } 
                        else 
                        {
                            $cfgValue = '<input type="text" value="'.stripslashes($settings['value']).'" name="'.$settings['key'].'" class="form-field-text saveSetting">';
                            echo $cfgValue;
                        }
?>
                    <label class="form-field-name"><?php if(!empty($settings['description'])){ ?><span><i class="fa fa-question-circle" rel="<?php echo $settings['description']; ?>"></i></span> <?php } echo $settings['title']; ?></label>

<?php
                    }
?>
                    <h2>Add-Ons</h2>
<?php
                    $addons_res = SRPCore()->query("SELECT * FROM addons ORDER BY sort_order");
                    while($addons = $addons_res->fetch())
                    {
?>
                    
                    <input type="radio" value="0" name="addon_<?php echo $addons['addon_id']; ?>" <?php if($addons['inactive']==0){ echo 'checked'; } ?>> On <input type="radio" value="1" name="addon_<?php echo $addons['addon_id']; ?>" <?php if($addons['inactive']==1){ echo 'checked'; } ?>> Off
                    <label for="" class="form-field-name"><?php echo db_output($addons['title']); ?></label>
                    
<?php
                    }
?>
					<br>
                    <!--hidden and aux stuffs -->
                    <input type="submit" name="update" class="form-field-submit" value="Save" onclick="adminformhash(this.form, this.form.password, this.form.re_password);"/>
                </form>
            </div>
		</div><!-- end .main-scroll-->
	</div><!-- end .main -->
</div><!-- end .table -->
<?php
include('includes/footer_alt.php');
?>