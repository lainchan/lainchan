<?php

/* post_reply.html */
class __TwigTemplate_2a6e4309b9f7dc4a632ee8b3f6f3b2e4 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->blocks = array(
            '__internal_2a6e4309b9f7dc4a632ee8b3f6f3b2e4_1' => array($this, 'block___internal_2a6e4309b9f7dc4a632ee8b3f6f3b2e4_1'),
            '__internal_2a6e4309b9f7dc4a632ee8b3f6f3b2e4_2' => array($this, 'block___internal_2a6e4309b9f7dc4a632ee8b3f6f3b2e4_2'),
        );
    }

    protected function doGetParent(array $context)
    {
        return false;
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $context = array_merge($this->env->getGlobals(), $context);

        // line 1
        echo twig_remove_whitespace_filter((string) $this->renderBlock("__internal_2a6e4309b9f7dc4a632ee8b3f6f3b2e4_1", $context, $blocks));
        // line 95
        if ($this->getContext($context, 'index')) {
            echo truncate($this->getAttribute($this->getContext($context, 'post'), "body", array(), "any", false), $this->getAttribute($this->getContext($context, 'post'), "link", array(), "any", false));
        } else {
            echo $this->getAttribute($this->getContext($context, 'post'), "body", array(), "any", false);
        }
        echo twig_remove_whitespace_filter((string) $this->renderBlock("__internal_2a6e4309b9f7dc4a632ee8b3f6f3b2e4_2", $context, $blocks));
    }

    // line 1
    public function block___internal_2a6e4309b9f7dc4a632ee8b3f6f3b2e4_1($context, array $blocks = array())
    {
        // line 3
        echo "<div class=\"post reply\" id=\"reply_";
        echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
        echo "\">

<p class=\"intro\"";
        // line 5
        if ((!$this->getContext($context, 'index'))) {
            echo " id=\"";
            echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
            echo "\"";
        }
        echo ">
\t<input type=\"checkbox\" class=\"delete\" name=\"delete_";
        // line 6
        echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
        echo "\" id=\"delete_";
        echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
        echo "\" />
\t<label for=\"delete_";
        // line 7
        echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
        echo "\">
\t\t";
        // line 8
        if ((twig_length_filter($this->env, $this->getAttribute($this->getContext($context, 'post'), "subject", array(), "any", false)) > 0)) {
            // line 9
            echo "\t\t\t";
            // line 10
            echo "\t\t\t<span class=\"subject\">";
            echo $this->getAttribute($this->getContext($context, 'post'), "subject", array(), "any", false);
            echo "</span> 
\t\t";
        }
        // line 12
        echo "\t\t";
        if ((twig_length_filter($this->env, $this->getAttribute($this->getContext($context, 'post'), "email", array(), "any", false)) > 0)) {
            // line 13
            echo "\t\t\t";
            // line 14
            echo "\t\t\t<a class=\"email\" href=\"mailto:";
            echo $this->getAttribute($this->getContext($context, 'post'), "email", array(), "any", false);
            echo "\">
\t\t";
        }
        // line 16
        echo "\t\t<span class=\"name\">";
        echo $this->getAttribute($this->getContext($context, 'post'), "name", array(), "any", false);
        echo "</span>
\t\t";
        // line 17
        if ((twig_length_filter($this->env, $this->getAttribute($this->getContext($context, 'post'), "trip", array(), "any", false)) > 0)) {
            // line 18
            echo "\t\t\t<span class=\"trip\">";
            echo $this->getAttribute($this->getContext($context, 'post'), "trip", array(), "any", false);
            echo "</span>
\t\t";
        }
        // line 20
        echo "\t\t";
        if ((twig_length_filter($this->env, $this->getAttribute($this->getContext($context, 'post'), "email", array(), "any", false)) > 0)) {
            // line 21
            echo "\t\t\t";
            // line 22
            echo "\t\t\t</a>
\t\t";
        }
        // line 24
        echo "\t\t";
        if ((twig_length_filter($this->env, $this->getAttribute($this->getContext($context, 'post'), "capcode", array(), "any", false)) > 0)) {
            // line 25
            echo "\t\t\t";
            // line 26
            echo "\t\t\t";
            echo capcode($this->getAttribute($this->getContext($context, 'post'), "capcode", array(), "any", false));
            echo "
\t\t";
        }
        // line 28
        echo "\t\t";
        if (($this->getAttribute($this->getContext($context, 'post'), "mod", array(), "any", false) && twig_hasPermission_filter($this->getAttribute($this->getContext($context, 'post'), "mod", array(), "any", false), $this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "mod", array(), "any", false), "show_ip", array(), "any", false), $this->getAttribute($this->getContext($context, 'board'), "uri", array(), "any", false)))) {
            // line 29
            echo "\t\t\t [<a style=\"margin:0;\" href=\"?/IP/";
            echo $this->getAttribute($this->getContext($context, 'post'), "ip", array(), "any", false);
            echo "\">";
            echo $this->getAttribute($this->getContext($context, 'post'), "ip", array(), "any", false);
            echo "</a>]
\t\t";
        }
        // line 31
        echo "\t\t ";
        echo twig_date_filter($this->getAttribute($this->getContext($context, 'post'), "time", array(), "any", false), $this->getAttribute($this->getContext($context, 'config'), "post_date", array(), "any", false));
        echo "
