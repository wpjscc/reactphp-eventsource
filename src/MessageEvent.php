<?php

namespace Clue\React\EventSource;

class MessageEvent
{
    /**
     * @param string $data
     * @param string $lastEventId
     * @param float  $retryTime   passed by reference, will be updated with `retry` field in seconds if valid
     * @return self
     * @internal
     */
    public static function parse($data, $lastEventId, &$retryTime = 0.0)
    {
        $lines = preg_split(
            '/\r\n|\r(?!\n)|\n/S',
            $data
        );

        $data = '';
        $id = $lastEventId;
        $type = 'message';

        foreach ($lines as $line) {
            $name = strstr($line, ':', true);
            $value = (string) substr(strstr($line, ':'), 1);
            if (isset($value[0]) && $value[0] === ' ') {
                $value = (string) substr($value, 1);
            }
            if ($name === 'data') {
                $data .= $value . "\n";
            } elseif ($name === 'id') {
                $id = $value;
            } elseif ($name === 'event' && $value !== '') {
                $type = $value;
            } elseif ($name === 'retry' && $value === (string)(int)$value && $value >= 0) {
                $retryTime = $value * 0.001;
            }
        }

        if (substr($data, -1) === "\n") {
            $data = substr($data, 0, -1);
        }

        return new self($data, $id, $type);
    }

    /**
     * @internal
     * @param string $data
     * @param string $lastEventId
     * @param string $type
     */
    private function __construct($data, $lastEventId, $type)
    {
        $this->data = $data;
        $this->lastEventId = $lastEventId;
        $this->type = $type;
    }

    /**
     * @var string
     * @readonly
     */
    public $data = '';

    /**
     * @var string
     * @readonly
     */
    public $lastEventId = '';

    /**
     * @var string
     * @readonly
     */
    public $type = 'message';
}
