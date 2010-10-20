<?php
class MendeleyTest extends UnitTestCase {
	function testGet() {
		$sharedCollectionId = 164791;
		$url = 'sharedcollections/' . $sharedCollectionId;
		$params = array('page' => 1, 'items' => 1);
		
		$mendeley = new Mendeley();
		$result = $mendeley->get($url, $params);

		$this->assertEqual((int)$result->shared_collection_id, $sharedCollectionId);
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

		$mendeley = new Mendeley();
		$result = $mendeley->post('documents/', array('document' => (array)$doc));

		$this->assertTrue(!empty($result));
		$this->assertTrue(isset($result->document_id) && is_numeric($result->document_id));
	}

	function testCreateCollection() {
		$sharedCollection = array(
			'sharedcollection' =>
			array(
				'name' => 'Example Collection'
			)
		);

		$mendeley = new Mendeley();
		$result = $mendeley->post('sharedcollections', $sharedCollection);
		
		$this->assertTrue(!empty($result));
	}
}