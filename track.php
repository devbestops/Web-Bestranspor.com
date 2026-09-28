<?php

ini_set('display_errors', 0);
error_reporting(0);

// Set header JSON
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Cek ekstensi cURL di server
if (!function_exists('curl_init')) {
    echo json_encode(['error' => 'Ekstensi PHP cURL belum diaktifkan di server produksi.']);
    exit;
}

// Tangkap request dari JavaScript
$inputData = json_decode(file_get_contents('php://input'), true);
$hawbNo = isset($inputData['HAWBNo']) ? trim($inputData['HAWBNo']) : '';

if (empty($hawbNo)) {
    echo json_encode(['error' => 'Nomor AWB tidak boleh kosong']);
    exit;
}

// Panggil API Pusat via cURL
$apiUrl = 'http://59.153.83.135/api/best/HAWBStatus';
$payload = json_encode(['HAWBNo' => $hawbNo]);

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Tangkap error cURL jika koneksi diblokir / timeout
if ($curlError) {
    echo json_encode(['error' => 'Gagal melakukan cURL ke API Pusat: ' . $curlError]);
    exit;
}

if ($httpCode === 200 && $response) {
    echo $response;
} else {
    echo json_encode(['error' => 'API Pusat mengembalikan HTTP Status: ' . $httpCode]);
}