<?php
	/*
	 * Free-Series.de validation code
	 * (c) 2013 by NeoPhoenix <admin@neophoenix.net>
	 */
	 
	class VALIDATION extends CORE
	{
		static public $_page = array();

		static public function validatePage()
		{
			self::$_page = array('home','');
			if(isset($_GET['page'])) $parr = explode('.',$_GET['page'].'.');
			else if(isset($_COOKIE['page'])) $parr = explode('.',$_COOKIE['page'].'.');
			else $parr = self::$_page;
			
			if(preg_match('~^[a-zA-Z0-9]+$~',strtolower($parr[0])) && preg_match('~^[a-zA-Z0-9]*$~',strtolower($parr[1])))
			{
				self::$_page[0] = strtolower($parr[0]);
				self::$_page[1] = strtolower($parr[1]);
			}
			
			if(file_exists('./tpl/sites/'.self::$_page[0].'_'.self::$_page[1].'.php.html'))
			{
				if(!self::canAccessPage(array(self::$_page[0],self::$_page[1])))
				{
					self::$_page[0]	= 'error';
					self::$_page[1]	= '404';
				}
			}
			else
			{
				self::$_page[0] = 'error';
				self::$_page[1] = '404';
			}
			self::$_page['file'] = self::$_page[0].'_'.self::$_page[1].'.php.html';
			setcookie('page',self::$_page[0].'.'.self::$_page[1],time()+(60*5));
			return true;
		}
		
		static public function canAccessPage($parr)
		{
			if(USER::hasUserPermission('canAccessPage:'.@$parr[0].'_*')) return true;
			else if(USER::hasUserPermission('canAccessPage:'.@$parr[0].'_'.@$parr[1])) return true;
			else if(USER::hasUserPermission('canAccessPage:*_*')) return true;
			return false;
		}
	}

?>









