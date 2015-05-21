<?php
/*
 *      srp_core.php
 *      
 *      Copyright 2012 Derrek Bertrand <bernard.neurotic@gmail.com>
 *      
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *      
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *      
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */

define('SRP_LOG_NONE', 0);
define('SRP_LOG_NOTICE', 1);
define('SRP_LOG_ERROR', 2);
define('SRP_LOG_CRITICAL', 4);
define('SRP_LOG_EMAIL', 8);
define('SRP_LOG_DEBUG', 0xffffffff);

define('SRP_ESC_HTML', 1);
define('SRP_ESC_SQL', 2);
define('SRP_ESC_URL', 4);


/*
 * SRPCore
 * 
 * A core class that embodies the database class, error class, file uploads
 * class, session handling, Info and Settings, Unix time stamp to durations,
 * image resizing, and misc functionality from the older admins
 */
class SRPCore 
{ 
    private static $pInstance; //a reference of the one instance of this class
    const VERSION_STR = '1.0.3b';

    
    //the ini settings from 'srp.core.ini'
    private $ini;
    //the URI to be used
    private $url;
    private $url_array;
    //database info
    private $mysqli;
    //config array
    private $configuration;

    private function __construct() {
        //get the settings from the ini file
        $this->ini = parse_ini_file('srp_core.ini', true);
        if($this->ini === false)
        {
            echo 'Could not load SRP\'s core configuration!';
            die;
        }

        //a slight misnomer, this is used to set the URL variables
        $this->soft_redirect($_SERVER['REQUEST_URI'], true, true);
    }

    function __destruct() {
        //close the connection
        if(is_object($this->mysqli))
            $this->mysqli->close();
    }
    
    
    private function __clone() { ; }
    
   
    //goes to the database and fetches the configuration entries
    //this gets called only if config functions get called for speed
    private function _fetch_configuration()
    {
        //if it is not set, then go!
        if($this->configuration === null && is_object($this->mysqli))
        {
            //get the result
            $res = $this->query('SELECT `key`, `value` FROM configuration');
            //check that we actually have rows
            if($res->num_rows() > 0)
            {
                //set each item up as:
                // $cfg[KEY] = value
                while($row = $res->fetch())
                    $this->configuration[$row['key']]=$row['value'];

                //add files_uploaded directory to the cfg
                $this->configuration['UPLOAD_DIR']=$this->ini['uploads']['dir'];
            }
            else
            {
                $this->log_critical('Tried to fetch config, but no configuration items were retrieved.');
            }
        }
        else
        {
            $this->log_notice('Tried to get config, but really we shouldn\'t be trying to.');
        }
    }
    
    public function session_start()
    {
        //set some basic session settings
        if (substr_count ($_SERVER['HTTP_HOST'], '.') == 1)
	    {
		    $cookie_domain = '.' . $_SERVER['HTTP_HOST'];
	    }
	    else
	    {
		    $cookie_domain = preg_replace('/^([^.])*/i', null, $_SERVER['HTTP_HOST']);
	    }
        
        //set the domain for the cookie
        ini_set ('session.cookie_domain', $cookie_domain);
        //empty tags
        ini_set('url_rewriter.tags','');
        //do not put it in the url
        ini_set('session.use_trans_sid', false);
        //start our session
        session_start();
    }

    public function sec_session_start()
    {
        $session_name = 'sec_session_id';   // Set a custom session name 
        $secure = false;

        // This stops JavaScript being able to access the session id.
        $httponly = true;

        // Forces sessions to only use cookies.
        if (ini_set('session.use_only_cookies', 1) === FALSE) {
            header("Location: ../error.php?err=Could not initiate a safe session (ini_set)");
            exit();
        }

        // Gets current cookies params.
        $cookieParams = session_get_cookie_params();
        session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly);

        // Sets the session name to the one set above.
        session_name($session_name);

