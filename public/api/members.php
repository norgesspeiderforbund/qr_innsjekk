<?php

$token = $_GET['token'];

header('Content-Type: application/json');

// Sjekk om brukeren har tilgang til å se folk.
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://min.speiding.no/api/check_permission?permission=SeeMembers&body_type=project&body_id=2108");
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Authorization: Bearer " . $token
));
curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = json_decode(curl_exec($ch), true);
curl_close($ch);

if(array_key_exists("err", $output)) {
    echo json_encode(["error" => true, "output" => $output, "token" => $token]);
    exit;
}


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://min.speiding.no/api/project/get/participants?id=2108&key=f4c3194cc4de1cdd470525b0867c63119a53f1eb");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);
$output = json_decode($output, true);
curl_close($ch);

if(!array_key_exists('participants', $output)) {
    echo json_encode(["error" => true, "output" => $output]);
    exit;
}

$result = array();
$haik = json_decode(file_get_contents('haik.json'), true);

foreach ($output['participants'] as $member_no => $data) {

    if($data['cancelled']){
        continue;   // Meldt seg av.
    }

    $status = "Ikke kommet";
    $statuscolor = "grey";
    $statusicon = "mdi-account-alert";

    if($data['checked_in']){
        // Sjekket inn
        $status = "Er i leir";
        $statuscolor = "light-green";
        $statusicon = "mdi-account-multiple-check";
    }

    if(!$data['checked_in'] && $data['attended']){
        // Sjekket ut
        $status = "Utsjekket";
        $statuscolor = "red";
        $statusicon = "mdi-account-arrow-right";
    }

    if(in_array($member_no, $haik) && $haik[$member_no]) {
        // Er på haik.
        $status = "Er på haik";
        $statuscolor = "blue";
        $statusicon = "mdi-tent";
    }

    array_push($result, array(
        "member_no" => $member_no,
        "name" => $data['first_name'] . ' ' . $data['last_name'],
        "status" => $status,
        "statuscolor" => $statuscolor,
        "statusicon" => $statusicon,
    ));
}

echo json_encode($result);