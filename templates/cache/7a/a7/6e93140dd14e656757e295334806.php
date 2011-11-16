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
        // line 104
        if ($this->getContext($context, 'index')) {
            echo truncate($this->getAttribute($this->getContext($context, 'post'), "body", array(), "any", false), $this->getAttribute($this->getContext($context, 'post'), "link", array(), "any", false));
        } else {
            echo $this->getAttribute($this->getContext($context, 'post'), "body", array(), "any", false);
        }
        echo twig_remove_whitespace_filter((string) $this->renderBlock("__internal_7aa76e93140dd14e656757e295334806_2", $context, $blocks));
        // line 128
        $context['hr'] = $this->getAttribute($this->getContext($context, 'post'), "hr", array(), "any", false);
        // line 129
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
            // line 130
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
        // line 132
        echo "<br class=\"clear\"/>";
        if ($this->getContext($context, 'hr')) {
            echo "<hr/>";
        }
        // line 133
        echo "
";
    }

    // line 1
    public function block___internal_7aa76e93140dd14e656757e295334806_1($context, array $blocks = array())
    {
        // line 3
        echo "
";
        // line 4
        if ($this->getAttribute($this->getContext($context, 'post'), "embed", array(), "any", false)) {
            // line 5
            echo "\t";
            echo $this->getAttribute($this->getContext($context, 'post'), "embed", array(), "any", false);
            echo "
";
        } elseif (($this->getAttribute($this->getContext($context, 'post'), "file", array(), "any", false) == "deleted")) {
            // line 7
            echo "\t<img src=\"";
            echo $this->getAttribute($this->getContext($context, 'config'), "image_deleted", array(), "any", false);
            echo "\" alt=\"\" />
";
        } elseif (($this->getAttribute($this->getContext($context, 'post'), "file", array(), "any", false) && $this->getAttribute($this->getContext($context, 'post'), "file", array(), "any", false))) {
            // line 9
            echo "\t<p class=\"fileinfo\">File: <a href=\"";
            echo $this->getAttribute($this->getContext($context, 'config'), "uri_img", array(), "any", false);
            echo $this->getAttribute($this->getContext($context, 'post'), "file", array(), "any", false);
            echo "\">";
            echo $this->getAttribute($this->getContext($context, 'post'), "file", array(), "any", false);
            echo "</a> <span class=\"unimportant\">
\t(
\t\t";
            // line 11
            if (($this->getAttribute($this->getContext($context, 'post'), "thumb", array(), "any", false) == "spoiler")) {
                // line 12
                echo "\t\t\tSpoiler Image, 
\t\t";
            }
            // line 14
            echo "\t\t";
            echo format_bytes($this->getAttribute($this->getContext($context, 'post'), "filesize", array(), "any", false));
            echo "
\t\t";
            // line 15
            if (($this->getAttribute($this->getContext($context, 'post'), "filex", array(), "any", false) && $this->getAttribute($this->getContext($context, 'post'), "filey", array(), "any", false))) {
                // line 16
                echo "\t\t\t, ";
                echo $this->getAttribute($this->getContext($context, 'post'), "filex", array(), "any", false);
                echo "x";
                echo $this->getAttribute($this->getContext($context, 'post'), "filey", array(), "any", false);
                echo "
\t\t\t";
                // line 17
                if ($this->getAttribute($this->getContext($context, 'config'), "show_ratio", array(), "any", false)) {
                    // line 18
                    echo "\t\t\t\t, ";
                    echo $this->getAttribute($this->getContext($context, 'post'), "ratio", array(), "any", false);
                    echo "
\t\t\t";
                }
                // line 20
                echo "\t\t";
            }
            // line 21
            echo "\t\t";
            if ($this->getAttribute($this->getContext($context, 'config'), "show_filename", array(), "any", false)) {
                // line 22
                echo "\t\t\t, 
\t\t\t";
                // line 23
                if ((twig_length_filter($this->env, $this->getAttribute($this->getContext($context, 'post'), "filename", array(), "any", false)) > $this->getAttribute($this->getContext($context, 'config'), "max_filename_display", array(), "any", false))) {
                    // line 24
                    echo "\t\t\t\t<span title=\"";
                    echo $this->getAttribute($this->getContext($context, 'post'), "filename", array(), "any", false);
                    echo "\">";
                    echo twig_truncate_filter($this->getAttribute($this->getContext($context, 'post'), "filename", array(), "any", false), $this->getAttribute($this->getContext($context, 'config'), "max_filename_display", array(), "any", false));
                    echo "</span>
\t\t\t";
                } else {
                    // line 26
                    echo "\t\t\t\t";
                    echo $this->getAttribute($this->getContext($context, 'post'), "filename", array(), "any", false);
                    echo "
\t\t\t";
                }
                // line 28
                echo "\t\t";
            }
            // line 29
            echo "\t)
\t</span></p>
<a href=\"";
            // line 31
            echo $this->getAttribute($this->getContext($context, 'config'), "uri_img", array(), "any", false);
            echo $this->getAttribute($this->getContext($context, 'post'), "file", array(), "any", false);
            echo "\" target=\"_blank\"";
            if (($this->getAttribute($this->getContext($context, 'this'), "thumb", array(), "any", false) == "file")) {
                echo " class=\"file\"";
            }
            echo ">
<img src=\"
\t";
            // line 33
            if (($this->getAttribute($this->getContext($context, 'post'), "thumb", array(), "any", false) == "file")) {
                // line 34
                echo "\t\t";
                echo $this->getAttribute($this->getContext($context, 'config'), "root", array(), "any", false);
                echo "
\t\t";
                // line 35
                if ($this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "file_icons", array(), "any", false), twig_extension_filter($this->getAttribute($this->getContext($context, 'post'), "filename", array(), "any", false)), array(), "array", false)) {
                    // line 36
                    echo "\t\t\t";
                    echo sprintf($this->getAttribute($this->getContext($context, 'config'), "file_thumb", array(), "any", false), $this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "file_icons", array(), "any", false), twig_extension_filter($this->getAttribute($this->getContext($context, 'post'), "filename", array(), "any", false)), array(), "array", false));
                    echo "
\t\t";
                } else {
                    // line 38
                    echo "\t\t\t";
                    echo sprintf($this->getAttribute($this->getContext($context, 'config'), "file_thumb", array(), "any", false), $this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "file_icons", array(), "any", false), "default", array(), "any", false));
                    echo "
