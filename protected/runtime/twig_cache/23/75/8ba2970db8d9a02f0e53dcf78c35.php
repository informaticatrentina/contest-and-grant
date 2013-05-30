<?php

/* /views/site/index.html */
class __TwigTemplate_23758ba2970db8d9a02f0e53dcf78c35 extends Twig_Template
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
        echo "<div class=\"container maincontent\">
  <!-- Slideshow -->
  <ul class=\"rslides\" id=\"myslider\">
    ";
        // line 4
        if (isset($context["contestInfo"])) { $_contestInfo_ = $context["contestInfo"]; } else { $_contestInfo_ = null; }
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($_contestInfo_);
        foreach ($context['_seq'] as $context["_key"] => $context["info"]) {
            // line 5
            echo "      <li><a href=\"index.php?r=site/Entries&id=";
            if (isset($context["info"])) { $_info_ = $context["info"]; } else { $_info_ = null; }
            echo $this->getAttribute($_info_, "contestId");
            echo "\"><img src=\"";
            if (isset($context["info"])) { $_info_ = $context["info"]; } else { $_info_ = null; }
            echo $this->getAttribute($_info_, "imagePath");
            echo "\" alt=\"contest image\" /></a></li>  
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['info'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 7
        echo "    
  </ul>

  <!-- Slideshow 2 Pager -->
  <div style=\"position:relative; top:-60px; z-index:9; list-style:circle;\">
    <ul id=\"myslider-pager\" style=\"list-style:circle; background-color:#000000;\">
      ";
        // line 13
        if (isset($context["contestInfo"])) { $_contestInfo_ = $context["contestInfo"]; } else { $_contestInfo_ = null; }
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($_contestInfo_);
        foreach ($context['_seq'] as $context["_key"] => $context["info"]) {
            // line 14
            echo "        <li style=\"list-style:disc;\"><a href=\"#\">";
            if (isset($context["info"])) { $_info_ = $context["info"]; } else { $_info_ = null; }
            echo $this->getAttribute($_info_, "contestId");
            echo "</a></li>  
      ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['info'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 16
        echo "    </ul>
  </div>
  <hr>

  <!-- Three boxes -->
  <div class=\"row\">
    <div class=\"span4\">
      <h2>Concorsi Aperti</h2>
      <div class=\"homepage-box\">
        <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
        <p><a class=\"btn\" href=\"#\">View details &raquo;</a></p>
      </div>
    </div>
    <div class=\"span4\">
      <h2>Crea il tuo Concorso</h2>
      <div class=\"homepage-box\">
        <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
        <p><a class=\"btn\" href=\"#\">View details &raquo;</a></p>
      </div>
    </div>
    <div class=\"span4\">
      <h2>Principi</h2>
      <div class=\"homepage-box\">
        <p class=\"text-center\"><img src=\"images/principi.png\" width=\"226\" height=\"227\" alt=\"principi\"></p>
      </div>
    </div>
  </div>

  <hr>

</div>";
    }

    public function getTemplateName()
    {
        return "/views/site/index.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  65 => 16,  55 => 14,  50 => 13,  42 => 7,  29 => 5,  24 => 4,  19 => 1,);
    }
}
