<?php

class MKleine_Mailpreview_Model_Email_Template_Filter extends Mage_Core_Model_Email_Template_Filter
{
    public function getCurrentReplacements($text)
    {
        $vars = array();

        $matches = array();
        if (preg_match_all(Varien_Filter_Template::CONSTRUCTION_PATTERN, $text, $matches, PREG_SET_ORDER))
        {
            foreach($matches as $index => $construction) {

                $match = $construction[0];
                $directiveType = $construction[1];
                $variableName = $construction[2];

                $replacedValue = '';
                $callback = array($this, $directiveType.'Directive');

                if(!is_callable($callback)) {
                    continue;
                }

                try {
                    $replacedValue = call_user_func($callback, $construction);
                } catch (Exception $e) {
                    Mage::log($e);
                }

                if (in_array($directiveType, array('var'))) {
                    $vars[trim($variableName)] = array(
                        'type' => $directiveType,
                        'replacement' => $replacedValue,
                        'replaced' => ($replacedValue != $match)
                    );
                }
                else if (in_array($directiveType, array('htmlescape'))) {
                    $default = '{mk_no_replacement}';
                    foreach($this->getVariableNamesOf($variableName, $default) as $var => $value) {
                        $vars[trim($var)] = array(
                            'type' => $directiveType,
                            'replacement' => $value,
                            'replaced' => ($value != $default)
                        );
                    }
                }
            }
        }

        $vars['logo_url'] = array(
            'type' => 'var',
            'replacement' => 'test',
            'replaced' => ($replacedValue != $match)
        );

        return $vars;
    }

    protected function getVariableNamesOf($value, $default = null)
    {
        $vars = array();

        $tokenizer = new Varien_Filter_Template_Tokenizer_Parameter();
        $tokenizer->setString($value);
        $params = $tokenizer->tokenize();
        foreach ($params as $key => $value) {
            if (substr($value, 0, 1) === '$') {
                $variable = substr($value, 1);
                $vars[$variable] = $this->_getVariable(substr($value, 1), $default);
            }
        }

        return $vars;
    }
}