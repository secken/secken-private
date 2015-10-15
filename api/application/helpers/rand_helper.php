<?php

if ( ! function_exists('create_rand_string'))
{
    function create_rand_string( $length = 8 )
    {

        // 密码字符集，可任意添加你需要的字符
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password ='';
        for ($i = 0; $i < $length; $i++)
        {
            // 这里提供两种字符获取方式
            // 第一种是使用 substr 截取$chars中的任意一位字符；
            $password .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }

        return $password;
    }
}
