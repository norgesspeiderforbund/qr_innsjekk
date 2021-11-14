<?php

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$member_no = $data['member_no'];
$token = $data['token'];
$mittnavn = $data['mittnavn'];
$checkin = $data['checkin'];
$haik = $data['haik'];

if(!isset($member_no) || !isset($token) || !isset($mittnavn)) die("Mangler parametere.");

// Sjekk om brukeren har tilgang til å sjekke inn folk.
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://min.speiding.no/api/check_permission?permission=ManageProjectCheckIn&body_type=project&body_id=2108");
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Authorization: Bearer " . $token
));
curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = json_decode(curl_exec($ch), true);
curl_close($ch);

if(array_key_exists("err", $output)){
    // Ingen tilgang
    $errorfile = json_decode(file_get_contents('errors.json'), true);
    array_push($errorfile, array(
        'time' => date(DATE_RFC2822),
        'participant' => $member_no,
        'checkersname' => $mittnavn,
        'checkin' => $checkin,
        'haik' => $haik,
        'message' => 'Ingen tilgang. Feil i token eller liknende.',
        'response' => $output
    ));
    file_put_contents('errors.json', json_encode($errorfile));
    
    // Pushmelding til Andreas
    $data = [
        "title"=> "Feil under innsjekk",
        "message"=> $mittnavn."; Ingen tilgang.",
        "priority"=> 5,
    ];
    
    $data_string = json_encode($data);
    
    $url = "https://gotify.roeste.no/message?token=AAMU6xghqV1O5a0";
    
    $headers = [
        "Content-Type: application/json; charset=utf-8"
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    
    $result = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close ($ch);

    echo json_encode(["error" => true, "output" => $output, "token" => $token]);
    exit;
}


// Brukerne skal ikke sjekkes ut av arrangementet når de drar på haik.
$comment = ($checkin ? "Sjekket inn av " . $mittnavn : "Sjekket ut av " . $mittnavn);
if($haik) {
    $comment = "HAIK: " . ($checkin ? "Tilbake i leir, registrert av " . $mittnavn : "Sendt på haik, registrert av " . $mittnavn);;

    $haikere = json_decode(file_get_contents('haik.json'), true);
    $haikere[$member_no] = !$checkin;
    file_put_contents('haik.json', json_encode($haikere));


    $checkin = true;
}

// Hvis man ikke er meldt tilbake i leir, men skal sjekke ut
if(!$checkin) {
    $haikere = json_decode(file_get_contents('haik.json'), true);
    $haikere[$member_no] = false;
    file_put_contents('haik.json', json_encode($haikere));
}

// Lag innsjekkarray
$checkinarray =  array(
    $member_no => [
        "checked_in" => ($checkin ? 1 : 0),
        "comment" => $comment
    ]
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://min.speiding.no/api/project/checkin?id=2108&key=2d13e8f499fa4407810dfeed3f1e0c622f976aeb");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($checkinarray));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);
$output = json_decode($output, true);
curl_close($ch);

if($output['total'] == 1){
    echo json_encode(['success' => true]);
}else{
    echo json_encode(['success' => false]);

    $errorfile = json_decode(file_get_contents('errors.json'), true);
    array_push($errorfile, array(
        'time' => date(DATE_RFC2822),
        'participant' => $member_no,
        'checkersname' => $mittnavn,
        'checkin' => $checkin,
        'haik' => $haik,
        'message' => "Feil under innsjekk.",
        'response' => $output
    ));
    file_put_contents('errors.json', json_encode($errorfile));

    // Pushmelding til Andreas
    $data = [
        "title"=> "Feil under innsjekk",
        "message"=> $mittnavn."; Tjenerfeil.",
        "priority"=> 5,
    ];
    
    $data_string = json_encode($data);
    
    $url = "https://gotify.roeste.no/message?token=AAMU6xghqV1O5a0";
    
    $headers = [
        "Content-Type: application/json; charset=utf-8"
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    
    $result = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close ($ch);

}