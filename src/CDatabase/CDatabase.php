<?php

/**
 * Database wrapper, a database API for the framework
 */
class CDatabase
{
	/**
	 * Properties
	 */
	private $settings;                     // Connection settings used when instantiating PDO object
	private $db                 = null;    // The PDO object
	private $stmt               = null;    // The current statement
	private static $numQueries;            // Counter of all made queries, for debugging
	private static $queries     = array(); // All made queries, for debugging
	private static $params      = array(); // All used parameters, for debugging
	
	/**
	 * Constructor, creates a PDO object connecting to given database
	 *
	 * @param array $settings containing connection details about given database
	 */
	public function __construct($settings) {
		$defaultSettings = array (
			'dsn' => null,
			'username' => null,
			'password' => null,
			'driver_options' => null,
			'fetch_mode' => PDO::FETCH_OBJ
		);
		$this->settings = array_merge($defaultSettings, $settings);
		
		try {
			$this->db = new PDO($this->settings['dsn'], $this->settings['username'], $this->settings['password'], $this->settings['driver_options']);
		}
		catch (Exception $e) {
			//throw $e; //for debugging
			throw new PDOException("Could not connect to database.");
		}
		
    // Get debug information from session if it exists
    if (isset($_SESSION['CDatabase'])) {
      self::$numQueries = $_SESSION['CDatabase']['numQueries'];
      self::$queries    = $_SESSION['CDatabase']['queries'];
      self::$params     = $_SESSION['CDatabase']['params'];
      unset($_SESSION['CDatabase']);
    }
		
		$this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $this->settings['fetch_mode']);
	}
	
	/**
	 * Execute a SELECT query and return results
	 *
	 * @param string $query the SQL query with question mark (?) parameters
	 * @param array $params the SQL query parameters
	 * @param boolean $debug prints out SQL query if true
	 * @return array with results
	 */
	public function executeSelectFetchAll($query, $params = array(), $debug = false) {
		self::$queries[] = $query;
		self::$params[] = $params;
		self::$numQueries++;
		
		if ($debug) { echo "<p>Query = <br/><pre>{$query}</pre></p><p>Num query = " . self::$numQueries . "</p><p><pre>" . print_r($params, true) . "</pre></p>"; }
		
		$this->stmt = $this->db->prepare($query);
		$this->stmt->execute($params);
		
		return $this->stmt->fetchAll();
	}
	
	public function getDbSettings() {
		return $this->settings;
	}
	
	/**
	 * Execute an UPDATE, INSERT or DELETE query
	 *
	 * @param string $query the SQL query with question mark (?) parameters
	 * @param array $params the SQL query parameters
	 * @param boolean $debug prints out SQL query if true
	 * @return boolean true or false depending on success or error
	 */
  public function executeQuery($query, $params = array(), $debug=false) { 
    // Make database query
		$this->stmt = $this->db->prepare($query);
		$res = $this->stmt->execute($params);
		
    // Log details on the query
    $error = $res ? null : "\n\nError in executing query: " . $this->errorCode() . " " . print_r($this->errorInfo(), true);
    $logQuery = $query . $error;		
		self::$queries[] = $query; 
    self::$params[]  = $params; 
    self::$numQueries++;
 
    // Debug if set
    if($debug) {
      echo "<p>Query = <br/><pre>".htmlentities($logQuery)."</pre></p><p>Num query = " . self::$numQueries . "</p><p><pre>".htmlentities(print_r($params, 1))."</pre></p>";
    }

    return $res;
  }
	
  /**
   * Return rows affected of last INSERT, UPDATE, DELETE
   */
  public function rowCount() {
    return is_null($this->stmt) ? $this->stmt : $this->stmt->rowCount();
  }
	
  /**
   * Return last insert id
   */
  public function lastInsertId() {
    return $this->db->lastInsertid();
  }
	
  /**
   * Get a HTML summary of all queries made, for debugging and analysing purposes
   * 
   * @return string with html
   */
  public function dumpQueriesData() {
    $html  = '<pre>You have made ' . self::$numQueries . ' database queries.<br /><br />';
    foreach(self::$queries as $key => $val) {
      $params = empty(self::$params[$key]) ? null : htmlentities(print_r(self::$params[$key], true)) . '<br /><br />';
      $html .= $val . '<br /><br />' . $params;
    }
    return $html . '</pre>';
  }
	
  /**
   * Save debug information in session, useful as a flash memory when redirecting to another page
   * 
   * @param string $debug enables to save some extra debug information
   */
  public function saveDebug($debug=null) {
    if($debug) {
      self::$queries[] = $debug;
      self::$params[] = null;
    }
 
    self::$queries[] = 'Saved debuginformation to session.';
    self::$params[] = null;
 
    $_SESSION['CDatabase']['numQueries'] = self::$numQueries;
    $_SESSION['CDatabase']['queries']    = self::$queries;
    $_SESSION['CDatabase']['params']     = self::$params;
  }
	

  /**
   * Return error code of last unsuccessful statement, see PDO::errorCode().
   *
   * @return mixed null or the error code.
   */
  public function errorCode() {
    return $this->stmt->errorCode();
  }



  /**
   * Return textual representation of last error, see PDO::errorInfo().
   *
   * @return array with information on the error.
   */
  public function errorInfo() {
    return $this->stmt->errorInfo();
  }
}