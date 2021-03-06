<?php
/**
 *   https://github.com/Bigjoos/
 *   Licence Info: GPL
 *   Copyright (C) 2010 U-232 v.3
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless, putyn.
 **/
//=== Anonymous function
function get_anonymous()
{
global $CURUSER;
return $CURUSER['anonymous_until'];
}
//== + Parked function
function get_parked()
{
global $CURUSER;
return $CURUSER['parked_until'];
}

function autoshout($msg) {
global $INSTALLER09;
require_once(INCL_DIR.'bbcode_functions.php');
sql_query('INSERT INTO shoutbox(userid,date,text,text_parsed)VALUES ('.$INSTALLER09['bot_id'].','.TIME_NOW.','.sqlesc($msg).','.sqlesc(format_comment($msg)).')');
}

function parked()
{
global $CURUSER;
if ($CURUSER["parked"] == "yes")
stderr("Error", "<b>Your account is currently parked.</b>");
}

//== Get rep by CF
function get_reputation($user, $mode = '', $rep_is_on = TRUE)
  {
      global $INSTALLER09, $CURUSER;
      $member_reputation = "";
      if( $rep_is_on )
      {
      include CACHE_DIR.'/rep_cache.php';
      // ok long winded file checking, but it's much better than file_exists
      if( ! isset( $reputations ) || ! is_array( $reputations ) || count( $reputations ) < 1)
      {
      return '<span title="Cache doesn\'t exist or zero length">Reputation: Offline</span>';
      }
      $user['g_rep_hide'] = isset( $user['g_rep_hide'] ) ? $user['g_rep_hide'] : 0;
      $user['username'] =  ($user['anonymous'] != 'yes') ? $user['username'] : 'Anonymous';
      // Hmmm...bit of jiggery-pokery here, couldn't think of a better way.
      $max_rep = max(array_keys($reputations));
      if($user['reputation'] >= $max_rep)
      {
      $user_reputation = $reputations[$max_rep];
      }
      else
      foreach($reputations as $y => $x)
      {
      if( $y > $user['reputation'] ) { $user_reputation = $old; break; }
      $old = $x;
      }
      //$rep_is_on = TRUE;
      //$CURUSER['g_rep_hide'] = FALSE;
      $rep_power = $user['reputation'];
      $posneg = '';
      if( $user['reputation'] == 0 )
      {
      $rep_img = 'balance';
      $rep_power = $user['reputation'] * -1;
      }
      elseif( $user['reputation'] < 0 )
      {     
      $rep_img = 'neg';
      $rep_img_2 = 'highneg';
      $rep_power = $user['reputation'] * -1;
      }
      else
      {
      $rep_img = 'pos';
      $rep_img_2 = 'highpos';
      }
      /**
      if( $rep_power > 500 )
      {
      // work out the bright green shiny bars, cos they cost 100 points, not the normal 100
      $rep_power = ( $rep_power - ($rep_power - 500) ) + ( ($rep_power - 500) / 2 );
      }
      **/
      // shiny, shiny, shiny boots...
      // ok, now we can work out the number of bars/pippy things  
      $pips = 12;
      switch ($mode)
      {
      case 'comments':
      $pips = 12;
      break;
      case 'torrents':
      $pips = 1003;
      break;
      case 'users':
      $pips = 970;
      break;
      case 'posts':
      $pips = 12;
      break;
      default:
      $pips = 12; // statusbar
      }
      $rep_bar = intval($rep_power / 100);
      if( $rep_bar > 10 )
      {
      $rep_bar = 10;
      }
      if( $user['g_rep_hide'] ) // can set this to a group option if required, via admin?
      {
      $posneg = 'off';
      $rep_level = 'rep_off';
      }
      else
      { // it ain't off then, so get on with it! I wanna see shiny stuff!!
      $rep_level = $user_reputation ? $user_reputation : 'rep_undefined';// just incase
      for( $i = 0; $i <= $rep_bar; $i++ )
      {
      if( $i >= 5 )
      {
      $posneg .= "<img src='{$INSTALLER09['pic_base_url']}rep/reputation_$rep_img_2.gif' border='0' alt=\"Reputation Power $rep_power\n{$user['username']} $rep_level\" title=\"Reputation Power $rep_power {$user['username']} $rep_level\" />";
      }
      else
      {
      $posneg .= "<img src='{$INSTALLER09['pic_base_url']}rep/reputation_$rep_img.gif' border='0' alt=\"Reputation Power $rep_power\n{$user['username']} $rep_level\" title=\"Reputation Power $rep_power {$user['username']} $rep_level\" />";
      }
      }
      }
      // now decide the locale
      if($mode != '')
      return "Rep: ".$posneg . "<br /><br /><a href='javascript:;' onclick=\"PopUp('{$INSTALLER09['baseurl']}/reputation.php?pid={$user['id']}&amp;locale=".$mode."','Reputation',400,241,1,1);\"><img src='{$INSTALLER09['pic_base_url']}forumicons/giverep.jpg' border='0' alt='Add reputation:: {$user['username']}' title='Add reputation:: {$user['username']}' /></a>";
      else
      return " ".$posneg;
      } // END IF ONLINE
      // default
      return '<span title="Set offline by admin setting">Rep System Offline</span>';
}
//== End

