<?php

class PoeditString implements \Stringable {
	public $comments;

	function __construct(public $key, public $value = '', public $fuzzy = false, $comments = []) {
		$this->comments = (array)$comments;
	}

	public function __toString(): string {
		$str ='';
		foreach ($this->comments as $c) {
			$str .= "#: $c\n";
		}
		if ($this->fuzzy) $str .= "#, fuzzy\n";
		$str .= 'msgid "'.str_replace('"', '\\"', (string) $this->key).'"' . "\n";
		$str .= 'msgstr "'.str_replace('"', '\\"', (string) $this->value).'"' . "\n";
		$str .= "\n";
		return $str;
	}
}

?>