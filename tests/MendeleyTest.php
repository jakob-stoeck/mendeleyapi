<?php
class MendeleyTest extends UnitTestCase {
	private $tmp;
	private $mendeley;

	function __construct() {
		$this->mendeley = new Mendeley();
	}

	function testGet() {
		$groupId = 164791;
		$url = 'groups/' . $groupId;
		$params = array('page' => 1, 'items' => 1);
		$result = $this->mendeley->get($url, $params);
		$this->assertEqual((int)$result->group_id, $groupId);
	}

	function testMendeleyConstructorUsesRightConsumer() {
		$mendeley = new Mendeley('abc', '123');
		$consumer = $mendeley->getConsumer();
		$this->assertEqual('abc', $consumer->key);
		$this->assertEqual('123', $consumer->secret);
	}

	function testMendeleyWithCustomConstructorWorks() {
		$consumer = Configuration::getConsumer();
		$mendeley = new Mendeley($consumer['key'], $consumer['secret']);

		$groupId = 164791;
		$url = 'groups/' . $groupId;
		$params = array('page' => 1, 'items' => 1);
		$result = $mendeley->get($url, $params);
		$this->assertEqual((int)$result->group_id, $groupId);
	}

	/**
	 * Used to get as many distinct publication types as possible. Should only be used manually.
	 * @depends testGetGroupDocuments
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

	function testCreateGenericDocument() {
		$title = 'Example Title';
		$tags = array('a', 'b');
		$groupId = 504091;
		$type = 'Generic';

		$doc = new MendeleyDoc();
		$doc->title = $title;
		$doc->tags = $tags;
		$doc->group_id = $groupId;
		$doc->type = $type;

		$result = $this->mendeley->post('documents/', $doc->toParams());

		$this->assertTrue(!empty($result));
		$this->assertTrue(isset($result->document_id) && is_numeric($result->document_id));

		$this->tmp['documentId'] = $result->document_id;
	}

	// Unauthorized calls to document details don't work, because i don't get the document id as described here http://dev.mendeley.com/docs/user-specific-resources/user-library-document-details
	// function testGetDetailsUnauthorized() {
	// 	$url = 'documents/details/' . $this->tmp['documentId'];
	// 
	// 	$result = $this->mendeley->get($url);
	// 	$this->assertEqual($result->title, 'Example Title');
	// }

	function testGetDocDetails() {
		$title = 'Example Title';
		$tags = array('a', 'b');
		$type = 'Generic';

		$result = $this->mendeley->get('documents/' . $this->tmp['documentId']);
		$this->assertEqual($result->title, $title);
		$this->assertEqual($result->tags, $tags);
		$this->assertEqual($result->type, $type);
	}

	function testGetSearchUnauthorized() {
		$url = 'documents/search/' . urlencode('Example Title');
		$itemsPerPage = 5;
		$result = $this->mendeley->get($url, array('items' => $itemsPerPage), false);
		$this->assertEqual($result->items_per_page, $itemsPerPage);
	}

	function testCreateGroup() {
		$group = array(
			'group' =>
			array(
				'name' => 'Example Group',
				'type' => 'private',
			),
		);

		$result = $this->mendeley->post('groups', $group);
		$this->assertTrue(is_numeric($result->group_id) && $result->group_id > 0);
		$this->tmp['group_id'] = $result->group_id;
	}
	
	function testDeleteGroup() {
		$response = $this->mendeley->delete('groups/' . $this->tmp['group_id']);
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
		$this->assertEqual($response->group_id, 164791);
	}
	
	function testGetManyDocuments() {
		$many = 10000;
		$response = $this->mendeley->getGroupDocuments(164791, array('items' => $many));
		$this->assertEqual($response->total_pages, 1);
		$this->assertTrue($response->total_results < $many);
		$this->assertEqual(count($response->document_ids), $response->total_results);
	}
}
