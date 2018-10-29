<?PHP

function writeEditForm( $id, $row)
{
	global $cfg, $tableNr, $tableName, $message, $start, $dbase, $maxFieldLen, $sort, $find, $search, $parent_id, $action, $debug, $img;

	$combine= 'NONE';
//	writeLog( "writeEditForm: global message($message)", 1);

	echoDebug( "<br>Parent($parent_id)<br>");

	if( $parent_id)
	{
		$parentFieldName= $cfg->table[$tableNr]['linkField'] or $parentFieldName= $cfg->table[0]['name'].'_id';
		$parentTableNr= $cfg->table[$tableNr]['linkToTable'];
		echo "
			<h2><a href='?dBase=$dbase&amp;action=edit&amp;tableNr=$parentTableNr&amp;id=$parent_id'>".getReference( $parentTableNr, 'id', $row["$parentFieldName"])."</a></h2>";
	}

	if( isset( $_GET['correctField']))
	{
		$rowFieldName= $_GET['correctField'];
		$row[$rowFieldName]= correctString( $row[$rowFieldName]);
	}

	// ****** EDIT RECORD FORMULIER ***** //

	echo "
		<form method='post' action='{$_SERVER['PHP_SELF']}?dBase=$dbase&amp;find=$find' enctype='multipart/form-data'>
		<table class='edit'>
		<tr>
			<td>
				<b>#$id</b>
			</td>
			<td colspan='0'>";	//*  Note: colspan="0" tells the browser to span the cell to the last column of the column group (colgroup)


	if( $cfg->table[$tableNr]['navigate'] == 'both' || $cfg->table[$tableNr]['navigate'] == 'top')
			showButtons( $tableNr, $row);

	if( $cfg->table[$tableNr]['description'])
		echo "
				<big><b><i>{$cfg->table[$tableNr]['description']}</i></b></big> ";

	if( $load= $cfg->table[$tableNr]->load)
	{
		$path= $cfg['imageRoot'].$row["{$load['path']}"].$row["{$load['field']}"];
		echoDebug("This is a load record<br>");

		if( strpos( strtolower( $path), '.jpg') && file_exists( $path) )
			echo "
					<font color='red'><b>&nbsp;$message</b></font>
				</td>
				<td rowspan='{$load['rowspan']}'>
					<a target='_blank' href='$path'><img alt='{$load['field']}' src='index1showImage.php?db={$cfg['name']}&table=$tableName&field={$load['field']}&id=$id&width={$load['width']}'></a>
				</td>
			</tr>\n";
		else
		{
			if( strpos( strtolower( $path), '.jpg'))
				writeLog( "Error URL file not found: $path", 0);

			echoDebug("But file '$path' does not excist or is not .jpg file<br>");

			echo "
					<font color='red'><b>&nbsp;$message</b></font>
				</td>
				<td rowspan='{$img['rowspan']}' width='{$img['width']}'>&nbsp;</td>
			</tr>\n";
		}
	}
	else
		echo "
				<font color='red'><b>&nbsp;$message</b></font>
			</td>
		</tr>";

	//**
	//** for EACH FIELD (output fields with their values)
	//**
	$reference= '';

	foreach( $cfg->table[$tableNr]->field as $field)
	{
		if( isset( $field['hidden']))
			continue;

		$fieldName= $field['name'];
		$notByValue= false;
		$showFieldNameAs= isset($field['showAs']) ? $field['showAs'] : str_replace('_', ' ', $fieldName);
		$fieldLen=  (int)$field['len'] ? (int)$field['len'] : ((int)$field['maxLen'] ? (int)$field['maxLen'] : 80);
		$maxLength= (int)$field['maxLen'] > $fieldLen ? (int)$field['maxLen'] : $fieldLen;
		$fieldHig=  (int)$field['hig'];
		$fieldType= $field['type'];
		$fieldAddLink= $field['addLink'] ? " <a href='?dBase=$dbase&amp;action=edit&amp;function={$field['href']}&amp;id={$row['id']}'>{$field['addLink']}</a> &nbsp;" : '';
		$fieldExplain= $field['explain'];
		$fieldTableNr= false;
		$fieldValue= isset( $row["$fieldName"]) ?
			str_replace('"', "&quot;", stripslashes( $row["$fieldName"]) ) : 	//** replace double quotes otherwise you lose part of the data in that field
			($field['default'] ? $field['default'] : '');

		$onclick= $field['onclick'] == 'submit' ? "onclick='this.form.submit();'" : '';
//			$maxLength= getMySQLfieldLength( $fieldName, $tableName);


		if( $fieldLen > $maxFieldLen)
			$len= $maxFieldLen;
		else
			$len= $fieldLen;

		echoDebug( "Field= $fieldName - Len= $fieldLen - Hig= $fieldHig - Type= $fieldType<br>", 2);

		if( $field['combine'])	//** set field combination flags
		{
			if( $combine == 'START' || $combine == 'OPEN')
				$combine= 'OPEN';
			else
				$combine= 'START';
		}
		else
		{
			if( $combine == 'START' || $combine == 'OPEN')
				$combine= 'CLOSE';
			else
				$combine= 'NONE';
		}

		if( $fieldType == 'text')
			echo "
				<tr>
					<td valign='top'>$showFieldNameAs</td>
					<td><textarea cols='$fieldLen' rows='$fieldHig' NAME='$fieldName'>$fieldValue</textarea>&nbsp;</td>
				</tr>";
		else if( $fieldType == 'file')
			echo '
				<tr>
					<td valign="top">'.$showFieldNameAs.'</td>
					<td><input size="'.$len.'" maxlength="'.$maxLength.'" name="'.$fieldName.'" value="'.$fieldValue.'" type="file">&nbsp;'.$ref.$fieldExplain.$fieldAddLink.'</td>
				</tr>';
		else if( $fieldType == 'radio' || $fieldLen == 1)	//** this is a radio button yes/no for BOOL field len=1 	**//
		{
			if( $combine == 'START' || $combine == 'NONE')
			{
				echo "
					<tr>
						<td valign='top'>$showFieldNameAs</td><td>";		//* field name	*//
			}
			else
			{
				if( $showFieldNameAs != '')
					$showFieldNameAs.= ':';

				if( $field['spaces'])
					echo str_replace( ' ', '&nbsp;', $field['spaces']).$showFieldNameAs;
				else
					echo $showFieldNameAs;
			}

			if( $field['choices'])
			{
				$choices= explode( ',', $field['choices']);

				foreach( $choices as $key => $choice)
				{
					if( (int)$fieldValue == $key)
						echo "
							<input TYPE='radio' NAME='$fieldName' VALUE='$key' checked> $choice";	//* CHECKED	*//
					else
						echo "
							<input TYPE='radio' NAME='$fieldName' VALUE='$key'> $choice";
				}
			}
			else if( (int)$fieldValue)
				echo "
					<input TYPE='checkbox' NAME='$fieldName' VALUE='1' $onclick checked>";	//* CHECKED	*//
			else
				echo "
					<input TYPE='checkbox' NAME='$fieldName' VALUE='1' $onclick>";

			if( $combine == 'CLOSE' || $combine == 'NONE')
			{
				echo '
					</td>
				</tr>';

				$combine= 'NONE';
			}
		}
		else
		{
			// ** if field is a selection field
			// ** get selection item from xml file
			// **
			if( $fieldType == 'select')
			{
				if( $field['select'])
				{
//					echoDebug( "Create Selection list for field($fieldName) use({$field['select']}), value: '".$row[ "$fieldName" ]."'<br>");
					$array= array();

					if( $field['byValue'])
						$array= getDbItem( $cfg->$field['select'], $byValue=TRUE);
					else
						$array= getDbItem( $cfg->$field['select'], $byValue=FALSE);
				}
				else if( $field['choices'])
				{
					echoDebug( "Create Selection list for field($fieldName) use({$field['choices']}), value: '".$row[ "$fieldName" ]."'<br>");
					$array= array();
					$tmp= explode( ',' , $field['choices']);

					foreach( $tmp as $choice)
						$array["$choice"]= $choice;
				}
				else if( isset( $field['tableNr']))
				{
					$fieldTableNr= (int)$field['tableNr'];
					createTableIfNotExists( (int)$field['tableNr']);
//					echoDebug( "Create Selection list for field($fieldName) from table({$field['tableNr']}), value: '".$row[ "$fieldName" ]."'<br>");

					if( $field['noEdit'])
						$fieldValueRef= getReference( (int)$field['tableNr'], 'id', $fieldValue);
					else if( trim( $cfg->table[$fieldTableNr]['name']) == '')
						die("<p class='error'><b>Config Error</b> in field '<b>$fieldName</b>', reference to table not correct</p>");
					else
					{
						$array= array();
						$refFieldArray= explode( ',', $cfg->table[$fieldTableNr]['reference']);
						$refFields= $cfg->table[$fieldTableNr]['reference'];
						$refDelim= $cfg->table[$fieldTableNr]['referenceDelimiter'] or $refDelim= ' ';

						if( $field['dependent'])
						{
							if( $pos= strpos( $field['dependent'], "="))
							{
								$depFieldName = substr( $field['dependent'], 0, $pos);
								$depIdx= substr( $field['dependent'], $pos+1);
								$depFieldValue= isset( $row["$depIdx"]) ? $row["$depIdx"] : '';
							}
							else
							{
								$depFieldName = $field['dependent'];
								$depFieldValue= isset($row["$depFieldName"]) ? $row["$depFieldName"] : '';
							}

							if( fieldExists( $cfg->table[$fieldTableNr]['name'], 'deleted') )
								$query= "SELECT id, $refFields FROM `".$cfg->table[$fieldTableNr]['name'].
									"` WHERE `$depFieldName`='$depFieldValue' AND !deleted ORDER BY $refFields";
							else
								$query= "SELECT id, $refFields FROM `".$cfg->table[$fieldTableNr]['name'].
									"` WHERE `$depFieldName`='$depFieldValue' ORDER BY $refFields";
						}
						else if( fieldExists( $cfg->table[$fieldTableNr]['name'], 'deleted') )
							$query= "SELECT id, $refFields FROM `".$cfg->table[$fieldTableNr]['name'].
								"` WHERE !deleted ORDER BY $refFields";
						else
							$query= "SELECT id, $refFields FROM `".$cfg->table[$fieldTableNr]['name'].
								"` ORDER BY $refFields";

						echoDebug( "Make selection array: $query<br>");
						$sResult = mysql_query( $query)
							or die("<br>Select error on line 686<br>".$query."<br><br>".mysql_error());

						$tableNrSelectFields= getTableNrSelectFields( $fieldTableNr);

						while( $sRow = mysql_fetch_array($sResult) )
						{
							foreach( $refFieldArray as $refField)
							{
								$refField= trim( $refField);

								if( isset( $tableNrSelectFields["$refField"]))					//* if this is a select field to an other table
									$reference .= getReference( $tableNrSelectFields["$refField"], 'id', stripslashes( $sRow["$refField"])).$refDelim;
								else
									$reference .= stripslashes( $sRow["$refField"]).$refDelim;
							}

							if( $cfg->table[$fieldTableNr]['referenceAddTableRef'])
							{
								echoDebug("referenceAddTableRef has 3 parameters ({$sRow['taxon_id']}): call getReference( (int){$refPar[0]}, {$refPar[1]}, $refID={$sRow[$refID]})");

								$reference .= getReference( (int)$refPar[0], $refPar[1], $sRow[$refID]);
							}

							$array[$sRow['id']]= trim( $reference, $refDelim);
							$reference= '';
						}
					}
				}
				else
					die("tags missing in configuration file for selection field '$fieldName'");

				if( $combine == 'START' || $combine == 'NONE')
				{
					echo "
						<tr>
							<td>";
				}
				else if( $field['spaces'])
					echo str_replace( ' ', '&nbsp;', $field['spaces']);

				//** FIELD NAME
				if( isset( $field['tableNr']) && !$cfg['blockLinks'])
					echo "<a target='_blank' href='?dBase=$dbase&amp;tableNr=$fieldTableNr&amp;action=edit&amp;id=$fieldValue'>$showFieldNameAs</a>";
				else
					echo $showFieldNameAs;

				if( $combine == 'START' || $combine == 'NONE')	//* close left column and open right column
					echo '</td>
						<td>';
				else if( $showFieldNameAs != '')
					echo ' : ';

				if( $field['noEdit'])
				{
					echo "<b>$fieldValueRef</b>
						<input type='hidden' name='$fieldName' value='$fieldValue'>"; //* add hidden for copy function
				}
				else
					showSelectionField( $array, $fieldName, $fieldValue, $field['byValue'], $field['onchange'] );

				echo "&nbsp;$fieldExplain $fieldAddLink";

				if( $combine == 'CLOSE' || $combine == 'NONE')
				{
					echo '
						</td>
					</tr>';

					$combine= 'NONE';
				}
			}
			else	//** normal field **/
			{
				if( $fieldType == 'reference' && $row[ "$fieldName" ])
					$ref= "(= ".getReference( (int)$field['tableNr'], $field['reference'], $row[ "$fieldName" ]).")";
				else
					$ref= '';

				if( $fieldName  == 'autoIndex')
					echo "
						<tr><td>$showFieldNameAs</td><td><b>$fieldValue</b></td></tr>";
				else if( $fieldName == 'Changed' || $fieldName == 'Created')
					echo "
						<tr>
							<td>$showFieldNameAs</td>
							<td>$fieldValue</td>
						</tr>";
				else if( $fieldName  != 'id')
				{
					if( $combine == 'START' || $combine == 'NONE')
					{
						if( $field['subheader'])
							echo "
						<tr><td></td><td><i>{$field['subheader']}<i></td></tr>";
						echo '
						<tr>
							<td>';
					}
					else if( $field['spaces'])
						echo str_replace( ' ', '&nbsp;', $field['spaces']);

					if( $fieldName == 'DOI' && $row[ "$fieldName" ] != '' && !$field['noEdit'])
					{

						if( substr( $fieldValue, 0, 18) == 'http://dx.doi.org/')
							echo '<a target="_blank" href="'.$fieldValue.'"><b>'.$showFieldNameAs.'</b></a>&nbsp;';
						else
							echo '<a target="_blank" href="http://dx.doi.org/'.$fieldValue.'"><b>'.$showFieldNameAs.'</b></a>&nbsp;';
					}
					else if( $fieldName == 'url' && $row[ "$fieldName" ] != '' && !$field['noEdit'])
					{
						$relativePath= $cfg['imageRoot'].$row[ "path" ].$field['relativePath'];

						if( substr( $relativePath.$fieldValue, 0, 4) == 'http' || file_exists( $relativePath.$fieldValue))
							echo '<a target="_blank" href="'.$relativePath.$fieldValue.'"><b>'.$showFieldNameAs.'</b></a>&nbsp;';
						else
							echo "<font color='red'><b>$showFieldNameAs</b></font>&nbsp;";
					}
					else if( strpos( $row[ "$fieldName" ], '@') )
						echo '<a href="mailto:'.$row["$fieldName"].'"><b>'.$showFieldNameAs.'</b></a>&nbsp;';
					else if( $fieldTableNr)
						echo "<a target='_blank' href='?dBase=$dbase&amp;tableNr=$fieldTableNr&amp;action=edit&amp;id=$fieldValue'>$showFieldNameAs</a>";
					else if( isset( $field['referenceTable']) && !$cfg['blockLinks'])
						echo "<a target='_blank' href='?dBase=$dbase&amp;tableNr={$field['referenceTable']}&amp;action=edit&amp;id=$fieldValue'>$showFieldNameAs</a>";
					else
						echo $showFieldNameAs;

					if( $combine == 'START' || $combine == 'NONE')	//* close left column and open right column
						echo '</td>
							<td>';
					else if( $showFieldNameAs != '')
						echo ': ';

					//* if the field is not editable
					//*
					if( $field['derivative'])
						echo '<b>'.fieldFunction( $field['derivative'], $row).'</b>&nbsp;'.$ref.$fieldExplain.$fieldAddLink;
					else if( ($field['noEdit'] || $field['function']) && $action != 'advanced')
					{
						echo "<input type='hidden' name='$fieldName' value='$fieldValue'>";

						if( $fieldName == 'url')
							echo '<a target="_blank" href="'.$fieldValue.'"><b>'.$fieldValue.'</b></a>&nbsp;';
						else if( $field['function'] && $field['referenceTable'])
							echo '<b>'.getReference( $field['referenceTable'], 'id', $fieldValue).'</b>&nbsp;'.$ref.$fieldExplain.$fieldAddLink;
						else
							echo '<b>'.$fieldValue.'</b>&nbsp;'.$ref.$fieldExplain.$fieldAddLink;

					}
					else
						echo '<input size="'.$len.'" maxlength="'.$maxLength.'" name="'.$fieldName.'"
							value="'.$fieldValue.'" type="text">&nbsp;'.$ref.$fieldExplain.$fieldAddLink;


					if( $combine == 'CLOSE' || $combine == 'NONE')
					{
						echo '
							</td>
						</tr>';

						$combine= 'NONE';
					}
				}

				if( $fieldType == 'imagePath' && strtolower( substr( $row[ "$fieldName" ], strlen($row[ "$fieldName" ]) - 4 )) == '.jpg')
				{
					$imageHeight=  (int)$field['imageHeight'];

					if( $field['imageRoot'])
						$imageRoot=  $field['imageRoot'].'/';

					echo "
						<tr>
							<td>&nbsp;</td>
							<td><a target='_blank' href='$imageRoot".$row[ "$fieldName" ]."'>
								<img src='$imageRoot".$row[ "$fieldName" ]."' height='$imageHeight' alt='picture should be here'></a>
							</td>
						</tr>";

//								<img test='hallo' src='index1showImage.php?db={$cfg['name']}&amp;table=$tableName&amp;id=$id&amp;width={$load['width']}'>
				}
			}
		}
	}

	echo '
		<tr><td>&nbsp;</td>
			<td>';

	if( $id)
	{
		foreach( $cfg->table as $table)			// ** output links to subrecords	** //
		{
			$linkTableNr= (int)$table['linkToTable'];
			$thisTableNr= (int)$table['nr'];

			if( $table['linkToTable'] != '' &&  $linkTableNr == $tableNr)
			{
				createTableIfNotExists( $thisTableNr);

				if( fieldExists( $cfg->table[$thisTableNr]['name'], 'deleted') )
					$num= getNbrRows( $table['name'], "WHERE ".$tableName."_id='$id' AND !deleted; ");
				else
					$num= getNbrRows( $table['name'], "WHERE ".$tableName."_id='$id';");

				echo "
					<a href='{$_SERVER['PHP_SELF']}?dBase=$dbase&amp;tableNr={$table['nr']}&amp;".
						getArgs("sort=$sort&find=$find&parent_id=$id")."'>".$table['name']."($num)&nbsp;</a>";
			}
		}
	}

	echo '
			</td>
		</tr>';

	echo "
			<td> &nbsp;
				<input type='hidden' name='dBase' value='$dbase'>
				<input type='hidden' name='tableNr' value='$tableNr'>
				<input type='hidden' name='parent_id' value='$parent_id'>
				<input type='hidden' name='start' value='$start'>
				<input type='hidden' name='id' value='$id'>
				<input type='hidden' name='action' value='edit'>
			</td>
			<td colspan='2'>";
	if( $action == 'advanced')
		echo '<INPUT TYPE="submit" name="knop" VALUE="Advanced Search">';
	else if( $cfg->table[$tableNr]['navigate'] == '' || $cfg->table[$tableNr]['navigate'] == 'bottom' || $cfg->table[$tableNr]['navigate'] == 'both')
		showButtons( $tableNr, $row);

	echo '
			</td>
		</table>
		</form>
	';
}	//* end writeEditForm()

