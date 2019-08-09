<?php
include('config.php');

// Debug
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

$url = explode("/",$_SERVER['REQUEST_URI']);

// Check if commander is valid. If the commander is not valid it dies with a 404 message. This is also used to prevent URL scanning.
if ( !in_array($url[1], $validCommanders) ) {
	
	writeFile( 'logs/', 'failAccess.txt', "\n".time()." Failed access attempt under commander name ".$url[1]." User IP: ".getUserIpAddr(), 'a' );
	header("HTTP/1.0 404 Not Found");
	die('404');

} else { // Process the request.

	logCommand($url);
	if ( $url[2] == 'set' ) { 
		$command = setCommand($url);
		echo "{'result':'success','message':'".$command."'}";
		exit;
	}

	if ( $url[2] == 'get' ) { 
		echo getCommand($url);
		exit;
	}

	if ( $url[2] == 'log' ) {
		echo getLog($url);
		exit;
	}

	// Request is invalid so faile. 
	echo "{'result':'fail','message':'invalid URL structure.'}";

}

/**
 * Writes content to a files. 
 * @param  string $path      Path from root folder. 
 * @param  string $file      File name.
 * @param  string $content   String to write.
 * @param  string $operation Whether to replace the content ('w') or append to the content ('a')
 * @return null
 */
function writeFile( $path, $file, $content, $operation = 'w' ) {

	$fp = fopen( $path."/".$file, $operation);
	fwrite($fp, $content);  
	fclose($fp);



}

/**
 * Read a file.
 * @param  string  $path   Path from root folder. 
 * @param  string  $file   File name.
 * @param  boolean $delete Whether to delete the message directly after reading it.
 * @return string          Content of file.
 */
function read( $path, $file, $delete = true ) {

	if ( !file_exists($path."/".$file)){
		return "";
	}

	$fileContent = file_get_contents($path."/".$file);
	
	if( $delete ) {

		$fp = fopen( $path."/".$file, 'w');
		fwrite($fp, "");  
		fclose($fp); 

	} 

	return $fileContent;

}

/**
 * Gets IP of user. 
 * @return string IP address.
 */
function getUserIpAddr(){
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/**
 * Create a new command.
 * @param string $url raw php url object.
 * @return string Command logged.
 */
function setCommand($url){

	if ( isset($url[5] )) {
		$command = "{'".$url[4]."':'".$url[5]."'}";
	} else {
		$command = "{'command':'".$url[4]."'}";
	}

	writeFile('commands/',$url[3].'.txt',$command);
	return $command;

}

/**
 * Log a new command. This appends the command to a log file in the format 'user_bot.txt'
 * @param string $url raw php url object.
 * @return string Command logged.
 */
function logCommand($url){
	$command = "{'timestamp':'".time()."','ip':'".getUserIpAddr()."','command':'".json_encode( $url )."'},";
	writeFile('logs',$url[1]."_".$url[3].'.txt',"\n".$command,'a');
	return $command;
}

/**
 * Get a command.
 * @param string $url raw php url object.
 * @return string Command
 */
function getCommand($url){

	return read('commands',$url[3].'.txt');

}

/**
 * Returns a log of all a bots commands.
 * @param string $url raw php url object.
 * @return string Command
 */
function getLog($url){

	return "[".read('logs',$url[1]."_".$url[3].'.txt',false)."]";

}

?>