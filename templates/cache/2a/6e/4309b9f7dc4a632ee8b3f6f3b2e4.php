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
        // line 96
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
        echo "\" itemprop=\"comment\" itemscope itemid=\"/";
        echo $this->getAttribute($this->getContext($context, 'board'), "uri", array(), "any", false);
        echo "/";
        echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
        echo "\" itemtype=\"http://schema.org/UserComments\"
\t";
        // line 4
        if ((!$this->getContext($context, 'index'))) {
            echo " itemref=\"/";
            echo $this->getAttribute($this->getContext($context, 'board'), "uri", array(), "any", false);
            echo "/";
            echo $this->getAttribute($this->getContext($context, 'post'), "thread", array(), "any", false);
            echo "\"";
        }
        echo ">

<p class=\"intro\"";
        // line 6
        if ((!$this->getContext($context, 'index'))) {
            echo " id=\"";
            echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
            echo "\"";
        }
        echo ">
\t<input type=\"checkbox\" class=\"delete\" name=\"delete_";
        // line 7
        echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
        echo "\" id=\"delete_";
        echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
        echo "\" />
\t<label for=\"delete_";
        // line 8
        echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
        echo "\">
\t\t";
        // line 9
        if ((twig_length_filter($this->env, $this->getAttribute($this->getContext($context, 'post'), "subject", array(), "any", false)) > 0)) {
            // line 10
            echo "\t\t\t";
            // line 11
            echo "\t\t\t<span itemprop=\"name\" class=\"subject\">";
            echo $this->getAttribute($this->getContext($context, 'post'), "subject", array(), "any", false);
            echo "</span> 
\t\t";
        }
        // line 13
        echo "\t\t";
        if ((twig_length_filter($this->env, $this->getAttribute($this->getContext($context, 'post'), "email", array(), "any", false)) > 0)) {
            // line 14
            echo "\t\t\t";
            // line 15
            echo "\t\t\t<a class=\"email\" href=\"mailto:";
            echo $this->getAttribute($this->getContext($context, 'post'), "email", array(), "any", false);
            echo "\">
\t\t";
        }
        // line 17
        echo "\t\t<span class=\"name\">";
        echo $this->getAttribute($this->getContext($context, 'post'), "name", array(), "any", false);
        echo "</span>
\t\t";
        // line 18
        if ((twig_length_filter($this->env, $this->getAttribute($this->getContext($context, 'post'), "trip", array(), "any", false)) > 0)) {
            // line 19
            echo "\t\t\t<span class=\"trip\">";
            echo $this->getAttribute($this->getContext($context, 'post'), "trip", array(), "any", false);
            echo "</span>
\t\t";
        }
        // line 21
        echo "\t\t";
        if ((twig_length_filter($this->env, $this->getAttribute($this->getContext($context, 'post'), "email", array(), "any", false)) > 0)) {
            // line 22
            echo "\t\t\t";
            // line 23
            echo "\t\t\t</a>
\t\t";
        }
        // line 25
        echo "\t\t";
        if ((twig_length_filter($this->env, $this->getAttribute($this->getContext($context, 'post'), "capcode", array(), "any", false)) > 0)) {
            // line 26
            echo "\t\t\t";
            // line 27
            echo "\t\t\t";
            echo capcode($this->getAttribute($this->getContext($context, 'post'), "capcode", array(), "any", false));
            echo "
\t\t";
        }
        // line 29
        echo "\t\t";
        if (($this->getAttribute($this->getContext($context, 'post'), "mod", array(), "any", false) && twig_hasPermission_filter($this->getAttribute($this->getContext($context, 'post'), "mod", array(), "any", false), $this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "mod", array(), "any", false), "show_ip", array(), "any", false), $this->getAttribute($this->getContext($context, 'board'), "uri", array(), "any", false)))) {
            // line 30
            echo "\t\t\t [<a style=\"margin:0;\" href=\"?/IP/";
            echo $this->getAttribute($this->getContext($context, 'post'), "ip", array(), "any", false);
            echo "\">";
            echo $this->getAttribute($this->getContext($context, 'post'), "ip", array(), "any", false);
            echo "</a>]
\t\t";
        }
        // line 32
        echo "\t\t <time itemprop=\"commentTime\" datetime=\"";
        echo twig_date_filter($this->getAttribute($this->getContext($context, 'post'), "time", array(), "any", false), "%Y-%m-%dT%H:%M:%S%z");
        echo "\">";
        echo twig_date_filter($this->getAttribute($this->getContext($context, 'post'), "time", array(), "any", false), $this->getAttribute($this->getContext($context, 'config'), "post_date", array(), "any", false));
        echo "</time>
