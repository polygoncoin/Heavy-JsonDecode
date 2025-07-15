<?php
/**
 * Custom Json Decode
 * php version 7
 *
 * @category  JsonDecode
 * @package   CustomJsonDecode
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
namespace CustomJsonDecode;

use CustomJsonDecode\JsonDecodeObject;

/**
 * Custom Json Decode Engine
 * php version 7
 *
 * @category  JsonDecode
 * @package   CustomJsonDecode
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class JsonDecodeEngine
{
    /**
     * File Handle
     *
     * @var null|resource
     */
    private $_jsonFileHandle = null;

    /**
     * Array of JsonDecodeObject _objects
     *
     * @var JsonDecodeObject[]
     */
    private $_objects = [];

    /**
     * Current JsonDecodeObject object
     *
     * @var null|JsonDecodeObject
     */
    private $_currentObject = null;

    /**
     * Characters that are escaped while creating JSON
     *
     * @var string[]
     */
    private $_escapers = array(
        "\\", "\"", "\n", "\r", "\t", "\x08", "\x0c", ' '
    );

    /**
     * Characters that are escaped with for $_escapers while creating JSON
     *
     * @var string[]
     */
    private $_replacements = array(
        "\\\\", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b", ' '
    );

    /**
     * JSON file start position
     *
     * @var null|int
     */
    public $startIndex = null;

    /**
     * JSON file end position
     *
     * @var null|int
     */
    public $endIndex = null;

    /**
     * JSON char counter
     * Starts from $_s_ till $_e_
     *
     * @var null|int
     */
    private $_charCounter = null;

    /**
     * JsonDecode constructor
     *
     * @param resource $_jsonFileHandle JSON file handle
     */
    public function __construct(&$_jsonFileHandle)
    {
        $this->_jsonFileHandle = &$_jsonFileHandle;
    }

    /**
     * Start processing the JSON string
     *
     * @param bool $index Index output
     *
     * @return \Generator
     */
    public function process($index = false): \Generator
    {
        // Flags Variable
        $quote = false;

        // Values inside Quotes
        $keyValue = '';
        $valueValue = '';

        // Values without Quotes
        $nullStr = null;

        // Variable mode - key/value;
        $varMode = 'keyValue';

        $strToEscape  = '';
        $prevIsEscape = false;

        $this->_charCounter = $this->startIndex !== null ? $this->startIndex : 0;
        fseek(
            stream: $this->_jsonFileHandle,
            offset: $this->_charCounter,
            whence: SEEK_SET
        );

        for (;
            (
                ($char = fgetc(stream: $this->_jsonFileHandle)) !== false &&
                (
                    ($this->endIndex === null) ||
                    ($this->endIndex !== null
                        && $this->_charCounter <= $this->endIndex
                    )
                )
            )
            ;$this->_charCounter++
        ) {
            switch (true) {
            case $quote === false:
                switch (true) {
                // Start of Key or value inside quote
                case $char === '"':
                    $quote = true;
                    $nullStr = '';
                    break;

                //Switch mode to value collection after colon
                case $char === ':':
                    $varMode = 'valueValue';
                    break;

                // Start or End of Array
                case in_array(needle: $char, haystack: ['[',']','{','}']):
                    $arr = $this->_handleOpenClose(
                        char: $char,
                        keyValue: $keyValue,
                        nullStr: $nullStr,
                        index: $index
                    );
                    if ($arr !== false) {
                        yield $arr['key'] => $arr['value'];
                    }
                    $keyValue = $valueValue = '';
                    $varMode = 'keyValue';
                    break;

                // Check for null values
                case $char === ',' && !is_null(value: $nullStr):
                    $nullStr = $this->_checkNullStr(nullStr: $nullStr);
                    switch ($this->_currentObject->mode) {
                    case 'Array':
                        $this->_currentObject->arrayValues[] = $nullStr;
                        break;
                    case 'Assoc':
                        if (!empty($keyValue)) {
                            $this->_currentObject->assocValues[$keyValue] = $nullStr;
                        }
                        break;
                    }
                    $nullStr = null;
                    $keyValue = $valueValue = '';
                    $varMode = 'keyValue';
                    break;

                //Switch mode to value collection after colon
                case in_array(needle: $char, haystack: $this->_escapers):
                    break;

                // Append char to null string
                case !in_array(needle: $char, haystack: $this->_escapers):
                    $nullStr .= $char;
                    break;
                }
                break;

            case $quote === true:
                switch (true) {
                // Collect string to be escaped
                case $varMode === 'valueValue'
                    && ($char === '\\'
                        || ($prevIsEscape
                            && in_array(
                                needle: $strToEscape . $char,
                                haystack: $this->_replacements
                            )
                    )):
                    $strToEscape .= $char;
                    $prevIsEscape = true;
                    break;

                // Escape value with char
                case $varMode === 'valueValue'
                    && $prevIsEscape === true
                    && in_array(
                        needle: $strToEscape . $char,
                        haystack: $this->_replacements
                    ):
                    $$varMode .= str_replace(
                        search: $this->_replacements,
                        replace: $this->_escapers,
                        subject: $strToEscape . $char
                    );
                    $strToEscape = '';
                    $prevIsEscape = false;
                    break;

                // Escape value without char
                case $varMode === 'valueValue' && $prevIsEscape === true
                    && in_array(
                        needle: $strToEscape,
                        haystack: $this->_replacements
                    ):
                    $$varMode .= str_replace(
                        search: $this->_replacements,
                        replace: $this->_escapers,
                        subject: $strToEscape
                    ) . $char;
                    $strToEscape = '';
                    $prevIsEscape = false;
                    break;

                // Closing double quotes
                case $char === '"':
                    $quote = false;
                    switch (true) {
                    // Closing quote of Key
                    case $varMode === 'keyValue':
                        $varMode = 'valueValue';
                        break;

                    // Closing quote of Value
                    case $varMode === 'valueValue':
                        $this->_currentObject->assocValues[$keyValue] = $valueValue;
                        $keyValue = $valueValue = '';
                        $varMode = 'keyValue';
                        break;
                    }
                    break;

                // Collect values for key or value
                default:
                    $$varMode .= $char;
                }
                break;
            }
        }
        $this->_objects = [];
        $this->_currentObject = null;
    }

    /**
     * Get JSON string
     *
     * @return bool|string
     */
    public function getJsonString(): bool|string
    {
        $offset = $this->startIndex !== null ? $this->startIndex : 0;
        $length = $this->endIndex - $offset + 1;

        return stream_get_contents(
            stream: $this->_jsonFileHandle,
            length: $length,
            offset: $offset
        );
    }

    /**
     * Handles array / object open close char
     *
     * @param string $char     Character among any one "[" "]" "{" "}"
     * @param string $keyValue String value of key of an object
     * @param string $nullStr  String present in JSON without double quotes
     * @param bool   $index    Index output
     *
     * @return array|bool
     */
    private function _handleOpenClose($char, $keyValue, $nullStr, $index): array|bool
    {
        $arr = false;
        switch ($char) {
        case '[':
            if (!$index) {
                $arr = [
                    'key' => $this->_getKeys(),
                    'value' => $this->_getObjectValues()
                ];
            }
            $this->_increment();
            $this->_startArray(key: $keyValue);
            break;
        case '{':
            if (!$index) {
                $arr = [
                    'key' => $this->_getKeys(),
                    'value' => $this->_getObjectValues()
                ];
            }
            $this->_increment();
            $this->_startObject(key: $keyValue);
            break;
        case ']':
            if (!empty($keyValue)) {
                $this->_currentObject->arrayValues[] = $keyValue;
                if (is_null(value: $this->_currentObject->arrayKey)) {
                    $this->_currentObject->arrayKey = 0;
                } else {
                    $this->_currentObject->arrayKey++;
                }
            }
            if ($index) {
                $arr = [
                    'key' => $this->_getKeys(),
                    'value' => [
                        '_s_' => $this->_currentObject->startIndex,
                        '_e_' => $this->_charCounter
                    ]
                ];
            } else {
                if (!empty($this->_currentObject->arrayValues)) {
                    $arr = [
                        'key' => $this->_getKeys(),
                        'value' => $this->_currentObject->arrayValues
                    ];
                }
            }
            $this->_currentObject = null;
            $this->_popPreviousObject();
            break;
        case '}':
            if (!empty($keyValue) && !empty($nullStr)) {
                $nullStr = $this->_checkNullStr(nullStr: $nullStr);
                $this->_currentObject->assocValues[$keyValue] = $nullStr;
            }
            if ($index) {
                $arr = [
                    'key' => $this->_getKeys(),
                    'value' => [
                        '_s_' => $this->_currentObject->startIndex,
                        '_e_' => $this->_charCounter
                    ]
                ];
            } else {
                if (!empty($this->_currentObject->assocValues)) {
                    $arr = [
                        'key' => $this->_getKeys(),
                        'value' => $this->_currentObject->assocValues
                    ];
                }
            }
            $this->_currentObject = null;
            $this->_popPreviousObject();
            break;
        }
        if ($arr !== false
            && !empty($arr)
            && isset($arr['value'])
            && $arr['value'] !== false
            && count(value: $arr['value']) > 0
        ) {
            return $arr;
        }
        return false;
    }

    /**
     * Check String present in JSON without double quotes for null or int
     *
     * @param string $nullStr String present in JSON without double quotes
     *
     * @return bool|int|null
     */
    private function _checkNullStr($nullStr): bool|int|null
    {
        $return = false;
        if ($nullStr === 'null') {
            $return = null;
        } elseif (is_numeric(value: $nullStr)) {
            $return = (int)$nullStr;
        }
        if ($return === false) {
            $this->_isBadJson(str: $nullStr);
        }
        return $return;
    }

    /**
     * Start of array
     *
     * @param null|string $key Used while creating simple array inside an object
     *
     * @return void
     */
    private function _startArray($key = null): void
    {
        $this->_pushCurrentObject(key: $key);
        $this->_currentObject = new JsonDecodeObject(mode: 'Array', assocKey: $key);
        $this->_currentObject->startIndex = $this->_charCounter;
    }

    /**
     * Start of object
     *
     * @param null|string $key Used while creating object inside an object
     *
     * @return void
     */
    private function _startObject($key = null): void
    {
        $this->_pushCurrentObject(key: $key);
        $this->_currentObject = new JsonDecodeObject(mode: 'Assoc', assocKey: $key);
        $this->_currentObject->startIndex = $this->_charCounter;
    }

    /**
     * Push current object
     *
     * @param string $key Key
     *
     * @return void
     */
    private function _pushCurrentObject($key): void
    {
        if ($this->_currentObject) {
            if ($this->_currentObject->mode === 'Assoc'
                && (is_null(value: $key) || empty(trim(string: $key)))
            ) {
                $this->_isBadJson(str: $key);
            }
            if ($this->_currentObject->mode === 'Array'
                && (is_null(value: $key) || empty(trim(string: $key)))
            ) {
                $this->_isBadJson(str: $key);
            }
            array_push($this->_objects, $this->_currentObject);
        }
    }

    /**
     * Pop Previous object
     *
     * @return void
     */
    private function _popPreviousObject(): void
    {
        if (count(value: $this->_objects) > 0) {
            $this->_currentObject = array_pop($this->_objects);
        } else {
            $this->_currentObject = null;
        }
    }

    /**
     * Increment arrayKey counter for array of _objects or arrays
     *
     * @return void
     */
    private function _increment(): void
    {
        if (!is_null(value: $this->_currentObject)
            && $this->_currentObject->mode === 'Array'
        ) {
            if (is_null(value: $this->_currentObject->arrayKey)) {
                $this->_currentObject->arrayKey = 0;
            } else {
                $this->_currentObject->arrayKey++;
            }
        }
    }

    /**
     * Returns extracted object values
     *
     * @return array|bool
     */
    private function _getObjectValues(): array|bool
    {
        $arr = false;
        if (!is_null(value: $this->_currentObject)
            && $this->_currentObject->mode === 'Assoc'
            && count(value: $this->_currentObject->assocValues) > 0
        ) {
            $arr = $this->_currentObject->assocValues;
            $this->_currentObject->assocValues = [];
        }
        return $arr;
    }

    /**
     * Check for a valid JSON
     *
     * @param string $str String
     *
     * @return void
     */
    private function _isBadJson($str): void
    {
        $str =  !is_null(value: $str) ? trim(string: $str) : $str;
        if (!empty($str)) {
            die("Invalid JSON: {$str}");
        }
    }

    /**
     * Generated Array
     *
     * @return array
     */
    private function _getKeys(): array
    {
        $keys = [];
        $return = &$keys;
        $objCount = count(value: $this->_objects);
        if ($objCount > 0) {
            for ($i=0; $i<$objCount; $i++) {
                switch ($this->_objects[$i]->mode) {
                case 'Assoc':
                    if (!is_null(value: $this->_objects[$i]->assocKey)) {
                        $keys[] = $this->_objects[$i]->assocKey;
                    }
                    break;
                case 'Array':
                    if (!is_null(value: $this->_objects[$i]->assocKey)) {
                        $keys[] = $this->_objects[$i]->assocKey;
                    }
                    if (!is_null(value: $this->_objects[$i]->arrayKey)) {
                        $keys[] = $this->_objects[$i]->arrayKey;
                    }
                    break;
                }
            }
        }
        if ($this->_currentObject) {
            switch ($this->_currentObject->mode) {
            case 'Assoc':
                if (!is_null(value: $this->_currentObject->assocKey)) {
                    $keys[] = $this->_currentObject->assocKey;
                }
                break;
            case 'Array':
                if (!is_null(value: $this->_currentObject->assocKey)) {
                    $keys[] = $this->_currentObject->assocKey;
                }
                break;
            }
        }
        return $return;
    }

    /**
     * Generated Assoc Array
     *
     * @return array
     */
    private function _getAssocKeys(): array
    {
        $keys = [];
        $return = &$keys;
        $objCount = count(value: $this->_objects);
        if ($objCount > 0) {
            for ($i=0; $i<$objCount; $i++) {
                switch ($this->_objects[$i]->mode) {
                case 'Assoc':
                    if (!is_null(value: $this->_objects[$i]->assocKey)) {
                        $keys[$this->_objects[$i]->assocKey] = [];
                        $keys = &$keys[$this->_objects[$i]->assocKey];
                    }
                    break;
                case 'Array':
                    if (!is_null(value: $this->_objects[$i]->assocKey)) {
                        $keys[$this->_objects[$i]->assocKey] = [];
                        $keys = &$keys[$this->_objects[$i]->assocKey];
                    }
                    if (!is_null(value: $this->_objects[$i]->arrayKey)) {
                        $keys[$this->_objects[$i]->arrayKey] = [];
                        $keys = &$keys[$this->_objects[$i]->arrayKey];
                    }
                    break;
                }
            }
        }
        if ($this->_currentObject) {
            switch ($this->_currentObject->mode) {
            case 'Assoc':
                if (!is_null(value: $this->_currentObject->assocKey)) {
                    $keys[$this->_currentObject->assocKey] = [];
                    $keys = &$keys[$this->_currentObject->assocKey];
                }
                break;
            case 'Array':
                if (!is_null(value: $this->_currentObject->assocKey)) {
                    $keys[$this->_currentObject->assocKey] = [];
                    $keys = &$keys[$this->_currentObject->assocKey];
                }
                break;
            }
        }
        return $return;
    }
}
