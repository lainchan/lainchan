<?php

class Twig_Extensions_Extension_Tinyboard extends Twig_Extension
{
	/**
	* Returns a list of filters to add to the existing list.
	*
	* @return array An array of filters
	*/
	public function getFilters()
	{
		return Array(
			'filesize' => new Twig_Filter_Function('format_bytes'),
			'truncate' => new Twig_Filter_Function('twig_truncate_filter'),
			'truncate_body' => new Twig_Filter_Function('truncate'),
			'extension' => new Twig_Filter_Function('twig_extension_filter'),
			'sprintf' => new Twig_Filter_Function('sprintf'),
			'capcode' => new Twig_Filter_Function('capcode'),
			'hasPermission' => new Twig_Filter_Function('twig_hasPermission_filter'),
			'date' => new Twig_Filter_Function('twig_date_filter'),
			'poster_id' => new Twig_Filter_Function('poster_id'),
			'remove_whitespace' => new Twig_Filter_Function('twig_remove_whitespace_filter'),
			'count' => new Twig_Filter_Function('count'),
			'ago' => new Twig_Filter_Function('ago'),
			'until' => new Twig_Filter_Function('until'),
			'split' => new Twig_Filter_Function('twig_split_filter'),
			'push' => new Twig_Filter_Function('twig_push_filter'),
		);
	}
	
	/**
	* Returns a list of functions to add to the existing list.
	*
	* @return array An array of filters
	*/
	public function getFunctions()
	{
		return Array(
			'time' => new Twig_Filter_Function('time'),
			'timezone' => new Twig_Filter_Function('twig_timezone_function'),
			'hiddenInputs' => new Twig_Filter_Function('hiddenInputs'),
			'hiddenInputsHash' => new Twig_Filter_Function('hiddenInputsHash'),
		);
	}
	
	/**
	* Returns the name of the extension.
	*
	* @return string The extension name
	*/
	public function getName()
	{
		return 'tinyboard';
	}
}

function twig_timezone_function() {
	// there's probably a much easier way of doing this
	return sprintf("%s%02d", ($hr = (int)floor(($tz = date('Z')) / 3600)) > 0 ? '+' : '-', abs($hr)) . ':' . sprintf("%02d", (($tz / 3600) - $hr) * 60);
}

function twig_split_filter($str, $delim) {
	return explode($delim, $str);
}

function twig_push_filter($array, $value) {
	array_push($array, $value);
	return $array;
}

function twig_remove_whitespace_filter($data) {
	return preg_replace('/[\t\r\n]/', '', $data);
}

function twig_date_filter($date, $format) {
	return strftime($format, $date);
}

function twig_hasPermission_filter($mod, $permission, $board = false) {
	return hasPermission($permission, $board, $mod);
}

function twig_extension_filter($value, $case_insensitive = true) {
	$ext = substr($value, strrpos($value, '.') + 1);
	if($case_insensitive)
		$ext = strtolower($ext);		
	return $ext;
}

function twig_sprintf_filter( $value, $var) {
	return sprintf($value, $var);
}

function twig_truncate_filter($value, $length = 30, $preserve = false, $separator = '&hellip;') {
	if (mb_strlen($value) > $length) {
		if ($preserve) {
			if (false !== ($breakpoint = strpos($value, ' ', $length))) {
				$length = $breakpoint;
			}
		}
		return substr($value, 0, $length) . $separator;
	}
	return $value;
}

