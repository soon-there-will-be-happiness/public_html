<?php


class SegmentFilterFormElements {
    private $elements;
    private $html;
    private $depend_name;
    private $depend_value;
    private $depend_blocks;
    private $groups_blocks;


    /**
     * SegmentFilterFormElements constructor.
     */
    public function __construct() {
        $this->elements = [];
        $this->html = '';
        $this->depend_name = $this->depend_value = '';
        $this->depend_blocks = [];
        $this->groups_blocks = [];
    }


    /**
     * @param $name
     * @param string $value
     * @param string $label
     * @param string $placeholder
     * @param null $classes
     * @param string $attributes
     */
    public function addTextInput($name, $value = '', $label = '', $placeholder = '', $classes = null, $attributes = '') {
        $element = new SegmentFilterFormElement;
        $element->setTextInput($name, $value, $label, $placeholder, $attributes);
        if ($classes) {
            $element->addHtmlClasses($classes);
        }

        $this->elements[$name] = $element;
    }


    /**
     * @param $name
     * @param string $value
     * @param string $label
     * @param string $placeholder
     * @param string $attributes
     */
    public function addDateInput($name, $value = '', $label = '', $placeholder = '', $attributes = '') {
        $element = new SegmentFilterFormElement;
        $element->setDateInput($name, $value, $label, $placeholder, 'd.m.Y H:i', $attributes);

        $this->elements[$name] = $element;
    }


    /**
     * @param $name
     * @param $value
     * @param $label
     * @param $items
     * @param string $attributes
     */
    public function addRadio($name, $value, $label, $items, $attributes = '') {
        $element = new SegmentFilterFormElement;
        $element->setRadio($name, $value, $label, $items, $attributes);

        $this->elements[$name] = $element;
    }


    /**
     * @param $name
     * @param $value
     * @param $label
     * @param $items
     * @param null $classes
     * @param string $attributes
     */
    public function addSelect($name, $value, $label, $items, $classes = null, $attributes = '') {
        $element = new SegmentFilterFormElement;
        $element->setSelect($name, $value, $label, $items, $attributes);
        if ($classes) {
            $element->addHtmlClasses($classes);
        }

        $this->elements[$name] = $element;
    }


    /**
     * @param $name
     * @param $value
     * @param $label
     * @param $items
     * @param string $attributes
     */
    public function addMultiSelect($name, $value, $label, $items, $attributes = '') {
        $element = new SegmentFilterFormElement;
        $element->setMultiSelect($name, $value, $label, $items, $attributes);

        $this->elements[$name] = $element;
    }

    /**
     * @param $name
     * @param $value
     * @param $depend_els
     */
    public function addDependency($name, $value, $depend_els) {
        foreach ($depend_els as $el) {
            if (!in_array($this->getElement($el)->getId(), $this->depend_blocks)) {
                $this->depend_blocks[] = $this->getElement($el)->getId();
                $this->getElement($el)->addDependencyFrom();
            }

            $el_id = $this->getElement($el)->getId();
            $this->getElement($name)->addDependencyFor($value, $el_id);
        }
    }


    /**
     * @param $group_els
     * @param $group_name
     */
    public function addGroups($group_els, $group_name) {
        foreach ($group_els as $el) {
            if (!in_array($this->getElement($el)->getId(), $this->groups_blocks)) {
                $this->groups_blocks[$group_name][] = $this->getElement($el)->getId();
                $this->getElement($el)->setGroup($group_name);
            }
        }
    }


    /**
     * @param $name
     * @return SegmentFilterFormElement
     */
    public function getElement($name) {
        return $this->elements[$name];
    }


    public function setAdditionalInfo($text) {
        /** @var $element SegmentFilterFormElement */
        foreach ($this->elements as $element) {
            $element->setAdditionalInfo($text);
        }
    }


    /**
     * @param $attribute
     * @param $value
     */
    public function setAttributes($attribute, $value) {
        if ($attribute) {
            /** @var $element SegmentFilterFormElement */
            foreach ($this->elements as $element) {
                $element->setAttributes("$attribute=\"$value\"");
            }
        }
    }


    /**
     * @return string
     */
    public function getHtml() {
        $depended_els_html = '';
        $groups_els_html = [];

        /** @var $element SegmentFilterFormElement */
        foreach ($this->elements as $element) {
            if ($element->isDepended()) {
                $depended_els_html .= $element->getHtml();
            } elseif($element->getGroup()) {
                if (!isset($groups_els_html[$element->getGroup()])) {
                    $groups_els_html[$element->getGroup()] = $element->getHtml();
                } else {
                    $groups_els_html[$element->getGroup()] .= $element->getHtml();
                }
            } else {
                $this->html .= $element->getHtml();
            }
        }

        if ($depended_els_html) {
            $this->html .= "<div class=\"depended-elements\">$depended_els_html</div>";
        }

        if ($groups_els_html) {
            foreach ($groups_els_html as $group_name => $group_els_html) {
                $this->html .= "<div class=\"groups-elements $group_name\">$group_els_html</div>";
            }
        }

        return $this->html;
    }
}