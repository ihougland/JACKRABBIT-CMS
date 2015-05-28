<?php
/*******************************************
 _   _ ____  _         
| | | |  _ \| |    ___ 
| | | | |_) | |   / __|
| |_| |  _ <| |___\__ \
 \___/|_| \_\_____|___/
*******************************************/
function href_link($page = '', $parameters = '', $connection = 'NONSSL')
{

  $server = ($connection=='NONSSL') ? SRPCore()->cfg('HTTP_SERVER') : SRPCore()->cfg('HTTPS_SERVER');

  if(!empty($parameters)) $parameters = "?".$parameters;

  $link_href = $server.$page.$parameters;

  return $link_href;

}
function build_page_href($page_id)
{
  global $db;
  
  $page_href_string = "pages/";
  
  $sql = "SELECT * FROM pages WHERE page_id=".$page_id;
  $result = $db->query($sql);
  $row = $db->fetch($result);
  
  $page_href_string .= preg_replace('/[^a-zA-Z0-9]/','+', preg_replace(array('/^and$/','/^by$/','/[^a-zA-Z0-9 ]*/'),'',strtolower($row['title'])));
  
  $page_href_string .= '/'.$page_id;
  

  return $page_href_string;
}
function zen_redirect($url) 
{
  // clean up URL before executing it
  while (strstr($url, '&&')) $url = str_replace('&&', '&', $url);
  while (strstr($url, '&amp;&amp;')) $url = str_replace('&amp;&amp;', '&amp;', $url);
  // header locates should not have the &amp; in the address it breaks things
  while (strstr($url, '&amp;')) $url = str_replace('&amp;', '&', $url);

  header('Location: ' . $url);

  zen_exit();
}
function get_all_get_params($exclude_array = '') {
  global $_GET;

  if ($exclude_array == '') $exclude_array = array();

  $get_url = '';

  reset($_GET);
  while (list($key, $value) = each($_GET)) 
  {
    if ($key != 'error' && !in_array($key, $exclude_array)) $get_url .= $key . '=' . $value . '&';
  }

  return $get_url;
}

function get_all_get_params_slash($exclude_array = '',$slash_var_string) {
  if ($exclude_array == '') $exclude_array = array();

  $get_url = '';
  
  $slash_var_array = explode("/",$slash_var_string);
  unset($slash_var_array[0]);
  $num_slash_vals = count($slash_var_array);
  $slash_val_counter = 1;
  while($slash_val_counter<=$num_slash_vals)
  {
    $slash_variable = $slash_var_array[$slash_val_counter++];
    $slash_value =  str_replace('|', '/',$slash_var_array[$slash_val_counter++]);
    if ($slash_variable != 'error' && !in_array($slash_variable, $exclude_array)) $get_url .= $slash_variable . '/' . $slash_value . '/';
  }

  return $get_url;
}
/*******************************************
 _____         _     _____                          _   _   _             
|_   _|____  _| |_  |  ___|__  _ __ _ __ ___   __ _| |_| |_(_)_ __   __ _ 
  | |/ _ \ \/ / __| | |_ / _ \| '__| '_ ` _ \ / _` | __| __| | '_ \ / _` |
  | |  __/>  <| |_  |  _| (_) | |  | | | | | | (_| | |_| |_| | | | | (_| |
  |_|\___/_/\_\\__| |_|  \___/|_|  |_| |_| |_|\__,_|\__|\__|_|_| |_|\__, |
                                                                    |___/ 
*******************************************/
function output_string($string, $translate = false, $protected = false) 
{
  if ($protected == true) 
    return htmlspecialchars($string);
  elseif ($translate == false) 
    return db_input($string, array('"' => '&quot;'));
  else 
    return db_input($string, $translate);
}
function db_input($string) 
{
    return addslashes(trim($string));
}
function db_output($string) 
{
    return stripslashes($string);
}
function format_url($url)
{
	return (substr($url,0,4)!='http') ? 'http://'.$url : $url;
}
function filter_money($number)
{
	$new_number = preg_replace('/[^0-9.]/', '', $number);
	return $new_number;
}

