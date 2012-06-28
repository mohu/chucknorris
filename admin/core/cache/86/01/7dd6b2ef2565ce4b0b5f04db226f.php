<?php

/* common/footer.html */
class __TwigTemplate_86017dd6b2ef2565ce4b0b5f04db226f extends Twig_Template
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
        echo "      </div><!--/row-->

      <hr>

      <footer>
        <p>&copy; Chuck Norris ";
        // line 6
        echo twig_escape_filter($this->env, twig_date_format_filter($this->env, "now", "Y"), "html", null, true);
        echo "</p>
      </footer>

    </div><!--/.fluid-container-->

    <!-- JavaScript at the bottom for fast page loading -->
    <script src=\"/admin/js/bootstrap.min.js\"></script>

  </body>
</html>";
    }

    public function getTemplateName()
    {
        return "common/footer.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  24 => 6,  69 => 36,  58 => 29,  39 => 21,  118 => 92,  17 => 1,  32 => 3,  28 => 5,  25 => 4,  23 => 3,  20 => 2,  18 => 1,  189 => 53,  175 => 52,  171 => 50,  167 => 49,  161 => 48,  151 => 46,  148 => 45,  136 => 44,  131 => 43,  121 => 40,  116 => 39,  109 => 91,  106 => 35,  88 => 34,  76 => 26,  71 => 25,  66 => 35,  57 => 19,  50 => 28,  45 => 17,  31 => 5,  29 => 4,  26 => 3,);
    }
}
