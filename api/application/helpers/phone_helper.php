<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter Phone Helpers
 */

// ------------------------------------------------------------------------

if ( ! function_exists('valid_phone'))
{

	function valid_phone($phonenumber)
	{
        return preg_match("/^1[34578]\d{9}$/",$phonenumber) ? TRUE : FALSE;
	}
}
