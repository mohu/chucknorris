<?php

/* common/nav.html */
class __TwigTemplate_65b90560b52f41eca2fc4a0f9e51622a extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "    <div class=\"navbar navbar-fixed-top\">
      <div class=\"navbar-inner\">
        <div class=\"container\">
          <a class=\"btn btn-navbar\" data-toggle=\"collapse\" data-target=\".nav-collapse\">
            <span class=\"icon-bar\"></span>
            <span class=\"icon-bar\"></span>
            <span class=\"icon-bar\"></span>
          </a>
          <a class=\"brand\" href=\"/admin\">Chuck Norris</a>
          <div class=\"nav-collapse\">
            <ul class=\"nav\">
              <li><a href=\"/admin\">cPanel</a></li>
              <li><a href=\"/admin/subscribers\">News Subscribers</a></li>
              <li><a href=\"/admin/enquiries\">Enquiries</a></li>
              <li><a href=\"/admin/careerenquiries\">Career Enquiries</a></li>
              <li><a href=\"/admin/settings\">Settings</a></li>
            </ul>
            <ul class=\"nav pull-right\">
              <li><a href=\"/\" target=\"_blank\">Preview</a></li>
              <li class=\"dropdown\" id=\"menu1\">
                <a class=\"dropdown-toggle\" data-toggle=\"dropdown\" href=\"#menu1\">Logout ";
        // line 21
        if (isset($context["session"])) { $_session_ = $context["session"]; } else { $_session_ = null; }
        echo twig_escape_filter($this->env, $this->getAttribute($_session_, "user"), "html", null, true);
        echo "? <b class=\"caret\"></b></a>
                <ul class=\"dropdown-menu\">
                  <li><a href=\"/admin/logout/\"><i class=\"icon-off\"></i> Logout</a></li>
                </ul>
              </li>
            </ul>
          </ul>
          ";
        // line 28
        if (isset($context["session"])) { $_session_ = $context["session"]; } else { $_session_ = null; }
        if ($this->getAttribute($_session_, "region")) {
            echo "<p class=\"navbar-text pull-right\">Region: <span class=\"badge badge-info\">";
            if (isset($context["session"])) { $_session_ = $context["session"]; } else { $_session_ = null; }
            echo twig_escape_filter($this->env, $this->getAttribute($_session_, "region"), "html", null, true);
            echo "</span></p>";
        }
        // line 29
        echo "          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>
    <div class=\"container-fluid\">
        <div class=\"row-fluid\">
";
        // line 35
        if (isset($context["backtrace"])) { $_backtrace_ = $context["backtrace"]; } else { $_backtrace_ = null; }
        if ((!$this->getAttribute($_backtrace_, 0))) {
            // line 36
            echo "    ";
            $this->env->loadTemplate("common/sidenav.html")->display($context);
        }
    }

    public function getTemplateName()
    {
        return "common/nav.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  69 => 36,  58 => 29,  39 => 21,  118 => 92,  17 => 1,  32 => 3,  28 => 5,  25 => 4,  23 => 3,  20 => 2,  18 => 1,  189 => 53,  175 => 52,  171 => 50,  167 => 49,  161 => 48,  151 => 46,  148 => 45,  136 => 44,  131 => 43,  121 => 40,  116 => 39,  109 => 91,  106 => 35,  88 => 34,  76 => 26,  71 => 25,  66 => 35,  57 => 19,  50 => 28,  45 => 17,  31 => 5,  29 => 4,  26 => 3,);
    }
}
