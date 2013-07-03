function _(s) {
	return typeof l10n[s] != 'undefined' ? l10n[s] : s;
}
function test(param) {
	var a = _("Hello world, testing jsgettext");
	func(_('Test string'));
	var reg1 = /"[a-z]+"/i;
	var reg2 = /[a-z]+\+\/"aa"/i;
	var s1 = _('string 1: single quotes');
	var s2 = _("string 2: double quotes");
	var s3 = _("/* comment in string */");
	var s4 = _("regexp in string: /[a-z]+/i");
	var s5 = jsgettext( "another function" );
	var s6 = avoidme("should not see me!");
	var s7 = _("string 2: \"escaped double quotes\"");
	var s8 = _('string 2: \'escaped single quotes\'');

	// "string in comment"
	//;

	/**
	 * multiple
	 * lines
	 * comment
	 * _("Hello world from comment")
	 */
}