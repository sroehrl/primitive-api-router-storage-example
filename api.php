<?php


use Neoan3\Apps\Ops;

require_once 'vendor/autoload.php';
// receive requests

// header JSON

header('Content-Type: application/json');

// METHOD & endpoint based router

class Api {
    private $instance = null;
    public $result = '';
    function __construct()
    {
        $finalFunction = $this->extractMethod();
        $this->instance = new Router();
        $this->result = json_encode($this->instance->$finalFunction());
    }
    function extractMethod()
    {
        $url_parts = explode('/', $_SERVER['REQUEST_URI']);
        $function = explode('?',ucfirst(end($url_parts)));
        return strtolower($_SERVER['REQUEST_METHOD']) . $function[0];
    }
}

class Router{
    function postLogin()
    {
        $error = ['error' => 'wrong credentials'];
        // username & password
        if(!isset($_POST['password']) || !isset($_POST['username'])){
            return $error;
        }
        $db = new flatDB();
        $user = $db->read($_POST['username']);
        if(Ops::decrypt($user['password'],$_POST['password']) === $_POST['password']){
            return ['login' => 'success'];
        }
        return $error;
    }
    function postRegister()
    {
        // username & password
        if(!isset($_POST['password']) || !isset($_POST['username'])){
            return ['error' => 'wrong credentials'];
        }
        $db = new flatDB();
        $db->write($_POST['username'],['password'=>Ops::encrypt($_POST['password'],$_POST['password'])]);
        return ['registered' => $_POST['username']];
    }
}

class flatDB{
    private $db;
    private $fileLocation;
    function __construct()
    {
        $this->fileLocation = dirname(__DIR__).'/database.json';
        if(!file_exists($this->fileLocation)){
            file_put_contents($this->fileLocation,'{}');
        }
        $this->db = json_decode(file_get_contents($this->fileLocation),true);
    }

    function read($identifier)
    {
        return isset($this->db[$identifier]) ? $this->db[$identifier] : [];
    }
    function write($identifier, $content = [])
    {
        $this->db[$identifier] = $content;
        file_put_contents($this->fileLocation, json_encode($this->db));
    }
}

$api = new Api();

echo $api->result;