\t\t";
                }
                // line 40
                echo "\t";
            } elseif (($this->getAttribute($this->getContext($context, 'post'), "thumb", array(), "any", false) == "spoiler")) {
                // line 41
                echo "\t\t";
                echo $this->getAttribute($this->getContext($context, 'config'), "root", array(), "any", false);
                echo $this->getAttribute($this->getContext($context, 'config'), "spoiler_image", array(), "any", false);
                echo "
\t";
            } else {
                // line 43
                echo "\t\t";
                echo $this->getAttribute($this->getContext($context, 'config'), "uri_thumb", array(), "any", false);
                echo $this->getAttribute($this->getContext($context, 'post'), "thumb", array(), "any", false);
                echo "
\t";
            }
            // line 44
            echo "\" style=\"width:";
            echo $this->getAttribute($this->getContext($context, 'post'), "thumbx", array(), "any", false);
            echo "px;height:";
            echo $this->getAttribute($this->getContext($context, 'post'), "thumby", array(), "any", false);
            echo "px\" alt=\"\" /></a>
";
        }
        // line 46
        echo "<div class=\"post op\"><p class=\"intro\"";
        if ((!$this->getContext($context, 'index'))) {
            echo " id=\"";
            echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
            echo "\"";
        }
        echo ">
\t<input type=\"checkbox\" class=\"delete\" name=\"delete_";
        // line 47
        echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
        echo "\" id=\"delete_";
        echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
        echo "\" />
\t<label for=\"delete_";
        // line 48
        echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
        echo "\">
\t\t";
        // line 49
        if ((twig_length_filter($this->env, $this->getAttribute($this->getContext($context, 'post'), "subject", array(), "any", false)) > 0)) {
            // line 50
            echo "\t\t\t";
            // line 51
            echo "\t\t\t<span class=\"subject\">";
            echo $this->getAttribute($this->getContext($context, 'post'), "subject", array(), "any", false);
            echo "</span> 
\t\t";
        }
        // line 53
        echo "\t\t";
        if ((twig_length_filter($this->env, $this->getAttribute($this->getContext($context, 'post'), "email", array(), "any", false)) > 0)) {
            // line 54
            echo "\t\t\t";
            // line 55
            echo "\t\t\t<a class=\"email\" href=\"mailto:";
            echo $this->getAttribute($this->getContext($context, 'post'), "email", array(), "any", false);
            echo "\">
\t\t";
        }
        // line 57
        echo "\t\t<span class=\"name\">";
        echo $this->getAttribute($this->getContext($context, 'post'), "name", array(), "any", false);
        echo "</span>
