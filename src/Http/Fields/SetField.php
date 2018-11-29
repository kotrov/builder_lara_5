<?php

namespace Vis\Builder\Fields;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;

/**
 * Class SetField.
 */
class SetField extends AbstractField
{
    /**
     * @return bool
     */
    public function isEditable()
    {
        return true;
    }

    // end isEditable

    /**
     * @param $db
     * @param $value
     */
    public function onSearchFilter(&$db, $value)
    {
        $table = $this->definition['db']['table'];
        $db->where($table.'.'.$this->getFieldName(), 'LIKE', '%'.$value.'%');
    }

    // end onSearchFilter

    /**
     * @return string
     * @throws \Throwable
     */
    public function getFilterInput()
    {
        if (! $this->getAttribute('filter')) {
            return '';
        }

        $definitionName = $this->getOption('def_name');
        $sessionPath = 'table_builder.'.$definitionName.'.filters.'.$this->getFieldName();
        $filter = Session::get($sessionPath, '');

        $table = view('admin::tb.filter_set');
        $table->filter = $filter;
        $table->name = $this->getFieldName();
        $table->options = $this->getAttribute('options');

        return $table->render();
    }

    /**
     * @param array $row
     * @return string
     * @throws \Throwable
     */
    public function getEditInput($row = [])
    {
        if ($this->hasCustomHandlerMethod('onGetEditInput')) {
            $res = $this->handler->onGetEditInput($this, $row);
            if ($res) {
                return $res;
            }
        }

        $table = view('admin::tb.input_set');

        $table->selected = $this->getAttribute('json_use') ?
                            json_decode($this->getValue($row)) :
                            explode(',', $this->getValue($row));

        if (! is_array($table->selected)) {
            $table->selected = [];
        }

        $table->name = $this->getFieldName();
        $table->options = $this->getAttribute('options');

        return $table->render();
    }

    /**
     * @param $row
     * @return string
     */
    public function getRowColor($row)
    {
        $colors = $this->getAttribute('colors');
        if ($colors) {
            return isset($colors[$this->getValue($row)]) ? $colors[$this->getValue($row)] : '';
        }
    }

    /**
     * @param $row
     * @param string $postfix
     * @return bool|string
     */
    public function getValue($row, $postfix = '')
    {
        if ($this->hasCustomHandlerMethod('onGetValue')) {
            $res = $this->handler->onGetValue($this, $row, $postfix);
            if ($res) {
                return $res;
            }
        }

        $fieldName = $this->getFieldName().$postfix;
        // postfix used for getting values for form - tabs loop
        // so there is no need to force appending postfix
        if ($this->getAttribute('tabs') && ! $postfix) {
            $tabs = $this->getAttribute('tabs');
            $fieldName = $fieldName.$tabs[0]['postfix'];
        }

        return isset($row[$fieldName]) ? $row[$fieldName] : '';
    }

    /**
     * @param $value
     * @return string|void
     */
    public function prepareQueryValue($value)
    {
        if (! $value && $this->getAttribute('is_null')) {
            return;
        }

        if ($this->getAttribute('json_use')) {
            return json_encode($value);
        }

        return implode(',', $value);
    }

    /**
     * @param $row
     * @return bool|string
     */
    public function getListValue($row)
    {
        if ($this->hasCustomHandlerMethod('onGetListValue')) {
            $res = $this->handler->onGetListValue($this, $row);
            if ($res) {
                return $res;
            }
        }

        $values = $this->getAttribute('json_use') ?
                    json_decode($this->getValue($row)) :
                     array_filter(explode(',', $this->getValue($row)));

        $options = $this->getAttribute('options');

        if (! is_array($values)) {
            return;
        }

        $prepared = [];
        foreach ($values as $value) {
            $prepared[] = $options[$value];
        }

        return implode(', ', $prepared);
    }

    public function getListValueFastEdit($row, $ident)
    {
        $options = $this->getAttribute('options');
        $selected = $this->getAttribute('json_use') ?
            json_decode($this->getValue($row)) :
            explode(',', $this->getValue($row));
        $isJson = $this->getAttribute('json_use');

        return view('admin::tb.fast_edit_set', compact('row', 'ident', 'options', 'selected', 'isJson'));
    }
}
