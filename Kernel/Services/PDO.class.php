<?php

namespace Kernel\Services;

use Kernel\Exceptions as Exceptions;

/**
 * @brief This class is an adaptation of PHP Data Object for convenience use of PDO in Pandore.
 *
 * @details
 * This class offers to simplify the prepared queries mechanism using one method named executeQuery. This method can be used through four others methods associated with the four classic types of data manipulations (DELETE, INSERT, SELECT, and UPDATE). These methods check queries type written by developpers and provide directly the result usually expected.
 *
 * @see Kernel::Services::Logger.
 */
class PDO extends \PDO
{
    use Tools;

    /**
     * @brief The delete query type.
     */
    const DELETE = 'DELETE';
    /**
     * @brief The insert query type.
     */
    const INSERT = 'INSERT';
    /**
     * @brief The select query type.
     */
    const SELECT = 'SELECT';
    /**
     * @brief The update query type.
     */
    const UPDATE = 'UPDATE';
    
    /**
     * @brief The database name.
     * @var String.
     */
    private $database;
    
    /**
     * @brief Constructor.
     * @param String $DSN The DSN.
     *
     * @exception Kernel::Exceptions::PDOException When something bad occurs about original PDO building.
     * 
     * @see http://php.net/manual/en/ref.pdo-mysql.connection.php.
     */
    public function __construct($DSN)
    {   
        $DSNArray = $this->DSNToArray($DSN);
        $DSN = $DSNArray->offsetGet('dbms').':host='.$DSNArray->offsetGet('host').';dbname='.$DSNArray->offsetGet('dbname');
        
        try {
            parent::__construct($DSN, $DSNArray->offsetGet('username'), $DSNArray->offsetGet('password'));
            $this->database = $DSNArray->offsetGet('dbname');
        } catch(\PDOException $exception) {
            Logger::log('pdo', $exception->getMessage());

            throw new Exceptions\PDOException($exception->getMessage());
        }
    }
    
    /**
     * @brief Converts PDO to string.
     * @return String The description.
     *
     * @details
     * This php magic php decides how PDO object will react when it is treated like a string.
     * 
     * @see http://www.php.net/manual/en/language.oop5.magic.php#object.tostring.
     */
    public function __toString()
    {
        return print_r($this, true);
    }
    
    /**
     * @brief Executes delete query.
     * @param String $query The query.
     * @param Array $queryData An array of data which are bound in the SQL statement.
     * @return Int The rows number affected by the delete query.
     *
     * @exception Kernel::Exceptions::PDOException When the query doesn't contain delete instruction.
     */
    public function delete($query, $queryData = array())
    {
        if(stripos($query, self::DELETE) === false)
        {
            throw new Exceptions\PDOException('There is no delete instruction in '.$query);
        }
        
        return $this->executeQuery($query, $queryData)->rowCount();
    }
    
    /**
     * @brief Executes query with the parameters.
     * @param String $query The query.
     * @param Array $queryData An array of data which are bound in the SQL statement.
     * @param Int $fetchStyle Controls how the next row will be returned.
     * @return PDOStatement The associated PDOStatement instance.
     *
     * @details
     * This method uses PDO prepared queries which allows to secure database access.
     */
    public function executeQuery($query, $queryData, $fetchStyle = null)
    {
        $pdoStatement = $this->prepare($query);
        $success = $pdoStatement->execute($queryData);
        
        $error = $pdoStatement->errorInfo();
        if($error[0] != '00000')
        {
            $message = 'Error '.$error[1].' : '.$error[2].' in '.$query.' with '.print_r($queryData, true);
            Logger::log('pdo', $message);
        }
        
        return ($fetchStyle == null) ? $pdoStatement : $pdoStatement->fetchAll($fetchStyle);
    }

    /**
     * @brief Get the database name.
     * @return String The database name.
     */
    public function getDatabase()
    {
        return $this->database;
    }
    
    /**
     * @brief Get the last insert id.
     * @param String $name The sequence object name from which the ID should be returned.
     * @return String The last insert id.
     *
     * @see http://www.php.net/manual/en/pdo.lastinsertid.php.
     */
    public function getLastInsertId($name)
    {
        return $this->lastInsertId($name);
    }
    
    /**
     * @brief Executes insert query.
     * @param String $query The query.
     * @param Array $queryData An array of data which are bound in the SQL statement.
     * @return Int The rows number affected by the insert query.
     *
     * @exception Kernel::Exceptions::PDOException When the query doesn't contain insert instruction.
     */
    public function insert($query, $queryData = array())
    {
        if(stripos($query, self::INSERT) === false)
        {
            throw new Exceptions\PDOException('There is no insert instruction in '.$query);
        }
        
        return $this->executeQuery($query, $queryData)->rowCount();
    }
    
    /**
     * @brief Executes select query.
     * @param String $query The query.
     * @param Array $queryData An array of data which are bound in the SQL statement.
     * @param Int $fetchStyle Controls how the next row will be returned.
     * @return PDOStatement The associated PDOStatement instance.
     *
     * @exception Kernel::Exceptions::PDOException When the query doesn't contain select instruction.
     */
    public function select($query, $queryData = array(), $fetchStyle = null)
    {
        if(stripos($query, self::SELECT) === false)
        {
            throw new Exceptions\PDOException('There is no select instruction in '.$query);
        }
        
        return $this->executeQuery($query, $queryData, $fetchStyle);
    }
    
    /**
     * @brief Executes update query.
     * @param String $query The query.
     * @param Array $queryData An array of data which are bound in the SQL statement.
     * @return Int The rows number affected by the update query.
     * 
     * @exception Kernel::Exceptions::PDOException When the query doesn't contain update instruction.
     */
    public function update($query, $queryData = array())
    {
        if(stripos($query, self::UPDATE) === false)
        {
            throw new Exceptions\PDOException('There is no update instruction in '.$query);
        }
        
        return $this->executeQuery($query, $queryData)->rowCount();
    }
}

?>