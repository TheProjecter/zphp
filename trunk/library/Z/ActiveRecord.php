<?php

class Z_ActiveRecord implements ArrayAccess
{	
	protected $_Db;
	protected $_DbName;
	
	protected $_Data = array();
	protected $_ExtraData = array();
	protected $_Changed = false;
	protected $_Loaded = false;
	
	protected $_Fields = array();
	protected $_Primary = 'id';	
	protected $_TableName = 'table';
		
	protected $_Class = __CLASS__;
	
	protected $_Mappings = array();	
	protected $_MappingData = array();
	protected $_MappedLinks = array();
	
	protected $_ManyMappings = array();
	protected $_ManyMappingData = array();
	protected $_ManyMappedLinks = array();
	protected $_ChangedFields = array();
	public static $PrimaryField = 'id';
	
	
	public function __construct($dbName = null)
	{
		$this->_DbName = $dbName;
		$this->_Db = Z_Database::GetInstance($dbName);
		
		$this->LoadMappings();
	}
	
	// Now active records can be unserialized :)
	public function __wakeup()
	{		
		$this->_Db = Z_Database::getInstance($this->_DbName);
	}
	
	public function __sleep()
	{
		$this->_Db = null;
		$vars = get_object_vars($this);
		// var_dump($vars);die();
		// print_r($vars);
		return array_keys($vars);
	}
	public function Refresh()
	{
		if ($this->_Loaded)
		{
			$pid = $this->_Primary;
			$pid = $this->$pid;
			$this->_MappingData = array();
			$this->_MappedLinks = array();
			$this->_ManyMappingData = array();
			$this->_ManyMappedLinks = array();
			$this->LoadMappings();
			
			return ($this->Load($pid) !== false);
		}
	}
	
	public function PrimaryField()
	{
		return $this->_Primary;	
	}
	
	public function IsLoaded()
	{
		return $this->_Loaded;	
	}
	
	/**
	 * @return Database 
	 */
	public function GetDb()
	{
		return $this->_Db;		
	}
	
