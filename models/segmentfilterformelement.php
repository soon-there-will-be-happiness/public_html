<?php defined('BILLINGMASTER') or die;

class SegmentFilterFormElement {

    private $type;
    public $name;
    private $value;
    private $label;
    private $placeholder;
    private $items = [];
    private $format;
    private $is_depended;
    private $group_name;
    private $id;
    private $depend_elements;
    private $html_classes;
    private $additional_info;
    private $attributes;


    /**
     * SegmentFilterFormElement constructor.
     */
    public function __construct() {
        $this->type = '';
        $this->name = '';
        $this->value = '';
        $this->label = '';
        $this->placeholder = '';
        $this->items = '';
        $this->format = 'd.m.Y H:i';
        $this->is_depended = false;
        $this->group_name = null;
        $this->depend_elements = [];
        $this->html_classes ='';
        $this->additional_info ='';
        $this->attributes = '';
    }


    /**
     * @return bool
     */
    public function hasDependElements() {
        return !empty($this->depend_elements) ? true : false;
    }


    /**
     * @param $attributes
     */
    public function setAttributes($attributes) {
        $this->attributes = $attributes;
    }


    /**
     * @param $name
     * @param string $value
     * @param string $label
     * @param string $placeholder
     * @param string $attributes
     */
    public function setTextInput($name, $value = '', $label = '', $placeholder = '', $attributes = '') {
        $this->type = 'text_input';
        $this->name = $name;
        $this->value = $value;
        $this->label = $label;
        $this->placeholder = $placeholder;
        $this->attributes = $attributes;
        $this->setId();
    }


    /**
     * @param $name
     * @param string $value
     * @param string $label
     * @param string $placeholder
     * @param string $format
     * @param string $attributes
     */
    public function setDateInput($name, $value = '', $label = '', $placeholder = '', $format = 'd.m.Y H:i', $attributes = '') {
        $this->type = 'date_input';
        $this->name = $name;
        $this->value = $value;
        $this->label = $label;
        $this->placeholder = $placeholder;
        $this->format = $format;
        $this->attributes = $attributes;
        $this->setId();
    }


    /**
     * @param $name
     * @param string $value
     * @param string $label
     * @param $items
     * @param string $attributes
     */
    public function setRadio($name, $value = '', $label = '', $items, $attributes = '') {
        $this->type = 'radio';
        $this->name = $name;
        $this->value = $value;
        $this->label = $label;
        $this->items = $items;
        $this->attributes = $attributes;
        $this->setId();
    }


    /**
     * @param $name
     * @param string $value
     * @param string $label
     * @param $items
     * @param string $attributes
     */
    public function setSelect($name, $value = '', $label = '', $items, $attributes = '') {
        $this->type = 'select';
        $this->name = $name;
        $this->value = $value;
        $this->label = $label;
        $this->items = $items;
        $this->attributes = $attributes;
        $this->setId();
    }


    /**
     * @param $name
     * @param string $value
     * @param string $label
     * @param $items
     * @param string $attributes
     */
    public function setMultiSelect($name, $value = '', $label = '', $items, $attributes = '') {
        $this->type = 'multi_select';
        $this->name = $name;
        $this->value = $value;
        $this->label = $label;
        $this->items = $items;
        $this->attributes = $attributes;
        $this->setId();
    }


    public function addDependencyFrom() {
        $this->is_depended = true;
        $this->html_classes .= ' hidden depend-block mt-10';
    }


    /**
     * @return bool
     */
    public function isDepended() {
        return $this->is_depended;
    }


    /**
     * @return null
     */
    public function getGroup() {
        return $this->group_name;
    }


    /**
     * @param $val
     * @param $dep_el_id
     */
    public function addDependencyFor($val, $dep_el_id) {
        $this->depend_elements[$val][] = $dep_el_id;
    }


    /**
     * @param $group_name
     */
    public function setGroup($group_name) {
        $this->group_name = $group_name;
    }


