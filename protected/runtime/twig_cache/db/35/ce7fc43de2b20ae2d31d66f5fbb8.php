<?php

/* /views/site/contestCreation.html */
class __TwigTemplate_db35ce7fc43de2b20ae2d31d66f5fbb8 extends Twig_Template
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
        echo "<link rel=\"stylesheet\" href=\"css/jquery-ui-1.8.21.custom.css\">

<div class=\"container darkheader\">
  <div class=\"row\">
    <div class=\"span12\"><h1> Create Contest</h1></div>
  </div>
</div>
<div class=\"container maincontent\">
  <form class=\"form-signin\" action=\"index.php?r=site/createContest\" method=\"post\"  enctype = \"multipart/form-data\">
    ";
        // line 10
        if (isset($context["message"])) { $_message_ = $context["message"]; } else { $_message_ = null; }
        if ((!twig_test_empty($_message_))) {
            // line 11
            echo "      <span class=\"text-error\">
        ";
            // line 12
            if (isset($context["message"])) { $_message_ = $context["message"]; } else { $_message_ = null; }
            echo $_message_;
            echo "
      </span>
    ";
        }
        // line 15
        echo "    <label>Contest Title</label>
    <input type=\"text\" class=\"input-block-level\" name=\"contestTitle\" required placeholder=\"contest Title\">
    <label>Start Date</label>
    <input type=\"text\" name=\"startDate\" class=\"input-block-level\" id=\"start_date\" readonly=\"readonly\" required placeholder =\"Stat Date\" />
    <label>End Date</label>
    <input type=\"text\" name=\"endDate\" class=\"input-block-level\" id=\"end_date\" readonly=\"readonly\" required placeholder =\"End Date\" />
    <label>Upload Image</label>
    <input type=\"file\" required class=\"input-block-level\" name=\"image\" />
    <label>Contest Description</label>
    <textarea  class=\"input-block-level textarea\" name=\"contestDescription\" required placeholder=\"Contest Description\" ></textarea> 
    <button class=\"btn btn-large btn-primary\" type=\"submit\" name='contestDetail'>create contest</button>
  </form>
  <hr>
</div>  
<script src=\"js/jquery.ui.datepicker.js\"></script>
<script src=\"js/jquery.ui.core.js\"></script>
<script src=\"js/common.js\"></script>";
    }

    public function getTemplateName()
    {
        return "/views/site/contestCreation.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  43 => 15,  36 => 12,  33 => 11,  30 => 10,  19 => 1,);
    }
}
