<?php
/**
 *   Mendeley API Client
 *
 *   Copyright (C) 2010  Jakob Stoeck
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License along
 *   with this program; if not, write to the Free Software Foundation, Inc.,
 *   51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 */

class Mendeley {
	const MENDELEY_REQUEST_TOKEN_ENDPOINT = 'http://www.mendeley.com/oauth/request_token/';
	const MENDELEY_ACCESS_TOKEN_ENDPOINT = 'http://www.mendeley.com/oauth/access_token/';
	const MENDELEY_AUTHORIZE_ENDPOINT = 'http://www.mendeley.com/oauth/authorize/';
	const MENDELEY_OAPI_PRIVATE_URL = 'http://www.mendeley.com/oapi/library/';
	const MENDELEY_OAPI_PUBLIC_URL = 'http://www.mendeley.com/oapi/';

	/**
	 * @var OAuthConsumer $consumer
	 */
	private $consumer;
	/**
	 * @var OAuthToken $accessToken
	 */
	private $accessToken;
	/**
	 * @var OAuthSignatureMethod $signatureMethod
	 */
	private $signatureMethod;
	private $cache;

	/**
	 * @param string
	 * 	consumer key, you may optionally give a consumer key and secret to the class constructor which overrides the default values in the configuration file.
	 * @param string
	 * 	consumer secret
	 */
	public function __construct($consumerKey = null, $consumerSecret = null) {
		require_once 'Configuration.php';
		require_once Configuration::getPathToOauth();

		if($consumerKey !== null && $consumerSecret !== null) {
			$consumer = array('key' => $consumerKey, 'secret' => $consumerSecret);
		} else {
			$consumer = Configuration::getConsumer();
		}

		$this->consumer = new OAuthConsumer($consumer['key'], $consumer['secret'], null);
		$this->signatureMethod = new OAuthSignatureMethod_HMAC_SHA1();
		$this->cache = new MendeleyCache('_' . md5($this->consumer->key));
	}

	/**
	 * Returns true if we are on the OAuth callback page, i.e. the user granted permissions
	 *
	 * @return boolean
	 */
	private function isActiveCallback() {
		return isset($_GET['oauth_verifier']);
	}

	/**
	 * @todo what if access token no longer valid?
	 */
	private function getAccessToken() {
		if(!$this->accessToken) {
			$access_cache = $this->cache->get('access_token');

			if(isset($access_cache['oauth_token'])) {
				$this->accessToken = new OAuthToken($access_cache['oauth_token'], $access_cache['oauth_token_secret']);
			} else {
				// get request token
				$request = $this->cache->get('request_token');

				if($request === false) {
					$request = $this->oauthTokenRequest(null, self::MENDELEY_REQUEST_TOKEN_ENDPOINT);
					$this->cache->set('request_token', $request);
				}

				$request_token = new OAuthToken($request['oauth_token'], $request['oauth_token_secret']);

				if(!$this->isActiveCallback()) {
					// authorize
					$callback_url = sprintf('http://%s%s', $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);
					$auth_url = self::MENDELEY_AUTHORIZE_ENDPOINT . '?oauth_token=' . urlencode($request_token->key) . '&oauth_callback=' . urlencode($callback_url);
					header('Location: ' . $auth_url); exit;
				} else {
					// get access token
					if($_REQUEST['oauth_token'] !== $request_token->key) {
						throw new Exception('Request token is wrong, please try again. This could happen if you open a strange URL. Please open the basis URL with no query arguments attached instead.');
					}
					$request = $this->oauthTokenRequest($request_token, self::MENDELEY_ACCESS_TOKEN_ENDPOINT, array('oauth_verifier' => $_GET['oauth_verifier']));
					$this->cache->set('access_token', $request);
					$this->cache->del('request_token');
					$this->accessToken = new OAuthToken($request['oauth_token'], $request['oauth_token_secret']);
				}
			}
		}

		return $this->accessToken;
	}

