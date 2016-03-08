<?php

namespace Planit;

use GuzzleHttp\Client;

class API {
  private $api;
  private $token;
  private $client;
  
  function __construct($secretKey, $api) {
      if ((!isset($secretKey)) || (!$secretKey)) {
          throw new Exception("no secret key");
      }
      
      $this->api = $api ? $api : 'https://plan-it-com.herokuapp.com';
      $this->token = $secretKey;
      $this->client = new \GuzzleHttp\Client(['cookies' => true, 'base_uri' => $this->api]);
      $client->requestAsync('POST', 'auth/token', ['body' => ['token' => $this->token]]);
  }
}
