<?PHP
set_time_limit(0);
error_reporting(E_ALL ^ E_NOTICE);

define('FAMILY',8);
define('GENUS',12);
define('SPECIES', 18);

function fieldFunction( $function, $row)
{
		 if( $function == 'calculate_full_name')	return calculate_full_name( $row);
	else if( $function == 'vernamCurrentLanguage')	return vernamCurrentLanguage( $row);
	else if( $function == 'linkTaxon')				return linkTaxon( $row);
	else if( $function == 'getFamilyName')			return getTaxonNameByRank( $row, FAMILY);
	else if( $function == 'getGenusName')			return getTaxonNameByRank( $row, GENUS);
	else
		die( "ERROR: unknown Field Function: '$function'");
}

function executeAction( $action)
{
	global $tableName;

		 if( $action == 'loadXML') 		loadXML();
	else if( $action == 'loadWGS') 		loadWGS();
	else if( $action == 'setTaxonName') setTaxonName();
	else if( $action == 'setAccesName') setAccesName();
	else if( $action == 'loadData') 	loadData();
	else if( $action == 'InternetLinksAccession')	return InternetSearch( 'Accession');
	else if( $action == 'InternetLinksTaxon')		return InternetSearch( 'Taxon');
	else if( $action == 'setFamilyAndGenusName')	return setFamilyAndGenusName();
	else
		echo "$action unknown functionality";
}

function InternetSearch( $record)
{
	$id= $_GET['id'] or die( "InternetSearch no Record ID given");

	if( $record == 'Accession')
		$query= "SELECT taxon.*, itf_rk.code as rank FROM accession
			RIGHT JOIN taxon ON taxon_id=taxon.id
			JOIN itf_rk ON taxon.rank_id=itf_rk.id
			WHERE accession.id='$id'";
	else
		$query= "SELECT *, itf_rk.code as rank FROM taxon
			JOIN itf_rk ON taxon.rank_id=itf_rk.id
			WHERE taxon.id='$id'";

	$result= mysql_query( $query ) or die( "(TT 41) ".$query."<br>".mysql_error());

	if( $row= mysql_fetch_array( $result))
	{
//		print_r( $row);

		if( file_exists( 'kaunas/InternetSearch.xml'))
		{
			$data= implode( ' ', file('kaunas/InternetSearch.xml'));
			$data=  str_replace( "@itf_fam", $row['itf_fam'],
					str_replace( "@itf_fulnam", $row['itf_fulnam'],
					str_replace( "@itf_gen", $row['itf_gen'],
					str_replace( "@epithet", $row['epithet'],
					str_replace( "@rank", $row['rank'],
					$data)))));
			echo $data;
		}
		else
			die( "Report: kaunas/InternetlinksAccession.xml is missing");
	}
	else
		die( "Accession is not linked to any Taxon");
}

function getTaxonID( $epithet, $rank_id, $parent_taxon_id, $author, $cult)
{
	if( $epithet == '')
		return FALSE;

	$result= mysql_query( "SELECT * FROM taxon WHERE epithet='".addslashes($epithet)."' AND itf_cul='".addslashes($cult)."' AND rank_id='$rank_id' AND parent_taxon_id='$parent_taxon_id'" );

	if( $row= mysql_fetch_array( $result))
		return $row['id'];
	else
	{
		$result= mysql_query( "SELECT * FROM taxon_author WHERE combination='$author'" );

		if( $row= mysql_fetch_array( $result))
			$taxon_author_id= $row['id'];
		else
		{
			$query= "INSERT INTO taxon_author SET combination='".addslashes( $author)."'";

			if( isset( $_GET['insert']))
				mysql_query( $query ) or die( "(TT 35) ".$query."<br>".mysql_error());

			echo "TAXON AUTHOR: $query<br>\n";
			$taxon_author_id= mysql_insert_id();
		}

		$query= "INSERT INTO taxon SET epithet='".addslashes($epithet)."', itf_cul='".addslashes($cult)."', rank_id='$rank_id', taxon_author_id='$taxon_author_id', parent_taxon_id='$parent_taxon_id'";

		if( isset( $_GET['insert']))
			mysql_query( $query ) or die( "(TT 35) ".$query."<br>".mysql_error());

		echo "TAXON: $query<br>\n";
		return mysql_insert_id();
	}
}