        session_start();            // Start the PHP session 
        session_regenerate_id();    // regenerated the session, delete the old one. 
    }

    public function session_assert($variable = 'logged_in', $to = 'index.php')
    {
        //if it is not set or is false
        if(!isset($_SESSION[$variable]) || !$_SESSION[$variable])
        {
            //quit and redirect the user
            header('Location: '.$to);
            exit();
        }
    }

    public function cfg($key = null,$value = null)
    {
        //we have strings only as keys
        if(is_string($key))
        {
            //make sure we got the configuration
            if($this->configuration === null)
                $this->_fetch_configuration();
            //if there is no value
            if(is_null($value))
            {
                return $this->configuration[strtoupper($key)];
            }
            //there is a value; so replace it
            else
            {
                $this->configuration[strtoupper($key)] = $value;
            }
        }
        else
            $this->log_notice('In '.__FUNCTION__.', non string was passed as key.');
    }

    public function cfg_replace($string = null)
    {
        //you can't str_replace on non-strings
        if(is_string($string))
        {
            //make sure we got the config values
            if($this->configuration === null)
                $this->_fetch_configuration();

            //it is possible that we still don't have an array
            if(is_array($this->configuration))
            {
                //its safe here, so replace the tokens
                foreach($this->configuration as $key => $value)
                {
                    //look for [KEY] and replace with value
                    $string = str_replace('['.$key.']', $value, $string);
                }
            }
            else
                $this->log_notice(__FUNCTION__.' we got something other than an array back...');
        }
        else
        {
            $this->log_notice(__FUNCTION__.' was not passed a string.');
        }

        //disable content editable areas
        $string = str_replace('contenteditable="true"', '', $string);

        //return the thing!
        return $string;
    }

/*
 * string version_str()
 * 
 * returns version as a string
 */
    public static function version_str()
    {
        return self::VERSION_STR;
    }
    
    
/*
 * SRPCore get_instance()
 * 
 * gets the insance of SRPCore, but use the SRPCore() function instead
 * SRPCore() is more terse than SRPCore::get_instance()
 * returns SRPCore instance
 */
    public static function get_instance() 
    {
        if (!self::$pInstance) 
        {
            self::$pInstance = new SRPCore(); 
        }
        return self::$pInstance; 
    }
    
    
/*
 * SRPCore log_notice($message)
 * 
 * logs a notice
 * returns SRPCore instance for chaining
 */
    public function log_notice($message)
    {
        if($this->ini['logging']['level'] & SRP_LOG_NOTICE)
        {
            $handle = fopen($this->ini['logging']['file'], 'a');
            if($handle === false)
            {
                echo 'Cannot open log file "'.$this->ini['logging']['file'].'"';
                die;
            }
            fwrite($handle, date('[D M d H:m:s Y]').' [notice] '.$message."\n");
            fclose($handle);
        }
        return $this;
    }
    
    
/*
 * SRPCore log_error($message)
 * 
 * logs an error
 * returns SRPCore instance for chaining
 */
    public function log_error($message)
    {
        if($this->ini['logging']['level'] & SRP_LOG_ERROR)
        {
            $handle = fopen($this->ini['logging']['file'], 'a');
            if($handle === false)
            {
                echo 'Cannot open log file "'.$this->ini['logging']['file'].'"';
                die;
            }
            fwrite($handle, date('[D M d H:m:s Y]').' [error] '.$message."\n");
            fclose($handle);
        }
        return $this;
    }
    
    
/*
 * SRPCore log_critical($message)
 * 
 * logs a critical error, emailing the error message if instructed to do so in the config file
 * returns SRPCore instance for chaining
 */
    public function log_critical($message)
    {
        if($this->ini['logging']['level'] & SRP_LOG_CRITICAL)
        {
            $handle = fopen($this->ini['logging']['file'], 'a');
            if($handle === false)
            {
                echo 'Cannot open log file "'.$this->ini['logging']['file'].'"';
                die;
            }
            fwrite($handle, date('[D M d H:m:s Y]').' [critical] '.$message."\n");
            fclose($handle);
        }
        if($this->ini['logging']['level'] & SRP_LOG_EMAIL)
            mail($this->ini['logging']['email'], 'Critical Error: '.$this->ini['info']['site_name'], $message);
        return $this;
    }
    
    
