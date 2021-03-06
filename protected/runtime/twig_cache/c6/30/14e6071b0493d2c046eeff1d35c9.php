<?php

/* /views//layouts/frontEnd.html */
class __TwigTemplate_c63014e6071b0493d2c046eeff1d35c9 extends Twig_Template
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
    <title>Contest and Grants</title>
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <meta name=\"description\" content=\"\">
    <meta name=\"author\" content=\"\">

    <!-- Le styles -->
    <link href=\"css/bootstrap.css\" rel=\"stylesheet\">
    <link href=\"css/custom.css\" rel=\"stylesheet\">
    <link rel=\"stylesheet\" type=\"text/css\" href=\"css/jquery.pageslide.css\">
    <link rel=\"stylesheet\" href=\"css/responsiveslides.css\">
    <link rel=\"stylesheet\" href=\"css/responsiveslides.css\">
    

    <!-- Fav and touch icons -->
    <link rel=\"apple-touch-icon-precomposed\" sizes=\"144x144\" href=\"../assets/ico/apple-touch-icon-144-precomposed.png\">
    <link rel=\"apple-touch-icon-precomposed\" sizes=\"114x114\" href=\"../assets/ico/apple-touch-icon-114-precomposed.png\">
    <link rel=\"apple-touch-icon-precomposed\" sizes=\"72x72\" href=\"../assets/ico/apple-touch-icon-72-precomposed.png\">
    <link rel=\"apple-touch-icon-precomposed\" href=\"../assets/ico/apple-touch-icon-57-precomposed.png\">
    <link rel=\"shortcut icon\" href=\"../assets/ico/favicon.png\">
    
    <script src=\"js/jquery.js\"></script>
  </head>

  <body>
    <!-- container header -->
    <div class=\"navbar navbar-inverse navbar-fixed-top\">
      <div class=\"navbar-inner\">
        <div class=\"container\">
          <button type=\"button\" class=\"btn btn-navbar\" data-toggle=\"collapse\" data-target=\".nav-collapse\">
            <span class=\"icon-bar\"></span>
            <span class=\"icon-bar\"></span>
            <span class=\"icon-bar\"></span>
          </button>

          <a class=\"brand\" href=\"#\"> <img src=\"images/civiclinkslogo.png\" width=\"116\" height=\"24\" alt=\"logo\"></a>
          <div class=\"nav-collapse collapse\"> 
            ";
        // line 41
        if (isset($context["this"])) { $_this_ = $context["this"]; } else { $_this_ = null; }
        echo $this->getAttribute($_this_, "widget", array(0 => "zii.widgets.CMenu", 1 => array("htmlOptions" => array("class" => "nav"), "items" => array(0 => array("label" => "Fact Checking", "url" => array(0 => "#")), 1 => array("label" => "Timu", "url" => array(0 => "#")), 2 => array("label" => "Civic Links", "url" => array(0 => "#")), 3 => array("label" => "Contest and Grants", "url" => array(0 => "/site/index")))), 2 => true), "method");
        // line 49
        echo "
            <ul class=\"nav pull-right\">
              <li><a href=\"javascript:\$.pageslide({ direction: 'left', href: 'login.html' })\">Login</a></li>

            </ul><a href=\"javascript:\$.pageslide({ direction: 'left', href: 'register.html' })\" class=\"btn pull-right\">Sign Up</a> 

          </div>
        </div>
      </div>
    </div>
    
    <div class=\"container maincontent\">
      <div class=\"row\">
        <div class=\"span12\"><img src=\"images/fact-checking.png\" width=\"225\" height=\"43\" alt=\"fact\" style=\"margin-bottom:20px;\"></div>
      </div>
    </div>
    <!-- /container header -->
       
    <!-- Content  will be change according to request page (default - content of index page) -->  
    ";
        // line 68
        if (isset($context["content"])) { $_content_ = $context["content"]; } else { $_content_ = null; }
        echo $_content_;
        echo "
    
    
    <!-- container footer -->
    <div class=\"container darkfooter\">

      <footer>
        <div class=\"row\">
          <div class=\"span6\">
            <p> Donec sed odio dui. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Vestibulum id ligula porta felis euismod semper. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>
          </div>

          <div class=\"span6\">
            <div class=\"footer-header-background\"><span class=\"highlight-text\">Sign Up for our mailing list</span></div>
            <p> Donec sed odio dui. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Vestibulum id ligula porta felis euismod semper. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>
          </div>
        </div>
        <div class=\"row\"><div class=\"span12\">&nbsp;</div></div>
        <div class=\"row\">
          <div class=\"span6\">
            <div class=\"footer-header-background\"><span class=\"highlight-text\">Follow Us</span></div>
            <p><img src=\"images/facebook.png\" width=\"48\" height=\"48\" alt=\"facebook\" class=\"social-media-icons\"><img src=\"images/twitter.png\" width=\"48\" height=\"48\" alt=\"twitter\" class=\"social-media-icons\"><img src=\"images/googleplus.png\" width=\"48\" height=\"48\" alt=\"google\" class=\"social-media-icons\"><img src=\"images/youtube.png\" width=\"48\" height=\"48\" alt=\"youtube\" class=\"social-media-icons\"><img src=\"images/rss.png\" width=\"48\" height=\"48\" alt=\"rss\" class=\"social-media-icons\"></p>
          </div>

          <div class=\"span6\">
            <div class=\"footer-header-background\"><span class=\"highlight-text\">Latest Tweets</span></div>
            <p> Donec sed odio dui. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Vestibulum id ligula porta felis euismod semper. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>
          </div>
        </div>

      </footer>

    </div>
    <!-- /container footer -->

    <script src=\"js/bootstrap.js\"></script>
    <script src=\"js/jquery.pageslide.min.js\"></script>
    <script src=\"js/responsiveslides.js\"></script>

    <script>
      \$(function () {
        // Slideshow 2
        \$(\"#myslider\").responsiveSlides({
          auto: false,
          pager: true,
          speed: 300,
          manualControls: '#myslider-pager',
        });
      });
    </script>
  </body>";
    }

    public function getTemplateName()
    {
        return "/views//layouts/frontEnd.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  85 => 68,  64 => 49,  61 => 41,  19 => 1,);
    }
}
