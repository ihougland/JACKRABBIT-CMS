<?php
include('includes/application_top.php');
include('includes/session_check.php');
include('includes/header_alt.php');
include('includes/addon_functions.php');
$db = SRPCore();
$page_id = $_GET['page_id'];
?>
<div class="menu-bar">
	<h1>Galleries</h1>
</div>
<div class="table">

	<div class="main main-no-pad">
		<div class="main-scroll">
			<div class="page">
				<table width="100%">
					<tr>
						<th>Title</th>
						<th># Images</th>
						<th>Updated</th>
						<th>Manage</th>
					</tr>
					
<?php
		$res = SRPCore()->query("SELECT * FROM galleries WHERE page_id = '".db_input($_GET['page_id'])."' ORDER BY sort_order ASC");
		while($row = $res->fetch())
		{
			$num_images = get_num_gallery_images($row['gallery_id']);
			
?>
					<tr class="sortable">
						<td><?php echo db_output($row['title']); ?></td>
						<td><?php echo $num_images; ?></td>
						<td><?php echo date('m/d/Y g:i a', strtotime($row['last_updated'])); ?></td>
						<td><a href="#">Images</a><a href="#">Edit</a><a href="#">Delete</a></td>
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