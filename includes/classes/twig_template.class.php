<?php
/**
 * Class HW_Twig_engine
 */
class HW_Twig_engine {
    /**
     * twig object
     * @var
     */
    static $twig;
    /**
     * instance of this class
     * @var
     */
    static $instance;

    /**
     * load twig class
     * @param $dir
     */
    public static function init($dir) {
        if(!self::$instance) {
            self::$instance = new self();
        }
        if(!self::$twig && class_exists('Twig_Autoloader')) {
            Twig_Autoloader::register();
            $loader = new Twig_Loader_Filesystem($dir);
            self::$twig = new Twig_Environment($loader);
        }
    }
    /**
     *
     * @param $file
     * @return Twig_TemplateInterface
     */
    private static function twig_loadTemplate($file) {
        if(self::twig_file_exists($file)) {
            return self::$twig->loadTemplate($file);
        }
    }

    /**
     * check if twig template file exists
     * @param $file
     * @return bool
     */
    private  static function twig_file_exists($file){

        try{
            self::$twig->loadTemplate($file);
            return true;
        }
        catch(Exception $e){
            return false;
        }
    }
    /**
     * call twig->display method
     * @param $tpl
     * @param $data: data to be sent to template file
     */
    public static function twig_display($file, $data = array()) {
        $link = self::twig_loadTemplate($file);
        if($link) {
            $link->display($data);
        }
    }
    /**
     * call twig->render method
     * @param $tpl
     * @param $data: data to be sent to template file
     */
    public static function twig_render($tpl, $data = array()) {
        $link = self::twig_loadTemplate($tpl);
        if($link) {
            return $link->render($data);
        }
    }
}