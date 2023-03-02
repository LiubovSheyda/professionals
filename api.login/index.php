<?php

require 'connect.php';
require 'functions.php';

header("Content-Type: application/json; charset=UTF-8");

$method = $_SERVER['REQUEST_METHOD'];

$q = $_GET['q'];
$parms = explode('/', $q);

$type = $parms[0];
$id = $parms[1];
$active = $parms[2];

switch($method){
    case "GET":
        if($type === "users"){
            if($id){
                getOneRecord($connect, $id);
            } else{
                getRecords($connect);
            }
        }

        if($type === "logout"){
            logoutUser($connect);
        }
    break;

    case "POST":
        if($type === "users"){
            addRecord($connect, $_POST);
        }

        if($type === "login"){
            loginUser($connect, $_POST);
        }

        if($type === "work-shift"){
            if($active === 'open'){
                openWorkShift($connect, $id);
            } elseif($active === 'close'){
                closeWorkShift($connect, $id);
            } else{
                addWorkShift($connect, $_POST);
            }
        }
    break;
}

?>