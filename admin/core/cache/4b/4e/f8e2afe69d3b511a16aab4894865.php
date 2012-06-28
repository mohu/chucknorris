<?php

/* base.html */
class __TwigTemplate_4b4ef8e2afe69d3b511a16aab4894865 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'content' => array($this, 'block_content'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        $this->env->loadTemplate("common/header.html")->display($context);
        // line 2
        echo "
";
        // line 3
        $this->displayBlock('content', $context, $blocks);
        // line 4
        echo "
";
        // line 5
        $this->env->loadTemplate("common/footer.html")->display($context);
    }

    // line 3
    public function block_content($context, array $blocks = array())
    {
    }

    public function getTemplateName()
    {
        return "base.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  32 => 3,  28 => 5,  25 => 4,  23 => 3,  20 => 2,  18 => 1,  189 => 53,  175 => 52,  171 => 50,  167 => 49,  161 => 48,  151 => 46,  148 => 45,  136 => 44,  131 => 43,  121 => 40,  116 => 39,  109 => 36,  106 => 35,  88 => 34,  76 => 26,  71 => 25,  66 => 24,  57 => 19,  50 => 18,  45 => 17,  31 => 5,  29 => 4,  26 => 3,);
    }
}
