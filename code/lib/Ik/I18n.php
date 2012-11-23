<?php
class Ik_I18n
{
/******************************************************************************
 * STATIC
 ******************************************************************************/
    
    /**
     * Return translated value from array by lang key
     *
     * @param array $titleSet
     * @param string $langs
     * @return string title
     */
    static public function getI18nValue($data, $langs = null)
    {
        if (empty($data)) {
            return '';
        } elseif (!is_array($data)) {
            return $data;
        }
        
        if ($langs === null) {
            $langs = Zend_Registry::get('clientLangs');
        }
        $langs = (array) $langs;
    
        if (is_string(key($data))) {
            foreach ($langs as $lang) {
                if (isset($data[$lang])) {
                    return $data[$lang];
                }
            }
            
            return current($data);
        } else {
            foreach ($langs as $lang) {
                foreach ($data as $key => $value) {
                    if (isset($value['l'])
                            && $value['l'] == $lang)
                    {
                        return $value['v'];
                    }
                }
            }
            
            $value = reset($data);
            if (isset($value['v'])) {
                return $value['v'];
            }
        }
    
        return '';
    }    
    
/******************************************************************************
 * END.
 ******************************************************************************/
}