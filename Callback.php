<?php    
    session_start();

    $_SESSION['code'] = $_GET['code'];
    
    header('Location: http://localhost/api/v1/albums?q='.$_SESSION['name']);
?>