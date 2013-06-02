<?
require_once("Predis/Autoloader.php");

function publishToRedis($message, $channel = "/todo") {
			
	Predis\Autoloader::register();

	$redis = new Predis\Client();
	
	$redis->publish($channel, $message);
		
}
?>