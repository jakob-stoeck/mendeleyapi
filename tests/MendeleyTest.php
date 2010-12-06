<?php
class MendeleyTest extends UnitTestCase {
	private $tmp;
	private $mendeley;

	function __construct() {
		$this->mendeley = new Mendeley();
	}

	function testGetSharedCollectionDocuments() {
		$sharedCollectionId = 164791;
		$url = 'sharedcollections/' . $sharedCollectionId;
		$params = array('page' => 1, 'items' => 1);
		$result = $this->mendeley->get($url, $params);
		$this->assertEqual((int)$result->shared_collection_id, $sharedCollectionId);
	}
	
	/**
	 * Used to get as many distinct publication types as possible. Should only be used manually.
	 * @depends testGetSharedCollectionDocuments
	 */
	function getTypes() {
		$docs = array();
		foreach($result->document_ids as $id) {
			$docs[] = $this->mendeley->get('documents/' . $id);
		}
		
		$types = array();
		foreach($docs as $d) {
			$types[$d->type] = null;
		}
		
		echo var_dump($types);
	}

	function testCreateDocument() {
		$title = 'Example Title';
		$url = 'http://www.example.org/';
		$tags = array('a', 'b');
		$groupId = 504091;

		$doc = new MendeleyDoc();
		$doc->title = $title;
		$doc->url = $url;
		$doc->tags = $tags;
		$doc->group_id = $groupId;

		$result = $this->mendeley->post('documents/', $doc->toParams());

		$this->assertTrue(!empty($result));
		$this->assertTrue(isset($result->document_id) && is_numeric($result->document_id));

		$this->tmp['documentId'] = $result->document_id;
	}

	// Unauthorized calls to document details don't work, because i don't get the document id as described here http://dev.mendeley.com/docs/user-specific-resources/user-library-document-details
	// function testGetDetailsUnauthorized() {
	// 	$url = 'documents/details/' . $this->tmp['documentId'];
	//
	// 	$result = $this->mendeley->get($url, array(), false);
	// 	$this->assertEqual($result->title, 'Example Title');
	// }

	function testGetDocDetails() {
		$title = 'Example Title';
		$url = 'http://www.example.org/';
		$tags = array('a', 'b');

		$result = $this->mendeley->get('documents/' . $this->tmp['documentId']);
		$this->assertEqual($result->title, $title);
		$this->assertEqual($result->url, $url);
		$this->assertEqual($result->tags, $tags);
	}

	function testGetSearchUnauthorized() {
		$url = 'documents/search/' . urlencode('Example Title');
		$itemsPerPage = 5;
		$result = $this->mendeley->get($url, array('items' => $itemsPerPage), false);
		$this->assertEqual($result->items_per_page, $itemsPerPage);
	}

	function testCreateCollection() {
		$sharedCollection = array(
			'sharedcollection' =>
			array(
				'name' => 'Example Collection'
			)
		);
		
		try {
			$result = $this->mendeley->post('sharedcollections', $sharedCollection);
			$this->assertTrue(isset($result->shared_collection_id));
			$this->tmp['shared_collection_id'] = $result->shared_collection_id;
		} catch(Exception $e) {
			var_dump($e->getMessage());
		}
	}

	function testDeleteCollection() {
		$response = $this->mendeley->delete('sharedcollections/' . $this->tmp['shared_collection_id']);
		$this->assertTrue(empty($response));
	}

	function testDeleteDocument() {
		$this->assertTrue(isset($this->tmp['documentId']) && is_numeric($this->tmp['documentId']));
		$response = $this->mendeley->delete('documents/' . $this->tmp['documentId']);
		$this->assertTrue(empty($response));
	}

	function testGetGroupDocuments() {
		$response = $this->mendeley->getGroupDocuments(164791);
		$this->assertTrue(isset($response->total_results));
		$this->assertEqual($response->group_type, 'sharedcollections');
	}

	function testGetManyDocuments() {
		$many = 10000;
		$response = $this->mendeley->getGroupDocuments(164791, array('items' => $many));
		$this->assertEqual($response->total_pages, 1);
		$this->assertTrue($response->total_results < $many);
		$this->assertEqual(count($response->document_ids), $response->total_results);
	}
}
