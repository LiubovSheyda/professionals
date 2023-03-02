<?php

// Генерация токена
function get_token() {
	$token = md5(microtime() . 'salt' . time());
	return $token;
}

// Функция для вывода всех записей из БД
function getRecords($connect)
{
    $users = mysqli_query($connect, "SELECT * FROM `users`");

    $userList = [];

    while($user = $users -> fetch_assoc()){
        $userList["users"][] = $user;
    }

    echo json_encode($userList);
}

// Функция для вывода одной записи из БД по id
function getOneRecord($connect, $id)
{
    $user = mysqli_query($connect, "SELECT * FROM `users` WHERE `id` = '$id';");

    if(mysqli_num_rows($user) === 0){
        $response = [
            "status" => false,
            "message" => "Record not found"
        ];

        http_response_code(404);
        echo json_encode($response);
    }
    else{
        $user = mysqli_fetch_assoc($user);
        echo json_encode($user);
    }
}

// Добавление новой записи в БД
function addRecord($connect, $data){
    $name = $data['name'];
    $surname = $data['surname'];
    $email = $data['email'];
    $password = $data['password'];

    mysqli_query($connect, "INSERT INTO `users`(`id`, `name`, `surname`, `email`, `password`, `token`, `status`, `group`) VALUES (NULL,'$name','$surname','$email','$password', '', 'working', 'официант')");

    $response = [
        "status" => true,
        "message" => "Record created"
    ];

    http_response_code(201);
    echo json_encode($response);
}

// Аунтефикация пользователя
function loginUser($connect, $data){
    $login = $data['name'];
    $password = $data['password'];

    $loginDB = mysqli_query($connect, "SELECT * FROM `users` WHERE `name` = '$login' AND `password` = '$password'");
   
    if(mysqli_num_rows($loginDB) !== 0){
        $token = get_token();
        $response = [
            "data" => [
                "user_token" => $token,
            ]
        ];

        $user = mysqli_fetch_assoc($loginDB);
        $idUser = $user["id"];

        mysqli_query($connect, "UPDATE `users` SET `token`='$token' WHERE `id` = '$idUser' ");

        http_response_code(200);
        echo json_encode($response);
    }
    else{
        $response = [
            "error" => [
                "code" => 401,
                "message" => "Authentication failed",
            ]
        ];

        http_response_code(401);
        echo json_encode($response);
    }
}

// Выход пользователя из системы
function logoutUser($connect){

    mysqli_query($connect, "UPDATE `users` SET `token`=' ' WHERE `token` IS NOT NULL ");
    $response = [
        "data" => [
            "message" => "logout",
        ]
    ];

    echo json_encode($response);
}

// Добавление смены в БД
function addWorkShift($connect, $data){
    $start = $data["start"];
    $end = $data["end"];

    mysqli_query($connect, "INSERT INTO `work_shift`(`id`, `start`, `end`) VALUES (NULL,'$start','$end')");
    // $id = mysqli_query($connect, "SELECT `id` FROM `work_shift` WHERE `start` = '$start' AND `end` = '$end';");

    // $id = mysqli_fetch_assoc($id);

    $id = mysqli_insert_id($connect);

    $response = [
        "id" => /*$id["id"]*/ $id,
        "start" => $start,
        "end" => $end,
    ];

    http_response_code(201);
    echo json_encode($response);
}

function openWorkShift($connect, $id){
    $works = mysqli_query($connect, "SELECT active FROM `work_shift` WHERE `active` = 'true'");

    if(mysqli_num_rows($works) > 0){
        
        $response = [
            "error" => [
                "code" => 403,
                "message" => "Error",
            ]
        ];

        http_response_code(403);
        echo json_encode($response);

    } else{
        mysqli_query($connect, "UPDATE `work_shift` SET `active`='true' WHERE `id`='$id'");
        $allWorks = mysqli_query($connect, "SELECT * FROM `work_shift` WHERE `id`='$id'");

        $allWorks = mysqli_fetch_assoc($allWorks);


        $response = [
            "data" => [
                "id" => $id,
                "start" => $allWorks["start"],
                "end" => $allWorks["end"],
                "active" => $allWorks["active"],
            ]
        ];

        echo json_encode($response);
    }
}

function closeWorkShift($connect, $id){
    $allWorks = mysqli_query($connect, "SELECT `active` FROM `work_shift` WHERE `id`='$id'");
    $active = mysqli_fetch_assoc($allWorks);

    if($active["active"] === "false"){
        
        $response = [
            "error" => [
                "code" => 403,
                "message" => "Error",
            ]
        ];

        http_response_code(403);
        echo json_encode($response);

    } else{
        mysqli_query($connect, "UPDATE `work_shift` SET `active`='false' WHERE `id`='$id'");

        $allWorks = mysqli_query($connect, "SELECT * FROM `work_shift` WHERE `id`='$id'");
        $works = mysqli_fetch_assoc($allWorks);

        $response = [
            "data" => [
                "id" => $id,
                "start" => $works["start"],
                "end" => $works["end"],
                "active" => $works["active"],
            ]
        ];

        echo json_encode($response);
    }
}

?>