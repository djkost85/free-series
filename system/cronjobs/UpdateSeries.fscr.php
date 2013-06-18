<?php

	class FreeSeriesCronjob
	{
		private $affected_series	= null;
		private $series				= array();
		private $execute_categories = array();
		private $execute_tasks		= array();
		
		public function __construct()
		{
			$sql	= 'SELECT * FROM fs_series';
			$result	= mysql_query($sql);
			$this->series = array();
			while($tmp_serie = mysql_fetch_array($result)) $this->series[$tmp_serie['id']] = $tmp_serie;
		}
		
		public function setCategoriesToUpdate($categories)
		{
			$this->execute_categories = array_unique($categories);
			return array_unique($this->execute_categories);
		}
		
		public function setTasksToExecute($tasklist)
		{
			$sql			= 'SELECT * FROM fs_cronjob_executions';
			$result			= mysql_query($sql);
			$cronjob_info	= null;
			if(mysql_num_rows($result)) while($tmp_info = mysql_fetch_array($result)) $cronjob_info[] = $tmp_info['name'];
			if(!isset($tasklist) || empty($tasklist) || sizeof($tasklist) <= 0) foreach($cronjob_info as $cronjob_singleinfo) $this->execute_tasks[] = $cronjob_singleinfo;
			else foreach($tasklist as $task) if(in_array($task,$cronjob_info)) $this->execute_tasks[] = $task;
			$this->execute_tasks = array_unique($this->execute_tasks);
			return $this->execute_tasks;
		}
		
		public function setSeriesToUpdate($series_list)
		{
			$this->affected_series = null;
			if(!isset($series_list) || empty($series_list) || sizeof($series_list) <= 0)
			{
				foreach($this->series as $serie)
				{
					if(empty($serie) || intval($serie) == 0) continue;
					$this->affected_series[] = $serie['id'];
				}
			}
			else $this->affected_series = array_unique($series_list);
			return array_unique($this->affected_series);
		}
		
		public function execute()
		{
			$executed_cronjobs = null;
			if(in_array('series',$this->execute_categories))
			{
				if(!empty($this->affected_series))
				{
					foreach(array_unique($this->affected_series) as $serie)
					{
						if(empty($serie) || intval($serie) <= 0) continue;
						if(in_array('recalc.episodes.count',$this->execute_tasks))
						{//Anzahl der Episoden ermitteln
							$sql	= 'SELECT SUM(episodes) FROM fs_series_seasons WHERE sid='.intval($serie);
							$result	= mysql_query($sql);
							mysql_query('UPDATE fs_series SET episodes='.intval(@mysql_result($result,0)).' WHERE id='.intval($serie));
							$executed_cronjobs[] = 'recalc.episodes.count';
						}
						if(in_array('recalc.favorites.count',$this->execute_tasks))
						{//Anzahl der Favoriten ermitteln
							$sql	= 'SELECT COUNT(*) FROM fs_user_favorites WHERE sid='.intval($serie);
							$result	= mysql_query($sql);
							mysql_query('UPDATE fs_series SET favorites='.intval(@mysql_result($result,0)).' WHERE id='.intval($serie));
							$executed_cronjobs[] = 'recalc.favorites.count';
						}
						if(in_array('recalc.series.metascore',$this->execute_tasks))
						{//Metascore der Serie berechnen
							$sql	= 'SELECT COUNT(*) FROM fs_series_seasons WHERE metascore>0 AND sid='.intval($serie);
							$result	= mysql_query($sql);
							$total_count = intval(@mysql_result($result,0));
							$sql	= 'SELECT SUM(metascore) FROM fs_series_seasons WHERE metascore>0 AND sid='.intval($serie);
							$result	= mysql_query($sql);
							$total_metascore = intval(@mysql_result($result,0));
							if($total_count > 1) $metascore = round($total_metascore/$total_count);
							else $metascore = 0;
							mysql_query('UPDATE fs_series SET metascore='.$metascore.' WHERE id='.intval($serie));
							$executed_cronjobs[] = 'recalc.series.metascore';
						}
						if(in_array('recalc.series.userrating',$this->execute_tasks))
						{//Userbewertung der Serie berechnen
							$sql	= 'SELECT COUNT(*) FROM fs_series_user_rating WHERE rating>0 AND sid='.intval($serie);
							$result	= mysql_query($sql);
							$total_count = intval(@mysql_result($result,0));
							$sql	= 'SELECT SUM(rating) FROM fs_series_user_rating WHERE rating>0 AND sid='.intval($serie);
							$result = mysql_query($sql);
							$total_rating = intval(@mysql_result($result,0));
							if($total_count > 0) $rating = round($total_rating/$total_count);
							else $rating = 0;
							mysql_query('UPDATE fs_series SET userrating='.intval($rating).' WHERE id='.intval($serie));
							$executed_cronjobs[] = 'recalc.series.userrating';
						}
					}
				}
			}
			if(in_array('stats',$this->execute_categories))
			{
				if(in_array('update.series.count',$this->execute_tasks))
				{//Anzahl der Serien aktualisieren
					$sql	= 'SELECT COUNT(*) FROM fs_series';
					$result	= mysql_query($sql);
					mysql_query('UPDATE fs_stats SET wert='.intval(mysql_result($result,0)).' WHERE name="fs.series.count"');
					$executed_cronjobs[] = 'update.series.count';
				}
				if(in_array('update.seasons.count',$this->execute_tasks))
				{//Anzahl der Staffeln aktualisieren
					$sql	= 'SELECT COUNT(*) FROM fs_series_seasons';
					$result	= mysql_query($sql);
					mysql_query('UPDATE fs_stats SET wert='.intval(mysql_result($result,0)).' WHERE name="fs.seasons.count"');
					$executed_cronjobs[] = 'update.seasons.count';
				}
				if(in_array('update.episodes.count',$this->execute_tasks))
				{//Anzahl der Episoden aktualisieren
					$sql	= 'SELECT SUM(episodes) FROM fs_series_seasons';
					$result	= mysql_query($sql);
					mysql_query('UPDATE fs_stats SET wert='.intval(mysql_result($result,0)).' WHERE name="fs.episodes.count"');
					$executed_cronjobs[] = 'update.episodes.count';
				}
				if(in_array('update.links.count',$this->execute_tasks))
				{//Anzahl der Links aktualisieren
					$sql	= 'SELECT COUNT(*) FROM fs_series_links';
					$result	= mysql_query($sql);
					mysql_query('UPDATE fs_stats SET wert='.intval(mysql_result($result,0)).' WHERE name="fs.episodes.links.count"');
					$executed_cronjobs[] = 'update.links.count';
				}
				if(in_array('update.accounts.count',$this->execute_tasks))
				{//Anzahl der Accounts updaten
					$sql	= 'SELECT COUNT(*) FROM wcf'.WCF_N.'_user';
					$result	= mysql_query($sql);
					mysql_query('UPDATE fs_stats SET wert='.intval(mysql_result($result,0)).' WHERE name="fs.accounts.count"');
					$executed_cronjobs[] = 'update.accounts.count';
				}
			}
			if(!empty($executed_cronjobs))
			{
				$executed_cronjobs = array_unique($executed_cronjobs);
				foreach($executed_cronjobs as $cronjob) mysql_query('UPDATE fs_cronjob_executions SET timestamp='.time().' WHERE name="'.mysql_real_escape_string($cronjob).'"');
				return $executed_cronjobs;
			}
			else return array();
		}
	}
	
?>