function loadData()
{
	$lines= file( 'kaunas/Inventor.txt');
	$cnt= 0;
	array_shift( $lines);	//* skip header
	$field= array( 'id'=>0, 'accid'=>1, 'name'=>2, 'family'=>3, 'genus'=>4, 'species'=>5, 'infra'=>6, 'acc_year'=>7, 'acc_country'=>8, 'country'=>9,
		'rel_city'=>10, 'relation'=>11, 'acc_loc2'=>12, 'coll_year'=>13, 'donors_donor_year'=>14, 'seed'=>15, 'ipen'=>16, 'spont'=>17,
		'incorrect_name'=>18, 'source'=>19, 'amount'=>20, 'remarks'=>21, '??'=>22, 'synonym'=>23);
	print_r( $field);
	echo "<hr>";

	foreach( $lines as $line)
	{
		$cnt++;

		//* TAXON
		$genus= $species= $infra= $rank= $cult= $rest= '';
		$data= explode( "\t", trim( $line));

		for( $i=0; $i<24; $i++)
			$data[$i]= $data[$i] == 'XX' ? '' : $data[$i];

		echo "<b>".implode(' - ', $data)."</b><br>\n";

//		print_r( $data); echo "<br>";

		$family= $data["{$field['family']}"];
		$genus= $data["{$field['genus']}"];

		$array= explode( ' ', str_replace( '  ', ' ',  $data["{$field['species']}"]));
		array_shift( $array);	//*	skip genus
		$species= implode( ' ', $array);
		$name= $rest= str_replace( '  ', ' ',  $data["{$field['name']}"]);

		if( ($first= substr( $data["{$field['infra']}"], 0, 1)) == "'")
		{
			$cult= $data["{$field['infra']}"];

			if( $pos= strpos( $name, "'"))
				$rest= substr( $name, 0, $pos - 1);
		}
		else if( $data["{$field['infra']}"] != '')
		{
			$infra= $data["{$field['infra']}"];

			if( $pos= strpos( $name, $infra))
			{
				$infra_aut= substr( $name, $pos + strlen($infra) + 1);
				$rest= substr( $name, 0, $pos - 1);
			}
		}

		$array= explode( ' ', $rest);
		array_shift( $array);	//*	skip genus

		if( $array && $array[0] == '×')
			array_shift( $array);	//*	x

		array_shift( $array);	//*	skip species

		if( $infra != '')
			$rank= array_pop( $array);

		$author= implode( ' ', $array);

		$family_id= getTaxonID( $family, 8, 0, '', '');
		$genus_id = getTaxonID( $genus, 12, $family_id, '', '');
		$species_id = getTaxonID( $species, 18, $genus_id, $author, $cult);
		$infra_id = getTaxonID( $infra, ($rank == 'f.' ? 25 : ($rank == 'var.' ? 22 : 20)), $species_id, $infra_aut, $cult);
		$taxon_id = $infra_id ? $infra_id : ($species_id ? $species_id : ($genus_id ? $genus_id : $family_id) );

		//* Synonym
		$array= explode( ' ', str_replace( '  ', ' ',  $data["{$field['synonym']}"]));
		$genus_id = getTaxonID( array_shift( $array), 12, $family_id, '', '');
		getTaxonID( array_shift( $array), 18, $genus_id, implode( ' ', $array), '');

		//* RELATION
		$rel_name= $data["{$field['relation']}"];
		$rel_city= $data["{$field['rel_city']}"];
		$iso_two_let= $data["{$field['acc_country']}"];

		$result= mysql_query( "SELECT * FROM itf_cou WHERE iso_two_let='$iso_two_let'" );

		if( $row= mysql_fetch_array( $result))
			$itf_cou_id= $row['id'];
		else
			$itf_cou_id= 0;

		$result= mysql_query( "SELECT * FROM relation WHERE name='$relation'" );

		if( $row= mysql_fetch_array( $result))
			$relation_id= $row['id'];
		else
		{
			$query= "INSERT INTO relation SET name='$rel_name', city='$rel_city', itf_cou_id='$itf_cou_id'";

			if( isset( $_GET['insert']))
				mysql_query( $query ) or die( "(TT 35) ".$query."<br>".mysql_error());

			echo "RELATION: $query<br>\n";
			$relation_id= mysql_insert_id();
		}

		//* ACCESSION
		$result= mysql_query( "SELECT * FROM accession WHERE id='{$data["{$field['id']}"]}'" );

		if( $row= mysql_fetch_array( $result))
			echo "Accession record '{$data["{$field['id']}"]}' already in database<br>\n";
		else
		{
			$query= "INSERT INTO identification SET taxon_id='$taxon_id', accession_id='{$data["{$field['id']}"]}', illeg_name='{$data["{$field['incorrect_name']}"]}'";

			if( isset( $_GET['insert']))
				mysql_query( $query ) or die( "(TT 157) ".$query."<br>".mysql_error());

			echo "IDENTIFICATION: $query<br>\n";
			$identification_id= mysql_insert_id();

			$itf_cou_id= 0;
			$itf_prot_id= 3;

			if( $data["{$field['country']}"] != '')
			{
				$iso_two_let= $data["{$field['country']}"];
				$result= mysql_query( "SELECT * FROM itf_cou WHERE iso_two_let='$iso_two_let'" );

				if( $row= mysql_fetch_array( $result))
				{
					$itf_cou_id= $row['id'];
					$itf_prot_id= 1;
				}
			}

			$query= "INSERT INTO accession SET
				id='".$data["{$field['id']}"]."',
				itf_accsta_id=1,
				itf_accid='".$data["{$field['acc_year']}"].'-'.$data["{$field['accid']}"]."',
				itf_cdat='".$data["{$field['coll_year']}"]."',
				ipen='".$data["{$field['ipen']}"]."',
				itf_cou_id='$itf_cou_id',
				taxon_id='$taxon_id', relation_id='$relation_id', itf_prot_id='$itf_prot_id', identification_id='$identification_id'
				";

			if( isset( $_GET['insert']))
				mysql_query( $query ) or die( "(TT 157) ".$query."<br>".mysql_error());

			echo "ACCESSION: $query<br>\n";
			$accession_id= mysql_insert_id();
		}

		if( isset($_GET['limit']) && $cnt > 30)
			break;

		echo "<hr>\n";
	}
}


