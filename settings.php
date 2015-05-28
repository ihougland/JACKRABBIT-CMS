<?php
include('includes/application_top.php');
include('includes/session_check.php');
include('includes/header_alt.php');
$db = SRPCore();
?>
<div class="menu-bar">
	<h1>Settings</h1>
</div>
<div class="table">
	<div class="sidebar-left">
		<div class="sidebar-left-scroll">
			<h1><i class="fa fa-s"></i> Setting Categories</h1>
			<ul class="side-options">
				<li>
<?php
//get distinct category names (only those with active & visible settings)
$set_category_res = SRPCore()->query("SELECT DISTINCT category FROM configuration WHERE hide = 0 ORDER BY category ASC");
while($set_category = $set_category_res->fetch_item())
{
	//make sure at least 1 setting in this category is active
	$num_active_res = SRPCore()->query("SELECT COUNT(*) as total FROM configuration WHERE category='".$set_category."' AND inactive = 0 AND hide = 0");
	$num_active = $num_active_res->fetch_item();
	if($num_active>0)
	{
?>	
					<a href="settings.php?category=<?php echo $set_category; ?>" <?php if($_GET['category']==$set_category){ ?>id="current-setting"<?php } ?>><?php echo $set_category; ?></a>
<?php
	}
}
?>
				</li>
			</ul>
		</div>
	</div>

	<div class="main main-no-pad">
		<div class="main-scroll">
			<div class="page">
<?php
	if(!empty($_GET['category']))
	{
?>
				<h1><?php echo $_GET['category']; ?></h1>
<?php
		$settings_res = SRPCore()->query("SELECT * FROM configuration WHERE category = '".db_input($_GET['category'])."' AND inactive=0 AND hide=0 ORDER BY configuration_id ASC");
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
	}
?>
			</div>
		</div><!-- end .main-scroll-->
	</div><!-- end .main -->
</div><!-- end .table -->
<?php
include('includes/footer_alt.php');
?>