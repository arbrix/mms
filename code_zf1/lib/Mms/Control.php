<?php
class Mms_Control
{
    public static function getClass($type) 
	{
        $type = Zend_Filter::filterStatic($type, 'Word_CamelCaseToUnderscore');
        $type = Zend_Filter::filterStatic($type, 'Word_SeparatorToCamelCase', array('-'));

        $class = 'Mms_Control_' . ucfirst($type);
        if (!@class_exists($class)) {
            throw new Mms_Exception('Control does not exist - ' . $type);
        }
	    return $class;
	}

	public static function factory($type, $options = array())
    {
        $class = self::getClass($type);
        $instance = new $class((array) $options);
        if ($instance instanceof Mms_Control_Abstract) {
            return $instance;
        } else {
            throw new Mms_Exception('Invalid control name - ' . $type);
        }
    }
}