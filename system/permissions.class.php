<?php
	/*
	 * Free-Series.de permissions
	 * (c) 2013 by NeoPhoenix <admin@neophoenix.net>
	 */
	 
	class PERMISSIONS extends CORE
	{
		static private $group_permissions = array();
		
		public function assignPermissions()
		{
			$tmp = simplexml_load_file('system/resources/wcf_group_permissions.xml');
			foreach($tmp->group as $group)
			{
				if(!isset(self::$group_permissions[intval($group->id)])) self::$group_permissions[intval($group->id)] = array();
				foreach(explode(',',$group->permissions) as $perm)
				{
					if(!in_array($perm,@self::$group_permissions[intval($group->id)]))
					{
						self::$group_permissions[intval($group->id)][] = $perm;
					}
				}
				foreach($tmp->group as $inherit_group)
				{
					if($inherit_group->id != $group->id && in_array($group->id,explode(',',$inherit_group->inherit)))
					{
						if(!isset(self::$group_permissions[intval($inherit_group->id)])) self::$group_permissions[intval($inherit_group->id)] = array();
						foreach(explode(',',$group->permissions) as $perm)
						{
							if(!in_array($perm,@self::$group_permissions[intval($inherit_group->id)]))
							{
								self::$group_permissions[intval($inherit_group->id)][] = $perm;
							}
						}
					}
				}
			}
			return self::$group_permissions;
		}
		
		private function assignGroupPermissions($groupID)
		{
			
		}
		
		public function hasGroupPermission($groupID,$permission)
		{
			if(!isset(self::$group_permissions[$groupID])) return false;
			return in_array($permission,self::$group_permissions[$groupID]);
		}
		
		public function getGroupPermissions($groupID)
		{
			if(!isset(self::$group_permissions[intval($groupID)])) return array();
			return self::$group_permissions[intval($groupID)];
		}
	}
?>