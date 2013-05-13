<?php

/* /views/site/contestEntries.html */
class __TwigTemplate_35a5bfa5485a23eb58eb2b993bdef01f extends Twig_Template
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
        echo "<div class=\"container darkheader\">
  <div class=\"row\">
    <div class=\"span12\"><h1>";
        // line 3
        if (isset($context["contestInfo"])) { $_contestInfo_ = $context["contestInfo"]; } else { $_contestInfo_ = null; }
        echo $this->getAttribute($_contestInfo_, "contestTitle");
        echo "</h1></div>
  </div>
</div>
<div class=\"container maincontent\">
  <div class=\"row\">
    <div class=\"span8\" style=\"min-height:300px; background-color: #666; position:relative; \">
      <img src= ";
        // line 9
        if (isset($context["contestInfo"])) { $_contestInfo_ = $context["contestInfo"]; } else { $_contestInfo_ = null; }
        echo $this->getAttribute($_contestInfo_, "imagePath");
        echo " />
      <div style=\"position:absolute; top:180px; width:100%; text-align:right;\">
        <img src=\"images/facebook.png\" width=\"30px\" height=\"30px\" alt=\"facebook\" class=\"social-media-icons\" /><br>
        <img src=\"images/twitter.png\" width=\"30px\" height=\"30px\" alt=\"twitter\" class=\"social-media-icons\" /><br>
        <img src=\"images/rss.png\" width=\"30px\" height=\"30px\" alt=\"rss\" class=\"social-media-icons\" />
      </div>
    </div>
    <div class=\"span4\">
      <div class=\"homepage-box\" style=\"min-height:260px;\">
        <h4>Start Date: ";
        // line 18
        if (isset($context["contestInfo"])) { $_contestInfo_ = $context["contestInfo"]; } else { $_contestInfo_ = null; }
        echo $this->getAttribute($_contestInfo_, "startDate");
        echo "<br>
          End Date: ";
        // line 19
        if (isset($context["contestInfo"])) { $_contestInfo_ = $context["contestInfo"]; } else { $_contestInfo_ = null; }
        echo $this->getAttribute($_contestInfo_, "endDate");
        echo "</h4>
        <p>";
        // line 20
        if (isset($context["contestInfo"])) { $_contestInfo_ = $context["contestInfo"]; } else { $_contestInfo_ = null; }
        echo $this->getAttribute($_contestInfo_, "contestDescription");
        echo "</p>
      </div>
    </div>
  </div>

  <hr>

  <!-- Three page buttons -->
  <div class=\"row\">
    <div class=\"span4\">
      <a href=\"contest-brief.html\" class=\"contest-button btn-block\">Contest Briefs</a>
    </div>

    <div class=\"span4\">
      <a href=\"#\" class=\"contest-button btn-block\">Submit and win</a>
    </div>

    <div class=\"span4\">
      <a href=\"contest-winners.html\" class=\"contest-button btn-block\">winners</a>
    </div>
  </div>

  <hr>

  <!-- Contest entries listing using masonary -->

  <div id=\"posts\" class=\"row\">
    ";
        // line 47
        if (isset($context["entries"])) { $_entries_ = $context["entries"]; } else { $_entries_ = null; }
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($_entries_);
        foreach ($context['_seq'] as $context["_key"] => $context["entry"]) {
            // line 48
            echo "      <div class=\"post span4\">
        <div style=\"padding:10px;\">
          <img src=\"images/gameofthrones/game_of_thrones.jpg\" width=\"600\" height=\"400\" />
          <h6>";
            // line 51
            if (isset($context["entry"])) { $_entry_ = $context["entry"]; } else { $_entry_ = null; }
            echo $this->getAttribute($_entry_, "title");
            echo "</h6>
          by Mario Rossi
          <div class=\"pull-right\"><img src=\"images/facebook.png\" width=\"24px\" height=\"24px\" alt=\"facebook\" />
            <img src=\"images/twitter.png\" width=\"24px\" height=\"24px\" alt=\"twitter\" />
          </div>
        </div>
      </div>   
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['entry'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 58
        echo "   
  </div>
  
  ";
        // line 61
        if (isset($context["entries"])) { $_entries_ = $context["entries"]; } else { $_entries_ = null; }
        if ((!twig_test_empty($_entries_))) {
            // line 62
            echo "    <div class=\"row\">
      <div class=\"span12\">
        <a href=\"#\" class=\"btn btn-large btn-block\">SEE ALL</a> 
      </div>
    </div>
    <hr>
  ";
        }
        // line 69
        echo "  
</div>
<!-- Masonary scripts for listing -->
<script src=\"js/jquery.masonry.min.js\"></script>
<script>
  
  \$(document).ready(function () {
\t\t   
    \$(\"#posts\").masonry({
      itemSelector: '.post',
      isAnimated: true,
      columnWidth: function( containerWidth ) {
        
        var width = \$(window).width();
        var col = 200;
        
        
        if(width < 1200 && width >= 980) {
          col = 160;
        }
        else if(width < 980 && width >= 768) {
          col = 8;
        }
        
        return col;
      }
    });
    
  });
</script>
";
    }

    public function getTemplateName()
    {
        return "/views/site/contestEntries.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  129 => 69,  120 => 62,  117 => 61,  112 => 58,  97 => 51,  92 => 48,  87 => 47,  56 => 20,  51 => 19,  46 => 18,  33 => 9,  23 => 3,  19 => 1,);
    }
}
