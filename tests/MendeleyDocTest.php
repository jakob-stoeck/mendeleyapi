<?php
class MendeleyDocTest extends UnitTestCase {
	function testToJson() {
		$title = 'Example Title';
		$url = 'http://www.example.org/';
		$tags = array('a', 'b');
		$groupId = 123;

		$doc = new MendeleyDoc();
		$doc->title = $title;
		$doc->url = $url;
		$doc->tags = $tags;
		$doc->groupId = $groupId;

		$json = $doc->toJson();
		$doc2 = json_decode($json);

		$this->assertEqual($doc2->title, $title);
		$this->assertEqual($doc2->url, $url);
		$this->assertEqual($doc2->tags, $tags);
		$this->assertEqual($doc2->groupId, $groupId);
	}

	function testToParams() {
		$title = 'Example Title';
		$url = 'http://www.example.org/';
		$tags = array('a', 'b');
		$groupId = 123;

		$doc = new MendeleyDoc();
		$doc->title = $title;
		$doc->url = $url;
		$doc->tags = $tags;
		$doc->groupId = $groupId;

		$doc = $doc->toParams();

		$this->assertTrue(isset($doc['document']));
		$this->assertEqual($doc['document']['title'], $title);
		$this->assertEqual($doc['document']['url'], $url);
		$this->assertEqual($doc['document']['tags'], $tags);
		$this->assertEqual($doc['document']['groupId'], $groupId);
	}

	function testConstructWithDocumentId() {
		$documentId = '646077082';
		$doc = MendeleyDoc::constructWithDocumentId($documentId);
		$this->assertEqual($doc->title, 'A Square Is Not a Rectangle');
		$this->assertEqual($doc->year, 2009);
		$this->assertEqual($doc->documentId, $documentId);
	}
}