\t\t";
        // line 58
        if ((twig_length_filter($this->env, $this->getAttribute($this->getContext($context, 'post'), "trip", array(), "any", false)) > 0)) {
            // line 59
            echo "\t\t\t<span class=\"trip\">";
            echo $this->getAttribute($this->getContext($context, 'post'), "trip", array(), "any", false);
            echo "</span>
\t\t";
        }
        // line 61
        echo "\t\t";
        if ((twig_length_filter($this->env, $this->getAttribute($this->getContext($context, 'post'), "email", array(), "any", false)) > 0)) {
            // line 62
            echo "\t\t\t";
            // line 63
            echo "\t\t\t</a>
\t\t";
        }
        // line 65
        echo "\t\t";
        if ((twig_length_filter($this->env, $this->getAttribute($this->getContext($context, 'post'), "capcode", array(), "any", false)) > 0)) {
            // line 66
            echo "\t\t\t";
            // line 67
            echo "\t\t\t";
            echo capcode($this->getAttribute($this->getContext($context, 'post'), "capcode", array(), "any", false));
            echo "
\t\t";
        }
        // line 69
        echo "\t\t";
        if (($this->getAttribute($this->getContext($context, 'post'), "mod", array(), "any", false) && twig_hasPermission_filter($this->getAttribute($this->getContext($context, 'post'), "mod", array(), "any", false), $this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "mod", array(), "any", false), "show_ip", array(), "any", false), $this->getAttribute($this->getContext($context, 'board'), "uri", array(), "any", false)))) {
            // line 70
            echo "\t\t\t [<a style=\"margin:0;\" href=\"?/IP/";
            echo $this->getAttribute($this->getContext($context, 'post'), "ip", array(), "any", false);
            echo "\">";
            echo $this->getAttribute($this->getContext($context, 'post'), "ip", array(), "any", false);
            echo "</a>]
\t\t";
        }
        // line 72
        echo "\t\t ";
        echo twig_date_filter($this->getAttribute($this->getContext($context, 'post'), "time", array(), "any", false), $this->getAttribute($this->getContext($context, 'config'), "post_date", array(), "any", false));
        echo "
\t</label>
\t";
        // line 74
        if ($this->getAttribute($this->getContext($context, 'config'), "poster_ids", array(), "any", false)) {
            // line 75
            echo "\t\t ID: ";
            echo poster_id($this->getAttribute($this->getContext($context, 'post'), "ip", array(), "any", false), $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false));
            echo "
\t";
        }
        // line 77
        echo "\t <a class=\"post_no\" href=\"";
        echo $this->getAttribute($this->getContext($context, 'post'), "link", array(), "any", false);
        echo "\">No.</a>
\t<a class=\"post_no\"
\t\t";
        // line 79
        if ((!$this->getContext($context, 'index'))) {
            // line 80
            echo "\t\t\t onclick=\"citeReply(";
            echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
            echo ");\"
\t\t";
        }
        // line 82
        echo "\t\t href=\"";
        if ($this->getContext($context, 'index')) {
            // line 83
            echo "\t\t\t";
            echo $this->getAttribute($this->getContext($context, 'post'), "link", array("q", ), "method", false);
            echo "
\t\t";
        } else {
            // line 85
            echo "\t\t\tjavascript:void(0);
\t\t";
        }
        // line 86
        echo "\">
\t\t";
        // line 87
        echo $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false);
        echo "
