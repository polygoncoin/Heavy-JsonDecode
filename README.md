# JSON Decode
 
PHP JSON Decode large data with lesser resources
 

## Examples
 

### Validating JSON.
 

    <?php
    require "JsonDecode.php";
    
    // Create JsonEncode Object.
    $JsonDecode = new JsonDecode('/usr/local/var/www/rnd/test.json');

    // Validate JSON
    $JsonDecode->validate();

    $jsonDecode = null;


### Generating hierarchy output.
 

    <?php
    require "JsonDecode.php";
    
    // Create JsonEncode Object.
    $JsonDecode = new JsonDecode('/usr/local/var/www/rnd/test.json');

    // Validate JSON
    $JsonDecode->validate();

    // Get hierarchy output 
    foreach($JsonDecode->process() as $keys => $arr) {
        var_dump($keys);
        var_dump($arr);
    }
    
    $jsonDecode = null;

### Indexing JSON.
 

    <?php
    require "JsonDecode.php";
    
    // Create JsonEncode Object.
    $JsonDecode = new JsonDecode('/usr/local/var/www/rnd/test.json');

    // Validate JSON
    $JsonDecode->validate();

    // Indexing JSON
    $JsonDecode->indexJSON();
    
    $jsonDecode = null;

### Accessing data via keys pattern.
 

    <?php
    require "JsonDecode.php";
    
    // Create JsonEncode Object.
    $JsonDecode = new JsonDecode('/usr/local/var/www/rnd/test.json');

    // Validate JSON
    $JsonDecode->validate();

    // Indexing JSON
    $JsonDecode->indexJSON();

    // Load specific Keys seperated by colon
    $JsonDecode->load('data:0:data1');

    // Get hierarchy output based on loaded keys
    foreach($JsonDecode->process() as $keys => $arr) {
        var_dump($keys);
        var_dump($arr);
    }
    
    $jsonDecode = null;

### Accessing data of Array after indexing JSON.
 

    <?php
    require "JsonDecode.php";
    
    // Create JsonEncode Object.
    $JsonDecode = new JsonDecode('/usr/local/var/www/rnd/test.json');

    // Validate JSON
    $JsonDecode->validate();

    // Indexing JSON
    $JsonDecode->indexJSON();

    // Transverse across object key 'data'
    for ($i=0, $i_count = $JsonDecode->getCount('data'); $i < $i_count; $i++) {
         print_r($JsonDecode->get('data:'.$i));
    }
    
    $jsonDecode = null;


