//Tv search
<?php
/*
Last edited 07/23/2010 
*/


/**** Functions ****/
function  saveterms($findme){
   foreach ( split(" ", $findme) as $term) {
		mysql_real_escape_string($term);
		$sql="INSERT into tvpress.terms (term,num) values ('". $term . "', '1') on duplicate key update num=num+1;";
		mysql_query($sql);
		}

}

function showpop(){
$eplist="/tv/show/"; 
$shows=array();
//$sql="SELECT  `showid`, count(*) as `num`  FROM `favdata` group by `showid` order by num DESC LIMIT 0, 10 ;"
$sql="SELECT  `showid`, count(*) as `num`  FROM `newsave` group by `showid` order by num DESC LIMIT 0, 10 ;";
$result=mysql_query($sql);
while ($line = mysql_fetch_array($result) ) {
  $sql="SELECT `showname`, `summary` from showdata where showid=". $line['showid'] . ";";
  $tres=mysql_query($sql);
  $show=mysql_fetch_assoc($tres);
    mysql_free_result($tres);
  
  $sql="select aka from akadata where showid = " . $line['showid'] . ";"; //find aka data in db
                        $akaresult=mysql_query($sql);
                        $akas=array();
                        while($thing = mysql_fetch_array($akaresult,MYSQL_ASSOC) ) {
                                $akas[]=$thing['aka'];
                        }
                        $aka ="";
                        $aka .= join(", ", $akas); //join them all in a nice single string
                        mysql_free_result($akaresult);
						//output
  echo "<A HREF='" . $eplist . $line['showid'] . "' onmouseover='TagToTip(\"". str_replace("&quot;","",$show['showname']). "\")' onmouseout=\"UnTip()\" > 
               <div id=\"". str_replace("&quot;","",$show['showname']) ."\" style='display:none;'> " . 
                wordwrap( $show['summary'], 60, " <br /> ") .  "</div> ". $show['showname'] .  "</A> ";
        if ( $line['aka'] != "" ) //any aka for above show
                echo "<ul style=\"font-size:80%\"> <li> Also known as: " . $aka . "</li> </ul>";
        echo "<br />";
		} //end while
		mysql_free_result($result);
}

function tvsearchform($findme){
	stripbadchars($findme);
	

	echo '
	<form id="tvsearch"><div>Search for a TV Show:</div>

	<div  style="display:inline;width:150px"><input name="search" type="text" value="'. str_replace('\&quot;', '&quot;',$findme) . '"/></div> ';
	if ($findme == "" ){
	echo '<div style="display:inline;float:right;background-color:#A9D0F5;min-width:100px;max-width:350px;"><H3> Try these Popular shows:</h3>' ;
		showpop() ;
		echo 	'</div>';
	}

echo '<input type="submit" value="Search" />

	</form>';
}

function stripbadchars( &$string) {
        $string=htmlentities($string,ENT_QUOTES, "UTF-8");
}

function tv_getresults( $findme ) {


$findme=mysql_real_escape_string($findme);
//$sql="select `showid` from `tvpress`.`showdata` where `showname` LIKE '%" . $findme . "%';";
$sql="select `showid` from `tvpress`.`showdata` where match (`showname`) against('" . $findme . "' ) OR `showname` LIKE '%" . str_replace(" ", "%", $findme) . "%';";

//echo "$sql <br/>";
$titleresult = mysql_query($sql);
//$sql="select `showid` from `tvpress`.`akadata` where aka LIKE '%" . $findme . "%';";
//$sql="select `showid` from `tvpress`.`showdata` where match (`aka`) against('" . $findme . "' IN BOOLEAN MODE) OR `aka` LIKE '%". $findme . "%';";
$sql="select `showid` from `tvpress`.`akadata` where match (`aka`) against('" . $findme . "' ) OR `aka` LIKE '%". str_replace(" ", "%", $findme) . "%';";

$akaresult =  mysql_query($sql);
//echo $sql;
//

$idar = array();
while ($line = mysql_fetch_array($titleresult, MYSQL_ASSOC) ) {
        $idar[] =  $line['showid'];
}
while ($line = mysql_fetch_array($akaresult, MYSQL_ASSOC) ) {
        $idar[] =  $line['showid'];
}
mysql_free_result($akaresult);
mysql_free_result($titleresult);

$idar = array_unique($idar);
$ids = join("','", $idar);
//echo "$ids";
unset($idar);
$sql="SELECT `showid`,`showname`, `summary` from `showdata` where `showid` IN ('$ids');";
$titleresult=mysql_query($sql);

$rar = array();
while ($line = mysql_fetch_array($titleresult, MYSQL_ASSOC) ) {
        $rar[] = array( 'showid' => $line['showid'], 'title' => $line['showname'], 'sum' => $line['summary']);
}
mysql_free_result($titleresult);
// print_r($rar);
return $rar;
} //end of tv_getreults



/****
 Main 
 ****/
 
 