/*
 * mixed url([$index])
 * 
 * returns the url as a string, the slice of the url, or false
 */
    public function url($index = null, $esc = null)
    {
        //if blank return string version
        if($index === null)
            return rawurldecode($this->url);
        //if a slice by that name exists, return it
        else if(isset($this->url_array[$index]))
        {
            //if we have an escape type use it
            if(is_int($esc))
            {
                if($esc == SRP_ESC_HTML)
                {
                    return htmlentities(rawurldecode($this->url_array[$index]));
                }
                else if($esc == SRP_ESC_SQL)
                {
                    return $this->mysqli->real_escape_string(rawurldecode($this->url_array[$index]));
                }
                //this may seem redundant, but it escapes to RFC 3986
                else if($esc == SRP_ESC_URL)
                {
                    return rawurlencode(rawurldecode($this->url_array[$index]));
                }
                $this->log_error('Tried to escape to a type that doesn\'t exist.');
                return rawurldecode($this->url_array[$index]);
            }
            else
                return rawurldecode($this->url_array[$index]);
        }
        //otherwise return false
        else
            return false;
    }

    //gets path from root up until first blank or GET parameters
    public function get_truncated_path()
    {
        //start at 1
        $i = 1;
        //add the first slice
        $path = '/'.$this->url(0);

        //if the very first slice was blank, then stop
        if(strlen($this->url(0)))
            //the first slice has content
            //so keep going until you hit a blank
            while(strlen($this->url($i)))
            {
                //add the slice and a slash
                $path .= '/'.$this->url($i);

                //next slice
                $i++;
            }

        return $path;
    }

    //this is for performing a fake redirect
    //that is, it modifies the url so that you can run code
    //with the new url
    //you also have the option of resetting the configuration
    //as well as the default db, if necessary
    public function soft_redirect($url, $reset_cfg = false, $reset_db = false)
    {
        //the request url
        $this->url = $url;
        //what we will use for page processing
        $this->url_array = explode('/', $this->url);
        array_shift($this->url_array);

        if($reset_cfg)
        {
            //set configuration to null
            //unset is just to make double sure that
            //PHP knows to delete the memory
            unset($this->configuration);
            $this->configuration = null;
        }

        if($reset_db)
        {
            //now connect to default db
            $this->connect();
        }
    }
    
    public function connect($host = null, $user = null, $pass = null, $name = null)
    {
        //if they are null, then use the default
        if($host === null)
        {
            $host = $this->ini['dbase']['host'];
            $user = $this->ini['dbase']['user'];
            $pass = $this->ini['dbase']['pass'];
            $name = $this->ini['dbase']['name'];
        }
        //close any open connection
        if(is_object($this->mysqli))
            $this->mysqli->close();

        //now log into mysql
        $this->mysqli = @new mysqli(
                $host,
                $user,
                $pass,
                $name);
        if($this->mysqli->connect_error)
        {
            //a (useless) example of chaining
            $this->log_notice('Connection problem - '.$this->mysqli->connect_errno.': '.$this->mysqli->connect_error)
            ->mysqli = null;
        }
    }
    