function vernamCurrentLanguage( $row)
{
	global $cfg;

	$query= "SELECT * FROM vernacular_name
		LEFT JOIN language ON language_id=language.id
		WHERE taxon_id='{$row['id']}' AND language.code='{$cfg['language_code']}'";

	$result= mysql_query( $query ) or die( "(TT 41) ".$query."<br>".mysql_error());

	if( $row= mysql_fetch_array( $result))
		return $row['name'];
	else
		return '';
}

function linkTaxon( $row)
{
	$query= "SELECT * FROM identification WHERE id={$row['identification_id']}";
	$result= mysql_query( $query) or die( "(TT 238) ".$query."<br>".mysql_error());

	if( $row2= mysql_fetch_array( $result))
		return $row2['taxon_id'];
	else
		return NULL;
}

function setAccesName()
{
	$query=  "SELECT accession.id, identification.taxon_id FROM accession
		JOIN identification ON accession.identification_id=identification.id
		WHERE !deleted ORDER BY id";
	$result= mysql_query( $query ) or die( "(TT 41) ".$query."<br>".mysql_error());
	$cnt= 0;

	while( $row= mysql_fetch_array( $result))
	{
		$query= "UPDATE accession SET taxon_id='{$row['taxon_id']}' WHERE id={$row['id']}";
		mysql_query( $query ) or die( "(TT 41) ".$query."<br>".mysql_error());
		$cnt++;
	}

	echo "$cnt records updated";
}