    private function setId() {
        $this->id = md5("filter-element-{$this->name}".microtime());
    }


    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }


    /**
     * @return string
     */
    private function getHtmlClasses() {
        return $this->html_classes;
    }


    /**
     * @param $classes
     */
    public function addHtmlClasses($classes) {
        $this->html_classes .= " $classes";
    }


    /**
     * @param $text
     */
    public function setAdditionalInfo($text) {
        $this->additional_info = $text;
    }


    /**
     * @return string
     */
    private function getTextInputHtml() {
        $label_html = $this->label ? "<label>$this->label:</label>" : '';

        $html = <<<EOT
<div id="{$this->id}" class="{$this->getHtmlClasses()}">$label_html
    <input type="text" name="$this->name" placeholder="$this->placeholder" value="$this->value" $this->attributes>
</div>
EOT;
        return $html;
    }


    /**
     * @return string
     */
    private function getDateInputHtml() {
        $label_html = $this->label ? "<label>$this->label:</label>" : '';
        $html = <<<EOT
<div id="{$this->id}" class="datetimepicker-wrap{$this->getHtmlClasses()}">$label_html
    <input type="text" class="datetimepicker" name="$this->name" placeholder="$this->placeholder" value="$this->value" autocomplete="off" data-format="$this->format" $this->attributes>
</div>
EOT;
        return $html;
    }


    private function getTextAreaHtml() {

    }


    /**
     * @return string
     */
    private function getSelectHtml() {
        $label_html = $this->label ? "<label>$this->label:</label>" : '';
        $options_html = !$this->items ? '<option value="">Данных пока нет</option>' : '';
        $dependent_blocks = '';

        if ($this->items) {
            foreach ($this->items as $item) {
                $attr = '';
                if ($this->depend_elements && isset($this->depend_elements[$item['value']])) {
                    $attr = ' data-show_on="'.implode(',',$this->depend_elements[$item['value']]).'"';
                }

                $selected = $this->value !== null && $item['value'] == $this->value ? ' selected="selected"' : '';
                $options_html .= "<option value=\"{$item['value']}\"{$selected}{$attr}>{$item['title']}</option>";
            }
        }

        $additional_info_html = "<div class=\"additional-info".(!$this->additional_info ? ' hidden' : '')."\">{$this->additional_info}</div>";
        $html = <<<EOT
<div class="condition-top{$this->getHtmlClasses()}">
    <div id="{$this->id}" class="select-wrap">
        <select name="$this->name" $this->attributes>
            $options_html
        </select>
    </div>
    $additional_info_html
</div>
$dependent_blocks
EOT;
        return $html;
    }


    private function getMultiSelectHtml() {
        $label_html = $this->label ? "<label>$this->label:</label>" : '';
        $options_html = !$this->items ? '<option value="">Данных пока нет</option>' : '';
        $dependent_blocks = '';

        if ($this->items) {
            foreach ($this->items as $item) {
                $attr = '';
                if ($this->depend_elements && isset($this->depend_elements[$item['value']])) {
                    $attr = ' data-show_on="'.implode(',',$this->depend_elements[$item['value']]).'"';
                }

                $selected = $this->value && in_array($item['value'], $this->value) ? ' selected="selected"' : '';
                $options_html .= "<option value=\"{$item['value']}\"{$selected}{$attr}>{$item['title']}</option>";
            }
        }

        $additional_info_html = "<div class=\"additional-info".(!$this->additional_info ? ' hidden' : '')."\">{$this->additional_info}</div>";
        $html = <<<EOT
<div class="condition-top">
    <div id="{$this->id}" class="select-wrap{$this->getHtmlClasses()}">
        <select name="{$this->name}[]" multiple="multiple" $this->attributes>
            $options_html
        </select>
    </div>
    $additional_info_html
</div>
$dependent_blocks
EOT;
        return $html;
    }


    /**
     * @return string
     */
    private function getRadioHtml() {
        $label_html = $this->label ? "<label>$this->label:</label>" : '';
        $items_html = '';
        if ($this->items) {
            foreach ($this->items as $key => $item) {
                $checked = ($this->value === null && $key == 0) || ($this->value !== null && $item['value'] == $this->value) ? ' checked' : '';
                $items_html .= <<<EOT
<label class="custom-radio">
    <input name="$this->name" type="radio" value="{$item['value']}"$checked $this->attributes>
    <span>{$item['title']}</span>
</label>
EOT;
            }
        } else {
            $items_html = "<input name=\"$this->name\" type=\"hidden\" value=\"\">";
        }
        $html = <<<EOT
<div class="condition-top"> 
    <div id="{$this->id}" class="{$this->getHtmlClasses()}">
        $label_html
        <span class="custom-radio-wrap">
            $items_html
        </span>
    </div>
</div>
EOT;
        return $html;
    }


    public function setDependency($element_name, $element_value) {

    }


    /**
     * @return string
     */
    public function getHtml() {
        $html = '';

        switch ($this->type) {
            case 'text_input':
                $html = $this->getTextInputHtml();
                break;
            case 'date_input':
                $html = $this->getDateInputHtml();
                break;
            case 'radio':
                $html = $this->getRadioHtml();
                break;
            case 'select':
                $html = $this->getSelectHtml();
                break;
            case 'multi_select':
                $html = $this->getMultiSelectHtml();
                break;
        }

        return $html;
    }
}
