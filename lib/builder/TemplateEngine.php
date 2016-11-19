<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ntemple
 * Date: 3/16/13
 * Time: 12:38 AM
 * To change this template use File | Settings | File Templates.
 */


class TemplateEngine {

    function __construct() {   }

    function render($buffer, $contents, $logLevel) {

        $buffer = $this->filter_twig($buffer, $contents, $logLevel);
        $buffer = $this->subst($buffer, $contents, $logLevel);

        return $buffer;
    }


    function subst($content, $data) {

        $data  = array_merge( (array) $this, $data);

        foreach ($data as $n => $v) {
            if (!is_scalar($v)) continue;

            $content = str_replace("/*{".$n ."}*/", $v, $content);
            $content = str_replace("//{".$n ."}", $v, $content);
            $content = str_replace("{".$n ."}", $v, $content);

            $n = strtolower($n); $v = strtolower($v);

            $content = str_replace("/*{".$n ."}*/", $v, $content);
            $content = str_replace("//{".$n ."}", $v, $content);
            $content = str_replace("{".$n ."}", $v, $content);


            $n = strtoupper($n); $v = strtoupper($v);

            $content = str_replace("/*{".$n ."}*/", $v, $content);
            $content = str_replace("//{".$n ."}", $v, $content);
            $content = str_replace("{".$n ."}", $v, $content);

        }

        // $content .= print_r($data, true);

        return $content;

    }




    function filter_twig($buffer, $contents, $logLevel) {
        $opt = (array) $this;
        $opt['project'] = $contents;

        $twig = new Twig_Environment(new Twig_Loader_String(), array());
        $twig->addGlobal('ctx', $this);
        $buffer =  $twig->render($buffer, $opt);

        return $buffer;
    }

    function set($name, $value = null) {
        if (is_array($name)) {
            $array = $name;
            foreach ($array as $n => $v) {
                $this->$n = $v;
            }
        } else {
            $this->$name = $value;
        }
    }

    function debug($msg) {
        if ($this->_opt['debug']) {
            print ": $msg\n";
        }
    }

    function lipsum($size) {
        $out = '';
        for($i = 0; $i < $size; $i++) {
            $out .= 'lorum ipsum';
        }
        return $out;
    }


}
