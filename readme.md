URLParser-PHP
-------------

The best little URL tool for PHP!

Features

- Chainable methods
- Static or Object context, whatever works for you
- Batch wrapper to process multiple URLs

#Usage Examples

###Load a complex URL and merge in some query parameters.

```php
$url = new URLParser\URL('https://maps.google.ca/maps?saddr=Tokyo,+Japan&daddr=Toronto,+ON&hl=en&sll=43.653226,-79.383184&sspn=0.444641,1.056747&geocode=FRCUIAIduoZTCCnnVy7whxtdYDGJG1cii2EBLg%3BFWoYmgIdcLVE-ymlO8bXkMvUiTF3xLQqUFU1Mg&oq=tokyo&mra=ls&t=m&z=3');
$url->addQuery(array(
	'foo'	=> 'bar',
));
echo $url->make();
```

####Returns

https://maps.google.ca/maps?saddr=Tokyo,+Japan&daddr=Toronto,+ON&hl=en&sll=43.653226,-79.383184&sspn=0.444641,1.056747&geocode=FRCUIAIduoZTCCnnVy7whxtdYDGJG1cii2EBLg%3BFWoYmgIdcLVE-ymlO8bXkMvUiTF3xLQqUFU1Mg&oq=tokyo&mra=ls&t=m&z=3&foo=bar

###Load a complex URL and get rid of some query parameters.

```php
$url = new URLParser\URL('https://maps.google.ca/maps?saddr=Tokyo,+Japan&daddr=Toronto,+ON&hl=en&sll=43.653226,-79.383184&sspn=0.444641,1.056747&geocode=FRCUIAIduoZTCCnnVy7whxtdYDGJG1cii2EBLg%3BFWoYmgIdcLVE-ymlO8bXkMvUiTF3xLQqUFU1Mg&oq=tokyo&mra=ls&t=m&z=3');
$url->stripQuery('geocode');
echo $url->make();
```
####Returns

https://maps.google.ca/maps?saddr=Tokyo,+Japan&daddr=Toronto,+ON&hl=en&sll=43.653226,-79.383184&sspn=0.444641,1.056747&oq=tokyo&mra=ls&t=m&z=3

###Get an instance of the URL parser for the page currently being visited

```php
// .. and strip all the segments and query string from the URI
$url = URLParser\URL::stripSegments()
	->stripQueries()
	->make();
echo $url;
```
####Returns

Full URL of the current page minus the URI segments and query string.

###Swap a URI string

```php
$url = URLParser\URL('http://www.apple.com/bananas/coconut/date/elephant/giraffe');
$url->swapSegment('date','raisin');
echo $url->make();
```

####Returns

http://www.apple.com/bananas/coconut/raisin/elephant/giraffe

###Put something in front of coconut

```php
$url = URLParser\URL('http://www.apple.com/bananas/coconut/date/elephant/giraffe');
$url->prependSegment('lime','coconut');
echo $url->make();
```

####Returns

http://www.apple.com/bananas/lime/coconut/date/elephant/giraffe

###Change the host and protocol using method chaining

```php
$url = URLParser\URL::to('http://www.apple.com/bananas/coconut/date/elephant/giraffe')->host('www.microsoft.com')->protocol('ftp');
echo $url->make();
```

####Returns

ftp://www.microsoft.com/bananas/coconut/date/elephant/giraffe
