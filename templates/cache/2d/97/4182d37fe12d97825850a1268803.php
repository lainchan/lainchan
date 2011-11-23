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
\t";
        // line 11
        if ((!$this->getContext($context, 'nojavascript'))) {
            echo "<script type=\"text/javascript\" src=\"";
            echo $this->getAttribute($this->getContext($context, 'config'), "url_javascript", array(), "any", false);
            echo "\"></script>";
        }
        // line 12
        echo "\t";
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
            echo "<a href=\"?/\">";
            echo gettext("Return to dashboard");            echo "</a>";
        }
        echo "</p></div>
\t
\t";
        // line 35
        $this->env->loadTemplate("post_form.html")->display($context);
        // line 36
        echo "\t
\t";
        // line 37
        if ($this->getAttribute($this->getContext($context, 'config'), "blotter", array(), "any", false)) {
            echo "<hr /><div class=\"blotter\">";
            echo $this->getAttribute($this->getContext($context, 'config'), "blotter", array(), "any", false);
            echo "</div>";
        }
        // line 38
        echo "\t<hr />
\t<form name=\"postcontrols\" action=\"";
        // line 39
        echo $this->getAttribute($this->getContext($context, 'config'), "post_url", array(), "any", false);
        echo "\" method=\"post\">
\t<input type=\"hidden\" name=\"board\" value=\"";
        // line 40
        echo $this->getAttribute($this->getContext($context, 'board'), "uri", array(), "any", false);
        echo "\" />
\t";
        // line 41
        if ($this->getContext($context, 'mod')) {
            echo "<input type=\"hidden\" name=\"mod\" value=\"1\" />";
        }
        // line 42
        echo "\t";
        echo $this->getContext($context, 'body');
        echo "
\t<div class=\"delete\">
\t\t";
        // line 44
        echo gettext("Delete Post");        echo " [<input title=\"Delete file only\" type=\"checkbox\" name=\"file\" id=\"delete_file\" />
\t\t <label for=\"delete_file\">";
        // line 45
        echo gettext("File");        echo "</label>] <label for=\"password\">";
        echo gettext("Password");        echo "</label>
\t\t\t<input id=\"password\" type=\"password\" name=\"password\" size=\"12\" maxlength=\"18\" />
\t\t\t<input type=\"submit\" name=\"delete\" value=\"";
        // line 47
        echo gettext("Delete");        echo "\" />
\t</div>
\t<div class=\"delete\" style=\"clear:both\">
\t\t<label for=\"reason\">";
        // line 50
        echo gettext("Reason");        echo "</label>
\t\t\t<input id=\"reason\" type=\"text\" name=\"reason\" size=\"20\" maxlength=\"30\" />
\t\t\t<input type=\"submit\" name=\"report\" value=\"";
        // line 52
        echo gettext("Report");        echo "\" />
\t</div>
\t</form>
\t<div class=\"pages\">";
        // line 55
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
            // line 56
            echo "\t\t [<a ";
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
            // line 57
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
        // line 58
        echo $this->getAttribute($this->getContext($context, 'boardlist'), "bottom", array(), "any", false);
        echo "
\t<p class=\"unimportant\" style=\"margin-top:20px;text-align:center;\">Powered by <a href=\"http://tinyboard.org/\">Tinyboard</a> ";
        // line 59
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