function get_ratio_color($ratio)
  {
    if ($ratio < 0.1) return "#ff0000";
    if ($ratio < 0.2) return "#ee0000";
    if ($ratio < 0.3) return "#dd0000";
    if ($ratio < 0.4) return "#cc0000";
    if ($ratio < 0.5) return "#bb0000";
    if ($ratio < 0.6) return "#aa0000";
    if ($ratio < 0.7) return "#990000";
    if ($ratio < 0.8) return "#880000";
    if ($ratio < 0.9) return "#770000";
    if ($ratio < 1) return "#660000";
    if (($ratio >= 1.0) && ($ratio < 2.0)) return "#006600";
    if (($ratio >= 2.0) && ($ratio < 3.0)) return "#007700";
    if (($ratio >= 3.0) && ($ratio < 4.0)) return "#008800";
    if (($ratio >= 4.0) && ($ratio < 5.0)) return "#009900";
    if (($ratio >= 5.0) && ($ratio < 6.0)) return "#00aa00";
    if (($ratio >= 6.0) && ($ratio < 7.0)) return "#00bb00";
    if (($ratio >= 7.0) && ($ratio < 8.0)) return "#00cc00";
    if (($ratio >= 8.0) && ($ratio < 9.0)) return "#00dd00";
    if (($ratio >= 9.0) && ($ratio < 10.0)) return "#00ee00";
    if ($ratio >= 10) return "#00ff00";
    return "#777777";
  }

  function get_slr_color($ratio)
  {
    if ($ratio < 0.025) return "#ff0000";
    if ($ratio < 0.05) return "#ee0000";
    if ($ratio < 0.075) return "#dd0000";
    if ($ratio < 0.1) return "#cc0000";
    if ($ratio < 0.125) return "#bb0000";
    if ($ratio < 0.15) return "#aa0000";
    if ($ratio < 0.175) return "#990000";
    if ($ratio < 0.2) return "#880000";
    if ($ratio < 0.225) return "#770000";
    if ($ratio < 0.25) return "#660000";
    if ($ratio < 0.275) return "#550000";
    if ($ratio < 0.3) return "#440000";
    if ($ratio < 0.325) return "#330000";
    if ($ratio < 0.35) return "#220000";
    if ($ratio < 0.375) return "#110000";
    if (($ratio >= 1.0) && ($ratio < 2.0)) return "#006600";
    if (($ratio >= 2.0) && ($ratio < 3.0)) return "#007700";
    if (($ratio >= 3.0) && ($ratio < 4.0)) return "#008800";
    if (($ratio >= 4.0) && ($ratio < 5.0)) return "#009900";
    if (($ratio >= 5.0) && ($ratio < 6.0)) return "#00aa00";
    if (($ratio >= 6.0) && ($ratio < 7.0)) return "#00bb00";
    if (($ratio >= 7.0) && ($ratio < 8.0)) return "#00cc00";
    if (($ratio >= 8.0) && ($ratio < 9.0)) return "#00dd00";
    if (($ratio >= 9.0) && ($ratio < 10.0)) return "#00ee00";
    if ($ratio >= 10) return "#00ff00";
    return "#777777";
  }
  
  function ratio_image_machine($ratio_to_check) {
  global $INSTALLER09;
switch ($ratio_to_check) {
case $ratio_to_check >= 5:
    return '<img src="'.$INSTALLER09['pic_base_url'].'smilies/yay.gif" alt="Yay" title="Yay" />';
    break;
case $ratio_to_check >= 4:
    return '<img src="'.$INSTALLER09['pic_base_url'].'smilies/pimp.gif" alt="Pimp" title="Pimp" />';
    break;
case $ratio_to_check >= 3:
    return '<img src="'.$INSTALLER09['pic_base_url'].'smilies/w00t.gif" alt="W00t" title="W00t" />';
    break;      
case $ratio_to_check >= 2:
    return '<img src="'.$INSTALLER09['pic_base_url'].'smilies/grin.gif" alt="Grin" title="Grin" />';
    break;    
case $ratio_to_check >= 1.5:
    return '<img src="'.$INSTALLER09['pic_base_url'].'smilies/evo.gif" alt="Evo" title="Evo" />';
    break;
case $ratio_to_check >= 1:
    return '<img src="'.$INSTALLER09['pic_base_url'].'smilies/smile1.gif" alt="Smile" title="Smile" />';
    break;
case $ratio_to_check >= 0.5:
    return '<img src="'.$INSTALLER09['pic_base_url'].'smilies/noexpression.gif" alt="Blank" title="Blank" />';
    break;
case $ratio_to_check >= 0.25:
   return '<img src="'.$INSTALLER09['pic_base_url'].'smilies/cry.gif" alt="Cry" title="Cry" />';
    break;
case $ratio_to_check < 0.25:
   return '<img src="'.$INSTALLER09['pic_base_url'].'smilies/shit.gif" alt="Shit" title="Shit" />';
    break;
}
}
   
