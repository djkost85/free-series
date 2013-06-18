<?php
	/*
	 * Free-Series.de system code
	 * (c) 2013 by NeoPhoenix <admin@neophoenix.net>
	 */

	//Initializing core systems
	ob_start();
	$execution_start = microtime(true);
	require_once('system/core.class.php');
	CORE::Init();

	//Output
	include_once('tpl/header.tpl.html');
			echo '<div id="page">
				<div class="container">
					<h2>'.LANG::getData('main')->categories->{VALIDATION::$_page[0]}.'</h2>
				</div>
			</div>
			<div id="body">';
				require_once('tpl/analytics.php.html');
				if(file_exists('tpl/sites/'.VALIDATION::$_page['file'])) include('tpl/sites/'.VALIDATION::$_page['file']);
				else include('tpl/sites/error_404.php.html');
			echo '</div>';
			include_once('tpl/footer.tpl.html');
			?>
			<script src="js/jquery.fancybox.pack.js"></script>
			<script src="js/app.js"></script>
			<script>
			jQuery(document).ready(function()
			{
				App.init();
			});
			</script>
			<script>
			$('.tooltip-activate').tooltip()
			</script>
		</body>
<?php
	ob_end_flush();
?>