<?php

echo "<html>   
<head>
<meta name=\"txtweb-appkey\" content=\"fa0d179a-9e40-4d3f-a0f6-cba551dfd3a8\">
</head>
<body>";
$con = mysql_connect('localhost', 'root', 'TxtApp@DB$123');
$con = mysql_select_db('txtweb');
error_reporting(1);
include_once('../simple_html_dom.php');
$homepage = 0;
echo "<form action='http://ec2-184-169-171-66.us-west-1.compute.amazonaws.com/txtweb/ftbl_grp_reg.php' method='get' class='txtweb-form'>";
	echo "Mobile Number";
	echo "<input type='text' name='num'>";
	echo "</form>";
	echo "to subscribe for live alerts<br><br>";

function getDetailedScore($url)
{
	$htm=file_get_html($url);
	$rows=$htm->find('div[class*=row-]');
	$teams= explode(" vs ",$htm->find('title',0)->plaintext);
	$hometeam=str_replace("LiveScore : ","",$teams[0]);
	$awayteam=$teams[1];
	/*$shortTeam=array("FC Goa"=>"Goa","Chennaiyin FC"=>"Chennai","Delhi Dynamos FC"=>"Delhi","FC Pune City"=>"Pune","Northeast United FC"=>"NUFC",
				"Kerala Blasters FC"=>"Keralam","Atletico de Kolkata"=>"Kolkata","Mumbai City FC"=>"Mumbai");
	$awayteam=$shortTeam[trim($awayteam)];
	$hometeam=$shortTeam[$hometeam];*/
	foreach($rows as $row)
	{
		if(!$row->parent()->getAttribute('data-type'))
		{
		echo $row->plaintext;
		if($row->getAttribute('class')!="row row-tall")
		{
		if($row->find('span[class=inc redyellowcard]'))
			echo "(2nd Yellow-RED Card!)";
		if($row->find('span[class=inc redcard]'))
			echo "(RED Card!)";
		if($row->find('span[class=inc yellowcard]'))
			echo "(Yellow Card)";
		if($row->find('span[class=name]',0))
		{
		$playername=$row->find('span[class=name]',0);
		if(strlen($playername->plaintext)<1)
			$playername=$row->find('span[class=name]',1);
		if($playername->parent()->parent()->getAttribute('class')=="ply tright")
			echo "({$hometeam})";
		else
			echo "({$awayteam})";
		}
		}
		echo "<br>";
		}
	}
	$statsrows=$htm->find('div[data-type=stats]',0)->find('div[class*=row-]');
	if($statsrows)
		echo "<br>-STATS-<br>";
	foreach($statsrows as $row)
	{
		echo $row->plaintext.'<br>';
	}

	die;

}
function allow($league) {
    $leagues = array("England - Premier League", "Italy - Serie A", "Spain - Liga BBVA", "Germany - Bundesliga", "Champions League");
    foreach ($leagues as $ele) {
        if (stristr($ele, trim($league)))
            return true;
    }
    return false;
}

function search($message) {
    echo "Search Results:<br>";
    $url = "http://www.livescore.com/soccer/";
    $htm = file_get_html($url);
    $rows = $htm->find('div[class=content] table[class=league-table] tr');
    $live = 0;
    $find = 0;
    foreach ($rows as $row)
    /* foreach($row->find('td') as $ele)
      { */
        if (stristr($row->plaintext, $message) && !$row->find('span[class=league]') && !$row->find('span[class=date]')) {
            $find = 1;
            if ($row->find('img[alt=live]'))
                $live = 1;
            if ($link = $row->find('a[class=scorelink]')) {
                echo "<a href='http://ec2-184-169-171-66.us-west-1.compute.amazonaws.com/txtweb/football-working.php?url={$link[0]->href}'>";
                if ($live)
                    echo "LIVE ";
                echo " {$row->plaintext} </a><br>";
            }
            else {
                if (stristr($time = $row->find('td[class=fd]', 0)->plaintext, ":")) {
                    date_default_timezone_set("GMT");
                    $str = strtotime($time);
                    date_default_timezone_set("Asia/Kolkata");
                    $time = "(" . date("h:i A", $str) . ")";
                    $row->find('td[class=fd]', 0)->innertext = $time;
                }
                if ($live)
                    echo "LIVE ";
                echo $row->plaintext . '<br>';
            }
            $live = 0;
        }
    return $find;
//}
}

