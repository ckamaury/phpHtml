<?php
namespace CkAmaury\PhpHtml;

use CkAmaury\PhpMagicFunctions\ArrayUtils;

class FormAnalyzer{


    private $text;
    private $forms;

    public function __construct($text) {
        $this->text = $text;
        $this->extractForms();
    }

    private function preg_match_array($p_Pattern,$p_Text){
        $matches = array();
        preg_match_all($p_Pattern, $p_Text, $matches, PREG_OFFSET_CAPTURE);
        if(1 === count($matches)){
            foreach($matches[0] as $key => $value){
                $matches[$key] = $value;
            }
        }
        return $matches;
    }


    /**
     * Supprime les commentaires HTML
     * @param $p_Text
     * @return string|string[]|null
     */
    private function removeHTML_Comment($p_Text){
        return preg_replace('/<!--.*-->/','',$p_Text);
    }

    private function valuesForm($p_Matches){
        $return = array();
        if(array_key_exists(0,$p_Matches)){
            foreach($p_Matches[0] as $key => $value){
                $new_key    = $p_Matches[1][$key][0];
                $new_value  = $p_Matches[2][$key][0];
                $return[$new_key] = $new_value;
            }
        }
        return $return;
    }

    /**
     * Extrait les formulaires
     */
    private function extractForms(){
        $p_Text = $this->removeHTML_Comment($this->text);
        $array = ($this->preg_match_array('/<form([^>]*)>(.*?)<\/form>/s',$p_Text));
        $form = array();
        if(is_array($array) && is_array($array[0])){
            foreach($array[0] as $key => $value){

                //CHAMPS FORM
                $text = $array[1][$key][0];
                $text = preg_replace('/ /','',$text);
                $form[$key] = $this->valuesForm($this->preg_match_array('/([^=]*)="([^"]*)"/',$text));

                //TAGS INPUT
                $text = $array[2][$key][0];
                $text = $this->preg_match_array('/<input([^>]*)>/',$text);
                foreach($text[1] as $key2 => $value2){
                    $last_value = preg_replace('/ /','',$value2[0]);
                    $form[$key]['INPUT_TAGS'][] = $this->valuesForm($this->preg_match_array('/([^=]*)="([^"]*)"/',$last_value));
                }
            }
        }
        $this->forms = $form;
    }


    /**
     * @return array
     */
    public function getForms() {
        return $this->forms;
    }




    public function getInputsArray($p_Key){
        $inputs = array();
        if(array_key_exists($p_Key,$this->forms)){
            $tags = $this->forms[$p_Key]['INPUT_TAGS'];
            foreach($tags as $value){
                if(array_key_exists('name',$value)){
                    $inputs[ArrayUtils::get('name',$value)] = ArrayUtils::get('value',$value);
                }
            }
        }
        return $inputs;
    }

    public function getInputsHttpQuery($p_Key){
        return http_build_query($this->getInputsArray($p_Key));
    }

}