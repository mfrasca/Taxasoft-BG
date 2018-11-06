<?php
error_reporting( E_ALL ^ E_NOTICE);
//error_reporting( 0);

if( file_exists( 'index1pass.php'))
	include('index1pass.php');
else
	include('guest.pass.php');

$db= $_REQUEST["db"] or die( "Error: db=databaseName missing");
$id= $_REQUEST["id"] or die( "Error: id=recID missing");
$table= $_REQUEST["table"] or die( "Error: table=tableName missing");

$db_link = mysqli_connect( $cfgHost, $cfgUser, $cfgPass, $db);

if (mysqli_connect_errno())
	die( "<h2>MySQL connectie met host($host) is mislukt</h2>");

$result = query( "SELECT * FROM $table WHERE id='$id'", "SI 31");

if( !$row = mysqli_fetch_array($result))
	die( "ERROR: no mach in database [id='$id']");
else
{
	if( isset( $_GET['field']) )
		$path= $row["{$_GET['field']}"] == $row['url'] ? $row['path'].$row['url'] : $row["{$_GET['field']}"];
	else
		$path= $row['path'].$row['url'];

	if( isset( $_GET['imageRoot']) )
		$path= $_GET['imageRoot'].$path;

	if( isset( $_GET['path']) )
		$path= $_GET['path'].$path;

	if( !file_exists($path) )
		die("Error: Image file($path) not found [id='$id']");
}

$Orientation=  0;

if( $array= @exif_read_data( $path))
{
	switch( (int)$array['Orientation'])
	{
		case 3: $Orientation= 180; break;
		case 6: $Orientation= -90; break;
		case 8: $Orientation= 90; break;
		default:$Orientation=  0; break;
	}
}

if( isset( $_REQUEST["width"] ) && (int)$_REQUEST["width"])
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
		if( imagecopyresampled( $newimage,$image,0,0,0,0,$n_width,$n_height,$width,$height) )
//		if( imageCopyResized( $newimage,$image,0,0,0,0,$n_width,$n_height,$width,$height) )
		{
			if( $Orientation)								//** Rotate image if necessary
				$newimage = imagerotate( $newimage, $Orientation, 0) or die( 'rotation of image failed');

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

	if( $Orientation)								//** Rotate image if necessary
		$image = imagerotate( $image, $Orientation, 0) or die( 'rotation of image failed');

	header("content-type: image/jpeg");				/* Browser vertellen dat we een afbeelding gaan laten zien */
	imagejpeg ($image);								/* Afbeelding tonen 					*/
	imagedestroy($image);							/* Afbeeldingen uit geheugen verwijderen */
}

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

function query( $query, $line='no line number', $debugLevel=0)
{
	global $debug, $db_link;

	$result= mysqli_query( $db_link, $query ) or die( "<font color='red'>($line) ".$query."<br></font>".mysqli_error( $db_link));

	if( $debugLevel)
		echoDebug( $query."<br>", $debugLevel);

	return( $result);
}

?>
