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
			'filesize' => new Twig_Filter_Function('format_bytes', Array('needs_environment' => false)),
			'truncate' => new Twig_Filter_Function('twig_truncate_filter', array('needs_environment' => false)),
			'truncate_body' => new Twig_Filter_Function('truncate', array('needs_environment' => false)),
			'extension' => new Twig_Filter_Function('twig_extension_filter', array('needs_environment' => false)),
			'sprintf' => new Twig_Filter_Function('sprintf', array('needs_environment' => false)),
			'capcode' => new Twig_Filter_Function('capcode', array('needs_environment' => false)),
			'hasPermission' => new Twig_Filter_Function('twig_hasPermission_filter', array('needs_environment' => false)),
			'date' => new Twig_Filter_Function('twig_date_filter', array('needs_environment' => false)),
			'poster_id' => new Twig_Filter_Function('poster_id', array('needs_environment' => false)),
			'remove_whitespace' => new Twig_Filter_Function('twig_remove_whitespace_filter', array('needs_environment' => false)),
			'count' => new Twig_Filter_Function('count', array('needs_environment' => false)),
			'until' => new Twig_Filter_Function('until', array('needs_environment' => false))
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
			'time' => new Twig_Filter_Function('time', array('needs_environment' => false))
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

function twig_remove_whitespace_filter($data) {
	return preg_replace('/[\t\r\n]/', '', $data);
}

function twig_date_filter($date, $format) {
	return strftime($format, $date);
}

function twig_hasPermission_filter($mod, $permission, $board) {
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
	if (strlen($value) > $length) {
		if ($preserve) {
			if (false !== ($breakpoint = strpos($value, ' ', $length))) {
				$length = $breakpoint;
			}
		}
		return substr($value, 0, $length) . $separator;
	}
	return $value;
}

