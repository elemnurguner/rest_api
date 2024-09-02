<?php
// API anahtarı üretme fonksiyonu
function generateApiKey($length = 32)
{
     return bin2hex(random_bytes($length));
}

// Yeni bir API anahtarı oluşturun
$apiKey = generateApiKey();
echo json_encode(['api_key' => $apiKey]);
?>