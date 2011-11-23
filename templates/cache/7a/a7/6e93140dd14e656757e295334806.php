<?php

/* post_thread.html */
class __TwigTemplate_7aa76e93140dd14e656757e295334806 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->blocks = array(
            '__internal_7aa76e93140dd14e656757e295334806_1' => array($this, 'block___internal_7aa76e93140dd14e656757e295334806_1'),
            '__internal_7aa76e93140dd14e656757e295334806_2' => array($this, 'block___internal_7aa76e93140dd14e656757e295334806_2'),
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
        echo twig_remove_whitespace_filter((string) $this->renderBlock("__internal_7aa76e93140dd14e656757e295334806_1", $context, $blocks));
        // line 108
        if ($this->getContext($context, 'index')) {
            echo truncate($this->getAttribute($this->getContext($context, 'post'), "body", array(), "any", false), $this->getAttribute($this->getContext($context, 'post'), "link", array(), "any", false));
        } else {
            echo $this->getAttribute($this->getContext($context, 'post'), "body", array(), "any", false);
        }
        echo twig_remove_whitespace_filter((string) $this->renderBlock("__internal_7aa76e93140dd14e656757e295334806_2", $context, $blocks));
        // line 136
        $context['hr'] = $this->getAttribute($this->getContext($context, 'post'), "hr", array(), "any", false);
        // line 137
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getContext($context, 'post'), "posts", array(), "any", false));
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
        foreach ($context['_seq'] as $context['_key'] => $context['post']) {
            // line 138
            echo "\t";
            $this->env->loadTemplate("post_reply.html")->display($context);
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
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['post'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 140
        echo "<br class=\"clear\"/>";
        if ($this->getContext($context, 'hr')) {
            echo "<hr/>";
        }
        // line 141
        if ($this->getContext($context, 'index')) {
            // line 142
            echo "\t</div>
";
        }
    }

    // line 1
    public function block___internal_7aa76e93140dd14e656757e295334806_1($context, array $blocks = array())
    {
        // line 3
        echo "
";
        // line 4
        if ($this->getContext($context, 'index')) {
            // line 5
            echo "\t<div id=\"thread_";
            echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
            echo "\" itemscope itemid=\"/";
            echo $this->getAttribute($this->getContext($context, 'board'), "uri", array(), "any", false);
            echo "/";
            echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
            echo "\" itemtype=\"http://schema.org/CreativeWork\">
";
        }
        // line 7
        echo "
";
        // line 8
        if ($this->getAttribute($this->getContext($context, 'post'), "embed", array(), "any", false)) {
            // line 9
            echo "\t";
            echo $this->getAttribute($this->getContext($context, 'post'), "embed", array(), "any", false);
            echo "
";
        } elseif (($this->getAttribute($this->getContext($context, 'post'), "file", array(), "any", false) == "deleted")) {
            // line 11
            echo "\t<img itemprop=\"image\" src=\"";
            echo $this->getAttribute($this->getContext($context, 'config'), "image_deleted", array(), "any", false);
            echo "\" alt=\"\" />
";
        } elseif (($this->getAttribute($this->getContext($context, 'post'), "file", array(), "any", false) && $this->getAttribute($this->getContext($context, 'post'), "file", array(), "any", false))) {
            // line 13
            echo "\t<p class=\"fileinfo\">File: <a href=\"";
            echo $this->getAttribute($this->getContext($context, 'config'), "uri_img", array(), "any", false);
            echo $this->getAttribute($this->getContext($context, 'post'), "file", array(), "any", false);
            echo "\">";
            echo $this->getAttribute($this->getContext($context, 'post'), "file", array(), "any", false);
            echo "</a> <span class=\"unimportant\">
\t(
\t\t";
            // line 15
            if (($this->getAttribute($this->getContext($context, 'post'), "thumb", array(), "any", false) == "spoiler")) {
                // line 16
                echo "\t\t\tSpoiler Image, 
\t\t";
            }
            // line 18
            echo "\t\t";
            echo format_bytes($this->getAttribute($this->getContext($context, 'post'), "filesize", array(), "any", false));
            echo "
\t\t";
            // line 19
            if (($this->getAttribute($this->getContext($context, 'post'), "filex", array(), "any", false) && $this->getAttribute($this->getContext($context, 'post'), "filey", array(), "any", false))) {
                // line 20
                echo "\t\t\t, ";
                echo $this->getAttribute($this->getContext($context, 'post'), "filex", array(), "any", false);
                echo "x";
                echo $this->getAttribute($this->getContext($context, 'post'), "filey", array(), "any", false);
                echo "
\t\t\t";
                // line 21
                if ($this->getAttribute($this->getContext($context, 'config'), "show_ratio", array(), "any", false)) {
                    // line 22
                    echo "\t\t\t\t, ";
                    echo $this->getAttribute($this->getContext($context, 'post'), "ratio", array(), "any", false);
                    echo "
\t\t\t";
                }
                // line 24
                echo "\t\t";
            }
            // line 25
            echo "\t\t";
            if ($this->getAttribute($this->getContext($context, 'config'), "show_filename", array(), "any", false)) {
                // line 26
                echo "\t\t\t, 
\t\t\t";
                // line 27
                if ((twig_length_filter($this->env, $this->getAttribute($this->getContext($context, 'post'), "filename", array(), "any", false)) > $this->getAttribute($this->getContext($context, 'config'), "max_filename_display", array(), "any", false))) {
                    // line 28
                    echo "\t\t\t\t<span title=\"";
                    echo $this->getAttribute($this->getContext($context, 'post'), "filename", array(), "any", false);
                    echo "\">";
                    echo twig_truncate_filter($this->getAttribute($this->getContext($context, 'post'), "filename", array(), "any", false), $this->getAttribute($this->getContext($context, 'config'), "max_filename_display", array(), "any", false));
                    echo "</span>
\t\t\t";
                } else {
                    // line 30
                    echo "\t\t\t\t";
                    echo $this->getAttribute($this->getContext($context, 'post'), "filename", array(), "any", false);
                    echo "
\t\t\t";
                }
                // line 32
                echo "\t\t";
            }
            // line 33
            echo "\t)
\t</span></p>
<a href=\"";
            // line 35
            echo $this->getAttribute($this->getContext($context, 'config'), "uri_img", array(), "any", false);
            echo $this->getAttribute($this->getContext($context, 'post'), "file", array(), "any", false);
            echo "\" target=\"_blank\"";
            if (($this->getAttribute($this->getContext($context, 'post'), "thumb", array(), "any", false) == "file")) {
                echo " class=\"file\"";
            }
            echo ">
<img src=\"
\t";
            // line 37
            if (($this->getAttribute($this->getContext($context, 'post'), "thumb", array(), "any", false) == "file")) {
                // line 38
                echo "\t\t";
                echo $this->getAttribute($this->getContext($context, 'config'), "root", array(), "any", false);
                echo "
\t\t";
                // line 39
                if ($this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "file_icons", array(), "any", false), twig_extension_filter($this->getAttribute($this->getContext($context, 'post'), "filename", array(), "any", false)), array(), "array", false)) {
                    // line 40
                    echo "\t\t\t";
                    echo sprintf($this->getAttribute($this->getContext($context, 'config'), "file_thumb", array(), "any", false), $this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "file_icons", array(), "any", false), twig_extension_filter($this->getAttribute($this->getContext($context, 'post'), "filename", array(), "any", false)), array(), "array", false));
                    echo "
\t\t";
                } else {
                    // line 42
                    echo "\t\t\t";
                    echo sprintf($this->getAttribute($this->getContext($context, 'config'), "file_thumb", array(), "any", false), $this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "file_icons", array(), "any", false), "default", array(), "any", false));
                    echo "