/** class functions - pdq 2010 **/
/** START **/
   $class_names = array(
        UC_USER                 => 'User',
        UC_POWER_USER           => 'Power User',
        UC_VIP                  => 'VIP',
        UC_UPLOADER             => 'Uploader',
        UC_MODERATOR            => 'Moderator',
        UC_ADMINISTRATOR        => 'Administrator',
        UC_SYSOP                => 'SysOp');
        
   $class_colors = array(
        UC_USER                 => '8E35EF',
        UC_POWER_USER           => 'f9a200',
        UC_VIP                  => '009F00',
        UC_UPLOADER             => '0000FF',
        UC_MODERATOR            => 'FE2E2E',
        UC_ADMINISTRATOR        => 'B000B0',
        UC_SYSOP                => '4080B0');

   $class_images = array(
        UC_USER                 => $INSTALLER09['pic_base_url'].'class/user.gif',
        UC_POWER_USER           => $INSTALLER09['pic_base_url'].'class/power.gif',
        UC_VIP                  => $INSTALLER09['pic_base_url'].'class/vip.gif',
        UC_UPLOADER             => $INSTALLER09['pic_base_url'].'class/uploader.gif',
        UC_MODERATOR            => $INSTALLER09['pic_base_url'].'class/moderator.gif',
        UC_ADMINISTRATOR        => $INSTALLER09['pic_base_url'].'class/administrator.gif',
        UC_SYSOP                => $INSTALLER09['pic_base_url'].'class/sysop.gif');
        
   function get_user_class_name($class) {
        global $class_names;
        $class = (int)$class;
        if (!valid_class($class))
            return '';
        if (isset($class_names[$class]))
            return $class_names[$class];
        else
            return '';
    }
    
    function get_user_class_color($class) {
        global $class_colors;
        $class = (int)$class;
        if (!valid_class($class))
            return '';
        if (isset($class_colors[$class]))
            return $class_colors[$class];
        else
            return '';
    }
    
    function get_user_class_image($class) {
        global $class_images;
        $class = (int)$class;
        if (!valid_class($class))
            return '';
        if (isset($class_images[$class]))
            return $class_images[$class];
        else
            return '';
    }
    
    function valid_class($class) {
        $class = (int)$class;
        return (bool)($class >= UC_MIN && $class <= UC_MAX);
    }

    function min_class($min = UC_MIN, $max = UC_MAX) {
        global $CURUSER;
        $minclass = (int)$min;
        $maxclass = (int)$max;
        if (!isset($CURUSER))
            return false;
        if (!valid_class($minclass) || !valid_class($maxclass))
            return false;
        if ($maxclass < $minclass)
            return false;
        return (bool)($CURUSER['class'] >= $minclass && $CURUSER['class'] <= $maxclass);
    }
       