	protected function LoadMappings()
	{
		foreach ($this->_Mappings as $index => $value)
		{
			foreach ($value as $mappedField => $mapping)
				{
					if ((!in_array($mappedField, $this->_Fields)) && (count($this->_Fields) != 0))
						continue;
					
					foreach ($mapping as $key => $mapInfo)
					{
						if (!is_array($mapInfo))
						{
							$obj = $mapInfo;
							$pkey = '';
							
							$pkey = $obj::$PrimaryField;
							
							// eval('$pkey = ' . $obj . '::PrimaryField;');
							//$pkey = call_user_func("$obj::PrimaryField")
							if (!isset($this->_MappingData[$key]))
								$this->_MappingData[$key] = array();
							
							$this->_MappingData[$key][] = array($mappedField =>  array($obj, $pkey));
							$this->_MappedLinks[$mappedField] = array($obj, $pkey);
						}else{
							$obj = $mapInfo[0]; //new $mapInfo[0]();
							$pkey = $mapInfo[1];
							if (!isset($this->_MappingData[$key]))
								$this->_MappingData[$key] = array();
							
							$this->_MappingData[$key][] = array($mappedField =>  array($obj, $pkey));
							$this->_MappedLinks[$mappedField] = array($obj, $pkey);
						}
					}		
			}
		}
		
		// New - many mappings
		foreach ($this->_ManyMappings as $mappedField => $mapping)
		{
			//$mappedField = "$mappedField;
			
			if ((!in_array($mappedField, $this->_Fields)) && (count($this->_Fields) != 0))
				continue;
			
			foreach ($mapping as $key => $mapInfo)
			{
				if (!is_array($mapInfo))
				{
					$obj = $mapInfo; // new $mapInfo();
					exec('$pkey = ' . $obj . '::PrimaryField;');
					
					$this->_ManyMappingData[$key] = array($mappedField =>  array($obj, $pkey));
					$this->_ManyMappedLinks[$mappedField] = array($obj, $pkey);
				}else{
					$obj =$mapInfo[0];
					$pkey = $mapInfo[1];
					$this->_ManyMappingData[$key] = array($mappedField =>  array($obj, $pkey));
					$this->_ManyMappedLinks[$mappedField] = array($obj, $pkey);
				}
			}		
		}
	}
	/*
	protected function LoadMappings()
	{
		foreach ($this->_Mappings as $mappedField => $mapping)
		{
			if ((!in_array($mappedField, $this->_Fields)) && (count($this->_Fields) != 0))
				continue;
				
			foreach ($mapping as $key => $mapInfo)
			{
				if (!is_array($mapInfo))
				{
					$obj = new $mapInfo();
					$pkey = $obj->PrimaryField();
					
					$this->_MappingData[$key] = array($mappedField =>  array($obj, $pkey));
					$this->_MappedLinks[$mappedField] = array($obj, $pkey);
				}else{
					$obj = new $mapInfo[0]();
					$pkey = $mapInfo[1];
					$this->_MappingData[$key] = array($mappedField =>  array($obj, $pkey));
					$this->_MappedLinks[$mappedField] = array($obj, $pkey);
				}
			}		
		}
		
		// New - many mappings
		foreach ($this->_ManyMappings as $mappedField => $mapping)
		{
			//$mappedField = "$mappedField;
			
			if ((!in_array($mappedField, $this->_Fields)) && (count($this->_Fields) != 0))
				continue;
			
			foreach ($mapping as $key => $mapInfo)
			{
				if (!is_array($mapInfo))
				{
					$obj = new $mapInfo();
					$pkey = $obj->PrimaryField();
					
					$this->_ManyMappingData[$key] = array($mappedField =>  array($obj, $pkey));
					$this->_ManyMappedLinks[$mappedField] = array($obj, $pkey);
				}else{
					$obj = new $mapInfo[0]();
					$pkey = $mapInfo[1];
					$this->_ManyMappingData[$key] = array($mappedField =>  array($obj, $pkey));
					$this->_ManyMappedLinks[$mappedField] = array($obj, $pkey);
				}
			}		
		}
	}
	*/
	public function Load($id, $where = '')
	{
		if (is_array($id))
			return $this->LoadData($id);
			
		if ($where != '')
			$where = " AND $where ";
			
		$query = "SELECT * FROM " . $this->_TableName . " WHERE " . $this->_Primary . " = " . $this->_Db->QuoteSmart($id) . " $where LIMIT 1";
		$res = $this->_Db->Query($query);

		if (!$res)
		{
			$this->_Loaded = false;
			return false;
			
		}
		
		$data = $this->_Db->GetData();	
		if (count($data) == 0)
			return false;
		$this->LoadData($data[0]);
		return $this;
	}
	
	protected function LoadData($data)
	{
		$this->_Data = $data;
		$this->_Loaded = true;		
	}
	
	public function ToArray()
	{
		return $this->_Data;	
	}
	
	public function Extra($key, $value = null)
	{
		if ($value !== null)
		{
			$this->_ExtraData[$key] = $value;	
			return $value;
		}else{
			if (isset($this->_ExtraData[$key]))
				return $this->_ExtraData[$key]; 
		}	
		
		return null;
	}
	public function UpdateFields($fields = array())
	{
		if (!$this->_Loaded) 
			return false;
		
		$db = $this->_Db;
		
		$Query = "UPDATE " . $this->_TableName . " SET ";
		
		for($i=0;$i < count($fields);$i++)
		{
			$field = $fields[$i];
			
			if (!isset($this->_Data[$field]))
				continue; // Bad field specified
			
			if (isset($this->_Data[$field]))
				$data = $this->_Data[$field];
			else
				$data = '';
			
			if (isset($this->_MappedLinks[$field]))
			{
				$obj = 	$this->_MappedLinks[$field][0];
				$pkey = 	$this->_MappedLinks[$field][1];
									
				if (is_object($obj))
				{
					if ($obj->IsLoaded())
					{
						$data = $obj[$pkey];	
					}
				}
			}
			
			$Query.= $fields[$i] . ' = ' . $db->QuoteSmart($data);
			
			if ($i+1 < count($fields))
				$Query .=", ";	
		}
		
		if (substr($Query, -2) == ', ')
			$Query = substr($Query, 0, strlen($Query) - 2);
		
		$Query .= " WHERE " . $this->_Primary . " = " .$db->QuoteSmart($this->_Data[$this->_Primary]);
		
		$res = $db->Query($Query);
		if (!$res)
			return false;
		
		return true;		
	}
	
