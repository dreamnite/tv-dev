//Show information 

<?php
/*
<script language="Javascript">

function checkAll(obj)
{
  for (i=0;i<obj.length; i++) {
    obj[i].checked = true;
  }
}
</script>
Example onclick
onclick="checkAll(document.checkForm.itmBox)"
*/
function  savesid($showid){
   		$sql="UPDATE `tvpress`.`showdata` SET `visits` = visits + 1 WHERE `showdata`.`showid` =$showid;";
		mysql_query($sql);
		//echo $sql;

}


function isBitSet($bitMap, $bitMask)
  {
  //For use only with gmp bmaps
  $a=gmp_init(gmp_strval($bitMap));
//gmp_setbit($a, 3);
//gmp_setbit($a, 1);
//echo gmp_strval($a);
//echo "\n\n";
$b=gmp_init(gmp_strval($a));
//$b=$a;
//echo gmp_strval($b) . "\n";
gmp_clrbit($b,$bitMask);

//echo gmp_strval($b) . "\n";
$cmp= gmp_cmp($a , $b);
//echo $cmp;
if  ( $cmp == 0 )
	  return 0;
     //   echo "Values are the same\n";
else
	 return 1;
        //echo "No match\n";
  //return gmp_and($bitMask, $bitMap);
    //return (bool) gmp_intval(gmp_div(gmp_and($bitMask, $bitMap),$bitMask));
  }

function tvsave() { 
                $user = wp_get_current_user ();
                if ($user->ID > 0)
                        { //start if 1
                                $eparray= array() ;
                                $havearr= array() ;
                                foreach ($_POST as $thing => $value) { //start foreach1 
                                       //     echo "$thing = $value\n";
                                        if ($thing == "showid" ) { //start if 2
                                                $showid=$value;
                                        } //close if 2
                                        elseif ($thing == "favorite") {
                                                if ($value == "true"){
                                                        $fav="true";
                                                }
                                        }
                                        else { //start else
                                                $parts=split("-",$thing);
                                                if ( $parts[0] == "have") {
                                                    $season=$parts[1];
                                                    $episode= $parts[2];
                                                    //$episode= 1 << $episode;
                                                        if ($value == "true"){
															if ( !isset($havearr[$season])) {
																$havearr[$season]=gmp_init(0);
															}
														   
														gmp_setbit($havearr[$season], $episode);
														}
                                                } //close if 3
                                                else {
                                                $season=$parts[0];
                                                $episode=(float) $parts[1];
                                                //$episode= 1 << $episode;
                                                        if ($value == "true"){
															if (!isset($eparray[$season])){
																$eparray[$season] = gmp_init(0);
															}
														
                                                        gmp_setbit($eparray[$season],$episode);
														}
                                                } //close else 2
                                        } //close else

                        } //end foreach
 if ($fav == "true") {
                        $fsql= "INSERT into tvpress.favdata ( `userid` , `showid` ) VALUES ( ' " . $user->ID . "', '" . $showid . "'); " ;
                        }
                else {
                        $fsql = "DELETE from tvpress.favdata where showid=$showid and userid=" . $user->ID .";";
                        }
                mysql_query($fsql);

                foreach ( $eparray as $s => $e ) {
                 $ins= "INSERT INTO `tvpress`.`newsave` ( `userid` , `showid` , `season` , `epdata` ) VALUES ( '" . $user->ID . "', ' $showid ', '$s', '". gmp_strval($e). "') ON DUPLICATE KEY UPDATE epdata=". gmp_strval($e) . ";";
                  mysql_query($ins);
                } //end foreach 1
                foreach ( $havearr as $s => $e ) {
                 $ins= "INSERT INTO `tvpress`.`newsave` ( `userid` , `showid` , `season` , `havedata` ) VALUES ( '" . $user->ID . "', ' $showid ', '$s', '". gmp_strval($e) . "') ON DUPLICATE KEY UPDATE havedata=". gmp_strval($e). ";";
                  mysql_query($ins);
                } //end foreach 1

        }       //end if 1
                return true;
        }

$showid=$_GET["showid"];
//echo $showid;
//if  (isset($_POST)){

tvsave();

//}
if ( current_user_can( 'edit_pages' )) {
$edit = true;
}
else {
$edit = false;
}
$editlink="/tv/tvsearch/episode-list/edit-show";