\t</label>
\t";
        // line 34
        if ($this->getAttribute($this->getContext($context, 'config'), "poster_ids", array(), "any", false)) {
            // line 35
            echo "\t\t ID: ";
            echo poster_id($this->getAttribute($this->getContext($context, 'post'), "ip", array(), "any", false), $this->getAttribute($this->getContext($context, 'post'), "thread", array(), "any", false));
            echo "
\t";
        }
        // line 37
        echo "\t <a itemprop=\"url\" class=\"post_no\" ";
        if ((!$this->getContext($context, 'index'))) {
            echo "onclick=\"highlightReply(";
            echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
            echo ")\" ";
        }
        echo "href=\"";
        echo $this->getAttribute($this->getContext($context, 'post'), "link", array(), "any", false);
        echo "\">No.</a>
\t<a itemprop=\"replyToUrl\" class=\"post_no\"
\t\t";
        // line 39
        if ((!$this->getContext($context, 'index'))) {
            // line 40
            echo "\t\t\t onclick=\"citeReply(";
            echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
            echo ");\"
\t\t";
        }
        // line 42
        echo "\t\t href=\"";
        if ($this->getContext($context, 'index')) {
            // line 43
            echo "\t\t\t";
            echo $this->getAttribute($this->getContext($context, 'post'), "link", array("q", ), "method", false);
            echo "
\t\t";
        } else {
            // line 45
            echo "\t\t\tjavascript:void(0);
\t\t";
        }
        // line 46
        echo "\">
\t\t";
        // line 47
        echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
        echo "
\t</a>
\t</p>
\t";
        // line 50
        if ($this->getAttribute($this->getContext($context, 'post'), "embed", array(), "any", false)) {
            // line 51
            echo "\t\t";
            echo $this->getAttribute($this->getContext($context, 'post'), "embed", array(), "any", false);
            echo "
\t";
        } elseif (($this->getAttribute($this->getContext($context, 'post'), "file", array(), "any", false) == "deleted")) {
            // line 53
            echo "\t\t<img itemprop=\"image\" src=\"";
            echo $this->getAttribute($this->getContext($context, 'config'), "image_deleted", array(), "any", false);
            echo "\" alt=\"\" />
\t";
        } elseif (($this->getAttribute($this->getContext($context, 'post'), "file", array(), "any", false) && $this->getAttribute($this->getContext($context, 'post'), "file", array(), "any", false))) {
            // line 55
            echo "\t\t<p class=\"fileinfo\">File: <a href=\"";
            echo $this->getAttribute($this->getContext($context, 'config'), "uri_img", array(), "any", false);
            echo $this->getAttribute($this->getContext($context, 'post'), "file", array(), "any", false);
            echo "\">";
            echo $this->getAttribute($this->getContext($context, 'post'), "file", array(), "any", false);
            echo "</a> <span class=\"unimportant\">
\t\t(
\t\t\t";
            // line 57
            if (($this->getAttribute($this->getContext($context, 'post'), "thumb", array(), "any", false) == "spoiler")) {
                // line 58
                echo "\t\t\t\tSpoiler Image, 
\t\t\t";
            }
            // line 60
            echo "\t\t\t";
            echo format_bytes($this->getAttribute($this->getContext($context, 'post'), "filesize", array(), "any", false));
            echo "
\t\t\t";
            // line 61
            if (($this->getAttribute($this->getContext($context, 'post'), "filex", array(), "any", false) && $this->getAttribute($this->getContext($context, 'post'), "filey", array(), "any", false))) {
                // line 62
                echo "\t\t\t\t, ";
                echo $this->getAttribute($this->getContext($context, 'post'), "filex", array(), "any", false);
                echo "x";
                echo $this->getAttribute($this->getContext($context, 'post'), "filey", array(), "any", false);
                echo "
\t\t\t\t";
                // line 63
                if ($this->getAttribute($this->getContext($context, 'config'), "show_ratio", array(), "any", false)) {
                    // line 64
                    echo "\t\t\t\t\t, ";
                    echo $this->getAttribute($this->getContext($context, 'post'), "ratio", array(), "any", false);
                    echo "
\t\t\t\t";
                }
                // line 66
                echo "\t\t\t";
            }
            // line 67
            echo "\t\t\t";
            if ($this->getAttribute($this->getContext($context, 'config'), "show_filename", array(), "any", false)) {
                // line 68
                echo "\t\t\t\t, 
\t\t\t\t";
                // line 69
                if ((twig_length_filter($this->env, $this->getAttribute($this->getContext($context, 'post'), "filename", array(), "any", false)) > $this->getAttribute($this->getContext($context, 'config'), "max_filename_display", array(), "any", false))) {
                    // line 70
                    echo "\t\t\t\t\t<span title=\"";
                    echo $this->getAttribute($this->getContext($context, 'post'), "filename", array(), "any", false);
                    echo "\">";
                    echo twig_truncate_filter($this->getAttribute($this->getContext($context, 'post'), "filename", array(), "any", false), $this->getAttribute($this->getContext($context, 'config'), "max_filename_display", array(), "any", false));
                    echo "</span>
\t\t\t\t";
                } else {
                    // line 72
                    echo "\t\t\t\t\t";
                    echo $this->getAttribute($this->getContext($context, 'post'), "filename", array(), "any", false);
                    echo "
\t\t\t\t";
                }
                // line 74
                echo "\t\t\t";
            }
            // line 75
            echo "\t\t)
\t\t</span>
</p>
\t<a href=\"";
            // line 78
            echo $this->getAttribute($this->getContext($context, 'config'), "uri_img", array(), "any", false);
            echo $this->getAttribute($this->getContext($context, 'post'), "file", array(), "any", false);
            echo "\" target=\"_blank\"";
            if (($this->getAttribute($this->getContext($context, 'post'), "thumb", array(), "any", false) == "file")) {
                echo " class=\"file\"";
            }
            echo ">
\t<img src=\"
\t\t";
            // line 80
            if (($this->getAttribute($this->getContext($context, 'post'), "thumb", array(), "any", false) == "file")) {
                // line 81
                echo "\t\t\t";
                echo $this->getAttribute($this->getContext($context, 'config'), "root", array(), "any", false);
                echo "
\t\t\t";
                // line 82
                if ($this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "file_icons", array(), "any", false), twig_extension_filter($this->getAttribute($this->getContext($context, 'post'), "filename", array(), "any", false)), array(), "array", false)) {
                    // line 83
                    echo "\t\t\t\t";
                    echo sprintf($this->getAttribute($this->getContext($context, 'config'), "file_thumb", array(), "any", false), $this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "file_icons", array(), "any", false), twig_extension_filter($this->getAttribute($this->getContext($context, 'post'), "filename", array(), "any", false)), array(), "array", false));
                    echo "
