<?php defined('BILLINGMASTER') or die; 

foreach ($widget_params as $param) {
    if ($param['code'] && preg_match('#\[CUSTOM_FIELD_([0-9]+)\]#', $param['code'])) {
        $param['code'] = CustomFields::replaceContent($param['code']);
    }

    if (isset($param['code_html']) && $param['code_html']) {
        if (preg_match('#\[CUSTOM_FIELD_([0-9]+)\]#', $param['code_html'])) {
            $param['code_html'] = CustomFields::replaceContent($param['code_html']);
        }

        echo $param['code_html'];
    }

    if (strpos($param['code'], '<?') !== false) {
        System::parsePHPviaFile($param['code']);
    } else {
        echo $param['code'];
    }
}?>