<?php

/*
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
require_once('classes/srp_core.php');
require_once('general.php');
include_once 'login_functions.php';
//session stuffs
SRPCore()->sec_session_start();

if (isset($_POST['user_name'], $_POST['p'])) 
{
    
    
    $user_name = strip_tags(db_input($_POST['user_name']));
    $password = $_POST['p']; // The hashed password.
    $captcha_text = $_POST['captcha_text'];
    
    $login_attempt = login($user_name, $password, $_SESSION['attempts'], $captcha_text);
    if(is_bool($login_attempt) && $login_attempt == true)
    {
        // Login success 
        header("Location: ../pages.php");
        exit();
    } 
    else 
    {
        // Login failed 
        $_SESSION['login_error'] = $login_attempt;
        $_SESSION['attempts']++;
        header('Location: ../index.php?error=1');
        exit();
    }
} else {
    // The correct POST variables were not sent to this page. 
    $_SESSION['login_error'] = "Could not process login.";
    header('Location: ../index.php?error=1');
    exit();
}