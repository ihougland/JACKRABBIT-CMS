<?php
//--------------------------------------------------------------------------------------------------------------------//
// SRPForm Class:
// A singleton wrapper for the form generation
//--------------------------------------------------------------------------------------------------------------------//
require './class.captcha_x.php';
class SRPForm 
{ 
    private static $pInstance;
    
    private $errors = null;

    //the constructor
    private function __construct()
    {
        ;
    }
    
    //no cloning
    private function __clone() { ; }
    

    /*
     * 
     * name: replace_form_token
     * 
     * Parses the string passed to it and attempts to draw the form associated with any tokens encountered. It will either
     * draw the form outright, or try to parse it first.
     * 
     * @param &$str
     * @return string
     */
    

    public function replace_form_token($str)
    {
        global $db;
        $matches = array();
        $fid = 0;
        
        //if we find one or more of these
        if(preg_match('/\[AUTOFORM_[0-9]+\]/', $str, $matches) >= 1)
        {
            //process the form for each one
            foreach($matches as $match)
            {
                //get the form that we can process
                $fid = (int)str_replace(array('[AUTOFORM_', ']'), '', $match);
                
                //if we don't have a form being submitted, just draw the form
                if(!isset($_POST['form_identifier']))
                {
                    $str = str_replace($match, $this->draw_form($fid), $str);
                }
                else
                {
                    //get the form identifier for this form from the DB
                    $form_identifier_res = $db->query("SELECT MD5(name) FROM forms WHERE form_id=$fid");
                    $form_identifier = $db->numRows($form_identifier_res)?$db->fetchItem($form_identifier_res):'';
                    
                    //if they are not the same, then we just draw it like normal
                    if($form_identifier != $_POST['form_identifier'])
                    {
                        $str = str_replace($match, $this->draw_form($fid), $str);
                    }
                    //otherwise we have to process the form
                    else
                    {
                        if($this->parse_form($fid))
                            $str = str_replace($match, '<h1>Thanks!</h1><p>Your submission has been processed.</p>', $str);
                        else
                            $str = str_replace($match, $this->draw_form($fid), $str);
                    }
                }
            }
        }
        return $str;
    }
    
    /*
     * 
     * name: parse_form
     * 
     * Parses $_POST, looking for the items in the form associated with $identifier. It notes any errors in an array and
     * tries to send an email if successful.
     * 
     * @param $identifier
     * @return
     */
    
    public function parse_form($identifier)
    {
        //still need this for now
        global $db;
        
        //we get it by ID
        if(is_int($identifier))
        {
            $form_res = $db->query("SELECT * FROM forms WHERE form_id=$identifier");
        }
        else
            return '';
        
        //we either have something or we don't
        if($db->numRows($form_res) != 1)
            return '';
        
        //ready the form data
        $form = $db->fetch($form_res);
        
        //clean the errors
        $this->errors = null;
        
        //the email text
        $email = '';
        
        $field_res = $db->query("SELECT * FROM fields WHERE form_id={$form['form_id']} ORDER BY sort_order");
        
        while($field = $db->fetch($field_res))
        {
            //get the escaped data
            $data = $_POST['f_'.md5($field['field_id'].$form['secret'])];
            if(is_array($data))
                $data = implode(', ', $data);
            //sanitize html bits
            $data = htmlentities($data);
            
            //do headers
            if($field['field_type'] == 'Header')
            {
                $email .= "\n{$field['name']}\n\n";
                //next
                continue;
            }
            
            //if an opt in is required
            if($field['field_type'] == 'Opt-in' && $field['required'])
            {
                if(isset($_POST['f_'.md5($field['field_id'].$form['secret'])]))
                    $email .= "    {$field['name']}: ".stripslashes($data)."\n";    
                else
                    $this->errors[$field['field_id']] = "You must accept the terms of this form in order to be considered.";
                continue;
            }
            
            //if its required, and they don't have it, mark an error
            if($data == '' && $field['required'])
            {
                $this->errors[$field['field_id']] = "You must enter something for {$field['title']}.";
            }
            //if it has an error type, and they entered something then check it (but only if we didn't already complain)
            else if($field['logical_type'] != '' && $data != '')
            {
                //check the name type
                if($field['logical_type'] == 'Name')
                {
                    //strip numbers and brackets
                    $data = preg_replace('`[0-9<>]*`', '', $data);
                    if(strlen($data) < 2 || strlen($data) > 30)
                    {
                        $this->errors[$field['field_id']] = "{$field['title']} should be between 2 and 30 characters.";
                    }
                }
                //check the phone number
                else if($field['logical_type'] == 'Phone')
                {
                    //strip non-numerical stuff
                    $data = preg_replace('`[a-zA-Z\-()+ ]*`', '', $data);
                    if(strlen($data) < 7 || strlen($data) > 16)
                    {
                        $this->errors[$field['field_id']] = "{$field['title']} should be a valid phone number.";
                    }
                }
                //check email
                else if($field['logical_type'] == 'Email')
                {
                    $data = str_replace(array("\n", "\r", ';', ','), '', $data);
                }
            }
            //this prevents us from running stripslashes and the string parser on who knows how many strings
            //esp where its not necessary
            if($this->errors == null)
                $email .= "    {$field['name']}: ".stripslashes($data)."\n";
        }
        //get captcha text
        $captcha_text = $_POST['captcha_text'];
        $captcha_object = &new captcha_x();
        if (!$captcha_object->validate($captcha_text)) 
        {
            $this->errors['captcha_text'] .= "Letters in the picture do not match the text entered.";
        }

        //if we don't have any errors, then send the email and return true
        if($this->errors == null)
        {
            $f_emails = explode(';', $form['email']);
            foreach($f_emails as $to)
                mail($to, $form['name'], $email, "From: $to\n\r");
            return true;
        }
    }
    