function setFamilyAndGenusName()
{
	$result= mysql_query( "SELECT * FROM taxon WHERE !deleted ORDER BY id" ) or die( "(TT 289) ".$query."<br>".mysql_error());
	$cnt= 0;

	while( $row= mysql_fetch_array( $result))
	{
		$query= "UPDATE taxon SET
			itf_fam ='".addslashes( getTaxonNameByRank( $row, FAMILY))."',
			itf_gen ='".addslashes( getTaxonNameByRank( $row, GENUS))."'
			WHERE id={$row['id']}";
		mysql_query( $query ) or die( "(TT 298) ".$query."<br>".mysql_error());
		$cnt++;
	}

	echo "$cnt records updated";
}

function setTaxonName()
{
	$result= mysql_query( "SELECT * FROM taxon WHERE !deleted ORDER BY id" ) or die( "(TT 41) ".$query."<br>".mysql_error());
	$cnt= 0;

	while( $row= mysql_fetch_array( $result))
	{
		$query= "UPDATE taxon SET itf_fulnam ='".addslashes( calculate_full_name( $row))."' WHERE id={$row['id']}";
		mysql_query( $query ) or die( "(TT 41) ".$query."<br>".mysql_error());
		$cnt++;
	}

	echo "$cnt records updated";
}

function getTaxonNameByRank( $row, $rank)
{
	while( $row['rank_id'] != $rank && $row['parent_taxon_id'])
	{
		$query= "SELECT * FROM taxon WHERE id={$row['parent_taxon_id']}	;";
		$result= mysql_query( $query ) or die( "(TT 310) ".$query."<br>".mysql_error());
		$row= mysql_fetch_array( $result);
	}

	if( $row && $row['rank_id'] == $rank)
		return $row['epithet'];
	else
		return FALSE;
}

function calculate_full_name( $row)
{
	if( !$row['id'])
		return '';

	if( !$row['rank_id'])
		die( "calculate_full_name Error: Record has no rank");

	if( $row['rank_id'] < 18)			//* Rank Species=18: All above species level!
		return $row['epithet'];
	else if( trim( $row['hyb']) != '' && trim( $row['epithet']) == '' )		//* Hybrid without epithet -> must be revised!
	{
		$row1= mysql_fetch_array( mysql_query( "SELECT * FROM taxon WHERE id={$row['mother']};" )) or $row1['name']='?';
		$row2= mysql_fetch_array( mysql_query( "SELECT * FROM taxon WHERE id={$row['father']};" )) or $row2['name']='?';
		$array= explode( ' ', $row2['name']);
		$dupl= '';

		while( $part= array_shift( $array))
		{
			$dupl= trim( "$dupl $part");

			if( substr( $row1['name'], 0, strlen( $dupl)) != $dupl)
				break;
		}

		return $row1['name'].' '.$row['hyb'].' '.trim($part.' '.implode(' ', $array));
	}
	else
	{
		if( $row['rank_id'] > 18)						//* Rank Species=18
		{
			$result= mysql_query( "SELECT * FROM itf_rk WHERE id={$row['rank_id']}");

			if( $row2= mysql_fetch_array( $result))
				$rank= $row2['code'];
			else
				die( "calculate_full_name Error: Record has a rank_id not in table itf_rk");

			$name= $rank.' '.$row['epithet'];
		}
		else
			$name= $row['epithet'];

		$name.= $row['itf_cul'] != '' ? ' '.$row['itf_cul'] : '';

		if( $row['hyb'] != '')
			$name .= " {$row['hyb']}";

		while( $row && $row['rank_id'] != 12 && $row['parent_taxon_id'])
		{
			$query= "SELECT * FROM taxon WHERE id={$row['parent_taxon_id']}	;";
			$result= mysql_query( $query ) or die( "(BT 296) ".$query."<br>".mysql_error());

			if( $row= mysql_fetch_array( $result))
			{
				if( $row['rank_id'] == 12 || $row['rank_id'] == 18)		//* Rank Species=18, Genus=12
				{
					if( $rank == 'section' || $rank == 'subgen.')		//* Rank Section=14, Subgenus=13
						$name= "$name ($rank of {$row['epithet']})";
					else
						$name= $row['epithet']." $name";
				}
			}
			else
				die( "calculate_full_name Error: Parent with id({$row['parent_taxon_id']}) not found ->$query");

			echoDebug( "Found: {$row['id']} {$row['rank']} {$row['epithet']} ->$query<br>");
		}

		if( $row['rank_id'] != 12)
			die( "calculate_full_name Error: Name record with id({$row['id']}) has no parent with genus rank");

		return $name;
	}
}

