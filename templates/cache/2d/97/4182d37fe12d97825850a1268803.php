<?php

/* index.html */
class __TwigTemplate_2d974182d37fe12d97825850a1268803 extends Twig_Template
{
    protected function doGetParent(array $context)
    {
        return false;
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $context = array_merge($this->env->getGlobals(), $context);

        // line 1
        echo "<!DOCTYPE html>
<html>
<head>
\t<link rel=\"stylesheet\" media=\"screen\" href=\"";
        // line 4
        echo $this->getAttribute($this->getContext($context, 'config'), "url_stylesheet", array(), "any", false);
        echo "\" />
\t";
        // line 5
        if ($this->getAttribute($this->getContext($context, 'config'), "url_favicon", array(), "any", false)) {
            echo "<link rel=\"shortcut icon\" href=\"";
            echo $this->getAttribute($this->getContext($context, 'config'), "url_favicon", array(), "any", false);
            echo "\" />";
        }
        // line 6
        echo "\t<title>";
        echo $this->getAttribute($this->getContext($context, 'board'), "url", array(), "any", false);
        echo " - ";
        echo $this->getAttribute($this->getContext($context, 'board'), "name", array(), "any", false);
        echo "</title>
\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
\t<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no\" />
\t";
        // line 9
        if ($this->getAttribute($this->getContext($context, 'config'), "meta_keywords", array(), "any", false)) {
            echo "<meta name=\"keywords\" content=\"";
            echo $this->getAttribute($this->getContext($context, 'config'), "meta_keywords", array(), "any", false);
            echo "\" />";
        }
        // line 10
        echo "\t<link rel=\"stylesheet\" type=\"text/css\" id=\"stylesheet\" href=\"";
        echo $this->getAttribute($this->getContext($context, 'config'), "uri_stylesheets", array(), "any", false);
        echo $this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "default_stylesheet", array(), "any", false), 1, array(), "any", false);
        echo "\" />
\t<script type=\"text/javascript\" src=\"";
        // line 11
        echo $this->getAttribute($this->getContext($context, 'config'), "url_javascript", array(), "any", false);
        echo "\"></script>
\t";
        // line 12
        if ($this->getAttribute($this->getContext($context, 'config'), "recaptcha", array(), "any", false)) {
            echo "<style type=\"text/css\">";
            echo "
\t\t.recaptcha_image_cell {
\t\t\tbackground: none !important;
\t\t}
\t\ttable.recaptchatable {
\t\t\tborder: none !important;
\t\t}
\t\t#recaptcha_logo, #recaptcha_tagline {
\t\t\tdisplay: none;
\t\t\tfloat: right;
\t\t}
\t\t.recaptchatable a {
\t\t\tdisplay: block;
\t\t}
\t";
            // line 26
            echo "</style>";
        }
        // line 27
        echo "</head>
<body>\t
\t";
        // line 29
        echo $this->getAttribute($this->getContext($context, 'boardlist'), "top", array(), "any", false);
        echo "
\t";
        // line 30
        if ($this->getContext($context, 'pm')) {
            echo "<div class=\"top_notice\">You have <a href=\"?/PM/";
            echo $this->getAttribute($this->getContext($context, 'pm'), "id", array(), "any", false);
            echo "\">an unread PM</a>";
            if (($this->getAttribute($this->getContext($context, 'pm'), "waiting", array(), "any", false) > 0)) {
                echo ", plus ";
                echo $this->getAttribute($this->getContext($context, 'pm'), "waiting", array(), "any", false);
                echo " more waiting";
            }
            echo ".</div><hr />";
        }
        // line 31
        echo "\t";
        if ($this->getAttribute($this->getContext($context, 'config'), "url_banner", array(), "any", false)) {
            echo "<img class=\"banner\" src=\"";
            echo $this->getAttribute($this->getContext($context, 'config'), "url_banner", array(), "any", false);
            echo "\" ";
            if (($this->getAttribute($this->getContext($context, 'config'), "banner_width", array(), "any", false) || $this->getAttribute($this->getContext($context, 'config'), "banner_height", array(), "any", false))) {
                echo "style=\"";
                if ($this->getAttribute($this->getContext($context, 'config'), "banner_width", array(), "any", false)) {
                    echo "width:";
                    echo $this->getAttribute($this->getContext($context, 'config'), "banner_width", array(), "any", false);
                    echo "px";
                }
                echo ";";
                if ($this->getAttribute($this->getContext($context, 'config'), "banner_width", array(), "any", false)) {
                    echo "height:";
                    echo $this->getAttribute($this->getContext($context, 'config'), "banner_height", array(), "any", false);
                    echo "px";
                }
                echo "\" ";
            }
            echo "alt=\"\" />";
        }
        // line 32
        echo "\t<h1>";
        echo $this->getAttribute($this->getContext($context, 'board'), "url", array(), "any", false);
        echo " - ";
        echo $this->getAttribute($this->getContext($context, 'board'), "name", array(), "any", false);
        echo "</h1>