	/**
	 * Call Mendeley API
	 *
	 * You should cache frequent calls to this method in your application. At least GET calls.
	 *
	 * @param string $method
	 * @param string $url
	 * @param array $params
	 * @param boolean $authenticate
	 */
	private function http($method, $url, $params = array(), $authentication = true) {
		if(!is_array($params)) {
			throw new Exception('HTTP params need to be array in Mendeley::http');
		}

		if($authentication) {
			$url = self::MENDELEY_OAPI_PRIVATE_URL . $url;
			$token = $this->getAccessToken($this->signatureMethod, $this->consumer);
			$request = OAuthRequest::from_consumer_and_token($this->consumer, $token, $method, $url, $params);
			$request->sign_request($this->signatureMethod, $this->consumer, $token);
		} else {
			$url = self::MENDELEY_OAPI_PUBLIC_URL . $url;
			$params['consumer_key'] = $this->consumer->key;
			$request = new OAuthRequest($method, $url, $params);
		}

		if($method === 'GET') {
			$url = $request->to_url();
		} else {
			$url = $request->get_normalized_http_url();
			$params = $request->to_postdata();
		}

		if($request = MendeleyUtil::runCurl($url, $method, array(), $params)) {
			$request = json_decode($request);
		}

		return $request;
	}

	/**
	 * convenience method, @see Mendeley::http
	 */
	public function get($url, $params = array(), $authentication = true) {
		return $this->http('GET', $url, $params, $authentication);
	}

	/**
	 * convenience method, @see Mendeley::http
	 */
	public function post($url, $params = array()) {
		foreach($params as $key => &$value) {
			$value = json_encode(array_filter($value));
		}
		return $this->http('POST', $url, $params);
	}

	/**
	 * convenience method, @see Mendeley::http
	 */
	public function delete($url, $params = array()) {
		return $this->http('DELETE', $url, $params);
	}

	/**
	 * Enrich a group with its document details instead of only document ids
	 *
	 * @param string $collectionUrl
	 * 	works with sharedcollection/id and collection/id
	 * @param array $params
	 */
	public function getCollection($collectionUrl, $params = array()) {
		$collection = $this->get($collectionUrl, $params);
		$collection->documents = $this->loadDocumentDetails($collection->document_ids);
		return $collection;
	}

	/**
	 * Returns document content for all document ids
	 *
	 * @param array $documentIds
	 * 	array of integers
	 * @return array
	 */
	private function loadDocumentDetails($documentIds) {
		$documents = array();
		foreach($documentIds as $id) {
			$documents[$id] = $this->get('documents/' . $id);
		}
		return $documents;
	}

	/**
	 * Get collection details by knowing only the group id, not the collection type
	 *
	 * The Mendeley API differentiates groups between shared collections and collections but there is no API way to get the type of a collection by their group id. So we use this method to get a group transparently.
	 *
	 * @return StdClass
	 * 	Output of get('(shared)collections/$groupId') and an added 'group_type'
	 */
	public function getGroupDocuments($groupId, $params = array()) {
		$groupTypes = array(
			'sharedcollections',
			'collections'
		);

		foreach($groupTypes as $g) {
			$collection = $this->get($g . '/' . $groupId, $params);
			if(isset($collection->total_results)) {
				$collection->group_type = $g;
				return $collection;
			}
		}
	}

	/**
	 * Gets OAuth (request or access) token
	 *
	 * @param OAuthSignatureMethod $this->signatureMethod
	 * @param OAuthConsumer $consumer
	 * @param OAuthToken $token
	 * @param string $url
	 * @param array $params
	 * @return array
	 * 	instantiate like this: new OAuthToken($return['oauth_token'], $return['oauth_token_secret']);
	 */
	private function oauthTokenRequest($token, $url, $params = array()) {
		$acc_req = OAuthRequest::from_consumer_and_token($this->consumer, $token, 'GET', $url, $params);
		$acc_req->sign_request($this->signatureMethod, $this->consumer, $token);
		$request = MendeleyUtil::runCurl($acc_req->to_url(), 'GET');
		return OAuthUtil::parse_parameters($request);
	}
}

class MendeleyUtil {
	/**
	 * Executes a CURL request
	 *
	 * @param $url string
	 * 	URL to make request to
	 * @param $method string
	 * 	HTTP transfer method
	 * @param $headers
	 * 	HTTP transfer headers
	 * @param $postvals
	 * 	post values
	 * @return string
	*/
	public static function runCurl($url, $method = 'GET', $headers = array(), $postvals = null){
		$ch = curl_init();
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		curl_setopt($ch, CURLOPT_VERBOSE, true);

		switch($method) {
			case 'POST':
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postvals);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			break;
			case 'DELETE':
				if(!empty($postvals)) {
					$url = $url . '?' . $postvals;
				}
			break;
		}

		curl_setopt($ch, CURLOPT_URL, $url);
		$response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if($text = MendeleyUtil::isError($http_code)) {
			if(Configuration::DEBUG > 0) {
				var_dump(compact('http_code'));
			}
			$response = json_decode($response);
			throw new Exception(sprintf('Error %d (%s): %s', $http_code, $text, $response->error));
			$response = false;
		}

