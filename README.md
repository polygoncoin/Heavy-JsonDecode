# JSON Decode
 
PHP JSON Decode large data with lesser resources
 

## Examples
 

### Validating JSON.
 
````
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
````

### Accessing data of Array after indexing JSON.
 
````
    <?php
    require "JsonDecode.php";
    
    // Create JsonEncode Object.
    $JsonDecode = new JsonDecode('/usr/local/var/www/rnd/test.json');
    $JsonDecode->init();

    // Indexing JSON
    $JsonDecode->indexJson();

    // Transverse across key 'data'
    if ($JsonDecode->isset('data') && $JsonDecode->jsonType('data') === 'Array') {
        for ($i=0, $i_count = $JsonDecode->count('data'); $i < $i_count; $i++) {
            $row = $JsonDecode->get('data:'.$i);
            print_r($row);
        }
    }
    
    $jsonDecode = null;
````