\t</label>
\t";
        // line 33
        if ($this->getAttribute($this->getContext($context, 'config'), "poster_ids", array(), "any", false)) {
            // line 34
            echo "\t\t ID: ";
            echo poster_id($this->getAttribute($this->getContext($context, 'post'), "ip", array(), "any", false), $this->getAttribute($this->getContext($context, 'post'), "thread", array(), "any", false));
            echo "
\t";
        }
        // line 36
        echo "\t <a class=\"post_no\" ";
        if ((!$this->getContext($context, 'index'))) {
            echo "onclick=\"highlightReply(";
            echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
            echo ")\" ";
        }
        echo "href=\"";
        echo $this->getAttribute($this->getContext($context, 'post'), "link", array(), "any", false);
        echo "\">No.</a>
\t<a class=\"post_no\"
\t\t";
        // line 38
        if ((!$this->getContext($context, 'index'))) {
            // line 39
            echo "\t\t\t onclick=\"citeReply(";
            echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
            echo ");\"
\t\t";
        }
        // line 41
        echo "\t\t href=\"";
        if ($this->getContext($context, 'index')) {
            // line 42
            echo "\t\t\t";
            echo $this->getAttribute($this->getContext($context, 'post'), "link", array("q", ), "method", false);
            echo "
\t\t";
        } else {
            // line 44
            echo "\t\t\tjavascript:void(0);
\t\t";
        }
        // line 45
        echo "\">
\t\t";
        // line 46
        echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
        echo "
