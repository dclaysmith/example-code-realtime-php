<?
/**
 * CDBManager
 */

define("DB_HOST","localhost");
define("DB_NAME","todos");
define("DB_USERNAME","root");
define("DB_PWD","");
define("LOG_DB_CALLS",false);

class CDBManager {

	// variables
	protected $_username;
	protected $_password;
	protected $_host;
	protected $_database;
	private $_connection;
	protected $_collation = "";
	
	// constructor
	function __construct($bUseSlave=false) {
		
		if ($bUseSlave) {
			$this->_username 	= DB_SLAVE_USERNAME;
			$this->_password 	= DB_SLAVE_PWD;
			$this->_host 		= DB_SLAVE_HOST;
			$this->_database 	= DB_SLAVE_NAME;
		} else {		
			$this->_username 	= DB_USERNAME;
			$this->_password 	= DB_PWD;
			$this->_host 		= DB_HOST;
			$this->_database 	= DB_NAME;
		}

		$this->_connection =  $this->db_connect();
		mysql_set_charset("utf8", $this->_connection);
		
	}

	// properties
	public function getUsername() {
		return $this->_username;
	}
	public function getPassword() {
		return $this->_password;
	}
	public function getHost() {
		return $this->_host;
	}
	public function getDatabase() {
		return $this->_database;
	}
	public function getCollation() {
		return $this->_collation;
	}
	public function setDatabase($value) {
		$this->_database = $value;
	}
	public function setUsername($value) {
		$this->_username = $value;
	}
	public function setPassword($value) {
		$this->_password = $value;
	}
	public function setHost($value) {
		$this->_host = $value;
	}
	public function setCollation($value) {
		$this->_collation = $value;
	}	
		
	// methods
	public function getConnection($bCreateTransaction=false, $bDestroyTransaction=false) {
		static $_oTransactionConnection;
		if (!is_null($_oTransactionConnection) && !$bDestroyTransaction) {
			if (LOG_DB_CALLS) $this->debug(" - USING a transaction connection.");
			return $_oTransactionConnection;
		} elseif ($bCreateTransaction) {
			if (LOG_DB_CALLS) $this->debug(" - CREATING a transaction connection.");
			$_oTransactionConnection = $this->db_connect();
			return $_oTransactionConnection;
		} elseif ($bDestroyTransaction) {
			if (LOG_DB_CALLS) $this->debug(" - DESTROYING a transaction connection.");
			$_oTransactionConnection = null;
			return $this->_connection;
		} else {			
			if (LOG_DB_CALLS) $this->debug(" - Using standard connection.");
			return $this->_connection;
		}
	}
	
	
	/**
	 * db_connect
	 */
	function db_connect() {
		if (LOG_DB_CALLS) $this->debug("CDBManager.db_connect()");
		
		$result = mysql_connect($this->getHost(), $this->getUsername(), $this->getPassword());	
		
		if (!$result) return false;
		return $result;
	}
	
	/**
	 * run_sql_return_rs
	 */
	function run_sql_return_rs($sql)
	{
		if (LOG_DB_CALLS) $this->debug("CDBManager.run_sql_return_rs(".$sql.")");

		mysql_select_db($this->getDatabase(),$this->getConnection());
	
		// always run selcts on a fresh connection regardless of transaction state
		$result = mysql_query($sql, $this->getConnection());
	
		if (mysql_errno() > 0) {
			throw new exception("An error occurred running mysql_query. (".mysql_errno()."): ".mysql_error());
		}
		
		return $result;
	}

	/**
	 * run_sql_return_int
	 */
	function run_sql_return_int($sql)
	{
		if (LOG_DB_CALLS) $this->debug("CDBManager.run_sql_return_int(".$sql.")");

		mysql_select_db($this->getDatabase(),$this->getConnection());
		
		$result = mysql_query($sql, $this->getConnection());
		
		if (mysql_errno() > 0) {
			throw new exception("An error occurred running mysql_query. (".mysql_errno()."): ".mysql_error());
		}
		if (!$result) {
			return false;
		} else {
			return mysql_insert_id();
		}
	}

	/**
	 * run_sql
	 */
	function run_sql($sql)
	{
		if (LOG_DB_CALLS) $this->debug("CDBManager.run_sql(".$sql.")");

		mysql_select_db($this->getDatabase(),$this->getConnection());
		$result = mysql_query($sql, $this->getConnection());

		if (mysql_errno() > 0) {
			throw new exception("An error occurred running the sql (".mysql_errno()."): ".mysql_error());
		}
		return $result;
	}

	/**
	 * beginTransaction
	 */
	function beginTransaction()
	{
		if (LOG_DB_CALLS) $this->debug("CDBManager.beginTransaction()");
		
		mysql_select_db($this->getDatabase(),$this->getConnection());
		
		// we are beginning a transation so use the transaction (static) connection
		$result = mysql_query("BEGIN;", $this->getConnection(true));
		if (mysql_errno() > 0) {
			throw new exception("An error occurred running the sql (".mysql_errno()."): ".mysql_error());
		}
		return $result;
	}

	/**
	 * rollbackTransaction
	 */
	function rollbackTransaction()
	{
		if (LOG_DB_CALLS) $this->debug("CDBManager.rollbackTransaction()");

		mysql_select_db($this->getDatabase(),$this->getConnection());
		$result = mysql_query("ROLLBACK;", $this->getConnection());
		$this->getConnection(false, true);
		if (mysql_errno() > 0) {
			throw new exception("An error occurred running the sql (".mysql_errno()."): ".mysql_error());
		}
		return $result;
	}


	// commitTransaction
	function commitTransaction() {
		if (LOG_DB_CALLS) $this->debug("CDBManager.commitTransaction()");

		mysql_select_db($this->getDatabase(),$this->getConnection());
		$result = mysql_query("COMMIT;", $this->getConnection());
		$this->getConnection(false, true);
		if (mysql_errno() > 0) {
			throw new exception("An error occurred running the sql (".mysql_errno()."): ".mysql_error());
		}
		return $result;
	}

	// prep
	function prep($sString) {
		
		if (get_magic_quotes_gpc()) {
			throw new exception("Turn off magic quotes.");
		}
		
		if (!is_numeric($sString)) {
			$sString = mysql_real_escape_string($sString, $this->getConnection());
		}
		
		return $sString;

	}

	static function DBValue($value, $type="string") {
		switch ($type) {
			case("tinyint"):
				return ($value=="1") ? TRUE : FALSE;
				break;
			case("int"):
			case("bigint"):
				return (int) $value;
				break;
			default:
				//return utf8_encode($value);
				return $value;
				break;
		}
	}
}
?>