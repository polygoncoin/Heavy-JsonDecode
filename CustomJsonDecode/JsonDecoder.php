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

use CustomJsonDecode\JsonDecode;

/**
 * Custom Json Decoder
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
class JsonDecoder
{
    /**
     * JSON generator object
     *
     * @var null|JsonDecode
     */
    public static $jsonDecodeObj = null;

    /**
     * Initialize
     *
     * @param resource $jsonFileHandle JSON File handle
     *
     * @return void
     */
    public static function init(&$jsonFileHandle): void
    {
        self::$jsonDecodeObj = new JsonDecode(jsonFileHandle: $jsonFileHandle);
    }

    /**
     * JSON generator object
     *
     * @return bool|JsonDecode
     */
    public static function getObject(): bool|JsonDecode
    {
        if (is_null(value: self::$jsonDecodeObj)) {
            return false;
        }
        return self::$jsonDecodeObj;
    }
}
