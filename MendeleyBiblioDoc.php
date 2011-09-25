<?php
/**
 *   Mendeley API Client
 *
 *   Copyright (C) 2010, 2011  Jakob Stoeck
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

/**
 * MendeleyDoc extension to work together with Biblio (Drupal module)
 */
class MendeleyBiblioDoc extends MendeleyDoc {
	/**
	 * Contributor types
	 */
	const BIBLIO_AUTHOR = 1;
	const BIBLIO_SECONDARY_AUTHOR = 2;
	const BIBLIO_TERTIARY_AUTHOR = 3;
	const BIBLIO_SUBSIDIARY_AUTHOR = 4;
	const BIBLIO_CORPORATE_AUTHOR = 5;
	const BIBLIO_SERIES_EDITOR = 10;
	const BIBLIO_PERFORMERS = 11;
	const BIBLIO_SPONSOR = 12;
	const BIBLIO_TRANSLATOR = 13;
	const BIBLIO_EDITOR = 14;
	const BIBLIO_COUNSEL = 15;
	const BIBLIO_SERIES_DIRECTOR = 16;
	const BIBLIO_PRODUCER = 17;
	const BIBLIO_DEPARTMENT = 18;
	const BIBLIO_ISSUING_ORGANIZATION = 19;
	const BIBLIO_INTERNATIONAL_AUTHOR = 20;
	const BIBLIO_RECIPIENT = 21;
	const BIBLIO_ADVISOR = 22;

	/**
	 * Document types
	 */
	const BIBLIO_BOOK = 100;
	const BIBLIO_BOOK_CHAPTER = 101;
	const BIBLIO_JOURNAL_ARTICLE = 102;
	const BIBLIO_CONFERENCE_PAPER = 103;
	const BIBLIO_CONFERENCE_PROCEEDINGS = 104;
	const BIBLIO_NEWSPAPER_ARTICLE = 105;
	const BIBLIO_MAGAZINE_ARTICLE = 106;
	const BIBLIO_WEB_ARTICLE = 107;
	const BIBLIO_THESIS = 108;
	const BIBLIO_REPORT = 109;
	const BIBLIO_FILM = 110;
	const BIBLIO_BROADCAST = 111;
	const BIBLIO_ARTWORK = 112;
	const BIBLIO_SOFTWARE = 113;
	const BIBLIO_AUDIOVISUAL = 114;
	const BIBLIO_HEARING = 115;
	const BIBLIO_CASE = 116;
	const BIBLIO_BILL = 117;
	const BIBLIO_STATUTE = 118;
	const BIBLIO_PATENT = 119;
	const BIBLIO_PERSONAL = 120;
	const BIBLIO_MANUSCRIPT = 121;
	const BIBLIO_MAP = 122;
	const BIBLIO_CHART = 123;
	const BIBLIO_UNPUBLISHED = 124;
	const BIBLIO_DATABASE = 125;
	const BIBLIO_GOVERNMENT_REPORT = 126;
	const BIBLIO_CLASSICAL = 127;
	const BIBLIO_LEGAL_RULING = 128;
	const BIBLIO_MISCELLANEOUS = 129;
	const BIBLIO_MISCELLANEOUS_SECTION = 130;

	/**
	 * MendeleyDoc properties I haven't mapped so far because I didn't find a good Biblio field for it
	 * Ideally all should be mapped in the future
	 */
	public static function notMapped() {
		return array(
			'country',
			'discipline',
			'genre',
			'group_id',
			'identifiers',
			'publication_outlet',
			'language',
			'year', // TODO: we cannot check on year right now because biblio makes it "Submitted" if no year present
		);
	}

