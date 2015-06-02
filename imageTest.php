<?php
include('includes/application_top.php');
include('includes/session_check.php');
include('includes/header_alt.php');
$db = SRPCore();
?>
<script>
	$(document).ready(function(){
		//Program a custom submit function for the form
	
		$("form#data").submit(function(event){
	 
		  //disable the default form submission
		  event.preventDefault();
		 
		  //grab all form data  
		  var formData = new FormData($("form#data")[0]);
		 
		  $.ajax({
		    url: 'upload.php',
		    type: 'POST',
		    data: formData,
		    dataType: "json",
		    cache: false,
		    contentType: false,
		    processData: false,
		    success: function (returndata) {
		      $("#result").html(returndata.new_file);
		      console.log(returndata);
		    },
		    error: function() {
		        alert("Error occured");
		    }
		  });
		 
		});
	});
</script>
<div class="menu-bar">
	<h1>Image Add</h1>
</div>
<div class="table">

	<div class="main main-no-pad">
		<div class="main-scroll">
			<div class="page">

				<h1>Image Add</h1>
				<form id="data">
				  <input type="hidden" name="id" value="123">
				  Description: <input type="text" name="description" value=""><br />
				  Image: <input name="image" type="file" /><br />
				  <input type="submit" name="submit" value="Submit">
				</form>
				<div id="result"></div>
			</div>
		</div><!-- end .main-scroll-->
	</div><!-- end .main -->
</div><!-- end .table -->
<?php
include('includes/footer_alt.php');
?>