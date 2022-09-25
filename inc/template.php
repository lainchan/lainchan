<?php

/*
 *  Copyright (c) 2010-2013 Tinyboard Development Group
 */

require_once 'inc/bootstrap.php';
defined('TINYBOARD') or exit;

$twig = false;

function load_twig() {
	global $twig, $config;
	
	$loader = new Twig_Loader_Filesystem($config['dir']['template']);
	$loader->setPaths($config['dir']['template']);
	$twig = new Twig_Environment($loader, array(
		'autoescape' => false,
		'cache' => is_writable('templates') || (is_dir('templates/cache') && is_writable('templates/cache')) ?
			"{$config['dir']['template']}/cache" : false,
			'debug' => $config['debug'],
	));
	$twig->addExtension(new Twig_Extensions_Extension_Tinyboard());
	$twig->addExtension(new Twig_Extensions_Extension_I18n());
}


function Element($templateFile, array $options) {
	global $config, $debug, $twig, $build_pages;
	
	if (!$twig)
		load_twig();
	
	if (function_exists('create_pm_header') && ((isset($options['mod']) && $options['mod']) || isset($options['__mod'])) && !preg_match('!^mod/!', $templateFile)) {
		$options['pm'] = create_pm_header();
	}
	
	if (isset($options['body']) && $config['debug']) {
		$_debug = $debug;
		
		if (isset($debug['start'])) {
			$_debug['time']['total'] = '~' . round((microtime(true) - $_debug['start']) * 1000, 2) . 'ms';
			$_debug['time']['init'] = '~' . round(($_debug['start_debug'] - $_debug['start']) * 1000, 2) . 'ms';
			unset($_debug['start']);
			unset($_debug['start_debug']);
		}
		if ($config['try_smarter'] && isset($build_pages) && !empty($build_pages))
			$_debug['build_pages'] = $build_pages;
		$_debug['included'] = get_included_files();
		$_debug['memory'] = round(memory_get_usage(true) / (1024 * 1024), 2) . ' MiB';
		$_debug['time']['db_queries'] = '~' . round($_debug['time']['db_queries'] * 1000, 2) . 'ms';
		$_debug['time']['exec'] = '~' . round($_debug['time']['exec'] * 1000, 2) . 'ms';
		$options['body'] .=
			'<h3>Debug</h3><pre style="white-space: pre-wrap;font-size: 10px;">' .
				str_replace("\n", '<br/>', utf8tohtml(print_r($_debug, true))) .
			'</pre>';
	}
	// Read the template file
	if (@file_get_contents("{$config['dir']['template']}/${templateFile}")) {
		$body = $twig->render($templateFile, $options);
		if ($config['minify_html'] && preg_match('/\.html$/', $templateFile)) {
			$body = trim(preg_replace("/[\t\r\n]/", '', $body));
		}
		return $body;
	} else {
		throw new Exception("Template file '${templateFile}' does not exist or is empty in '{$config['dir']['template']}'!");
	}
}

class Twig_Extensions_Extension_Tinyboard extends Twig\Extension\AbstractExtension
{
	/**
	* Returns a list of filters to add to the existing list.
	*
	* @return array An array of filters
	*/
	public function getFilters()
	{
		return [
			new Twig\TwigFilter('filesize', 'format_bytes'),
			new Twig\TwigFilter('truncate', 'twig_truncate_filter'),
			new Twig\TwigFilter('truncate_body', 'truncate'),
			new Twig\TwigFilter('truncate_filename', 'twig_filename_truncate_filter'),
			new Twig\TwigFilter('extension', 'twig_extension_filter'),
			new Twig\TwigFilter('sprintf', 'sprintf'),
			new Twig\TwigFilter('capcode', 'capcode'),
			new Twig\TwigFilter('remove_modifiers', 'remove_modifiers'),
			new Twig\TwigFilter('hasPermission', 'twig_hasPermission_filter'),
			new Twig\TwigFilter('date', 'twig_date_filter'),
			new Twig\TwigFilter('poster_id', 'poster_id'),
			new Twig\TwigFilter('remove_whitespace', 'twig_remove_whitespace_filter'),
			new Twig\TwigFilter('ago', 'ago'),
			new Twig\TwigFilter('until', 'until'),
			new Twig\TwigFilter('push', 'twig_push_filter'),
			new Twig\TwigFilter('bidi_cleanup', 'bidi_cleanup'),
			new Twig\TwigFilter('addslashes', 'addslashes'),
		];
	}

