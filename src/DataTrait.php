<?php
namespace SimplePHP\View;

trait DataTrait {    
    protected $_data = [];

    function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    function __get($name)
    {
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        }
        return null;        
    }

    function setData($data, $append = false) {
        if ($append) {
            $this->_data += $data;
        } else {
            $this->_data = $data;
        }
    }

    function resetData() {        
        $this->_data = [];
    }

    #region ArrayAccess interface implementation

    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
    }

    #endregion
}