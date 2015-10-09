//Stats
<?php
function termpop(){
   $sql="select * from tvpress.terms order by num desc limit 0, 30;";
   $result=mysql_query($sql);
   $off2=5;
   $offset=-10;
   
   while ($line = mysql_fetch_array($result) ) {
			$size=70;
			/*
			if ( $offset == 1) {
			if ( $line['num'] > 230) {
			     $offset = $line['num'] - 60;
				}
				
				if ( $line['num'] < 10) { 
				 	  $offset -= $line['num'] + 40;
				 
				  }
				 } */
				$size -= $offset;
				$size += ($line['num'] *$off2);
				//echo "offset $offset size: $size";
			echo "<A onmouseover='Tip(".$line['num'].")' onmouseout='UnTip()' style=\"color:#365da0;font-size:". $size ."%\"> " . $line['term'] . "</a>";
				}
				
				//echo "End of termpop $offset";
mysql_free_result($result);


}
function showpop(){
$eplist="/tv/tvsearch/episode-list?showid="; 
$shows=array();
//$sql="SELECT  `showid`, count(*) as `num`  FROM `favdata` group by `showid` order by num DESC LIMIT 0, 10 ;"
$sql="SELECT  `showid`, count(*) as `num`  FROM `newsave` group by `showid` order by num DESC LIMIT 0, 50 ;";
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
function favpop(){
$eplist="/tv/tvsearch/episode-list?showid="; 
$shows=array();
$sql="SELECT  `showid`, count(*) as `num`  FROM `favdata` group by `showid` order by num DESC LIMIT 0, 50 ;";
//$sql="SELECT  `showid`, count(*) as `num`  FROM `newsave` group by `showid` order by num DESC LIMIT 0, 50 ;";
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


function visitpop(){
$eplist="/tv/tvsearch/episode-list?showid="; 
$shows=array();
$sql="SELECT showid,visits  FROM `showdata` WHERE `visits` != 0  ORDER BY `showdata`.`visits`  DESC LIMIT 0, 50";

//$sql="SELECT  `showid`, count(*) as `num`  FROM `newsave` group by `showid` order by num DESC LIMIT 0, 50 ;";
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


echo "<div style='border: solid 4px #eee;min-width:100px;max-width:350px;'> ";
echo "<H3> Popular Search Terms:</H3>";
termpop();
echo "</div>";
echo "<div style='border: solid 4px #eee;min-width:100px;max-width:350px;display:inline;float:left;'> ";
echo "<H3> Most Popular Shows with data saved:</H3>";
showpop();
echo "</div>";
echo "<div style='border: solid 4px #eee;min-width:100px;max-width:350px;display:inline;float:left;'> ";
echo "<H3> Popular Shows set as favorites:</H3>";
favpop();
echo "</div>";
echo "<BR />";
echo "</div>";
echo "<div style='border: solid 4px #eee;min-width:100px;max-width:350px;display:inline;float:left;'> ";
echo "<H3> Most Popular Shows by total visits:</H3>";
visitpop();
echo "</div>";

?>