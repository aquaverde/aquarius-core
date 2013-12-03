<?php 
class Formtype_Int extends Formtype {

    private function parseval($val, $formfield) {
        if (!is_numeric($val)) return null; 
        if ($formfield->sup1 == 0) {
            return intval($val);
        } else {
            $precision = max(0, $formfield->sup1);
            return bcadd($val, '0', $precision); // There's no specific parse function, so we use bcadd() instead
        }
    }

    function pre_contentedit($node, $content, $formtype, $formfield, $valobject) {
        $valobject->value = $this->parseval($valobject->value, $formfield);
    }

    function post_contentedit($formtype, $formfield, $value, $node, $content) {
        return $this->parseval($value, $formfield);
    }

    function db_get($values, $formfield) {
        return $this->parseval(first($values), $formfield);
    }

    function db_set($values, $formfield) {
        return array($this->parseval($values, $formfield));
    }
}
?>