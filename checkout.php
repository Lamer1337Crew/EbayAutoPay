<?php
/*

Auto Pay / Buy Items in Ebay.com Website
Author : FO (ALECTRA)
https://web.facebook.com/s7akeholder

*/

set_time_limit(0); 

$cookies = getcwd() . "/otobay.log";

if(file_exists($cookies)){
	unlink($cookies);
}

$get = http('https://pay.ebay.com/rgxo?action=create&rypsvc=true&pagename=ryp&TransactionId=-1&rev=8&item=163404756063&quantity=1&qty=1&var=462724642770');

if($get){
	
	$ajaxToken = mid($get, '"ajaxCSRFToken":"', '"'); // Ajax Token
	$sessID = mid($get, '"sessionId":"', '"'); // Session ID
	$buyToken = mid($get, 'name="srt" value="', '"'); // checkout / Confirm Token
	
	$addShipping = addShip($sessID, $ajaxToken); // Add Shipping
	
	if($addShipping){
		$addPayment = addPayment($sessID, $ajaxToken); // Add Billing + CC Payment
		if($addPayment){
			$checkOut = checkOut($sessID, $buyToken); // checkOut
			echo getMsg($checkOut);
		}
	}
}


function addShip($sessionID, $srt){
	
	// $shipping = Shipping address
	
	$shipping = '{"lastName":"AkhirNama","makePrimary":"false","addressType":"SHIPPING","emailConfirm":"emailku@cobapay.com","addressLine2":"Jalan nama2","addressLine1":"Jalan Nama","country":"ID","city":"Kotaku","postalCode":"11430","phoneNumber":"283969587236","email":"emailku@cobapay.com","disableValidation":"false","stateOrProvince":"Regionku","firstName":"AwalNama","sessionid":"'.$sessionID.'","srt":"'.$srt.'","pageType":"ryp"}';
	$headers = array(
	'Host: pay.ebay.com',
	'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 11_0_1 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A402 Safari/604.1',
	'Accept: application/json, text/plain, */*',
	'Accept-Language: en-US,en;q=0.5',
	'Referer: https://pay.ebay.com/rgxo?action=view&sessionid=' . $sessionID,
	'Content-Type: application/json;charset=utf-8',
	'Content-Length: ' . strlen($shipping),
	'DNT: 1',
	'Connection: keep-alive'
	);
	
	return http('https://pay.ebay.com/rgxo/ajax?action=addAddress', $shipping, $headers);
}

function getMsg($source){
	preg_match_all('|<span class="">(.*?)</span>|', $source, $msg);
	return $msg[1][0];
}

function addPayment($sessionID, $srt){
	
	// $shipping = Billing + CC Payment
	
	$billing = '{"cardHolderLastName":"Lopez","state":"IN","emailConfirm":"emailku@cobapay.com","addressType":"BILLING","shippingSameAsBilling":"false","country":"US","city":"Siti ku","addrLine1":"jl mari","addrLine2":"","cardExpiryYear":"21","postalCode":"34654","phoneNumber":"547568679879","email":"emailku@cobapay.com","paymentMethodId":"CC","cardExpiryMonth":"01","cardHolderFirstName":"Humberto ","cardNumber":"4523998770451727","securityCode":"376","":"","pmMethod":"CC","cardExpiryDate":"","sessionid":"'.$sessionID.'","srt":"'.$srt.'","pageType":"ryp"}';
	
	$headers = array(
	'Host: pay.ebay.com',
	'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 11_0_1 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A402 Safari/604.1',
	'Accept: application/json, text/plain, */*',
	'Accept-Language: en-US,en;q=0.5',
	'Referer: https://pay.ebay.com/rgxo?action=view&sessionid=' . $sessionID,
	'Content-Type: application/json;charset=utf-8',
	'Content-Length: ' . strlen($billing),
	'DNT: 1',
	'Connection: keep-alive'
	);
	
	return http('https://pay.ebay.com/rgxo/ajax?action=addPaymentInstrument', $billing, $headers);
}

function checkOut($sessionID, $buyToken){
	
	$params = "srt={$buyToken}";
	
	$headers = array(
	'Host: pay.ebay.com',
	'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 11_0_1 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A402 Safari/604.1',
	'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
	'Accept-Language: en-US,en;q=0.5',
	'Referer: https://pay.ebay.com/rgxo?action=view&sessionid=' . $sessionID,
	'Content-Type: application/x-www-form-urlencoded',
	'Content-Length: ' . strlen($params),
	'DNT: 1',
	'Connection: keep-alive',
	'Upgrade-Insecure-Requests: 1'
	);
	
	return http('https://pay.ebay.com/rgxo?action=confirm&sessionid=' . $sessionID, $params, $headers);
}


function http($url, $post = false, $headers = false)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_COOKIEJAR, getcwd() . "/otobay.log");
    curl_setopt($ch, CURLOPT_COOKIEFILE, getcwd() . "/otobay.log");
	
    if ($post) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    
    if ($headers) {
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function mid($string, $start, $end)
{
    $string = ' ' . $string;
    $ini    = strpos($string, $start);
    if ($ini == 0)
        return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}
