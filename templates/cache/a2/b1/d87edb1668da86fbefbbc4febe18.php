<?php

/* main.js */
class __TwigTemplate_a2b1d87edb1668da86fbefbbc4febe18 extends Twig_Template
{
    protected function doGetParent(array $context)
    {
        return false;
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $context = array_merge($this->env->getGlobals(), $context);

        // line 1
        echo "function get_cookie(cookie_name)
{
\tvar results = document.cookie.match ( '(^|;) ?' + cookie_name + '=([^;]*)(;|\$)');
\tif(results)
\t\treturn (unescape(results[2]));
\telse
\t\treturn null;
}

function highlightReply(id)
{
\tif(window.event !== undefined && event.which == 2) {
\t\t// don't highlight on middle click
\t\treturn true;
\t}
\t
\tvar divs = document.getElementsByTagName('div');
\tfor (var i = 0; i < divs.length; i++)
\t{
\t\tif (divs[i].className.indexOf('post') != -1)
\t\t\tdivs[i].className = divs[i].className.replace(/highlighted/, '');
\t}
\tif (id) {
\t\tpost = document.getElementById('reply_'+id);
\t\tif(post)
\t\t\tpost.className += ' highlighted';
\t}
}
function focusId(id)
{
\tdocument.getElementById(id).focus();
\tinit();
}

function generatePassword() {
\tpass = '';
\tchars = '";
        // line 37
        echo $this->getAttribute($this->getContext($context, 'config'), "genpassword_chars", array(), "any", false);
        echo "';
\tfor(i=0;i<8;i++) {
\t\trnd = Math.floor(Math.random() * chars.length);
\t\tpass += chars.substring(rnd,rnd + 1);
\t}
\treturn pass;
}

function dopost(form) {
\tlocalStorage.name = form.name.value.replace(/ ##.+\$/, '');
\tif(form.email.value != 'sage')
\t\tlocalStorage.email = form.email.value;
\t
\tsaved[document.location] = form.body.value;
\tsessionStorage.body = JSON.stringify(saved);
\t
\treturn form.body.value != \"\" || form.file.value != \"\";
}
function citeReply(id) {
\tbody = document.getElementById('body');
\t
\tif (document.selection) {
\t\t// IE
\t\tbody.focus();
\t\tsel = document.selection.createRange();
\t\tsel.text = '>>' + id + '\\n';
\t} else if (body.selectionStart || body.selectionStart == '0') {
\t\t// Mozilla
\t\tstart = body.selectionStart;
\t\tend = body.selectionEnd;
\t\tbody.value = body.value.substring(0, start) + '>>' + id + '\\n' + body.value.substring(end, body.value.length);
\t} else {
\t\t// ???
\t\tbody.value += '>>' + id + '\\n';
\t}
}

var selectedstyle = '";
        // line 74
        echo $this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "default_stylesheet", array(), "any", false), 0, array(), "any", false);
        echo "';
var styles = [
\t";
        // line 76
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($this->getContext($context, 'stylesheets'));
        $context['loop'] = array(
          'parent' => $context['_parent'],
          'index0' => 0,
          'index'  => 1,
          'first'  => true,
        );
        if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof Countable)) {
            $length = count($context['_seq']);
            $context['loop']['revindex0'] = $length - 1;
            $context['loop']['revindex'] = $length;
            $context['loop']['length'] = $length;
            $context['loop']['last'] = 1 === $length;
        }
        foreach ($context['_seq'] as $context['_key'] => $context['stylesheet']) {
            echo "['";
            echo $this->getAttribute($this->getContext($context, 'stylesheet'), "name", array(), "any", false);
            echo "', '";
            echo $this->getAttribute($this->getContext($context, 'stylesheet'), "uri", array(), "any", false);
            echo "']";
            if ((!$this->getAttribute($this->getContext($context, 'loop'), "last", array(), "any", false))) {
                echo ",
\t";
            }
            ++$context['loop']['index0'];
            ++$context['loop']['index'];
            $context['loop']['first'] = false;
            if (isset($context['loop']['length'])) {
                --$context['loop']['revindex0'];
                --$context['loop']['revindex'];
                $context['loop']['last'] = 0 === $context['loop']['revindex0'];
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['stylesheet'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 77
        echo "
];
var saved = {};

function changeStyle(x) {
\tlocalStorage.stylesheet = styles[x][1];
\tdocument.getElementById('stylesheet').href = styles[x][1];
\tselectedstyle = styles[x][0];
}

if(localStorage.stylesheet) {
\tfor(x=0;x<styles.length;x++) {
\t\tif(styles[x][1] == localStorage.stylesheet) {
\t\t\tchangeStyle(x);
\t\t\tbreak;
\t\t}
\t}
}

function rememberStuff() {
\tif(document.forms.post) {
\t\tif(!localStorage.password)
\t\t\tlocalStorage.password = generatePassword();
\t\tdocument.forms.post.password.value = localStorage.password;
\t\t
\t\tif(localStorage.name)
\t\t\tdocument.forms.post.name.value = localStorage.name;
\t\tif(localStorage.email)
\t\t\tdocument.forms.post.email.value = localStorage.email;
\t\t
\t\tif (window.location.hash.indexOf('q') == 1)
\t\t\tciteReply(window.location.hash.substring(2));
\t\t
\t\tif(sessionStorage.body) {
\t\t\tsaved = JSON.parse(sessionStorage.body);
\t\t\tif(get_cookie('";
        // line 112
        echo $this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "cookies", array(), "any", false), "js", array(), "any", false);
        echo "')) {
\t\t\t\t// Remove successful posts
\t\t\t\tsuccessful = JSON.parse(get_cookie('";
        // line 114
        echo $this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "cookies", array(), "any", false), "js", array(), "any", false);
        echo "'));
\t\t\t\tfor (var url in successful) {
\t\t\t\t\tsaved[url] = null;
\t\t\t\t}
\t\t\t\tsessionStorage.body = JSON.stringify(saved);
\t\t\t\t
\t\t\t\tdocument.cookie = '";
        // line 120
        echo $this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "cookies", array(), "any", false), "js", array(), "any", false);
        echo "={};expires=0;path=/;';
\t\t\t}
\t\t\tif(saved[document.location]) {
\t\t\t\tdocument.forms.post.body.value = saved[document.location];
\t\t\t}
\t\t}
\t\t
\t\tif(localStorage.body) {
\t\t\tdocument.forms.post.body.value = localStorage.body;
\t\t\tlocalStorage.body = '';
\t\t}
\t}
}

