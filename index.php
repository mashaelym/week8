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

        $sql = 'SELECT * FROM ' . $tableName . ' WHERE id = ?' ;
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

class model 
{
    public function save()
    {
        $db = dbConn::getConnection();

        if ($this->id == '')
        {
            $sql = $this->insert();

            print $sql . "<br/>";
            $statement = $db->prepare($sql);
            $columnValues = $this->getColumnValues();
            
            $i = 1;
            foreach($columnValues as $value)
            {
                $statement->bindValue($i, $value);
                $i++;
            }
                        
//           print "<pre>" . print_r($statement->debugDumpParams(), true) . "</pre>";

            $statement->execute();
//            echo 'I just saved record with id: ' . $db->lastInsertId();
            print "<br/>";
            
            return $db->lastInsertId(); 
        } 
        else
        {
            $sql = $this->update();
            
            print $sql . "<br/>";
            
            //check to see if the record I want to update exists
            $statement = $db->prepare($sql);
            $statement->execute();
      //      echo 'I just updated record: ' . $statement->rowCount(); 
            print "<br/>";
            
            return $this->id; 
        }
    }

    private function insert()
    {       
 
        $tableName = $this::TABLE_NAME;
        
        $columnString = implode(',', $this->getColumnNames());

        $valuePlaceholderArray = array_fill(0, sizeof($this->getColumnValues()), '?');
        
        $valueString = implode(',', $valuePlaceholderArray);
              
        $sql = "INSERT INTO $tableName ($columnString) VALUES ($valueString)";
        return $sql;
    }

    private function update()
    {

        $tableName = $this::TABLE_NAME;
        $primaryKey = $this::PRIMARY_KEY;
        $id = $this->getColumnValues()['id'];
                        
        $valueString = NULL;
        
        foreach($this->getColumnValues() as $columnName => $value)
        {
        
            if($columnName !== $primaryKey and !empty($value))
            {
                $valueString .= $columnName . ' = \'' . $value .'\', '; 
            }
        }
        
        $valueString = rtrim($valueString, ', ');
        
        $sql = "UPDATE $tableName SET $valueString WHERE $primaryKey = $id";
        return $sql;
    }
    
    public function deleteById()
    {

        $tableName = $this::TABLE_NAME;
        $primaryKey = $this::PRIMARY_KEY;
        
        $sql = "DELETE FROM $tableName WHERE $primaryKey = ?";
        
        $db = dbConn::getConnection();
        $statement = $db->prepare($sql);
        $statement->bindValue(1, $this->id);
        
//    print "<pre>" . print_r($statement->debugDumpParams(), true) . "</pre>";

        $statement->execute();
        

//      echo 'We deleted this number of rows ' . $statement->rowCount() . "<br/>";
//      echo 'I just tried to delete record with id: ' . $this->id;
        print "<br/>";
        
        if($statement->rowCount() == 1)
        {
            return true;
        }
        
        return false;
    }
    private function getColumnNames()
    {
        $obj = new ReflectionObject($this);
        $objs = $obj->getProperties(ReflectionProperty::IS_PUBLIC);
        $columns = array();
        
        foreach($objs as $column)
        {
            $columns[] = $column->{'name'};
        }
        
        return $columns;
    }
    
    private function getColumnValues()
     {

        $obj = new ReflectionObject($this);
        $columns = array();
        foreach($obj->getProperties(ReflectionProperty::IS_PUBLIC) as $property)
        {
            $columns[$property->getName()] = $property->getValue($this); 
            //array('property name' => 'property value')
        }
        
        return $columns;
    }

}
    
class account extends model
 {
    public $id;
    public $birthday;
    public $email;
    public $fname;
    public $gender;
    public $lname;
    public $password;
    public $phone;
    
    const PRIMARY_KEY = 'id';
    const TABLE_NAME = 'accounts';
}
class todo extends model
 {
    public $id;
    public $owneremail;
    public $ownerid;
    public $createddate;
    public $duedate;
    public $message;
    public $isdone;
   
    const PRIMARY_KEY = 'id';
    const TABLE_NAME = 'todos';
}

// *****************************************************************************

//testing ...


//insert
$record = new todo();
$record->message = 'some task';
$record->isdone = 0;
$record->ownerid = 1;
$record->owneremail = 'me@me.com';
$record->createddate = '2017-11-14 12:59:45';
$record->duedate = '2017-11-15 12:59:45';
$record->save();

echo '<h1>' . "Insert NEW Record" . '</h1>';

 $tableOpenString = '<table>';
    $headerString = '<thead><tr>';

    foreach ($record as $columnName)
    {
      $headerString.="<td>$columnName</td>";
    }

    $headerString.='</thead></tr>';
    $tableBodyString = '<tbody>';
    for ($i=1 ; $i<=sizeof($record)-1 ; $i++)
    {
        $currentArray = $records[$i];  

        if (sizeof($currentArray)==sizeof($record[0]))
        {
          $tableBodyString.='<tr>';
         foreach ($currentArray as $row)
         {
            $tableBodyString.="<td>$row</td>";
         }
         $tableBodyString.='</tr>';
        }
    }
    $tableBodyString.='</tbody>';
    $tableCloseString = '</table>';

    $htmlOutput = $tableOpenString . $headerString . $tableBodyString . $tableCloseString . "<br/>";

    print $htmlOutput; 
    echo '<hr>';


// this would be the method to put in the index page for todos

$records = todos::findAll();

echo '<h1>' . "Select All Records" . '</h1>';
$tableOpenString = '<table>';
    $headerString = '<thead><tr>';

    foreach ($records[0] as $columnName)
    {
      $headerString.="<td>$columnName</td>";
    }

    $headerString.='</thead></tr>';
    $tableBodyString = '<tbody>';
    for ($i=1 ; $i<=sizeof($records)-1 ; $i++)
    {
        $currentArray = $records[$i];  

        if (sizeof($currentArray)==sizeof($records[0]))
        {
          $tableBodyString.='<tr>';
         foreach ($currentArray as $row)
         {
            $tableBodyString.="<td>$row</td>";
         }
         $tableBodyString.='</tr>';
        }
    }
    $tableBodyString.='</tbody>';
    $tableCloseString = '</table>';

    $htmlOutput = $tableOpenString . $headerString . $tableBodyString . $tableCloseString . "<br/>";

    print $htmlOutput; 
    echo '<hr>';
 



//this code is used to get one record and is used for showing one record or updating one record

$record = todos::findOne(1);

echo '<h1>' . "Select One Record" . '</h1>';
$tableOpenString = '<table>';
    $headerString = '<thead><tr>';

    foreach ($record as $columnName)
    {
      $headerString.="<td>$columnName</td>";
    }

    $headerString.='</thead></tr>';
    $tableBodyString = '<tbody>';
    for ($i=1 ; $i<=sizeof($record)-1 ; $i++)
    {
        $currentArray = $record[$i];  

        if (sizeof($currentArray)==sizeof($record[0]))
        {
          $tableBodyString.='<tr>';
         foreach ($currentArray as $row)
         {
            $tableBodyString.="<td>$row</td>";
         }
         $tableBodyString.='</tr>';
        }
    }
    $tableBodyString.='</tbody>';
    $tableCloseString = '</table>';

    $htmlOutput = $tableOpenString . $headerString . $tableBodyString . $tableCloseString;

    print $htmlOutput; 
    echo '<hr>';

    ?>