	public function Update($updateMappings = false, $saveAll = false)
	{
	
		if ($this->_Changed)
		{
			
			if (!$this->_Loaded) 
				return false;
			
			if (count($this->_Fields) != 0)
			{
				$fields = $this->_Fields;	
			}else{
				$fields = array_keys($this->_Data);			
			}
			
			$db = $this->GetDb();
			$Query = "UPDATE " . $this->_TableName . " SET ";
			
			for($i=0;$i < count($fields);$i++)
			{
				$field = $fields[$i];
				
				if (!isset($this->_Data[$field]))
					continue; // Bad field specified
				
				if ((!$saveAll) && (!in_array($field, $this->_ChangedFields)))
					continue;
					
				if (isset($this->_Data[$field]))
					$data = $this->_Data[$field];
				else
					$data = '';
				
				if (isset($this->_MappedLinks[$field]))
				{
					$obj = 	$this->_MappedLinks[$field][0];
					$pkey = 	$this->_MappedLinks[$field][1];
					
					if (is_object($obj))
					{
						if ($obj->IsLoaded())
						{
							$data = $obj[$pkey];	
						}
					}
				}
				//$data = $this->_Data[$field];
				
				$Query.= $fields[$i] . ' = ' . $db->QuoteSmart($data);
				
				if ($i+1 < count($fields))
					$Query .=", ";	
			}
			
			if (substr($Query, -2) == ', ')
				$Query = substr($Query, 0, strlen($Query) - 2);
			
			$Query .= " WHERE " . $this->_Primary . " = " .$db->QuoteSmart($this->_Data[$this->_Primary]);
			
			$res = $db->Query($Query);
			if (!$res)
				return false;
			
			$res = true;
		}else{
			$res = 1;	
		}
		
		if (!$updateMappings)
			return $res;
			
		foreach($this->_MappedLinks as $field => $info)
		{
			$obj = $info[0];
			if (is_object($obj))
			{
				$res2 = $obj->Update(true, $saveAll);
				
				if ($res2 == false)
					$res = 2; // Means saved, but mapped links have issues!
			}
		}		
		
		foreach($this->_ManyMappedLinks as $field => $info)
		{
			$objs = $info[0];
			
			foreach ($objs as $obj)
			{
				if (is_object($obj))
				{
					$res2 = $obj->Update(true, $saveAll);
					
					if ($res2 == false)
						$res = 2; // Means saved, but mapped links have issues!
				}
			}
		}
		
		return $res;					
	}	
	
	public function Save($updateMappings = false, $saveAll = false)
	{
		if ($this->_Loaded)
			return $this->Update($updateMappings, $saveAll);
		else
			return $this->Insert();
	}
	
