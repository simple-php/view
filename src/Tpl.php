<?php
namespace SimplePHP\View;

use ArrayAccess;
use Exception;

class Tpl implements ArrayAccess {
    use DataTrait { 
        __get as private _dataTrait_get; 
        offsetExists as private _dataTrait_offsetExists;
    }
    protected $_tplPath = null;
    protected $_tplName = false;
    protected $_tplExt = '.phtml';
    protected $_layoutPath = null;
    protected $_layout = false;
    protected $_content = '';

    protected static $_global = [];
    protected static $_defaultTplPath = [];

    function __construct($tpl = null, $tplPath = null)
    {        
        $this->_tplPath = $tplPath;
        $this->_tplName = $tpl;
    }

    #region overloaded DataTrait methods

    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]) || isset(self::$_global[$offset]);
    }

    function __get($name)
    {
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        } else if (isset(self::$_global[$name])) {
            return self::$_global[$name];
        }
        return null;        
    }

    #endregion

    function setGlobal($name, $value) {
        self::$_global[$name] = $value;
    }

    function getTemplatePath() {
        return $this->_templatePath;
    }

    function getLayout() {
        return $this->_layout;
    }

    function setLayout($tpl) {
        $this->_layout = $tpl;
    }

    function getContent() {
        return $this->_content;
    }

    protected function _renderFile($filename) {
        if (isset(self::$_global['this']) || isset($this->_data['this'])) {
            throw new Exception('Dont use this as key name');
        }
        if (!empty(self::$_global)) extract(self::$_global, EXTR_OVERWRITE);
        if (!empty($this->_data)) extract($this->_data, EXTR_OVERWRITE);
        ob_start();
        include $filename;
        return ob_get_clean();
    }

    function render($tpl = null) {
        $content = false;
        if (!isset($tpl)) $tpl = $this->_tplName;
        
        $tplPath = $this->_tplPath;
        if (!isset($tplPath)) $tplPath = self::$_defaultTplPath;
        if (!isset($tplPath)) throw new Exception('No template path specified');
        if (!is_array($tplPath)) $tplPath = [$tplPath];

        if (!isset($tpl) || $tpl === false) throw new Exception('No template name specified');

        foreach ($tplPath as $path) {            
            $filename = $path . DIRECTORY_SEPARATOR . $tpl . $this->_tplExt;
            if (file_exists($filename)) {
                $content = $this->_renderFile($filename);
                if (isset($this->_layout) && $this->_layout !== false) {
                    $layoutPath = $this->_layoutPath;
                    if (!isset($layoutPath) || $layoutPath === false || empty($layoutPath)) {
                        $layoutPath = $tplPath;
                    }
                    $layoutTpl = new self($this->_layout, $layoutPath);
                    $layoutTpl->setData($this->_data);
                    $layoutTpl->_content = $content;
                    $content = $layoutTpl->render();
                    if ($content === false) {
                        throw new Exception('Layout '.$this->_layout.' not found');
                    }
                }
            }
            
        }
        return $content;
    }

    function __toString()
    {
        return $this->render();        
    }
}