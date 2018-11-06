<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

if( file_exists( 'index1pass.php'))
	include('index1pass.php');
else
	include('guest.pass.php');

include('index1Form.php');

	$sort= $find= $start= $combine= '';
	$user= FALSE;
	$debug= isset( $_GET['debug']) ? $_GET['debug'] : 0;
	$dbase = strtolower( _REQUEST('dBase'));

	if( is_dir( $dbase))
		$dbase.= '/default';

	if( !$dbase && file_exists('default.xml') )
		$dbase = 'default';

	echoDebug( "dBase= $dbase");

	if( !$dbase || !file_exists( $dbase.'.xml') )
		die( "<p align='center'>Non existing configuration ($dbase), check the arguments</a><br></p>");
	else
		$cfg = simplexml_load_file($dbase.'.xml') or die ("Unable to load XML file!");

	if( isset( $_GET['showcfg']))
	{
		print_r($cfg);
		die();
	}

	if( $cfg['include'] != '')
	{
		if( file_exists($cfg['include']) )
			include_once( $cfg['include']);
		else
			die( "ERROR: Addiditional Function file not found: '{$cfg['include']}'");
	}

	if( _GET('tableName'))	//* find record by table name
		$tableNr= getTableNumber( _GET('tableName'));
	else
	{
		if( _REQUEST('tableNr'))
			$tableNr= (int)_REQUEST('tableNr');
		else if( isset( $cfg['defaultTableNr']) )
			$tableNr= (int)$cfg['defaultTableNr'];
		else
			$tableNr= 0;
	}

	$tableName= $cfg->table[$tableNr]['name'] or die("<h2>Table Name Error</h2>");

	if( _GET('lines'))	//** overule list lines settings 	**//
	{
		setcookie("lines", _GET('lines'));
		$listLines = (int)_GET('lines');
	}

	if( !isset( $_GET['noheader']))
	{
		$iconLink= $cfg['shortcutIcon'] ? "<link REL='SHORTCUT ICON' HREF='{$cfg['shortcutIcon']}'>" : '';
		$title= _GET('id') ? $tableName.' '._GET('id') : $tableName;

		echo "
<!DOCTYPE html>
<html>
	<head>
		<style type=\"text/css\">
			@import \"index1.css?".(time())."\";
		</style>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=ISO8859-1\">
		$iconLink
		<title>$dbase ($title)</title>

		<script type=\"text/javascript\" src=\"https://code.jquery.com/jquery-latest.min.js\"></script>
		<script type=\"text/javascript\">
		(function($){
			$(document).ready(function(){
				$(window).keypress(function(event) {
					if (!(event.which == 115 && event.ctrlKey) && !(event.which == 19)) return true;
					$(\"#container form\").submit();
					event.preventDefault();
					return false;
				});

			});
		})(jQuery);
		</script>

		<script type=\"text/javascript\">
		$(document).ready(function() {
			$('tr.odd,tr.even').click(function() {
				var href = $(this).find(\"a\").attr(\"href\");
				if(href) {
					window.location = href;
				}
			});
		});
   		</script>
	</head>";

	$containerStyle="width:".( isset( $cfg['containerStyleWidth']) ? $cfg['containerStyleWidth'] : "1200px").
		";height:".( isset( $cfg['containerStyleHeight']) ? $cfg['containerStyleHeight'] : "750px").";";


	if( isset( $_GET['test']))
		print_r( $cfg);

	if( $cfg['bgrImage'])
		echo "
	<body background='".$cfg['bgrImage']."'>
		<div id='container'  style='$containerStyle'>";
	else
		echo "
	<body>
		<div id='container' style='$containerStyle'>";

		if( $cfg['menu'])
		{
			include( $cfg['menu']);
			echo "
		  <div id='content'>\n";
		}
	}

	if( $cfg['header'])
		include( $cfg['header']);

	if( $cfg['include_mysqli'])
		include( 'index1mysqli.php');

//	print_r($cfg);

	$db_link = mysqli_connect( $cfgHost, $cfgUser, $cfgPass, $cfg['name']);

	if (mysqli_connect_errno())
	{
		if( $debug)
			echo " - '$host', '$cfgUser', ''";

		die( "<h2>MySQL connectie met host is mislukt</h2>");
	}

	if( isset( $_GET['create']))
		query( "CREATE DATABASE IF NOT EXISTS ".$cfg['name']." default character set 'latin2' default collate 'latin2_general_ci'", 'I 148', 2);

	$query= '';
	$IP=$_SERVER['REMOTE_ADDR'];

	//* LOGIN PROCEDURE
	//*
	if( isset( $cfg['forceLogin'] ))
	{
		if( isset( $_GET['create']))
		{
			query( "CREATE TABLE IF NOT EXISTS `user` (`id` tinyint(4) NOT NULL AUTO_INCREMENT,`name` varchar(20) NOT NULL,
  			`email` varchar(50) NOT NULL,`password` varchar(10) NOT NULL,`ip` varchar(20) NOT NULL, `access` bigint(20) NOT NULL,
  			`last` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  			`edit` tinyint(1) NOT NULL,`add` tinyint(1) NOT NULL,`lines` tinyint(4) NOT NULL, `debug` tinyint(4) NOT NULL, PRIMARY KEY (`id`))
  			ENGINE=InnoDB DEFAULT CHARSET=latin2", '167');
  		}

		$passError= isset( $cfg['passAtempt']) ? $cfg['passAtempt'] : 'Login Atempt';

		if( _POST( $dbase."_user"))										//* login form sent user/password
		{
			if( strpos( _POST( $dbase."_user"), '=') || strpos( _POST($dbase."_user"), ';') || strpos( _POST($dbase."_password"), ';' ) || strpos( _POST($dbase."_user"), "'") || strpos( _POST($dbase."_password"), "'" ) )
				$passError= 'Non allowed tokens used';
			else if( $row = mysqli_fetch_array( query( "SELECT * FROM user WHERE name='"._POST($dbase."_user")."' && password='"._POST($dbase."_password")."'", '182')))
			{
				query( "UPDATE user SET ip='' WHERE ip='$IP'", '175');
				query( "UPDATE user SET ip='$IP' WHERE id='{$row['id']}'", '176');
				query( "UPDATE lastaccess SET access=0, last=".time()." WHERE ip='$IP'", '186');
				$user = mysqli_fetch_array( query( "SELECT * FROM user WHERE id='{$row['id']}'", '187'));
			}
			else
				$passError= isset( $cfg['passError']) ? $cfg['passError'] : 'Wrong User and or Password';
		}
		else
		{
			if( $user = mysqli_fetch_array( query( "SELECT * FROM user WHERE ip='$IP'", '184')))
			{
				$query= "";
				$lastDay= substr( $user['last'], 8, 2);
				$currDay= date( 'd');

				if( _GET('changePassword'))
					$query.= "password='"._GET('changePassword')."', ";

				if( _GET('user'))
					echo "User: <b>{$user['name']}</b> rights: edit={$user['edit']} add={$user['add']} lines: {$user['lines']} access: {$user['access']}<br>\n";

				if( _GET('lines'))
				{
					$query.= "`lines`='"._GET('lines')."', ";
					$user['lines']= _GET('lines');
				}

				if( isset( $_GET['debug']))
				{
					$query.= "`debug`='$debug', ";
					$user['debug']= $debug;
				}
				else
					$debug= $user['debug'];

				if( isset( $_GET['login']) || isset( $_GET['forget']) || ($lastDay != $currDay && !$user['keep']))
				{
					query( "UPDATE user SET ip='' WHERE ip='$IP'", '210');
					$query.= "ip='', ";
					$user= FALSE;
				}

				if( $query != '')
					writeLog( "Change Settings: $query", 1);

				if( $user['id'])
					query( "UPDATE user SET $query access=access+1 WHERE id='{$user['id']}'", '215');
			}

			if( isset( $user['admin']) && _GET('user') )
			{
				writeLog( "Admin($IP) add/alter user("._GET('user').") and password("._GET('password').") and edit("._GET('edit').") and password("._GET('password').")", 1);

				if( $row = mysqli_fetch_array( query( "SELECT * FROM user WHERE name='"._GET("user")."'", '223')))
					query( "UPDATE user SET password='"._GET('password')."',`edit`='"._GET('edit')."', `add`='"._GET('add')."' WHERE id='{$row['id']}'", '224');
				else if( _GET('password'))
					query( "INSERT INTO user SET name='"._GET('user')."', password='"._GET('password')."', `edit`='"._GET('edit')."', `add`='"._GET('add')."'", '226');
				else
					die( "ADMIN add user or set password etc.: user=name&password=nameIt&edit=1&add=1");
			}
		}

		if( !$user)
		{
			//* lastaccess table is to check the number of login atempts and the speed this is happening
			//*
			$maxAtempt= 10;

			if( isset( $_GET['create']))
			{
				query( "CREATE TABLE IF NOT EXISTS `lastaccess` (`id` tinyint(4) NOT NULL AUTO_INCREMENT, `ip` varchar(20) NOT NULL,
					`access` tinyint(4) NOT NULL, `first` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					`last` bigint(20) NOT NULL, PRIMARY KEY (`id`))
					ENGINE=InnoDB DEFAULT CHARSET=latin2", '236');
			}

			if( $row = mysqli_fetch_array( query( "SELECT * FROM lastaccess WHERE ip='$IP'", '238') ) )
			{
				if( $row['last'] && ($sec= time()-$row['last']) < 3)			//* too fast (less than 3 seconds in between atempts)
					die( "<font color='red'>Error in access atempt</font>");

				if( $sec > 15*60)
					query( "UPDATE lastaccess SET access=0, last=".time()." WHERE id={$row['id']}", '244');
				else
					query( "UPDATE lastaccess SET access=access+1, last=".time()." WHERE id={$row['id']}",'246' );

				if( ($atempt=$row['access']+1) > $maxAtempt)
					die( "<font color='red'>Max. number of access failures reached.</font>");
			}
			else
			{
				$atempt= 0;
				query( "INSERT INTO lastaccess SET ip='$IP', access=0", '254');
			}

			writeLog( "Get User("._POST($dbase.'_user').") and password("._POST($dbase.'_password').")", 1);
			$continueTxt= isset( $cfg['continue']) ? $cfg['continue'] : 'Continue';
			$userTxt= isset( $cfg['userTxt']) ? $cfg['userTxt'] : 'User Name: ';
			$passwordTxt= isset( $cfg['passwordTxt']) ? $cfg['passwordTxt'] : 'Password: ';

			die( "<h2>TAXASOFT© MySQL Database Interface<br>
				<small>{$cfg['forceLogin']} '$dbase'</small> </h2>
				<p class='error'>$passError ($atempt/$maxAtempt)</p>
				<form method='post' action='?dBase=$dbase' enctype='multipart/form-data'>
					<table>
						<tr><td>$userTxt</td><td><input size='20' name='".$dbase."_user' value='"._POST("$dbase_user")."' type='text'></td></tr>
						<tr><td>$passwordTxt</td><td><input size='20' name='".$dbase."_password' value='' type='password'></td></tr>
						<tr><td> </td><td>
							<input name='verifyPassword' value='$continueTxt' type='submit'>
							<input type='hidden' name='dBase' value='$dbase'></td></tr>
					</table>
				</form>
			</body>
			</html>");
		}
	}		//** End Login procedure	**//

	if( isset( $_GET['version']) || isset( $_GET['info']))
		versionInfo();
	else if( _GET('indexTable'))
		indexTable( _GET('indexTable'));

	if( isset( $_GET['list']))								// ** List all .xml files in folder
	{
		$array= scandir ( './', 0);
		echo "<h2>Database entry listing</h2>
			<table><tr><td>\n";
		$cnt= 0;

		foreach( $array as $name)
		{
			if( substr( $name, strlen($name)-4) == '.xml')
			{
				$dBase= substr( $name, 0, strlen($name)-4);

				if( $cnt%20 == 0 && $cnt)
					echo "</td>\n<td>";

				echo ++$cnt." <a href='?dBase=$dBase'>$dBase</a><br>\n";
			}
		}

		die( "</td></tr></table>");
	}
	else if( _GET('function'))
		executeAction( _GET('function'));					// ** include file xxxTools	is needed
	else if( isset( $_GET['listLog']))
		listLog( "log/$dbase.log");

	if( isset( $user['lines']))
		$listLines = $user['lines'] ? $user['lines'] : 30;
	else if( _REQUEST('lines'))
		$listLines = (int)_REQUEST('lines');
	else if( isset( $cfg['listLines']) && (int)$cfg['listLines'] > 0)
		$listLines= $cfg['listLines'];
	else
		$listLines= 1000;

	$maxFieldLen= 	(int)$cfg['maxFieldLen'];
	$unralatedTables=$cfg['link'];

	$fieldNr= 		$cfg->table[$tableNr]['fieldNr'];
	$sortDefault= 	$cfg->table[$tableNr]['sortDefault'];
//	$imgSrc = 		$cfg['version'] ? $cfg['version'] : $imgSrc;
	$imgSrc = 		$cfg['imgSrc'] ? $cfg['imgSrc'] : "img";

//	writeLog( "Settings: table=$tableName POST['id']="._POST('id'), 1);
	echoDebug( "Settings: listLines=$listLines - maxFieldLen=$maxFieldLen - table=$tableName - fieldNr=$fieldNr - sortDefault='$sortDefault'<br>");

	$knop = _REQUEST('knop');
	$id = 	_REQUEST('id');
	$name = _REQUEST('naam');
	$date = _REQUEST('datum');
	$find = _REQUEST('find');
	$action = $cfg['actionDefault'] != '' ? $cfg['actionDefault'] : _REQUEST('action');
	$language=  _REQUEST('lang');
	$start= (int)_REQUEST('start');
	$parent_id = 	_REQUEST('parent_id');

	if( $knop == "List" || $knop == "Search" || $knop == "Advanced Search")
		$action= '';
	else if( $knop == "Delete")
		$action= 'delete';
	else if( !$knop && _POST('id') && $action == 'edit' )
		$knop= 'Save';

	echoDebug( "Debug($debug): Action=$action Knop=$knop Start=$start Name=$name Date=$date TableNr=$tableNr ID=$id find=$find <br>");

	if( $knop == "Save" && isset( $cfg['forceLogin']) && !$user['edit'] )
		die( "<font color='red'>".(isset( $cfg['noEditRights']) ? $cfg['noEditRights'] : 'Sorry you do not have Edit rights')."</font>, <a href='javascript:javascript:history.go(-1)'>press here</a> to return'");
	else if( $knop == "Copy" && isset( $cfg['forceLogin']) && !$user['add'])
		die( "<font color='red'>".(isset( $cfg['noCopyRights']) ? $cfg['noCopyRights'] : 'Sorry you do not have copy record rights')."</font>, <a href='javascript:javascript:history.go(-1)'>press here</a> to return'");
	else if( $knop == "Delete" && isset( $cfg['forceLogin']) && (!$user['add'] || !$user['del']))
		die( "<font color='red'>".(isset( $cfg['noDeleteRights']) ? $cfg['noDeleteRights'] : 'Sorry you do not have delete record rights')."</font>, <a href='javascript:javascript:history.go(-1)'>press here</a> to return'");

	if( $knop == "Save")
	{
		// *****
		// ***** this is the SAVE procedure of a record
		// *****

		$idFieldName = $cfg->table[$tableNr]->field[0]['name'];
		$parentFieldName= $cfg->table[$tableNr]['linkField'] or $parentFieldName= $cfg->table[0]['name'].'_id';
		$index= $ref= '';
		$functionFields= FALSE;
		echoDebug( "Save record idFieldName($idFieldName) parentFieldName(parentFieldName)<br>");

		if( $id)																//** existing record
		{
			$changedFields= 0;
			writeLog( "-- $tableName record ($id) edited", 1);
			$result= query( "SELECT * FROM `$tableName` WHERE $idFieldName = '$id'", 'I 368', 1);
			$query= '';

			if( !($row = mysqli_fetch_array($result) ))
				die("<h1>Nummer ($id) niet gevonden, knop($knop)</h1>");
		}
		else																	//** new or copied record
		{
			$changedFields= 1;
			writeLog( "-- $tableName record (new) edited: parent $parentFieldName "._POST("$parentFieldName"), 1);

			if( mysqli_fetch_assoc( query( "SHOW COLUMNS FROM $tableName WHERE Field='Created'", '383') ) )
				$query = "INSERT INTO $tableName SET Created='".date('Ymd G:i:s ').$user['name']."'";
			else
				$query = "INSERT INTO $tableName ($idFieldName) VALUES (NULL)";

			query( $query, 402);
			$id= $new["$idFieldName"]= mysqli_insert_id( $db_link);
			echoDebug( "$query =>Added($id)");
			$row= array();
			$message= "Added";
		}

		$query= '';

		if( !$user && isset( $cfg['forceLogin']) )
			die( "User name was not entered, <a href='javascript:javascript:history.go(-1)'>press here</a> to return");

//		if( $cfg['add'] != '1' && $cfg['edit'] != '1' )
//			die( " No edit rights, sorry, <a href='javascript:javascript:history.go(-1)'>press here</a> to return");

		// ** load posted field values in hash table new (all fields)
		// **
		foreach( $cfg->table[$tableNr]->field as $field)
		{
			$fieldName= $field['name'];

			if( $field['function'])
			{
				$functionFields= TRUE;											//* remember that there are function fields
				$new["$fieldName"]= trim( _POST( "$fieldName"), " ");	//* it could be needed to remember the initial value
				continue;
			}

			if( $field['type'] == 'file')										//* file selection field
			{
				$errMsg= array(
					1=>'UPLOAD_ERR_INI_SIZE',
					2=>'UPLOAD_ERR_FORM_SIZE',
					3=>'UPLOAD_ERR_PARTIAL',
					4=>'UPLOAD_ERR_NO_FILE',
					5=>'UPLOAD_ERR_NO_TMP_DIR',
					6=>'UPLOAD_ERR_CANT_WRITE',
					7=>'UPLOAD_ERR_EXTENSION');

				$fTemp= $_FILES["$fieldName"]['tmp_name'];
				$errNo= $_FILES["$fieldName"]['error'];
//				print_r( $_FILES);

				if( $errNo == 0)												//* file has been selected and uploaded
				{
					$array= explode( '.', $_FILES["$fieldName"]['name']);		//* get the extention in last element of array
					$ext = strtolower( array_pop( $array));

					if( $field['rename'])
						$fname= "$tableName $id.$ext";							//* file name record name + id + ext
					else
						$fname= $_FILES["$fieldName"]['name'];

					$path= $field['destination'].$fname;
					writeLog( "A file has been uploaded $fTemp, move to $path", 0);

					if( $field['destination'] !='' && !file_exists( $field['destination']))
					{
						mkdir( $field['destination'], 0777) or die( "Was not able to create directory({$field['destination']})");
						writeLog( "Path({$field['destination']}) does not exists, create it", 0);
					}

					$load= $cfg->table[$tableNr]->load;							//* load is the table cfg to appoint
																				//* the fields in the database that holds
					if( !strpos( ' '.$field['allow'], strtolower($ext)) )
					{
						$message= "Error: file extention(=$ext) upload file not allowed({$field['allow']})";
						writeLog( $message, 1);
					}
					else if( move_uploaded_file( $fTemp, $path) )
					{															//* move file to destination folder
						chmod($path,0777);

						if( $load['path'] && $field['destination'])				//* there is a separate path field, so keep apart
						{
							$new['path']= $field['destination'];
							$new["{$load['field']}"]= $fname;
						}
						else													//* if no path field, put both in url field
							$new["{$load['field']}"]= $field['destination'].$fname;
					}
					else
						writeLog( "Error: move uploaded file $fTemp to $path", 1);
				}
				else if( $errNo != 4 )
				{
					$message= "Error Upload file({$_FILES["$fieldName"]['name']}): ".
						$errMsg["$errNo"];
					writeLog( "   $message", 0);
				}
			}
//			else if( $fieldName == 'Changed' && $message != "Added")
			else if( $fieldName == 'Changed')
				$new['Changed']= date('Ymd G:i:s ').$user['name'];
			else if( $fieldName == 'autoIndex')
				$new['autoIndex']= $index;
			else if( $fieldName == $idFieldName)
				$new["$fieldName"]= $id;
			else
				$new["$fieldName"]= trim( _POST( "$fieldName"), " ");

			if( $field['type'] == 'select' && !$field['byValue'] && isset( $field['index'] )) //* add to index => autoIndex
				$index .= getReference( $field['tableNr'], 'id', (int)_POST( "$fieldName") )." ";

		}	//* end foreach field *//

		if( $tableNr)										//** get reference on parent record
		{
			$parentTableNr= $cfg->table[$tableNr]['linkToTable'];
			$ref= getReference( $parentTableNr, "id", $new["$parentFieldName"]);
		}
		else												//** compile reference of this record
		{
			$fld= explode(', ', $cfg->table[0]['reference']);

			foreach( $fld as $fieldName)
			{
				if( isset( $new["$fieldName"]) )
					$ref .= stripslashes( $new["$fieldName"] ).' ';
			}
		}

		if( $functionFields)								//** if the form has function fields, execute them now
		{
			foreach( $cfg->table[$tableNr]->field as $field)
				if( $field['function'])
				{
					$new[ "{$field['name']}"]= addslashes( fieldFunction( $field['function'], $new));
					echoDebug( "execute fieldFunction( {$field['function']}) returns: '{$new[ "{$field['name']}"]}'<br>");
				}
		}

		foreach( $cfg->table[$tableNr]->field as $field)	//** compare old with new value and build query
		{
			$fieldName= $field['name'];
			$old["$fieldName"]= isset( $row[ "$fieldName"]) ? stripslashes( $row[ "$fieldName"]) : '';
			echoDebug( "compare $fieldName: '{$old["$fieldName"]}'='{$new["$fieldName"]}'", 2);

			if( isset( $new["$fieldName"]) && $fieldName != 'Changed' && $fieldName != 'Created' && $fieldName != 'deleted' &&
				"-".$old["$fieldName"] != "-".$new["$fieldName"] )
			{
				if( $field['len'] == 1)
				{
					if( $old["$fieldName"] == 0 && !$new["$fieldName"])
						continue;
					else if( $old["$fieldName"] == 1 && !$new["$fieldName"])
						$new["$fieldName"]= 0;
				}

				$changedFields++;
				writeLog( "  $fieldName <\t'".$old["$fieldName"]."'\n  $fieldName >\t'".$new["$fieldName"]."'", 0);

				if( $query)
					$query .= ", ";
															// ** set only the changed fields in query
				$query .= "`$fieldName`='".addslashes( htmlspecialchars_decode( $new["$fieldName"]))."'";
			}
		}

//		echoDebug( "fields changed( $changedFields) add Changed({$new['Changed']}) and autoIndex($index) to query($query)<br>");

		if( $changedFields)
		{
			if( isset( $new['Changed']))
				$query .= ", Changed='".$new['Changed']."'";

			query( "UPDATE $tableName SET $query WHERE $idFieldName='$id'", '548', 1);
			$message= $message == 'Added' ? 'Added' : "Changed";
			$query= '';
		}

		$action= 'edit';

	}	//****** end SAVE ******//

	if( $knop == "&lt;" || $knop == "&gt;")	// "&lt;" <  "&gt;" >
	{
		$action = "edit";

		if( $knop == "&lt;" && $id > 1)
			$query= "SELECT id FROM `$tableName` WHERE id < '$id' ORDER BY id DESC LIMIT 1";	//"
		else if( $knop == "&gt;")
			$query= "SELECT id FROM `$tableName` WHERE id > '$id' ORDER BY id ASC  LIMIT 1";

		if( $result= query( $query, '568'))
		{
			$row = mysqli_fetch_array( $result);
			echoDebug( $query."<br>row['id']='".$row['id']."'<br>");

			if( (int)$row['id'])
				$id= (int)$row['id'];
		}

		echoDebug( "We got a '$knop' button, goto $id");
	}

	if( $knop == "Undelete")										// ***** this is the UNdelete part of a record	***** //
	{
		writeLog( "-- $tableName record ($id) marked as UNDELETED ", 1);
		query( "UPDATE $tableName SET `deleted`='0' WHERE id= '$id' LIMIT 1", '585', 1);
		$action == "edit";
	}

	if( ($knop == "delete" || $action == "delete") && $cfg['add'])	// ***** this is the delete part of a record	***** //
	{
//		if( $cfg['add'] != '1' && $cfg['edit'] != '1' )
//			die( " No edit rights, sorry, <a href='javascript:javascript:history.go(-1)'>press here</a> to return");

		if( mysqli_fetch_assoc( query( "SHOW COLUMNS FROM $tableName WHERE Field='deleted'", '591') ) )
		{
			$query = "UPDATE $tableName SET `deleted`='1' WHERE id= '$id' LIMIT 1";
			$logMessage= "-- $tableName record ($id) marked as DELETED ";
		}
		else
		{
			$query= "DELETE FROM $tableName WHERE id= '$id' LIMIT 1";
			$logMessage= "-- $tableName record ($id) DELETED ";
		}

		writeLog( $logMessage, 1);
		query( $query, '604', 1);
		echo "<a href='?dBase=$dbase&amp;".getArgs("sort=$sort&find=$find")."'><b>".$cfg['name']."</b></a> -> ".
			$tableName.$unralatedTables."<h1><a href='javascript:javascript:history.go(-2)'>
					<img src='$imgSrc/page_first.jpg' alt='back'>&nbsp;</a> $logMessage</h1>";
	}
	else if( $action == "edit"  || $action == "advanced"  || $knop == "Add"   || $knop == "Copy"  )
	{
		// *****
		// ***** this is the EDIT part of a record
		// *****
		echoDebug( "***** EDIT part of a record<br>");

		if( $parent_id)
			$parentFieldName= $cfg->table[$tableNr]['linkField'] or $parentFieldName= $cfg->table[0]['name'].'_id';

		if( $action == "edit" || $knop == "Copy"  )
		{
			$histBack= $knop == "Save" ? -2 : -1;
			$backButton= $knop == "Save" ? "$imgSrc/page_first.jpg" : "$imgSrc/page_previous.jpg";

			if( isset( $cfg['showHeader']))
			{
				$icon= $cfg['icon'] ? "<img src='{$cfg['icon']}'>" : "";
				echo "
				<h2><a href='javascript:javascript:history.go($histBack)'><img src='$backButton' alt='back'>&nbsp;</a>
				$icon{$cfg['showHeader']}</h2>";
			}
			else if( !(int)$cfg['noDbName'])
				echo "
					<a href='javascript:javascript:history.go($histBack)'><img src='$backButton' alt='back'>&nbsp;</a>
					<a href='?dBase=$dbase&amp;".getArgs("sort=$sort&find=$find&start=$start")."'><b>".$cfg['name']."</b></a> -> ".$tableName.$unralatedTables;

			$fieldName= $cfg->table[$tableNr]->field[0]['name'];
			$result= query( "SELECT * FROM `$tableName` WHERE $fieldName = '$id'", 'I 621', 1);

			if( !$row = mysqli_fetch_array($result) )
			{
				$recordNotFound= isset( $cfg['recordNotFound']) ? $cfg['recordNotFound'] : "Record not found, number:";
				die("<h1>$recordNotFound $id</h1>");
			}

			if( $knop == "Copy")
			{
//				if( $cfg['add'] != '1' && $cfg['edit'] != '1' )
//					die( " No edit rights, sorry, <a href='javascript:javascript:history.go(-1)'>press here</a> to return");

				$id= FALSE;
				$row["$fieldName"]= FALSE;
				$message= 'Copy';
			}
		}
		else if( $parent_id)
		{
			echoDebug("<br>SET Parent ID: row[ $parentFieldName ]= $parent_id<br>");
			$row["$parentFieldName"]= $parent_id;
			$id= '';
		}
		else
		{
			$row= array();
			$id= '';
		}

		writeEditForm( $id, $row);
	}
	else if( isset( $_GET['listTables']))	// ** List All tables in database				** //
	{
		echo "<h2>List all Tables from '$dbase' ({$cfg['name']})</h2><table>\n";
		$nr= 0;

		foreach( $cfg->table as $table)
			echo "<tr><td>".$nr."</td><td><a href='?dBase=$dbase&amp;tableNr=".$nr++."'>{$table['name']}</a></td><td>{$table['description']}</td></tr>\n";

		echo "</table>\n";
	}
	else if( $action == "report")
	{
		$recordSeparator= $cfg->table[$tableNr]->report[0]["recordSeparator"];
		$reportName = $cfg->table[$tableNr]['reports'];
		$parentFieldName= $cfg->table[$tableNr]['linkField'] or $parentFieldName= $cfg->table[0]['name'].'_id';

		print_r( $cfg->table[$tableNr]['reports']);

		echo "<h2><a href='javascript:javascript:history.go(-1)'><img src='$imgSrc/page_previous.jpg' alt='back'>&nbsp;</a>
			{$cfg->table[$tableNr]->report[0]["headerName"]} ";

		if( $cfg->table[$tableNr]->report[0]['addDate'] == 1)
			echo date("d-m-Y")."</h2>";
		else
			echo "</h2>";

		$font= '';

			 $font.= "size='{$cfg->table[$tableNr]->report[0]['fontSize']}' ";
		if( $cfg->table[$tableNr]->report[0]['fontFace'])
			 $font.= "face='{$cfg->table[$tableNr]->report[0]['fontFace']}' ";

		if( $cfg->table[$tableNr]->report[0]['fontSize'])

		if( $cfg->table[$tableNr]->report[0]['fontColor'])
			 $font.= "color='{$cfg->table[$tableNr]->report[0]['fontColor']}' ";

		if( $font)
			echo "<font $font>";

		$query= "SELECT * FROM `$tableName`";

		if( $parent_id)
			$query .= " WHERE $parentFieldName='$parent_id'";
		else if( $find)
			/* work this out */;

		if( $cfg->table[$tableNr]->report[0]['order'])
			$query .= " ORDER BY ".$cfg->table[$tableNr]->report[0]['order'];
		else if( $sort= _REQUEST('sort') )
			$query .= " ORDER BY $sort";

		$result= query( $query, '713', 1);

		while( $row = mysqli_fetch_array($result) )
		{
			$separator= '';

			foreach( $cfg->table[$tableNr]->report[0]->field as $field)
			{
				if( $row["{$field['name']}"])
				{
					$value= stripslashes( $row["{$field['name']}"]);

					if( strpos( ' '.$field['process'], "revers initials") && strpos( $value, ',') )
					{
						$array = explode( ',', $value);

						if( count( $array) <= 2)
							$value= trim($array[1]).' '.$array[0];
					}

					if( strpos( ' '.$field['process'], "upper case") )
						$value= strtoupper($value);

					if( strpos( ' '.$field['process'], "bold") )
						$value= "<strong>".$value."</strong>";

					if( strpos( ' '.$field['process'], "italics") )
						$value= "<i>".$value."</i>";

					echo "$separator{$field['prefix']}$value{$field['suffix']}";
				}

				$separator= $field['separator']."\n";
			}

			echo $recordSeparator;
		}

		if( $font)
			echo "</font>";

		die( "</div></body>\n</html>");
	}
	else if( $action == 'toLower' || $action == 'trim' || $action == 'addDot' ||  $action == 'rstrip0' ||
		$action == 'do' || $action == 'corrColl' || $action == 'replace')
		correct( $action);
	else if( $action != '')				// ** All other actions are executed once and	** //
		executeAction($action);			// ** checked in include file xxxTools			** //
    else
	{
//****** This part is to LIST the records
//******
		$search= '';

		if( isset( $cfg['showHeader']))
			echo "
			<h2><a href='javascript:javascript:history.go(-1)'><img src='$imgSrc/page_previous.jpg' alt='back'>&nbsp;</a>{$cfg['showHeader']}</h2>\n";

		if( $tableNr == 0 && !(int)$cfg['noDbName'])
			echo"<a href='?dBase=$dbase'>".$cfg['name']."</a> -> <font color='blue'><b>".$tableName."</b></font>".$unralatedTables;
		else if(  !$cfg['noDbName'])
			echo "<a href='javascript:javascript:history.go(-1)'><img src='$imgSrc/page_previous.jpg' alt='back'>&nbsp;</a>
				<a href=\"?dBase=$dbase&amp;".getArgs("sort=$sort&find=".stripslashes($find)."&start=$start")."\">".$cfg['name']."</a> -> <b>".$tableName."</b>";

		if( $parent_id)
		{
			$parentTableNr= $cfg->table[$tableNr]['linkToTable'];
			echo "<h2><a href='?dBase=$dbase&amp;action=edit&amp;tableNr=$parentTableNr&amp;id=$parent_id'>".getReference( $parentTableNr, 'id', $parent_id)."</a></h2>";
		}

		if( !($sort= _REQUEST('sort')))
		{
			if( $sortDefault == '')
				$sort= $cfg->table[$tableNr]->field[0]['name'] ;
			else
				$sort= $sortDefault;
		}

		$parentRecName= $cfg->table[$tableNr]['linkField'] or $parentRecName= $cfg->table[0]['name'].'_id';

		if( $parent_id > 0)		//** if relational table, get records related to id
			$search = " WHERE $parentRecName='$parent_id'";
		else if( _GET('advanced' ) )
			$search= $advanced = stripslashes( _GET('advanced'));

		else if( $knop == 'Advanced Search')
		{
			foreach( $cfg->table[$tableNr]->field as $field)
			{
				$fieldName= $field['name'];
				$value= addslashes( str_replace( '*', '%', rtrim( _POST( "$fieldName") ) ) );

				if( $field['type'] == 'select' && $value == '0')
					$value= '';

				if( $value != '')
				{
					if( !$search)
						$search .= " WHERE `".trim($field['name'])."` LIKE '$value'";
					else
						$search .= " AND `".trim($field['name'])."` LIKE '$value'";
				}
			}

			$advanced= $search;
			$sort= _POST( 'sort');
		}
		else
		{
			if( _GET('where') )
			{
				$search= ' WHERE '.stripslashes( _GET('where'));
				$find= '';
			}
			else if( ($find= trim( addslashes( str_replace( '*', '%', $find )))))		//** prepare the WHERE conditions
			{
				if( $pos= strpos( ' '.$find, ';') ) 						// ** MYSQL injections
					die( "<p><b> Invalid Search, do not use ';' (pos[$pos] $find)</b></p>");

				if( strpos( $find, '=') )									//** search given field only
				{
					if( strpos( $find, '%') )
						$findLike= str_replace( '=', ' LIKE ', $find);
					else
						$findLike= $find;

					echodebug( "search string is: $findLike<br>");
					$search .= " WHERE ".stripslashes( $findLike);
				}
				else
				{
					if( $cfg->table[$tableNr]['strictSearch'])				//** strictSearch is one field only and strictly as find
					{
						if( strpos( ' '.$find, '%') || strpos( ' '.$find, '_'))
							$search= " WHERE (`{$cfg->table[$tableNr]['strictSearch']}` LIKE '$find'";
						else
							$search= " WHERE (`{$cfg->table[$tableNr]['strictSearch']}`='$find'";
					}
					else if( $cfg->table[$tableNr]['limitSearch'])
					{
						$array= explode(',', $cfg->table[$tableNr]['limitSearch']);

						foreach( $array as $fieldName)
						{
							if( !$search)
								$search .= " WHERE (`$fieldName` LIKE '%$find%'";
							else
								$search .= " OR `$fieldName` LIKE '%$find%'";
						}
					}
					else
					{
						foreach( $cfg->table[$tableNr]->field as $field)
						{
							if( $field['type'] == 'file' || $field['derivative'])
								continue;

							if( !$search)
								$search .= " WHERE (`".trim($field['name'])."` LIKE '%$find%'";
							else
								$search .= " OR `".trim($field['name'])."` LIKE '%$find%'";
						}
					}

					$search .= ")";
				}
			}
			else if( $cfg->table[$tableNr]['filter'])
				$search = " WHERE ".$cfg->table[$tableNr]['filter'];

			$selectDefault= $cfg->table[$tableNr]['selectDefault'];

			if( $selectDefault != '' && $find != '')
				$search .= " AND $selectDefault";
			else if( $selectDefault != '' && $find == '')
				$search .= " WHERE $selectDefault";
		}

		if( isset( $tableName) && mysqli_fetch_assoc( query( "SHOW COLUMNS FROM $tableName WHERE Field='deleted'", 'I 883') ) )
		{
			if( !$search)
				$search = " WHERE";
			else
				$search .= " AND";

			if( isset( $_REQUEST['deleted']))
				$search .= " `deleted`='1'";
			else
				$search .= " `deleted`='0'";
		}

		if( $knop == "Search")
			$start= 0;

		$end= (int)$start + (int)$listLines;
		echoDebug( "Start=$start End=$end Find=$find");
		createTableIfNotExists( $tableNr);

		$total = getNbrRows( $tableName, $search);
		$arraySort= explode( ',', $sort);

		foreach( $arraySort as $sortField)
		{
			if( !mysqli_fetch_assoc( query( "SHOW COLUMNS FROM $tableName WHERE Field='$sortField'", '911') ) )	//* check field for SQL injection
				$sort= $sortDefault;
		}

		$desc= isset( $_GET['DESC']) ? 'DESC' : '';
		echodebug( "<br>Sort is: '$sort' desc=$desc<br>\n");
		$result = query( "SELECT * FROM `".$tableName."` $search ORDER BY $sort $desc LIMIT $start, $listLines", 'I 935', 1);
		$cnt = mysqli_num_rows( $result);

		if( $cfg['showSearchNavigation'] == 'top' || $cfg['showSearchNavigation'] == 'both')
			showSearchNavigation();

		echo "
			<table class='list'>
				<tr>
					<th>&nbsp;&nbsp;</th>
			";
		//**
		//** print table header field names
		//**

		foreach( $cfg->table[$tableNr]->field as $field)
		{
			if( (int)$field['list'] > 0)
			{
				$showFieldNameAs= $field['showAs'] != '' ? $field['showAs'].' ' : str_replace('_', ' ', $field['name']).' ';

				if( $field['derivative'])
					echo "
						<th>$showFieldNameAs&nbsp;</th>";
				else if( $sort == $field['name'] && !isset( $_GET['DESC']))
					echo "
						<th>
							<a href=\"?dBase=$dbase&amp;tableNr=$tableNr&amp;DESC&amp;".
							getArgs("sort={$field['name']}&start=0&find=".stripslashes($find)."&amp;parent_id=$parent_id")."\">
							<img alt='pgup ' src='$imgSrc/page_up.jpg'></a>$showFieldNameAs&nbsp;
						</th>";
				else
					echo "
						<th>
							<a href=\"?dBase=$dbase&amp;tableNr=$tableNr&amp;".
							getArgs("sort={$field['name']}&start=0&find=".stripslashes($find)."&amp;parent_id=$parent_id")."\">
							<img alt='pgdn ' src='$imgSrc/page_down.jpg'></a>$showFieldNameAs&nbsp;
						</th>";
			}
		}

		echo "    	<th>&nbsp;&nbsp;</th>
				</tr>
			";
		$tableLine ='';

		while ($row = mysqli_fetch_array($result))		// ** get records and print list
		{
			$tableLine = ($tableLine == 'even' ? 'odd' : 'even');
			$id= $row["id"];

			echo "
				<tr class='$tableLine'><td>&nbsp;&nbsp;<a href='?dBase=$dbase&amp;action=edit&amp;start=$start&amp;id=$id&amp;".getArgs("sort=$sort&tableNr=$tableNr&find=$find&parent_id=$parent_id")."'></a></td>\n\t\t\t\t\t";

			foreach( $cfg->table[$tableNr]->field as $field)
			{
				$fieldName= $field['name'];

				if( $field['type'] == 'select' && !$field['byValue'] )
				{
					if( $field['tableNr'])
						$fieldValue= getReference( (int)$field['tableNr'], 'id', $row["$fieldName"]);
					else
					{
						$selectList= getXmlItem( $cfg->{$field['select']}, $byValue=FALSE);
						$fieldValue= $selectList["{$row["$fieldName"]}"];
					}
				}
				else if( $field['derivative'])
					$fieldValue= fieldFunction( $field['derivative'], $row);
				else if( $field['function'] && $field['referenceTable'])
					$fieldValue= getReference( $field['referenceTable'], 'id', $row["$fieldName"]);
				else
					$fieldValue= stripslashes( $row["$fieldName"] );

				if( (int)$field['list'] > 0)
				{
					if( (int)$field['list'] > 1)
						$fieldValue= substr( $fieldValue, 0, (int)$field['list']);

					echo "<td class='cell'>$fieldValue</td>";
				}
			}

			echo "<td>&nbsp;&nbsp;</td>
				</tr>";
		}

		echo "</table>";

		if( $cfg['showSearchNavigation'] == ''  || $cfg['showSearchNavigation'] == 'bottom'  || $cfg['showSearchNavigation'] == 'both' )
			showSearchNavigation();
	}

	if( $cfg['site'])
	{
		echo "
		  </div>
		</div>
		<!-- END CONTENT -->\n";

		if( file_exists( "{$dbase}_menu.inc"))
			include( "{$dbase}_menu.inc");
		else
			echo "
		<!-- SUB MENU -->
		<div id='rightCol'>
		<h2>Menu</h2>
		{$dbase}_menu.inc not found
		</div
		<!-- END SUB MENU -->\n";
	}

	if( !isset( $_GET['noheader']))
	{
		if( $cfg['menu'])
			echo "
		  <!-- END content -->
		  </div>";

		echo "
	<!-- END container -->
	</div>
</body>
</html>";
	}

// ************************************ //
// **** Start function definitions **** //
// ************************************ //
function query( $query, $line='no line number', $debugLevel=0)
{
	global $debug, $db_link;

	$result= mysqli_query( $db_link, $query ) or die( "<font color='red'>($line) ".$query."<br></font>".mysqli_error( $db_link));

	if( $debugLevel)
		echoDebug( $query."<br>", $debugLevel);

	return( $result);
}

function insert_id()
{
	global $db_link;

	return mysqli_insert_id( $db_link);
}

function affected_rows()
{
	global $db_link;

	return mysqli_affected_rows( $db_link);
}

function listLog( $logFname)
{
	$handle = @fopen( $logFname, "r");
	$inRec= FALSE;
	$buffer= '';

	if ($handle)
	{
		while (($line = fgets($handle, 4096)) !== false)
		{
			if( $inRec && $line[0] == ' ')
				$buffer.= "<li>$line</li>\n";
			else if( strpos( $line, 'edited'))
			{
				$pos= strpos( $line, '--');
				$tableName= strtok( substr( $line, $pos+3), ' ');
				$pos= strpos( $line, '(');

				if( $inRec)
				{
					if( _GET('find'))
					{
						if( strpos( ' '.$buffer, _GET('find')))
							echo $buffer;
					}
					else
						echo $buffer;

					$buffer= '';
				}

				$inRec= TRUE;
				$pos2= strpos( $line, ')') or die("<font color='red'>no ) found in edit record</font>");
				$id= substr( $line, $pos+1, $pos2-$pos-1);
				$buffer.= substr( $line, 0, $pos+1)."<a target='_blank' href='?dBase=brom&tableName=$tableName&action=edit&id=$id'>$id</a>".substr( $line, $pos2)."<br>
				<ul>\n";
			}
			else if( $inRec)
			{
				$inRec= FALSE;
				$buffer.= "</ul>\n";

				if( _GET('find'))
				{
					if( strpos( ' '.$buffer, _GET('find')))
						echo $buffer;
				}
				else
					echo $buffer;

				$buffer= '';
			}
		}

		if (!feof($handle))
			die( "<font color='red'>Error: unexpected fgets() fail</font>\n");

		fclose($handle);
	}

	die();
}

function echoDebug( $string, $level=1)
{
	global $debug;

	if( $debug >= $level)
		echo $string."\n<br>";
}

function writeLog( $message, $time)
{
	global $user, $dbase;

	if( strpos( $dbase, '/'))
	{
		$array= explode( '/', $dbase);
		$fname= array_pop( $array);
		$logdir= implode( '/', $array).'/log';
	}
	else
	{
		$logdir= 'log';
		$fname= $dbase;
	}

	if( !is_dir( "$logdir"))
		mkdir( "$logdir",0777) or die( "<font color='red'>Log file error: Couldn't create '$logdir', 0777</font>");

	$file_log= fopen( "$logdir/$fname.log","a") or
		die( "Error - log file '$logdir/$fname.log' couldn't be openend");

	if( $time)
		$bytes_written = fwrite( $file_log, date('Ymd G:i:s')." {$user['name']}\t$message\n") or
			die( "Error - debug log file 'log/$dbase.log' couldn't be written: $message");
	else
		$bytes_written = fwrite( $file_log, "$message\n") or
			die( "Error - debug log file couldn't be written: $message");

	fclose( $file_log);
}

//* see $_GET['correctField'] in index1Form.php

function correct( $action)
{
	echo "<h2>Correct field($action)</h2>";
	$table= _REQUEST('table')  or die("use arguments:<b> &table=name&field=name&insert</b>");
	$fieldName= _REQUEST('field')  or die("use arguments:<b> &table=name&field=name&insert</b>");
	$where= stripslashes( _REQUEST('where') ) or $where= 1;

	if( $action == 'replace')
	{
		$src= _REQUEST('src')  or die("use arguments:<b> &table=name&field=name&src=string1&dst=string2&insert</b>");
		$dst= _REQUEST('dst')  or die("use arguments:<b> &table=name&field=name&src=string1&dst=string2&insert</b>");
	}

	if( isset( $_GET['insert']))
		echo "<h1>Correct table: $table<br>- action: $action<br>- field: $fieldName (no log)</h1>";
	else
		echo "<h1>Correct table: $table<br>- action: $action<br>- field: $fieldName (no log)</h1> (use <b>insert</b> to make the corrections)<br><br>";

	$result = query( "SELECT * FROM $table WHERE $where ORDER BY id", 'I 1171', 1);

	while( ($row = mysqli_fetch_array($result) ))
	{
		if( $action == 'toLower')
			$value= ucfirst(strtolower($row["$fieldName"]));
		else if( $action == 'trim')
			$value= trim( $row["$fieldName"], "\n ,");
		else if( $action == 'rstrip0')
			$value= ltrim( $row["$fieldName"], "0 ");
		else if( $action == 'replace')
			$value= str_replace( $src, $dst, $row["$fieldName"]);
		else if( $action == 'addDot')
		{
			if( $row["$fieldName"] && $row["$fieldName"][strlen($row["$fieldName"])-1] != '.')
				$value= $row["$fieldName"].".";
		}
		else if( $action == 'replace')
		{
			if( !_GET('src') || !_GET('dest'))
				die( "no source (src=..) or destination (dest=..) specified");

			if( strpos( ' '.$row["$fieldName"].' ', _GET('src').' ') )
				$value= str_replace( _GET('src'), _GET('dest'), $row["$fieldName"]);
			else if( $row["$fieldName"] == _GET('src'))
				$value= _GET('dest');
			else
				continue;
		}

		if( $action == 'corrColl')
		{
			if( $row['coll_nbr'])
				continue;

			$array= explode( ' ', $row['coll_prim']);

			if( count($array) < 2)
				continue;

			if( !isset( $_GET['insert']))
				echo $row['coll_prim']." *** ";

			$coll_nbr= array_pop( $array);

			if( strpos( $coll_nbr, '-') || strlen($coll_nbr) == 8)	//* 23071975
			{
				if( strlen($coll_nbr) == 8)
					$coll_date= substr($coll_nbr, 0, 2).'-'.substr($coll_nbr, 2, 2).'-'.substr($coll_nbr, 4);
				else
					$coll_date= $coll_nbr;


				$coll_nbr= trim(array_pop( $array), ', ');
				$query= "UPDATE $table SET coll_prim='".str_replace( "Ú", "é", implode( ' ', $array)) .
					"', coll_nbr='".$coll_nbr."', coll_date='$coll_date' WHERE id='".$row['id']."'";
			}
			else
				$query= "UPDATE $table SET coll_prim='".str_replace( "Ú", "é", implode( ' ', $array) ).
					"', coll_nbr='$coll_nbr' WHERE id='".$row['id']."'";

			if( (int)$coll_nbr < 1)
				continue;
		}
		else if( $row["$fieldName"] == $value && strlen($row["$fieldName"]) == strlen($value) )
		{
			echoDebug( "No changes record '{$row['id']} {$row["$fieldName"]} == $value<br>\n");
			continue;
		}
		else
		{
			echo "id({$row['id']}) '{$row["$fieldName"]}' => '$value'<br>\n";
			$query= "UPDATE $table SET $fieldName='".addslashes( stripslashes($value))."' WHERE id='{$row['id']}'";
		}

		$i++;

		if( isset( $_GET['insert']))
			query( $query, '1257');
		else
			echo $query."<br>";
	}

	echo "<hr>Number of records updated: $i<br>";
}

function fieldExists( $tableName, $fieldName)
{
	$result= query( "SHOW COLUMNS FROM $tableName WHERE Field='$fieldName'", '1267');

	if( mysqli_fetch_assoc( $result))
		return TRUE;
	else
		return FALSE;
}


function indexTable( $tableName)
{
	global $cfg;
	$tableNr= 999;

	if( !fieldExists( $tableName, 'autoIndex') )
		die( "indexTable( $tableName): Error autoIndex field does not exist");

	foreach( $cfg->table as $table)						//* find table number
	{
		if( $table['name'] == $tableName)
		{
			$tableNr= (int)$table['nr'];
			break;
		}
	}

	if( $tableNr == 999)
		die( "indexTable( $tableName): Error table number not found");

	$result= query( "SELECT * FROM `$tableName` ORDER BY id", '1296');
	$num_rows= mysqli_num_rows( $result);

	while( $row= mysqli_fetch_array( $result ))
	{
		$index= '';

		foreach( $cfg->table[$tableNr]->field as $field)
		{												//* add to index => autoIndex
			if( $field['type'] == 'select' && !$field['byValue'] && isset( $field['index'] ))
				$index .= getReference( $field['tableNr'], 'id', $row["{$field['name']}"] )." ";
		}

		$cnt++;
		query( "UPDATE $tableName SET autoIndex='".addslashes( $index)."' WHERE id={$row['id']}", '1310');

		if( !($cnt % 1000))
		{
			echo "$cnt/$num_rows records done<br>\n";
      		ob_flush();
      		flush();
      	}
	}

	die( "indexTable( $tableName) $cnt records updated");
}

function getTableNumber( $tableName)
{
	global $cfg;

	$tables= $cfg->table;

	foreach( $tables as $table)
	{
		if( $table['name'] == $tableName )
			return (int)$table['nr'];
	}

	die( "<h2>Table name $tableName not found</h2>");
}

function getTableName( $tableNr)
{
	global $cfg;

	if( isset( $cfg->tables))
		$tables= $cfg->tables->table;
	else
		$tables= $cfg->table;

	foreach( $tables as $table)
	{
		if( $table['nr'] == $tableNr )
			return (int)$table['name'];
	}

	die( "<h2>Table found with number $tableNr</h2>");
}


function getNbrRows( $tableName, $where)
{
	$result= query( "SELECT count(*) FROM `$tableName` $where", '1325');
	$row = mysqli_fetch_array( $result );
	return $row['count(*)'];
}

function createTableIfNotExists( $tableNr)
{
	global $cfg;

	if( !isset( $_GET['create']))
		return;

	if( !$cfg->table[$tableNr]->field)
	{
		print_r( $cfg->table[0]);
		die( "<br>Error line(1223): Table($tableNr) has no fields");
	}

	$query= "CREATE TABLE IF NOT EXISTS `".$cfg->table[$tableNr]['name']."` (";
	$delim= '';
	$i= 0;

	foreach( $cfg->table[$tableNr]->field as $field)
	{
		if( $field['name'] == 'id')
		{
			$type= " smallint(6) NOT NULL auto_increment";
			$id= TRUE;
		}
		else if( $field['hig'])
			$type= " text collate latin1_general_ci NOT NULL";
		else if( $field['len'] == 6)
			$type= " smallint(6) NOT NULL";
		else if( $field['len'] == 1)
			$type= " tinyint(1) NOT NULL";
		else if( $field['len'])
			$type= " varchar(".$field['len'].") collate latin1_general_ci NOT NULL";
		else
			die( "Error creating table '".$cfg->table[$tableNr]."', field '".$field['name']."' has no length defined");

		$query .= $delim."`".$field['name']."`".$type;
		$delim= ",  ";
		$i++;
	}

	if( !$i)
		die( "Error(29): Table($tableNr): ".$cfg->table[$tableNr]['name']." has no fields.<br>");

	if( $id)
		$query .= ", PRIMARY KEY (id)";

	$query .= " ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci ;";
	$result= query( $query, 'I 1366', 1	);
}

function getXmlItem( $items, $byValue)
{
	$i=1;
	$array= array();

	foreach( $items as $item)
	{
		if( $byValue)
			$array["{$item['name']}"]= $item['name'];
		else
			$array[$i++]= $item['name'];
	}
	return( $array);
}

function getTableNrSelectFields( $tableNr)
{
	global $cfg;
	$selectFields= array();

	foreach( $cfg->table[$tableNr]->field as $field)
	{
		if( $field['type']=='select' && isset( $field['tableNr']) && !isset( $field['byValue']))
			$selectFields["{$field['name']}"]= $field['tableNr'];
	}

	return $selectFields;
}

function getReference( $tableNr, $idFieldName, $id)
{
	global $cfg;

	$title= '';
	$tableNr= (int)$tableNr;

	if( !$id)
		return( '');

	if( $cfg->table[$tableNr]['reference'])
	{
		echoDebug("getReference tableNr($tableNr)=> fields(".$cfg->table[$tableNr]['reference'].") for $idFieldName='$id'", 3);
		$result= query( "SELECT * FROM `".$cfg->table[$tableNr]['name']."` WHERE $idFieldName='$id'", 'I 1411', 2);
		$referenceDelimiter= $cfg->table[$tableNr]['referenceDelimiter'] ? $cfg->table[$tableNr]['referenceDelimiter'] : ' ';

		if( !($row = mysqli_fetch_array($result) ))
		{
			echo("<h1>$idFieldName ($id) not found</h1>
				ERROR: getReference( tableNr=$tableNr, idFieldName=$idFieldName, id=$id): $query");
			return( "reference not found");
		}
		else
		{
			$fields= explode(',', str_replace( ' ', '', $cfg->table[$tableNr]['reference']));
			$tableNrSelectFields= getTableNrSelectFields( $tableNr);

			foreach( $fields as $fieldName)
			{
				echoDebug("$fieldName tableNr($tableNr)", 3);

				if( isset( $tableNrSelectFields["$fieldName"]))					//* if this is a select field to an other table
					$title .= getReference( $tableNrSelectFields["$fieldName"], $idFieldName, $row[ $fieldName ]).$referenceDelimiter;
				else
					$title .= stripslashes( $row["$fieldName"] ).$referenceDelimiter;
			}

			echoDebug("getReference returns( $title)", 3);
			return( trim( $title, $referenceDelimiter));
		}
	}
	else
	{
		echoDebug("getReference (reference not set for): tableNr='$tableNr', fieldName='$fieldName', id='$id' <br>
			Check table ($tableNr) with name: '".$cfg->table[$tableNr]['name']."'<br>");
		return( "No ref. table=$tableNr");
	}
}

function showSearchNavigation()
{
	global $cnt, $start, $sort, $listLines, $ancor, $language, $advanced, $row,
			$total, $debug, $dbase, $find, $end, $tableNr, $user, $cfg, $imgSrc, $id, $parent_id, $desc;

	// write prev & next buttons	and search box
	if( $cnt >= (int)$listLines)
	{
		$nextImg="page_next.jpg";
		$next=(int)$start+(int)$listLines;
	}
	else
	{
		$nextImg="page_next_not.jpg";
		$next= 0;
	}

	if( (int)$start > 0)
	{
		$prevImg="page_previous.jpg";

		if( (int)$start > (int)$listLines)
			$prev=(int)$start - (int)$listLines;
		else
			$prev= 0;
	}
	else
	{
		$prevImg="page_previous_not.jpg";
		$prev= 0;
	}

	if( $end > $total)
		$end= $total;

	$nr=(int)$start+1;
	$deleted= _REQUEST('deleted') ? "<INPUT TYPE='hidden' name='deleted' VALUE='1'>" : "";

	echo "
		<form method='post' action='?dBase=$dbase' enctype='multipart/form-data'>
		<p>
			<a href=\"?dBase=$dbase&amp;tableNr=$tableNr&amp;$desc&amp;".getArgs("sort=$sort&amp;tableNr=$tableNr&amp;parent_id=$parent_id&amp;advanced=".stripslashes($advanced)."&amp;find=".
			stripslashes($find)."&start=$prev")."\"><img src='$imgSrc/$prevImg' alt='back'></a>
			$nr to $end ($total)<a href=\"?dBase=$dbase&amp;tableNr=$tableNr&amp;$desc&amp;".getArgs("sort=$sort&tableNr=$tableNr&parent_id=$parent_id&advanced=".stripslashes($advanced)."&amp;find=".
			stripslashes($find)."&amp;start=$next")."\"><img src='$imgSrc/$nextImg' alt='forward'></a>
			<INPUT TYPE='hidden' name='tableNr' VALUE='$tableNr'>
			<INPUT TYPE='hidden' name='parent_id' VALUE='$parent_id'>
			<INPUT TYPE='hidden' name='lang' VALUE='$language'>
			<INPUT TYPE='hidden' name='id' VALUE='$id'>
			<INPUT TYPE='hidden' name='find' VALUE='$find'>
			<INPUT TYPE='hidden' name='sort' VALUE='$sort'>
			$deleted\n";

	if( !$parent_id && !$cfg['blockSearch'])
		echo '
			<INPUT type="text" name="find" value="'.stripslashes($find).'"  size=20>
			<INPUT TYPE="submit" name="knop" VALUE="Search">';

	if( $cfg['add'] == 1)
		echo "
			<INPUT TYPE='submit' name='knop' VALUE='Add'>";

	foreach( $cfg->table[$tableNr]->report as $report)
	{
		$url= $report['url'];
		$fieldNames= explode( ',', $report['replace']);

		foreach( $fieldNames as $fieldName)
			$url= str_replace( "@$fieldName",$row["$fieldName"], $url);

		$arguments= '';
		$array= explode( ',', $report['arguments']);

		foreach( $array as $argument)
			if( _REQUEST("$argument"))
				$arguments .= "&amp;$argument="._REQUEST("$argument");

		echo " <a href=\"$url".stripslashes($arguments)."\">{$report['name']}</a>";
	}

	foreach( $cfg->table[$tableNr]->function as $function)
	{
		$target= $function['target'] ? "target='{$function['target']}'" : '';
		$arguments= $parent= '';
		$array= explode( ',', $function['arguments']);

		foreach( $array as $argument)
			if( _REQUEST("$argument"))
				$arguments .= "&amp;$argument=".stripslashes(_REQUEST("$argument"));

		if( $parent_id != '')
			$parent= "&amp;parent_id=$parent_id";

		if( $function['location'] )
			echo " <a $target href=\"{$function['url']}$parent$arguments\">{$function['name']}</a>";
	}


	echo "
		</p>
		</FORM>";

}

function getArgs( $args)
{
	$array= explode( "&", $args);
	$result= '';

	foreach( $array as $arg)
	{
		$parts = explode( "=", $arg);

		if( $parts[1] && $parts[1] != '0')
		{
			if( $result)
				$result .= "&".implode( "=", $parts);
			else
				$result = implode( "=", $parts);
		}
	}

	return( $result);
}

function _GET( $name)
{
	if( isset($_GET["$name"]) )
		return htmlspecialchars( $_GET["$name"], ENT_COMPAT,'ISO-8859-1', true);
	else
		return FALSE;
}

function _REQUEST( $name)
{
	if( isset( $_REQUEST["$name"]))
		return htmlspecialchars( $_REQUEST[$name], ENT_COMPAT,'ISO-8859-1', true);
	else
		return FALSE;
}

function _POST( $name)
{
	if( isset( $_POST["$name"]) )
		return htmlspecialchars( $_POST[$name], ENT_COMPAT,'ISO-8859-1', true);
	else
		return FALSE;
}

function versionInfo()
{
	global $dbase, $user, $cfg;

	die( "
			<h2>© Eric J. Gouda MySQL Database Interface</h2>
			<table class=''>
			<tr><td colspan='3'><b>Involved files</b> (time and date updated) <font color='green'>Version 2.1.2</font></td></tr>
			<tr><td>index1.php</td><td>Main</td><td class='bold'>".date("d-m-Y H:i:s", filemtime( 'index1.php'))."</td></tr>
			<tr><td>index1Form.php</td><td>Form</td><td class='bold'>".date("d-m-Y H:i:s", filemtime( 'index1Form.php'))."</td></tr>
			<tr><td>index1showImage.php</td><td>Show Images</td><td class='bold'>".date("d-m-Y H:i:s", filemtime( 'index1showImage.php'))."</td></tr>
			<tr><td>style.css</td><td>Style Sheet</td><td class='bold'>".date("d-m-Y H:i:s", filemtime( 'style.css'))."</td></tr>
			</table>
			<br>
			Last changes:
			<ul>
				<li>2.1.3 (Dec. 2014) Field correction possibilities added.</li>
				<li>2.1.2 (Nov. 2014) Login is more robust now, with login control.</li>
				<li>2.1.1 (Oct. 2014) User information added to this info</li>
				<li>2.1 login with IP registration included to replace the coockies</li>
				<li>2.1 Copy record is no longer saved before changed (treated as add record)</li>
				<li>2.1 updateThisFieldOnly functionality has been removed, must be covered by the tool function (hidden field values)</li>
				<li>2.1 (Sept.2014) version information added</li>
			</ul>
			<br>
			<table class='topics'>
				<tr><td colspan='2'><b>User information:</b></td></tr>
				<tr><td>id:</td><td class='bold'>{$user['id']}</td></tr>
				<tr><td>name:</td><td class='bold'>{$user['name']}</td></tr>
				<tr><td>IP address:</td><td class='bold'>{$user['ip']}</td></tr>
				<tr><td>Lines:</td><td class='bold'>{$user['lines']}</td></tr>
				<tr><td>Rights edit:</td><td class='bold'>{$user['edit']}</td></tr>
				<tr><td>Rights add:</td><td class='bold'>{$user['add']}</td></tr>
				<tr><td>Accessed:</td><td class='bold'>{$user['access']}</td></tr>
				<tr><td>Last:</td><td class='bold'>{$user['last']}</td></tr>
				<tr><td>Force Login:</td><td class='bold'>{$cfg['forceLogin']}</td></tr>
			</table>
			<br>
			Field correction function, use correctField=[fieldName]:
			<ul>
				<li>&toLower [convert to lower case]</li>
				<li>&trim [remove blanks at start and end]</li>
				<li>&upperFirst [convert to lower case first letter of words]</li>
				<li>&replaceCR [replace CR with a spece and replace @ with two CR afterward]</li>
			</ul>
			<br><a href='?dBase=$dbase'>Continue</a>
		</div>
	</body>
</html>");
}

?>