	public function Copy($unsetPrimary = true, $unsetMappingsPrimary = false)
	{
		$class = get_class($this);
		
		$obj = new $class();
		if (count($this->_Fields) == 0)
		{
			foreach ($this->_Data as $key => $value)
			{
				$obj[$key] = $value;
			}
			
			foreach ($this->_MappedLinks as $key => $value)
			{
				$o = $value[0];
				$pkey = $value[1];
								
				if (is_object($o))
				{
					if ($o->IsLoaded())				
						$obj[$key] = $o[$pkey];
				}
			}	
			
		}
		
		foreach ($this->_Fields as $field)
		{
			foreach ($this->_Data as $key => $value)
			{
				if ($key != $field)
					continue;
				
				$obj[$key] = $value;
			}
			
			foreach ($this->_MappedLinks as $key => $value)
			{
				if ($key != $field)
					continue;
				$o = $value[0];
				$pkey = $value[1];
								
				if (is_object($o))
				{
					if ($o->IsLoaded())				
						$obj[$key] = $o[$pkey];
				}
			}
		}	
		foreach ($this->_MappingData as $mapName => $infoX)
		{
			foreach ($infoX as $index => $info)
			{
				foreach ($info as $fieldName => $info)
				{
					$obj->$mapName = $info[0]->Copy($unsetMappingsPrimary);
				}	
			}
		}
		
		// New - many mapping 
		foreach ($this->_ManyMappingData as $mapName => $info)
		{
			foreach ($info as $fieldName => $info)
			{
				$obj->$mapName = $info[0]->Copy($unsetMappingsPrimary);
			}	
		}
		
		if ($unsetPrimary === true)
		{
			$pkey = $this->PrimaryField();
			$obj->$pkey = null;
		}elseif (is_string($unsetPrimary))
		{
			$obj->$unsetPrimary = null;
		}
		
		return $obj;
	}
	
	public function Insert()
	{
		// Cannot insert an existing obj!
		// Use ->Clone()->Insert() instead!
		if ($this->_Loaded)
			return false;
		
		if (count($this->_Fields) != 0)
		{
			$fields = $this->_Fields;	
		}else{
			$fields = array_keys($this->_Data);			
		}
		
		$db = $this->GetDb();
		
		$Query = "INSERT INTO " . $this->_TableName . " SET ";
		
		for($i=0;$i < count($fields);$i++)
		{
			$field = $fields[$i];
			if (!isset($this->_Data[$field]))
				continue; // Bad field specified
			
			$data = $this->_Data[$field];
			
			if (isset($this->_MappedLinks[$field]))
			{
				$obj = 	$this->_MappedLinks[$field][0];
				$pkey = 	$this->_MappedLinks[$field][1];
				
				if ($obj->IsLoaded())
				{
					$data = $obj[$pkey];	
				}
			}
			
			$Query.= $field . ' = ' . $db->QuoteSmart($data);
			
			if ($i+1 < count($fields))
				$Query .=", ";	
		}
		if (substr($Query, -2) == ', ')
			$Query = substr($Query, 0, strlen($Query) - 2);
		
		//$Query .= " WHERE " . $this->_Primary . " = " .$db->QuoteSmart($this->_Data[$this->_Primary]);
		//print_r($this->_Data);
		//die($Query);
		$res =  $db->Query($Query);	
		if ($res)
		{
			$this->_Loaded = true;
			
			$res = $db->GetInsertId();
			if ($res == 0)
			{
				return $this->_Data[$this->_Primary];	
			}else{
				$this->_Data[$this->_Primary] = $res;
				return $res;
			}
		}else
			return false;
	}	
	
	public function Find($id, $where = '')
	{
		$thisClass = $this->_Class;		
		$obj = new $thisClass();
		
		return $obj->Load($id, $this->GenerateWhere($where));		
	}
	
	// Same as Find except you can define a custom field to search for	
	public function FindByField($field, $value, $where = '')
	{
		
		$thisClass = $this->_Class;		
		$obj = new $thisClass();
		
		if ($where != '')
			$where = " AND $where";
		
		$where = " $field = " . $this->_Db->QuoteSmart($value) . " " . $where;
		
		$query = "SELECT * FROM " . $this->_TableName . " WHERE $where LIMIT 1";
		$res = $this->_Db->Query($query);
		
		if (!$res)
		{
			return false;
		}
		$data = $this->_Db->GetData();	
		//die($query);
		if (count($data) == 0)
			return false;

		$obj->Load($data[0]);	
		
		return $obj;
	}
	
	// Same as Find except you can define a custom field to search for	
	public function FindManyByField($field, $value, $where = '', $mode = 'AND')
	{
		
		$thisClass = $this->_Class;		
		$obj = new $thisClass();
		
		if ($where != '')
			$where = " $mode $where";
		
		$where = " $field = " . $this->_Db->QuoteSmart($value) . " " . $where;
		
		$query = "SELECT * FROM " . $this->_TableName . " WHERE $where ";
		$res = $this->_Db->Query($query);
		
		if (!$res)
		{
			return false;
		}
		
		$data = $this->_Db->GetData();	
		$res = array();
		
		foreach ($data as $key => $val)
		{
			$obj = new $thisClass();
			$obj->Load($val);	
			$res[] = $obj;
		}
		
		return $res;
	}
	