function address_format($address_format_id, $address, $html, $boln, $eoln) 
{
  //get format
  $sql = "SELECT address_format as format FROM address_format WHERE address_format_id = '$address_format_id'";
  $address_format_result = SRPCore()->query($sql);
  $address_format = $address_format_result->fetch();

  if(!empty($address['company']))
  {
    $company = htmlspecialchars($address['company']);
  }
  if(!empty($address['firstname']))
  {
    $firstname = htmlspecialchars($address['firstname']);
    $lastname = htmlspecialchars($address['lastname']);
  }
  elseif(!empty($address['name'])) 
  {
    $firstname = htmlspecialchars($address['name']);
    $lastname = '';
  }
  else
  {
    $firstname = '';
    $lastname = '';
  }

  if(!empty($company) && !empty($firstname))
  {
    $recipient = $company.$cr.$firstname." ".$lastname;
  }
  elseif(!empty($firstname))
  {
    $recipient = $firstname." ".$lastname;
  }
  else
  {
    $recipient = '';
  }

  $street = htmlspecialchars($address['street_address']);
  $street_2 = htmlspecialchars($address['street_2']);
  $city = htmlspecialchars($address['city']);
  $state = htmlspecialchars($address['state']);

  if(!empty($address['country_id']))
  {
    $country = get_country_name($address['country_id']);
    if(!empty($address['zone_id']))
    {
      $state = get_zone_code($address['zone_id']);
    }
  }
  elseif(!empty($address['country']))
  {
      $country = htmlspecialchars($address['country']);
  }
  else
  {
    $country = '';
  }

  $postcode = htmlspecialchars($address['postcode']);
  $zip = $postcode;

  if ($html)
  {
    // HTML Mode
    $HR = '<hr>';
    $hr = '<hr>';

    if ( ($boln == '') && ($eoln == "\n") )
    { // Values not specified, use rational defaults
        $CR = '<br>';
        $cr = '<br>';
        $eoln = $cr;
    }
    else 
    { // Use values supplied
        $CR = $eoln . $boln;
        $cr = $CR;
    }

  } 
  else 
  {
    // Text Mode
    $CR = $eoln;
    $cr = $CR;
    $HR = '----------------------------------------';
    $hr = '----------------------------------------';

  }

  $statecomma = '';
  $streets = $street;
  if ($street_2 != '') $streets = $street . $cr . $street_2;
  if ($country == '') $country = htmlspecialchars($address['country']);
  if ($state != '') $statecomma = $state . ', ';
  $fmt = $address_format['format'];
  eval("\$address = \"$fmt\";");

  return $address;
}
/*******************************************
 ____                      _     
/ ___|  ___  __ _ _ __ ___| |__  
\___ \ / _ \/ _` | '__/ __| '_ \ 
 ___) |  __/ (_| | | | (__| | | |
|____/ \___|\__,_|_|  \___|_| |_| 
*******************************************/
                                 
