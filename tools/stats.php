#!/usr/bin/php
<?php
mb_internal_encoding('utf-8');
require dirname(__FILE__) . '/inc/cli.php';
                
$variants = array(array("hour", 3600), array("day", 3600*24), array("3 days", 3600*24*3),
                  array("week", 3600*24*7), array("month", 3600*24*7*30));

printf("           || ");
foreach ($variants as $iter) {
	list($term, $time) = $iter;
	printf("%8s | ", $term);
}
print("\n");
print(str_repeat('=', 13+11*count($variants)));
print("\n");


function mb_str_pad ($input, $pad_length, $pad_string, $pad_type, $encoding="UTF-8") { 
    if (!$encoding) {
        $diff = strlen($input) - mb_strlen($input);
    }
    else {
        $diff = strlen($input) - mb_strlen($input, $encoding);
    }
    return str_pad($input, $pad_length + $diff, $pad_string, $pad_type);
} 


$q = query("SELECT uri FROM ``boards``");
while ($f = $q->fetch()) {
	$str = $f['uri'];
        $str = mb_str_pad($str,10," ", STR_PAD_LEFT, "UTF-8");
        printf("%s || ", $str);
	foreach ($variants as $iter) {
		list($term, $time) = $iter;
		$qq = query(sprintf("SELECT COUNT(*) as count FROM ``posts_%s`` WHERE time > %d", $f['uri'], time()-$time));
		$c = $qq->fetch()['count'];

		printf("%8d | ", $c);
	}
	print("\n");
}