	public function offsetSet($varName, $varValue) 
	{		
		return $this->__set($varName, $varValue);
	}
	
	public function offsetExists($varName) {
		if (count($this->_Fields) == 0)
		{
			// No fields defined .. Accept everything - WARNING - PLEASE DEFINE THEM :)	
			return isset($this->_Data[$varName]);
		}
		
		// See if the varName is part of this obj
		if (in_array($varName, $this->_Fields))
		{
			return isset($this->_Data[$varName]);			
		}
		
		return false;		
	}
	
	public function offsetUnset($varName) {
		if (count($this->_Fields) == 0)
		{
			// No fields defined .. Accept everything - WARNING - PLEASE DEFINE THEM :)	
			if (isset($this->_Data[$varName]))
				unset($this->_Data[$varName]);
			
			return true;
		}
		
		// See if the varName is part of this obj
		if (in_array($varName, $this->_Fields))
		{
			if (isset($this->_Data[$varName]))
				unset($this->_Data[$varName]);		
			
			return true;
		}
		
		return false;	
	}
	
	public function offsetGet($offset) 
	{
		return $this->__get($offset);
	}
	
	/**
	 * This is method GenerateWhere
	 *
	 * @param mixed $where This is a description
	 * @return mixed This is the return value description
	 * @todo Further implement this function
	 */ 
	protected function GenerateWhere($where)
	{
		if (is_string($where))
			return $where;
		
		return '';	
	}
	
	public function __set($varName, $varValue)
	{
		$orname = $varName;
		$varName = strtolower("Set" . $varName);
		$methods = Z::getPublicMethods($this);
		
		for($i=0;$i < count($methods);$i++)
		{
			$methods[$i] = strtolower($methods[$i]);	
		}
		
		if (in_array($varName, $methods))
		{
			call_user_func(array($this, $varName), $varValue);			
			$this->_Changed = true;
			if (!in_array($varName, $this->_ChangedFields))
				$this->_ChangedFields[] = $varName;
			return;
		}
		
		// see if there is a 'private' method
		if (method_exists($this, $varName))
		{			
			// See if we have full access
			$stack = debug_backtrace(true);
			if (count($stack) < 3)
				return null;
			
			$callerObj = $stack[2]['object'];
			
			if ($this == $callerObj)
			{
				call_user_func(array($this, $varName), $varValue);
				$this->_Changed = true;
				if (!in_array($varName, $this->_ChangedFields))
					$this->_ChangedFields[] = $varName;
				return;
			}
		}
		$varName = $orname;
		
		if (count($this->_Fields) == 0)
		{
			// No fields defined .. Accept everything - WARNING - PLEASE DEFINE THEM :)	
			$this->_Data[$varName] = $varValue;
			$this->_Changed = true;
			if (!in_array($varName, $this->_ChangedFields))
				$this->_ChangedFields[] = $varName;
		}
		
		// See if the varName is part of this obj
		if (in_array($varName, $this->_Fields))
		{
			$this->_Data[$varName] = $varValue;
			$this->_Changed = true;
			if (!in_array($varName, $this->_ChangedFields))
				$this->_ChangedFields[] = $varName;
		}
		
		// Mappings
		if (isset( $this->_MappedLinks[$varName]))
		{			
			$this->_MappedLinks[$varName] = null;
			$this->_Changed = true;
			if (!in_array($varName, $this->_ChangedFields))
				$this->_ChangedFields[] = $varName;
		}
		
		if (isset( $this->_MappingData[$varName]))
		{			
			$infox = $this->_MappingData[$varName];
			foreach ($infox as $index => $info)
			{
				foreach ($info as $fieldName => $info)
				{
					$this->_Changed = true;
					if (!in_array($varName, $this->_ChangedFields))
						$this->_ChangedFields[] = $varName;
					
					if (!is_object($varValue))
						continue;
					
					$obj = $info[0];
					$pkey = $info[1];
					
					if (get_class($obj) != get_class($varValue))
						continue;
					
					$obj = $varValue;
					
					$this->_MappingData[$varName][$index] = array($fieldName =>  array($obj, $pkey));	
					$this->_MappedLinks[$fieldName] = array($obj, $pkey); 
				}
			}
		}
		
		// Many mapping not used here - does not allow adding (yet)
	}
	