function init_expanding() {
\tlink = document.getElementsByTagName('a');
\tfor ( i in link ) {
\t\tif(typeof link[i] == \"object\" && link[i].childNodes[0].src && link[i].className != 'file') {
\t\t\tlink[i].onclick = function(e) {
\t\t\t\tif(e.which == 2) {
\t\t\t\t\treturn true;
\t\t\t\t}
\t\t\t\tif(!this.tag) {
\t\t\t\t\tthis.tag = this.childNodes[0].src;
\t\t\t\t\tthis.childNodes[0].src = this.href;
\t\t\t\t\tthis.childNodes[0].style.width = 'auto';
\t\t\t\t\tthis.childNodes[0].style.height = 'auto';
\t\t\t\t\tthis.childNodes[0].style.opacity = '0.4';
\t\t\t\t\tthis.childNodes[0].style.filter = 'alpha(opacity=40)';
\t\t\t\t\tthis.childNodes[0].onload = function() {
\t\t\t\t\t\tthis.style.opacity = '1';
\t\t\t\t\t\tthis.style.filter = '';
\t\t\t\t\t}
\t\t\t\t} else {
\t\t\t\t\tthis.childNodes[0].src = this.tag;
\t\t\t\t\tthis.childNodes[0].style.width = 'auto';
\t\t\t\t\tthis.childNodes[0].style.height = 'auto';
\t\t\t\t\tthis.tag = '';
\t\t\t\t}
\t\t\t\treturn false;
\t\t\t}
\t\t\t
\t\t}
\t}
}

function init()
{
\tnewElement = document.createElement('div');
\tnewElement.className = 'styles';
\t
\tfor(x=0;x<styles.length;x++) {
\t\tstyle = document.createElement('a');
\t\tstyle.innerHTML = '[' + styles[x][0] + ']';
\t\tstyle.href = 'javascript:changeStyle(' + x + ');';
\t\tif(selectedstyle == styles[x][0])
\t\t\tstyle.className = 'selected';
\t\tnewElement.appendChild(style);
\t}\t
\t
\tdocument.getElementsByTagName('body')[0].insertBefore(newElement, document.getElementsByTagName('body')[0].lastChild.nextSibling)
\t
\tif(document.forms.postcontrols) {
\t\tdocument.forms.postcontrols.password.value = localStorage.password;
\t}
\t
\tif(window.location.hash.indexOf('q') != 1 && window.location.hash.substring(1))
\t\thighlightReply(window.location.hash.substring(1));
\t
\t";
        // line 189
        if ($this->getAttribute($this->getContext($context, 'config'), "inline_expanding", array(), "any", false)) {
            echo "init_expanding();";
        }
        echo "
}

var RecaptchaOptions = {
\ttheme : 'clean'
};

window.onload = init;
";
        // line 197
        if ($this->getAttribute($this->getContext($context, 'config'), "google_analytics", array(), "any", false)) {
            echo "

var _gaq = _gaq || [];_gaq.push(['_setAccount', '";
            // line 199
            echo $this->getAttribute($this->getContext($context, 'config'), "google_analytics", array(), "any", false);
            echo "']);";
            if ($this->getAttribute($this->getContext($context, 'config'), "google_analytics_domain", array(), "any", false)) {
                echo "_gaq.push(['_setDomainName', '";
                echo $this->getAttribute($this->getContext($context, 'config'), "google_analytics_domain", array(), "any", false);
                echo "'])";
            }
            if ((!$this->getAttribute($this->getContext($context, 'config'), "google_analytics_domain", array(), "any", false))) {
                echo "_gaq.push(['_setDomainName', 'none'])";
            }
            echo ";_gaq.push(['_trackPageview']);(function() {var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);})();";
        }
    }

    public function getTemplateName()
    {
        return "main.js";
    }

    public function isTraitable()
    {
        return false;
    }
}
