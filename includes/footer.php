			<div class="sidebar-right">
				<div class="sidebar-right-scroll">
					<ul>
					<?php
					//show recently edited pages (within the last 24 hours)
					$yesterday = date('Y-m-d H:i:s', $now - (24 * 60 * 60));
					$recent_edit_res = SRPCore()->query("SELECT page_id, title FROM pages WHERE last_updated > '$yesterday' ORDER BY last_updated DESC LIMIT 2");
					if($recent_edit_res->num_rows()!=0)
					{
					?>
						<li><a href="#"><i class="fa fa-chevron-down"></i> Recently Edited</a>
							<ul>
					<?php
						while($recent_edit = $recent_edit_res->fetch())
						{
					?>
								<li><a href="pages.php?page_id=<?php echo $recent_edit['page_id']; ?>"><i class="fa fa-file"></i> <?php echo db_output($recent_edit['title']); ?></a>
								</li>
					<?php
						}
					?>
							</ul>
						</li>
					<?php
					}
					?>
						<li class="pages"><a href="#"><i class="fa fa-chevron-down"></i> Pages</a>
							<ul class="draggable-parent">
						<?php
						//get top level pages that are sortable
						$parent_res = SRPCore()->query("SELECT page_id, title FROM pages WHERE parent_id = 0 AND sort_order>0 ORDER BY sort_order");
						while($parent = $parent_res->fetch())
						{
						?>
							
								<li class="draggable" id="list_<?php echo $parent['page_id']; ?>">
									<div><a href="pages.php?page_id=<?php echo $parent['page_id']; ?>" <?php if($_GET['page_id']==$parent['page_id']){ ?>id="current-page"<?php } ?>><i class="fa fa-reorder sort-drag"></i><i class="fa fa-file"></i> <?php echo db_output($parent['title']); ?></a>
									</div>
						<?php
							//get sub pages
							$child_res = SRPCore()->query("SELECT page_id, title FROM pages WHERE parent_id = ".$parent['page_id']." AND sort_order>0 ORDER BY sort_order");
							if($child_res->num_rows()!=0)
							{
						?>
									<ul class="dd-list">
						<?php
								while($child = $child_res->fetch())
								{
						?>
										<li class="draggable" id="list_<?php echo $child['page_id']; ?>">
											<div><a href="pages.php?page_id=<?php echo $child['page_id']; ?>" <?php if($_GET['page_id']==$child['page_id']){ ?>id="current-page"<?php } ?>><i class="fa fa-reorder sort-drag"></i><i class="fa fa-file"></i> <?php echo db_output($child['title']); ?></a></div>
						<?php
									//get sub pages
									$grandchild_res = SRPCore()->query("SELECT page_id, title FROM pages WHERE parent_id = ".$child['page_id']." AND sort_order>0 ORDER BY sort_order");
									if($grandchild_res->num_rows()!=0)
									{
								?>
											<ul class="dd-list">
								<?php
										while($grandchild = $grandchild_res->fetch())
										{
								?>
												<li class="draggable" id="list_<?php echo $grandchild['page_id']; ?>">
													<div><a href="pages.php?page_id=<?php echo $grandchild['page_id']; ?>" <?php if($_GET['page_id']==$grandchild['page_id']){ ?>id="current-page"<?php } ?>><i class="fa fa-reorder sort-drag"></i><i class="fa fa-file"></i> <?php echo db_output($grandchild['title']); ?></a></div>
												</li>
								<?php
										}
								?>
											</ul>
								<?php
									}
						?>
										
										</li>
						<?php
								}
						?>
									</ul>
						<?php
							}
						?>
								</li>
						<?php
						}
						?>
							</ul>
						<?php
						
						//Only Allow Add option if set in Configuration
						if(SRPCore()->cfg("ADD_PAGES")=='true')
						{
						?>
							<div class="sidebar-option">
								<hr><a href="#" id="addpage"><i class="fa fa-plus"></i> Add Page</a>
							</div>
						</li>
						<?php
						}
						//get pages that aren't "sortable" (not found in navigation)
						$nonsort_res = SRPCore()->query("SELECT page_id, title FROM pages WHERE sort_order = 0 ORDER BY title");
						if($nonsort_res->num_rows()!=0)
						{
						?>
						<li><a href="#"><i class="fa fa-chevron-down"></i> Misc Pages</a>
						
							<ul>
						<?php
							while($nonsort = $nonsort_res->fetch())
							{
						?>
								<li>
									<div><a href="pages.php?page_id=<?php echo $nonsort['page_id']; ?>" <?php if($_GET['page_id']==$nonsort['page_id']){ ?>id="current-page"<?php } ?>><i class="fa fa-file"></i> <?php echo db_output($nonsort['title']); ?></a>
									</div>
								</li>
						<?php
							}
						?>
							</ul>
						
	                    </li>
	                    <?php
						}
						?>
					</ul>
					<a href="#" class="panel-toggler"><i class="fa fa-chevron-right"></i></a>
				</div><!-- end .sidebar-right-scroll-->
			</div><!-- end .sidebar-right -->
		</div><!-- end .table -->
	</div><!-- end .main-wrap-->
</body>
</html>