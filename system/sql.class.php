<?php
	/*
	 * Free-Series.de sql class
	 * (c) 2013 by NeoPhoenix <admin@neophoenix.net>
	 */
	 
	class SQL extends CORE
	{
		//Variables
		static private $sql_handle = NULL;
		static public $queries_sent = 0;
		
		//Functions
		public function Init($config_file)
		{
			include($config_file);
			self::$sql_handle = @mysql_connect($dbHost,$dbUser,$dbPassword);
			if(!self::$sql_handle)
			{
				self::$sql_handle = NULL;
				return false;
			}
			mysql_select_db($dbName);
			mysql_set_charset('utf8');
			return true;
		}
		
		public function sendQuery($query)
		{
			self::$queries_sent += 1;
			return mysql_query($query);
		}
		
		public function __destruct()
		{
			if(self::$sql_handle)
			{
				mysql_close(self::$sql_handle);
				return true;
			}
			return false;
		}
		
		public function checkConnection()
		{
			if(self::$sql_handle) return true;
			return false;
		}
	}

?>