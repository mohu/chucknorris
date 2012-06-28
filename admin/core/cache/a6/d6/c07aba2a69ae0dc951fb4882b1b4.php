<?php

/* error-trace.html */
class __TwigTemplate_a6d6c07aba2a69ae0dc951fb4882b1b4 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = $this->env->loadTemplate("base.html");

        $this->blocks = array(
            'content' => array($this, 'block_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "base.html";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_content($context, array $blocks = array())
    {
        // line 4
        $this->env->loadTemplate("common/nav.html")->display($context);
        // line 5
        echo "<div class=\"row-fluid\">
    <div class=\"span12\">
            <div class=\"hero-unit\">
                <h1>Debug <small>Backtrace</small></h1>
            </div>
    </div>
</div>
<div class=\"row-fluid\">
    <div class=\"span12\">
        <div class=\"row-fluid\">
            <div class=\"span6\">
                <div class=\"well\">
                    <h3>Request method <small>";
        // line 17
        if (isset($context["backtrace"])) { $_backtrace_ = $context["backtrace"]; } else { $_backtrace_ = null; }
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($_backtrace_, 0), "context"), "_SERVER"), "REQUEST_METHOD"), "html", null, true);
        echo "</small></h3>
                    <h3>Request address <small>";
        // line 18
        if (isset($context["backtrace"])) { $_backtrace_ = $context["backtrace"]; } else { $_backtrace_ = null; }
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($_backtrace_, 0), "context"), "_SERVER"), "SERVER_NAME"), "html", null, true);
        if (isset($context["backtrace"])) { $_backtrace_ = $context["backtrace"]; } else { $_backtrace_ = null; }
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($_backtrace_, 0), "context"), "_SERVER"), "REQUEST_URI"), "html", null, true);
        echo "</small></h3>
                    <h3>Server time <small>";
        // line 19
        if (isset($context["backtrace"])) { $_backtrace_ = $context["backtrace"]; } else { $_backtrace_ = null; }
        echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute($_backtrace_, 0), "context"), "_SERVER"), "REQUEST_TIME"), "r"), "html", null, true);
        echo "</small></h3>
                </div>
            </div>
            <div class=\"span6\">
                <div class=\"well\">
                    <h3>Host <small>";
        // line 24
        if (isset($context["backtrace"])) { $_backtrace_ = $context["backtrace"]; } else { $_backtrace_ = null; }
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute($_backtrace_, 0), "context"), "host"), "html", null, true);
        echo "</small></h3>
                    <h3>Database <small>";
        // line 25
        if (isset($context["backtrace"])) { $_backtrace_ = $context["backtrace"]; } else { $_backtrace_ = null; }
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute($_backtrace_, 0), "context"), "db"), "html", null, true);
        echo "</small></h3>
                    <h3>PHP version <small>";
        // line 26
        if (isset($context["phpversion"])) { $_phpversion_ = $context["phpversion"]; } else { $_phpversion_ = null; }
        echo twig_escape_filter($this->env, $_phpversion_, "html", null, true);
        echo "</small></h3>
                </div>
            </div>
        </div>
    </div>
</div>
<div class=\"row-fluid\">
    <div class=\"span12\">
    ";
        // line 34
        if (isset($context["backtrace"])) { $_backtrace_ = $context["backtrace"]; } else { $_backtrace_ = null; }
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($_backtrace_);
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
        foreach ($context['_seq'] as $context["_key"] => $context["trace"]) {
            // line 35
            echo "        <div class=\"page-header\">
            <h1>Error <small>";
            // line 36
            if (isset($context["trace"])) { $_trace_ = $context["trace"]; } else { $_trace_ = null; }
            echo twig_escape_filter($this->env, $this->getAttribute($_trace_, "type"), "html", null, true);
            echo "</small></h1>
        </div>
        <blockquote>
            <p>";
            // line 39
            if (isset($context["trace"])) { $_trace_ = $context["trace"]; } else { $_trace_ = null; }
            echo $this->getAttribute($_trace_, "message");
            echo "</p>
            <small>In ";
            // line 40
            if (isset($context["trace"])) { $_trace_ = $context["trace"]; } else { $_trace_ = null; }
            echo twig_escape_filter($this->env, $this->getAttribute($_trace_, "file"), "html", null, true);
            echo " <strong>on line ";
            if (isset($context["trace"])) { $_trace_ = $context["trace"]; } else { $_trace_ = null; }
            echo twig_escape_filter($this->env, $this->getAttribute($_trace_, "line"), "html", null, true);
            echo "</strong></small>
        </blockquote>
        <h3>Backtrace</h3>
        ";
            // line 43
            if (isset($context["trace"])) { $_trace_ = $context["trace"]; } else { $_trace_ = null; }
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute($_trace_, "trace"));
            foreach ($context['_seq'] as $context["_key"] => $context["trace"]) {
                // line 44
                echo "            <p>";
                if (isset($context["trace"])) { $_trace_ = $context["trace"]; } else { $_trace_ = null; }
                echo twig_escape_filter($this->env, $this->getAttribute($_trace_, "file"), "html", null, true);
                echo " <strong>on line ";
                if (isset($context["trace"])) { $_trace_ = $context["trace"]; } else { $_trace_ = null; }
                echo twig_escape_filter($this->env, $this->getAttribute($_trace_, "line"), "html", null, true);
                echo " in <em>";
                if (isset($context["trace"])) { $_trace_ = $context["trace"]; } else { $_trace_ = null; }
                echo twig_escape_filter($this->env, $this->getAttribute($_trace_, "function"), "html", null, true);
                echo "</em></strong></p>
        ";
                // line 45
                if (isset($context["trace"])) { $_trace_ = $context["trace"]; } else { $_trace_ = null; }
                if ($this->getAttribute($_trace_, "output")) {
                    // line 46
                    echo "        <pre class=\"prettyprint linenum linenums:";
                    if (isset($context["trace"])) { $_trace_ = $context["trace"]; } else { $_trace_ = null; }
                    echo twig_escape_filter($this->env, $this->getAttribute($_trace_, "line"), "html", null, true);
                    echo "\"><code>";
                    if (isset($context["trace"])) { $_trace_ = $context["trace"]; } else { $_trace_ = null; }
                    echo twig_escape_filter($this->env, $this->getAttribute($_trace_, "output"), "html", null, true);
                    echo "</code></pre>
        ";
                }
                // line 48
                echo "        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['trace'], $context['_parent'], $context['loop']);
            $context = array_merge($_parent, array_intersect_key($context, $_parent));
            // line 49
            echo "        ";
            if (isset($context["loop"])) { $_loop_ = $context["loop"]; } else { $_loop_ = null; }
            if ((!$this->getAttribute($_loop_, "last"))) {
                // line 50
                echo "        <hr>
        ";
            }
            // line 52
            echo "    ";
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
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['trace'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 53
        echo "    </div>
</div>

";
    }

    public function getTemplateName()
    {
        return "error-trace.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  189 => 53,  175 => 52,  171 => 50,  167 => 49,  161 => 48,  151 => 46,  148 => 45,  136 => 44,  131 => 43,  121 => 40,  116 => 39,  109 => 36,  106 => 35,  88 => 34,  76 => 26,  71 => 25,  66 => 24,  57 => 19,  50 => 18,  45 => 17,  31 => 5,  29 => 4,  26 => 3,);
    }
}
