<?php

/* /views/site/login.html */
class __TwigTemplate_2187d8a4eded107369ef8be3da958367 extends Twig_Template
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
        echo "
    <style type=\"text/css\">
      body {
        padding-top: 40px;
        padding-bottom: 40px;
\t\tbackground-color: #333;
\t\tmargin: 20px;
      }

      .form-signin {
        max-width: 300px;
        padding: 19px 29px 29px;
        margin: 0 auto 20px;
        background-color: #fff;
        border: 1px solid #e5e5e5;
        -webkit-border-radius: 5px;
           -moz-border-radius: 5px;
                border-radius: 5px;
        -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
           -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                box-shadow: 0 1px 2px rgba(0,0,0,.05);
      }
      .form-signin .form-signin-heading,
      .form-signin .checkbox {
        margin-bottom: 10px;
      }
      .form-signin input[type=\"text\"],
      .form-signin input[type=\"password\"] {
        font-size: 16px;
        height: auto;
        margin-bottom: 15px;
        padding: 7px 9px;
      }
\t @media (max-width: 767px) {
\t\t 
\t\t body {
        padding-top: 0px;
\t\tmargin: 0px;
      } 
\t\t 
\t\t 
\t }

    </style>



    <div class=\"container\">
    
<form class=\"form-signin\" action=\"\">
    
    \t<input type=\"text\" class=\"input-block-level\" placeholder=\"contest Title\" />
        <input type=\"text\" name=\"startDate\" class=\"input-block-level\" id=\"start_date\" readonly=\"readonly\" placeholder =\"Stat Date\" />
       
        <input type=\"text\" class=\"input-block-level\" placeholder=\"End Date\">
        <input class=\"input-block-level\" type=\"file\">
        <textarea  class=\"input-block-level textarea\" placeholder=\"Contest Description\" ></textarea> 
        <button class=\"btn btn-large btn-primary\" type=\"submit\">Create</button>
        
        <hr>
       
      </form>
      
    </div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src=\"js/jquery.js\"></script>
    <script src=\"js/bootstrap.js\"></script>
    
    
";
    }

    public function getTemplateName()
    {
        return "/views/site/login.html";
    }

    public function getDebugInfo()
    {
        return array (  19 => 1,);
    }
}