function home($league) {

    $url = "http://www.livescore.com/soccer/";
    $htm = file_get_html($url);
    $leagues = $htm->find('span[class=league] span a');
    foreach ($leagues as $ele) {
        //echo $ele->plaintext.'<br>';
        if ($ele->href != $league)
            echo "<a href='http://ec2-184-169-171-66.us-west-1.compute.amazonaws.com/txtweb/football-working.php?league={$ele->href}' class='txtweb-menu-for'> {$ele->parent()->parent()->plaintext}</a><br>";
    }
    die;
}

if (!empty($_GET['txtweb-message'])) {
    if (search($_GET['txtweb-message']))
        die;
    echo "Nothing found for '{$_GET['txtweb-message']}'.<br>-<br>";
}if (isset($_GET['league']))
    $league = $_GET['league'];

else {
    $url = "http://www.livescore.com/soccer/live";
    $htm = file_get_html($url);
    $homepage = 1;
    if (!isset($_GET['url'])) {
        $msg = ($table = $htm->find('div[class=content] table[class=league-table]')) ? "Live Matches:<br>" : "No live macthes<br>:";

        echo $msg;
    }
//$league=$htm->find('span[class=league] span a',0)->href;
}


 
if (isset($_GET['league']))
    $url = "http://www.livescore.com" . $league;
$htm = file_get_html($url);
$table = $htm->find('div[class=content] table[class=league-table]');
$i = 0;
$j = 0;
date_default_timezone_set("Asia/Kolkata");
  $time = date("g:i:s a D, M j y");
  $my_file = '../logftbl.txt';
  $protocol=$_GET['txtweb-protocol'];
  $handle = fopen($my_file, 'a+') or die('Cannot open file:  ' . $my_file);
  $data = "{$url} - ".$_GET['txtweb-mobile']. " - " . $time . "\n";
  if($protocol=="1000")
  fwrite($handle, $data);
  fclose($handle);
  
  //individual match-details
if (isset($_GET['url'])) {

    $url = "http://www.livescore.com" . $_GET['url'];
    getDetailedScore($url);
    $htm = file_get_html($url);
    $row = $htm->find('table[class=match-details match-ellipsis league-table mtn] tr');
    $hometeam = $htm->find('th[class=home]', 0)->getAttribute('title');
    $awayteam = $htm->find('th[class=awy]', 0)->getAttribute('title');
    foreach ($row as $ele) {

        if ($ele->getAttribute('class') == 'menu')
            continue;
        if ($ele->find('[class=title]'))
            echo "-<br>";
        echo ucwords($ele->plaintext);

        if ($ele->find('span[class=inc yellowcard left]'))
            echo " [YC] ({$awayteam})";
        if ($ele->find('span[class=inc yellowcard right]'))
            echo " [YC] ({$hometeam})";
        if ($ele->find('span[class=inc redcard left]'))
            echo " [RED] ({$awayteam})";
        if ($ele->find('span[class=inc redcard right]'))
            echo " [RED] ({$hometeam})";
        if ($ele->find('span[class=inc redyellowcard right]'))
            echo " [2nd Yellow(RED] ({$hometeam})";
        if ($ele->find('span[class=inc redyellowcard left]'))
            echo " [2nd Yellow(RED)] ({$awayteam})";
        if ($ele->find('span[class=inc goal-miss left]'))
            echo " [Pen. Miss] ({$awayteam})";
        if ($ele->find('span[class=inc goal-miss right]'))
            echo " [Pen. Miss] ({$hometeam})";
        if ($ele->find('span[class=inc goal right]'))
            echo " [GOAL] ({$hometeam})";
        if ($ele->find('span[class=inc goal left]'))
            echo " [GOAL] ({$awayteam})";
        echo "<br>";
    }
    $home = 1;

if($stats=$htm->find('table[data-type=stats] tr[class]'))
echo "<br>STATS<br>";
foreach($stats as $ele)
{
	foreach($ele->find('td') as $cols)
		echo $cols->plaintext.' ';
	echo "<br>";
}
$subin=$htm->find('[class=inc sub-in]');
$subout=$htm->find('[class=inc sub-out]');

$subs=array();
/*
foreach($subin as $player)
{
	$sub["min"]= $player->parent()->prev_sibling ()->plaintext;
	$sub["player"]= $player->plaintext;
	$sub["mode"]="IN";
	array_push($subs,$sub);

}


foreach($subout as $player)
{
	$sub["min"]= $player->parent()->prev_sibling ()->plaintext;
	$sub["player"]= $player->plaintext;
	$sub["mode"]="OUT";
	array_push($subs,$sub);

}

foreach($subs as $sub)
	echo $sub["min"]." ".$sub["player"]." ".$sub["mode"].'<br>';

*/

 $sub = $htm->find('tr[class*=subs] td');
 echo "-<br>SUBS:<br>";

    foreach ($sub as $ele) {
		if($ele->find('[class=inc sub-in]'))
		{
        if ($ele->getAttribute('class') == 'min')
            echo $ele->plaintext;
        if ($ele->getAttribute('class') == "ply") {
            $names = $ele->find('div[class]');
            foreach ($names as $name)
                if ($name->getAttribute('class') == "inc sub-in")
	{
                    echo $name->plaintext . " IN ";
	 $team = $home ? $hometeam : $awayteam;
                //echo " ({$team})".'<br>';
	echo "<br>";
	$home = $home ? 0 : 1;
	}
                else
                    echo $name->parent()->prev_sibling ()->plaintext." ".$name->plaintext . " OUT/";
           /* if ($ele->find('div')) {

                $team = $home ? $hometeam : $awayteam;
                echo " ({$team})".'<br>';
            }
            $home = $home ? 0 : 1;
            continue;*/
        }
        //echo $ele->plaintext.'<br>';;
    }
	
}
    echo "<br>--";
	
die;
}