function format_username($user, $icons = true) {
        global $INSTALLER09;
        $user['id'] = (int)$user['id'];
        $user['class'] = (int)$user['class'];
        if ($user['id'] == 0)
            return 'System';
        elseif ($user['username'] == '')
        return 'unknown['.$user['id'].']';
        $username = '<span style="color:#'.get_user_class_color($user['class']).';"><b>'.$user['username'].'</b></span>';
        $str = '<span style="white-space: nowrap;"><a class="user_'.$user['id'].'" href="'.$INSTALLER09['baseurl'].'/userdetails.php?id='.$user['id'].'" target="_blank">'.$username.'</a>';
        if ($icons != false) {
            $str .= ($user['donor'] == 'yes' ? '<img src="'.$INSTALLER09['pic_base_url'].'star.png" alt="Donor" title="Donor" />' : '');
            $str .= ($user['warned'] >= 1 ? '<img src="'.$INSTALLER09['pic_base_url'].'alertred.png" alt="Warned" title="Warned" />' : '');
            $str .= ($user['leechwarn'] >= 1 ? '<img src="'.$INSTALLER09['pic_base_url'].'alertblue.png" alt="Leech Warned" title="Leech Warned" />' : '');
            $str .= ($user['enabled'] != 'yes' ? '<img src="'.$INSTALLER09['pic_base_url'].'disabled.gif" alt="Disabled" title="Disabled" />' : '');
            $str .= ($user['chatpost'] == 0 ?  '<img src="'.$INSTALLER09['pic_base_url'].'warned.png" alt="No Chat" title="Shout disabled" />'  : '');
            $str .= ($user['pirate'] != 0 ? '<img src="'.$INSTALLER09['pic_base_url'].'pirate.png" alt="Pirate" title="Pirate" />' : '');
            $str .= ($user['king'] != 0 ? '<img src="'.$INSTALLER09['pic_base_url'].'king.png" alt="King" title="King" />' : '');
        }
        $str .= "</span>\n";
        return $str;
}

function is_valid_id($id)
{
  return is_numeric($id) && ($id > 0) && (floor($id) == $id);
}

function member_ratio($up, $down) {
    switch(true) {
        case ($down > 0 && $up > 0): 
        $ratio = '<span style="color:'.get_ratio_color($up/$down).';">'.number_format($up/$down, 3).'</span>';
        break;
        case ($down > 0 && $up == 0): 
        $ratio = '<span style="color:'.get_ratio_color(1/$down).';">'.number_format(1/$down, 3).'</span>';
        break;
        case ($down == 0 && $up > 0): 
        $ratio=  '<span style="color: '.get_ratio_color($up/1).';">inf</span>';
        break;
       default:
       $ratio = '---';
   }
return $ratio;
}

