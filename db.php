<?php
$conn = new mysqli('localhost','root','','kickfit_db');
if(!$conn)
    {
        echo "Error:{$conn->connect_error}";
    }
?>