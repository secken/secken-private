<?php

if ( ! function_exists('get_signature'))
{
    function get_signature($params, $power_key)
    {
        ksort($params);
        $str = '';

        foreach ( $params as $key => $value ) {
            $str .= "$key=$value";
        }

        log_message('debug',$str.'--'.$power_key);
        return sha1($str . $power_key);
    }
}
