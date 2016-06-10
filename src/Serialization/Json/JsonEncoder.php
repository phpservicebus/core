<?php
namespace PSB\Core\Serialization\Json;


use PSB\Core\Exception\JsonSerializerException;
use PSB\Core\Util\Guard;

class JsonEncoder
{
    const FLOAT_CASTER = 'PSBFloat';

    const KEY_UTF8ENCODED = 1;
    const VALUE_UTF8ENCODED = 2;

    /**
     * @var int
     */
    private $jsonEncodeOptions;

    /**
     * @var string
     */
    private $encodingAnnotation;

    /**
     * @param int    $jsonEncodeOptions
     * @param string $encodingAnnotation
     */
    public function __construct($jsonEncodeOptions = JSON_UNESCAPED_UNICODE, $encodingAnnotation = '@utf8encoded')
    {
        Guard::againstNullAndEmpty('encodingAnnotation', $encodingAnnotation);

        if (PHP_VERSION_ID >= 50606) {
            $jsonEncodeOptions |= JSON_PRESERVE_ZERO_FRACTION;
        }

        $this->jsonEncodeOptions = $jsonEncodeOptions;
        $this->encodingAnnotation = $encodingAnnotation;
    }

    /**
     * @param array $data
     *
     * @return string
     */
    public function encode(array $data)
    {
        $data = $this->escapeFloatsIfNeeded($data);

        $json = json_encode($data, $this->jsonEncodeOptions);
        if ($json === false || json_last_error() != JSON_ERROR_NONE) {
            if (json_last_error() != JSON_ERROR_UTF8) {
                throw new JsonSerializerException('Invalid data to encode to JSON. Error: ' . json_last_error_msg());
            }

            $data = $this->encodeNonUtf8ToUtf8($data);
            $json = json_encode($data, $this->jsonEncodeOptions);

            if ($json === false || json_last_error() != JSON_ERROR_NONE) {
                throw new JsonSerializerException('Invalid data to encode to JSON. Error: ' . json_last_error_msg());
            }
        }

        return $this->unescapeFloatsIfNeeded($json);
    }

    /**
     * @param string $json
     *
     * @return array
     */
    public function decode($json)
    {
        $data = json_decode($json, true);
        if ($data === null && json_last_error() != JSON_ERROR_NONE) {
            throw new JsonSerializerException('Invalid JSON to unserialize.');
        }

        if (!is_array($data)) {
            throw new JsonSerializerException('Given JSON cannot represent an object.');
        }

        if (mb_strpos($json, $this->encodingAnnotation) !== false) {
            $data = $this->decodeNonUtf8FromUtf8($data);
        }

        return $data;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function escapeFloatsIfNeeded(array $data)
    {
        if (PHP_VERSION_ID >= 50606) {
            return $data;
        }

        array_walk_recursive(
            $data,
            function (&$value) {
                if (is_float($value) && ctype_digit((string)$value)) {
                    // Due to PHP bug #50224, floats with no decimals are converted to integers when encoded
                    $value = '(' . self::FLOAT_CASTER . ')' . $value . '.0';
                }
            }
        );

        return $data;
    }

    /**
     * @param string $json
     *
     * @return string
     */
    private function unescapeFloatsIfNeeded($json)
    {
        if (PHP_VERSION_ID >= 50606) {
            return $json;
        }

        $prevEncoding = mb_regex_encoding();
        mb_regex_encoding('UTF-8');
        $json = mb_ereg_replace('"\(' . self::FLOAT_CASTER . '\)([^"]+)"', '\1', $json);
        mb_regex_encoding($prevEncoding);

        return $json;
    }

    /**
     * @param array $serializedData
     *
     * @return array
     */
    private function encodeNonUtf8ToUtf8(array $serializedData)
    {
        $encodedKeys = [];
        $encodedData = [];
        foreach ($serializedData as $key => $value) {
            if (is_array($value)) {
                $value = $this->encodeNonUtf8ToUtf8($value);
            }

            if (!mb_check_encoding($key, 'UTF-8')) {
                $key = mb_convert_encoding($key, 'UTF-8', '8bit');
                $encodedKeys[$key] = (isset($encodedKeys[$key]) ? $encodedKeys[$key] : 0) | static::KEY_UTF8ENCODED;
            }

            if (is_string($value)) {
                if (!mb_check_encoding($value, 'UTF-8')) {
                    $value = mb_convert_encoding($value, 'UTF-8', '8bit');
                    $encodedKeys[$key] = (isset($encodedKeys[$key]) ? $encodedKeys[$key] : 0) | static::VALUE_UTF8ENCODED;
                }
            }

            $encodedData[$key] = $value;
        }

        if (!empty($encodedKeys)) {
            $encodedData[$this->encodingAnnotation] = $encodedKeys;
        }

        return $encodedData;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function decodeNonUtf8FromUtf8(array $data)
    {
        $encodedKeys = [];
        if (isset($data[$this->encodingAnnotation])) {
            $encodedKeys = $data[$this->encodingAnnotation];
            unset($data[$this->encodingAnnotation]);
        }

        $decodedData = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = $this->decodeNonUtf8FromUtf8($value);
            }

            if (isset($encodedKeys[$key])) {
                $originalKey = $key;
                if ($encodedKeys[$key] & static::KEY_UTF8ENCODED) {
                    $key = mb_convert_encoding($key, '8bit', 'UTF-8');
                }
                if ($encodedKeys[$originalKey] & static::VALUE_UTF8ENCODED) {
                    $value = mb_convert_encoding($value, '8bit', 'UTF-8');
                }
            }

            $decodedData[$key] = $value;
        }

        return $decodedData;
    }
}