	/**
	 * Returns a map biblio keys => mendeley keys
	 *
	 * @param boolean $flip
	 * 	set true to get mendeley => biblio
	 * @return array
	 */
	private static function map($flip = false) {
		// @see notMapped() for properties I haven't mapped so far

		$biblioToMendeleyFields = array_filter(array(
			'biblio_abst_e' => 'abstract',
			'biblio_abst_f' => null,
			'biblio_accession_number' => null,
			'biblio_access_date' => 'dateAccessed',
			'biblio_alternate_title' => null,
			'biblio_auth_address' => null,
			'biblio_call_number' => null,
			'biblio_citekey' => null,
			'biblio_coins' => null,
			'biblio_contributors' => null, // biblio sends a $node->biblio_contributors which includes authors, editors, @see contributor types
			'biblio_corp_authors' => null,
			'biblio_custom1' => null,
			'biblio_custom2' => null,
			'biblio_custom3' => null,
			'biblio_custom4' => null,
			'biblio_custom5' => null,
			'biblio_custom6' => null,
			'biblio_custom7' => null,
			'biblio_date' => null,
			'biblio_doi' => 'doi',
			'biblio_edition' => 'edition',
			'biblio_isbn' => 'isbn',
			'biblio_issn' => 'issn',
			'biblio_issue' => 'issue',
			'biblio_keywords' => 'keywords',
			'biblio_label' => null,
			// 'biblio_lang' => 'language',
			'biblio_mendeley_doc_id' => 'documentId',
			'biblio_notes' => 'notes',
			'biblio_number' => null,
			'biblio_number_of_volumes' => null,
			'biblio_original_publication' => 'originalPublication',
			'biblio_other_author_affiliations' => null,
			'biblio_other_number' => null,
			'biblio_pages' => 'pages',
			'biblio_place_published' => 'city',
			'biblio_publisher' => 'publisher',
			'biblio_pubmed_id' => 'pmid',
			'biblio_refereed' => null,
			'biblio_remote_db_name' => null,
			'biblio_remote_db_provider' => null,
			'biblio_reprint_edition' => null,
			'biblio_research_notes' => null,
			'biblio_secondary_authors' => null,
			'biblio_secondary_title' => null,
			'biblio_section' => null,
			'biblio_short_title' => null,
			'biblio_subsidiary_authors' => null,
			'biblio_tertiary_authors' => null,
			'biblio_tertiary_title' => null,
			'biblio_translated_title' => null,
			'biblio_type' => 'type', // needs callback $this->typeToBiblio()
			'biblio_type_of_work' => null,
			'biblio_url' => 'url',
			'biblio_volume' => 'volume',
			'biblio_year' => 'year',
			'title' => 'title',
			'taxonomy' => 'tags', // proprietary addition to add taxonomy terms to a node
		));

		if($flip) {
			$biblioToMendeleyFields = array_flip($biblioToMendeleyFields);
		}

		return $biblioToMendeleyFields;
	}

	/**
	 * Returns array usable by biblio
	 *
	 * You can build a MendeleyBiblioDoc from the MendeleyAPI and send it to biblio to save
	 *
	 * @param int $nid
	 * 	node id to add to the object to update an existing node
	 * @return array
	 */
	public function toNode($nid = null) {
		$node = self::map();

		foreach($node as $biblioKey => &$mendeley) {
			$mendeley = $this->$mendeley;
		}
		$node = (object)array_filter($node);

		$node->type = 'biblio';
		$node->biblio_type = self::mendeleyToBiblioType($this->type);
		if(!empty($this->authors)) {
			foreach((array)$this->authors as $a) {
				$node->biblio_contributors[self::BIBLIO_AUTHOR][] = array('name' => $a);
			}
		}
		if(!empty($this->editors)) {
			foreach((array)$this->editors as $a) {
				$node->biblio_contributors[self::BIBLIO_EDITOR][] = array('name' => $a);
			}
		}
		if(!empty($this->institution)) {
			$node->biblio_contributors[self::BIBLIO_CORPORATE_AUTHOR][] = array('name' => $this->institution);
		}
		if($nid !== null) {
			$node->nid = $nid;
		}

		return $node;
	}

	/**
	 * Instantiates a Mendeley Document by its internal document id
	 *
	 * This almost an exact copy of @see MendeleyDoc::constructWithDocumentId because get_called_class is only available in PHP >= 5.3
	 *
	 * @param string $documentId
	 * 	sent by Mendeley in e.g. collections/*collectionId*
	 */
	public static function constructWithDocumentId($documentId) {
		$that = new MendeleyBiblioDoc();
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

		// authors are stored as objects in Mendeley and as strings in Biblio
		if (isset($that->authors)) {
			foreach ($that->authors as &$a) {
				$a = implode(' ', array($a->forename, $a->surname));
			}
		}

		return $that;
	}