//=== get smilie based on ratio
function get_user_ratio_image($ratio)
{
global $INSTALLER09;
switch ($ratio)
{
case ($ratio == 0): return;
break;
case ($ratio < 0.6): return ' <img src="'.$INSTALLER09['pic_base_url'].'smilies/shit.gif" alt=" Bad ratio :("  title=" Bad ratio :("/>';
break;
case ($ratio <= 0.7): return ' <img src="'.$INSTALLER09['pic_base_url'].'smilies/weep.gif" alt=" Could be better"  title=" Could be better" />';
break;
case ($ratio <= 0.8): return ' <img src="'.$INSTALLER09['pic_base_url'].'smilies/cry.gif" alt=" Getting there!" title=" Getting there!" />';
break;
case ($ratio <= 1.5): return ' <img src="'.$INSTALLER09['pic_base_url'].'smilies/smile1.gif" alt=" Good Ratio :)" title=" Good Ratio :)" />';
break;
case ($ratio <= 2.0): return ' <img src="'.$INSTALLER09['pic_base_url'].'smilies/grin.gif" alt=" Great Ratio :)" title=" Great Ratio :)" />';
break;
case ($ratio <= 3.0): return ' <img src="'.$INSTALLER09['pic_base_url'].'smilies/w00t.gif" alt=" Wow! :D" title=" Wow! :D" />';
break;
case ($ratio <= 4.0): return ' <img src="'.$INSTALLER09['pic_base_url'].'smilies/pimp.gif" alt=" Fa-boo Ratio!" title=" Fa-boo Ratio!" />';
break;
case ($ratio > 4.0): return ' <img src="'.$INSTALLER09['pic_base_url'].'smilies/yahoo.gif" alt=" Great ratio :-D" title=" Great ratio :-D" />';
break;
  }
  return '';
}

 //=== avatar stuff... hell it's called all over the place :-o
 function avatar_stuff($avatar, $width = 80) {
  global $CURUSER, $INSTALLER09;
  $avatar_show = ($CURUSER['avatars'] == 'no' ? '' : (!$avatar['avatar'] ? '<img style="max-width:'.$width.'px;" src="'.$INSTALLER09['pic_base_url'].'default_avatar.gif" alt="avatar" />' : 
  (($avatar['offensive_avatar'] === 'yes' && $CURUSER['view_offensive_avatar'] === 'no') ? 
  '<img style="max-width:'.$width.'px;" src="'.$INSTALLER09['pic_base_url'].'fuzzybunny.gif" alt="avatar" />' : '<img style="max-width:'.$width.'px;" src="'.htmlspecialchars($avatar['avatar']).'" alt="avatar" />')));   
  return $avatar_show;
}

  //=== added a function to get all user info and print them up with link to userdetails page, class color, user icons... pdq's idea \o/
  function print_user_stuff($arr)
  {
  global $CURUSER, $INSTALLER09;
  return '<span style="white-space:nowrap;"><a href="userdetails.php?id='.$arr['id'].'" title="'. get_user_class_name($arr['class']).'">
  <span style="font-weight: bold;"></span></a>'.format_username($arr).'</span> '; 
  }

//made by putyn@tbdev
function blacklist($fo) {
	global $INSTALLER09;
	$blacklist = file_exists($INSTALLER09['nameblacklist']) && is_array(unserialize(file_get_contents($INSTALLER09['nameblacklist']))) ? unserialize(file_get_contents($INSTALLER09['nameblacklist'])) : array();
	if(isset($blacklist[$fo]) && $blacklist[$fo] == 1)
		return false;
	
	return true;
}

function get_server_load($windows = 0) {
if(class_exists("COM")) {
$wmi = new COM("WinMgmts:\\\\.");
$cpus = $wmi->InstancesOf("Win32_Processor"); 
$i = 1;
// Use the while loop on PHP 4 and foreach on PHP 5
//while ($cpu = $cpus->Next()) {
foreach($cpus as $cpu) {
$cpu_stats=0;
$cpu_stats += $cpu->LoadPercentage;
$i++;
}
return round($cpu_stats/2); // remove /2 for single processor systems
}
}
/** end functions **/
?>
