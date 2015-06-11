<?php
/**
 * Copyright (C) 2013 peredur.net
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
//require the core
include('includes/application_top.php');

if (login_check() == true) {
   //redirect to pages.php
    header("Location: pages.php");
    exit;
} else {
    $logged = 'out';
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
    <script src="mode/htmlmixed/htmlmixed.js"></script>
    <script src="addon/edit/matchbrackets.js"></script>

    <script type="text/javascript" src="js/jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="js/jquery.easing.1.3.js"></script>
    <script type="text/javascript" src="js/jquery-cookie.js"></script>
    <script type="text/javascript" src="js/gui.js"></script>
    <script type="text/javascript" src="js/editor.js"></script>
    
    <script type="text/JavaScript" src="js/sha512.js"></script> 
    <script type="text/JavaScript" src="js/forms.js"></script>

</head>

<body>
<!--
<header><div class="head-title">JACKRABBIT<span>CMS</span></header>
-->
<div class="main-wrap">
        <!--

http://stackoverflow.com/questions/25135963/node-webkit-mysql-connection-error-er-handshake-error-bad-handshake

-->
    <div class="menu-bar">
       
    </div>
        <div class="table">
            
            
            <div class="main">
                <div class="main-scroll">
                    <?php
                    if (isset($_GET['error']) && !empty($_SESSION['login_error'])) {
                        echo '<div class="errors">'.$_SESSION['login_error'].'</div>';
                        $_SESSION['login_error'] = '';
                    }
                    ?> 
                    
                    <form action="includes/process_login.php" method="post" name="login_form">
                    <div class="dark-modal">
                        <h1>Sign In</h1>
                        
                        <input type="text" name="user_name" placeholder="username" required/>
                        <input type="password" name="password" id="password" placeholder="password" required/>
                    <?php
                        if($_SESSION['attempts']>=2)
                        {
                            //show captcha
                    ?>
                        <img name="captcha" onclick="this.src='includes/classes/server.php?'+Math.random();" src="includes/classes/server.php" alt="CAPTCHA image" width="150" height="35"><br>
                        <input type="text" name="captcha_text" id="captcha_text" placeholder="Enter the Letters" required />
                    <?php
                        }
                    ?>
                        <input type="checkbox" id="remember"> <label for="remember">Remember Me?</label>
                        <input type="button" value="Sign In" onclick="formhash(this.form, this.form.password);">
                        <br><br>
                        <a href="https://scaredrabbit.com/pages/srp-account-login-help" target="_blank">Forgot Login?</a>
                    </div>
                    </form>
                </div>
                <!-- end .main-scroll-->
            </div>
            <!-- end .main -->
            
            
        </div>
        <!-- end .table -->
</div>

</body>

</html>
