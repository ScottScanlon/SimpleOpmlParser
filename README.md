SimpleOpmlParser
================

Simple PHP class to parse OPML files

OPML 2.0 specification: [http://dev.opml.org/spec2.html](http://dev.opml.org/spec2.html)

###Requirements
* PHP 5+
* SimpleXML library [http://php.net/manual/en/book.simplexml.php](http://php.net/manual/en/book.simplexml.php)

###Simple usage
#####HTML code:
```html
<form action="action.php" method="post" enctype="multipart/form-data">
  <label for="file">Filename:</label>
  <input type="file" name="opmlFile" accept=".opml"><br>
  <input type="submit" name="submit" value="Submit">
</form>
```
#####PHP code:
```php
require_once('SimpleOpmlParser.php');

$sop = new SimpleOpmlParser();
$sop->setOpmlUploadName("opmlFile"); //must be in html input name
$sop->setUploadDir("upload"); //optional, default: upload, need full rights (777) for the dir
$opml = $sop->init();
            
if ($opml == true) {
  if (isset($opml->head->title)) {
    echo "OPML file title: " . $opml->head->title . "<br/><br/>";
  }

  foreach ($opml->body->outline as $outline) {
    echo "Outline type: " . $outline->attributes()->type . "<br/>";
    echo "Outline text: " . $outline->attributes()->text . "<br/>";
    echo "Outline xmlUrl: " . $outline->attributes()->xmlUrl . "<br/><br/>";
  }
    
  $sop->deleteOpml(); //delete file from the upload directory
}
```
Full example in `demo/index.php` file.