$unresult = array(); // Unsorted results
$sresult = array(); //Sorted results
$searchurl="/tv/tvsearch?search="; //Realative url of the search
$episodelist="/tv/show/"; //Relative url of the episode-list
$searchstr= $_GET["search"]; //Get paramater for search
$searchstr=trim($searchstr); //Remove spaces
if ( $searchstr=="")  {
		tvsearchform(""); //show form with no input
        echo "Please enter a search string"; //Empty string
}
else {

tvsearchform($searchstr); //show pre-filled form
stripbadchars($searchstr); 
saveterms($searchstr);
$unresult = tv_getresults($searchstr); //get list of showids for search


$xar=array(); //Start of sorting array
$lmax=0; //Maximum result
foreach ( $unresult as $thing ) {
 //       $lev = similar_text( strtolower(str_replace("*", "", $thing['title'])), strtolower($searchstr)) ;
        $lev=1000; //base 1000
        $lev += levenshtein(strtolower(str_replace("*", "", $thing['title'])), strtolower($searchstr), 1, 7, 2) ; //Get lev distance
        $sim = similar_text( strtolower(str_replace("*", "", $thing['title'])), strtolower($searchstr)) ; //See how many characters are similar

foreach ( split(" ", $searchstr) as $sval) {
                if (stristr(str_replace("*", "", $thing['title']), $sval) !== FALSE ) { ///Does it contain one of the words in the search string? 
                        $lev -= 25; //Move it up in results
                }
				
				if ( preg_match("/^" . $sval . "/i", str_replace("*", "", $thing['title'])) != 0) { //does a word in the string start the string?
                        $lev -=25; //Move it up in results - whole word matches will move far more because of  secondary whole word check
                }
				if ( preg_match("/^" . $sval . "[[:punct:]]/i", str_replace("*", "", $thing['title'])) != 0) { //word. or word- etc , move lower
                        $lev +=15; //Move it up in results - whole word matches will move far more because of  secondary whole word check
                }
				if ( preg_match("/^" . $sval . "\b/i", str_replace("*", "", $thing['title'])) != 0) { //does a word in the string start the string?
                        $lev -=25; //Move it up in results - whole word matches will move far more because of  secondary whole word check
                }
				if ( preg_match("/" . $sval . "\b/i", str_replace("*", "", $thing['title'])) != 0) { //does a word in the string appear as a whole word?
                        $lev -=15; //Move it up in results - whole word matches will move far more because of  secondary whole word check
                }
        } //end foreach

        if ( $sim == 0 )  //search string is not at ALL similar
            $sim += -20; //move down results
         if ($sim == strlen($searchstr)) //number of charcters in search string match the number of similar characters found
              $sim += +20; //move up results

         $lev -= $sim; //actual movement by weights above

         if ($lev < 0 ) //If somehow we move this thing so high in the results we hit real 0, set it to 0
              $lev=0;

        $x=0;
 while ($x ==0) { //while we have not put this thing in the array
                if ( ! array_key_exists($lev, $xar) ) { //do not already have a key with this search position
                        $sql="select aka from akadata where showid = " . $thing['showid'] . ";"; //find aka data in db
                        $akaresult=mysql_query($sql);
                        $akas=array();
                        while($line = mysql_fetch_array($akaresult,MYSQL_ASSOC) ) {
                                $akas[]=$line['aka'];
                        }
                        $aka ="";
                        $aka .= join(", ", $akas); //join them all in a nice single string
                        mysql_free_result($akaresult);
                        unset($akas);
                        $xar[$lev] = array( 'showid' => $thing['showid'] , 'title' => $thing['title'] ,  'lev' => $lev, 'aka' => $aka, 'sum' => $thing['sum']); //slap it in the array
                        $x=1;
                        if ($lev >$lmax) {
                                $lmax=$lev; //move the maximum result
                        }
                }
                else {
                        $lev += 1; //Already taken, move down 1
                
                        }
        }
}
$l=0; //init l
while ( $l <= $lmax) { //walk the array
        if (array_key_exists($l, $xar)){

        $tmpsid = $xar[$l]['showid'];
        $tmpt = $xar[$l]['title'];
        $tmpa = $xar[$l]['aka'];
        $tmpl = $xar[$l]['lev'];
        $tmpsum = $xar[$l]['sum'];
        $sresult[] = array( 'showid' => $tmpsid , 'title' => $tmpt , 'aka' => $tmpa, 'lev' => $tmpl, 'sum' => $tmpsum); //assign to sorted array
        unset($tmpsid);
        unset($tmpt);
        unset($tmpa);
        unset($tmpl);
        unset($tmpsum);
        }
        $l += 1;

}
//print_r($unresult);
//output stuff
$results=0;
echo "<ul style=\"list-style-type: none;\">Results: <BR />";

foreach ($sresult as $line){ //output each result

        echo "<Li><A HREF='" . $episodelist . $line['showid'] . "' onmouseover='TagToTip(\"". str_replace("&quot;","",$line['title']). "\")' onmouseout=\"UnTip()\" > 
               <div id=\"". str_replace("&quot;","",$line['title']) ."\" style='display:none;'> " . 
                wordwrap( $line['sum'], 60, "<BR />") .  "</div> ". $line['title'] .  /*" " . $line['lev'] . */ "</A> ";
        if ( $line['aka'] != "" ) //any aka for above show
                echo "<ul style=\"font-size:80%\"> <li> Also known as: " . $line['aka'] . "</li> </ul>";
        echo "</li>";
                $results +=1;
                $showid=$line['showid'];
        }



//if ( $results == 1 ) 
  //      echo "<script> location.href=\"" . $episodelist . $showid . "\"  </script>";
if ( ($results == 0) )
        echo "<li>No results found</li>"; //please try your call again.
        //echo "<script> location.href=\"" . $searchurl . $searchstr . "%\" </script>";

echo "</ul>";
//while ($line = mysql_fetch_array($result)) {
/*
echo "xar <BR />";
print_r($xar);
echo "unresult <BR />";
print_r($unresult);
echo "sresult <BR />";
print_r($sresult);
 */
}

?>

