# Mendeley API Client

## About

You can use this client to easily query the Mendeley API (http://dev.mendeley.com/docs/) from your PHP code.
It accepts URLs from "user specific resources", handles the Authentication via OAuth and returns a PHP object with its results. The authentication is cached.

## Testing

You need to have [SimpleTest][2] installed to run the tests.

1. Run _webroot_/mendeleyapi/getAccessToken.php once to get your Mendeley access token. You only have to do that once. It should redirect you to the Mendeley page where you login and saves the token under /mendeleyapi/cache/access_token_SOMESTRING.
2. Runs with Simpletest. Point your browser to _webroot_/mendeleyapi/tests/all_tests.php or with the shell:

    `$ php path/to/mendeleyapi/tests/all_tests.php`

## Usage

Copy Configuration.sample.php to Configuration.php and input your Mendeley consumer and secret. If you don't have one, obtain it from the Mendeley developers site.

Use the library stand-alone like this:

```php
require_once 'path/to/mendeleyapi/Mendeley.php';
$mendeley = new Mendeley();

// GET request to look up things
$result = $mendeley->get('groups/12345'); // returns a PHP object with all documents of the group
$result = $mendeley->getCollection('groups/12345'); // similar to above, only that enriches the result with the document info for each document

// POST request to change things, in this case to add a document to a group
$doc = new MendeleyDoc();
$doc->type = 'Generic';
$doc->title = 'Example Title';
$doc->authors = array('Jakob Stoeck');
$doc->group_id = 504091;
$mendeley->post('documents/', $doc->toParams());

// DELETE
$mendeley->delete('documents/12345');
```

## Libraries used

For convenience a copy of the [OAuth Library][1] is pre-installed

[1]: http://code.google.com/p/oauth/
[2]: http://www.simpletest.org/
