<?php

namespace Planit;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class API {
  private $api;
  private $cookies;
  private $token;
  private $client;
  private $auth_promise;
  private $auth_result;
    
  function __construct($secretKey, $options) {
	if ((!isset($secretKey)) || (!$secretKey)) {
	  throw new Exception("no secret key");
	}

	$this->api = (isset($options['api'])) ? $options['api'] : 'https://plan-it-com.herokuapp.com';
	if(isset($options['ignoreSSL'])) $this->api = str_replace('https://', 'http://', $this->api);
	
	// can set your own cookies jar from guzzle
	$this->cookies = (isset($options['cookies_jar'])) ? isset($options['cookies_jar']) : true;
	
	$this->token = $secretKey;
	$this->client = new \GuzzleHttp\Client(['cookies' => $this->cookies, 'base_uri' => $this->api, 'http_errors' => false]);
	  
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
    return json_decode($this->client->request($method, $uri, $headers)->getBody()->getContents());
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
	  return $this->update('todos', $obj['_id'], $obj);
  }
  public function removeTodo($id) {
      return $this->remove('todos' . '/' . $id);
  }
  
  /**
   * can set an aaray of query-options:
   * client, isHeld, isPaid, startAt, endAt,
   * sort(default by startAt),
   * populate(currently only "client" is available)
  */ 
  public function getMeetings($query = array()) {
      return $this->get('meetings', $query);
  }
  public function createMeeting($obj) {
      return $this->create('meetings', $obj);
  }
  public function updateMeeting($obj) {
      return $this->update('meetings', $obj['_id'], $obj);
  }
  public function removeMeeting($id) {
      return $this->remove('meetings' . '/' . $id);
  }
  
  /**
   * optional query:
   * isInterested, isActive, select
   */
  public function getClients($query = array()) {
      return $this->get('clients', $query);
  }
  public function createClient($obj) {
      return $this->create('clients', $obj);
  }
  public function updateClient($obj) {
      return $this->update('clients', $obj['_id'], $obj);
  }
  public function removeClient($id) {
      return $this->remove('clients' . '/' . $id);
  }
  
  /**
   * amount can also be negative
   */
  public function addToClientBalance($clientId, $amount) {
      return $this->create('clients/' . $clientId . '/balance', [
          'client' => $clientId,
          'amount' => $amount
      ]);
  }
  
}
