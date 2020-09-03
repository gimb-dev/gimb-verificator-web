<?php

define("AUTH_ENDPOINT", "https://auth.gimb.tk/");
define("CODE_VALIDITY", "+1 day");

function fail($response_code, $message) {
    http_response_code($response_code);
    $result = array(
        "success" => FALSE,
        "message" => $message,
    );

    header("Content-type: application/json");
    exit(json_encode($result));
}

$json = file_get_contents('php://input');
$data = json_decode($json, TRUE);

if (!array_key_exists("username", $data) || !array_key_exists("password", $data)) {
    fail(400, "Bad request");
}

$ch = curl_init(AUTH_ENDPOINT);

$payload = json_encode(array(
    "username" => $data["username"],
    "password" => $data["password"],
));

curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json"));

curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

$result = curl_exec($ch);
curl_close($ch);

$result_parsed = json_decode($result, TRUE);

if (!array_key_exists("success", $result_parsed)) {
    fail(500, "Invalid response from upstream");
}

if ($result_parsed["success"] == FALSE) {
    fail(401, "Invalid credentials");
}

include($_SERVER["DOCUMENT_ROOT"] . "/includes/dbconfig.php");

$code = random_bytes(24);
$code = base64_encode($code);
$code = str_replace("/", "-", $code);
$code = str_replace("+", "_", $code);
$code = str_replace("=", "", $code);

$connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($connection->connect_error) {
    fail(500, "Cannot connect to database");
}

$validation_date = date("Y-m-d H:i:s", strtotime(CODE_VALIDITY));
$sql_statement_template = "REPLACE INTO %s (username, vk, expiration) VALUES ('%s', '%s', '%s')";

// $sql_stmt_prepare = str_replace("%s", "?", $sql_statement_template); // you could also do:
// $sql_statement_obj = $connection->prepare($sql_stmt_prepare); // meh, real_escape is safe
// $sql_statement_obj->bind_param("ssss", $sql_statement_template, DB_TABLE, $data["username"],
//                                $code, $validation_date);

$sql_statement = sprintf(
    $sql_statement_template,
    DB_TABLE,
    mysqli_real_escape_string($connection, $data["username"]),
    $code,
    $validation_date
);

if ($connection->query($sql_statement) !== TRUE) {
    fail(500, "Database error");
    //fail(500, mysqli_error($connection));
}

$connection->close();

$result = array(
    "success" => TRUE,
    "code" => $code,
);

header("Content-type: application/json");
exit(json_encode($result));
