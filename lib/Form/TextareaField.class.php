<?php
namespace Form;
use Form\Field;

class TextareaField extends Field{
    public function __construct($config){
        parent::__construct($config);
    }

    public function to_html($is_new){
        $class=$this->config['class'];
        $html="<div class='control-group'>";
        $html.= "<label class='control-label'>".htmlspecialchars($this->label)."</label>".
            "<div class='controls'>".
            "<textarea class='$class span4' name='{$this->name}'>{$this->value}</textarea>";
        if($this->error){
            $html.="<span class='help-inline'>".$this->error."</span>";
        }
        $html.='</div>';
        $html.='</div>';
        return $html;
    }
}
