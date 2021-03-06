<?php
/**
 *   https://github.com/Bigjoos/
 *   Licence Info: GPL
 *   Copyright (C) 2010 U-232 v.3
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless, putyn.
 **/
//== usercomments.php - by pdq - based on comments.php, duh :P
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php');
require_once(INCL_DIR.'user_functions.php');
require_once(INCL_DIR.'bbcode_functions.php');
require_once(INCL_DIR.'pager_functions.php');
require_once(INCL_DIR.'html_functions.php');
dbconn(false);
loggedinorreturn();

$lang = array_merge( load_language('global') );

$HTMLOUT = $user ='';

$action = isset($_GET["action"]) ? htmlspecialchars(trim($_GET["action"])) : '';

function usercommenttable($rows)
{
    $htmlout='';
    global $CURUSER, $INSTALLER09, $userid;
    $htmlout .= begin_main_frame();
    $htmlout .= begin_frame();
    $count = 0;
    foreach ($rows as $row) {
       $htmlout .="<p class='sub'>#{$row['id']} by ";
        if (isset($row["username"])) {
            $title = $row["title"];
            if ($title == "")
            $title = get_user_class_name($row["class"]);
            else
            $title = htmlspecialchars($title);
            $htmlout .="<a name='comm{$row['id']}' href='userdetails.php?id=".(int)$row['user']."'><b>" .
                htmlspecialchars($row["username"])."</b></a>" . ($row["donor"] == "yes" ? "<img src=\"{$INSTALLER09['pic_base_url']}star.gif\" alt='Donor' />" : "") . ($row["warned"] == "yes" ? "<img src=" . "\"{$INSTALLER09['pic_base_url']}warned.gif\" alt=\"Warned\" />" : "") . " ($title)\n";
        } else
            $htmlout .="<a name=\"comm" . $row["id"] . "\"><i>(orphaned)</i></a>\n";

        $htmlout .=" ".get_date($row["added"], 'DATE',0,1)."" .
            ($userid == $CURUSER["id"] || $row["user"] == $CURUSER["id"] || $CURUSER['class'] >= UC_STAFF ? " - [<a href='usercomment.php?action=edit&amp;cid={$row['id']}'>Edit</a>]" : "") .
            ($userid == $CURUSER["id"] || $CURUSER['class'] >= UC_STAFF ? " - [<a href='usercomment.php?action=delete&amp;cid={$row['id']}'>Delete</a>]" : "") .
            ($row["editedby"] && $CURUSER['class'] >= UC_STAFF ? " - [<a href='usercomment.php?action=vieworiginal&amp;cid={$row['id']}'>View original</a>]" : "") . "</p>\n";
        $avatar = ($CURUSER["avatars"] == "yes" ? htmlspecialchars($row["avatar"]) : "");
        if (!$avatar)
        $avatar = "{$INSTALLER09['pic_base_url']}default_avatar.gif";
        $text = format_comment($row["text"]);
        if ($row["editedby"])
        $text .= "<font size='1' class='small'><br /><br />Last edited by <a href='userdetails.php?id={$row['editedby']}'><b>{$row['username']}</b></a> ".get_date($row['editedat'], 'DATE',0,1)."</font>\n";
        $htmlout .= begin_table(true);
        $htmlout .="<tr valign='top'>\n";
        $htmlout .="<td align='center' width='150' style='padding:0px'><img width='150' src=\"{$avatar}\" alt=\"Avatar\" /></td>\n";
        $htmlout .="<td class='text'>{$text}</td>\n";
        $htmlout .="</tr>\n";
        $htmlout .= end_table();
    }
    $htmlout .= end_frame();
    $htmlout .= end_main_frame();
    return $htmlout;
}

