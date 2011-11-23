<?php

/* login.html */
class __TwigTemplate_bcb357b53ee83d351b20be234a5b0972 extends Twig_Template
{
    protected function doGetParent(array $context)
    {
        return false;
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $context = array_merge($this->env->getGlobals(), $context);

        // line 1
        if ($this->getContext($context, 'error')) {
            echo "<h2 style=\"text-align:center\">";
            echo $this->getContext($context, 'error');
            echo "</h2>";
        }
        // line 2
        echo "<form action=\"\" method=\"post\">
";
        // line 3
        if ($this->getContext($context, 'redirect')) {
            echo "<input type=\"hidden\" name=\"redirect\" value=\"";
            echo $this->getContext($context, 'redirect');
            echo "\" />";
        }
        // line 4
        echo "<table style=\"margin-top:25px;\">
\t<tr>
\t\t<th>
\t\t\t";
        // line 7
        echo gettext("Username");        // line 8
        echo "\t\t</th>
\t\t<td>
\t\t\t<input type=\"text\" name=\"username\" size=\"20\" maxlength=\"30\" value=\"";
        // line 10
        echo $this->getContext($context, 'username');
        echo "\">
\t\t</td>
\t</tr>
\t<tr>
\t\t<th>
\t\t\t";
        // line 15
        echo gettext("Password");        // line 16
        echo "\t\t</th>
\t\t<td>
\t\t\t<input type=\"password\" name=\"password\" size=\"20\" maxlength=\"30\" value=\"\">
\t\t</td>
\t</tr>
\t<tr>
\t\t<td></td>
\t\t<td>
\t\t\t<input type=\"submit\" name=\"login\" value=\"";
        // line 24
        echo gettext("Continue");        echo "\" />
\t\t</td>
</table>
</form>
";
    }

    public function getTemplateName()
    {
        return "login.html";
    }

    public function isTraitable()
    {
        return false;
    }
}
