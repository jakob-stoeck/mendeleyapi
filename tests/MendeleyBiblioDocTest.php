<?php
class MendeleyBiblioDocTest extends UnitTestCase {
	function testToNode() {
		$title = 'Example Title';
		$url = 'http://www.example.org/';
		$tags = array('a', 'b');
		$groupId = 123;

		$doc = new MendeleyBiblioDoc();
		$doc->authors = array('Jakob Stoeck');
		$doc->title = $title;
		$doc->url = $url;
		$doc->tags = $tags;
		$doc->groupId = $groupId;
		$doc->type = 'Magazine Article';

		$node = $doc->toNode();
		$this->assertEqual($title, $doc->title);
		$this->assertEqual($node->title, $doc->title);
		$this->assertEqual($node->biblio_url, $doc->url);
		$this->assertEqual($node->biblio_type, 106);
		$this->assertEqual($node->tags, $tags);
		$this->assertEqual($node->biblio_contributors[1][0]['name'], $doc->authors[0]);
		$this->assertTrue(is_numeric($node->biblio_type) && $node->biblio_type > 0);
	}

	function testConstructWithNode() {
		$node = self::nodeFactory();
		$doc = MendeleyBiblioDoc::constructWithNode($node);

		$this->assertEqual($node->title, $doc->title);
		$this->assertEqual($node->biblio_type, MendeleyBiblioDoc::mendeleyToBiblioType($doc->type));
		$this->assertEqual($node->biblio_contributors[MendeleyBiblioDoc::BIBLIO_AUTHOR][0]['name'], $doc->authors[0]);
		$this->assertEqual($node->biblio_abst_e, $doc->abstract);
	}

	function testUploadToMendeley() {
		$node = self::nodeFactory();
		$biblioDoc = MendeleyBiblioDoc::constructWithNode($node);

		$mendeley = new Mendeley();
		$response = $mendeley->post('documents', $biblioDoc->toParams());
		$this->assertTrue(isset($response->document_id) && is_numeric($response->document_id));

		$doc = $mendeley->get('documents/' . $response->document_id);
		$this->assertEqual($node->title, $doc->title);
		$this->assertEqual($node->biblio_type, MendeleyBiblioDoc::mendeleyToBiblioType($doc->type));
		// $this->assertEqual($node->biblio_contributors[MendeleyBiblioDoc::BIBLIO_AUTHOR][0]['name'], reset($doc->authors)); // mendeley puts a space before the author name after upload. Strange.
		$this->assertEqual($node->biblio_abst_e, $doc->abstract);
	}

	function testConstructWithDocumentId() {
		$documentId = '646077082';
		$doc = MendeleyBiblioDoc::constructWithDocumentId($documentId);
		$this->assertEqual($doc->title, 'A Square Is Not a Rectangle');
		$this->assertEqual($doc->year, 2009);
		$this->assertEqual($doc->tags, array('lsp', 'object oriented programming'));
		$this->assertEqual($doc->documentId, $documentId);
	}

	function testToNodeByConstructWithDocumentId() {
		$documentId = '646077082';
		$doc = MendeleyBiblioDoc::constructWithDocumentId($documentId);
		$node = $doc->toNode();
		$this->assertEqual($doc->title, $node->title);
		$this->assertEqual($documentId, $node->biblio_mendeley_doc_id);
		$this->assertEqual(reset($doc->authors), reset($node->biblio_contributors[MendeleyBiblioDoc::BIBLIO_AUTHOR][0]));
		$this->assertEqual('biblio', $node->type);
	}

	function testBiblioToMendeleyType() {
		$mendeleyType = 'Web Page';
		$biblioType = MendeleyBiblioDoc::BIBLIO_WEB_ARTICLE;
		$biblio = MendeleyBiblioDoc::mendeleyToBiblioType($mendeleyType);
		$this->assertEqual($biblio, $biblioType);

		$mendeley = MendeleyBiblioDoc::biblioToMendeleyType($biblioType);
		$this->assertEqual($mendeley, $mendeleyType);
	}

	static function nodeFactory() {
		$node = new StdClass();
	  $node->uid = '1';
	  $node->created = 1287598385;
	  $node->type = 'biblio';
	  $node->language = 'de';
	  $node->changed = 1287598385;
	  $node->biblio_type = '106';
	  $node->title = 'Example Node Title';
	  $node->biblio_tertiary_title = '';
	  $node->biblio_section = '';
	  $node->biblio_publisher = '';
		$node->biblio_contributors = array(1 => array(array('name' => 'Jakob Stoeck'), array('name' => 'Hans im Glück')));
		$node->biblio_keywords = array('a', 'b', 'c');
		$node->biblio_abst_e = 'Hier steh ich nun ich armer Tor';
		$node->biblio_mendeley_doc_id = null;

		return $node;
	}
}