\t\t";
                }
                // line 44
                echo "\t";
            } elseif (($this->getAttribute($this->getContext($context, 'post'), "thumb", array(), "any", false) == "spoiler")) {
                // line 45
                echo "\t\t";
                echo $this->getAttribute($this->getContext($context, 'config'), "root", array(), "any", false);
                echo $this->getAttribute($this->getContext($context, 'config'), "spoiler_image", array(), "any", false);
                echo "
\t";
            } else {
                // line 47
                echo "\t\t";
                echo $this->getAttribute($this->getContext($context, 'config'), "uri_thumb", array(), "any", false);
                echo $this->getAttribute($this->getContext($context, 'post'), "thumb", array(), "any", false);
                echo "
\t";
            }
            // line 48
            echo "\" style=\"width:";
            echo $this->getAttribute($this->getContext($context, 'post'), "thumbx", array(), "any", false);
            echo "px;height:";
            echo $this->getAttribute($this->getContext($context, 'post'), "thumby", array(), "any", false);
            echo "px\" alt=\"\" /></a>
";
        }
        // line 50
        echo "<div class=\"post op\"";
        if ((!$this->getContext($context, 'index'))) {
            echo " itemscope itemid=\"/";
            echo $this->getAttribute($this->getContext($context, 'board'), "uri", array(), "any", false);
            echo "/";
            echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
            echo "\" itemtype=\"http://schema.org/Article\"";
        }
        echo "><p class=\"intro\"";
        if ((!$this->getContext($context, 'index'))) {
            echo " id=\"";
            echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
            echo "\"";
        }
        echo ">
