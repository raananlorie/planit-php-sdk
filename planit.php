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
	$this->client = new \GuzzleHttp\Client(['cookies' => true, 'base_uri' => $this->api, 'http_errors' => false]);
	  
	$this->auth_promise = $this->client->requestAsync('POST', 'auth/token', ['form_params' => ['token' => $this->token]]);
	$this->auth_promise->then(function($r) {
		$this->auth_result = ($r->getStatusCode() == 200);
	  }, function() {
		$this->auth_result = false;
	});
  }
  
  private function is_auth() {
    if(isset($this->auth_result)) return $this->auth_result;
    $this->auth_promise->wait();
    return $this->auth_result;
  }
  
  private function request($uri, $method = 'GET', $headers = array()) {
    if(!$this->is_auth()) return false;
    return $this->client->request($method, $uri, $headers)->getBody()->getContents();
  }
  
  private function get($resource, $params = null) {
	  if(isset($params)) {
		  if(is_string($params)) {
			  $resource = $resource . '/' .$params;
		  } else {
			  $query = (is_array($params)) ? $params : [];
		  }
	  } else {
		  $query = null;
	  }
	  return $this->request($resource, 'GET', ['query' => $query]);
  }
  
  private function create($resource, $body, $query = array()) {
	  return $this->request($resource, 'POST', [
		  'query' => $query,
		  'form_params' => $body
	  ]);
  }
  
  private function update($resource, $id, $body, $query = array()) {
	  return $this->request($resource . '/' . $id, 'PUT', [
		  'query' => $query,
		  'form_params' => $body
	  ]);
  }
  
  private function remove($resource, $id) {
	  return $this->request($resource . '/' . $id, 'DELETE');
  }
  
  // API
  public function getTodos() {
	  return $this->get('todos');
  } 
  public function createTodo($note = '', $isDone = false, $client = null) {
	  $obj = [
		'note' => $note,
		'isDone' => $isDone
	  ];
	  if(isset($client)) $obj['client'] = $clientId;
	  return $this->create('todos', $obj);
  }
  public function updateTodo($obj) {
	  return $this->update('todos', $obj['id'], $obj);
  }
  
}
