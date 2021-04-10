<?php


namespace Olegnax\Athlete2\Model;


class Encryption
{
    /**
     * Encode.
     *
     * @param string $string String to encode.
     * @param string $key Encode key.
     *
     * @return string
     * @since 1.0.2 Removed key based encryption for better php support.
     *
     * @since 1.0.0
     */
    public static function encode($value, $key)
    {
        return trim(self::safe_b64encode($value));
    }

    /**
     * Safe base 64 encode.
     *
     * @param string $string String to encode.
     *
     * @return string
     * @since 1.0.0
     *
     */
    public static function safe_b64encode($string)
    {
        $data = base64_encode($string);
        $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);

        return $data;
    }

    /**
     * Decode.
     *
     * @param string $string String to decode.
     * @param string $key Decode key.
     *
     * @return string
     * @since 1.0.2 Removed key based encryption for better php support.
     *
     * @since 1.0.0
     */
    public static function decode($value, $key)
    {
        return trim(self::safe_b64decode($value));
    }

    /**
     * Safe base 64 decode.
     *
     * @param string $string String to decode.
     *
     * @return string
     * @since 1.0.0
     *
     */
    public static function safe_b64decode($string)
    {
        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }

        return base64_decode($data);
    }
}