// Parse search string into indivual objects
function parse_search_string($search_str = '', &$objects) {

    $search_str = db_input(strtolower($search_str));

	// Break up $search_str on whitespace; quoted string will be reconstructed later
    $pieces = split('[[:space:]]+', $search_str);
    $objects = array();
    $tmpstring = '';
    $flag = '';

    for ($k=0; $k<count($pieces); $k++) {
      while (substr($pieces[$k], 0, 1) == '(') {
        $objects[] = '(';
        if (strlen($pieces[$k]) > 1) {
          $pieces[$k] = substr($pieces[$k], 1);
        } else {
          $pieces[$k] = '';
        }
      }
      $post_objects = array();

      while (substr($pieces[$k], -1) == ')')  {
        $post_objects[] = ')';
        if (strlen($pieces[$k]) > 1) {
          $pieces[$k] = substr($pieces[$k], 0, -1);
        } else {
          $pieces[$k] = '';
        }
      }

	// Check individual words
      if ( (substr($pieces[$k], -1) != '"') && (substr($pieces[$k], 0, 1) != '"') ) {
        $objects[] = trim($pieces[$k]);
        for ($j=0; $j<count($post_objects); $j++) {
          $objects[] = $post_objects[$j];
        }
      } else {
	/* This means that the $piece is either the beginning or the end of a string.
	   So, we'll slurp up the $pieces and stick them together until we get to the
	   end of the string or run out of pieces.
	*/
	// Add this word to the $tmpstring, starting the $tmpstring
    $tmpstring = trim(preg_replace('/"/', ' ', $pieces[$k]));
	// Check for one possible exception to the rule. That there is a single quoted word.
    if (substr($pieces[$k], -1 ) == '"') {
		// Turn the flag off for future iterations
          $flag = 'off';
          $objects[] = trim($pieces[$k]);
          for ($j=0; $j<count($post_objects); $j++) {
            $objects[] = $post_objects[$j];
          }
          unset($tmpstring);
	// Stop looking for the end of the string and move onto the next word.
          continue;
     }
	// Otherwise, turn on the flag to indicate no quotes have been found attached to this word in the string.
        $flag = 'on';
	// Move on to the next word
        $k++;
	// Keep reading until the end of the string as long as the $flag is on
        while ( ($flag == 'on') && ($k < count($pieces)) ) {
          while (substr($pieces[$k], -1) == ')') {
            $post_objects[] = ')';
            if (strlen($pieces[$k]) > 1) {
              $pieces[$k] = substr($pieces[$k], 0, -1);

            } else {

              $pieces[$k] = '';
            }
          }
	// If the word doesn't end in double quotes, append it to the $tmpstring.
          if (substr($pieces[$k], -1) != '"') {
	// Tack this word onto the current string entity
            $tmpstring .= ' ' . $pieces[$k];
	// Move on to the next word
            $k++;
            continue;

          } else {

	/* If the $piece ends in double quotes, strip the double quotes, tack the
	   $piece onto the tail of the string, push the $tmpstring onto the $haves,
	   kill the $tmpstring, turn the $flag "off", and return.
	*/
         $tmpstring .= ' ' . trim(preg_replace('/"/', ' ', $pieces[$k]));
	// Push the $tmpstring onto the array of stuff to search for
            $objects[] = trim($tmpstring);
            for ($j=0; $j<count($post_objects); $j++) {
              $objects[] = $post_objects[$j];
            }
            unset($tmpstring);
	// Turn off the flag to exit the loop
            $flag = 'off';
          }
        }
      }
    }

	// add default logical operators if needed
    $temp = array();
    for($i=0; $i<(count($objects)-1); $i++) {
      $temp[] = $objects[$i];
      if ( ($objects[$i] != 'and') &&
           ($objects[$i] != 'or') &&
           ($objects[$i] != '(') &&
           ($objects[$i+1] != 'and') &&
           ($objects[$i+1] != 'or') &&
           ($objects[$i+1] != ')') ) {
        $temp[] = "and";
      }
    }

    $temp[] = $objects[$i];
    $objects = $temp;
    $keyword_count = 0;
    $operator_count = 0;
    $balance = 0;

    for($i=0; $i<count($objects); $i++) {
      if ($objects[$i] == '(') $balance --;
      if ($objects[$i] == ')') $balance ++;
      if ( ($objects[$i] == 'and') || ($objects[$i] == 'or') ) {
        $operator_count ++;
      } elseif ( ($objects[$i]) && ($objects[$i] != '(') && ($objects[$i] != ')') ) {
        $keyword_count ++;
      }
    }

    if ( ($operator_count < $keyword_count) && ($balance == 0) ) {
      return true;
    } else {
      return false;
    }
} 
/*******************************************
 _____                        ___      ____                  _        _           
|__  /___  _ __   ___  ___   ( _ )    / ___|___  _   _ _ __ | |_ _ __(_) ___  ___ 
  / // _ \| '_ \ / _ \/ __|  / _ \/\ | |   / _ \| | | | '_ \| __| '__| |/ _ \/ __|
 / /| (_) | | | |  __/\__ \ | (_>  < | |__| (_) | |_| | | | | |_| |  | |  __/\__ \
/____\___/|_| |_|\___||___/  \___/\/  \____\___/ \__,_|_| |_|\__|_|  |_|\___||___/
*******************************************/                                                                                  
//Get Zone Name
function get_zone_name($zone_id)
{
	$sql = "SELECT zone_name FROM zones WHERE zone_id='".(int)$zone_id."'";
	$zone_result = SRPCore()->query($sql);
	$zone_name = $zone_result->fetch_item();
	return $zone_name;
}
//Get Zone Code
function get_zone_code($zone_id)
{
  $sql = "SELECT zone_code FROM zones WHERE zone_id='".(int)$zone_id."'";
  $zone_result = SRPCore()->query($sql);
  $zone_code = $zone_result->fetch_item();
  return $zone_code;
}
//Get Country Name
function get_country_name($country_id)
{
  $sql = "SELECT countries_name FROM countries WHERE countries_id='".(int)$country_id."'";
  $country_result = SRPCore()->query($sql);
  $country_name = $country_result->fetch_item();
  return $country_name;
}
//Get Country Format
function get_country_format($country_id)
{
  $sql = "SELECT format_id FROM countries WHERE countries_id='".(int)$country_id."'";
  $format_result = SRPCore()->query($sql);
  $country_name = $format_result->fetch_item();
  return $format_result;
}