		return $response;
	}

	/**
	 * Returns text-equivalent of HTTP error codes or false if no error
	 * Messages from http://dev.mendeley.com/docs/http-responses-and-errors
	 *
	 * @param int $http_code
	 * @return mixed
	 */
	private static function isError($http_code) {
		switch($http_code) {
			case 200: // ok
			case 201: // created (post header)
			case 204: // no content
				return false;
			break;
			case 400:
				return 'Bad Request: The request was invalid. An accompanying error message will explain why.';
			break;
			case 401:
				return 'Unauthorized: Authentication credentials were missing or incorrect.';
			break;
			case 403:
				return 'Forbidden: The request is understood, but it has been refused. An accompanying error message will explain why. This code is used when requests are being denied due to rate limiting.';
			break;
			case 404:
				return 'Not Found: The URI requested is invalid or the resource requested doesn\'t exist.';
			break;
			case 503:
				return 'Service Unavailable: Mendeley is up, but something went wrong, please try again later.';
			break;
			default:
				return 'Unknown Error';
		}
	}

	/**
	 * Returns document detail keys (there is no documentation about those)
	 */
	public static function arrayKeysRecursive($array) {
		$keys = array();
		foreach($array as $key => $value) {
			$keys = array_merge($keys, array_keys((array)$value));
		}
		return array_unique($keys);
	}
}

/**
 * Represents a document received from the API
 */
class MendeleyDoc {
	public $abstract;
	public $authors;
	public $city;
	public $country;
	public $discipline;
	public $documentId;
	public $doi;
	public $edition;
	public $editors;
	public $genre;
	public $groupId;
	public $identifiers;
	public $institution;
	public $isbn;
	public $issn;
	public $issue;
	public $keywords;
	public $notes;
	public $pages;
	public $pmid;
	public $publication_outlet; // this is snake_case because it's like that in the API
	public $publisher;
	public $tags;
	public $title;
	public $type;
	public $url;
	public $volume;
	public $year;

	/**
	 * Instantiate a Mendeley Document by its internal document id
	 *
	 * @param string $documentId
	 * 	sent by Mendeley in e.g. collections/*collectionId*
	 */
	public static function constructWithDocumentId($documentId) {
		$that = new MendeleyDoc();
		$mendeley = new Mendeley();

		if($remote = $mendeley->get('documents/' . $documentId)) {
			$localParams = array_keys(get_object_vars($that));
			$remoteParams = array_keys(get_object_vars($remote));
			$match = array_intersect($localParams, $remoteParams);
			foreach($match as $name) {
				if(!empty($remote->$name)) {
					$that->$name = $remote->$name;
				}
			}
			$that->documentId = $documentId;
		}
		return $that;
	}

	public function toJson() {
		return json_encode($this);
	}

	public function toParams() {
		return array('document' => (array)$this);
	}
}

/**
 * Helper to cache arbitrary variables esp. oauth tokens in the file system
 */
class MendeleyCache {
	private $dir;
	private $suffix;

	/**
	 * @param string $suffix
	 * 	is added to the cache files, can be used to cache tokens for multiple consumer
	 */
	public function __construct($suffix = '') {
		$this->dir = dirname(__FILE__) . '/cache/';
		$this->suffix = $suffix;
	}

	public function get($name) {
		if(@$cache = file_get_contents($this->dir . $name . $this->suffix)) {
			return unserialize($cache);
		} else {
			return false;
		}
	}

	public function del($name) {
		@unlink($this->dir . $name . $this->suffix);
	}

	public function set($name, $value) {
		$success = $this->filePutContentsWithDir($this->dir . $name . $this->suffix, serialize($value));

		if($success == false) {
			throw new Exception(sprintf('Could not create directory or file: %s. Please check the permissions.', $this->dir . $name . $this->suffix));
		}
	}

	public function getDir() {
		return $this->dir;
	}

	/**
	 * File put contents fails if you try to put a file in a directory that doesn't exist. This creates the directory and the file.
	 * @see http://www.php.net/manual/de/function.file-put-contents.php#84180 TrentTompkins at gmail dot com
	 */
	private function filePutContentsWithDir($dir, $contents){
		$success = true;
		$parts = explode('/', $dir);
		$file = array_pop($parts);
		$dir = '';
		foreach($parts as $part) {
			if(!is_dir($dir .= '/' . $part)) {
				$success = mkdir($dir) && $success;
			}
		}

		$success = file_put_contents($dir . '/' . $file, $contents) && $success;

		return $success;
	}
}
