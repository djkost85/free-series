<?php
	/*
	 * Free-Series.de core code
	 * (c) 2013 by NeoPhoenix <admin@neophoenix.net>
	 */
	
	header("Content-Type: text/html; charset=utf-8");
	require_once('config.inc.php');
	require(RELATIVE_WBB_PATH.'config.inc.php');//wbb config
	require(RELATIVE_WBB_PATH.RELATIVE_WCF_DIR.'config.inc.php');//wcf config
	
	class CORE
	{
		static public $version = '1.0.0';
		static public $called = 1;
		
		public function Init()
		{
			if(!SQL::Init(RELATIVE_WBB_PATH.RELATIVE_WCF_DIR.'config.inc.php'))//sql connection initialisieren
			{
				exit('System failure: e_0x1');
			}
			LANG::Init();
			PERMISSIONS::assignPermissions();
			USER::Init();
			if(USER::trySessionAuth()) LANG::getLanguageID(USER::$user['userID']);
			LANG::loadFile('main','main');
			VALIDATION::validatePage();
		}
	}
	
	require_once('sql.class.php');
	require_once('user.class.php');
	require_once('permissions.class.php');
	require_once('lang.class.php');
	require_once('validation.class.php');
?>