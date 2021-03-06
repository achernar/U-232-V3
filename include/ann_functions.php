<?php
/**
 *   https://github.com/Bigjoos/
 *   Licence Info: GPL
 *   Copyright (C) 2010 U-232 V3
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
//== Announce mysql error
function ann_sqlerr($file = '', $line = '') {
    global $INSTALLER09, $CURUSER;
		$error    = ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false));
		$error_no = ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false));
     if ( $INSTALLER09['ann_sql_error_log'] AND ANN_SQL_DEBUG == 1 )
		 {
			$_ann_sql_err  = "\n===================================================";
			$_ann_sql_err .= "\n Date: ". date( 'r' );
			$_ann_sql_err .= "\n Error Number: " . $error_no;
			$_ann_sql_err .= "\n Error: " . $error;
			$_ann_sql_err .= "\n IP Address: " . $_SERVER['REMOTE_ADDR'];
			$_ann_sql_err .= "\n in file ".$file." on line ".$line;
			$_ann_sql_err .= "\n URL:".$_SERVER['REQUEST_URI'];
			if ( $FH = @fopen( $INSTALLER09['ann_sql_error_log'], 'a' ) )
			{
				@fwrite( $FH, $_ann_sql_err );
				@fclose( $FH );
			}
		}
   }

  //== Crazyhour by pdq
  function crazyhour_announce() {
   global $mc1, $INSTALLER09;
   $crazy_hour = (TIME_NOW + 3600);
   $cz['crazyhour'] = $mc1->get_value('crazyhour');
   if ($cz['crazyhour'] === false) {
      $cz['sql'] = mysqli_query($GLOBALS["___mysqli_ston"], 'SELECT var, amount FROM freeleech WHERE type = "crazyhour"') or ann_sqlerr(__FILE__, __LINE__);
      $cz['crazyhour'] = array();   

      if (mysqli_num_rows($cz['sql']) !== 0)
         $cz['crazyhour'] = mysqli_fetch_assoc($cz['sql']);
      else {
         $cz['crazyhour']['var'] = mt_rand(TIME_NOW, (TIME_NOW + 86400));
         $cz['crazyhour']['amount'] = 0;
         mysqli_query($GLOBALS["___mysqli_ston"], 'UPDATE LOW_PRIORITY freeleech SET var = '.$cz['crazyhour']['var'].', amount = '.$cz['crazyhour']['amount'].' 
         WHERE type = "crazyhour"') or ann_sqlerr(__FILE__, __LINE__);
      }
      $mc1->cache_value('crazyhour', $cz['crazyhour'], 0);            
   }

   if ($cz['crazyhour']['var'] < TIME_NOW) { // if crazyhour over
      $cz_lock       = $mc1->add_value('crazyhour_lock', 1, 10);
      if ($cz_lock !== false) {
         $cz['crazyhour_new']       = mktime(23, 59, 59, date('m'), date('d'), date('y'));
         $cz['crazyhour']['var']    = mt_rand($cz['crazyhour_new'], ($cz['crazyhour_new'] + 86400));
         $cz['crazyhour']['amount'] = 0;
         $cz['remaining']           = ($cz['crazyhour']['var'] - TIME_NOW);

         mysqli_query($GLOBALS["___mysqli_ston"], 'UPDATE LOW_PRIORITY freeleech SET var = '.$cz['crazyhour']['var'].', amount = '.$cz['crazyhour']['amount'].' '.
                     'WHERE type = "crazyhour"') or ann_sqlerr(__FILE__, __LINE__);
         $mc1->cache_value('crazyhour', $cz['crazyhour'], 0);
         // log, shoutbot
         $text        = 'Next [color=orange][b]Crazyhour_A[/b][/color] is at '.date('F j, g:i a', $cz['crazyhour']['var']);
         $text_parsed = 'Next <span style="font-weight:bold;color:orange;">Crazyhour_A</span> is at '.date('F j, g:i a', $cz['crazyhour']['var']);

         mysqli_query($GLOBALS["___mysqli_ston"], 'INSERT LOW_PRIORITY INTO sitelog (added, txt) '.
                     'VALUES('.TIME_NOW.', '.ann_sqlesc($text_parsed).')') or ann_sqlerr(__FILE__, __LINE__);

         mysqli_query($GLOBALS["___mysqli_ston"], 'INSERT LOW_PRIORITY INTO shoutbox (userid, date, text, text_parsed) '.
                     'VALUES (2, '.TIME_NOW.', '.ann_sqlesc($text).', '.ann_sqlesc($text_parsed).')') or ann_sqlerr(__FILE__, __LINE__);
         $mc1->delete_value('shoutbox_');
      }
      return false;
   }
   elseif (($cz['crazyhour']['var'] < $crazy_hour) && ($cz['crazyhour']['var'] >= TIME_NOW)) { // if crazyhour
      if ($cz['crazyhour']['amount'] !== 1) {
         $cz['crazyhour']['amount'] = 1;     
         $cz_lock = $mc1->add_value('crazyhour_lock', 1, 10);
         if ($cz_lock !== false) {
            mysqli_query($GLOBALS["___mysqli_ston"], 'UPDATE LOW_PRIORITY freeleech SET amount = '.$cz['crazyhour']['amount'].' 
            WHERE type = "crazyhour"') or ann_sqlerr(__FILE__, __LINE__);
            $mc1->cache_value('crazyhour', $cz['crazyhour'], 0);
            // log, shoutbot
            $text        = 'w00t! It\'s [color=orange][b]Crazyhour_A[/b][/color] :w00t:';
            $text_parsed = 'w00t! It\'s <span style="font-weight:bold;color:orange;">Crazyhour_A</span> <img src="pic/smilies/w00t.gif" alt=":w00t:" />';
            mysqli_query($GLOBALS["___mysqli_ston"], 'INSERT LOW_PRIORITY INTO sitelog (added, txt) 
            VALUES('.TIME_NOW.', '.ann_sqlesc($text_parsed).')') or ann_sqlerr(__FILE__, __LINE__);

            mysqli_query($GLOBALS["___mysqli_ston"], 'INSERT LOW_PRIORITY INTO shoutbox (userid, date, text, text_parsed) '.
                        'VALUES (2, '.TIME_NOW.', '.ann_sqlesc($text).', '.ann_sqlesc($text_parsed).')') or ann_sqlerr(__FILE__, __LINE__);
            $mc1->delete_value('shoutbox_');
         }
      }
      return true;
   }
   else
      return false;
}

// get torrentfromhash by pdq
function get_torrent_from_hash($info_hash) {
   global $mc1,$INSTALLER09;
   $key = 'torrent::hash:::'.md5($info_hash);
   $ttll = 21600; // 21600;
   $torrent = $mc1->get_value($key);
   if ($torrent === false) {
      $res = mysqli_query($GLOBALS["___mysqli_ston"], 'SELECT id, category, banned, free, vip, seeders, leechers, times_completed, seeders + leechers AS numpeers, added AS ts, visible FROM torrents WHERE info_hash = '.ann_sqlesc($info_hash)) or ann_sqlerr(__FILE__, __LINE__);
      if (mysqli_num_rows($res)) {
         $torrent = mysqli_fetch_assoc($res);
         $torrent['id']       = (int)$torrent['id'];
         $torrent['free'] = (int)$torrent['free'];
         $torrent['category'] = (int)$torrent['category'];
         $torrent['numpeers'] = (int)$torrent['numpeers'];
         $mc1->cache_value($key, $torrent, $ttll);

         $torrent['seeders']         = (int)$torrent['seeders'];
         $torrent['leechers']         = (int)$torrent['leechers'];
         $torrent['times_completed'] = (int)$torrent['times_completed'];
         $torrent['ts']             = (int)$torrent['ts'];
         $seed_key  = 'torrents::seeds:::'.$torrent['id']; 
         $leech_key = 'torrents::leechs:::'.$torrent['id'];
         $comp_key  = 'torrents::comps:::'.$torrent['id'];
         $mc1->add_value($seed_key, $torrent['seeders'], $ttll);
         $mc1->add_value($leech_key, $torrent['leechers'], $ttll);
         $mc1->add_value($comp_key, $torrent['times_completed'], $ttll);
      }
      else {
         $mc1->cache_value($key, 0, 86400);
         return false;
      }
   }
   elseif (!$torrent)
      return false;
   else {
      $seed_key  = 'torrents::seeds:::'.$torrent['id']; 
      $leech_key = 'torrents::leechs:::'.$torrent['id']; 
      $comp_key  = 'torrents::comps:::'.$torrent['id'];
      $torrent['seeders']         = $mc1->get_value($seed_key);
      $torrent['leechers']        = $mc1->get_value($leech_key);
      $torrent['times_completed'] = $mc1->get_value($comp_key);
      if ($torrent['seeders'] === false || $torrent['leechers'] === false || $torrent['times_completed'] === false) {
         $res = mysqli_query($GLOBALS["___mysqli_ston"], 'SELECT seeders, leechers, times_completed FROM torrents WHERE id = '.$torrent['id']) or ann_sqlerr(__FILE__, __LINE__);
         if (mysqli_num_rows($res)) {
            $torrentq = mysqli_fetch_assoc($res);
            $torrent['seeders']         = (int)$torrentq['seeders'];
            $torrent['leechers']        = (int)$torrentq['leechers'];
            $torrent['times_completed'] = (int)$torrentq['times_completed'];
            $mc1->add_value($seed_key, $torrent['seeders'], $ttll);
            $mc1->add_value($leech_key, $torrent['leechers'], $ttll);
            $mc1->add_value($comp_key, $torrent['times_completed'], $ttll);
         }
         else {
            $mc1->delete_value($key);
            return false;
         }
      }
   }
   return $torrent;
}

// adjusttorrentpeers by pdq
function adjust_torrent_peers($id, $seeds = 0, $leechers = 0, $completed = 0) {
   global $mc1;
   if (!is_int($id) || $id < 1)
      return false;

   if (!$seeds && !$leechers && !$completed)
      return false;

   $adjust    = 0;
   $seed_key  = 'torrents::seeds:::'.$id; 
   $leech_key = 'torrents::leechs:::'.$id; 
   $comp_key  = 'torrents::comps:::'.$id;

   if ($seeds > 0)
      $adjust += (bool) $mc1->increment($seed_key, $seeds);
   elseif ($seeds < 0)
      $adjust += (bool) $mc1->decrement($seed_key, -$seeds);

   if ($leechers > 0)
      $adjust += (bool) $mc1->increment($leech_key, $leechers);
   elseif ($leechers < 0)
      $adjust += (bool) $mc1->decrement($leech_key, -$leechers);

   if ($completed > 0)
      $adjust += (bool) $mc1->increment($comp_key, $completed);

   return (bool)$adjust;
}

// freeslots by pdq
function get_slots($torrentid, $userid) {
    global $mc1;
    $ttl_slot = 86400;
    $torrent['freeslot'] = $torrent['doubleslot'] = 0;
    $slot = $mc1->get_value('fllslot_'.$userid);
    if ($slot === false) {
       $res_slots = mysqli_query($GLOBALS["___mysqli_ston"], 'SELECT * FROM freeslots WHERE userid = '.$userid) or ann_sqlerr(__FILE__, __LINE__);
        $slot = array();
         if (mysqli_num_rows($res_slots)) {
              while ($rowslot = mysqli_fetch_assoc($res_slots))
              $slot[] = $rowslot;
        }
       $mc1->add_value('fllslot_'.$userid, $slot, $ttl_slot);
   }
   
   if (!empty($slot))
       foreach ($slot as $sl) {
           if ($sl['torrentid'] == $torrentid && $sl['free'] == 'yes')
               $torrent['freeslot'] = 1;

           if ($sl['torrentid'] == $torrentid && $sl['doubleup'] == 'yes')
               $torrent['doubleslot'] = 1;
       }

	return $torrent;
}

//=== detect abnormal uploads
function auto_enter_abnormal_upload($userid, $rate, $upthis, $diff, $torrentid, $client, $realip, $last_up)
{
mysqli_query($GLOBALS["___mysqli_ston"], 'INSERT LOW_PRIORITY INTO cheaters (added, userid, client, rate, beforeup, upthis, timediff, userip, torrentid) VALUES('.ann_sqlesc(TIME_NOW).', '.ann_sqlesc($userid).', '.ann_sqlesc($client).', '.ann_sqlesc($rate).', '.ann_sqlesc($last_up).', '.ann_sqlesc($upthis).', '.ann_sqlesc($diff).', '.ann_sqlesc($realip).', '.ann_sqlesc($torrentid).')') or ann_sqlerr(__FILE__, __LINE__);
}

function err($msg)
{
	benc_resp(array('failure reason' => array('type' => 'string', 'value' => $msg)));
	
	exit();
}

function benc_resp($d)
{
	benc_resp_raw(benc(array('type' => 'dictionary', 'value' => $d)));
}

function gzip() {
	if (@extension_loaded('zlib') && @ini_get('zlib.output_compression') != '1' && @ini_get('output_handler') != 'ob_gzhandler') {
		@ob_start('ob_gzhandler');
	}
}

function benc_resp_raw($x) {
	header("Content-Type: text/plain");
	header("Pragma: no-cache");
	echo($x);
}

function benc($obj) {
	if (!is_array($obj) || !isset($obj["type"]) || !isset($obj["value"]))
		return;
	$c = $obj["value"];
	switch ($obj["type"]) {
		case "string":
			return benc_str($c);
		case "integer":
			return benc_int($c);
		case "list":
			return benc_list($c);
		case "dictionary":
			return benc_dict($c);
		default:
			return;
	}
}

function benc_str($s) {
	return strlen($s) . ":$s";
}

function benc_int($i) {
	return "i" . $i . "e";
}

function benc_list($a) {
	$s = "l";
	foreach ($a as $e) {
		$s .= benc($e);
	}
	$s .= "e";
	return $s;
}

function benc_dict($d) {
	$s = "d";
	$keys = array_keys($d);
	sort($keys);
	foreach ($keys as $k) {
		$v = $d[$k];
		$s .= benc_str($k);
		$s .= benc($v);
	}
	$s .= "e";
	return $s;
}

function hash_where($name, $hash) {
    $shhash = preg_replace('/ *$/s', "", $hash);
    return "($name = " . ann_sqlesc($hash) . " OR $name = " . ann_sqlesc($shhash) . ")";
}

function portblacklisted($port)
{
    //=== new portblacklisted ....... ==> direct connect 411 ot 413,  bittorrent 6881 to 6889, kazaa 1214, gnutella 6346 to 6347, emule 4662, winmx 6699, IRC bot based trojans 65535
    $portblacklisted = array(411, 412, 413, 6881 ,6882, 6883, 6884, 6885, 6886, 6887, 6889, 1214, 6346, 6347, 4662, 6699, 65535);
        if (in_array($port, $portblacklisted)) return true;

    return false;
}

function ann_sqlesc($x) {
    return "'".((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $x) : ((trigger_error("Error.", E_USER_ERROR)) ? "" : ""))."'";
}
?>
