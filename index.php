<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
define('DATABASE', 'ma735');
define('USERNAME', 'ma735');
define('PASSWORD', 'Zv51vHNoj');
define('CONNECTION', 'sql1.njit.edu');
class dbConn{
    protected static $db;
    private function __construct() {
    try {
            self::$db = new PDO( 'mysql:host=' . CONNECTION .';dbname=' . DATABASE, USERNAME, PASSWORD );
            self::$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            }
        catch (PDOException $e)
        {
            echo "Connection Error: " . $e->getMessage();
        }
    }

    public static function getConnection() {

        if (!self::$db) {

         new dbConn();
        }
        return self::$db;
    }
}

$c = dbConn::getConnection(); 

class collection {
    static public function create() {
        $model = new static::$modelName;
        return $model;
    }
    static public function findAll() {
        $db = dbConn::getConnection();
        $calledClassName = get_called_class();

        $modelClassName = $calledClassName::$modelName;
        $tableName = $modelClassName::TABLE_NAME;

        $sql = 'SELECT * FROM ' . $tableName;
        $statement = $db->prepare($sql);
        $statement->execute();
        $class = static::$modelName;
        $statement->setFetchMode(PDO::FETCH_CLASS, $class);
        $recordsSet =  $statement->fetchAll();
        return $recordsSet;
    }
    static public function findOne($id) {
        $db = dbConn::getConnection();
        $calledClassName = get_called_class();

        $modelClassName = $calledClassName::$modelName;
        $tableName = $modelClassName::TABLE_NAME;

        $sql = 'SELECT * FROM ' . $tableName . ' WHERE id = ?';
        $statement = $db->prepare($sql);
        $statement->bindValue(1, $id);
        $statement->execute();

        $class = static::$modelName;
        $statement->setFetchMode(PDO::FETCH_CLASS, $class);
        $recordsSet =  $statement->fetchAll();

        if($statement->rowCount() == 1)
        {
            return $recordsSet[0];
        }
        
        return NULL;
    }       
}

class accounts extends collection {
    protected static $modelName = 'account';
}
class todos extends collection {
    protected static $modelName = 'todo';
}

class model {

    public function save()
    {
        if ($this->id = '') {
            $sql = $this->insert();
        } else {
            $sql = $this->update();
        }
        $db = dbConn::getConnection();
        $statement = $db->prepare($sql);
        $statement->execute();
        $tableName = get_called_class();
        $array = get_object_vars($this);
        $columnString = implode(',', $array);
        $valueString = ":".implode(',:', $array);
        echo 'I just saved record: ' . $this->id;
    }
    private function insert() {
        $sql = '';
        return $sql;
    }
    private function update() {
        $sql = '';
        return $sql;
        echo 'I just updated record' . $this->id;
    }
    public function delete() {
        echo 'I just deleted record' . $this->id;
    }
}
class account extends model {
    public $id;
    public $birthday;
    public $email;
    public $fname;
    public $gender;
    public $lname;
    public $password;
    public $phone;
    public function __construct()
    {
        $this->tableName = 'accounts';
    
    }
}
class todo extends model {
    public $id;
    public $owneremail;
    public $ownerid;
    public $createddate;
    public $duedate;
    public $message;
    public $isdone;
    public function __construct()
    {
        $this->tableName = 'todos';
    
    }
}