if ( !is_numeric( $showid ) ) {
        echo "We're sorry, we could not find the show you have selected";
}
else {
//$showid=1;
savesid($showid);

//$url="http://show-track.com/tv/show/{$showid}";
$showlink="/tv/show/{$showid}";			
//echo  "<p class='fb-like'><iframe src='http://www.facebook.com/plugins/like.php?href={$url}&amp;layout=standard&amp;show_faces=true&amp;width=260&amp;action=like&amp;colorscheme=light'  scrolling='no' frameborder='0' allowTransparency='true' style='border:none; overflow:hidden; width:260px; height:26px'></iframe></p>";

$slimit=$_GET["season"];
if ($slimit == "ALL") {
        $seasonsql="";
}

elseif ( !is_numeric($slimit) ) {
$slimit=-1;
}
elseif ($slimit > -1 ) {
        $seasonsql = "and season=$slimit";
}

else
$seasonsql = "";


$user = wp_get_current_user ();
/* check favorites */
                if ($user->ID > 0)
                {

$favsql = "select showid from tvpress.favdata where showid='$showid' and userid='" . $user->ID . "';";
//echo $favsql;
$fres = mysql_query($favsql);
$fav = mysql_result($fres,0);
mysql_free_result($fres);
//echo $fav;
}
else
   $fav=0;
if ($fav == $showid) {
        $favtxt = 'checked="checked"';
        }
else {
        $favtxt = "";
}

//$showid=4628;
$sql="select showname, origin, status, class, genre, network, summary, image  from showdata where showid=$showid;";
$showname = mysql_query($sql);
$line=mysql_fetch_array($showname);
echo "<H2> " . $line['showname'] . "</H2>";
if ($edit) {
echo "<A HREF=\"". $editlink . "?sid=". $showid ."\"> Edit this show </a>\n ";
}
echo "
        <table>
        <tr> <td> Origin: </td> <td> " . $line['origin'] . "</td></tr>
        <tr> <td> Status: </td> <td> " . $line['status'] . "</td></tr>
        <tr> <td> Class: </td> <td> " . $line['class'] . "</td></tr>
        <tr> <td> Genres: </td> <td> " . $line['genre'] . "</td> </tr>
        <tr> <td> Network: </td> <td> ". $line ['network'] . "</td> </tr>
        </table>
        <table>\n";

if ($line['summary'] != "" ) {
        echo "
        <tr> <td> <A onclick=\"changeVisibility('showsum')\" > Show/Hide Summary </a> </td></tr>
        <tr id=\"showsum\" style='visibility:collapse;'> <td> ". $line['summary'] . " </td></tr>\n";
}
echo "  <tr> <td> <image src=\"http://" . $line['image'] . "\"/> </td> </tr>";

echo "</table>";

mysql_free_result($showname);
                if (empty ($user) || $user == '' || $user->ID == 0)
                {
                       echo "<p> To start tracking your favorite shows, Connect with Facebook or log in/register using the box on the right. Questions, Comments? <A HREF='/tv/contact-us'> Let us know! </a></p>";
                        $id=0;
                }
                else
                   $id=$user->ID;
				   echo "<p> Please note: Episode summaries, if available, can be viewed by hovering over the title. </p>";
$links=array(array());
$sql="select season, episode, service, link from tvpress.watchlinks where showid=$showid;";
$lresult=mysql_query($sql);
while ($line = mysql_fetch_assoc($lresult) ) {
       $s=$line['season'];
       $e=$line['episode'];
       $links[$s][$e]=array("link" => $line['link'], "service" => $line['service']);
       unset($s);
       unset($e);
}
mysql_free_result($lresult);

echo '<form method="post"  name="showform">';
echo " <table>  <tr><td> <input type=\"checkbox\" name=\"favorite\" value=\"true\" " . $favtxt . " /> </td> <td> Make this show a favorite </td></tr> </table><br />";

echo "Season: <BR /> <center>";
$sql="select season from epdata where showid=$showid group by season;";
$result= mysql_query($sql);
while ($row = mysql_fetch_row($result) ) {
        if ($row[0] == 0)
        echo "<A HREF=\"{$showlink}&season=" . $row[0] . "\"> Specials/Movies </A> | ";
        else
        echo "<A HREF=\"{$showlink}&season=" . $row[0] . "\"> Season " . $row[0] . " </A> | ";
//    echo $slimit;
    /*if ($slimit == -1) {
                 $slimit=-2;
                $seasonsql="and season=". $row[0];
        }*/
}
mysql_free_result($result);
        echo "<A HREF=\"{$showlink}&season=ALL\"> All </A> <BR /> ";

$sql="select season, episode, prodid, airdate, title, summary from epdata where showid=$showid " . $seasonsql . " order by season;";
$result = mysql_query($sql);
if (mysql_num_rows($result)==0) {
        echo "We currently do not have any episodes for this show";
}
else {
$epdata = 0;

//echo $query;
echo "<input type=\"hidden\" name=\"showid\" value=\"$showid\">";
//foreach ( mysql_fetch_array($result); as $line )
$currseason=-1;
while ($line = mysql_fetch_array($result)) {
        if ( $line['prodid'] == "NULL" ) {
                $line['prodid'] = '';
        }
        if ( $line['season'] != $currseason ) {
                if ($currseason != -1 ){
                        echo "</table>";
//                        echo "<input type=\"submit\" Value=\"Save\">\n";
                }
                
				 echo " <input type=\"submit\" Value=\"Save\"><BR />\n";
				 echo "<BR />\n";
                if ($line['season'] ==0)
                        echo "<B> Specials/Movies </B>";
                else
                echo "<B> Season " . $line['season'] . "</b>"; 
               
                echo "<BR />\n";
                 $currseason=$line['season'];
                 $epsql="SELECT epdata from newsave where showid=$showid AND season=" . $line['season'] . " AND userid=$id;";
                 $epresult = mysql_query($epsql);
                 $epbits = gmp_init( mysql_result($epresult,0));
                 $havesql="SELECT havedata from newsave where showid=$showid AND season=" . $line['season'] . " AND userid=$id;";
                 $haveresult = mysql_query($havesql);
                 $havebits = gmp_init(mysql_result($haveresult,0));
                 mysql_free_result($haveresult);
                 mysql_free_result($epresult);
                 // echo "<BR /> $epbits <BR /> $epsql <BR /> $epresult <br />" . mysql_result($epresult) . "<BR />";
				echo "<A onclick=\"changeVisibility('season-table-". $line['season'] . "')\" > Show/Hide this season </a><BR />";
                echo "<A onclick=\"checkAll(document.showform.seen" . $currseason . ")\"> Mark all episodes in this season as seen </A> <BR />  <A onclick=\"checkAll(document.showform.have" . $currseason . ")\"> Mark all episodes in this season as available </A>   ";
        echo "<input type=\"hidden\" name=\"$currseason-0\" value=\"true\" >";
        echo "<input type=\"hidden\" name=\"have-$currseason-0\" value=\"true\" >";
  echo "<Table border=1 id='season-table-". $line['season'] . "' style='visibility:visible;' rules=\"row\" >\n<tr>\n<th>Seen</th><th>Available</th><th>Episode</th><th>Original Airdate</th><th>Title</th><th> Watch Now </th></TR>";
        }
echo "<tr>";
$epck=  $line['episode'];
if ( isBitSet($epbits, $epck ))
        echo "\n<td>" . '<input type="checkbox" id="seen' . $line['season'] . '" name="' . $line['season'] . '-' . $line['episode'] . '"' . ' value="true" checked="checked" /></TD> ';

else
echo "\n<td>" . '<input type="checkbox" id="seen' . $line['season'] . '" name="' . $line['season'] . '-' . $line['episode'] . '"' . ' value="true" /></td> ';
//echo $line;
$haveck= $line['episode'];
if ( isBitSet($havebits, $haveck ) )
echo "\n<td>" . '<input type="checkbox" id="have' . $line['season'] . '" name="have-' . $line['season'] . '-' . $line['episode'] . '"' . ' value="true" checked="checked" /></td><td> ' . $line['episode'] . '</td><td>  '  . $line['airdate'] . '</td>  ';

else
        echo "\n<td>" . '<input type="checkbox" id="have' . $line['season'] . '" name="have-' . $line['season'] . '-' . $line['episode'] . '"' . ' value="true" /></td><td> ' . $line['episode'] . '</td><td>  ' . $line['airdate'] . '</td>  ';

$s=$line['season'];
$e=$line['episode'];
if ($line['summary'] != ""){
        echo "<td> <A onmouseover='TagToTip(\"". str_replace("&quot;","",$line['title']). $line['season'] . "-". $line['episode'] . "\")' onmouseout=\"UnTip()\">" . $line['title'] .  '</a> <div id ="'. str_replace("&quot;","",$line['title']). $line['season'] . "-". $line['episode'] .'" style="display:none;"> ' . wordwrap( $line['summary'], 60, " <br /> ") . '</td>' ;
}
else {
        echo "<td> <A onmouseover=\"Tip('No Summary Available')\" onmouseout=\"UnTip()\">" . $line['title'] .  '</td>' ;
}

if (isset($links[$s][$e]['service'])){
echo  '<td> <A href="'. $links[$s][$e]['link'] . '"> Watch on ' . $links[$s][$e]['service'] . ' </a> </td>'. "\n";
}
else {
echo ' <td> Not Available  </td>' . "\n";
}
if ($edit) {
echo "<td> <A HREF=\"". $editlink . "?sid=". $showid ."&season=". $s ."&episode=". $e . " \"> Edit episode </td>";
}
echo "</tr>";
}
echo "</table>";
}
mysql_free_result($result);

echo "<input type=\"submit\" id=\"submit\" name=\"submit\" Value=\"Save\">\n";
echo " </form>\n";
}
?>