	/**
	* Returns a list of functions to add to the existing list.
	*
	* @return array An array of filters
	*/
	public function getFunctions()
	{
		return [
			new Twig\TwigFunction('time', 'time'),
			new Twig\TwigFunction('floor', 'floor'),
			new Twig\TwigFunction('timezone', 'twig_timezone_function'),
			new Twig\TwigFunction('hiddenInputs', 'hiddenInputs'),
			new Twig\TwigFunction('hiddenInputsHash', 'hiddenInputsHash'),
			new Twig\TwigFunction('ratio', 'twig_ratio_function'),
			new Twig\TwigFunction('secure_link_confirm', 'twig_secure_link_confirm'),
			new Twig\TwigFunction('secure_link', 'twig_secure_link'),
			new Twig\TwigFunction('link_for', 'link_for')
		];
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
	return 'Z';
}

function twig_push_filter($array, $value) {
	array_push($array, $value);
	return $array;
}

function twig_remove_whitespace_filter($data) {
	return preg_replace('/[\t\r\n]/', '', $data);
}

function custom_strftime(string $format, $timestamp = null, ?string $locale = null): string
{
        if (null === $timestamp) {
                $timestamp = new \DateTime;
        }
        elseif (is_numeric($timestamp)) {
                $timestamp = date_create('@' . $timestamp);

                if ($timestamp) {
                        $timestamp->setTimezone(new \DateTimezone(date_default_timezone_get()));
                }
        }
        elseif (is_string($timestamp)) {
                $timestamp = date_create($timestamp);
        }

        if (!($timestamp instanceof \DateTimeInterface)) {
                throw new \InvalidArgumentException('$timestamp argument is neither a valid UNIX timestamp, a valid date-time string or a DateTime object.');
        }

        $locale = substr((string) $locale, 0, 5);

        $intl_formats = [
                '%a' => 'EEE',  // An abbreviated textual representation of the day     Sun through Sat
                '%A' => 'EEEE', // A full textual representation of the day     Sunday through Saturday
                '%b' => 'MMM',  // Abbreviated month name, based on the locale  Jan through Dec
                '%B' => 'MMMM', // Full month name, based on the locale January through December
                '%h' => 'MMM',  // Abbreviated month name, based on the locale (an alias of %b) Jan through Dec
        ];

        $intl_formatter = function (\DateTimeInterface $timestamp, string $format) use ($intl_formats, $locale) {
                $tz = $timestamp->getTimezone();
                $date_type = \IntlDateFormatter::FULL;
                $time_type = \IntlDateFormatter::FULL;
                $pattern = '';

                // %c = Preferred date and time stamp based on locale
                // Example: Tue Feb 5 00:45:10 2009 for February 5, 2009 at 12:45:10 AM
                if ($format == '%c') {
                        $date_type = \IntlDateFormatter::LONG;
                        $time_type = \IntlDateFormatter::SHORT;
                }
                // %x = Preferred date representation based on locale, without the time
                // Example: 02/05/09 for February 5, 2009
                elseif ($format == '%x') {
                        $date_type = \IntlDateFormatter::SHORT;
                        $time_type = \IntlDateFormatter::NONE;
                }
                // Localized time format
                elseif ($format == '%X') {
                        $date_type = \IntlDateFormatter::NONE;
                        $time_type = \IntlDateFormatter::MEDIUM;
                }
                else {
                        $pattern = $intl_formats[$format];
                }

                return (new \IntlDateFormatter($locale, $date_type, $time_type, $tz, null, $pattern))->format($timestamp);
        };

        // Same order as https://www.php.net/manual/en/function.strftime.php
        $translation_table = [
                // Day
                '%a' => $intl_formatter,
                '%A' => $intl_formatter,
                '%d' => 'd',
                '%e' => function ($timestamp) {
                        return sprintf('% 2u', $timestamp->format('j'));
                },
                '%j' => function ($timestamp) {
                        // Day number in year, 001 to 366
                        return sprintf('%03d', $timestamp->format('z')+1);
                },
                '%u' => 'N',
                '%w' => 'w',

                // Week
                '%U' => function ($timestamp) {
                        // Number of weeks between date and first Sunday of year
                        $day = new \DateTime(sprintf('%d-01 Sunday', $timestamp->format('Y')));
                        return sprintf('%02u', 1 + ($timestamp->format('z') - $day->format('z')) / 7);
                },
                '%V' => 'W',
                '%W' => function ($timestamp) {
                        // Number of weeks between date and first Monday of year
                        $day = new \DateTime(sprintf('%d-01 Monday', $timestamp->format('Y')));
                        return sprintf('%02u', 1 + ($timestamp->format('z') - $day->format('z')) / 7);
                },

                // Month
                '%b' => $intl_formatter,
                '%B' => $intl_formatter,
                '%h' => $intl_formatter,
                '%m' => 'm',

                // Year
                '%C' => function ($timestamp) {
                        // Century (-1): 19 for 20th century
                        return floor($timestamp->format('Y') / 100);
                },
                '%g' => function ($timestamp) {
                        return substr($timestamp->format('o'), -2);
                },
                '%G' => 'o',
                '%y' => 'y',
                '%Y' => 'Y',

                // Time
                '%H' => 'H',
                '%k' => function ($timestamp) {
                        return sprintf('% 2u', $timestamp->format('G'));
                },
                '%I' => 'h',
                '%l' => function ($timestamp) {
                        return sprintf('% 2u', $timestamp->format('g'));
                },
                '%M' => 'i',
                '%p' => 'A', // AM PM (this is reversed on purpose!)
                '%P' => 'a', // am pm
                '%r' => 'h:i:s A', // %I:%M:%S %p
                '%R' => 'H:i', // %H:%M
                '%S' => 's',
                '%T' => 'H:i:s', // %H:%M:%S
                '%X' => $intl_formatter, // Preferred time representation based on locale, without the date

                // Timezone
                '%z' => 'O',
                '%Z' => 'T',

                // Time and Date Stamps
                '%c' => $intl_formatter,
                '%D' => 'm/d/Y',
                '%F' => 'Y-m-d',
                '%s' => 'U',
                '%x' => $intl_formatter,
        ];

        $out = preg_replace_callback('/(?<!%)(%[a-zA-Z])/', function ($match) use ($translation_table, $timestamp) {
                if ($match[1] == '%n') {
                        return "\n";
                }
                elseif ($match[1] == '%t') {
                        return "\t";
                }

                if (!isset($translation_table[$match[1]])) {
                        throw new \InvalidArgumentException(sprintf('Format "%s" is unknown in time format', $match[1]));
                }

                $replace = $translation_table[$match[1]];

                if (is_string($replace)) {
                        return $timestamp->format($replace);
                }
                else {
                        return $replace($timestamp, $match[1]);
                }
        }, $format);

        $out = str_replace('%%', '%', $out);
        return $out;
}

function twig_date_filter($date, $format) {
        return custom_strftime($format, $date);
}

function twig_hasPermission_filter($mod, $permission, $board = null) {
	return hasPermission($permission, $board, $mod);
}

function twig_extension_filter($value, $case_insensitive = true) {
	$ext = mb_substr($value, mb_strrpos($value, '.') + 1);
	if($case_insensitive)
		$ext = mb_strtolower($ext);
	return $ext;
}

function twig_sprintf_filter( $value, $var) {
	return sprintf($value, $var);
}

function twig_truncate_filter($value, $length = 30, $preserve = false, $separator = '…') {
	if (mb_strlen($value) > $length) {
		if ($preserve) {
			if (false !== ($breakpoint = mb_strpos($value, ' ', $length))) {
				$length = $breakpoint;
			}
		}
		return mb_substr($value, 0, $length) . $separator;
	}
	return $value;
}

function twig_filename_truncate_filter($value, $length = 30, $separator = '…') {
	if (mb_strlen($value) > $length) {
		$value = strrev($value);
		$array = array_reverse(explode(".", $value, 2));
		$array = array_map("strrev", $array);

		$filename = &$array[0];
		$extension = isset($array[1]) ? $array[1] : false;

		$filename = mb_substr($filename, 0, $length - ($extension ? mb_strlen($extension) + 1 : 0)) . $separator;

		return implode(".", $array);
	}
	return $value;
}

function twig_ratio_function($w, $h) {
	return fraction($w, $h, ':');
}
function twig_secure_link_confirm($text, $title, $confirm_message, $href) {
	global $config;

	return '<a onclick="if (event.which==2) return true;if (confirm(\'' . htmlentities(addslashes($confirm_message)) . '\')) document.location=\'?/' . htmlspecialchars(addslashes($href . '/' . make_secure_link_token($href))) . '\';return false;" title="' . htmlentities($title) . '" href="?/' . $href . '">' . $text . '</a>';
}
function twig_secure_link($href) {
	return $href . '/' . make_secure_link_token($href);
}