\t</a>
\t";
        // line 89
        if ($this->getAttribute($this->getContext($context, 'post'), "sticky", array(), "any", false)) {
            // line 90
            echo "\t\t<img class=\"icon\" title=\"Sticky\" src=\"";
            echo $this->getAttribute($this->getContext($context, 'config'), "image_sticky", array(), "any", false);
            echo "\" alt=\"Sticky\" />
\t";
        }
        // line 92
        echo "\t";
        if ($this->getAttribute($this->getContext($context, 'post'), "locked", array(), "any", false)) {
            // line 93
            echo "\t\t<img class=\"icon\" title=\"Locked\" src=\"";
            echo $this->getAttribute($this->getContext($context, 'config'), "image_locked", array(), "any", false);
            echo "\" alt=\"Locked\" />
\t";
        }
        // line 95
        echo "\t";
        if (($this->getAttribute($this->getContext($context, 'post'), "bumplocked", array(), "any", false) && (($this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "mod", array(), "any", false), "view_bumplock", array(), "any", false) < 0) || ($this->getAttribute($this->getContext($context, 'post'), "mod", array(), "any", false) && twig_hasPermission_filter($this->getAttribute($this->getContext($context, 'post'), "mod", array(), "any", false), $this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "mod", array(), "any", false), "view_bumplock", array(), "any", false), $this->getAttribute($this->getContext($context, 'board'), "uri", array(), "any", false)))))) {
            // line 96
            echo "\t\t<img class=\"icon\" title=\"Bumplocked\" src=\"";
            echo $this->getAttribute($this->getContext($context, 'config'), "image_bumplocked", array(), "any", false);
            echo "\" alt=\"Locked\" />
\t";
        }
        // line 98
        echo "\t";
        if ($this->getContext($context, 'index')) {
            // line 99
            echo "\t\t<a href=\"";
            echo $this->getAttribute($this->getContext($context, 'post'), "root", array(), "any", false);
            echo $this->getAttribute($this->getContext($context, 'board'), "dir", array(), "any", false);
            echo $this->getAttribute($this->getAttribute($this->getContext($context, 'config'), "dir", array(), "any", false), "res", array(), "any", false);
            echo sprintf($this->getAttribute($this->getContext($context, 'config'), "file_page", array(), "any", false), $this->getAttribute($this->getContext($context, 'post'), "id", array(), "any", false));
            echo "\">[";
            echo gettext("Reply");            echo "]</a>
\t";
        }
        // line 101
        echo "\t";
        echo $this->getAttribute($this->getContext($context, 'post'), "postControls", array(), "any", false);
        echo "
\t</p>
\t<p class=\"body\">
\t\t";
    }

    // line 104
    public function block___internal_7aa76e93140dd14e656757e295334806_2($context, array $blocks = array())
    {
        // line 105
        echo "\t</p>
\t";
        // line 106
        if (($this->getAttribute($this->getContext($context, 'post'), "omitted", array(), "any", false) || $this->getAttribute($this->getContext($context, 'post'), "omitted_images", array(), "any", false))) {
            // line 107
            echo "\t\t<span class=\"omitted\">
\t\t\t";
            // line 108
            if ($this->getAttribute($this->getContext($context, 'post'), "omitted", array(), "any", false)) {
                // line 109
                echo "\t\t\t\t";
                echo $this->getAttribute($this->getContext($context, 'post'), "omitted", array(), "any", false);
                echo " post
\t\t\t\t";
                // line 110
                if (($this->getAttribute($this->getContext($context, 'post'), "omitted", array(), "any", false) != 1)) {
                    // line 111
                    echo "\t\t\t\t\ts
\t\t\t\t";
                }
                // line 113
                echo "\t\t\t\t";
                if ($this->getAttribute($this->getContext($context, 'post'), "omitted_images", array(), "any", false)) {
                    // line 114
                    echo "\t\t\t\t\t and 
\t\t\t\t";
                }
                // line 116
                echo "\t\t\t";
            }
            // line 117
            echo "\t\t\t";
            if ($this->getAttribute($this->getContext($context, 'post'), "omitted_images", array(), "any", false)) {
                // line 118
                echo "\t\t\t\t";
                echo $this->getAttribute($this->getContext($context, 'post'), "omitted_images", array(), "any", false);
                echo " image repl
\t\t\t\t";
                // line 119
                if (($this->getAttribute($this->getContext($context, 'post'), "omitted_images", array(), "any", false) == 1)) {
                    // line 120
                    echo "\t\t\t\t\ty
\t\t\t\t";
                } else {
                    // line 122
                    echo "\t\t\t\t\ties
\t\t\t\t";
                }
                // line 124
                echo "\t\t\t";
            }
            echo " omitted. Click reply to view.
\t\t</span>
\t";
        }
        // line 127
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
