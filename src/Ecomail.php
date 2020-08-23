<?php declare(strict_types = 1);

/**
 * API DOCS https://ecomailczv2.docs.apiary.io/
 */

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

	/**
	 * Sends transactiona e-mail with ecomail template
	 */
	public function sendTransactionalTemplate(array $data)
	{
		$url = self::URL . 'transactional/send-template';

		$post = [
			'message' => [
				'template_id' => $data['message']['template_id'],
				'subject' => $data['message']['subject'],
				'from_name' => $data['message']['from_name'],
				'from_email' => $data['message']['from_email'],
				'reply_to' => $data['message']['reply_to'],
				'to' => [
					'email' => $data['message']['to']['email'],
					'name' => $data['message']['to']['name'],
					'cc' => $data['message']['to']['cc'],
					'bcc' => $data['message']['to']['bcc'],
				],
				'attachments' => [],
				'global_merge_vars' => [],
				'metadata' => [],
			]
		];

		// attachments
		if (isset($data['message']['attachments']) && is_array($data['message']['attachments'])) {
			foreach($data['message']['attachments'] as $key => $val) {
				$post['message']['attachments'][$key]['type'] = $val['type'];
				$post['message']['attachments'][$key]['type'] = $val['name'];
				$post['message']['attachments'][$key]['type'] = $val['content'];
			}			
		}

		// global merge vars
		if (isset($data['message']['global_merge_vars']) && is_array($data['message']['global_merge_vars'])) {
			foreach($data['message']['global_merge_vars'] as $key => $val) {
				$post['message']['global_merge_vars'][$key]['name'] = $val['name'];
				$post['message']['global_merge_vars'][$key]['content'] = $val['content'];
			}			
		}

		// metadata
		if (isset($data['message']['metadata']) && is_array($data['message']['metadata'])) {
			foreach($data['message']['metadata'] as $key => $val) {
				$post['message']['metadata'][$key][$val['key']] = $val['value'];
			}			
		}

		$post = json_encode($post);

		return $this->sendRequest($url, 'POST', $post);
	}

	/**
	 * Sends transactiona e-mail with html
	 */
	public function sendTransactional(array $data)
	{
		$url = self::URL . 'transactional/send-message';

		$post = [
			'message' => [
				'subject' => $data['subject'],
				'from_name' => $data['from_name'],
				'from_email' => $data['from_email'],
				'reply_to' => $data['reply_to'],
				'text' => $data['text'],
				'html' => $data['html'],
				'amp_html' => $data['amp_html'],
				'to' => [
					'email' => $data['email'],
					'name' => $data['name'],
					'cc' => $data['cc'],
					'bcc' => $data['bcc'],
				],
				'attachments' => [],
				'global_merge_vars' => [],
				'metadata' => [],
				'options' => [],
			]
		];

		// attachments
		if (isset($data['message']['attachments']) && is_array($data['message']['attachments'])) {
			foreach($data['message']['attachments'] as $key => $val) {
				$post['message']['attachments'][$key]['type'] = $val['type'];
				$post['message']['attachments'][$key]['type'] = $val['name'];
				$post['message']['attachments'][$key]['type'] = $val['content'];
			}			
		}

		// global merge vars
		if (isset($data['message']['global_merge_vars']) && is_array($data['message']['global_merge_vars'])) {
			foreach($data['message']['global_merge_vars'] as $key => $val) {
				$post['message']['global_merge_vars'][$key]['name'] = $val['name'];
				$post['message']['global_merge_vars'][$key]['content'] = $val['content'];
			}			
		}

		// metadata
		if (isset($data['message']['metadata']) && is_array($data['message']['metadata'])) {
			foreach($data['message']['metadata'] as $key => $val) {
				$post['message']['metadata'][$key][$val['key']] = $val['value'];
			}			
		}

		// options
		if (isset($data['message']['options']) && is_array($data['message']['options'])) {
			foreach($data['message']['options'] as $key => $val) {
				$post['message']['options'][$key]['click_tracking'] = $val['click_tracking'];
				$post['message']['options'][$key]['open_tracking'] = $val['open_tracking'];
			}			
		}

		$post = json_encode($post);

		return $this->sendRequest($url, 'POST', $post);
	}
}
