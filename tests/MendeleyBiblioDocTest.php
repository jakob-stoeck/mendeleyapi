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
		$this->assertEqual($node->taxonomy, $tags);
		$this->assertEqual($node->biblio_contributors[1][0]['name'], $doc->authors[0]);
		$this->assertTrue(is_numeric($node->biblio_type) && $node->biblio_type > 0);
	}

	function testConstructWithNode() {
		$node = self::nodeFactory();
		$doc = MendeleyBiblioDoc::constructWithNode($node);

		$this->assertEqual($node->biblio_type, MendeleyBiblioDoc::mendeleyToBiblioType($doc->type));
		$this->assertEqual($node->title, $doc->title);
		$this->assertEqual($node->biblio_publisher, $doc->publisher);
		$this->assertEqual($node->biblio_contributors[MendeleyBiblioDoc::BIBLIO_AUTHOR][0]['name'], $doc->authors[0]);
		$this->assertEqual($node->biblio_contributors[MendeleyBiblioDoc::BIBLIO_AUTHOR][1]['name'], $doc->authors[1]);
		$this->assertEqual($node->biblio_keywords, $doc->keywords);
		$this->assertEqual($node->biblio_abst_e, $doc->abstract);
		$this->assertEqual($node->taxonomy['taxonomy_term_1']['title'], $doc->tags[0]);
		$notInMendeley = 'node parameter which do not exist in the mendeley api are not set';
		$this->assertTrue(!isset($doc->tertiary_title), $notInMendeley);
		$this->assertTrue(!isset($doc->section), $notInMendeley);

		$notSetText = 'parameters not set in node should not be set in biblio doc: ';
		$this->assertNull($doc->city, $notSetText . 'city');
		$this->assertNull($doc->discipline, $notSetText . 'discipline');
		$this->assertNull($doc->city, $notSetText . 'city');
		$this->assertNull($doc->country, $notSetText . 'country');
		$this->assertNull($doc->discipline, $notSetText . 'discipline');
		$this->assertNull($doc->documentId, $notSetText . 'documentId');
		$this->assertNull($doc->doi, $notSetText . 'doi');
		$this->assertNull($doc->edition, $notSetText . 'edition');
		$this->assertNull($doc->editors, $notSetText . 'editors');
		$this->assertNull($doc->genre, $notSetText . 'genre');
		$this->assertNull($doc->groupId, $notSetText . 'groupId');
		$this->assertNull($doc->identifiers, $notSetText . 'identifiers');
		$this->assertNull($doc->institution, $notSetText . 'institution');
		$this->assertNull($doc->isbn, $notSetText . 'isbn');
		$this->assertNull($doc->issn, $notSetText . 'issn');
		$this->assertNull($doc->issue, $notSetText . 'issue');
		$this->assertNull($doc->notes, $notSetText . 'notes');
		$this->assertNull($doc->pages, $notSetText . 'pages');
		$this->assertNull($doc->pmid, $notSetText . 'pmid');
		$this->assertNull($doc->publication_outlet, $notSetText . 'publication_outlet');
		$this->assertNull($doc->url, $notSetText . 'url');
		$this->assertNull($doc->volume, $notSetText . 'volume');
		$this->assertNull($doc->year, $notSetText . 'year');
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
		$this->assertEqual($node->taxonomy['taxonomy_term_1']['title'], $doc->tags[0]);
		$this->assertEqual($node->taxonomy['taxonomy_term_2']['title'], $doc->tags[1]);
		$this->assertEqual($node->biblio_contributors[MendeleyBiblioDoc::BIBLIO_AUTHOR][0]['name'], $doc->authors[0], 'mendeley does not put a space before the author name after upload');
		$this->assertEqual($node->biblio_contributors[MendeleyBiblioDoc::BIBLIO_AUTHOR][1]['name'], $doc->authors[1], 'mendeley does not put a space before the author name after upload'); 
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
		$node->taxonomy = array(
			'taxonomy_term_1' => array('title' => 'a'),
			'taxonomy_term_2' => array('title' => 'b'),
		);
		return $node;
	}

	function testUnknownBiblioTypeIsMisc() {
		$mendeleyType = 'Generic';
		$biblioType = MendeleyBiblioDoc::BIBLIO_MISCELLANEOUS;
		$biblio = MendeleyBiblioDoc::mendeleyToBiblioType('unknown type');
		$this->assertEqual($biblio, $biblioType);

		$mendeley = MendeleyBiblioDoc::biblioToMendeleyType('unknown type');
		$this->assertEqual($mendeley, $mendeleyType);
	}
}
