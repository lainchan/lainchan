<?php

/* post_form.html */
class __TwigTemplate_2c7bbba93327ba78b74a61cb7bb6a56b extends Twig_Template
{
    protected function doGetParent(array $context)
    {
        return false;
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $context = array_merge($this->env->getGlobals(), $context);

        // line 1
        echo "<form name=\"post\" onsubmit=\"return dopost(this);\" enctype=\"multipart/form-data\" action=\"";
        echo $this->getAttribute($this->getContext($context, 'config'), "post_url", array(), "any", false);
        echo "\" method=\"post\">
";
        // line 2
        echo $this->getContext($context, 'hidden_inputs');
        echo "
";
        // line 3
        if ($this->getContext($context, 'id')) {
            echo "<input type=\"hidden\" name=\"thread\" value=\"";
            echo $this->getContext($context, 'id');
            echo "\" />";
        }
        // line 4
        echo "<input type=\"hidden\" name=\"board\" value=\"";
        echo $this->getAttribute($this->getContext($context, 'board'), "uri", array(), "any", false);
        echo "\" />
";
        // line 5
        if ($this->getContext($context, 'mod')) {
            echo "<input type=\"hidden\" name=\"mod\" value=\"1\" />";
        }
        // line 6
        echo "\t<table>
\t\t";
        // line 7
        if ((!$this->getAttribute($this->getContext($context, 'config'), "field_disable_name", array(), "any", false))) {
            echo "<tr>
\t\t\t<th>
\t\t\t\t";
            // line 9
            echo gettext("Name");            // line 10
            echo "\t\t\t</th>
\t\t\t<td>
\t\t\t\t<input type=\"text\" name=\"name\" size=\"25\" maxlength=\"50\" autocomplete=\"off\" />
\t\t\t</td>
\t\t</tr>";
        }
        // line 15
        echo "\t\t";
        if ((!$this->getAttribute($this->getContext($context, 'config'), "field_disable_email", array(), "any", false))) {
            echo "<tr>
\t\t\t<th>
\t\t\t\t";
            // line 17
            echo gettext("Email");            // line 18
            echo "\t\t\t</th>
\t\t\t<td>
\t\t\t\t<input type=\"text\" name=\"email\" size=\"25\" maxlength=\"40\" autocomplete=\"off\" />
\t\t\t</td>
\t\t</tr>";
        }
        // line 23
        echo "\t\t<tr>
\t\t\t<th>
\t\t\t\t";
        // line 25
        echo gettext("Subject");        // line 26
        echo "\t\t\t</th>
\t\t\t<td>
\t\t\t\t<input style=\"float:left;\" type=\"text\" name=\"subject\" size=\"25\" maxlength=\"100\" autocomplete=\"off\" />
\t\t\t\t<input accesskey=\"s\" style=\"margin-left:2px;\" type=\"submit\" name=\"post\" value=\"";
        // line 29
        if ($this->getContext($context, 'id')) {
            echo $this->getAttribute($this->getContext($context, 'config'), "button_reply", array(), "any", false);
        } else {
            echo $this->getAttribute($this->getContext($context, 'config'), "button_newtopic", array(), "any", false);
        }
        echo "\" />";
        if ($this->getAttribute($this->getContext($context, 'config'), "spoiler_images", array(), "any", false)) {
            echo " <input id=\"spoiler\" name=\"spoiler\" type=\"checkbox\" /> <label for=\"spoiler\">";
            echo gettext("Spoiler Image");            echo "</label>";
        }
        // line 30
        echo "\t\t\t</td>
\t\t</tr>
\t\t<tr>
\t\t\t<th>
\t\t\t\t";
        // line 34
        echo gettext("Comment");        // line 35
        echo "\t\t\t</th>
\t\t\t<td>
\t\t\t\t<textarea name=\"body\" id=\"body\" rows=\"5\" cols=\"35\"></textarea>
\t\t\t</td>
\t\t</tr>
\t\t";
        // line 40
        if ($this->getAttribute($this->getContext($context, 'config'), "recaptcha", array(), "any", false)) {
            // line 41
            echo "\t\t<tr>
\t\t\t<th>
\t\t\t\t";
            // line 43
            echo gettext("Verification");            // line 44
            echo "\t\t\t</th>
\t\t\t<td>
\t\t\t\t<script type=\"text/javascript\" src=\"http://www.google.com/recaptcha/api/challenge?k=";
            // line 46
            echo $this->getAttribute($this->getContext($context, 'config'), "recaptcha_public", array(), "any", false);
            echo "\"></script>
\t\t\t</td>
\t\t</tr>
\t\t";
        }
        // line 50
        echo "\t\t<tr>
\t\t\t<th>
\t\t\t\t";
        // line 52
        echo gettext("File");        // line 53
        echo "\t\t\t</th>
\t\t\t<td>
\t\t\t\t<input type=\"file\" name=\"file\" />
\t\t\t</td>
\t\t</tr>
\t\t";
        // line 58
        if ($this->getAttribute($this->getContext($context, 'config'), "enable_embedding", array(), "any", false)) {
            // line 59
            echo "\t\t<tr>
\t\t\t<th>
\t\t\t\t";
            // line 61
            echo gettext("Embed");            // line 62
            echo "\t\t\t</th>
\t\t\t<td>
\t\t\t\t<input type=\"text\" name=\"embed\" size=\"30\" maxlength=\"120\" autocomplete=\"off\" />
\t\t\t</td>
\t\t</tr>
\t\t";
        }
        // line 68
        echo "\t\t";
        if ($this->getContext($context, 'mod')) {
            // line 69
            echo "\t\t<tr>
\t\t\t<th>
\t\t\t\t";
            // line 71
            echo gettext("Flags");            // line 72
            echo "\t\t\t</th>
\t\t\t<td>
\t\t\t\t";
            // line 74
            if (((!$this->getContext($context, 'id')) && twig_hasPermission_filter($this->getAttribute($this->getContext($context, 'post'), "mod", array(), "any", false), $this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "mod", array(), "any", false), "sticky", array(), "any", false), $this->getAttribute($this->getContext($context, 'board'), "uri", array(), "any", false)))) {
                echo "<div>
\t\t\t\t\t<label for=\"sticky\">";
                // line 75
                echo gettext("Sticky");                echo "</label>
\t\t\t\t\t<input title=\"";
                // line 76
                echo gettext("Sticky");                echo "\" type=\"checkbox\" name=\"sticky\" id=\"sticky\" /><br />
\t\t\t\t</div>";
            }
            // line 78
            echo "\t\t\t\t";
            if (((!$this->getContext($context, 'id')) && twig_hasPermission_filter($this->getAttribute($this->getContext($context, 'post'), "mod", array(), "any", false), $this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "mod", array(), "any", false), "lock", array(), "any", false), $this->getAttribute($this->getContext($context, 'board'), "uri", array(), "any", false)))) {
                echo "<div>
\t\t\t\t\t<label for=\"lock\">";
                // line 79
                echo gettext("Lock");                echo "</label><br />
\t\t\t\t\t<input title=\"";
                // line 80
                echo gettext("Lock");                echo "\" type=\"checkbox\" name=\"lock\" id=\"lock\" />
\t\t\t\t</div>";
            }
            // line 82
            echo "\t\t\t\t";
            if (twig_hasPermission_filter($this->getAttribute($this->getContext($context, 'post'), "mod", array(), "any", false), $this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "mod", array(), "any", false), "rawhtml", array(), "any", false), $this->getAttribute($this->getContext($context, 'board'), "uri", array(), "any", false))) {
                echo "<div>
\t\t\t\t\t<label for=\"raw\">";
                // line 83
                echo gettext("Raw HTML");                echo "</label><br />
\t\t\t\t\t<input title=\"";
                // line 84
                echo gettext("Raw HTML");                echo "\" type=\"checkbox\" name=\"raw\" id=\"raw\" />
\t\t\t\t</div>";
            }
            // line 86
            echo "\t\t\t</td>
\t\t</tr>
\t\t";
        }
        // line 89
        echo "\t\t";
        if ((!$this->getAttribute($this->getContext($context, 'config'), "field_disable_password", array(), "any", false))) {
            echo "<tr>
\t\t\t<th>
\t\t\t\t";
            // line 91
            echo gettext("Password");            // line 92
            echo "\t\t\t</th>
\t\t\t<td>
\t\t\t\t<input type=\"password\" name=\"password\" size=\"12\" maxlength=\"18\" autocomplete=\"off\" /> 
\t\t\t\t<span class=\"unimportant\">";
            // line 95
            echo gettext("(For file deletion.)");            echo "</span>
\t\t\t</td>
\t\t</tr>";
        }
        // line 98
        echo "\t</table>
</form>

<script type=\"text/javascript\">";
        // line 101
        echo "
\trememberStuff();
";
        // line 103
        echo "</script>
";
    }

    public function getTemplateName()
    {
        return "post_form.html";
    }

    public function isTraitable()
    {
        return false;
    }
}
