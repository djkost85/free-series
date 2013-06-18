<?php
	/*
	 * Free-Series.de user class
	 * (c) 2013 by NeoPhoenix <admin@neophoenix.net>
	 */
	 
	class USER extends CORE
	{
		//Variables
		static public $user = array('userID'=>0);
		
		//Functions
		public function Init()
		{
			self::$user['userGroups']		= self::getUserGroups(self::$user['userID']);
			self::$user['userPermissions']	= self::getUserPermissions();
			if(isset($_COOKIE['fs_token']) && preg_match('~^[0-9a-zA-Z]+$~',$_COOKIE['fs_token'])) self::$user['fs_token'] = $_COOKIE['fs_token'];
			else
			{
				self::$user['fs_token'] = md5(time().$_SERVER['REMOTE_ADDR']);
				setcookie('fs_token',self::$user['fs_token']);
			}
		}
		
		public function trySessionAuth()
		{
			if(isset($_COOKIE['wcf_userID']) && isset($_COOKIE['wcf_password']))
			{
				$query = "SELECT * FROM wcf".WCF_N."_user WHERE userID='".intval($_COOKIE['wcf_userID'])."'";
				$result = SQL::sendQuery($query);
				if(mysql_num_rows($result))
				{
					$row = mysql_fetch_array($result);
					if($row['password'] == sha1($row['salt'].$_COOKIE['wcf_password']))
					{//Correct cookie for login
						self::$user['userID']			= intval($_COOKIE['wcf_userID']);
						self::$user['userName']			= $row['username'];
						self::$user['userGroups']		= self::getUserGroups(intval($_COOKIE['wcf_userID']));
						self::$user['userPermissions']	= self::getUserPermissions();
						return 1;
					}
					else
					{
						unset($_COOKIE['wcf_userID']);
						unset($_COOKIE['wcf_password']);
						unset(self::$user['userName']);
						self::$user['userID'] = 0;
						setcookie('wcf_userID','');
						setcookie('wcf_password','');
						return 0;//Falsches PW
					}
				}
			}
			return 0;//Nicht eingeloggt
		}
		
		public function isLoggedIn()
		{
			return (intval(self::$user['userID'])?true:false);
		}
		
		public function hasUserPermission($perm)
		{
			return (in_array($perm,self::$user['userPermissions'])?true:false);
		}
		
		public function actionAuth()
		{
			if(isset($_GET['fs_token']) && !empty($_GET['fs_token']) && !strcmp($_GET['fs_token'],self::$user['fs_token'])) return true;
			else if(isset($_POST['fs_token']) && !empty($_POST['fs_token']) && !strcmp($_POST['fs_token'],self::$user['fs_token'])) return true;
			else return false;
		}
		
		private function getUserGroups()
		{
			$tmp	= array();
			$tmp[]	= 1;
			$sql	= 'SELECT groupID FROM wcf'.WCF_N.'_user_to_groups WHERE userID="'.intval(self::$user['userID']).'"';
			$result = SQL::sendQuery($sql);
			if(mysql_num_rows($result)) while($row = mysql_fetch_row($result)) if(!in_array($row[0],$tmp)) $tmp[] = $row[0];
			return $tmp;
		}
		
		private function getUserPermissions()
		{
			$tmp = array();
			foreach(self::$user['userGroups'] as $group)
			{
				$perms = PERMISSIONS::getGroupPermissions($group);
				foreach($perms as $perm) if(!in_array($perm,$tmp)) $tmp[] = $perm;
			}
			return $tmp;
		}
	}
?>