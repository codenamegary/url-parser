URLParser
-------------

The best little URL tool for PHP!

#####Features

- Fully tested
- Chainable methods
- PSR-0 autoload and PSR-1 compliant
- Friendly syntax for Segment, Query String and other URL part manipulation

#####Coming Soon

- Batch wrapper for processing multiple URLs
- Composer / Packagist publishing

#Usage Examples

###Load a complex URL and merge in some query parameters.

```php
$url = new codenamegary\URLParser\URL('https://maps.google.ca/maps?saddr=Tokyo,+Japan&daddr=Toronto,+ON&hl=en&sll=43.653226,-79.383184&sspn=0.444641,1.056747&geocode=FRCUIAIduoZTCCnnVy7whxtdYDGJG1cii2EBLg%3BFWoYmgIdcLVE-ymlO8bXkMvUiTF3xLQqUFU1Mg&oq=tokyo&mra=ls&t=m&z=3');
$url->addQuery(array(
	'foo'	=> 'bar',
));
echo $url->make();
```

####Returns

https://maps.google.ca/maps?saddr=Tokyo,+Japan&daddr=Toronto,+ON&hl=en&sll=43.653226,-79.383184&sspn=0.444641,1.056747&geocode=FRCUIAIduoZTCCnnVy7whxtdYDGJG1cii2EBLg%3BFWoYmgIdcLVE-ymlO8bXkMvUiTF3xLQqUFU1Mg&oq=tokyo&mra=ls&t=m&z=3&foo=bar

###Load a complex URL and get rid of some query parameters.

```php
$url = new codenamegary\URLParser\URL('https://maps.google.ca/maps?saddr=Tokyo,+Japan&daddr=Toronto,+ON&hl=en&sll=43.653226,-79.383184&sspn=0.444641,1.056747&geocode=FRCUIAIduoZTCCnnVy7whxtdYDGJG1cii2EBLg%3BFWoYmgIdcLVE-ymlO8bXkMvUiTF3xLQqUFU1Mg&oq=tokyo&mra=ls&t=m&z=3');
$url->stripQuery('geocode');
echo $url->make();
```
####Returns

https://maps.google.ca/maps?saddr=Tokyo,+Japan&daddr=Toronto,+ON&hl=en&sll=43.653226,-79.383184&sspn=0.444641,1.056747&oq=tokyo&mra=ls&t=m&z=3

###Get an instance of the URL parser for the page currently being visited

```php
// .. and strip all the segments and query string from the URI
$url = new codenamegary\URLParser\URL;
$url->stripSegments()
    ->stripQueries()
    ->make();
echo $url;
```
####Returns

Full URL of the current page minus the URI segments and query string.

###Swap a URI string

```php
$url = new codenamegary\URLParser\URL('http://www.apple.com/bananas/coconut/date/elephant/giraffe');
$url->swapSegment('date','raisin');
echo $url->make();
```

####Returns

http://www.apple.com/bananas/coconut/raisin/elephant/giraffe

###Put something in front of coconut

```php
$url = new codenamegary\URLParser\URL('http://www.apple.com/bananas/coconut/date/elephant/giraffe')
    ->prependSegment('lime','coconut');
echo $url->make();
```

####Returns

http://www.apple.com/bananas/lime/coconut/date/elephant/giraffe

###Change the host and protocol using method chaining

```php
$url = codenamegary\URLParser\URL::to('http://www.apple.com/bananas/coconut/date/elephant/giraffe')->host('www.microsoft.com')->protocol('ftp');
echo $url->make();
```

####Returns

ftp://www.microsoft.com/bananas/coconut/date/elephant/giraffe