\t<input type=\"checkbox\" class=\"delete\" name=\"delete_";
        // line 51
        echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
        echo "\" id=\"delete_";
        echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
        echo "\" />
\t<label for=\"delete_";
        // line 52
        echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
        echo "\">
\t\t";
        // line 53
        if ((twig_length_filter($this->env, $this->getAttribute($this->getContext($context, 'post'), "subject", array(), "any", false)) > 0)) {
            // line 54
            echo "\t\t\t";
            // line 55
            echo "\t\t\t<span itemprop=\"headline\" class=\"subject\">";
            echo $this->getAttribute($this->getContext($context, 'post'), "subject", array(), "any", false);
            echo "</span> 
\t\t";
        }
        // line 57
        echo "\t\t";
        if ((twig_length_filter($this->env, $this->getAttribute($this->getContext($context, 'post'), "email", array(), "any", false)) > 0)) {
            // line 58
            echo "\t\t\t";
            // line 59
            echo "\t\t\t<a class=\"email\" href=\"mailto:";
            echo $this->getAttribute($this->getContext($context, 'post'), "email", array(), "any", false);
            echo "\">
\t\t";
        }
        // line 61
        echo "\t\t<span class=\"name\">";
        echo $this->getAttribute($this->getContext($context, 'post'), "name", array(), "any", false);
        echo "</span>