function correctString( $value)
{
	if( isset( $_GET['toLower']))
		return ucfirst( strtolower( $value));
	else if( isset( $_GET['trim']))
		return trim( $value, "\n ,");
	else if( isset( $_GET['replace']) && isset( $_GET['by']))
		return str_replace( $_GET['replace'], $_GET['by'], $value);
	else if( isset( $_GET['upperFirst']))
	{
		$array= explode( ' ', $value);

		foreach( $array as $part)
			$rval.= ucfirst( strtolower( $part) ).' ';

		return trim( $rval);
	}
	else if( isset( $_GET['removeCR']) || isset( $_GET['replaceCR']))
	{
		return
		str_replace( "@","\n\n",
		str_replace( "||","|\n|",
		str_replace( "| |","|\n|",
		str_replace( "  "," ",
		str_replace( "\n"," ",
		str_replace( "\r"," ",
		stripslashes( $value )))))));
	}
	else
		die( "<p><font color='red'>correctString: Error, unknown or no correction given [toLower|trim|upperFirst|replaceCR]</font></p>");
}

function showSelectionField( $hash, $fieldName, $selected, $byValue, $onchange=FALSE)
{
	echoDebug( "showSelectionField for '$fieldName' (selected:'$selected') byValue($byValue)");
	$onchange= $onchange == 'submit' ? "onchange='this.form.submit()'" : '';
	$selecFlg= $selected ? "SELECTED" : '';
	$nullValue= $byValue ? '': 0;

	echo "
			<select NAME='$fieldName' $onchange>
				<option VALUE='$nullValue' $selecFlg>-select-";

	foreach($hash as $key => $value)
	{
		if( $byValue)
		{
			$value= trim( $value);

			if( $selected == $value )
				echo "
					<option VALUE='$value' SELECTED>$value";
			else
				echo "
					<option VALUE='$value'>$value";
		}
		else
		{
			if( $selected == $key )
				echo "
					<option VALUE='$key' SELECTED>$value";
			else
				echo "
					<option VALUE='$key'>$value";
		}
	}

	echo "
		</select>";
}


