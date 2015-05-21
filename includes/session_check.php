<?php
if (login_check() != true) {
    //take to index
    header("Location: index.php");
    exit;
}
?>