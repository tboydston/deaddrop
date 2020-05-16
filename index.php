<?php
include('config.php');

$url = explode("/",$_SERVER['REQUEST_URI']);

//@TODO add proper URL format filtering.
if ( empty($url[1]) || isset($url[6]) ){ die('404'); }

// Sanitize URL.
for ($i=0; $i < count($url)-1; $i++) { 
	$url[$i] = filter_var($url[$i], FILTER_SANITIZE_STRING);
}

// Check if commander is valid. If the commander is not valid it dies with a 404 message. This also pervent using the service for bot command and control.
if ( is_array($validCommanders) && !in_array($url[1], $validCommanders ) ) {
	
	writeFile( 'logs/', 'failAccess.txt', "\n".time()." Failed access attempt under commander name ".$url[1]." User IP: ".getUserIpAddr(), 'a' );
	header("HTTP/1.0 404 Not Found");
	die('404'); 

} else if ( ( is_array($validCommanders) && in_array($url[1], $validCommanders ) ) || $validCommanders == "*" ) { // Process the request.


	logCommand($url);

	if ( !isset($url[2]) ){
		echo formatResult('invalid URL structure.',$status='fail');
		exit;
	}

	if ( $url[2] == 'set' ) { 
		$command = setCommand($url);
		echo formatResult($command);
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
	echo formatResult('invalid URL structure.',$status='fail');


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
		fwrite($fp, formatResult("",$status='success'));  
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

	$command = new stdClass();

	if ( isset($url[5] )) {
		$command->{$url[4]} = $url[5];
	} else {

		if ( !isset($url[4]) ) {
			$command->command = "";
		} else {
			$command->command = $url[4];
		}
		
	}

	$command->result = "success";

	$command = json_encode($command);


	writeFile('commands/',$url[3].'.txt',$command);
	return $command;

}

/**
 * Log a new command. This appends the command to a log file in the format 'user_bot.txt'
 * @param string $url raw php url object.
 * @return string Command logged.
 */
function logCommand($url){
	$command = new stdClass();
	$command->timestamp = time();
	$command->ip = getUserIpAddr();
	$command->command = implode(",", $url ); 
	$command = json_encode( $command );
	writeFile('logs',$url[1]."_".$url[3].'.txt',$command.",",'a');
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
	$result = new stdClass();
	$result->result = "success";
	$result->log = "[".rtrim( read('logs',$url[1]."_".$url[3].'.txt',false), "," )."]";
	return json_encode($result);
}

function formatResult($msg,$status='success'){
	$result = new stdClass();
	$result->result = $status;
	$result->message = $msg;
	return json_encode($result);

}

?>