/*if (!$homepage)
    echo $htm->find('span[class=league]', 0)->plaintext . '<br>';*/
$scorerow = "";
foreach ($table as $ele) {
	//$allow=1;
    $rows = $ele->find('tr');
    foreach ($rows as $row) {
        /* if($row->find('th'))
          echo "<br>"; */
	/*if(!$allow)
		continue;*/
	if (!$homepage)
        if ($date = $row->find('[class=date]', 0)) {
                echo "<br>".$date->plaintext;
		if($stage=$row->find('[class=league] span', 0))
			echo " - ".$stage->plaintext;
		echo "<br>";
            continue;
	}
	if ($comp = $row->find('[class=league]', 0)) {
		/*if(allow($comp->plaintext))
		{*/
                echo $comp->plaintext . '<br>';
		/*	$allow=1;
		}
		else
			$allow=0;*/
            continue;
        }
        foreach ($row->find('td') as $col) {

            if ($col->find('a[class=scorelink]', 0)) {
                $url = $col->find('a[class=scorelink]', 0)->href;
            }
            if ($col->getAttribute('class') == "fd") {
                if (stristr($col->plaintext, ":")) {
                    /* $time=explode(":",$col->plaintext);
                      $hour=fmod(($time[0]+5),24);
                      $min=$time[1]+30;
                      if($min>=60)
                      {
                      $min=fmod($min,60);
                      $hour++;
                      }
                      if($hour>=24)
                      $hour-=24;
                      $scorerow=$scorerow."{$hour}:{$min} IST"; */
                    date_default_timezone_set("GMT");
                    $str = strtotime($col->plaintext);
                    date_default_timezone_set("Asia/Kolkata");
                    $time = date("h:i A", $str);
                    $scorerow = $scorerow . "[{$time}] ";
                } else {
                    if (!$homepage)
                        if (!stristr($col->plaintext, "FT") && $col->plaintext != " Postp. ")
                            $scorerow = $scorerow . "LIVE ";
                    $scorerow = $scorerow . $col->plaintext . " ";
                }
            }
            else
                $scorerow = $scorerow . $col->plaintext . " ";
        }
        if (strpos($url, "/soccer") == 0 && !strstr($url, "livescore") && !empty($url)) {
            echo "<a href='http://ec2-184-169-171-66.us-west-1.compute.amazonaws.com/txtweb/football-working.php?url={$url}&league={$league}'> $scorerow </a>";
            $url = "";
        }
        else
            echo $scorerow;
        echo "<br>";
        $scorerow = "";
        /* if ($score = $row->find('a[class=scorelink]', 0)) {
          $url = $score->href;

          }

          if (isset($url))
          if (strpos($url, "/soccer") == 0) {
          echo "<a href='http://ec2-184-169-171-66.us-west-1.compute.amazonaws.com/txtweb/football-working.php?url={$url}'> $row->plaintext </a>";
          ++$i;
          }
          else
          echo $row->plaintext;
          echo "<br>"; */
    }
    echo "<br>";
}
echo "-<br>";
/* $res = mysql_query("select * from football");
  while($row=mysql_fetch_array($res))
  {
  if($league!=$row['link'])
  echo "<a href='http://ec2-184-169-171-66.us-west-1.compute.amazonaws.com/txtweb/football.php?league={$row['link']}' class='txtweb-menu-for'> {$row['league']} </a><br>";

  } */
echo "send @football teamname for scores of that team<br>Eg: @football real<br>-<br>";
home($league);

?>