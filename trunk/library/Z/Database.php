<?php

class Z_Database
{	
	/**
	 * @var zCore 
	 */
	protected $_application;
	
	protected $_ezDb;
	protected $_result;	
	protected $_lastQuery = '';
	protected $_dbConfig = array();
	protected $_options = array('AUTO_PING' => false);
	
	static protected $_Instance = array();
	
	public function setOption($option, $value)
	{
		$this->_options[$option] = $value;
	}
	
	public function getOption($option)
	{
		if (!isset($this->_options[$option]))
			return null;
			
		return $this->_options[$option];	
	}
	
	public static function getInstance($name = null) 
	{ 
		$class = __CLASS__;
		
		if ($name == null)
		{
			$db_conf = Z_Global::getSubkey('@Z.Config', 'Database');
			$db_conf = $db_conf['default'];
			$name = 'default';
		}elseif (is_string($name))
		{
			$dbconfig = Z_Global::getSubkey('@Z.Config', 'Database');
			$db_conf = $dbconfig[$name];							
		}else{
			
			$db_conf = $name;	
			$name = $db_conf['name'];
		}
		
		if (!isset(self::$_Instance[$name]))
		{
			self::$_Instance[$name] = new $class($db_conf);
		}
		
		return self::$_Instance[$name];
	}
	
	public function getLastQuery()
	{
		return $this->_lastQuery;	
	}
	
	public function isConnected()
	{
		return (is_object($this->_ezDb));
	}
	
	public function __construct($db_conf)
	{
		$this->Connect($db_conf);
	}
	
	public function ping($autoReconnect = false)
	{
		try{
			$this->Query('SELECT 1');	
			return true;
		}catch(Exception $e)
		{
			try{
				$this->Connect($this->_dbConfig);
				return true;
			}catch(Exception $e)
			{
				return false;	
			}
		}
	}
	
	protected function connect($db_conf)
	{
		try{
			if ($db_conf == null)
				$resource_id = ezcDbFactory::create('mysql://localhost/');
			else
				$resource_id = ezcDbFactory::create( 'mysql://' . $db_conf['username'] . ':' . $db_conf['password'] . '@' . $db_conf['host'] . ':' . $db_conf['port'] . '/' . $db_conf['db_name'] );

		}catch(Exception $ex)
		{				
			return false;
		}
		
		$this->_dbConfig = $db_conf;		
		$this->_ezDb =$resource_id;
		
		/*
		try{
			$sQuery  = 'SET NAMES UTF8';
			$rResult = $this->Query($sQuery);
			$sQuery  = 'SET CHARACTER SET UTF8';
			$rResult = $this->Query($sQuery);
			$sQuery  = 'SET COLLATION_CONNECTION = "utf8_general_ci"';
			$rResult = $this->Query($sQuery);
		}catch(Exception $ex){
			
		}
		*/
		return true;
	}
	
	public function quoteSmart($value, $add = true)
	{		
		if ($add)
			$value = "'" . addslashes($value) . "'";
		else
			$value = addslashes($value);
		
		return $value;
	}
	
	public function query($query)
	{
		if (($this->getOption('AUTO_PING')) && (!$this->Ping(true)))
			return false;
			
		try{
			$this->_lastQuery = $query;
			$res = $this->_ezDb->Query($query);
			
			if (!$res)
			{
				$this->_result = array();	
			}else{
				try{
					$this->_result = $res->fetchAll( PDO::FETCH_ASSOC );
				}catch(Exception $ex)
				{
					$this->_result = array();
				}
				
			}
			if (($res) && (is_array($this->_result)))
				return $this->_result;
				
			return $res;
		}catch(Exception $ex)
		{
			$this->_result = array();	
			//print_r($ex);
			return false;		
		}
	}
	
	public function prepare ($query)
	{
		if (($this->getOption('AUTO_PING')) && (!$this->Ping(true)))
			return false;
		
		return $this->_ezDb->prepare($query);		
	}
	
	public function insert ($table)
	{
		if (($this->getOption('AUTO_PING')) && (!$this->Ping(true)))
			return false;
		
		return $this->_ezDb->createInsertQuery()->insertInto($table);
	}	
	
	public function select ($selectedFields = '*')
	{
		if (($this->getOption('AUTO_PING')) && (!$this->Ping(true)))
			return false;
		
		return $this->_ezDb->createSelectQuery()->select($selectedFields);
	}
	
	public function update ($table)
	{
		if (($this->getOption('AUTO_PING')) && (!$this->Ping(true)))
			return false;
		
		return $this->_ezDb->createUpdateQuery()->update($table);
	}
	
	public function delete ($table)
	{
		if (($this->getOption('AUTO_PING')) && (!$this->Ping(true)))
			return false;
		
		return $this->_ezDb->createDeleteQuery()->deleteFrom($table);
	}
	
	public function begin()
	{
		if (($this->getOption('AUTO_PING')) && (!$this->Ping(true)))
			return false;
		
		return $this->_ezDb->beginTransaction();		
	}
	
	public function commit()
	{
		if (($this->getOption('AUTO_PING')) && (!$this->Ping(true)))
			return false;
		
		return $this->_ezDb->commit();		
	}
	
	public function rollback()
	{
		if (($this->getOption('AUTO_PING')) && (!$this->Ping(true)))
			return false;
			
		return $this->_ezDb->rollback();		
	}
	
	public function getData()
	{
		if ($this->_result)
		{
			return $this->_result;			
		}
		
		return array();
	}
	
	public function getInsertId()
	{		
		return $this->_ezDb->lastInsertId();		
	}
	
	public function count()
	{
		if ($this->_result)
		{
			return count($this->_result);
		}
		
		return false;
	}	
}

