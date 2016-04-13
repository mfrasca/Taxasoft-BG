<?php
error_reporting(E_ALL ^ E_NOTICE);

include('guest.pass.php');

$db= $_REQUEST["db"] or die( "Error: db=databaseName missing");
$id= $_REQUEST["id"] or die( "Error: id=recID missing");
$table= $_REQUEST["table"] or die( "Error: table=tableName missing");

function writeLog( $message, $time)
{
	$file_log= fopen( "showImg.log","a") or
		die( "Error - log file 'gallery.log' couldn't be openend");

	if( $time)
		$bytes_written = fwrite( $file_log, date('Ymd G:i:s')." $user\t$message\n") or
			die( "Error - debug log file couldn't be written: $message");
	else
		$bytes_written = fwrite( $file_log, "- $message\n") or
			die( "Error - debug log file couldn't be written: $message");

	fclose( $file_log);
}

if (!@mysql_connect( $cfgHost, $cfgUser, $cfgPass))
	die( "<h2>MySQL connectie met host($host) is mislukt</h2>");

mysql_select_db( $db);
$query= "SELECT * FROM $table WHERE id='$id';";
$result = mysql_query( $query) or die("<br>$query<br><br>".mysql_error());

if( !$row = mysql_fetch_array($result))
	die( "ERROR: no mach in database [id='$id']");
else
{
	if( isset( $_GET['field']) )
		$path= $row["{$_GET['field']}"] == $row['url'] ? $row['path'].$row['url'] : $row["{$_GET['field']}"];
	else
		$path= $row['path'].$row['url'];

	if( isset( $_GET['path']) )
		$path= $_GET['path'].$path;

	if( !file_exists($path) )
		die("Error: Image file($path) not found [id='$id']");
}

if( isset( $_REQUEST["width"] ))
{
	$max  = $_REQUEST["width"];
	$image= imagecreatefromjpeg( $path);					//** Afbeelding aanmaken
	$width=ImageSx(  $image);              					//** Original picture width is stored
	$height=ImageSy( $image);             					//** Original picture height is stored

	if( $width > $height)
	{
		$n_width= $max;
		$n_height= round( $max/$width * $height);
	}
	else
	{
		$n_height= $max;
		$n_width= round( $max/$height * $width);
	}

	if( $newimage= imagecreatetruecolor( $n_width, $n_height))
	{
		if( imageCopyResized( $newimage,$image,0,0,0,0,$n_width,$n_height,$width,$height) )
		{
			header( "content-type: image/jpeg");			//** Browser vertellen dat we een afbeelding gaan laten zien
			imagejpeg( $newimage);							//** Afbeelding tonen
			imagedestroy( $newimage);						//** Afbeeldingen uit geheugen verwijderen
		}
		else
			writeLog( "Server Error - imageCopyResized file ", 0);
	}
	else
		writeLog( "Server Error - imagecreatetruecolor file $path width($width) height($height) n_width($n_width) n_height($n_height)", 0);

	imagedestroy($image);									//** Afbeeldingen uit geheugen verwijderen
}
else
{
	$image= imagecreatefromjpeg( $path);
	header("content-type: image/jpeg");				/* Browser vertellen dat we een afbeelding gaan laten zien */
	imagejpeg ($image);								/* Afbeelding tonen 					*/
	imagedestroy($image);							/* Afbeeldingen uit geheugen verwijderen */
}

?>
