<?php
/*
 * INTERKASSA Ltd. (c)
 * 
 * $Id: Exception.php 158 2010-09-04 16:49:32Z denis $
 * $HeadURL: http://www.interkassa.com/svn/interkassa1/trunk/lib/Ik/Exception.php $
 * $LastChangedBy: denis $
 * $LastChangedDate: 2010-09-04 19:49:32 +0300 (Сб, 04 сен 2010) $
 * $LastChangedRevision: 158 $
 */

class Ik_Exception extends Exception
{
/******************************************************************************
 * MAIN
 ******************************************************************************/    
    
    /**
     * Inner Exception
     * @var Exception
     */
    protected $_innerException = null;
    
    public function __construct($message = '', $code = 0, $innerException = null) 
    {
        if ((null !== $innerException) && ($innerException instanceof Exception)) {
            $this->_innerException = $innerException;
        }
        
        parent::__construct($message, $code);
    }
    
    function __toString()
    {
        $str = '';
        $str .= 'Exception: ' . get_class($this) . PHP_EOL;
        $str .= 'Message: ' . (($this->getMessage()) ? ($this->getMessage()) : ('-'));
        $str .= (($this->code !== null) ? (' (Code: ' . $this->code . ')') : ('')) . PHP_EOL;
        $str .= 'File: ' . $this->getFile() . PHP_EOL;
        $str .= 'Line: ' . $this->getLine() . PHP_EOL;
        if ($this->_innerException !== null) {
            $str .= '==> Inner Exception:' . PHP_EOL;
            $innerExStrs = explode(PHP_EOL, (string) $this->_innerException);
            foreach ($innerExStrs as $key => $value) {
                $innerExStrs[$key] = '    ' . $value;
            }
            $str .= implode(PHP_EOL, $innerExStrs) . PHP_EOL;
        }
        return $str;
    }
    
    public function getInnerException()
    {
        return $this->_innerException;
    }
        
/******************************************************************************
 * END.
 ******************************************************************************/    
}