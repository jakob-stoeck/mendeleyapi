Mendeley API Client
===================

About
-----

You can use this client to easily query the Mendeley API (http://dev.mendeley.com/docs/) from your PHP code.
It accepts URLs from "user specific resources", handles the Authentication via OAuth and returns a PHP object with its results. The authentication is cached.

Usage
-----

1. Download the OAuth library from http://code.google.com/p/oauth/
2. Copy Configuration.sample.php to Configuration.php and input your Mendeley consumer and secret. If you don't have one, obtain it from the Mendeley developers site.

Use the library like this:

`    <?php
    require_once 'path/to/mendeleyapi/Mendeley.php';
    $mendeley = new Mendeley();

    // GET request to look up things
    $result = $mendeley->get('sharedcollections/12345'); // $result is now a PHP object with all documents of the shared collection with this id
    $result = $mendeley->getCollection('sharedcollections/12345'); // similar to above, only that $result->documents now contains also all document info

    // POST request to change things, in this case to add a document to a group (collection)
    $doc = new MendeleyDoc();
    $doc->title = 'Example Title';
    $doc->url = 'http://www.example.org/';
    $doc->group_id = 504091;

    try {
      $result = $mendeley->post('documents/', $doc->toParams());
    } catch(Exception $e) {
      echo $e->getMessage();
    }

    // DELETE
    try {
      $mendeley->delete('documents/12345');
    } catch(Exception $e) {
      echo $e->getMessage();
    }
    ?>`

Biblio to Mendeley Type Converting
----------------------------------

Not all Biblio publication types are supported by Mendeley and vice-versa. Please see the following list:

`   <?php
    // biblio types in the mendeley api
    BIBLIO_BILL => 'Bill',
    BIBLIO_BOOK => 'Book',
    BIBLIO_BOOK_CHAPTER => 'Book Section',
    BIBLIO_BROADCAST => 'Television Broadcast',
    BIBLIO_CASE => 'Case',
    BIBLIO_CONFERENCE_PROCEEDINGS => 'Conference Proceedings',
    BIBLIO_FILM => 'Film',
    BIBLIO_HEARING => 'Hearing',
    BIBLIO_JOURNAL_ARTICLE => 'Journal Article',
    BIBLIO_MAGAZINE_ARTICLE => 'Magazine Article',
    BIBLIO_NEWSPAPER_ARTICLE => 'Newspaper Article',
    BIBLIO_PATENT => 'Patent',
    BIBLIO_SOFTWARE => 'Computer Program',
    BIBLIO_STATUTE => 'Statute',
    BIBLIO_THESIS => 'Thesis',
    BIBLIO_WEB_ARTICLE => 'Web Page',
    // biblio types not yet in the mendeley api:
    BIBLIO_ARTWORK => 'Generic',
    BIBLIO_AUDIOVISUAL => 'Generic',
    BIBLIO_CHART => 'Generic',
    BIBLIO_CLASSICAL => 'Generic',
    BIBLIO_CONFERENCE_PAPER => 'Generic',
    BIBLIO_DATABASE => 'Generic',
    BIBLIO_GOVERNMENT_REPORT => 'Generic',
    BIBLIO_LEGAL_RULING => 'Generic',
    BIBLIO_MANUSCRIPT => 'Generic',
    BIBLIO_MAP => 'Generic',
    BIBLIO_MISCELLANEOUS => 'Generic',
    BIBLIO_MISCELLANEOUS_SECTION => 'Generic',
    BIBLIO_PERSONAL => 'Generic',
    BIBLIO_REPORT => 'Generic',
    BIBLIO_UNPUBLISHED => 'Generic',
    // mendeley api types not supported by biblio:
    // ??? => 'Encyclopedia Article';
    // ??? => 'Working Paper'; ?>`

Testing
-------

1. Run _webroot_/mendeleyapi/getAccessToken.php once to get your Mendeley access token. You only have to do that once. It should redirect you to the Mendeley page where you login and saves the token under /mendeleyapi/cache/access_token_SOMESTRING.
2. Runs with Simpletest. Point your browser to _webroot_/mendeleyapi/tests/all_tests.php or do it in the shell:

    $ php path/to/mendeleyapi/tests/all_tests.php