function showButtons( $tableNr, $row)
{
	global $cfg, $start, $sort;

	$buttons= $cfg->table[$tableNr]['buttons'];

	if( $buttons != '')
	{
		$array= explode( ',', $buttons);

		foreach( $array as $button)
		{
			$button= trim($button);

			if( $button == 'Undelete')
			{
				if( fieldExists( $cfg->table[$tableNr]['name'], 'deleted') && $row['deleted']=='1')
					echo '
					<input name="knop" value="Undelete" type="submit">';
			}
			else if( $button == 'navigate')
				echo '
				<input name="knop" value="<" type="submit">
				<input name="knop" value=">" type="submit">';
			else
				echo '
				<input name="knop" value="'.$button.'" type="submit">';
		}
	}
	else
	{
		echo '
				<input name="knop" value="Save" type="submit">';

		if( !isset( $cfg['noPrevNext']))
			echo '
				<input name="knop" value="<" type="submit">
				<input name="knop" value=">" type="submit">';

		if( isset( $cfg['add']))
		{
			if( fieldExists( $cfg->table[$tableNr]['name'], 'deleted') && (int)$row['deleted'])
				echo'
				<input name="knop" value="Undelete" type="submit">';
			else if( fieldExists( $cfg->table[$tableNr]['name'], 'deleted') )
				echo'
				<input name="knop" value="Copy" type="submit">
				<input name="knop" value="Delete" type="submit">';
			else
				echo'
				<input name="knop" value="Copy" type="submit">';
		}
		echo'
				<input name="knop" value="List" type="submit">';
	}

	foreach( $cfg->table[$tableNr]->function as $function)
	{
		if( $function['location'] == 'list')
			continue;

		$url= $function['url'];
		$target= $function['target'] ? "target='{$function['target']}'" : '';
		$fieldNames2replace= explode( ',', $function['replace']);

		foreach( $fieldNames2replace as $fieldName)
		{
			if( isset( $row[$fieldName]))
				$url= str_replace( "@$fieldName", $row[$fieldName], $url);
		}

		if( strpos( ' '.$url, 'http://') )
			echo " <a target='_blank' href='$url'>{$function['name']}</a>";
		else
		{
			$arguments= '';
			$array= explode( ',', $function['arguments']);

			foreach( $array as $argument)
			{
				if( $argument == 'start')
					$arguments .= "&amp;start=$start";
				else if( $argument == 'sort')
					$arguments .= "&amp;sort=".getRequestIsset('sort');
			}

			echo " <a $target href='$url$arguments'>{$function['name']}</a>";
		}
	}

}

?>