\t<div class=\"title\">";
        // line 33
        if ($this->getAttribute($this->getContext($context, 'board'), "title", array(), "any", false)) {
            echo $this->getAttribute($this->getContext($context, 'board'), "title", array(), "any", false);
        }
        echo "<p>";
        if ($this->getContext($context, 'mod')) {
            echo "<a href=\"?/\">Return to dashboard</a>";
        }
        echo "</p></div>
\t
\t
\t<form name=\"post\" onsubmit=\"return dopost(this);\" enctype=\"multipart/form-data\" action=\"";
        // line 36
        echo $this->getAttribute($this->getContext($context, 'config'), "post_url", array(), "any", false);
        echo "\" method=\"post\">
\t";
        // line 37
        echo $this->getContext($context, 'hidden_inputs');
        echo "
\t<input type=\"hidden\" name=\"board\" value=\"";
        // line 38
        echo $this->getAttribute($this->getContext($context, 'board'), "uri", array(), "any", false);
        echo "\" />
\t";
        // line 39
        if ($this->getContext($context, 'mod')) {
            echo "<input type=\"hidden\" name=\"mod\" value=\"1\" />";
        }
        // line 40
        echo "\t\t<table>
\t\t\t<tr>
\t\t\t\t<th>
\t\t\t\t\t";
        // line 43
        echo gettext("Name");        // line 44
        echo "\t\t\t\t</th>
\t\t\t\t<td>
\t\t\t\t\t<input type=\"text\" name=\"name\" size=\"25\" maxlength=\"50\" autocomplete=\"off\" />
\t\t\t\t</td>
\t\t\t</tr>
\t\t\t<tr>
\t\t\t\t<th>
\t\t\t\t\t";
        // line 51
        echo gettext("Email");        // line 52
        echo "\t\t\t\t</th>
\t\t\t\t<td>
\t\t\t\t\t<input type=\"text\" name=\"email\" size=\"25\" maxlength=\"40\" autocomplete=\"off\" />
\t\t\t\t</td>
\t\t\t</tr>
\t\t\t<tr>
\t\t\t\t<th>
\t\t\t\t\t";
        // line 59
        echo gettext("Subject");        // line 60
        echo "\t\t\t\t</th>
\t\t\t\t<td>
\t\t\t\t\t<input style=\"float:left;\" type=\"text\" name=\"subject\" size=\"25\" maxlength=\"100\" autocomplete=\"off\" />
\t\t\t\t\t<input accesskey=\"s\" style=\"margin-left:2px;\" type=\"submit\" name=\"post\" value=\"";
        // line 63
        echo $this->getAttribute($this->getContext($context, 'config'), "button_newtopic", array(), "any", false);
        echo "\" />";
        if ($this->getAttribute($this->getContext($context, 'config'), "spoiler_images", array(), "any", false)) {
            echo " <input id=\"spoiler\" name=\"spoiler\" type=\"checkbox\" /> <label for=\"spoiler\">";
            echo gettext("Spoiler Image");            echo "</label>";
        }
        // line 64
        echo "\t\t\t\t</td>
\t\t\t</tr>
\t\t\t<tr>
\t\t\t\t<th>
\t\t\t\t\t";
        // line 68
        echo gettext("Comment");        // line 69
        echo "\t\t\t\t</th>
