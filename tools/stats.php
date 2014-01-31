#!/usr/bin/php
<?php
require dirname(__FILE__) . '/inc/cli.php';
                
if(!is_writable($config['file_script'])) {
        get_httpd_privileges();
}

$variants = [["hour", 3600], ["day", 3600*24], ["3 days", 3600*24*3],
             ["week", 3600*24*7], ["month", 3600*24*7*30]];

printf("           || ");
foreach ($variants as list($term, $time)) {
	printf("%8s | ", $term);
}
print("\n");
print(str_repeat('=', 13+11*count($variants)));
print("\n");


$q = query("SELECT uri FROM ``boards``");
while ($f = $q->fetch()) {
	printf("%10s || ", $f['uri']);
	foreach ($variants as list($term, $time)) {
		$qq = query(sprintf("SELECT COUNT(*) as count FROM ``posts_%s`` WHERE time > %d", $f['uri'], time()-$time));
		$c = $qq->fetch()['count'];

		printf("%8d | ", $c);
	}
	print("\n");
}
