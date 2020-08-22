<?php

namespace Martinsvacinka\EcomailNette;

class Ecomail
{
	private $key;
	
	const URL = 'http://api2.ecomailapp.cz/';
	

	public function __construct($key) {
		if(empty($key)) {
			throw new \Exception('You must specify Ecomail API_KEY.');
		}
		$this->key = $key;
	}
	
	private function sendRequest($url, $request = 'POST', $data = '') {

		$http_headers = array();
		$http_headers[] = "key: " . $this->key;
		$http_headers[] = "Content-Type: application/json";
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
		
		if(!empty($data)) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			
			if($request == 'POST') {
				curl_setopt($ch, CURLOPT_POST, TRUE);
			} else {
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request);
			}
		}
		
		$result = curl_exec($ch);
		curl_close($ch);
		
		return json_decode($result);
	}
	
	public function getLists() {
		
		$url = self::URL . 'lists';
		
		return $this->sendRequest($url);
	}

	public function getList($id) {
		
		$url = self::URL . 'lists/' . $id;
		
		return $this->sendRequest($url);
	}
	
	public function getSubscribers($list_id, $page = 1) {
		
		$url = self::URL . 'lists/' . $list_id . '/subscribers' . ($page > 1 ? '?page=' . $page : '');
		
		return $this->sendRequest($url);
	}
	
	public function getSubscriber($list_id, $email) {
		
		$url = self::URL . 'lists/' . $list_id . '/subscriber/' . $email;
		
		return $this->sendRequest($url);
	}
	
	public function addSubscriber($list_id, $data = array(), $trigger_autoresponders = FALSE, $update_existing = TRUE, $resubscribe = FALSE) {
		
		$url = self::URL . 'lists/' . $list_id . '/subscribe';
		$post = json_encode(array(
			'subscriber_data' => array(
				'name' => $data['name'],
				'surname' => $data['surname'],
				'email' => $data['email'],
				'vokativ' => $data['vokativ'],
				'vokativ_s' => $data['vokativ_s'],
				'company' => $data['company'],
				'city' => $data['city'],
				'street' => $data['street'],
				'zip' => $data['zip'],
				'country' => $data['country'],
				'phone' => $data['phone'],
				'pretitle' => $data['pretitle'],
				'surtitle' => $data['surtitle'],
				'birthday' => $data['birthday'],
				'custom_fields' => (array)$data['custom_fields'],
			),
			'trigger_autoresponders' => $trigger_autoresponders,
			'update_existing' => $update_existing,
			'resubscribe' => $resubscribe
		));
		
		return $this->sendRequest($url, 'POST', $post);
	}
	
	public function deleteSubscriber($list_id, $email) {
		
		$url = self::URL . 'lists/' . $list_id . '/unsubscribe';
		$post = json_encode(array('email' => $email ));
		
		return $this->sendRequest($url, 'DELETE', $post);
	}
	
	public function updateSubscriber($list_id, $data = array()) {
		
		$url = self::URL . 'lists/' . $list_id . '/update-subscriber';
		$post = json_encode(array(
			'email' => $data['email'],
			'subscriber_data' => array(
				'name' => $data['name'],
				'surname' => $data['surname'],
				'vokativ' => $data['vokativ'],
				'vokativ_s' => $data['vokativ_s'],
				'company' => $data['company'],
				'city' => $data['city'],
				'street' => $data['street'],
				'zip' => $data['zip'],
				'country' => $data['country'],
				'phone' => $data['phone'],
				'pretitle' => $data['pretitle'],
				'surtitle' => $data['surtitle'],
				'birthday' => $data['birthday'],
				'custom_fields' => (array)$data['custom_fields'],
			)
		));
		
		return $this->sendRequest($url, 'PUT', $post);
	}
}
