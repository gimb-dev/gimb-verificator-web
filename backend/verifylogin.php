<?php

define("AUTH_ENDPOINT", "https://auth.gimb.tk/");

if (!array_key_exists("username", $_POST) || !array_key_exists("password", $_POST)) {
    http_response_code(400);
    $result = array(
        "success" => FALSE,
        "message" => "Bad request",
    );

    header("Content-type: application/json");
    exit(json_encode($result));
}

$ch = curl_init(AUTH_ENDPOINT);

$payload = json_encode(array(
    "username" => $_POST["username"],
    "password" => $_POST["password"],
));

curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json"));

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($ch);
curl_close($ch);

$result_parsed = json_decode($result, true);

if (!array_key_exists("success", $result_parsed)) {
    http_response_code(500);
    $result = array(
        "success" => FALSE,
        "message" => "Invalid response from upstream",
    );

    header("Content-type: application/json");
    exit(json_encode($result));
}

if ($result_parsed["success"] == false) {
    http_response_code(401);
    $result = array(
        "success" => FALSE,
        "message" => "Authentication failed",
    );

    header("Content-type: application/json");
    exit(json_encode($result));
}

$code = random_bytes(24);
$code = base64_encode($code);
$code = str_replace("/", "-", $code);
$code = str_replace("+", "_", $code);
$code = str_replace("=", "", $code);