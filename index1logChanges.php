<?php
	$fname= $_GET['fname'] or die( "No file name given: ?fname=name");

	$file_log= fopen( $fname,"r") or
		die( "Error - log file $fname couldn't be openend");

	$started= $continued= false;

	while( $line = fgets( $file_log) )
	{
		$cnt++;

		if( $line[0] == ' ' && $line[1] == ' ')
		{
			if( !$started)
			{
				$started= true;
				$array= explode( "\t", $oldLine);
				$array= explode( " ", $array[0]);
				$user= $array[2];
				$array= explode( " ", $oldLine);
				$record= trim( $array[5], "()");

				$array= explode( "\t", $line);
				$orig[]= $array[1];
			}
			else
			{
				$continued= true;
				$array= explode( "\t", $line);
				$final[]= $array[1];
			}
		}
		else if( substr( $line, 0, 4) == '2012' && $continued)
		{
			echo "--- $user (<a target='_blank' href='index1.php?dBase=specimenSmith&action=edit&id=$record'>$record</a>) ---<br>\n";

			while( $orig[0] && ($line1= trim( array_shift($orig) ) ) && ($line2= trim(array_shift($final))) )
			{
				if( $line1 != $line2)
					echo "< $line1<br>\n> $line2<br>\n";
			}

			$orig= $final= array();
			$started= $continued= false;
		}
		else if( $continued)
				$final[]= $line;
		else if( $started)
				$orig[]= $line;

		$oldLine= $line;
	}

	fclose( $file_log);
?>