\t\t\t\t<td>
\t\t\t\t\t<textarea name=\"body\" id=\"body\" rows=\"5\" cols=\"30\"></textarea>
\t\t\t\t</td>
\t\t\t</tr>
\t\t\t";
        // line 74
        if ($this->getAttribute($this->getContext($context, 'config'), "recaptcha", array(), "any", false)) {
            // line 75
            echo "\t\t\t<tr>
\t\t\t\t<th>
\t\t\t\t\t";
            // line 77
            echo gettext("Verification");            // line 78
            echo "\t\t\t\t</th>
\t\t\t\t<td>
\t\t\t\t\t<script type=\"text/javascript\" src=\"http://www.google.com/recaptcha/api/challenge?k=";
            // line 80
            echo $this->getAttribute($this->getContext($context, 'config'), "recaptcha_public", array(), "any", false);
            echo "\"></script>
\t\t\t\t</td>
\t\t\t</tr>
\t\t\t";
        }
        // line 84
        echo "\t\t\t<tr>
\t\t\t\t<th>
\t\t\t\t\t";
        // line 86
        echo gettext("File");        // line 87
        echo "\t\t\t\t</th>
\t\t\t\t<td>
\t\t\t\t\t<input type=\"file\" name=\"file\" />
\t\t\t\t</td>
\t\t\t</tr>
\t\t\t";
        // line 92
        if ($this->getAttribute($this->getContext($context, 'config'), "enable_embedding", array(), "any", false)) {
            // line 93
            echo "\t\t\t<tr>
\t\t\t\t<th>
\t\t\t\t\t";
            // line 95
            echo gettext("Embed");            // line 96
            echo "\t\t\t\t</th>
\t\t\t\t<td>
\t\t\t\t\t<input type=\"text\" name=\"embed\" size=\"30\" maxlength=\"120\" autocomplete=\"off\" />
\t\t\t\t</td>
\t\t\t</tr>
\t\t\t";
        }
        // line 102
        echo "\t\t\t";
        if ($this->getContext($context, 'mod')) {
            // line 103
            echo "\t\t\t<tr>
\t\t\t\t<th>
\t\t\t\t\t";
            // line 105
            echo gettext("Flags");            // line 106
            echo "\t\t\t\t</th>
\t\t\t\t<td>
\t\t\t\t\t<div>
\t\t\t\t\t\t<label for=\"sticky\">";
            // line 109
            echo gettext("Sticky");            echo "</label>
\t\t\t\t\t\t<input title=\"";
            // line 110
            echo gettext("Sticky");            echo "\" type=\"checkbox\" name=\"sticky\" id=\"sticky\" /><br />
\t\t\t\t\t</div>
\t\t\t\t\t<div>
\t\t\t\t\t\t<label for=\"lock\">";
            // line 113
            echo gettext("Lock");            echo "</label><br />
\t\t\t\t\t\t<input title=\"";
            // line 114
            echo gettext("Lock");            echo "\" type=\"checkbox\" name=\"lock\" id=\"lock\" />
\t\t\t\t\t</div>
\t\t\t\t\t<div>
\t\t\t\t\t\t<label for=\"raw\">";
            // line 117
            echo gettext("Raw HTML");            echo "</label><br />
\t\t\t\t\t\t<input title=\"";
            // line 118
            echo gettext("Raw HTML");            echo "\" type=\"checkbox\" name=\"raw\" id=\"raw\" />
\t\t\t\t\t</div>
\t\t\t\t</td>
\t\t\t</tr>
\t\t\t";
        }
        // line 123
        echo "\t\t\t<tr>
\t\t\t\t<th>
\t\t\t\t\t";
        // line 125
        echo gettext("Password");        // line 126
        echo "\t\t\t\t</th>
\t\t\t\t<td>
\t\t\t\t\t<input type=\"password\" name=\"password\" size=\"12\" maxlength=\"18\" autocomplete=\"off\" /> 
\t\t\t\t\t<span class=\"unimportant\">";
        // line 129
        echo gettext("(For file deletion.)");        echo "</span>
\t\t\t\t</td>
\t\t\t</tr>
\t\t</table>
\t</form>
\t<script type=\"text/javascript\">";
        // line 134
        echo "
\t\trememberStuff();
\t";
        // line 136
        echo "</script>
