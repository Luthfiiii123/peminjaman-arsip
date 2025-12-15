<?php
    $server="localhost";
    $user="root";
    $password="";
    $database="db_arsip";
    $port="3306";

    $db=mysqli_connect($server, $user, $password, $database, $port);

    if(!$db)
    {
        die("Gagal terhubung dengan database: " . mysqli_connect_error());
    }
    else
    {
        // echo "Database terkoneksi";
    }
?>
