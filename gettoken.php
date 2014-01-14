<?php

/* gettoken.php - get a token with the authorization code grant type */

class TradeStationWebAPI {
  private $_clientId;
  private $_clientSecret;
  private $_baseurl;
  private $_redirectUri;

  public function __construct ($client_id, $client_secret, $environment, $redirect_url) {
    $this->_clientId = $client_id;
    $this->_clientSecret = $client_secret;
    $this->_baseurl = $environment;
    $this->_redirectUri = $redirect_url;
  }

  public function setAccessToken ($code) {
    $request = $this->initCurl($this->_baseurl . "security/authorize");

    // Assemble POST parameters for the request.
    $post_fields = "grant_type=authorization_code&code=" . $code .
      "&client_id=" . $this->_clientId .
      "&client_secret=" . $this->_clientSecret .
      "&redirect_uri=" . $this->_redirectUri;

    // Obtain and return the access token from the response
    curl_setopt($request, CURLOPT_POST, true);
    curl_setopt($request, CURLOPT_POSTFIELDS, $post_fields);

    $response = curl_exec($request);
    if ($response == false) {
      die("curl_exec() failed. Error: " . curl_error($request));
    }

    // Return JSON token
    $this->_token = $response;
  }

  public function refreshToken ($refresh) {
    $request = $this->initCurl($this->_baseurl . "security/authorize");

    // Assemble POST parameters for the request.
    $post_fields = "grant_type=refresh_token&refresh_token=" . $refresh .
      "&client_id=" . $this->_clientId .
      "&client_secret=" . $this->_clientSecret .
      "&redirect_uri=" . $this->_redirectUri;

    // Obtain and return the access token from the response
    curl_setopt($request, CURLOPT_POST, true);
    curl_setopt($request, CURLOPT_POSTFIELDS, $post_fields);

    $response = curl_exec($request);
    if ($response == false) {
      die("curl_exec() failed. Error: " . curl_error($request));
    }

    // Return JSON token
    $this->_token = $response;
  }

  public function getAccessToken () {
    return $this->_token;
  }

  public function getBarchartHistory ($symbol) {
    // Initialize curl
    $request = $this->initCurl($this->_baseurl . "stream/barchart/" . $symbol . "/5/Minute/11-21-2013/11-26-2013");

    // Add token
    curl_setopt($request, CURLOPT_HTTPHEADER, array ( "Authorization: Bearer " . json_decode($this->_token)->access_token ));

    $response = curl_exec($request);
    if ($response == false) {
      die("curl_exec() failed. Error: " . curl_error($request));
    }

    return $response;
  }

  private function initCurl ($url) {
    $request = null;

    if (($request = @curl_init($url)) == false) {
      header("HTTP/1.1 500", true, 500);
      die("Cannot initialize cUrl session. Is cUrl enabled for PHP?");
    }

    curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($request, CURLOPT_ENCODING, 1);

    return $request;
  }

}

$apikey = ""; // set this to your API Key
$apisecret = ""; // set this to your API Secret
$baseurl = "https://sim.api.tradestation.com/v2/"; // change this to LIVE when you're ready: https://api.tradestation.com/v2/
$redirecturi = urlencode(strtok("$_SERVER[HTTP_REFERER]", '?'));
$webapi = new TradeStationWebAPI($apikey, $apisecret, $baseurl, $redirecturi);

function user_login($baseurl, $apikey, $redirecturi) {
  header("Location: " . $baseurl . "/authorize?client_id=" . $apikey . "&response_type=code&redirect_uri=" . $redirecturi);
}

if (isset($_GET["code"])) {

  // Swap authorization code for access token
  $webapi->setAccessToken($_GET["code"]);

  if (isset(json_decode($webapi->getAccessToken())->access_token)) {
    header('Content-Type: application/json');
    echo $webapi->getAccessToken();
  } else {
    // authorization code must have expired, so re-login
    user_login($baseurl, $apikey, $redirecturi);
  }

} elseif (isset($_GET["refresh"])) {
  // use refresh_token to generate access_token
  $webapi->refreshToken($_GET["refresh"]);

  if (isset(json_decode($webapi->getAccessToken())->access_token)) {
    if (isset($_GET["history"])) {
      echo $webapi->getBarchartHistory($_GET["history"]);
    } else {
      header('Content-Type: application/json');
      echo $webapi->getAccessToken();
    }

  } else {
    // refresh token must have been invalidated, so re-login
    user_login($baseurl, $apikey, $redirecturi);
  }

} else {
  // redirect to login page, a successful login will reload this page with an authorization code
  user_login($baseurl, $apikey, $redirecturi);
}

?>