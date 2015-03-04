<?php

require ('config.class.php');
class Pars{
    private $handle;
    private $mysqli;
    private $dtm = Config::DB_TABLE_NAME;
	function __construct(){
        //Получаем ссылку на фаил
        $this->handle = fopen(Config::PFILE, "r");
        //Получаем ссылку на базу
        $this->mysqli = new mysqli(Config::DB_HOST, Config::DB_LOGIN, Config::DB_PASS, Config::DB_NAME);
        if($this->mysqli->connect_error){
            die('Ошибка подключения ('.$this->mysqli->connect_error.')');
        }
        //Проверяем есть ли база, если нет - создаем
        $sql = "SELECT `id` FROM".' '.Config::DB_TABLE_NAME;
        if($this->mysqli->query($sql) === false){
            $sql = <<<SQL1
                CREATE TABLE IF NOT EXISTS `$this->dtm` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `uid` varchar(256) NOT NULL,
                  `pid` int(10) unsigned NOT NULL,
                  `title` text NOT NULL,
                  `sort` int(10) unsigned NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
SQL1;
            if($this->mysqli->query($sql)){
                echo 'Таблица создана, парсинг заверщен';
            }else{
                die('Ошибка создания таблицы ('.$this->mysqli->error.')');
            }
        }else{
            die('В базе уже есть записи, скорее всего парсинг уже осуществлен');
        }
    }
    //Закрываем соединения с БД и фаилом
	function __destruct(){
        $this->mysqli->close();
        fclose($this->handle);
    }
    //Собственно сама ф-ия парсинга
    function parserTxt(){
        if ($this->handle) {
            while (($buffer = fgets($this->handle)) !== false) {
                $arr = explode("#", $buffer);
                $uid = $arr[0];
                $title = $arr[1];
                $pid = $arr[2];
                $sort = $arr[3];
                $sql = <<<SQL2
                    INSERT INTO `$this->dtm` (`uid`, `pid`, `title`, `sort`)
                    VALUES ('$uid', $pid, '$title', $sort)
SQL2;
                if(!($this->mysqli->query($sql))){
                    die('Ошибка запись в БД ('.$this->mysqli->error.')');
                }
            }
            if (!feof($this->handle)) {
                echo "Ошибка:  неожиданный конец фаила \n";
            }
        }
    }
	
}