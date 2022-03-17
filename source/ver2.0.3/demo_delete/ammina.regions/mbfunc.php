<?
function amreg_strlen($string)
{
	if (function_exists("mb_strlen")) {
		return mb_strlen($string);
	}
	return strlen($string);
}

function amreg_strpos($haystack, $needle, $offset = 0)
{
	if (function_exists("mb_strpos")) {
		return mb_strpos($haystack, $needle, $offset);
	}
	return strpos($haystack, $needle, $offset);
}

function amreg_strrpos($haystack, $needle, $offset = 0)
{
	if (function_exists("mb_strrpos")) {
		return mb_strrpos($haystack, $needle, $offset);
	}
	return strrpos($haystack, $needle, $offset);
}

function amreg_substr($string, $start, $length = null)
{
	if (function_exists("mb_substr")) {
		return mb_substr($string, $start, $length);
	}
	return substr($string, $start, $length);
}

function amreg_strtolower($str)
{
	if (function_exists("mb_strtolower")) {
		return mb_strtolower($str);
	}
	return strtolower($str);
}

function amreg_strtoupper($string)
{
	if (function_exists("mb_strtoupper")) {
		return mb_strtoupper($string);
	}
	return strtoupper($string);
}

function amreg_stripos($haystack, $needle, $offset = null)
{
	if (function_exists("mb_stripos")) {
		return mb_stripos($haystack, $needle, $offset);
	}
	return stripos($haystack, $needle, $offset);
}

function amreg_strripos($haystack, $needle, $offset = null)
{
	if (function_exists("mb_strripos")) {
		return mb_strripos($haystack, $needle, $offset);
	}
	return strripos($haystack, $needle, $offset);
}

function amreg_strstr($haystack, $needle, $before_needle = null)
{
	if (function_exists("mb_strstr")) {
		return mb_strstr($haystack, $needle, $before_needle);
	}
	return strstr($haystack, $needle, $before_needle);
}

function amreg_stristr($haystack, $needle, $before_needle = null)
{
	if (function_exists("mb_stristr")) {
		return mb_stristr($haystack, $needle, $before_needle);
	}
	return stristr($haystack, $needle, $before_needle);
}

function amreg_strrchr($haystack, $needle)
{
	if (function_exists("mb_strrchr")) {
		return mb_strrchr($haystack, $needle);
	}
	return strrchr($haystack, $needle);
}

function amreg_substr_count($haystack, $needle, $offset = null, $length = null)
{
	if (function_exists("mb_substr_count")) {
		if (function_exists('mb_strlen') && ((int)ini_get('mbstring.func_overload') & 2)) {
			if (!is_null($offset) || !is_null($length)) {
				$checkString = mb_substr($haystack, $offset, $length);
				$result = mb_substr_count($checkString, $needle);
			} else {
				$result = mb_substr_count($haystack, $needle);
			}
			return $result;
		}
	}
	return substr_count($haystack, $needle, $offset, $length);
}
