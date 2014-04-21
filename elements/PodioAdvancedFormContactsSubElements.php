<?php

/**
 * Podio Advanced Form - A form generator for Podio
 *
 * @author      Carl-Fredrik Herö <carlfredrik.hero@gmail.com>
 * @copyright   2014 Carl-Fredrik Herö
 * @link        https://github.com/carlfredrikhero/podio-advanced-form
 * @license     https://github.com/carlfredrikhero/podio-advanced-form
 * @version     1.0.0
 * @package     PodioAdvancedForm
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/**
 * Element for Sub Contact Field
 * @package PodioAdvancedForm
 * @author  Carl-Fredrik Herö
 * @since   1.0.0
 */
abstract class PodioAdvancedFormContactsSubElement{
    /**
     * @var PodioAdvancedFormContactElement 
     */
    protected $parent;
	
    /**
     * Constructor
     * @param array $attributes
     */
    public function __construct(array $attributes) {
        $this->set_name($attributes['name']);
        unset($attributes['name']);

        $this->set_parent($attributes['parent']);
        unset($attributes['parent']);

        foreach($attributes AS $key => $attribute){
                $this->set_attribute($key, $attribute);
        }

        if ($parent_class = $this->parent->get_attribute('class')){
                $this->set_attribute('class', $parent_class);
        }

        // special handling of required attribute
        // if parent field is not required, set required to false
        if ($this->get_parent()->get_attribute('required') === false){
                $this->set_attribute('required', false);
        }
    }
	
    /**
     * Get Parent
     * @return PodioAdvancedFormContactElement
     */
    protected function get_parent(){
        return $this->parent;
    }

    /**
     * Set Parent
     * @param PodioAdvancedFormContactElement $parent
     */
    protected function set_parent(PodioAdvancedFormContactElement $parent){
        $this->parent = $parent;
    }

    /**
     * Get Name
     * @return string
     */
    public function get_name(){
        $parent = $this->get_parent();
        $name = $parent->get_name();
        $name = $name . '[' . $this->name . ']';

        if ($this->get_attribute('multi')){
                $name .= '[]';
        }

        return $name;
    }

    /**
     * Set Name
     * @param string $name
     */
    public function set_name($name){
        $this->name = (string) $name;
    }

    /**
     * Get Attribute
     * @param string $key
     * @return mixed
     */
    public function get_attribute($key){
        // backward compability
        if ('name' == $key){
            return $this->get_name();
        }

        if (!array_key_exists($key, $this->attributes)){
            return null;
        }

        return $this->attributes[$key];
    }

    /**
     * Set Attribute
     * @param type $key
     * @param type $value
     */
    public function set_attribute($key, $value){
        $key = (string) $key;
        $this->attributes[$key] = $value;
    }

    /**
     * Get attributes
     * @return array
     */
    public function get_attributes(){
        $attributes = $this->attributes;
        $attributes['name'] = $this->get_name();
        return $attributes;
    }

    /**
     * Renders element in locked mode
     * @return string
     */
    public function render_locked(){
        $element = "";

        $values = $this->get_parent()->get_item_field()->values;

        // don't use $this->get_name() AS it will return the parent
        // name with subelement as array key (ex. contact[name])
        $name = $this->name;

        if ($values){
            // force array for all value, not just email and phone
            $value = (array) $values[0]['value'][$name];
            foreach($value AS $v){
                $element .= '<div class="locked">';
                $element .= $v;
                $element .= '</div>';
            }
        }

        $decorator_class = array();
        $decorator = sprintf($this->parent->get_decorator('field'), 
                        $this->get_attribute('name'),
                        $this->get_attribute('placeholder'),
                        $element,
                        '', // description is always empty in these fields
                        ($this->get_attribute('required')) ? $this->parent->get_decorator('field_required') : '',
                        implode(' ', $decorator_class)
                    );

        return $decorator;
    }

    public function render($element = null, $default_field_decorator = 'field'){
        // output is:
        // decorator
        // element

        $values = $this->get_parent()->get_attribute('value');

        if ($this->get_parent()->is_locked()){
            if (!$values){
                return '';
            }
            $element = $this->render_locked();
        } else {

            $attributes = $this->get_attributes();

            $attributes_string = '';
            foreach($attributes AS $key => $attribute){
                // if true, then attribute minimization is allowed
                if ($attribute === true){
                    $attributes_string .= ' ' . $key;
                } elseif ($attribute){ // all falsy values won't be added
                    $attributes_string .= ' ' . $key . '="' . (string) $attribute . '"';
                }
            }

            $element = '<input';
            $element .= $attributes_string;
            $element .= '>';
        }

        if ($this->parent->get_form()->is_sub_form()){
            $decorator_format = 'sub_sub_field';
        } else {
            $decorator_format = 'sub_field';
        }

        $decorator_class = array();
        // TODO implement error handling
//                if ($this->error){
//                    $decorator_class[] = 'error';
//                }

        $decorator = sprintf($this->parent->get_decorator($decorator_format), 
                            $this->get_attribute('name'),
                            $this->get_attribute('placeholder'),
                            $element,
                            '', // description is always empty in these fields
                            ($this->get_attribute('required')) ? $this->get_decorator('field_required') : '',
                            implode(' ', $decorator_class)
                    );

        return $decorator;
    }
}

/**
 * Text Element for Sub Contact Field
 * @package PodioAdvancedForm
 * @author  Carl-Fredrik Herö
 * @since   1.0.0
 */
class PodioAdvancedFormContactsSubElementText extends PodioAdvancedFormContactsSubElement{
    /**
     * @var array 
     */
    protected $attributes = array(
        'type' => 'text',
    );
}

/**
 * Email Element for Sub Contact Field
 * @package PodioAdvancedForm
 * @author  Carl-Fredrik Herö
 * @since   1.0.0
 */
class PodioAdvancedFormContactsSubElementEmail extends PodioAdvancedFormContactsSubElementText{
    /**
     * @var array 
     */
    protected $attributes = array(
        'type' => 'email',
        'multi' => true,
    );
}