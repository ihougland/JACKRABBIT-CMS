<?php
//require the db and other essentials
require_once('includes/application_top.php');
include('includes/session_check.php');
require_once('includes/classes/smart_resize.php');

//check for post
if(isset($_GET['size']))
{
    //check for values
    if(intval($_GET['x2']))
    if(intval($_GET['y2']))
    if(intval($_GET['w']))
    if(intval($_GET['h']))
    {
        $image_info = getimagesize($_GET['in_img']);
        if($image_info['mime'] == 'image/jpeg')
            $current_image = imagecreatefromjpeg($_GET['in_img']);
        else if($image_info['mime'] == 'image/png')
            $current_image = imagecreatefrompng($_GET['in_img']);

        // The x and y coordinates on the original image where we
        // will begin cropping the image
        $left = intval($_GET['x']);
        $top = intval($_GET['y']);
         
        // This will be the final size of the image (e.g. how many pixels
        // left and down we will be going)
        $crop_width = intval($_GET['x2'] - $_GET['x']);
        $crop_height = intval($_GET['y2'] - $_GET['y']);

         
        // Resample the image
        $canvas = imagecreatetruecolor($crop_width, $crop_height);
        imagecopy($canvas, $current_image, 0, 0, $left, $top, $crop_width, $crop_height);
        
        if($image_info['mime'] == 'image/jpeg')
            imagejpeg($canvas, $_GET['out_img'], 90);
        else if($image_info['mime'] == 'image/png')
            imagepng($canvas, $_GET['out_img']);
        imagedestroy($canvas);
        imagedestroy($current_image);

        //we now have the crop of the image
        //we need to resize
        smart_resize_image($_GET['out_img'],
                           $_GET['out_img'],
                           $_GET['w'],
                           $_GET['h']
                          );
        header('Location: '.$_GET['landing']);
        die;
    }
}   

//and the CMS <head>
require_once('includes/header_alt.php');
?>
<script src="js/jquery.Jcrop.min.js"></script>
<link rel="stylesheet" href="css/jquery.Jcrop.css" type="text/css" />
<style>
/* CSS Specific to the Crop tool */
#preview-pane .preview-container {
  width: <?php echo $_GET['w']; ?>px;
  height: <?php echo $_GET['h']; ?>px;
  overflow: hidden;
}

</style>
<script type="text/javascript">
  //JS specific to the crop tool
  jQuery(function($){
    var boundx, boundy;
      // Invoke Jcrop in typical fashion
      $('#target').Jcrop({
        onChange:   showCoords,
        onSelect:   showCoords,
        onRelease:  clearCoords,
        minSize: [ <?php echo $_GET['w']; ?>, <?php echo $_GET['h']; ?> ],
        aspectRatio: <?php echo $_GET['w']; ?>/<?php echo $_GET['h']; ?>
      },function(){
        jcrop_api = this;
        var bounds = this.getBounds();
        boundx = bounds[0];
        boundy = bounds[1];
        jcrop_api.animateTo([0,0,<?php echo $_GET['w']; ?>,<?php echo $_GET['h']; ?>]);
      });

    function jCropPreview(c)
    {
      if (parseInt(c.w) > 0)
      {
        var rx = <?php echo $_GET['w']; ?> / c.w;
        var ry = <?php echo $_GET['h']; ?> / c.h;

        $('#preview-pane img').css({
          width: Math.round(rx * boundx) + 'px',
          height: Math.round(ry * boundy) + 'px',
          marginLeft: '-' + Math.round(rx * c.x) + 'px',
          marginTop: '-' + Math.round(ry * c.y) + 'px'
        });
      }
    };

    function showCoords(c)
    {
        $('#img_x').val(c.x);
        $('#img_y').val(c.y);
        $('#img_x2').val(c.x2);
        $('#img_y2').val(c.y2);
        //jCropPreview(c);
    };

    function clearCoords()
    {
        $('.coords').val('');
    };
  });

</script>
<div class="menu-bar">
    <h1>Crop Image</h1>
</div>
<div class="table">
    <div class="main main-no-pad">
        <div class="main-scroll">
            <div class="page">
                <form method="get" enctype="multipart/form-data">
                <input type="submit" name="size" class="form-field-submit" value="Crop Image"/>
                    <img src="<?php echo $_GET['in_img']; ?>" id="target" />
                    <!--<div id="preview-pane">
                        <div class="preview-container">
                            <img src="<?php echo $_GET['in_img']; ?>" class="jcrop-preview" alt="Preview" />
                        </div>
                    </div>-->
                    <input type="hidden" name="in_img" value="<?php echo $_GET['in_img']; ?>" />
                    <input type="hidden" name="out_img" value="<?php echo $_GET['out_img']; ?>" />
                    <input type="hidden" class="coords" id="img_x" name="x" value="" />
                    <input type="hidden" class="coords" id="img_y" name="y" value="" />
                    <input type="hidden" class="coords" id="img_x2" name="x2" value="" />
                    <input type="hidden" class="coords" id="img_y2" name="y2" value="" />
                    <input type="hidden" id="img_w" name="w" value="<?php echo $_GET['w']; ?>" />
                    <input type="hidden" id="img_h" name="h" value="<?php echo $_GET['h']; ?>" />
                    <input type="hidden" name="landing" value="<?php echo $_GET['landing']; ?>" />
                
                </form>
            </div>
        </div><!-- end .main-scroll-->
    </div><!-- end .main -->
</div><!-- end .table -->

<?php
//end the head tags
require_once('includes/footer_alt.php');
?>