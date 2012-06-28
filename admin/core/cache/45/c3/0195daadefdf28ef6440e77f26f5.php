<?php

/* common/header.html */
class __TwigTemplate_45c30195daadefdf28ef6440e77f26f5 extends Twig_Template
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
        echo "<!DOCTYPE html>
<html lang=\"en\">
  <head>
    <meta charset=\"utf-8\">
    <title>Chuck Norris</title>
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <meta name=\"description\" content=\"\">
    <meta name=\"author\" content=\"\">

    <!-- Styles -->
    <link href=\"/admin/css/bootstrap.css\" rel=\"stylesheet\">
    <style type=\"text/css\">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }
    </style>
    <link href=\"/admin/css/bootstrap-responsive.css\" rel=\"stylesheet\">
    <link href=\"/admin/css/datepicker.css\" rel=\"stylesheet\">
    <link href=\"/admin/css/prettify.css\" rel=\"stylesheet\">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src=\"http://html5shim.googlecode.com/svn/trunk/html5.js\"></script>
    <![endif]-->

    <!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
    <script src=\"//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js\"></script>
    <script>window.jQuery || document.write('<script src=\"/admin/js/libs/jquery-1.7.1.min.js\"><\\/script>')</script>
    <script src=\"/admin/js/bootstrap-datepicker.js\"></script>
    <script src=\"/admin/js/jquery.validate.js\"></script>
    <script src=\"/admin/libs/dataTables/js/jquery.dataTables.min.js\"></script>
    <script src=\"/admin/js/prettify.js\"></script>

    <!-- Load TinyMCE -->
    <script src=\"/admin/libs/tinymce/jscripts/tiny_mce/jquery.tinymce.js\"></script>
    <script type=\"text/javascript\">
      \$().ready(function() {
        \$('textarea').tinymce({
          // Location of TinyMCE script
          script_url : '/admin/libs/tinymce/jscripts/tiny_mce/tiny_mce.js',

          // update validation status on change
          onchange_callback: function(editor) {
            tinyMCE.triggerSave();
            \$(\"#\" + editor.id).valid();
          },

          // General options
          theme : \"advanced\",
          skin : \"thebigreason\",
          plugins : \"paste\",
          width: \"500\",
          height: \"250\",
          force_p_newlines : true,
          force_br_newlines : false,
          paste_create_paragraphs: true,
          theme_advanced_styles : \"Paragraph=p;Header 1=h2;Header 2=h3;Header 3=h4\",

          // Theme options
          theme_advanced_buttons1 : \"styleselect,bold,italic,underline,strikethrough,|,bullist,numlist,|,undo,redo,|,link,unlink,|,charmap,fullscreen,|,pastetext,pasteword\",
          theme_advanced_buttons2 : \"\",
          theme_advanced_buttons3 : \"\",
          theme_advanced_buttons4 : \"\",
          theme_advanced_toolbar_location : \"top\",
          theme_advanced_toolbar_align : \"left\",
          theme_advanced_statusbar_location : \"bottom\",
          theme_advanced_resizing : true,
          paste_auto_cleanup_on_paste : true,
            paste_preprocess : function(pl, o) {
                // Content string containing the HTML from the clipboard
                // alert(o.content);
                o.content = o.content;
            },
            paste_postprocess : function(pl, o) {
                // Content DOM node containing the DOM structure of the clipboard
                // alert(o.node.innerHTML);
                o.node.innerHTML = o.node.innerHTML;
            }
        });
      });
    </script>
    <!-- /TinyMCE -->

    <script type=\"text/javascript\">
    \$(document).ready(function() {
      \$('.dropdown-toggle').dropdown();
      if (";
        // line 91
        if (isset($context["module"])) { $_module_ = $context["module"]; } else { $_module_ = null; }
        if ($_module_) {
            echo "true";
        } else {
            echo "false";
        }
        echo ") {
        \$('ul.nav-list li.";
        // line 92
        if (isset($context["module"])) { $_module_ = $context["module"]; } else { $_module_ = null; }
        echo twig_escape_filter($this->env, $_module_, "html", null, true);
        echo "').addClass('active');
      }
    });
    </script>

  </head>

  <body onload=\"prettyPrint()\">";
    }

    public function getTemplateName()
    {
        return "common/header.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  118 => 92,  17 => 1,  32 => 3,  28 => 5,  25 => 4,  23 => 3,  20 => 2,  18 => 1,  189 => 53,  175 => 52,  171 => 50,  167 => 49,  161 => 48,  151 => 46,  148 => 45,  136 => 44,  131 => 43,  121 => 40,  116 => 39,  109 => 91,  106 => 35,  88 => 34,  76 => 26,  71 => 25,  66 => 24,  57 => 19,  50 => 18,  45 => 17,  31 => 5,  29 => 4,  26 => 3,);
    }
}
