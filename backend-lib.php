<?php
//backend-lib.php
/*** A unified back end php library to return JSON objects for the front end ****
**** Contains functions needed by the backend to provide information *****/

/****  MYSQL required code ********/

//Special user 0 = not logged in

function tvdbconnect(){
 // Connect to the Database
 $link = mysql_connect('localhost', 'tvdb', 'sh0wmaster');

        if (!$link) { //no link, die
                die('Could not connect: ' . mysql_error());
        }
}

/**** Favorites list ******
 *** Functions pertaining to the favorites list ***
 Status: Returns JSON Code. / Complete
 
*/

function favlist($user) {

                if (empty ($user) || $user == ""|| $user == 0)
                {
				echo " { 
							error: \"not logged in\" 
							}";
                // echo "Log in to save your favorites!";
                }
else {
	$myfavsql = "select showid from tvpress.favdata where userid='" . $user . "';";
//echo $favsql;

	echo "{ ";
	echo "favorites: [ ";
	$count=0;
	$myfres = mysql_query($myfavsql);
	while ($row = mysql_fetch_row($myfres) ) {

		$titlesql="select showname from tvpress.showdata where showid='". $row[0] ."';";
		$titleres= mysql_query($titlesql);
		$title=mysql_result($titleres,0);
		mysql_free_result($titleres);
		if ( $count == 0 ) {
			echo "{  id: ". $row[0] . ", title: \"". $title . "\" }";
		}
		else {
			echo ", {  id: ". $row[0] . ", title: \"". $title . "\" }";
		}
		$count++;
	//$fav = mysql_result($fres,0);
	}
	echo " ] \n }";
}
mysql_free_result($myfres);
//echo $fav;
}

/***********************End of favorite list functions **************************/
/********* Search related functions ********/

/** Store search terms to the database ***/
//May or may not be used in final
function  saveterms($findme){
   foreach ( split(" ", $findme) as $term) {
		mysql_real_escape_string($term);
		$sql="INSERT into tvpress.terms (term,num) values ('". $term . "', '1') on duplicate key update num=num+1;";
		mysql_query($sql);
		}

}

//Show pop
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
//Shows the tv search form
/*** Should not be needed now with front end seperation ****
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
*/


/** Make strings utf-8 happy ***/
// Status - complete
function stripbadchars( &$string) {
        $string=htmlentities($string,ENT_QUOTES, "UTF-8");
}
///End stripbadchars
/// TV get results based on parameter findme
function tv_getresults( $findme ) {


$findme=mysql_real_escape_string($findme); //Make the string safe to use with mysql

/*** Actual search sql***/
$sql="select `showid` from `tvpress`.`showdata` where match (`showname`) against('" . $findme . "' ) OR `showname` LIKE '%" . str_replace(" ", "%", $findme) . "%';";

//echo "$sql <br/>"; //debug

$titleresult = mysql_query($sql); //Results bases on title of show

$sql="select `showid` from `tvpress`.`akadata` where match (`aka`) against('" . $findme . "' ) OR `aka` LIKE '%". str_replace(" ", "%", $findme) . "%';";

$akaresult =  mysql_query($sql); //Results based on akas for show
//echo $sql;
//

$idar = array(); // Array of show ids
while ($line = mysql_fetch_array($titleresult, MYSQL_ASSOC) ) {
        $idar[] =  $line['showid'];
}
while ($line = mysql_fetch_array($akaresult, MYSQL_ASSOC) ) {
        $idar[] =  $line['showid'];
}
mysql_free_result($akaresult);
mysql_free_result($titleresult);

$idar = array_unique($idar); //Eliminate duplicate ids from the array
$ids = join("','", $idar);
//echo "$ids";
unset($idar);
$sql="SELECT `showid`,`showname`, `summary` from `showdata` where `showid` IN ('$ids');"; //Based on the ids, get the titles, summary
$titleresult=mysql_query($sql);

$rar = array(); //Populate the return array
while ($line = mysql_fetch_array($titleresult, MYSQL_ASSOC) ) {
        $rar[] = array( 'showid' => $line['showid'], 'title' => $line['showname'], 'sum' => $line['summary']);
}
mysql_free_result($titleresult);
// print_r($rar);
return $rar; //Return an array of show ids, titles, and summaries
} //end of tv_getreults


/************* Tvsearch() *********************/
// Uses the get parameter search to return JSON results
// todo: Make GET parameter a function parameter.

function tvsearch(){

//Variable declarations
$unresult = array(); // Unsorted results
$sresult = array(); //Sorted results
//Link constants - CLEANUP aisle 7.
$searchurl="/tv/tvsearch?search="; //Realative url of the search
$episodelist="/tv/show/"; //Relative url of the episode-list


$searchstr= $_GET["search"]; //Get paramater for search

$searchstr=trim($searchstr); //Remove spaces
if ( $searchstr=="")  {
		//tvsearchform(""); //show form with no input - No longer show form as we are now return JSON
        //echo "Please enter a search string"; //Empty string
		echo " { 
							error: \"Search String Empty\" 
							}";
}
else {

// tvsearchform($searchstr); //show pre-filled form Ajax will now take care of this

stripbadchars($searchstr);  //Make searchstr utf-8 clean
// saveterms($searchstr); // Saves the individual search terms to the database - CLEANUP - I do not think this is needed anymore


$unresult = tv_getresults($searchstr); //get unsorted results

{  //Sorting results -TODO: Split into a function
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

}
//End of sorting results
//print_r($unresult);

//output stuff
$results=0;
//echo "<ul style=\"list-style-type: none;\">Results: <BR />";
echo " { 
			searchresults: [ ";
			
foreach ($sresult as $line){ //output each result

        echo "{ showid: " .  $line['showid'] . ", summary: \"" . str_replace("&quot;","",$line['sum']) . "\", title: \"". str_replace("&quot;","",$line['title']) ."\",  " ;
          if ( $line['aka'] != "" ) //any aka for above show
                echo " aka: " . $line['aka'] . " ";
        if ($results == 0) {
		echo "}";
			}
		else { 
		    echo "}, ";
			}
                $results +=1;
                $showid=$line['showid'];
        }



//if ( $results == 1 ) 
  //      echo "<script> location.href=\"" . $episodelist . $showid . "\"  </script>";
if ( ($results == 0) )
        echo "<li>No results found</li>"; //please try your call again.
        //echo "<script> location.href=\"" . $searchurl . $searchstr . "%\" </script>";

echo " ] 
				
		}";
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
}

?>