/*******************************************
_____                        
|  ___|__  _ __ _ __ ___  ___ 
| |_ / _ \| '__| '_ ` _ \/ __|
|  _| (_) | |  | | | | | \__ \
|_|  \___/|_|  |_| |_| |_|___/
*******************************************/
function draw_pull_down_menu($name, $values, $default = '', $parameters = '') 
{
    $field = '<select class="formfield" name="'.$name.'"';

    if (!empty($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if (empty($default) && isset($GLOBALS[$name])) $default = stripslashes($GLOBALS[$name]);

    for ($i=0, $n=sizeof($values); $i<$n; $i++) {
      $field .= '<option value="' . output_string($values[$i]['id']) . '"';
      if ($default == $values[$i]['id']) {
        $field .= ' SELECTED';
      }

      $field .= '>' . output_string($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';
    }
    $field .= '</select>';

    return $field;
}
// Output a form select
function draw_select_menu($name, $values, $default = '', $parameters = '', $required = false) {
//  $field = '<select name="' . zen_output_string($name) . '"';
    $field = '<select rel="dropdown" name="' . $name . '"';
    if (!empty($parameters)) $field .= ' ' . $parameters;
    $field .= '>';
    if (empty($default) && isset($GLOBALS[$name])) $default = stripslashes($GLOBALS[$name]);

    for ($i=0, $n=sizeof($values); $i<$n; $i++) {
      $field .= '<option value="' . $values[$i]['id'] . '"';
      if ($default == $values[$i]['id']) {
        $field .= ' SELECTED';
      }

      $field .= '>' . $values[$i]['text'] . '</option>';
    }
    $field .= '</select>';
    return $field;
}
/*******************************************
 ____       _   _   _                 
/ ___|  ___| |_| |_(_)_ __   __ _ ___ 
\___ \ / _ \ __| __| | '_ \ / _` / __|
 ___) |  __/ |_| |_| | | | | (_| \__ \
|____/ \___|\__|\__|_|_| |_|\__, |___/
                            |___/  
*******************************************/
    
// Output a form textarea
function cfg_draw_textarea($value, $key) {
    $field = '<textarea class="form-field-textarea saveSetting" name="' . $key . '">'.stripslashes($value).'</textarea>';
    return $field;
}

// Output site live/hidden checkbox
function cfg_draw_onoff($value, $key) {
    $field = '<div>
            <input type="radio" id="'.$key.'" name="'.$key.'" class="switch" '.(($value=='true')?'checked':'').'/>
          </div>';
    return $field;
}

// Output a form radio
function cfg_draw_radio($default, $key, $values) {
    $field = '';
	$values_array = explode(',', $values);
    for ($i=0, $n=sizeof($values_array); $i<$n; $i++) {
      $field .= '<input type="radio" name="'.$key.'" value="' . $values_array[$i] . '"';
      if ($default == $values_array[$i]) {
        $field .= ' checked';
      }

      $field .= ' />' . $values_array[$i].'&nbsp;&nbsp;';
    }
    return $field;
}

// Output a form select
function cfg_draw_select($default, $key, $values) {
    $field = '<select name="'.$key.'" class="form-field-select saveSetting" id="'.$key.'">';
	$values_array = explode(',', $values);
    for ($i=0, $n=sizeof($values_array); $i<$n; $i++) {
      $field .= '<option value="' . $values_array[$i] . '"';
      if ($default == $values_array[$i]) {
        $field .= ' selected';
      }

      $field .= '>'. $values_array[$i] .'</option>';
    }
	$field .= '</select>';
    return $field;
}

function cfg_select_zone_list($zone_id)
{
  $zones_array = array();
  
  $sql = "SELECT zone_id, zone_name FROM zones WHERE zone_country_id = '223' ORDER BY zone_name";
  $zones_result = SRPCore()->query($sql);
  while ($zones = $zones_result->fetch())
  {
    $zones_array[] = array('id' => $zones['zone_id'], 'text' => $zones['zone_name']);
  }
  //name, values, default
  return draw_pull_down_menu('value', $zones_array, $zone_id);
}
  
function cfg_select_country_list($sel) 
{  
  $country_array = array();
  
  $sql = "SELECT countries_id, countries_name FROM countries ORDER BY countries_name";
  $country_result = SRPCore()->query($sql);
  while ($country = $country_result->fetch()) 
  {
    $country_array[] = array('id' => $country['countries_id'], 'text' => $country['countries_name']);
  }
  //name, values, default
  return draw_select_menu('value', $country_array, $sel);
}

/*******************************************
 __  __ _          
|  \/  (_)___  ___ 
| |\/| | / __|/ __|
| |  | | \__ \ (__ 
|_|  |_|_|___/\___|
*******************************************/

function call_function($function, $parameter, $object = '') 
{
    if ($object == '') 
  {
      return call_user_func($function, $parameter);
    } 
  elseif (PHP_VERSION < 4) 
  {
      return call_user_method($function, $object, $parameter);
    }
  else 
  {
      return call_user_func(array($object, $function), $parameter);
    }
}
function encrypt_password($plain) 
{
  $password = '';
  for ($i=0; $i<10; $i++) 
  {
    $password .= get_rand();
  }
  $salt = substr(md5($password), 0, 2);
  $password = md5($salt . $plain) . ':' . $salt;
  return $password;
}
function validate_password($plain, $encrypted) 
{
  if (!empty($plain) && !empty($encrypted)) 
  {
    // split apart the hash / salt
    $stack = explode(':', $encrypted);    
    if (sizeof($stack) != 2) return false;
    if (md5($stack[1] . $plain) == $stack[0]) return true;
  }
  
  return false;
}
function get_rand($min = null, $max = null) {
    static $seeded;
    if (!isset($seeded)) {
      mt_srand((double)microtime()*1000000);
      $seeded = true;
    }
    if (isset($min) && isset($max)) {
      if ($min >= $max) {
        return $min;
      } else {
        return mt_rand($min, $max);
      }
    } else {

      return mt_rand();
    }
}
function create_random_value($length, $type = 'mixed') {
    if ( ($type != 'mixed') && ($type != 'chars') && ($type != 'digits')) return false;
    $rand_value = '';
    while (strlen($rand_value) < $length) {
      if ($type == 'digits') {
        $char = get_rand(0,9);
      } else {
        $char = chr(get_rand(0,255));
      }
      if ($type == 'mixed') {
        if (preg_match('/[^a-z0-9]/', $char)) $rand_value .= $char;
      } elseif ($type == 'chars') {
        if (preg_match('/[^a-z]/', $char)) $rand_value .= $char;
      } elseif ($type == 'digits') {
        if (preg_match('/[^0-9]/', $char)) $rand_value .= $char;
      }
    }
    return $rand_value;
}
function order($list)
{
    // an array to keep the sort order for each level based on the parent id as the key
    $sort = array();
    foreach ($list as $id => $parentId)
    {
        /* a null value is set for parent id by nested sortable for root level elements
           so you set it to 0 to work in your case (from what I could deduct from your code) */
        $parentId = ($parentId === null) ? 0 : $parentId;

        // init the sort order value to 1 if this element is on a new level
        if (!array_key_exists($parentId, $sort)) {
            $sort[$parentId] = 1;
        }

        //update database
        SRPCore()->query("UPDATE pages SET sort_order='".$sort[$parentId]."', parent_id='".$parentId."' WHERE page_id='".$id."'"); 

        // increment the sort order for this level
        $sort[$parentId]++;
    }
}
?>