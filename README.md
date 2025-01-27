# JSON Decode

PHP JSON Decode large data with lesser resources


## Examples


### Validating JSON.

```PHP
<?php
require "JsonDecode.php";

// Creating json file handle
$fp = fopen('/usr/local/var/www/rnd/test.json', 'rb');

// Create JsonEncode Object.
$JsonDecode = new JsonDecode($fp);
$JsonDecode->init();

// Validate JSON
$JsonDecode->validate();

$jsonDecode = null;
```

### Accessing data of Array after indexing JSON.

```PHP
<?php
require "JsonDecode.php";

// Creating json file handle
$fp = fopen('test.json', 'rb');

// Create JsonEncode Object.
$JsonDecode = new JsonDecode($fp);
$JsonDecode->init();

// Indexing JSON
$JsonDecode->indexJson();

// Transverse across key 'data'
if ($JsonDecode->isset('data') && $JsonDecode->jsonType('data') === 'Array') {
    for ($i=0, $i_count = $JsonDecode->count('data'); $i < $i_count; $i++) {
        $key = "data:{$i}";

        // Row details without Sub arrays
        $row = $JsonDecode->get($key);
        print_r($row);

        // Row with Sub arrays / Complete $key array recursively.
        $completeRow = $JsonDecode->getCompleteArray($key);
        print_r($completeRow);
    }
}

$jsonDecode = null;
```