\t\t\t";
                } else {
                    // line 85
                    echo "\t\t\t\t";
                    echo sprintf($this->getAttribute($this->getContext($context, 'config'), "file_thumb", array(), "any", false), $this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "file_icons", array(), "any", false), "default", array(), "any", false));
                    echo "
\t\t\t";
                }
                // line 87
                echo "\t\t";
            } elseif (($this->getAttribute($this->getContext($context, 'post'), "thumb", array(), "any", false) == "spoiler")) {
                // line 88
                echo "\t\t\t";
                echo $this->getAttribute($this->getContext($context, 'config'), "root", array(), "any", false);
                echo $this->getAttribute($this->getContext($context, 'config'), "spoiler_image", array(), "any", false);
                echo "
\t\t";
            } else {
                // line 90
                echo "\t\t\t";
                echo $this->getAttribute($this->getContext($context, 'config'), "uri_thumb", array(), "any", false);
                echo $this->getAttribute($this->getContext($context, 'post'), "thumb", array(), "any", false);
                echo "
\t\t";
            }
            // line 91
            echo "\" style=\"width:";
            echo $this->getAttribute($this->getContext($context, 'post'), "thumbx", array(), "any", false);
            echo "px;height:";
            echo $this->getAttribute($this->getContext($context, 'post'), "thumby", array(), "any", false);
            echo "px\" alt=\"\" />
\t</a>
\t";
        }
        // line 94
        echo "\t";
        echo $this->getAttribute($this->getContext($context, 'post'), "postControls", array(), "any", false);
        echo "
\t<p itemprop=\"commentText\" class=\"body\">
\t\t";
    }

    // line 96
    public function block___internal_2a6e4309b9f7dc4a632ee8b3f6f3b2e4_2($context, array $blocks = array())
    {
        // line 97
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
