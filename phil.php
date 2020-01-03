<?php


/* Debugging */
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* Wordpress required */
ini_set("include_path", '/home/troop/php:' . ini_get("include_path")  );
$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );

/*----------*/

$curl = curl_init();

curl_setopt_array($curl, array(
//  CURLOPT_URL => "http://api.captchacoder.com/imagepost.ashx",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
//  CURLOPT_TIMEOUT => 90,
//  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW  Content-Disposition: form-data; name=\"action\"    upload  ------WebKitFormBoundary7MA4YWxkTrZu0gW  Content-Disposition: form-data; name=\"key\"    TQQ8I6FDCAKJJGWE4EHNEKPZ9O19WMDTJ7GHTR3T  ------WebKitFormBoundary7MA4YWxkTrZu0gW  Content-Disposition: form-data; name=\"captchatype\"    3  ------WebKitFormBoundary7MA4YWxkTrZu0gW  Content-Disposition: form-data; name=\"gen_task_id\"    42  ------WebKitFormBoundary7MA4YWxkTrZu0gW  Content-Disposition: form-data; name=\"sitekey\"    6LfNHqgUAAAAANiQo7V-zteNF4zIlsFCtmejLHKG  ------WebKitFormBoundary7MA4YWxkTrZu0gW  Content-Disposition: form-data; name=\"pageurl\"    https://www.scoutbook.com/mobile  ------WebKitFormBoundary7MA4YWxkTrZu0gW--",
//  CURLOPT_HTTPHEADER => array(
//    "Content-Type: application/x-www-form-urlencoded",
//    "cache-control: no-cache",
//    "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
//  ),

));


  // send login request
    $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, "http://api.captchacoder.com/imagepost.ashx");
  curl_setopt($ch, CURLOPT_TIMEOUT, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, "action=upload&key=TQQ8I6FDCAKJJGWE4EHNEKPZ9O19WMDTJ7GHTR3T&captchatype=3&gen_task_id=4242&sitekey=6LfNHqgUAAAAANiQo7V-zteNF4zIlsFCtmejLHKG&pageurl=https://www.scoutbook.com/mobile" );


$response = curl_exec($ch);
$err = curl_error($ch);

curl_close($ch);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}
echo '<pre>';
var_dump($response);
var_dump($err);
echo '</pre>';

/*
<input type="hidden" name="ReCaptchaResponse" 
value="03AOLTBLReHH9OGE2528uRR-ZPNxY_WrgiX_zMZHNEyPGBhjpV3IRZXBLKXeH2FzwuJNu0k9YGbuInEXTDwl0_IapGjGqqPDtqNb_r7qzfgqg6a39swSdo1U-ItqNGUGHAn_kqcRIbY0XgHqmnqBFE_TT-kzFmILBzhNnew_PWbeP-5TjuQq4iohxotlFt1zAViEJy-X9EI18QOlnl-q47KKZdFwLj4N7iyMvcim3MvOc2OFRj8lCvexj_0EYsg8fKVeHVpGzPJmEqilkLO6-H2lhweIi-mEKDEnyhYFsNG_acEarGZ-5KpYoHUuUhazD6eNe1O-rmwhtF"
>
*/