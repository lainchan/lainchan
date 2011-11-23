<?php

/* page.html */
class __TwigTemplate_8bb16dea6df49df571827b55c09e4e19 extends Twig_Template
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
        echo $this->getContext($context, 'title');
        echo "</title>
\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
\t<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no\" />
\t<link rel=\"stylesheet\" type=\"text/css\" id=\"stylesheet\" href=\"";
        // line 9
        echo $this->getAttribute($this->getContext($context, 'config'), "uri_stylesheets", array(), "any", false);
        echo $this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "default_stylesheet", array(), "any", false), 1, array(), "any", false);
        echo "\" />
\t";
        // line 10
        if ((!$this->getContext($context, 'nojavascript'))) {
            echo "<script type=\"text/javascript\" src=\"";
            echo $this->getAttribute($this->getContext($context, 'config'), "url_javascript", array(), "any", false);
            echo "\"></script>";
        }
        // line 11
        echo "</head>
<body>
\t";
        // line 13
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
        // line 14
        echo "\t<h1>";
        echo $this->getContext($context, 'title');
        echo "</h1>
\t<div class=\"title\">";
        // line 15
        if ($this->getContext($context, 'subtitle')) {
            echo $this->getContext($context, 'subtitle');
        }
        echo "<p>";
        if ($this->getContext($context, 'mod')) {
            echo "<a href=\"?/\">";
            echo gettext("Return to dashboard");            echo "</a>";
        }
        echo "</p></div>
\t";
        // line 16
        echo $this->getContext($context, 'body');
        echo "
\t<hr />
\t<p class=\"unimportant\" style=\"margin-top:20px;text-align:center;\">Powered by <a href=\"http://tinyboard.org/\">Tinyboard</a> ";
        // line 18
        echo $this->getAttribute($this->getContext($context, 'config'), "version", array(), "any", false);
        echo " | <a href=\"http://tinyboard.org/\">Tinyboard</a> Copyright &copy; 2010-2011 Tinyboard Development Group</p>
</body>
</html>
";
    }

    public function getTemplateName()
    {
        return "page.html";
    }

    public function isTraitable()
    {
        return false;
    }
}
