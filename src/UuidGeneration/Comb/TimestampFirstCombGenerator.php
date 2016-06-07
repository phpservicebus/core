<?php
namespace PSB\Core\UuidGeneration\Comb;


use PSB\Core\UuidGeneration\UuidGeneratorInterface;

class TimestampFirstCombGenerator implements UuidGeneratorInterface
{
    /**
     * @return string
     */
    public function generate()
    {
        $time = explode(' ', microtime(false));
        $seconds = (int)$time[1];
        $milliseconds = (int)substr($time[0], 2, 4);

        $params = array(
            // 32 bits for "time_low"
            $seconds >> 16,
            $seconds & 0xffff,
            // 16 bits for "time_mid"
            $milliseconds,
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clock_seq_hi_res",
            // 8 bits for "clock_seq_low",
            // two most significant bits hold one and zero for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );

        return vsprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', $params);
    }
}
