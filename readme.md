URLParser-PHP
-------------

The best little URL tool for PHP!

Features

- Chainable methods
- Static or Object context, whatever works for you
- Batch wrapper to process multiple URLs

#Usage Examples

Load a complex URL and merge in some query parameters.

```php
$url = new URLParser\URL('https://maps.google.ca/maps?saddr=Tokyo,+Japan&daddr=Toronto,+ON&hl=en&sll=43.653226,-79.383184&sspn=0.444641,1.056747&geocode=FRCUIAIduoZTCCnnVy7whxtdYDGJG1cii2EBLg%3BFWoYmgIdcLVE-ymlO8bXkMvUiTF3xLQqUFU1Mg&oq=tokyo&mra=ls&t=m&z=3');
$url->addQuery(array(
	'foo'	=> 'bar',
));
echo $url->make();

/**
 * Returns
 *
 *	https://maps.google.ca/maps?saddr=Tokyo,+Japan&daddr=Toronto,+ON&hl=en&sll=43.653226,-79.383184&sspn=0.444641,1.056747&geocode=FRCUIAIduoZTCCnnVy7whxtdYDGJG1cii2EBLg%3BFWoYmgIdcLVE-ymlO8bXkMvUiTF3xLQqUFU1Mg&oq=tokyo&mra=ls&t=m&z=3&foo=bar
 */
```

Load a complex URL and get rid of some query parameters.

```php
$url = new URLParser\URL('https://maps.google.ca/maps?saddr=Tokyo,+Japan&daddr=Toronto,+ON&hl=en&sll=43.653226,-79.383184&sspn=0.444641,1.056747&geocode=FRCUIAIduoZTCCnnVy7whxtdYDGJG1cii2EBLg%3BFWoYmgIdcLVE-ymlO8bXkMvUiTF3xLQqUFU1Mg&oq=tokyo&mra=ls&t=m&z=3');
$url->stripQuery('geocode');
echo $url->make();

/**
 * Returns
 *
 *	https://maps.google.ca/maps?saddr=Tokyo,+Japan&daddr=Toronto,+ON&hl=en&sll=43.653226,-79.383184&sspn=0.444641,1.056747&oq=tokyo&mra=ls&t=m&z=3
 */
```