	/**
	 * Constructs a MendeleyBiblioDoc from a Biblio node
	 *
	 * Can be sent to Mendeley to post a Document which was created in Biblio
	 *
	 * @param StdClass
	 * @param int
	 * @return MendeleyBiblioDoc
	 */
	public function constructWithNode($node, $groupId = null) {
		$that = new MendeleyBiblioDoc();
		$mendeleyKeys = array_keys(get_object_vars($that));
		$map = self::map(true);

		// map node attributes to mendeley attributes
		foreach($mendeleyKeys as $m) {
			if(isset($map[$m])) {
				$biblioKey = $map[$m];
				if(isset($node->$biblioKey) && !empty($node->$biblioKey)) {
					$that->$m = $node->$biblioKey;
				}
			}
		}
		// there three ways in drupal to represent tags in a node ...
		if(isset($that->tags) && !empty($that->tags)) {
			if(isset($that->tags['tags'])) {
				// comma-separated list (i.e. user input)
				$that->tags = explode(',', reset($that->tags['tags']));
				foreach($that->tags as &$t) {
					$t = trim($t);
				}
			} elseif(is_array($that->tags)) {
				if(is_array(reset($that->tags))) {
					// array of arrays (no user input but a drupal db query result)
					foreach($that->tags as &$term) {
						$term = $term['title'];
					}
				} elseif(is_object(reset($that->tags))) {
					// array of objects (drupal node load result)
					foreach($that->tags as &$term) {
						$term = $term->name;
					}
				}
			}
			$that->tags = array_values($that->tags);
		}

		$that->type = self::biblioToMendeleyType($node->$map['type']);

		foreach($node->biblio_contributors as $biblioContribKey => $contribs) {
			foreach($contribs as $key => &$values) {
				$values = $values['name'];
			}

			switch($biblioContribKey) {
				case self::BIBLIO_AUTHOR:
					$that->authors = $contribs;
				break;

				case self::BIBLIO_EDITOR:
					$that->editors = $contribs;
				break;

				case self::BIBLIO_CORPORATE_AUTHOR:
					$that->institution = reset($contribs);
				break;
			}
		}

		if($groupId !== null) {
			$that->group_id = $groupId;
		}

		return $that;
	}

	/**
	 * Convenience method for @see MendeleyBiblioDoc::biblioToMendeleyType
	 */
	public static function mendeleyToBiblioType($mendeleyType) {
		return self::biblioToMendeleyType($mendeleyType, true);
	}

	/**
	 * Maps biblio publication types to mendeley publication types.
	 *
	 * Not all types of biblio are supported by the mendeley api
	 *
	 * @param mixed
	 * 	biblio type id if flip is false, else Mendeley type string
	 * @return int
	 * 	biblio/mendeley type id/string
	 */
	public static function biblioToMendeleyType($type, $flip = false) {
		$biblioToMendeley = array(
			// biblio types in the mendeley api
			self::BIBLIO_BILL => 'Bill',
			self::BIBLIO_BOOK => 'Book',
			self::BIBLIO_BOOK_CHAPTER => 'Book Section',
			self::BIBLIO_BROADCAST => 'Television Broadcast',
			self::BIBLIO_CASE => 'Case',
			self::BIBLIO_CONFERENCE_PROCEEDINGS => 'Conference Proceedings',
			self::BIBLIO_FILM => 'Film',
			self::BIBLIO_HEARING => 'Hearing',
			self::BIBLIO_JOURNAL_ARTICLE => 'Journal Article',
			self::BIBLIO_MAGAZINE_ARTICLE => 'Magazine Article',
			self::BIBLIO_NEWSPAPER_ARTICLE => 'Newspaper Article',
			self::BIBLIO_PATENT => 'Patent',
			self::BIBLIO_SOFTWARE => 'Computer Program',
			self::BIBLIO_STATUTE => 'Statute',
			self::BIBLIO_THESIS => 'Thesis',
			self::BIBLIO_WEB_ARTICLE => 'Web Page',
			// biblio types not yet in the mendeley api:
			self::BIBLIO_ARTWORK => 'Generic',
			self::BIBLIO_AUDIOVISUAL => 'Generic',
			self::BIBLIO_CHART => 'Generic',
			self::BIBLIO_CLASSICAL => 'Generic',
			self::BIBLIO_CONFERENCE_PAPER => 'Generic',
			self::BIBLIO_DATABASE => 'Generic',
			self::BIBLIO_GOVERNMENT_REPORT => 'Generic',
			self::BIBLIO_LEGAL_RULING => 'Generic',
			self::BIBLIO_MANUSCRIPT => 'Generic',
			self::BIBLIO_MAP => 'Generic',
			self::BIBLIO_MISCELLANEOUS => 'Generic',
			self::BIBLIO_MISCELLANEOUS_SECTION => 'Generic',
			self::BIBLIO_PERSONAL => 'Generic',
			self::BIBLIO_REPORT => 'Generic',
			self::BIBLIO_UNPUBLISHED => 'Generic',
			// mendeley api types not supported by biblio:
			// self::??? => 'Encyclopedia Article';
			// self::??? => 'Working Paper';
		);

		if($flip) {
			$biblioToMendeley = array_flip($biblioToMendeley);
		}

		if(isset($biblioToMendeley[$type])) {
			return $biblioToMendeley[$type];
		} else {
			return ($flip ? self::BIBLIO_MISCELLANEOUS : $biblioToMendeley[self::BIBLIO_MISCELLANEOUS]);
		}
	}
}
