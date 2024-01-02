<?php
date_default_timezone_set('Asia/Jakarta');
$bulan = 10; // Oktober
$tahun = 2023;
$tahun_akhir = 2023;// Tahun saat ini
$date_akhir = date("Y-m-d");
$token = "";

// Menentukan tanggal awal dan akhir bulan
$tanggal_awal = strtotime("$tahun-$bulan-08");
$tanggal_akhir = strtotime("$date_akhir");

// $token = getToken();
$token = "dfc248f8122e39a13270c6a3499f1fde";

// Membuat daftar tanggal
$daftar_tanggal = array();

for ($tanggal = $tanggal_awal; $tanggal <= $tanggal_akhir; $tanggal += 86400) {
    $daftar_tanggal[] = date("Y-m-d", $tanggal);
}

// // Menampilkan daftar tanggal beserta timestamp
foreach ($daftar_tanggal as $tanggal) {
	if($tanggal !== $date_akhir){
		$timestamp = strtotime($tanggal);
    	$tanggal_next = date("Y-m-d", strtotime($tanggal . "+1 day"));
    	$timestamp_nxt = strtotime($tanggal_next);
        $result = getDataHistory($token, $timestamp, $timestamp_nxt, "28723");
        if(count($result["result"]) > 0){
            importPortal($result["result"], "zabbix_cpl_gti_gu21_upsa2");
            // sendMessage($tanggal);
            // sleep(5);
            echo "Tanggal: $tanggal, Timestamp: $timestamp \n";
        }
	}
}

function sendMessage($date){
     $str = "";
     $str .= $date."\n";
     $botToken = '6609546303:AAFiBTo0oPfxcEib-mOhxJ5bQ3ScjkVUdrg';
     $chatId = '956131643';
     $apiUrl = "https://api.telegram.org/bot$botToken/sendMessage?parse_mode=Markdown";
     $data = [
         'chat_id' => $chatId,
         'text' => $str,
     ];

     $options = [
         CURLOPT_URL => $apiUrl,
         CURLOPT_POST => true,
         CURLOPT_POSTFIELDS => $data,
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_HEADER => false,
     ];

     $curl = curl_init();
     curl_setopt_array($curl, $options);

     $response = curl_exec($curl);
     if ($response === false) {
         echo 'cURL error: ' . curl_error($curl);
     } else {
         $responseData = json_decode($response, true);
         if ($responseData['ok']) {
             echo 'Message sent successfully!';
         } else {
             echo 'Message sending failed. Error: ' . $responseData['description'];
         }
     }

     curl_close($curl);
}


function curl($url, $header, $body, $method){
    $option = [];
    if($method === "POST"){
        $option = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
        ];
    } else {
        $option = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
        ];
    }
   

    $ch = curl_init();
    curl_setopt_array($ch, $option);
    $response = curl_exec($ch);
    return $response;
}


function getToken(){
    $url = 'http://172.18.65.246/zabbix/api_jsonrpc.php';

    $header = array(
        'Content-Type: application/json',
    );

    $payload = [
        "jsonrpc" => "2.0",
        "method" => "user.login",
        "params" => [
            "user" => "QAC",
            "password" => "QAC12345"
        ],
        "id" => 1
    ];

    $result = curl($url, $header, $payload, "POST");
    return json_decode($result, true);
}

function getDataHistory($token, $start, $end, $itemID){
    $url = 'http://172.18.65.246/zabbix/api_jsonrpc.php';

    $header = array(
        'Content-Type: application/json',
    );

    $payload = [
        "jsonrpc" => "2.0",
        "method" => "history.get",
        "params" => [
            "output" => "extend",
            "history" => 0,
            "itemids" => $itemID,
            "sortfield" => "clock",
            "sortorder" => "DESC",
            "time_from" => $start,
            "time_till" => $end,
            "limit" => 45000
        ],
        "auth" => $token,
        "id" => 1
    ];

    $result = curl($url, $header, $payload, "POST");
    return json_decode($result, true);
}

function importPortal($data, $tableName){
    $url = 'https://clq.bri.co.id/api/bigdata/zabbix-import-history';

    $header = array(
        'Content-Type: application/json',
    );

    $payload = [
        "table" => $tableName,
        "data" => $data
    ];

    $result = curl($url, $header, $payload, "POST");
    return json_decode($result, true);
}

?>