function loadWGS()
{
	if (file_exists('kaunas/wgs_gazetteer.xml'))
	{
		$xml = simplexml_load_file('kaunas/wgs_gazetteer.xml');
		$cnt= 0;
		$country= array();


		foreach( $xml->m_itf_cou[0]->itf_cou as $itf_cou)		//* Countries
		{
			$result= mysql_query( "SELECT * FROM itf_cou WHERE iso_two_let='{$itf_cou['m_iso_two_let']}'");

			if( $row= mysql_fetch_array( $result))
			{
				echo "Already in itf_cou: '{$itf_cou['m_iso_name']}'<br>\n";
				continue;
			}

			$cnt++;

			$query= "INSERT INTO itf_cou SET iso_two_let='{$itf_cou['m_iso_two_let']}', iso_three_let='{$itf_cou['m_iso_three_let']}',
				iso_name='".addslashes( $itf_cou['m_iso_name'])."', iso_num='{$itf_cou['m_iso_num']}'";

			if( isset( $_GET['insert']))
				mysql_query( $query ) or die( "(TS 177) ".$query."<br>".mysql_error());
			else
				echo "$query<br>\n";

			$country["{$itf_cou['OID']}"]= $cnt;
		}

		echo "$cnt records done, NEXT SERIES<br>";

		foreach( $xml->m_itf_cou[1]->itf_cou as $itf_cou)		//* Countries second series
		{
			$result= mysql_query( "SELECT * FROM itf_cou WHERE iso_two_let='{$itf_cou['m_iso_two_let']}'");

			if( $row= mysql_fetch_array( $result))
			{
				echo "Already in itf_cou: '{$itf_cou['m_iso_name']}'<br>\n";
				continue;
			}

			$cnt++;

			$query= "INSERT INTO itf_cou SET iso_two_let='{$itf_cou['m_iso_two_let']}', iso_three_let='{$itf_cou['m_iso_three_let']}',
				iso_name='{$itf_cou['m_iso_name']}', iso_num='{$itf_cou['m_iso_num']}'";

			if( isset( $_GET['insert']))
				mysql_query( $query ) or die( "(TS 202) ".$query."<br>".mysql_error());
			else
				echo "$query<br>\n";

			$country["{$itf_cou['OID']}"]= $cnt;
		}

		echo "$cnt country records inserted<br>\n";
		$cnt= 0;

		foreach( $xml->m_itf_wgs->itf_wgs as $itf_wgs)			//* WGS
		{
			$result= mysql_query( "SELECT * FROM itf_wgs WHERE wgscode='{$itf_wgs['m_wgscode']}'");

			if( $row= mysql_fetch_array( $result))
			{
				echo "Already in itf_wgs: '{$itf_wgs['m_wgscode']} {$itf_wgs['m_name']}'<br>\n";
				continue;
			}

			$cnt++;
			$parent_id= $wgs_codes["{$itf_wgs->m_parentarea['OID']}"];
			$itf_cou_id= $country["{$itf_wgs->m_country['OID']}"];

			$query= "INSERT INTO itf_wgs SET wgscode='{$itf_wgs['m_wgscode']}', name='".addslashes( $itf_wgs['m_name'])."',
				parent_id='$parent_id', itf_cou_id='$itf_cou_id'";

			if( isset( $_GET['insert']))
				mysql_query( $query ) or die( "(TS 229) ".$query."<br>".mysql_error());
			else
				echo "$query<br>\n";

			$wgs_codes["{$itf_wgs['OID']}"]= $cnt;

			if( isset( $itf_wgs->m_gazetteer))
			{
				foreach( $itf_wgs->m_gazetteer->BTGazetteer as $gaz)			//* Gazetteer
				{
					$query= mysql_query( "SELECT * FROM itf_wgs_gazetteer WHERE ift_wgs_id='$cnt' AND name='{$gaz['name']}'" );

					if( $row= mysql_fetch_array( $result))
					{
						echo "Already in itf_wgs_gazetteer: '{$gaz['name']}'<br>\n";
						continue;
					}

					$query= "INSERT INTO itf_wgs_gazetteer SET itf_wgs_id='$cnt', name='".addslashes( $gaz['m_name'])."', synonym='".
						addslashes( $gaz['m_synonym'])."', notes='".addslashes( trim( $gaz['m_notes'], ' "'))."'";

					if( isset( $_GET['insert']))
						mysql_query( $query ) or die( "(TS 251) ".$query."<br>".mysql_error());
					else
						echo "-- $query<br>\n";
				}
			}
		}

	}
	else
		exit('Failed to open kaunas/Genera.xml.');

	echo "$cnt WGS records inserted<br>\n";
}


