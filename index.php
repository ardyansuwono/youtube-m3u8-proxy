<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    die("Missing id parameter! Example: ?id=dQw4w9WgXcQ");
}

$id = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET["id"]); // basic sanitize

function get_data($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => "Mozilla/5.0",
        CURLOPT_REFERER => "https://www.youtube.com/",
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
    ]);

    $data = curl_exec($ch);

    if (curl_errno($ch)) {
        die("cURL error: " . curl_error($ch));
    }

    curl_close($ch);
    return $data;
}

// Ambil halaman video
$html = get_data("https://www.youtube.com/watch?v=" . $id);

// Cari URL manifest HLS
preg_match('/"hlsManifestUrl":"(.*?)"/', $html, $matches);

if (!$matches || empty($matches[1])) {
    die("Stream not found. Video mungkin bukan live / dibatasi.");
}

$m3u8 = stripslashes($matches[1]);

// Ambil isi manifest asli
$stream = get_data($m3u8);

if (!$stream) {
    die("Cannot load m3u8 file");
}

// Kirim ke client
header("Content-Type: application/vnd.apple.mpegurl");
header("Content-Disposition: inline; filename=\"stream.m3u8\"");
header("Access-Control-Allow-Origin: *");

echo $stream;
exit;
