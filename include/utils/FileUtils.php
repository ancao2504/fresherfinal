<?php

/*
    FileUtils
    Author: Hieu Nguyen
    Date: 2018-08-03
    Purpose: to provide util functions to work with files
*/

class FileUtils {

    public static function writeArrayToFile($array, $file, $message = '') {
        global $current_user;
        $timestamp = date('Y-m-d h:i:s a');
        $content = "<?php\n\n/*\n\tSystem auto-generated on {$timestamp} by {$current_user->user_name}. {$message}\n*/\n\n";
        
        foreach ($array as $arrName => $arrData) {
            $content .= '$'. $arrName .' = ';
            $content .= self::arrayToString($arrData);
            $content .= ";\n\n";
        }

        try {
            if (!file_exists($file)) {
                mkdir(dirname($file), 0777, true);
                file_put_contents($file, '');
            }

            @chmod($file, 0777);
            file_put_contents($file, $content);
            return true;
        }
        catch (Exception $ex) {
            return false;
        }
    }

    public static function writeReturnArrayToFile($array, $file, $message = '') {
        global $current_user;
        $timestamp = date('Y-m-d h:i:s a');
        $content = "<?php\n\n/*\n\tSystem auto-generated on {$timestamp} by {$current_user->user_name}. {$message}\n*/\n\n";
        
        $content .= 'return ';
        $content .= self::arrayToString($array);
        $content .= ";";

        try {
            if (!file_exists($file)) {
                mkdir(dirname($file), 0777, true);
                file_put_contents($file, '');
            }

            @chmod($file, 0777);
            file_put_contents($file, $content);
            return true;
        }
        catch (Exception $ex) {
            return false;
        }
    }

    private static function arrayToString($var, $opts = array()) {
        $opts = array_merge(array('indent' => '', 'tab' => '    ', 'array-align' => false), $opts);

        switch (gettype($var)) {
            case 'array':
                $result = array();
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $maxLength = $opts['array-align'] ? max(array_map('strlen', array_map('trim', array_keys($var)))) + 2 : 0;
                
                foreach ($var as $key => $value) {
                    $key = str_replace("'' . \"\\0\" . '*' . \"\\0\" . ", "", self::arrayToString($key));
                    $result[] = $opts['indent'] . $opts['tab']
                        . ($indexed ? '' : str_pad($key, $maxLength) . ' => ')
                        . self::arrayToString($value, array_merge($opts, array('indent' => $opts['indent'] . $opts['tab'])));
                }

                return "array(\n" . implode(",\n", $result) . "\n" . $opts['indent'] . ")";
            case 'boolean':
                return $var ? 'true' : 'false';
            case 'NULL':
                return 'null';
            default:
                return var_export($var, true);
        }
    }
}

?>