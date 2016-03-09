<?php

namespace Planit;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class API {
  private $api;
  private $token;
  private $client;
  private $auth_promise;
  private $auth_result;
  
  function __construct($secretKey, $api) {
      if ((!isset($secretKey)) || (!$secretKey)) {
          throw new Exception("no secret key");
      }
      
      $this->api = $api ? $api : 'https://plan-it-com.herokuapp.com';
      $this->token = $secretKey;
      $this->client = new \GuzzleHttp\Client(['cookies' => true, 'base_uri' => $this->api]);
      $this->auth_promise = $this->client->requestAsync('POST', 'auth/token', ['body' => ['token' => $this->token]]);
      $this->auth_promise->then(function() {
        $this->auth_result = true;
      }, function() {
        $this->auth_result = false;
      });
  }
  
  private function is_auth() {
    if(isset($this->auth_result)) return $this->auth_result;
    $this->auth_promise->wait();
    return $this->auth_result;
  }
  
  private function request($uri, $method = 'GET', $headers) {
    if(!$this->is_auth()) return false;
    return $this->client->request($method, $uri, $headers);
  }
}
