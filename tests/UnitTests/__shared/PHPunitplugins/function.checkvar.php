<?php
/**
 * Smarty plugin for testing scopes
 *


 */

use Smarty\DataObject;
use Smarty\Template;

/**
 * Smarty {checkvar}
 *
 * @param array $params parameter array
 * @param Template $template template object
 *
 * @return string
 */
function smarty_function_checkvar($params, \Smarty\Template $template)
{
    $output = '';
    $types = array('template', 'data', 'smarty', 'global');
    if (isset($params['types'])) {
        $types = (array)$params['types'];
    }
    $var = $params['var'];
    $ptr = $template;
    while ($ptr) {
        if (in_array('template', $types) && $ptr instanceof Template) {
            $output .= "#{$ptr->source->name}:\${$var} =";
            $output .= $ptr->hasVariable($var) ? preg_replace('/\s/', '', var_export($ptr->getValue($var), true)) : '>unassigned<';
            $i = 0;
            while (isset($ptr->_var_stack[ $i ])) {
                $output .= "#{$ptr->_var_stack[ $i ]['name']} = ";
                $output .= isset($ptr->_var_stack[ $i ][ 'tpl' ][$var]) ? preg_replace('/\s/', '', var_export($ptr->_var_stack[ $i ][ 'tpl' ][$var]->value, true)) : '>unassigned<';
                $i ++;
            }
            $ptr = $ptr->parent;
        } elseif (in_array('data', $types) && $ptr instanceof DataObject) {
            $output .= "#data:\${$var} =";
            $output .= $ptr->hasVariable($var) ? preg_replace('/\s/', '', var_export($ptr->getValue($var), true)) : '>unassigned<';
            $ptr = $ptr->parent;
        } else {
            $ptr = null;
        }
    }
    if (in_array('smarty', $types)) {
        $output .= "#Smarty:\${$var} =";
        $output .= $template->smarty->hasVariable($var) ?
            preg_replace('/\s/', '', var_export($template->smarty->getValue($var), true)) : '>unassigned<';
    }
    if (in_array('global', $types)) {
        $output .= "#global:\${$var} =";
        $output .= $template->_getSmartyObj()->getGlobalVariable($var) ?
            preg_replace('/\s/', '', var_export($template->_getSmartyObj()->getGlobalVariable($var)->getValue(), true)) : '>unassigned<';
    }
    return $output;
}
