<?php

// wcf imports
require_once(WCF_DIR.'/lib/data/cronjobs/Cronjob.class.php');
require_once('UpdateSeries.fscr.php');

class FreeSeriesWCFCronjob implements Cronjob
{
	public function execute($data)
	{
		$cronjob = new FreeSeriesCronjob();
		$cronjob->setCategoriesToUpdate(array('series','stats'));
		$cronjob->setSeriesToUpdate(array());
		$cronjob->setTasksToExecute(array());
		$cronjob->execute();
	}
}
	
?>