    /*
     * 
     * name: draw_form
     * 
     * Draws the form with identifier
     * 
     * @param $identifier
     * @return
     */
    public function draw_form($identifier)
    {
        //for now we still need this
        global $db;
        
        //we get it by ID
        if(is_int($identifier))
        {
            $form_res = $db->query("SELECT * FROM forms WHERE form_id=$identifier");
        }
        else
            return '';
        
        //we either have something or we don't
        if($db->numRows($form_res) != 1)
            return '';
        
        //ready the form data
        $form = $db->fetch($form_res);
        
        //we have a form of some type, so lets return the string
        ob_start();
        ?>
        <form method="post">
        <div class="form-table">
            <h2><?php echo $form['name']; ?></h2>
        <?php
        if($this->errors != null)
        { ?>
            <div class="form-errors">
                <?php
                    foreach($this->errors as $error)
                    {
                        echo $error.'<br />';
                    }
                ?>
            </div>
            <br>
        <?php
        }
        //now lets fetch the items and display the form
        $field_res = $db->query("SELECT * FROM fields WHERE form_id={$form['form_id']} ORDER BY sort_order");
        
        //display each field
        while($field = $db->fetch($field_res))
        {   
            //handle textarea
            if($field['field_type'] == 'Textarea')
            {    
        ?>
                <label class="form-field-textbox-name"><?php echo $field['title']; if($field['required']) echo '<i class="form-field-required">*</i>'; ?></label><br>
                    <textarea name="f_<?php echo md5($field['field_id'].$form['secret']); ?>" class="form-field-textarea" <?php if($field['required']){ echo "required"; } ?>><?php echo stripslashes($_POST['f_'.md5($field['field_id'].$form['secret'])]); ?></textarea>
                
            <br>
      <?php }
            //handle header type
            else if($field['field_type'] == 'Header')
            {
                       ?>
            
            <div class="form-field-heading"><?php echo $field['title']; ?></div>
            <?php 
            }
            //handle Opt-ins
            else if($field['field_type'] == 'Opt-in')
            {
                       ?>
            
                <?php if($field['required']) echo '<i class="form-field-required">*</i>'; ?><input type="checkbox" name="f_<?php echo md5($field['field_id'].$form['secret']); ?>" class="form-field-checkbox" value="Agreed" <?php
                if(isset($_POST['f_'.md5($field['field_id'].$form['secret'])]))
                {
                    if($_POST['f_'.md5($field['field_id'].$form['secret'])] == 'Agreed')
                        echo 'checked="checked" '; 
                }
                ?>/><?php echo $field['title']; ?>
            <br>
            <?php 
            }
            //handle checkboxes
            else if($field['field_type'] == 'Checkbox')
            {    
        ?>
                <label class="form-field-name"><?php echo $field['title']; ?></label><br>
                <?php
                //get the checkboxes
                $option_res = $db->query("SELECT * FROM field_options WHERE form_id={$form['form_id']} AND field_id={$field['field_id']} ORDER BY sort_order");
                while($option = $db->fetch($option_res))
                { ?>
                    <span class="form-field-checkbox-wrap">
                    <input id="o_<?php echo md5($option['text']); ?>" type="checkbox" name="f_<?php echo md5($field['field_id'].$form['secret']); ?>[]" class="form-field-checkbox" value="<?php echo $option['value']; ?>" <?php
                        //if it is set select the right one!
                        if(isset($_POST['f_'.md5($field['field_id'].$form['secret'])]))
                        {
                            if(in_array($option['value'], $_POST['f_'.md5($field['field_id'].$form['secret'])]))
                                echo 'checked="checked" '; 
                        }
                        else if($option['default'])
                            echo 'checked="checked" ';                    
                    ?>/><label for="o_<?php echo md5($option['text']); ?>"><?php echo $option['text']; ?></label>
                    </span>
          <?php }
                ?>
            <br>
      <?php }
            //handle radio
            else if($field['field_type'] == 'Radio')
            {    
        ?>
                <label class="form-field-name"><?php echo $field['title']; ?></label><br>
                <?php
                //its a radio, so lets get the options
                $option_res = $db->query("SELECT * FROM field_options WHERE form_id={$form['form_id']} AND field_id={$field['field_id']} ORDER BY sort_order");
                while($option = $db->fetch($option_res))
                { ?>
                    <span class="form-field-radio-wrap">
                    <input id="o_<?php echo md5($option['text']); ?>" type="radio" name="f_<?php echo md5($field['field_id'].$form['secret']); ?>" class="form-field-radio" value="<?php echo $option['value']; ?>" <?php
                        //if it is set select the right one!
                        if(isset($_POST['f_'.md5($field['field_id'].$form['secret'])]))
                        {
                            if($_POST['f_'.md5($field['field_id'].$form['secret'])] == $option['value'])
                                echo 'checked="checked" '; 
                        }
                        else if($option['default'])
                            echo 'checked="checked" ';
                    
                    ?>/><label for="o_<?php echo md5($option['text']); ?>"><?php echo $option['text']; ?></label>
                    </span>
          <?php }
                ?>
            <br>
      <?php }
            //handle Select
            else if($field['field_type'] == 'Select')
            {    
        ?>
                <label class="form-field-name"><?php echo $field['title']; ?></label><br>
                <select class="form-field-select" name="f_<?php echo md5($field['field_id'].$form['secret']); ?>"><?php
                //its a select, so lets get the options
                $option_res = $db->query("SELECT * FROM field_options WHERE form_id={$form['form_id']} AND field_id={$field['field_id']} ORDER BY sort_order");
                while($option = $db->fetch($option_res))
                { ?>
                    <option value="<?php echo $option['value']; ?>" <?php
                        //if it is set select the right one!
                        if(isset($_POST['f_'.md5($field['field_id'].$form['secret'])]))
                        {
                            if($_POST['f_'.md5($field['field_id'].$form['secret'])] == $option['value'])
                                echo 'selected="selected" '; 
                        }
                        else if($option['default'])
                            echo 'selected="selected" ';
                    ?>><?php echo $option['text']; ?></option>
          <?php }
                ?></select>
            <br>
      <?php }
            //otherwise, do a text style field
            else
            {
                if($field['logical_type']=="Email")
                {
                    $field['field_type'] = "email";
                }
        ?>
                <label class="form-field-name"><?php echo $field['title']; ?></label><br>
                <input type="<?php echo $field['field_type']; ?>" name="f_<?php echo md5($field['field_id'].$form['secret']); ?>" class="form-field-text<?php if(isset($this->errors[$field['field_id']])) echo ' form-field-error'; ?>" value="<?php echo stripslashes($_POST['f_'.md5($field['field_id'].$form['secret'])]); ?>" <?php if($field['required']){ echo "required"; } ?>/>
                <?php if($field['required']) echo '<i class="form-field-required">*</i>'; ?>
            <br>
      <?php }
        }
        ?>
        <br><img name="captcha" onclick="this.src='server.php?'+Math.random();" src="server.php" alt="CAPTCHA image" width="150" height="35">
        <br>
        <label class="form-field-name">Type the letters from the image.</label><br>
        <input id="captcha_text" name="captcha_text" class="form-field-text required" type="text" required/> <i class="form-field-required">*</i>
        <br>
        <input type="hidden" name="form_identifier" value="<?php echo md5($form['name']); ?>" />
        <input class="form-field-submit" type="submit" />
        <br>
        </div>
        </form>
        <?php
        return ob_get_clean();
    }

    //generates an instance or returns the instance already created
    public static function getInstance() 
    {
        if (!self::$pInstance) 
        {
            self::$pInstance = new SRPForm(); 
        }
        return self::$pInstance; 
    } 
}
//overload the class with a function
function SRPForm()
{
    //a little cleaner because it should pass everything along to getInstance()
    //meaning that we don't have to worry about passing arguments
    return call_user_func_array('SRPForm::getInstance', func_get_args());
    //return SRPForm::getInstance();
}

?>
