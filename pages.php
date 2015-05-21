<?php
include('includes/application_top.php');
include('includes/session_check.php');
include('includes/header.php');
$db = SRPCore();

if(isset($_GET['page_id'])) 
{ 
	$row = SRPCore()->query("SELECT * FROM pages WHERE page_id = ".intval($_GET['page_id']))->fetch();
?>
			<div class="sidebar-left">
				<div class="sidebar-left-scroll">
					<div class="panel">
						<div class="panel-title">
							<a href="#" class="panel-toggle"><i class="fa fa-chevron-circle-down"></i></a>
							<a href="#" class="panel-title-tab panel-title-active">Page Details</a>
						</div>
						<div class="panel-contents">
							Est. Read Time: <span id="read-time"></span>
							<br> Words: <span id="words"></span>
							<br> Characters: <span id="characters"></span>
							<br> Last Edited: <span id="edited-date"><?php echo ($row['last_updated']!='0000-00-00 00:00:00')?date('m/d/Y g:i A', strtotime($row['last_updated'])):'N/A'; ?></span>
						</div>
					</div><!-- end .panel -->

					<div class="panel">
						<div class="panel-title">
							<a href="#" class="panel-toggle"><i class="fa fa-chevron-circle-down"></i></a>
							<a href="#" class="panel-title-tab panel-title-active">Text</a>
							<a href="#" class="panel-title-tab">Img</a>
							<a href="#" class="panel-title-tab">Table</a>
							<a href="#" class="panel-title-tab">Link</a>
							<a href="#" class="panel-title-tab">Embed</a>
						</div>
						<div class="panel-contents">
							<div class="panel-group editor-toolbar">
								<ul>
									<li><a href="#" title="Bold" rel="B" onclick="document.execCommand ('bold', false, null);"><i class="fa fa-bold"></i></a>
									</li>
									<li><a href="#" title="Italic" rel="I" onclick="document.execCommand ('italic', false, null);"><i class="fa fa-italic"></i></a>
									</li>
									<li><a href="#" title="Underline" rel="U" onclick="document.execCommand('underline', false, null);"><i class="fa fa-underline"></i></a>
									</li>
									<li><a href="#" title="Strikethrough" rel="Strike" onclick="document.execCommand('strikethrough', false, null);"><i class="fa fa-strikethrough"></i></a>
									</li>
									<li><a href="#" title="Block Quote" rel="Blockquote" onclick="wrapElement('blockquote');"><i class="fa fa-quote-left"></i></a>
									</li>

									<li><a href="#" title="Large Heading" rel="h1" onclick="wrapElement('h1');"><b style="font-size:.8em">H1</b></a>
									</li>
									<li><a href="#" title="Medium Heading" rel="h2" onclick="wrapElement('h2');"><b style="font-size:.8em">H2</b></a>
									</li>
									<li><a href="#" title="Small Heading" rel="h3" onclick="wrapElement('h3');"><b style="font-size:.8em">H3</b></a>
									</li>

									<li><a href="#" title="Subscript" rel="Sub" onclick="document.execCommand('subscript', false, null);"><i class="fa fa-subscript"></i></a>
									</li>
									<li><a href="#" title="Superscript" rel="Sup" onclick="document.execCommand('superscript', false, null);"><i class="fa fa-superscript"></i></a>
									</li>

									<li><a href="#" title="Align Left" rel="Left" onclick="document.execCommand('justifyLeft', false, null);"><i class="fa fa-align-left"></i></a>
									</li>
									<li><a href="#" title="Align Center" rel="Center" onclick="document.execCommand('justifyCenter', false, null);"><i class="fa fa-align-center"></i></a>
									</li>
									<li><a href="#" title="Align Right" rel="Right" onclick="document.execCommand('justifyRight', false, null);"><i class="fa fa-align-right"></i></a>
									</li>
									<li><a href="#" title="Jusitfy" rel="Justify" onclick="document.execCommand('justifyFull', false, null);"><i class="fa fa-align-justify"></i></a>
									</li>

									<li><a href="#" title="Bulleted List" rel="OL" onclick="document.execCommand('insertOrderedList', false, null); rangeMouseup();"><i class="fa fa-list-ol"></i></a>
									</li>
									<li><a href="#" title="Numbered List" rel="UL" onclick="document.execCommand('insertUnorderedList', false, null); rangeMouseup();"><i class="fa fa-list-ul"></i></a>
									</li>
									<li><a href="#" title="Add Link" onclick="createLink();"><i class="fa fa-link"></i></a>
									</li>
									<li><a href="#" title="Remove Link" onclick="document.execCommand('unlink', false, null);"><i class="fa fa-unlink"></i></a>
									</li>

									<li><a href="#" title="Horizontal Line" rel="hr" onclick="document.execCommand('insertHorizontalRule', false, null);"><i class="fa fa-minus"></i></a>
									</li>

									<li><a href="#" title="Clear Formatting" id="removeHeadings"><i class="fa fa-times-circle"></i></a>
									</li>
								</ul>
							</div>

							<div class="panel-group editor-toolbar">
								<ul>
									<li><a href="#" title="Wrap & Align Left" class="fl"><i class="icon-float-left"></i></a></li>
									<li><a href="#" title="Wrap & Align Right" class="fr"><i class="icon-float-right"></i></a></li>
									<li><a href="#" title="Center Image" class="fc"><i class="icon-center"></i></a></li>
									<li><a href="#" title="Inline With Text" class="fn"><i class="icon-inline"></i></a></li>
									<li><a href="#" title="Delete Image" class="fd"><i class="fa fa-trash"></i></a></li>
								</ul>
								<hr>
								<label id="img-size-label"><span></span> <i class="fa fa-arrows-h"></i> Max Size <div id="original-size"></div></label> <br><input type="text" placeholder="ex: 300 or 50%" id="photo-width"><br>
								<label><i class="fa fa-info-circle"></i> Photo Description</label> <br><input type="text" placeholder="Photo Description" id="photo-desc"><br>
								<label><i class="fa fa-link"></i> Optional Link</label> <br><input type="text" placeholder="http://www.example.com" id="photo-url">
							</div>
						
							<div class="panel-group editor-toolbar">
								<ul>
									<li><a href="#" title="Add Row Above" class="add-row-above"><i class="icon-add-row-before"></i></a></li>
									<li><a href="#" title="Add Row Below" class="add-row-below"><i class="icon-add-row-after"></i></a></li>

									<li><a href="#" title="Add Column Before" class="add-column-before"><i class="icon-add-column-before"></i></a></li>

									<li><a href="#" title="Add Column After" class="add-column-after"><i class="icon-add-column-after"></i></a></li>
									<li><a href="#" title="Delete Entire Table" class="delete-table"><i class="fa fa-trash"></i></a></li>
									<li><a href="#" title="Delete Row" class="delete-row"><i class="icon-delete-row"></i></a></li>
									<li><a href="#" title="Delete Column" class="delete-column"><i class="icon-delete-column"></i></a></li>                        		
									<li><a href="#" title="Add Table Heading" class="add-header"><i class="icon-add-heading"></i></a></li>
									<li><a href="#" title="Delete Table Heading" class="delete-header"><i class="icon-remove-heading"></i></a></li>
									
								</ul>
							</div>

							<div class="panel-group editor-toolbar">
								<label id="link-label"><span></span> <i class="fa fa-link"></i> Link Location</label> <br><input type="text" id="link-location"><br>
							</div>

							<div class="panel-group editor-toolbar">
								<ul>
									<li><a href="#" title="Delete Embed" class="delete-embed"><i class="fa fa-trash"></i></a></li>								
								</ul>
								<hr>
								<label><span></span> <i class="fa fa-arrows-h"></i> Max Size</label> <br><input type="text" id="embed-size"><br>
								<label><i class="fa fa-film"></i> Aspect Ratio</label><br>
								<div class="radio-wrap">
									<input type="radio" id="aspect-4-3" name="aspect"/>
									<label id="aspect-4-3-label" for="aspect-4-3"><span></span><br>4:3</label>
								</div>
								<div class="radio-wrap">
									<input type="radio" id="aspect-16-9" name="aspect"/>
									<label id="aspect-16-9-label" for="aspect-16-9"><span></span><br>16:9</label>
								</div>
								<div class="radio-wrap">
									<input type="radio" id="aspect-21-9" name="aspect"/>
									<label id="aspect-21-9-label" for="aspect-21-9"><span></span><br>21:9</label>
								</div>
							</div>
						</div><!-- end .panel-contents -->
					</div><!-- end .panel -->

					<div class="panel">
						<div class="panel-title">
							<a href="#" class="panel-toggle"><i class="fa fa-chevron-circle-down"></i></a>
							<a href="#" class="panel-title-tab panel-title-active">SEO</a>
						</div>
						<div class="panel-contents">
							<div class="panel-group">
								<input type="hidden" id="page_id" value="<?php echo $_GET['page_id']; ?>">
								<div>
									<label><span class="warn"></span>Page Name <span class="seo-length"></span></label>
									<br>
									<input id="pageTitle" class="seo-input" type="text" placeholder="Page name on website" maxlength="55" value="<?php echo db_output($row['title']); ?>"> 
								</div>
								<div>
									<label><span class="warn"></span>Page Browser Title <span class="seo-length"></span></label>
									<br>
									<input id="metaTitle" class="seo-input" type="text" placeholder="Text for browser's title" maxlength="55" value="<?php echo db_output($row['meta_title']); ?>">
									<br> 
								</div>
								<div>
									<label><span class="warn"></span>Page Description <span class="seo-length"></span></label>
									<br>
									<textarea id="metaDescription" class="seo-input" placeholder="A quick description of this page." maxlength="160"><?php echo db_output($row['meta_description']); ?></textarea>
								</div>
							</div>
						</div><!-- end .panel-contents-->
					</div><!-- end .panel -->
				</div><!-- end .sidebar-left-scroll-->
			</div><!-- end .sidebar-left -->

			<div class="main">
				<textarea name="" id="stripformat" cols="30" rows="10"></textarea>
				<div class="main-scroll">
					<div class="editor">
						<div class="editor-title">
							<!--<a href="settings.html" class="title-btn-right in-iframe"><i class="fa fa-user"></i> Manage Staff</a>-->
							<a href="#" class="title-btn-right special-btn"><i class="fa fa-plus"></i> Add Special Content</a>
							<div class="view-btns">
								<a href="#" class="on">EDIT</a><!--
								--><a href="#"  id="htmlMode">HTML</a><!--
								--><a href="http://www.woot.co.uk/" class="in-iframe">LIVE</a>
							</div>
							<h1><?php echo db_output($row['title']); ?></h1>
						</div>

						<div class="add-special-content">
							<div><!-- for padding -->
							<h1>Add Special Content</h1>
							You may choose one special content element to appear below the regular text of this page.<br>
							<div class="special-list">
							<a href="#">
								<img src="images/gallery.svg" alt="">
								Gallery
							</a>
							<a href="#">
								<img src="images/gallery-multi.svg" alt="">
								Multi Galleries
							</a>
							<a href="#">
								<img src="images/staff.svg" alt="">
								Staff Members
							</a>
							<a href="#">
								<img src="images/faq.svg" alt="">
								FAQs
							</a>
							<a href="#">
								<img src="images/products.svg" alt="">
								Products
							</a>
							<a href="#">
								<img src="images/logos.svg" alt="">
								Logos
							</a>
							</div>
							</div>
						</div>

						<div id="pageText" class="editor-text" contenteditable="true">
							<?php echo db_output($row['text']); ?>
						</div><!-- end .editor-text -->
					</div><!-- end .editor -->
					<div class="html-mode">
						<textarea name="htmlTextarea" id="htmlTextarea"></textarea>
					</div>
				</div><!-- end .main-scroll-->
			</div><!-- end .main -->
<?php
}
else
{
?>
<div class="main">
    <div class="main-scroll">
        <div class="greeter">
            Select a page to get started
        </div>
    </div>
    <!-- end .main-scroll-->
</div>
<!-- end .main -->
<?php
}
include('includes/footer.php');
?>


		

			