\t </a>
\t</p>
\t";
        // line 49
        if ($this->getAttribute($this->getContext($context, 'post'), "embed", array(), "any", false)) {
            // line 50
            echo "\t\t";
            echo $this->getAttribute($this->getContext($context, 'post'), "embed", array(), "any", false);
            echo "
\t";
        } elseif (($this->getAttribute($this->getContext($context, 'post'), "file", array(), "any", false) == "deleted")) {
            // line 52
            echo "\t\t<img src=\"";
            echo $this->getAttribute($this->getContext($context, 'config'), "image_deleted", array(), "any", false);
            echo "\" alt=\"\" />
\t";
        } elseif (($this->getAttribute($this->getContext($context, 'post'), "file", array(), "any", false) && $this->getAttribute($this->getContext($context, 'post'), "file", array(), "any", false))) {
            // line 54
            echo "\t\t<p class=\"fileinfo\">File: <a href=\"";
            echo $this->getAttribute($this->getContext($context, 'config'), "uri_img", array(), "any", false);
            echo $this->getAttribute($this->getContext($context, 'post'), "file", array(), "any", false);
            echo "\">";
            echo $this->getAttribute($this->getContext($context, 'post'), "file", array(), "any", false);
            echo "</a> <span class=\"unimportant\">
\t\t(
\t\t\t";
            // line 56
            if (($this->getAttribute($this->getContext($context, 'post'), "thumb", array(), "any", false) == "spoiler")) {
                // line 57
                echo "\t\t\t\tSpoiler Image, 
\t\t\t";
            }
            // line 59
            echo "\t\t\t";
            echo format_bytes($this->getAttribute($this->getContext($context, 'post'), "filesize", array(), "any", false));
            echo "
\t\t\t";
            // line 60
            if (($this->getAttribute($this->getContext($context, 'post'), "filex", array(), "any", false) && $this->getAttribute($this->getContext($context, 'post'), "filey", array(), "any", false))) {
                // line 61
                echo "\t\t\t\t, ";
                echo $this->getAttribute($this->getContext($context, 'post'), "filex", array(), "any", false);
                echo "x";
                echo $this->getAttribute($this->getContext($context, 'post'), "filey", array(), "any", false);
                echo "
\t\t\t\t";
                // line 62
                if ($this->getAttribute($this->getContext($context, 'config'), "show_ratio", array(), "any", false)) {
                    // line 63
                    echo "\t\t\t\t\t, ";
                    echo $this->getAttribute($this->getContext($context, 'post'), "ratio", array(), "any", false);
                    echo "
\t\t\t\t";
                }
                // line 65
                echo "\t\t\t";
            }
            // line 66
            echo "\t\t\t";
            if ($this->getAttribute($this->getContext($context, 'config'), "show_filename", array(), "any", false)) {
                // line 67
                echo "\t\t\t\t, 
\t\t\t\t";
                // line 68
                if ((twig_length_filter($this->env, $this->getAttribute($this->getContext($context, 'post'), "filename", array(), "any", false)) > $this->getAttribute($this->getContext($context, 'config'), "max_filename_display", array(), "any", false))) {
                    // line 69
                    echo "\t\t\t\t\t<span title=\"";
                    echo $this->getAttribute($this->getContext($context, 'post'), "filename", array(), "any", false);
                    echo "\">";
                    echo twig_truncate_filter($this->getAttribute($this->getContext($context, 'post'), "filename", array(), "any", false), $this->getAttribute($this->getContext($context, 'config'), "max_filename_display", array(), "any", false));
                    echo "</span>
\t\t\t\t";
                } else {
                    // line 71
                    echo "\t\t\t\t\t";
                    echo $this->getAttribute($this->getContext($context, 'post'), "filename", array(), "any", false);
                    echo "
\t\t\t\t";
                }
                // line 73
                echo "\t\t\t";
            }
            // line 74
            echo "\t\t)
\t\t</span>
</p>
\t<a href=\"";
            // line 77
            echo $this->getAttribute($this->getContext($context, 'config'), "uri_img", array(), "any", false);
            echo $this->getAttribute($this->getContext($context, 'post'), "file", array(), "any", false);
            echo "\" target=\"_blank\"";
            if (($this->getAttribute($this->getContext($context, 'this'), "thumb", array(), "any", false) == "file")) {
                echo " class=\"file\"";
            }
            echo ">
\t<img src=\"
\t\t";
            // line 79
            if (($this->getAttribute($this->getContext($context, 'post'), "thumb", array(), "any", false) == "file")) {
                // line 80
                echo "\t\t\t";
                echo $this->getAttribute($this->getContext($context, 'config'), "root", array(), "any", false);
                echo "
\t\t\t";
                // line 81
                if ($this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "file_icons", array(), "any", false), twig_extension_filter($this->getAttribute($this->getContext($context, 'post'), "filename", array(), "any", false)), array(), "array", false)) {
                    // line 82
                    echo "\t\t\t\t";
                    echo sprintf($this->getAttribute($this->getContext($context, 'config'), "file_thumb", array(), "any", false), $this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "file_icons", array(), "any", false), twig_extension_filter($this->getAttribute($this->getContext($context, 'post'), "filename", array(), "any", false)), array(), "array", false));
                    echo "
\t\t\t";
                } else {
                    // line 84
                    echo "\t\t\t\t";
                    echo sprintf($this->getAttribute($this->getContext($context, 'config'), "file_thumb", array(), "any", false), $this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "file_icons", array(), "any", false), "default", array(), "any", false));
                    echo "
\t\t\t";
                }
                // line 86
                echo "\t\t";
            } elseif (($this->getAttribute($this->getContext($context, 'post'), "thumb", array(), "any", false) == "spoiler")) {
                // line 87
                echo "\t\t\t";
                echo $this->getAttribute($this->getContext($context, 'config'), "root", array(), "any", false);
                echo $this->getAttribute($this->getContext($context, 'config'), "spoiler_image", array(), "any", false);
                echo "
\t\t";
            } else {
                // line 89
                echo "\t\t\t";
                echo $this->getAttribute($this->getContext($context, 'config'), "uri_thumb", array(), "any", false);
                echo $this->getAttribute($this->getContext($context, 'post'), "thumb", array(), "any", false);
                echo "
\t\t";
            }
            // line 90
            echo "\" style=\"width:";
            echo $this->getAttribute($this->getContext($context, 'post'), "thumbx", array(), "any", false);
            echo "px;height:";
            echo $this->getAttribute($this->getContext($context, 'post'), "thumby", array(), "any", false);
            echo "px\" alt=\"\" />
\t</a>
\t";
        }
        // line 93
        echo "\t";
        echo $this->getAttribute($this->getContext($context, 'post'), "postControls", array(), "any", false);
        echo "
\t<p class=\"body\">
\t\t";
    }

    // line 95
    public function block___internal_2a6e4309b9f7dc4a632ee8b3f6f3b2e4_2($context, array $blocks = array())
    {
        // line 96
        echo "\t</p>
</div>
<br/>
";
    }

    public function getTemplateName()
    {
        return "post_reply.html";
    }

    public function isTraitable()
    {
        return false;
    }
}