	// Copy/paste in each class!
	protected static $_Finder = null;
	public static function Finder()
	{
		$class = __CLASS__;
		
		if (self::$_Finder == null)
			self::$_Finder = new $class();
		
		return self::$_Finder;
	}
	
	public function __get($var)
	{
		$orname = $var;
		$varName = "Get" . $var;
		$methods = Z::getPublicMethods($this);
		for($i=0;$i < count($methods);$i++)
		{
			$methods[$i] = strtolower($methods[$i]);	
		}
		
		// First check global access (public methods)
		if (in_array($varName, $methods))
		{
			return call_user_func(array($this, $varName));
		}
		
		// see if there is a 'private' method
		if (method_exists($this, $varName))
		{
			
			// See if we have full access
			$stack = debug_backtrace(true);
			if (count($stack) < 3)
				return null;
			
			$callerObj = $stack[2];
			if ($this == $callerObj)
			{
				return call_user_func(array($this, $varName));
			}
		}
		
		$varName = $orname;
		if (count($this->_Fields) == 0)
		{
			// No fields defined .. Accept everything - WARNING - PLEASE DEFINE THEM :)	
			if (isset($this->_Data[$varName]))
				return $this->_Data[$varName];
		}
		
		// See if the varName is part of this obj
		if (in_array($varName, $this->_Fields))
		{
			if (isset($this->_Data[$varName]))
				return $this->_Data[$varName];
		}
		
		// Mappings
		if (isset( $this->_MappingData[$varName]))
		{			
			$infox = $this->_MappingData[$varName];
			foreach ($infox as $index=> $info)
			{
				foreach ($info as $fieldName => $info)
				{
					$obj = $info[0];
					$pkey = $info[1];
					
					if (!is_object($obj))
					{
						$obj = new $obj();
						$info[0] = $obj;
						//print_r($obj);
						$this->_MappingData[$varName][$index][$fieldName] = $info;					
					}
					
					if ((($obj->IsLoaded()) && ($this->_MappedLinks[$fieldName])))
						return $obj;
					
					//var_dump($obj->IsLoaded());die();
					$res =  $obj->FindByField($pkey, $this->_Data[$fieldName]);	
					//echo $pkey . '-' .  $this->_Data[$fieldName];die();
					if ($res == false)
						return null;
					
					$this->_MappingData[$varName][$index] = array($fieldName =>  array($res, $pkey));	
					$this->_MappedLinks[$fieldName] = array($res, $pkey); 
					return $res;
				}
			}
		}
		
		// NEW many Mappings
		if (isset( $this->_ManyMappingData[$varName]))
		{			
			$info = $this->_ManyMappingData[$varName];
			foreach ($info as $fieldName => $info)
			{
				$obj = $info[0];
				
				if (!is_object($obj))
				{
					$obj = new $obj();
					$info[0] = $obj;
					$this->_ManyMappingData[$varName][$fieldName] = $info;					
				}

				$pkey = $info[1];
				
				if ((($obj->IsLoaded()) && ($this->_ManyMappedLinks[$fieldName])))
					return $obj;
				
			//	echo get_class($obj)  . '-';
				$res =  $obj->FindManyByField($pkey, $this->_Data[$fieldName]);	
				if ($res == false)
					return null;
				
				$res = new Z_Array($res);
				$this->_ManyMappingData[$varName] = array($fieldName =>  array($res, $pkey));	
				$this->_ManyMappedLinks[$fieldName] = array($res, $pkey); 
				return $res;
			}
		}
		return null;
	}
}	