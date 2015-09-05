<?php

/**
* A PHP session handler to keep session data within a MySQL database
*
* @author 	Manuel Reinhard <manu@sprain.ch>
* @link		https://github.com/sprain/PHP-MySQL-Session-Handler
*/

class MySqlSessionHandler{

    /**
     * a database MySQLi connection resource
     * @var resource
     */
    protected $dbConnection;
    
    /**
     * the name of the DB table which handles the sessions
     * @var string
     */
    protected $dbTable;

    /**
     * Set db data if no connection is being injected
     * @param 	string	$dbHost
     * @param	string	$dbUser
     * @param	string	$dbPassword
     * @param	string	$dbDatabase
     */
    public function setDbDetails($dbHost, $dbUser, $dbPassword, $dbDatabase)
    {
        $this->dbConnection = new mysqli($dbHost, $dbUser, $dbPassword, $dbDatabase);

        if (mysqli_connect_error()) {
            throw new Exception('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
        }
    }

    /**
     * Inject DB connection from outside
     * @param 	object	$dbConnection	expects MySQLi object
     */
    public function setDbConnection($dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    /**
     * Inject DB connection from outside
     * @param 	object	$dbConnection	expects MySQLi object
     */
    public function setDbTable($dbTable)
    {
        $this->dbTable = $dbTable;
    }

    /**
     * Open the session
     * @return bool
     */
    public function open()
    {
        //delete old session handlers
        $limit = time() - (3600 * 24);
        $sql = sprintf("DELETE FROM %s WHERE timestamp < %s", $this->dbTable, $limit);
        return $this->dbConnection->query($sql);
    }

    /**
     * Close the session
     * @return bool
     */
    public function close()
    {
        return $this->dbConnection->close();
    }

    /**
     * Read the session
     * @param int session id
     * @return string string of the sessoin
     */
    public function read($id)
    {
        $sql = sprintf("SELECT data FROM %s WHERE id = '%s'", $this->dbTable, $this->dbConnection->escape_string($id));
        if ($result = $this->dbConnection->query($sql)) {
            if ($result->num_rows && $result->num_rows > 0) {
                $record = $result->fetch_assoc();
                return $record['data'];
            } else {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * Write the session
     * @param int session id
     * @param string data of the session
     */
    public function write($id, $data)
    {

        $sql = sprintf("REPLACE INTO %s VALUES('%s', '%s', '%s')",
                       $this->dbTable,
                       $this->dbConnection->escape_string($id),
                       $this->dbConnection->escape_string($data),
                       time());
        return $this->dbConnection->query($sql);
    }

    /**
     * Destoroy the session
     * @param int session id
     * @return bool
     */
    public function destroy($id)
    {
        $sql = sprintf("DELETE FROM %s WHERE `id` = '%s'", $this->dbTable, $this->dbConnection->escape_string($id));
        return $this->dbConnection->query($sql);
    }

    /**
     * Garbage Collector
     * @param int life time (sec.)
     * @return bool
     * @see session.gc_divisor      100
     * @see session.gc_maxlifetime 1440
     * @see session.gc_probability    1
     * @usage execution rate 1/100
     *        (session.gc_probability/session.gc_divisor)
     */
    public function gc($max)
    {
        $sql = sprintf("DELETE FROM %s WHERE `timestamp` < '%s'", $this->dbTable, time() - intval($max));
        return $this->dbConnection->query($sql);
    }
}
