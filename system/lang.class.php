<?php
	/*
	 * Free Series language management
	 * (c) 2013 by NeoPhoenix <admin@neophoenix.net>
	 */
	
	define('DEFAULT_LANG',1);//de = default language
	class LANG
	{
		static public 	$languageID		= DEFAULT_LANG;
		static private 	$xml			= array();
		static public	$languages		= array();
		
		public function Init()
		{
			$sql	= 'SELECT * FROM fs_languages';
			$result	= SQL::sendQuery($sql);
			while($tmp_lang = mysql_fetch_array($result)) self::$languages[$tmp_lang['id']] = $tmp_lang;
			return self::$languages;
		}
		
		public function getLanguageID($userid)
		{
			if(!intval($userid)) return DEFAULT_LANG;
			$sql	= 'SELECT languageID FROM wcf'.WCF_N.'_user_to_languages WHERE userID='.intval($userid);
			$result = SQL::sendQuery($sql);
			if(!mysql_num_rows($result)) return DEFAULT_LANG;
			self::$languageID = mysql_result($result,0);
			return self::$languageID;
		}
		
		public function loadFile($storage,$filename,$opt_language=-1,$overwrite=false)
		{
			if(isset(self::$xml[$storage]) && (!isset($overwrite) || $overwrite == false)) return false;
			if(isset($storage) && isset($filename))
			{
				$path = './languages/'.$filename.'_'.($opt_language==(-1)?self::$languageID:$opt_language).'.lang.xml';
				if(file_exists($path))
				{
					self::$xml[$storage] = simplexml_load_file($path);
					return true;
				}
			}
			return false;
		}
		
		public function getData($offset)
		{
			if(!isset(self::$xml[$offset])) self::loadFile($offset,$offset);
			return self::$xml[$offset];
		}
	}
?>