\t
\t";
        // line 138
        if ($this->getAttribute($this->getContext($context, 'config'), "blotter", array(), "any", false)) {
            echo "<hr /><div class=\"blotter\">";
            echo $this->getAttribute($this->getContext($context, 'config'), "blotter", array(), "any", false);
            echo "</div>";
        }
        // line 139
        echo "\t<hr />
\t<form name=\"postcontrols\" action=\"";
        // line 140
        echo $this->getAttribute($this->getContext($context, 'config'), "post_url", array(), "any", false);
        echo "\" method=\"post\">
\t<input type=\"hidden\" name=\"board\" value=\"";
        // line 141
        echo $this->getAttribute($this->getContext($context, 'board'), "uri", array(), "any", false);
        echo "\" />
\t";
        // line 142
        if ($this->getContext($context, 'mod')) {
            echo "<input type=\"hidden\" name=\"mod\" value=\"1\" />";
        }
        // line 143
        echo "\t";
        echo $this->getContext($context, 'body');
        echo "
\t<div class=\"delete\">
\t\t";
        // line 145
        echo gettext("Delete Post");        echo " [<input title=\"Delete file only\" type=\"checkbox\" name=\"file\" id=\"delete_file\" />
\t\t <label for=\"delete_file\">";
        // line 146
        echo gettext("File");        echo "</label>] <label for=\"password\">";
        echo gettext("Password");        echo "</label>
\t\t\t<input id=\"password\" type=\"password\" name=\"password\" size=\"12\" maxlength=\"18\" />
\t\t\t<input type=\"submit\" name=\"delete\" value=\"";
        // line 148
        echo gettext("Delete");        echo "\" />
\t</div>
\t<div class=\"delete\" style=\"clear:both\">
\t\t<label for=\"reason\">";
        // line 151
        echo gettext("Reason");        echo "</label>
\t\t\t<input id=\"reason\" type=\"text\" name=\"reason\" size=\"20\" maxlength=\"30\" />
\t\t\t<input type=\"submit\" name=\"report\" value=\"";
        // line 153
        echo gettext("Report");        echo "\" />
\t</div>
\t</form>
\t<div class=\"pages\">";
        // line 156
        echo $this->getAttribute($this->getContext($context, 'btn'), "prev", array(), "any", false);
        echo " ";
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($this->getContext($context, 'pages'));
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
        foreach ($context['_seq'] as $context['_key'] => $context['page']) {
            // line 157
            echo "\t\t[<a ";
            if ($this->getAttribute($this->getContext($context, 'page'), "selected", array(), "any", false)) {
                echo "class=\"selected\"";
            }
            if ((!$this->getAttribute($this->getContext($context, 'page'), "selected", array(), "any", false))) {
                echo "href=\"";
                echo $this->getAttribute($this->getContext($context, 'page'), "link", array(), "any", false);
                echo "\"";
            }
            echo ">";
            echo $this->getAttribute($this->getContext($context, 'page'), "num", array(), "any", false);
            echo "</a>]";
            if ($this->getAttribute($this->getContext($context, 'loop'), "last", array(), "any", false)) {
                echo " ";
            }
            // line 158
            echo "\t";
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
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['page'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        echo " ";
        echo $this->getAttribute($this->getContext($context, 'btn'), "next", array(), "any", false);
        echo "</div>
\t";
        // line 159
        echo $this->getAttribute($this->getContext($context, 'boardlist'), "bottom", array(), "any", false);
        echo "
\t<p class=\"unimportant\" style=\"margin-top:20px;text-align:center;\">Powered by <a href=\"http://tinyboard.org/\">Tinyboard</a> ";
        // line 160
        echo $this->getAttribute($this->getContext($context, 'config'), "version", array(), "any", false);
        echo " | <a href=\"http://tinyboard.org/\">Tinyboard</a> Copyright &copy; 2010-2011 Tinyboard Development Group</p>
\t<p class=\"unimportant\" style=\"text-align:center;\">All trademarks, copyrights, comments, and images on this page are owned by or are the responsibility of their respective parties.</p>
\t\t
</body>
</html>
";
    }

    public function getTemplateName()
    {
        return "index.html";
    }

    public function isTraitable()
    {
        return false;
    }
}
