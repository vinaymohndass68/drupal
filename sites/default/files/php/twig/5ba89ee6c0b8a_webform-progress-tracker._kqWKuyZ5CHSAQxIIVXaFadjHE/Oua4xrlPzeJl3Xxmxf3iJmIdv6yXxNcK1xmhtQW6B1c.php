<?php

/* modules/contrib/webform/templates/webform-progress-tracker.html.twig */
class __TwigTemplate_67571bcb7984796c73626548c3126bdb5168dd59b6da0b68d33921db42500c69 extends Twig_Template
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
        $tags = array("for" => 23, "set" => 24, "if" => 35);
        $filters = array("length" => 35, "t" => 39);
        $functions = array("attach_library" => 20);

        try {
            $this->env->getExtension('Twig_Extension_Sandbox')->checkSecurity(
                array('for', 'set', 'if'),
                array('length', 't'),
                array('attach_library')
            );
        } catch (Twig_Sandbox_SecurityError $e) {
            $e->setSourceContext($this->getSourceContext());

            if ($e instanceof Twig_Sandbox_SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

        // line 20
        echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->env->getExtension('Drupal\Core\Template\TwigExtension')->attachLibrary("webform/webform.progress.tracker"), "html", null, true));
        echo "

<ul class=\"webform-progress-tracker progress-tracker progress-tracker--center\">
  ";
        // line 23
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["progress"] ?? null));
        foreach ($context['_seq'] as $context["index"] => $context["page"]) {
            // line 24
            echo "    ";
            $context["is_completed"] = ($context["index"] < ($context["current_index"] ?? null));
            // line 25
            echo "    ";
            $context["is_active"] = ($context["index"] == ($context["current_index"] ?? null));
            // line 26
            echo "    ";
            // line 27
            $context["classes"] = array(0 => "progress-step", 1 => ((            // line 29
($context["is_completed"] ?? null)) ? ("is-complete") : ("")), 2 => ((            // line 30
($context["is_active"] ?? null)) ? ("is-active") : ("")));
            // line 33
            echo "    <li";
            echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute(($context["attributes"] ?? null), "setAttribute", array(0 => "data-webform-page", 1 => $this->getAttribute($context["page"], "name", array())), "method"), "setAttribute", array(0 => "title", 1 => $this->getAttribute($context["page"], "title", array())), "method"), "setAttribute", array(0 => "class", 1 => ""), "method"), "addClass", array(0 => ($context["classes"] ?? null)), "method"), "html", null, true));
            echo ">
      <span class=\"progress-marker\">";
            // line 34
            echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, ($context["index"] + 1), "html", null, true));
            echo "</span>
      ";
            // line 35
            if ((twig_length_filter($this->env, ($context["progress"] ?? null)) < ($context["max_pages"] ?? null))) {
                // line 36
                echo "        <span class=\"progress-text\">
          <div class=\"progress-title\">
            ";
                // line 38
                if ((($context["is_active"] ?? null) || ($context["is_completed"] ?? null))) {
                    // line 39
                    echo "              <span class=\"visually-hidden\">";
                    echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->renderVar(((($context["is_active"] ?? null)) ? (t("Current")) : (t("Completed")))));
                    echo ": </span>
            ";
                }
                // line 41
                echo "            ";
                echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->getAttribute($context["page"], "title", array()), "html", null, true));
                echo "
          </div>
        </span>
      ";
            }
            // line 45
            echo "    </li>
  ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['index'], $context['page'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 47
        echo "</ul>
";
    }

    public function getTemplateName()
    {
        return "modules/contrib/webform/templates/webform-progress-tracker.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  103 => 47,  96 => 45,  88 => 41,  82 => 39,  80 => 38,  76 => 36,  74 => 35,  70 => 34,  65 => 33,  63 => 30,  62 => 29,  61 => 27,  59 => 26,  56 => 25,  53 => 24,  49 => 23,  43 => 20,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "modules/contrib/webform/templates/webform-progress-tracker.html.twig", "F:\\xampp\\htdocs\\drupal\\modules\\contrib\\webform\\templates\\webform-progress-tracker.html.twig");
    }
}