\t\t";
        // line 62
        if ((twig_length_filter($this->env, $this->getAttribute($this->getContext($context, 'post'), "trip", array(), "any", false)) > 0)) {
            // line 63
            echo "\t\t\t<span class=\"trip\">";
            echo $this->getAttribute($this->getContext($context, 'post'), "trip", array(), "any", false);
            echo "</span>
\t\t";
        }
        // line 65
        echo "\t\t";
        if ((twig_length_filter($this->env, $this->getAttribute($this->getContext($context, 'post'), "email", array(), "any", false)) > 0)) {
            // line 66
            echo "\t\t\t";
            // line 67
            echo "\t\t\t</a>
\t\t";
        }
        // line 69
        echo "\t\t";
        if ((twig_length_filter($this->env, $this->getAttribute($this->getContext($context, 'post'), "capcode", array(), "any", false)) > 0)) {
            // line 70
            echo "\t\t\t";
            // line 71
            echo "\t\t\t";
            echo capcode($this->getAttribute($this->getContext($context, 'post'), "capcode", array(), "any", false));
            echo "
\t\t";
        }
        // line 73
        echo "\t\t";
        if (($this->getAttribute($this->getContext($context, 'post'), "mod", array(), "any", false) && twig_hasPermission_filter($this->getAttribute($this->getContext($context, 'post'), "mod", array(), "any", false), $this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "mod", array(), "any", false), "show_ip", array(), "any", false), $this->getAttribute($this->getContext($context, 'board'), "uri", array(), "any", false)))) {
            // line 74
            echo "\t\t\t [<a style=\"margin:0;\" href=\"?/IP/";
            echo $this->getAttribute($this->getContext($context, 'post'), "ip", array(), "any", false);
            echo "\">";
            echo $this->getAttribute($this->getContext($context, 'post'), "ip", array(), "any", false);
            echo "</a>]
\t\t";
        }
        // line 76
        echo "\t\t <time itemprop=\"dateCreated\" datetime=\"";
        echo twig_date_filter($this->getAttribute($this->getContext($context, 'post'), "time", array(), "any", false), "%Y-%m-%dT%H:%M:%S%z");
        echo "\">";
        echo twig_date_filter($this->getAttribute($this->getContext($context, 'post'), "time", array(), "any", false), $this->getAttribute($this->getContext($context, 'config'), "post_date", array(), "any", false));
        echo "</time>
\t</label>
\t";
        // line 78
        if ($this->getAttribute($this->getContext($context, 'config'), "poster_ids", array(), "any", false)) {
            // line 79
            echo "\t\t ID: ";
            echo poster_id($this->getAttribute($this->getContext($context, 'post'), "ip", array(), "any", false), $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false));
            echo "
\t";
        }
        // line 81
        echo "\t <a itemprop=\"url\" class=\"post_no\" href=\"";
        echo $this->getAttribute($this->getContext($context, 'post'), "link", array(), "any", false);
        echo "\">No.</a>
\t<a class=\"post_no\"
\t\t";
        // line 83
        if ((!$this->getContext($context, 'index'))) {
            // line 84
            echo "\t\t\t onclick=\"citeReply(";
            echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
            echo ");\"
\t\t";
        }
        // line 86
        echo "\t\t href=\"";
        if ($this->getContext($context, 'index')) {
            // line 87
            echo "\t\t\t";
            echo $this->getAttribute($this->getContext($context, 'post'), "link", array("q", ), "method", false);
            echo "
\t\t";
        } else {
            // line 89
            echo "\t\t\tjavascript:void(0);
\t\t";
        }
        // line 90
        echo "\">
\t\t";
        // line 91
        echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
        echo "
\t</a>
\t";
        // line 93
        if ($this->getAttribute($this->getContext($context, 'post'), "sticky", array(), "any", false)) {
            // line 94
            echo "\t\t<img class=\"icon\" title=\"Sticky\" src=\"";
            echo $this->getAttribute($this->getContext($context, 'config'), "image_sticky", array(), "any", false);
            echo "\" alt=\"Sticky\" />
\t";
        }
        // line 96
        echo "\t";
        if ($this->getAttribute($this->getContext($context, 'post'), "locked", array(), "any", false)) {
            // line 97
            echo "\t\t<img class=\"icon\" title=\"Locked\" src=\"";
            echo $this->getAttribute($this->getContext($context, 'config'), "image_locked", array(), "any", false);
            echo "\" alt=\"Locked\" />
\t";
        }
        // line 99
        echo "\t";
        if (($this->getAttribute($this->getContext($context, 'post'), "bumplocked", array(), "any", false) && (($this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "mod", array(), "any", false), "view_bumplock", array(), "any", false) < 0) || ($this->getAttribute($this->getContext($context, 'post'), "mod", array(), "any", false) && twig_hasPermission_filter($this->getAttribute($this->getContext($context, 'post'), "mod", array(), "any", false), $this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "mod", array(), "any", false), "view_bumplock", array(), "any", false), $this->getAttribute($this->getContext($context, 'board'), "uri", array(), "any", false)))))) {
            // line 100
            echo "\t\t<img class=\"icon\" title=\"Bumplocked\" src=\"";
            echo $this->getAttribute($this->getContext($context, 'config'), "image_bumplocked", array(), "any", false);
            echo "\" alt=\"Locked\" />
\t";
        }
        // line 102
        echo "\t";
        if ($this->getContext($context, 'index')) {
            // line 103
            echo "\t\t<a itemprop=\"discussionUrl\" href=\"";
            echo $this->getAttribute($this->getContext($context, 'post'), "root", array(), "any", false);
            echo $this->getAttribute($this->getContext($context, 'board'), "dir", array(), "any", false);
            echo $this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "dir", array(), "any", false), "res", array(), "any", false);
            echo sprintf($this->getAttribute($this->getContext($context, 'config'), "file_page", array(), "any", false), $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false));
            echo "\">[";
            echo gettext("Reply");            echo "]</a>