/*
 * mixed query($string)
 * 
 * preforms the specified query
 * returns a SRPSQLResult object
 */
    public function query($string = null)
    {
        if(!is_string($string))
        {
            $this->log_critical('You tried to query an un-string!');
            return new SRPSQLResult();
        }
        if($this->mysqli === null)
        {
            $this->log_critical('Query function was used without having a database.');
            return new SRPSQLResult();
        }
        $result = $this->mysqli->query($string);
        //there was an issue
        if($result === false)
        {
            $this->log_critical('Query failed to run: '."\n        $string\n        "
            .$this->mysqli->errno.': '.$this->mysqli->error);
            return new SRPSQLResult();
        }
        //there was no issue, so make a 'true' result or make a  
        if($result === true)
        {
            return new SRPSQLResult(true);
        }
        else
        {
            return new SRPSQLResult($result);
        }
    }
    public function prepare($string = null)
    {
        if(!is_string($string))
        {
            $this->log_critical('You tried to query an un-string!');
            return new SRPSQLResult();
        }
        if($this->mysqli === null)
        {
            $this->log_critical('Query function was used without having a database.');
            return new SRPSQLResult();
        }
        $result = $this->mysqli->prepare($string);
        //there was an issue
        if($result === false)
        {
            $this->log_critical('Query failed to run: '."\n        $string\n        "
            .$this->mysqli->errno.': '.$this->mysqli->error);
            return new SRPSQLResult();
        }
        //there was no issue, so make a 'true' result or make a  
        if($result === true)
        {
            return new SRPSQLResult(true);
        }
        else
        {
            return new SRPSQLResult($result);
        }
    }

 /*
  * int last_inserted()
  *
  * returns the last inserted id
  */
    public function last_inserted()
    {
        return $this->mysqli->insert_id;
    }


    //These are depreciated, but makes for easier conversion
    public function fetch($res)
    {
        if(is_object($res))
            return $res->fetch();
        else
            return 0;
    }

    public function fetchItem($res)
    {
        if(is_object($res))
            return $res->fetch_item();
        else
            return 0;
    }

    public function numRows($res)
    {
        if(is_object($res))
            return $res->num_rows();
        else
            return 0;
    }
}
/*
 * SRPCore SRPCore()
 * 
 * returns an SRPCore instance, used for clean chaining
 */
function SRPCore()
{
    return SRPCore::get_instance(func_get_args());
}




/*
 * SRPSQLResult
 * 
 * A result wrapper to enable chaining
 * is not to be created directly
 * 
 */
class SRPSQLResult
{
    private $result;
    
    public function __construct($result = false) {
        $this->result = $result;
    }
    
    
    private function __clone() { ; }
    
    
    function __destruct() {
        if(is_object($this->result))
            $this->result->free_result();
    }
    
    
/*
 * bool status()
 * 
 * true if good false on error
 */
    public function status()
    {
        if($this->result === false)
            return false;
        else
            return true;
    }
    
/*
 * int num_rows()
 * 
 * returns the number of rows in a result
 */
    public function num_rows()
    {
        if(is_object($this->result))
            return $this->result->num_rows;
        else
            return 0;
    }


/*
 * array fetch_all()
 * 
 * returns an array of associative arrays of fields
 */
    public function fetch_all()
    {
        if(is_object($this->result))
            return $this->result->fetch_all(MYSQLI_BOTH);
        else
            return 0;
    }


/*
 * array fetch()
 * 
 * returns an associative array of fields
 */
    public function fetch()
    {
        if(is_object($this->result))
            return $this->result->fetch_array();
        else
            return array();
    }

/*
 * array fetch_item()
 * 
 * returns an item
 */
    public function fetch_item()
    {
        if(is_object($this->result))
        {
            $row = $this->result->fetch_row();
            return $row[0];
        }
        else
            return null;
    }

/*
 * void data_seek()
 * 
 * seeks to a specific row
 */
    public function data_seek($row = 0)
    {
        if(is_object($this->result) && is_int($row))
        {
            return $this->result->data_seek($row);
        }
    }

    public function bind_param($y, $field)
    {
        if(is_object($this->result))
            return $this->result->bind_param($y, $field);
        else
            return array();
    }

    public function execute()
    {
        if(is_object($this->result))
            return $this->result->execute();
        else
            return array();
    }

    public function store_result()
    {
        if(is_object($this->result))
            return $this->result->store_result();
        else
            return array();
    }

    public function bind_result($vars)
    {
        if(is_object($this->result))
        {
            return call_user_func_array(array($this->result,'bind_result'),$vars);//$this->result->bind_result($vars_list);
        }
        else
            return array();
    }

    public function pfetch()
    {
        if(is_object($this->result))
            return $this->result->fetch();
        else
            return array();
    }

}
?>
