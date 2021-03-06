<?php
/**
 *   https://github.com/Bigjoos/
 *   Licence Info: GPL
 *   Copyright (C) 2010 U-232 v.3
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless, putyn.
 **/
 
function linkcolor($num) {
    if (!$num)
    return "red";
    return "green";
}

function readMore($text, $char, $link)
{
    return (strlen($text) > $char ? substr(htmlspecialchars($text), 0, $char-1) . "...<br /><a href='$link'>Read more...</a>": htmlspecialchars($text));
}


function torrenttable($res, $variant = "index") {
    global $INSTALLER09, $CURUSER, $lang, $free, $mc1;
    require_once(INCL_DIR.'bbcode_functions.php');
    $htmlout = $prevdate = $free_slot = $free_color = $slots_check = $double_slot = $private = $newgenre =  $oldlink = $char = $description = $type = $sort = $row = '';
    $count_get = 0;
    /** ALL FREE/DOUBLE **/
    foreach($free as $fl) {
    switch ($fl['modifier']) {
    case 1:
    $free_display = '[Free]';
    break;
    case 2:
   $free_display = '[Double]';
    break;
    case 3:
    $free_display = '[Free and Double]';
    break;
}

$slot = make_freeslots($CURUSER['id'], 'fllslot_');
$book = make_bookmarks($CURUSER['id'], 'bookmm_');
$all_free_tag = ($fl['modifier'] != 0 && ($fl['expires'] > TIME_NOW || $fl['expires'] == 1) ? ' <a class="info" href="#">
            <b>'.$free_display.'</b> 
            <span>'. ($fl['expires'] != 1 ? '
            Expires: '.get_date($fl['expires'], 'DATE').'<br />
            ('.mkprettytime($fl['expires'] - TIME_NOW).' to go)</span></a><br />' : 'Unlimited</span></a><br />') : '');
}

 $oldlink = array();
    foreach($_GET as $key=>$var) {
  if(in_array($key, array('sort','type')))
    continue;
  if(is_array($var)) {
    foreach($var as $s_var)
      $oldlink[] = sprintf('%s=%s',$key.'%5B%5D',$s_var);
  } else
    $oldlink[] = sprintf('%s=%s',$key,$var);
}
 
if ($oldlink > 0) 
    $oldlink = join('&amp;',$oldlink).'&amp;';

    $links = array('link1','link2','link3','link4','link5','link6','link7','link8','link9');
    $i =1;
    foreach($links as $link) {
    if(isset($_GET['sort']) && $_GET['sort'] == $i)
	  $$link = (isset($_GET['type']) && $_GET['type'] == 'desc') ? 'asc' : 'desc';
    else
	  $$link = 'desc';
    $i++;
    }
  
   $htmlout .= "<!--<div class='global_icon'><img src='images/global.design/torrents.png' alt='' title='Categorys' class='global_image' width='25'/></div>
    <div class='global_head'>Torrents</div><br />
    <div class='global_text'><br />-->
   <table border='1' cellspacing='0' cellpadding='5'>
   <tr>
   <td class='colhead' align='center'>{$lang["torrenttable_type"]}</td>
   <td class='colhead' align='left'><a href='{$_SERVER["PHP_SELF"]}?{$oldlink}sort=1&amp;type={$link1}'>{$lang["torrenttable_name"]}</a></td>
   <!--<td class='colhead' align='left'>{$lang["torrenttable_subtitles"]}</td>-->
   <td class='colhead' align='left'><img src='{$INSTALLER09['pic_base_url']}zip.gif' border='0' alt='Download' title='Download' /></td>";
   
   $htmlout.= ($variant == 'index' ? "<td class='colhead' align='center'><a href='{$INSTALLER09['baseurl']}/bookmarks.php'><img src='{$INSTALLER09['pic_base_url']}bookmarks.png'  border='0' alt='Bookmark' title='Go To My Bookmarks' /></a></td>" : '');

   if ($variant == "mytorrents")
   {
   $htmlout .= "<td class='colhead' align='center'>{$lang["torrenttable_edit"]}</td>\n";
   $htmlout .= "<td class='colhead' align='center'>{$lang["torrenttable_visible"]}</td>\n";
   }
 
   $htmlout .= "<td class='colhead' align='right'><a href='{$_SERVER["PHP_SELF"]}?{$oldlink}sort=2&amp;type={$link2}'>{$lang["torrenttable_files"]}</a></td>
   <td class='colhead' align='right'><a href='{$_SERVER["PHP_SELF"]}?{$oldlink}sort=3&amp;type={$link3}'>{$lang["torrenttable_comments"]}</a></td>
   <td class='colhead' align='center'><a href='{$_SERVER["PHP_SELF"]}?{$oldlink}sort=4&amp;type={$link4}'>{$lang["torrenttable_added"]}</a></td>
   <td class='colhead' align='center'><a href='{$_SERVER["PHP_SELF"]}?{$oldlink}sort=5&amp;type={$link5}'>{$lang["torrenttable_size"]}</a></td>
   <!--<td class='colhead' align='center'>{$lang["torrenttable_progress"]}</td>-->
   <td class='colhead' align='center'><a href='{$_SERVER["PHP_SELF"]}?{$oldlink}sort=6&amp;type={$link6}'>{$lang["torrenttable_snatched"]}</a></td>
   <td class='colhead' align='right'><a href='{$_SERVER["PHP_SELF"]}?{$oldlink}sort=7&amp;type={$link7}'>{$lang["torrenttable_seeders"]}</a></td>
   <td class='colhead' align='right'><a href='{$_SERVER["PHP_SELF"]}?{$oldlink}sort=8&amp;type={$link8}'>{$lang["torrenttable_leechers"]}</a></td>";


   if ($variant == 'index')
   $htmlout .= "<td class='colhead' align='center'><a href='{$_SERVER["PHP_SELF"]}?{$oldlink}sort=9&amp;type={$link9}'>{$lang["torrenttable_uppedby"]}</a></td>\n";
   $htmlout .= "</tr>\n";

    $categories = genrelist();
    foreach($categories as $key => $value)
    $change[$value['id']] = array('id' => $value['id'], 'name'  => $value['name'], 'image' => $value['image']);
    while ($row = mysqli_fetch_assoc($res)) 
    {
       $row['cat_name'] = htmlspecialchars($change[$row['category']]['name']);
       $row['cat_pic'] = htmlspecialchars($change[$row['category']]['image']);
       /** Freeslot/doubleslot in Use **/
       $id = $row["id"]; 
       foreach ($slot as $sl) 
       $slots_check = ($sl['torrentid'] == $id && $sl['free'] =='yes' OR $sl['doubleup'] == 'yes'); 
       if ($row["sticky"] == "yes")
       $htmlout .= "<tr class='highlight'>\n";
       else 
       $htmlout .= '<tr class="'.(($free_color && $all_free_tag != '') || ($row['free'] != 0) || $slots_check ? 'freeleech_color' : 'browse_color').'">';  
       $htmlout .= "<td align='center' style='padding: 0px'>";
       if (isset($row["cat_name"])) 
       {
       $htmlout .= "<a href='browse.php?cat={$row['category']}'>";
       if (isset($row["cat_pic"]) && $row["cat_pic"] != "")
       $htmlout .= "<img border='0' src='{$INSTALLER09['pic_base_url']}caticons/{$CURUSER['categorie_icon']}/{$row['cat_pic']}' alt='{$row['cat_name']}' />";
       else
       {
       $htmlout .= $row["cat_name"];
       }
       $htmlout .= "</a>";
       }
       else
       {
       $htmlout .= "-";
       }
       $htmlout .= "</td>\n";
       $dispname = htmlspecialchars($row["name"]);
       $smalldescr = (!empty($row['description']) ? "<i>[" . htmlspecialchars($row['description']) . "]</i>" : "" );
       $checked = ((!empty($row['checked_by']) && $CURUSER['class'] >= UC_USER) ? "&nbsp;<img src='{$INSTALLER09['pic_base_url']}mod.gif' width='15' border='0' alt='Checked - by ".htmlspecialchars($row['checked_by'])."' title='Checked - by ".htmlspecialchars($row['checked_by'])."' />" : "");
       $poster = empty($row["poster"]) ? "<img src=\'{$INSTALLER09['pic_base_url']}noposter.png\' width=\'150\' height=\'220\' border=\'0\' alt=\'Poster\' title=\'poster\' />" : "<img src=\'".htmlspecialchars($row['poster'])."\' width=\'150\' height=\'220\' border=\'0\' alt=\'Poster\' title=\'poster\' />";
       $rating = empty($row["rating"]) ? "No votes yet":"".ratingpic((int)$row["rating"]).""; 
       //$pre = (!empty($row["pretime"]) ? "&nbsp;Uploaded: ".get_pretime($row["pretime"])." after pre." : "No pretime was found" );
       if ($row["descr"])
       $descr = str_replace("\"", "&quot;", readMore($row["descr"], 350, "details.php?id=".(int)$row["id"]."&amp;hit=1"));
       $htmlout .= "<td align='left'><a href='details.php?";
       if ($variant == "mytorrents")
       $htmlout .= "returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;";
       $htmlout .= "id=$id";
       if ($variant == "index")
       $htmlout .= "&amp;hit=1";
	   
	   if(!empty($row['newgenre'])) {
      $newgenre = array();
			$row['newgenre'] = explode(',',$row['newgenre']);
			foreach($row['newgenre'] as $foo)
				$newgenre[] = '<a href="browse.php?search='.trim(strtolower($foo)).'&amp;searchin=genre">'.$foo.'</a>';
			$newgenre = '<i>'.join(', ',$newgenre).'</i>';
		}	
       $sticky = ($row['sticky']=="yes" ? "<img src='{$INSTALLER09['pic_base_url']}sticky.gif' style='border:none' alt='Sticky' title='Sticky !' />" : "");
       $nuked = ($row["nuked"] == "yes" ? "<img src='{$INSTALLER09['pic_base_url']}nuked.gif' style='border:none' alt='Nuked'  align='right' title='Reason :".htmlspecialchars($row["nukereason"])."' />" : "");
       $release_group = ($row['release_group']=="scene" ? "&nbsp;<img src='{$INSTALLER09['pic_base_url']}scene.gif' title='Scene' alt='Scene' style='border:none' />" : ($row['release_group']=="p2p" ? "&nbsp;<img src='{$INSTALLER09['pic_base_url']}p2p.gif' title='P2P' alt='P2P' />" : "")); 
       $viponly = ($row["vip"]== "1" ? "<img src='{$INSTALLER09['pic_base_url']}star.png' border='0' alt='Vip Torrent' title='Vip Torrent' />" : "");
       $bump = ($row['bump'] == "yes" ? "<img src='{$INSTALLER09['pic_base_url']}up.gif' width='12px' alt='Re-Animated torrent' title='This torrent was ReAnimated!' />" : "");
       /** FREE Torrent **/
       $free_tag = ($row['free'] != 0 ? ' <a class="info" href="#"><b>[FREE]</b> <span>'. ($row['free'] > 1 ? 'Expires: '.get_date($row['free'], 'DATE').'<br />('.mkprettytime($row['free'] - TIME_NOW).' to go)<br />' : 'Unlimited<br />').'</span></a>' : $all_free_tag);
       if (!empty($slot))
                foreach ($slot as $sl) {
                    if ($sl['torrentid'] == $id && $sl['free'] == 'yes')
                        $free_slot = 1;
                    if ($sl['torrentid'] == $id && $sl['doubleup'] == 'yes')
                        $double_slot = 1;
                    if ($free_slot && $double_slot)
                        break;
       }
       $freeslot = ($INSTALLER09['mods']['slots'] ? ($free_slot ?'&nbsp;<img src="'.$INSTALLER09['pic_base_url'].'freedownload.gif" width="12px" alt="Free Slot" title="Free Slot in Use" />&nbsp;<small>Free Slot</small>' : '').($double_slot ?'&nbsp;<img src="'.$INSTALLER09['pic_base_url'].'doubleseed.gif" width="12px" alt="Double Upload Slot" title="Double Upload Slot in Use" />&nbsp;<small>Double Slot</small>' : ''):'').($row['nuked'] != 'no' && $row['nuked'] != '' ? '&nbsp;<span title="Nuked '.htmlspecialchars($row['nuked']).'" class="browse-icons-nuked"></span>' : '');
       //==
       $Subs='';
       $movie_cat = array("1","5","6","10","11"); //add here your movie category 
       if (in_array($row["category"], $movie_cat) && !empty($row["subs"]) )
       {
       $subs_array = explode(",",$row["subs"]);
       require_once(CACHE_DIR.'subs.php');
       foreach ($subs_array as $k => $sid) {
       foreach ($subs as $sub){
       if ($sub["id"] == $sid)
       $Subs ="<img border=\'0\' width=\'16px\' style=\'padding:3px;\' src=\'{$sub["pic"]}\' alt=\'{$sub["name"]}\' title=\'{$sub["name"]}\' />";
       }
       }
       }else
       $Subs ="---";
       $htmlout .= "' onmouseover=\"Tip('<b>" . CutName($dispname, 80) . "</b><br /><b>Added:&nbsp;".get_date($row['added'],'DATE',0,1)."</b><br /><b>Size:&nbsp;".mksize(htmlspecialchars($row["size"])) ."</b><br /><b>Subtitle:&nbsp;{$Subs}</b><br /><b>Seeders:&nbsp;".htmlspecialchars($row["seeders"]) ."</b><br /><b>Leechers:&nbsp;".htmlspecialchars($row["leechers"]) ."</b><br /><b>Rating:&nbsp;".htmlspecialchars($rating) ."</b><br />$poster');\" onmouseout=\"UnTip();\"><b>" . CutName($dispname, 45) . "</b></a>&nbsp;&nbsp;<a href=\"javascript:klappe_descr('descr" . (int)$row["id"] . "');\" ><img src=\"{$INSTALLER09['pic_base_url']}plus.png\" border=\"0\" alt=\"Show torrent info in this page\" title=\"Show torrent info in this page\" /></a>&nbsp;&nbsp;$viponly&nbsp;$release_group&nbsp;$sticky&nbsp;".($row['added'] >= $CURUSER['last_browse'] ? " <img src='{$INSTALLER09['pic_base_url']}newb.png' border='0' alt='New !' title='New !' />" : "")."&nbsp;$checked&nbsp;$free_tag&nbsp;$nuked<br />\n".$freeslot."&nbsp;$newgenre&nbsp;$bump&nbsp;$smalldescr</td>\n";
	     if ($variant == "mytorrents")
       $htmlout .= "<td align='center'><a href=\"download.php?torrent={$id}".($CURUSER['ssluse'] == 3 ? "&amp;ssl=1" : "")."\"><img src='{$INSTALLER09['pic_base_url']}zip.gif' border='0' alt='Download This Torrent!' title='Download This Torrent!' /></a></td>\n";
	        
       if ($variant == "mytorrents")  
       $htmlout .= "<td align='center'><a href='edit.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;id={$row['id']}'>{$lang["torrenttable_edit"]}</a></td>\n";
       $htmlout.= ($variant == "index" ? "<td align='center'><a href=\"download.php?torrent={$id}".($CURUSER['ssluse'] == 3 ? "&amp;ssl=1" : "")."\"><img src='{$INSTALLER09['pic_base_url']}zip.gif' border='0' alt='Download This Torrent!' title='Download This Torrent!' /></a></td>" : "");
        
       if ($variant == "mytorrents") 
       {
       $htmlout .= "<td align='right'>";
       if ($row["visible"] == "no")
       $htmlout .= "<b>{$lang["torrenttable_not_visible"]}</b>";
       else
       $htmlout .= "{$lang["torrenttable_visible"]}";
       $htmlout .= "</td>\n";
       }
       /** pdq bookmarks **/
       $booked = '';
       if (!empty($book))
            foreach ($book as $bk) {
                if ($bk['torrentid'] == $id)    
                    $booked = 1;
        }    
        $rm_status = (!$booked ? ' style="display:none;"' : ' style="display:inline;"');
        $bm_status = ($booked ? ' style="display:none;"' : ' style="display:inline;"');
        $bookmark = '<span id="bookmark'.$id.'"'.$bm_status.'>
                    <a href="bookmark.php?torrent='.$id.'&amp;action=add" class="bookmark" name="'.$id.'">
                    <span title="Bookmark it!" class="add_bookmark_b">
                    <img src="'.$INSTALLER09['pic_base_url'].'aff_tick.gif" align="top" width="14px" alt="Bookmark it!" title="Bookmark it!" />
                    </span>
                    </a>
                    </span>
                    
                    <span id="remove'.$id.'"'.$rm_status.'>
                    <a href="bookmark.php?torrent='.$id.'&amp;action=delete" class="remove" name="'.$id.'">
                    <span class="remove_bookmark_b">
                    <img src="'.$INSTALLER09['pic_base_url'].'aff_cross.gif" align="top" width="14px" alt="Delete Bookmark!" title="Delete Bookmark!" />
                    </span>
                    </a>
                    </span>';
       
       if ($variant == "index")  
       $htmlout.="<td align='right'>{$bookmark}</td>"; 
       
       if ($row["type"] == "single")
       {
       $htmlout .= "<td align='right'>{$row["numfiles"]}</td>\n";
       }
       else 
       {
       if ($variant == "index")
       {
       $htmlout .= "<td align='right'><b><a href='filelist.php?id=$id'>{$row["numfiles"]}</a></b></td>\n";
       }
       else
       {
       $htmlout .= "<td align='right'><b><a href='filelist.php?id=$id'>{$row["numfiles"]}</a></b></td>\n";
       }
       }

       if (!$row["comments"])
       {
       $htmlout .= "<td align='right'>{$row["comments"]}</td>\n";
       }
       else 
       {
       if ($variant == "index")
       {
       $htmlout .= "<td align='right'><b><a href='details.php?id=$id&amp;hit=1&amp;tocomm=1'>{$row["comments"]}</a></b></td>\n";
       }
       else
       {
       $htmlout .= "<td align='right'><b><a href='details.php?id=$id&amp;page=0#startcomments'>{$row["comments"]}</a></b></td>\n";
       }
       }

       $htmlout .= "<td align='center'><span style='white-space: nowrap;'>" . str_replace(",", "<br />", get_date( $row['added'],'')) . "</span></td>\n";
       $htmlout .= "<td align='center'>" . str_replace(" ", "<br />", mksize($row["size"])) . "</td>\n";

       if ($row["times_completed"] != 1)
       $_s = "".$lang["torrenttable_time_plural"]."";
       else
       $_s = "".$lang["torrenttable_time_singular"]."";
       $htmlout .= "<td align='center'><a href='snatches.php?id=$id'>" . number_format($row["times_completed"]) . "<br />$_s</a></td>\n";

       if ($row["seeders"]) 
       {
       if ($variant == "index")
       {
       if ($row["leechers"]) $ratio = $row["seeders"] / $row["leechers"]; else $ratio = 1;
       $htmlout .= "<td align='right'><b><a href='peerlist.php?id=$id#seeders'><font color='" .get_slr_color($ratio) . "'>{$row["seeders"]}</font></a></b></td>\n";
       }
       else
       {
       $htmlout .= "<td align='right'><b><a class='".linkcolor($row["seeders"])."' href='peerlist.php?id=$id#seeders'>{$row["seeders"]}</a></b></td>\n";
       }
       }
       else
       {
       $htmlout .= "<td align='right'><span class='".linkcolor($row["seeders"])."'>{$row["seeders"]}</span></td>\n";
       }

       if ($row["leechers"]) 
       {
       if ($variant == "index")
       $htmlout .= "<td align='right'><b><a href='peerlist.php?id=$id#leechers'>".number_format($row["leechers"])."</a></b></td>\n";
       else
       $htmlout .= "<td align='right'><b><a class='".linkcolor($row["leechers"])."' href='peerlist.php?id=$id#leechers'>{$row["leechers"]}</a></b></td>\n";
       }
       else
       $htmlout .= "<td align='right'>0</td>\n";
        
       if ($variant == "index") {
       $htmlout .= "<td align='center'>" . (isset($row["username"]) ? (($row["anonymous"] == "yes" && $CURUSER['class'] < UC_MODERATOR && $row['owner'] != $CURUSER['id']) ? "<i>".$lang['torrenttable_anon']."</i>" : "<a href='userdetails.php?id=" .(int)$row["owner"] . "'><b>" . htmlspecialchars($row["username"]) . "</b></a>") : "<i>(".$lang["torrenttable_unknown_uploader"].")</i>") . "</td>\n";
       }
    
       $htmlout .= "</tr>\n";
       $htmlout .="<tr id=\"kdescr{$row["id"]}\" style=\"display:none;\"><td width=\"100%\" colspan=\"13\">".format_comment($descr)."</td></tr>\n";
       }
       $htmlout .= "</table><!--</div>-->\n";
       return $htmlout;
       }  
        
?>
