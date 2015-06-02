<?php
	if(isset($_GET['page_id']))
	{
		$headrow = SRPCore()->query("SELECT * FROM pages WHERE page_id = ".intval($_GET['page_id']))->fetch();
	}
?>
<!doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Jackrabbit CMS</title>
	<link rel="shortcut icon" href="favicon.ico"> 
	<link rel="stylesheet" href="css/stylesheet.css">
	<link rel="stylesheet" href="css/font-awesome.min.css">
	<link rel="stylesheet" href="css/fontello.css">

	<link rel="stylesheet" href="lib/codemirror.css">
	<link rel="stylesheet" href="css/syntax.css">
	<script src="js/codemirror-compressed.js"></script>
	<script src="mode/htmlmixed/htmlmixed.js"></script>
	<script src="mode/javascript/javascript.js"></script>
	<script src="mode/css/css.js"></script>
	<script src="mode/xml/xml.js"></script>
	<script src="addon/edit/matchbrackets.js"></script>

	<script type="text/javascript" src="js/jquery-1.8.3.min.js"></script>
	<script type="text/javascript" src="js/jquery-ui.min.js"></script>
	<script type="text/javascript" src="js/jquery.easing.1.3.js"></script>
	<script type="text/javascript" src="js/jquery-cookie.js"></script>
	<script type="text/javascript" src="js/jquery.htmlClean.min.js"></script>
	<script type="text/javascript" src="js/gui.js"></script>
	<script type="text/javascript" src="js/editor.js"></script>	
	<script type="text/javascript" src="js/jquery.mjs.nestedSortable.js"></script>

</head>
<?php
    //pop off the name of the file so we can show the active page
    $nav_page = array_pop(explode('/', $_SERVER['SCRIPT_NAME']));
?>
<body>
	<div class="main-wrap">
		<!--[if lt IE 10]>
		<div class="dinosaur"><b>Hey there,</b> your browser is out of date and might not work well here. <a href="http://www.google.com/chrome">Upgrade?</a></div>
		<![endif]-->
		<div class="menu-bar">
			<div class="account"><i class="fa fa-user"></i> <?php echo $_SESSION['username']; ?> | <a href="includes/logout.php"><i class="fa fa-sign-out"></i> Log Out</a></div>
			<ul>
			<?php
			if($nav_page=="pages.php")
			{
			?>
				<li><a href="#">File</a>
					<ul>
						<?php if($headrow['type']!=0){ ?><li><a href="#" onClick="<?php if($headrow['type']==3){ ?>savePageUpload();<?php } else { ?>savePage();<?php } ?>">Save</a></li><?php } ?>
						<li><a href="pages.php">Close Page</a></li>
						<li><a href="#" onClick="deletePage();">Delete Page</a></li>
					</ul>
				</li>
			<?php
				if($headrow['type']==1)
				{
			?>
				<li><a href="#">Insert</a>
					<ul>
						<li><a href="#" onclick="insertImage();">Image</a></li>
						<li><a href="#">Link to Document</a></li>
						<li><a href="#" onclick="createnewLink();">Link to Webpage</a></li>
						<li class="editor-drop"><a href="#" title="Insert Table" id="tableInsert">Table</a>
							<ul>
								<li>
									<div class="table-dimension">
										<div class="row-add">
											<a href="#" class="cell-add"></a>
											<a href="#" class="cell-add"></a>
											<a href="#" class="cell-add"></a>
											<a href="#" class="cell-add"></a>
										</div>
										<div class="row-add">
											<a href="#" class="cell-add"></a>
											<a href="#" class="cell-add"></a>
											<a href="#" class="cell-add"></a>
											<a href="#" class="cell-add"></a>
										</div>
										<div class="row-add">
											<a href="#" class="cell-add"></a>
											<a href="#" class="cell-add"></a>
											<a href="#" class="cell-add"></a>
											<a href="#" class="cell-add"></a>
										</div>
										<div class="row-add">
											<a href="#" class="cell-add"></a>
											<a href="#" class="cell-add"></a>
											<a href="#" class="cell-add"></a>
											<a href="#" class="cell-add"></a>
										</div>
									</div>

									<div class="table-dimension-result">0 x 0</div>
								</li>
							</ul>
						</li>
						<li><a href="#" onclick="createEmbed();">Embedded Video</a></li>
						<li class="editor-drop"><a href="#">Form Token</a>
							<ul>
								<li><a href="#" class="token">[Token1]</a></li>
								<li><a href="#" class="token">[Token2]</a></li>
							</ul>
						</li>
					</ul>
				</li>
			<?php
				}
			}
			?>
				<li><a href="#">Manage</a>
					<ul>
						<li><a href="settings.php" class="in-iframe">Website Settings</a>
						</li>
					</ul>
				</li>
				
				<li><a href="#">Help</a>
					<ul>
						<li><a href="help.html" class="in-iframe">Help Docs</a></li>
						<li><a href="bug.html" class="in-iframe">Report A Bug</a></li>
						<li><a href="appinfo.html" class="in-iframe">App Info</a></li>
					</ul>
				</li>
			</ul>
		</div><!-- end .menu-bar -->
		<div class="table">