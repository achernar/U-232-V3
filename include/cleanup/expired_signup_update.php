<?php
/**
 *   https://github.com/Bigjoos/
 *   Licence Info: GPL
 *   Copyright (C) 2010 U-232 v.3
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless, putyn.
 **/

function docleanup( $data ) {
    global $INSTALLER09, $queries, $mc1;
    set_time_limit(0);
    ignore_user_abort(1);
   
   $deadtime = TIME_NOW - $INSTALLER09['signup_timeout'];
	sql_query("DELETE FROM users WHERE status = 'pending' AND added < $deadtime AND last_login < $deadtime AND last_access < $deadtime");

   write_log("Expired Signup clean-------------------- Expired Signup cleanup Complete using $queries queries --------------------");

   if( false !== mysqli_affected_rows($GLOBALS["___mysqli_ston"]) )
   {
   $data['clean_desc'] = mysqli_affected_rows($GLOBALS["___mysqli_ston"]) . " items deleted/updated";
   }

   
   if( $data['clean_log'] )
   {
   cleanup_log( $data );
   }
        
   }  
  
function cleanup_log( $data )
{
  $text = sqlesc($data['clean_title']);
  $added = TIME_NOW;
  $ip = sqlesc($_SERVER['REMOTE_ADDR']);
  $desc = sqlesc($data['clean_desc']);
  sql_query( "INSERT INTO cleanup_log (clog_event, clog_time, clog_ip, clog_desc) VALUES ($text, $added, $ip, {$desc})" ) or sqlerr(__FILE__, __LINE__);
}
?>