if ($action == "add") {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $userid = 0 + $_POST["userid"];
        if (!is_valid_id($userid))
            stderr("Error", "Invalid ID.");

        $res = sql_query("SELECT username FROM users WHERE id ={$userid}") or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_array($res,  MYSQLI_NUM);
        if (!$arr)
            stderr("Error", "No user with that ID.");

        $text = trim($_POST["text"]);
        if (!$text)
            stderr("Error", "Comment body cannot be empty!");

        sql_query("INSERT INTO usercomments (user, userid, added, text, ori_text) VALUES ({$CURUSER['id']}, {$userid}, '" . TIME_NOW . "', " . sqlesc($text) . "," . sqlesc($text) . ")");

        $newid = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);

        sql_query("UPDATE users SET comments = comments + 1 WHERE id ={$userid}");

        header("Refresh: 0; url=userdetails.php?id=$userid&viewcomm=$newid#comm$newid");
        die;
    }

    $userid = 0 + $_GET["userid"];
    if (!is_valid_id($userid))
        stderr("Error", "Invalid ID.");

    $res = sql_query("SELECT username FROM users WHERE id = {$userid}") or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_assoc($res);
    if (!$arr)
        stderr("Error", "No user with that ID.");

    $HTMLOUT .="<h1>Add a comment for '" . htmlspecialchars($arr["username"]) . "'</h1>
    <form method='post' action='usercomment.php?action=add'>
    <input type='hidden' name='userid' value='".(int)$userid."' />
    <textarea name='text' rows='10' cols='60'></textarea>
    <br /><br />
    <input type='submit' class='btn' value='Do it!' /></form>\n";

    $res = sql_query("SELECT usercomments.id, usercomments.text, usercomments.editedby, usercomments.editedat, usercomments.added, username, users.id as user, users.avatar, users.title, users.anonymous, users.class, users.donor, users.warned, users.leechwarn, users.chatpost FROM usercomments LEFT JOIN users ON usercomments.user = users.id WHERE user = {$userid} ORDER BY usercomments.id DESC LIMIT 5");

    $allrows = array();
    while ($row = mysqli_fetch_assoc($res))
    $allrows[] = $row;

    if (count($allrows)) {
        $HTMLOUT .="<h2>Most recent comments, in reverse order</h2>\n";
        $HTMLOUT .= usercommenttable($allrows);
    }
    echo stdhead("Add a comment for \"" .htmlspecialchars($arr["username"]). "\"") . $HTMLOUT . stdfoot();
    die;
} elseif ($action == "edit") {
    $commentid = 0 + $_GET["cid"];
    if (!is_valid_id($commentid))
        stderr("Error", "Invalid ID.");

    $res = sql_query("SELECT c.*, u.username, u.id FROM usercomments AS c LEFT JOIN users AS u ON c.userid = u.id WHERE c.id={$commentid}") or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_assoc($res);
    if (!$arr)
        stderr("Error", "Invalid ID.");

    if ($arr["user"] != $CURUSER["id"] && $CURUSER['class'] < UC_STAFF)
        stderr("Error", "Permission denied.");

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $text = $_POST["text"];
        $returnto = $_POST["returnto"];

        if ($text == "")
            stderr("Error", "Comment body cannot be empty!");

        $text = sqlesc($text);

        $editedat = sqlesc(TIME_NOW);

        sql_query("UPDATE usercomments SET text={$text}, editedat={$editedat}, editedby={$CURUSER['id']} WHERE id={$commentid}") or sqlerr(__FILE__, __LINE__);

        if ($returnto)
        header("Location: " . htmlspecialchars($returnto) . "");
    else
        header("Location: {$INSTALLER09['baseurl']}/userdetails.php?id={$userid}");
        die;
    }

    $HTMLOUT .="<h1>Edit comment for \"" . htmlspecialchars($arr["username"]) . "\"</h1>
    <form method='post' action='usercomment.php?action=edit&amp;cid={$commentid}'>
    <input type='hidden' name='returnto' value='{$_SERVER["HTTP_REFERER"]}' />
    <input type=\"hidden\" name=\"cid\" value='".(int)$commentid."' />
    <textarea name='text' rows='10' cols='60'>" . htmlspecialchars($arr["text"]) . "</textarea>
    <input type='submit' class='btn' value='Do it!' /></form>";
    echo stdhead("Edit comment for \"" .htmlspecialchars($arr["username"])."\"") . $HTMLOUT . stdfoot();
    stdfoot();
    die;
} elseif ($action == "delete") {
    
    $commentid = 0 + $_GET["cid"];

    if (!is_valid_id($commentid))
        stderr("Error", "Invalid ID.");
    
    $sure = isset($_GET["sure"]) ? (int)$_GET["sure"] : false;

    if (!$sure) {
        $referer = $_SERVER["HTTP_REFERER"];
        stderr("Delete comment", "You are about to delete a comment. Click\n" . "<a href='usercomment.php?action=delete&amp;cid=$commentid&amp;sure=1" .
             ($referer ? "&amp;returnto=" . urlencode($referer) : "") . "'>here</a> if you are sure.");
        //stderr("Delete comment", "You are about to delete a comment. Click\n" . "<a href='usercomment.php?action=delete&amp;cid={$commentid}&amp;sure=1&amp;returnto=".urlencode($_SERVER['PHP_SELF'])."'>here</a> if you are sure.");
    }
    
    $res = sql_query("SELECT id, userid FROM usercomments WHERE id={$commentid}") or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_assoc($res);
    
    if ($arr['id'] != $CURUSER['id']) {
        if ($CURUSER['class'] < UC_STAFF)
            stderr("Error", "Permission denied.");
    }
    
    if ($arr)
    $userid = (int)$arr["userid"];
    sql_query("DELETE FROM usercomments WHERE id={$commentid}") or sqlerr(__FILE__, __LINE__);
    
    if ($userid && mysqli_affected_rows($GLOBALS["___mysqli_ston"]) > 0)
        sql_query("UPDATE users SET comments = comments - 1 WHERE id = {$userid}");
        $returnto = $_GET["returnto"];
        if ($returnto)
        header("Location: " . htmlspecialchars($returnto) . "");
        else
        header("Location: {$INSTALLER09['baseurl']}/userdetails.php?id={$userid}");
    die;
} elseif ($action == "vieworiginal") {
    if ($CURUSER['class'] < UC_STAFF)
        stderr("Error", "Permission denied.");

    $commentid = 0 + $_GET["cid"];

    if (!is_valid_id($commentid))
        stderr("Error", "Invalid ID.");

    $res = sql_query("SELECT c.*, u.username FROM usercomments AS c LEFT JOIN users AS u ON c.userid = u.id WHERE c.id={$commentid}") or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_assoc($res);
    if (!$arr)
        stderr("Error", "Invalid ID");

    $HTMLOUT .="<h1>Original contents of comment #{$commentid}</h1>
    <table width='500' border='1' cellspacing='0' cellpadding='5'>
    <tr><td class='comment'>\n";
    $HTMLOUT .=" ".htmlspecialchars($arr["ori_text"]);
    $HTMLOUT .="</td></tr></table>\n";

    $returnto = htmlspecialchars($_SERVER["HTTP_REFERER"]);
    if ($returnto)
         $HTMLOUT .="<font size='small'>(<a href='{$returnto}'>back</a>)</font>\n";

    echo stdhead("User Comments") . $HTMLOUT . stdfoot();
    die;
} else
    stderr("Error", "Unknown action");

die;
?>