function loadXML()
{
	if (file_exists('kaunas/Genera.xml'))
	{
		$xml = simplexml_load_file('kaunas/Genera.xml');
		$rank['RNK_G0']='genus';
		$rank['RNK_F0']='family';
		$cnt= 0;

		foreach( $xml->m_taxonauthorcombination->BTTaxonAuthorCombination as $comb)
		{
			$cnt++;
			$query= "INSERT INTO taxon_author SET combination='".addslashes( $comb['m_combination'])."'";

			if( isset( $_GET['insert']))
				mysql_query( $query ) or die( "(TS 279) ".$query."<br>".mysql_error());
			else
				echo "$query<br>\n";

			$author["{$comb['OID']}"]= $cnt;
		}

		$cnt= 0;

		foreach( $xml->m_taxon[0]->BTTaxon as $taxon)		//* FAMILIES
		{
			$cnt++;
			$query= "INSERT INTO taxon SET epithet='".addslashes( $taxon['m_epithet'])."', rank_id=8";

			if( isset( $_GET['insert']))
				mysql_query( $query ) or die( "(TS 294) ".$query."<br>".mysql_error());
			else
				echo "$query<br>\n";

			$family["{$taxon['OID']}"]= $cnt;
		}

		foreach( $xml->m_taxon[1]->BTTaxon as $taxon)
		{
			$cnt++;
			$parent_taxon_id= $family["{$taxon->m_parent['OID']}"];
			$correct_taxon_id= $family["{$taxon->m_correct_taxon['OID']}"];
			$taxon_author_id= $author["{$taxon->m_taxon_author['OID']}"];

			$query= "INSERT INTO taxon SET epithet='".addslashes( $taxon['m_epithet'])."', rank_id=12, taxon_author_id='$taxon_author_id',
				parent_taxon_id='$parent_taxon_id', correct_taxon_id='$correct_taxon_id'";

			if( isset( $_GET['insert']))
				mysql_query( $query ) or die( "(TS 312) ".$query."<br>".mysql_error());
			else
				echo "$query<br>\n";

//			echo "{$taxon['m_epithet']} $aut - $parent<br>\n";
			$family["{$taxon['OID']}"]= $cnt;

		}

	}
	else
		exit('Failed to open kaunas/Genera.xml.');
}

?>