<?php
// tts.php — ElevenLabs Text-to-Speech proxy
// Keeps your API key hidden from the frontend.

session_start();

// Only allow logged-in students to use this endpoint
if (!isset($_SESSION['student_id'])) {
    http_response_code(403);
    exit('Unauthorized');
}

$text = isset($_POST['text']) ? trim($_POST['text']) : '';
if ($text === '') {
    http_response_code(400);
    exit('No text provided');
}

// ── CONFIG ──
$apiKey  = "sk_5c28de60e635883d514dad5bd4b42bdc9c18d1187cefd312"; // <-- ElevenLabs API key
$voiceId = "2zRM7PkgwBPiau2jvVXc";          // "Adam" — deep/warm male voice. Swap for any Voice ID you like.

$url = "https://api.elevenlabs.io/v1/text-to-speech/$voiceId";

$payload = json_encode([
    "text" => $text,
    "model_id" => "eleven_multilingual_v2",
    "voice_settings" => [
        "stability" => 0.5,
        "similarity_boost" => 0.8,
        "style" => 0.3,
        "use_speaker_boost" => true,
         "speed" => 0.7
    ]
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "xi-api-key: $apiKey",
    "Content-Type: application/json",
    "Accept: audio/mpeg"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($response === false || $httpCode !== 200) {
    http_response_code(502);
    error_log("ElevenLabs TTS error: HTTP $httpCode - $curlErr - " . substr($response, 0, 300));
    exit('TTS request failed');
}

header("Content-Type: audio/mpeg");
header("Cache-Control: no-store");
echo $response;
