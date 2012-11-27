<?php
/**
 * Translate a message
 * You can give multiple params or an array of params.
 * Example 1: translate('%1\$s + %2\$s', $value1, $value2);
 * Example 2: translate('%1\$s + %2\$s', array($value1, $value2));
 *
 * @param  string $message Id of the message to be translated
 * @return string Translated message
 */
function _t($message)
{
    $translate = Zend_Registry::get('Zend_Translate');
    $options   = func_get_args();

    array_shift($options);

    if ($translate !== null) {
        $message = $translate->translate($message);
    }

    if (count($options) === 0) {
        return $message;
    }

    return vsprintf($message, $options);
}
/**
 * Merges any number of arrays of any dimensions, the later overwriting
 * previous keys, unless the key is numeric, in whitch case, duplicated
 * values will not be added.
 *
 * The arrays to be merged are passed as arguments to the function.
 *
 * @access public
 * @return array Resulting array, once all have been merged
 */
function array_merge_replace_recursive()
{
    // Holds all the arrays passed
    $params = func_get_args();
    //var_dump($params);


    // First array is used as the base, everything else overwrites on it
    $return = array_shift($params);

    // Merge all arrays on the first array
    foreach ($params as $array) {
        foreach ($array as $key => $value) {
            // Numeric keyed values are added (unless already there)
            if (is_numeric($key) && (!in_array($value, $return))) {
                if (is_array($value)) {
                    $return[] = $this->array_merge_replace_recursive($return[$$key], $value);
                } else {
                    $return[] = $value;
                }

            // String keyed values are replaced
            } else {
                if (isset($return[$key]) && is_array($value) && is_array($return[$key])) {
                    $return[$key] = array_merge_replace_recursive($return[$$key], $value);
                } else {
                    $return[$key] = $value;
                }
            }
        }
    }

    return $return;
}

function add_include_path($path)
{
    set_include_path(implode(PATH_SEPARATOR, array(
        $path,
        get_include_path(),
    )));
}

/**
 * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
 * keys to arrays rather than overwriting the value in the first array with the duplicate
 * value in the second array, as array_merge does. I.e., with array_merge_recursive,
 * this happens (documented behavior):
 *
 * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
 *     => array('key' => array('org value', 'new value'));
 *
 * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
 * Matching keys' values in the second array overwrite those in the first array, as is the
 * case with array_merge, i.e.:
 *
 * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
 *     => array('key' => array('new value'));
 *
 * Parameters are passed by reference, though only for performance reasons. They're not
 * altered by this function.
 *
 * @param array $array1
 * @param array $array2
 * @return array
 * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
 * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
 */
function array_merge_recursive_distinct(array $array1, array $array2)
{
    $merged = $array1;

    foreach ($array2 as $key => &$value) {
        if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
            $merged[$key] = @array_merge_recursive_distinct($merged[$key], $value);
        } else {
            $merged[$key] = $value;
        }
    }

    return $merged;
}

function get_pagegen($start, $finish)
{
    $total_time = $finish - $start;
    return 'Page generated in ' . number_format($total_time, 3) . ' sec.';
}

function is_valid_url($url)
{
    if (preg_match("/^(http|https|ftp)://([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?/?/i", $url)) {
        return true;
    } else {
        return false;
    }
}

function getRealIPAddr()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function truncate($text, $limit = 25, $ending = '...')
{
    $text = strip_tags($text);
    if (strlen($text) > $limit) {
        $text = substr($text, 0, $limit);
        $text = substr($text, 0, -(strlen(strrchr($text, ' '))));
        $text = $text . $ending;
    }
    return $text;
}

function send_simple_mail($from, $to, $subject, $body)
{
    $headers = "From: $from\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "Return-Path: $from\r\n";
    $headers .= "X-Mailer: PHP5\n";
    $headers .= 'MIME-Version: 1.0' . "\n";
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
    @mail($to, $subject, $body, $headers);
}

function get_title_value()
{
    $fp = fopen("http://www.example.com", "r");
    $page = '';
    while (!feof($fp)) {
        $page .= fgets($fp, 4096);
    }
    $titre = eregi("<title>(.*)</title>", $page, $regs);
    echo $regs[1];
    fclose($fp);
}

function br2nl($text)
{
    return preg_replace('/<br\\s*?\/??>/i', '', $text);
}

function mailzf($to_mail, $subject, $message, $from_mail = null, $from_name = null, $to_name = null)
{
    $mail = new Zend_Mail('windows-1251');
    $mail->addTo($to_mail, $to_name);
    $mail->setSubject($subject);
    $mail->setBodyHtml($message);
    $mail->setBodyText(strip_tags(br2nl($message)));
    $mail->setFrom($from_mail, $from_name);
    $mail->send();
}

function base64url_encode($plainText) {
    $base64 = base64_encode($plainText);
    $base64url = strtr($base64, '+/=', '-_,');
    return $base64url;
}

function base64url_decode($plainText) {
    $base64url = strtr($plainText, '-_,', '+/=');
    $base64 = base64_decode($base64url);
    return $base64;
}

function encode_email($email='info@domain.com', $linkText='Contact Us', $attrs ='class="emailencoder"' )
{
    // remplazar aroba y puntos
    $email = str_replace('@', '&#64;', $email);
    $email = str_replace('.', '&#46;', $email);
    $email = str_split($email, 5);

    $linkText = str_replace('@', '&#64;', $linkText);
    $linkText = str_replace('.', '&#46;', $linkText);
    $linkText = str_split($linkText, 5);

    $part1 = '<a href="ma';
    $part2 = 'ilto&#58;';
    $part3 = '" '. $attrs .' >';
    $part4 = '</a>';

    $encoded = '<script type="text/javascript">';
    $encoded .= "document.write('$part1');";
    $encoded .= "document.write('$part2');";
    foreach($email as $e)
    {
            $encoded .= "document.write('$e');";
    }
    $encoded .= "document.write('$part3');";
    foreach($linkText as $l)
    {
            $encoded .= "document.write('$l');";
    }
    $encoded .= "document.write('$part4');";
    $encoded .= '</script>';

    return $encoded;
}

function is_valid_email($email, $test_mx = false)
{
    if(eregi("^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $email))
        if($test_mx)
        {
            list($username, $domain) = split("@", $email);
            return getmxrr($domain, $mxrecords);
        }
        else
            return true;
    else
        return false;
}