\t";
        }
        // line 105
        echo "\t";
        echo $this->getAttribute($this->getContext($context, 'post'), "postControls", array(), "any", false);
        echo "
\t</p>
\t<p itemprop=\"description\" class=\"body\">
\t\t";
    }

    // line 108
    public function block___internal_7aa76e93140dd14e656757e295334806_2($context, array $blocks = array())
    {
        // line 109
        echo "\t</p>
\t";
        // line 110
        if (($this->getAttribute($this->getContext($context, 'post'), "omitted", array(), "any", false) || $this->getAttribute($this->getContext($context, 'post'), "omitted_images", array(), "any", false))) {
            // line 111
            echo "\t\t<span class=\"omitted\">
\t\t\t";
            // line 112
            if ($this->getAttribute($this->getContext($context, 'post'), "omitted", array(), "any", false)) {
                // line 113
                echo "\t\t\t\t";
                echo $this->getAttribute($this->getContext($context, 'post'), "omitted", array(), "any", false);
                echo " post
\t\t\t\t";
                // line 114
                if (($this->getAttribute($this->getContext($context, 'post'), "omitted", array(), "any", false) != 1)) {
                    // line 115
                    echo "\t\t\t\t\ts
\t\t\t\t";
                }
                // line 117
                echo "\t\t\t\t";
                if ($this->getAttribute($this->getContext($context, 'post'), "omitted_images", array(), "any", false)) {
                    // line 118
                    echo "\t\t\t\t\t and 
\t\t\t\t";
                }
                // line 120
                echo "\t\t\t";
            }
            // line 121
            echo "\t\t\t";
            if ($this->getAttribute($this->getContext($context, 'post'), "omitted_images", array(), "any", false)) {
                // line 122
                echo "\t\t\t\t";
                echo $this->getAttribute($this->getContext($context, 'post'), "omitted_images", array(), "any", false);
                echo " image repl
\t\t\t\t";
                // line 123
                if (($this->getAttribute($this->getContext($context, 'post'), "omitted_images", array(), "any", false) == 1)) {
                    // line 124
                    echo "\t\t\t\t\ty
\t\t\t\t";
                } else {
                    // line 126
                    echo "\t\t\t\t\ties
\t\t\t\t";
                }
                // line 128
                echo "\t\t\t";
            }
            echo " omitted. Click reply to view.
\t\t</span>
\t";
        }
        // line 131
        echo "<meta itemprop=\"genre\" content=\"/";
        echo $this->getAttribute($this->getContext($context, 'board'), "uri", array(), "any", false);
        echo "/\" />
";
        // line 132
        if ((!$this->getContext($context, 'index'))) {
            // line 133
            echo "<meta itemprop=\"interactionCount\" content=\"UserComments:";
            echo count($this->getAttribute($this->getContext($context, 'post'), "posts", array(), "any", false));
            echo "\" />
";
        }
        // line 135
        echo "</div>";
    }

    public function getTemplateName()
    {
        return "post_thread.html";
    }

    public function isTraitable()
    {
        return false;
    }
}
