<?php

require_once 'PoeditString.php';

class PoeditParser {

	protected $header = '';
	protected $strings = [];

	protected function _fixQuotes($str) {
		return stripslashes((string) $str);
	}

	public function __construct(protected $file)
 {
 }

	public function parse() {
		$contents = file_get_contents($this->file);

		$parts = preg_split('#(\r\n|\n){2}#', $contents, -1, PREG_SPLIT_NO_EMPTY);

		$this->header = array_shift($parts);

		foreach ($parts as $part) {

			// parse comments
			$comments = [];
			preg_match_all('#^\\#: (.*?)$#m', (string) $part, $matches, PREG_SET_ORDER);
			foreach ($matches as $m) $comments[] = $m[1];

			$isFuzzy = preg_match('#^\\#, fuzzy$#im', (string) $part) ? true : false;

			preg_match_all('# ^ (msgid|msgstr)\ " ( (?: (?>[^"\\\\]++) | \\\\\\\\ | (?<!\\\\)\\\\(?!\\\\) | \\\\" )* ) (?<!\\\\)" $ #ixm', (string) $part, $matches2, PREG_SET_ORDER);

			$k = $this->_fixQuotes($matches2[0][2]);
			$v = !empty($matches2[1][2]) ? $this->_fixQuotes($matches2[1][2]) : '';

			$this->strings[$k] = new PoeditString($k, $v, $isFuzzy, $comments);
		}
	}

	public function merge($strings) {
		foreach ((array)$strings as $str) {
			if (!in_array($str, array_keys($this->strings))) {
				$this->strings[$str] = new PoeditString($str);
			}
		}
	}

	public function getHeader() {
		return $this->header;
	}

	public function getStrings() {
		return $this->strings;
	}

	public function getJSON() {
		$str = [];
		foreach ($this->strings as $s) {
			if ($s->value) $str[$s->key] = $s->value;
		}
		return json_encode($str, JSON_THROW_ON_ERROR);
	}

	public function toJSON($outputFilename, $varName = 'l10n') {
		$str = "$varName = " . $this->getJSON() . ";";
		return file_put_contents($outputFilename, $str) !== false;
	}

	public function save($filename = null) {
		$data = $this->header . "\n\n";
		foreach ($this->strings as $str) {
			$data .= $str;
		}
		return file_put_contents($filename ?: $this->file, $data) !== false;
	}
}


?>
