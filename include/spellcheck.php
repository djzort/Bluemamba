<?
/*********************************************************************

	CREDIT:   Inspired by Chris Snyder's spellcheckphp
		      http://chxo.com/scripts/spellcheck.php

    Modified: 10/25/2006
              
    Document: include/spellcheck.php
              
    Function: spell check functions (requires aspell)
              
*********************************************************************/

function splchk_check($message, $language)
{
	global $SPELLING_LANG;
	global $ASPELL_PATH;
	
	$file = tempnam("/tmp", "imo_");
	$fp   = fopen($file, "w");
	if($fp)
	{
		$lines   = explode("\n", $message);
		$started = false;
		if(is_array($lines))
		{
			while(list($k, $line) = each($lines))
			{
				if(ereg("^>", $line)) $line = ""; // Ignore quoted lines
				if(!ereg("[a-zA-Z ]", $line)) $line = ""; // Ignore lines that contain no text
				if($line[0]=='-') $line[0] = " ";
				$line = chop($line);
				if(!empty($line)) $started = true; // We won't write leading empty lines
				if($started) fputs($fp, $line."\r\n");
			}
		}
		fclose($fp);
	}
	
	// Make sure language is supported
	$lang = $SPELLING_LANG;
	if(!$lang) return false;
	
	// Run command
	$command = $ASPELL_PATH." -a --language-tag=$lang < $file";
	$temp    = exec($command, $output, $errorno);
	
	// Remove file
	unlink($file);
	
	// Check for error
	if(!is_array($output))
	{
		echo "Got $errorno $temp";
		return false;
	}
	
	// Process
	$last_line = "";
	$line_num  = 1;
	$words     = array();
	$pos       = array();
	while(list($key, $line) = each($output))
	{
		$line = chop($line);
		if($line[0]=='&')
		{
			$output[$key] = $line_num.":".$line;
			list($pre,$post) = explode(": ", $line);
			$pre_a      = explode(" ", $pre);
			$word       = $pre_a[1];
			$offset     = $pre_a[3];
			$candidates = explode(", ", $post);
			$words[$line_num.":".$offset] = $candidates;
			$pos[$line_num.":".$offset]   = $word;
		}
		if(empty($line)) $line_num++;
		$last_line = $line;
	}
	
	$result["words"]   = $words;
	$result["pos"]     = $pos;
	$result["output"]  = implode("\n", $output);
	$result["command"] = $command;
	
	return $result;
}


function splchk_showform($positions, $words, $str)
{
	if(empty($str["correct"]))  $str["correct"]  = "Correct Spelling"; // 13
	if(empty($str["nochange"])) $str["nochange"] = "No Changes";       // 14
	if(empty($str["ignore"]))   $str["ignore"]   = "ignore";           // 17
	if(empty($str["delete"]))   $str["delete"]   = "delete";           // 18
	if(empty($str["formname"])) $str["formname"] = "form[0]";

	echo "<br><table>\n";
	$count = 1;
	// Show list of unknown words and suggestions
	while(list($offset, $word) = each($positions))
	{
		echo "<tr>";
		echo "<td align=right>$word ( @ $offset):&nbsp;</td>\n<td>\n";
		echo "<input type=\"hidden\" name=\"words[$count]\" value=\"$word\">\n";
		echo "<input type=\"hidden\" name=\"offsets[$count]\" value=\"$offset\">\n";
		echo "<select name=\"suggestions[$count]\" onChange=\"document.".$str["formname"].".correct$count.value=this.value;\">\n";
		echo "<option value=\"$word\">$word [".$str["ignore"]."]\n";
		echo "<option value=\"\">[".$str["delete"]."]\n";
		$a = $words[$offset];
		while(list($k, $alt_word) = each($a))
		{
			echo "<option value=\"$alt_word\">$alt_word\n";
		}
		echo "</select></td>\n<td>\n";
		echo "<input type=\"text\" name=\"correct$count\" value=\"$word\" size=20><br>\n";
		$count++;
		echo "</td></tr>\n";
	}
	echo "</table><br>\n";
	echo '<input type="submit" name="correct_spelling" value="'.$str["correct"].'">';
	echo '<input type="submit" name="no_changes" value="'.$str["nochange"].'">';
}


function splchk_correct($message, $words, $offsets, $suggestions, $correct)
{
	
	// No errors, return without chagnes
	if(!is_array($words) || count($words)==0)
	{
		return $message;
	}
	
	// Build correction tree, with line number as main key
	// Offset as secondary key
	while(list($num,$word) = each($words))
	{
		$correction  = $correct[$num];
		$correction2 = $suggestions[$num];
		if(empty($correction)) $correction = $correction2;
		$correction = stripslashes($correction);
		
		if($word != $correction)
		{
			list($line_num, $offset) = explode(":", $offsets[$num]);
		
			$corr_a["word"]         = $word;
			$corr_a["correction"]   = $correction;
			$cq[$line_num][$offset] = $corr_a;
			
			echo $word." -> ".$correction."<br>\n";
		}
	}
	
	// If no corrections, return without changes
	if(!is_array($cq)) return $message;
	
	// Chop up message, split leading empty lines from real content
	$lines_raw = explode("\n", $message);
	$started   = false;
	while(list($k, $line) = each($lines_raw))
	{
		$line = chop($line);
		if(!empty($line)) $started = true;
		if($started)
		{
			$lines[] = $line;
		}
		else
		{
			$head[] = $line;
		}
	}

	//process correction tree
	echo "<!-- Spellchecker debug output\n ";
	ksort($cq);
	reset($cq);
	while(list($line_num, $a) = each($cq))
	{
		$line = chop($lines[$line_num-1]);
		echo "line: $line_num\n";
		//handle corrections in line in reverse order so 
		//offsets won't get screwed up by prior corrections
		krsort($a);
		reset($a);
		while(list($offset, $a2) = each($a))
		{
			$word       = $a2["word"];
			$correction = $a2["correction"];
			
			$before = substr($line, 0, $offset);
			$error  = substr($line, $offset, strlen($word));
			$after  = substr($line, $offset+strlen($word));
			if(strcmp($error, $word) == 0)
			{
				$line = $before.$correction.$after; //validate error
				echo "\t$offset\t$word -> $correction\n";
			}
			else
			{
				echo "\t$offset\t$word -> error: found $error instead of $word at offset\n";
			}
		}
		echo "old line: \"".$lines[$line_num-1]."\"\n";
		echo "new line: \"".$line."\"\n";
		$lines[$line_num-1] = $line;
	}
	echo "//-->\n";
	
	if(is_array($head) && count($head)>0)
	{
		$head_str = implode("\n", $head)."\n";
	}
	return $head_str.implode("\n", $lines);
}

?>