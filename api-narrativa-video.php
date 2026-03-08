<?php

header("Content-Type: application/json");

$OPENAI_API_KEY = "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";

if (!$OPENAI_API_KEY) {
    echo json_encode([
        "success"=>false,
        "error"=>"OPENAI_API_KEY não configurada"
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success"=>false,
        "error"=>"Método inválido"
    ]);
    exit;
}

if (!isset($_FILES["video"])) {
    echo json_encode([
        "success"=>false,
        "error"=>"Envie um vídeo"
    ]);
    exit;
}

$produto = $_POST["nome_produto"] ?? "produto";

$tmp = $_FILES["video"]["tmp_name"];

$id = uniqid();

$videoInput = "uploads/narrativa/video_$id.mp4";
$audioFile = "uploads/narrativa/audio_$id.mp3";
$videoFinal = "uploads/narrativa/video_final_$id.mp4";

move_uploaded_file($tmp,$videoInput);

$prompt = "Crie uma narração curta e persuasiva para apresentar o produto: $produto";

$ch = curl_init();

curl_setopt_array($ch,[
CURLOPT_URL=>"https://api.openai.com/v1/responses",
CURLOPT_RETURNTRANSFER=>true,
CURLOPT_POST=>true,
CURLOPT_HTTPHEADER=>[
"Content-Type: application/json",
"Authorization: Bearer ".$OPENAI_API_KEY
],
CURLOPT_POSTFIELDS=>json_encode([
"model"=>"gpt-4.1-mini",
"input"=>$prompt
])
]);

$response = curl_exec($ch);

$data=json_decode($response,true);

$text=$data["output"][0]["content"][0]["text"] ?? "";

curl_close($ch);

$ch = curl_init();

curl_setopt_array($ch,[
CURLOPT_URL=>"https://api.openai.com/v1/audio/speech",
CURLOPT_RETURNTRANSFER=>true,
CURLOPT_POST=>true,
CURLOPT_HTTPHEADER=>[
"Content-Type: application/json",
"Authorization: Bearer ".$OPENAI_API_KEY
],
CURLOPT_POSTFIELDS=>json_encode([
"model"=>"gpt-4o-mini-tts",
"voice"=>"alloy",
"input"=>$text
])
]);

$audio=curl_exec($ch);

file_put_contents($audioFile,$audio);

curl_close($ch);

$cmd="ffmpeg -y -i $videoInput -i $audioFile -map 0:v -map 1:a -c:v copy -shortest $videoFinal";

shell_exec($cmd);

echo json_encode([
"success"=>true,
"narrativa"=>$text,
"video_url"=>$videoFinal
]);
