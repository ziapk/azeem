<?php
//function __autoload($class_name) {
spl_autoload_register(function ($class_name) {
    //class directories
    $directorys = array('classes/');
    
    //for each directory
    foreach($directorys as $directory)
    {
        //see if the file exsists
        if(file_exists(dirname(__FILE__).'/../../'.$directory. strtolower($class_name). '.php'))
        {
            require_once(dirname(__FILE__).'/../../'.$directory. strtolower($class_name).'.php');
            //only require the class once, so quit after to save effort (if you got more, then name them something else 
            return;
        }            
    }
});