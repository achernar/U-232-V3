<?php
/**********************************************************
New 2010 forums that don't suck for TB based sites....
Download Atachment, so far coded from scratch,
it's possible some TB code snuck in, who knows lol

but a big credit to php.net and google lol

beta thurs june 17th 2010 v0.1

Powered by Bunnies!!!
**********************************************************/

if (!defined('BUNNY_FORUMS')) 
{
	$HTMLOUT ='';
	$HTMLOUT .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
        <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
        <title>ERROR</title>
        </head><body>
        <h1 style="text-align:center;">ERROR</h1>
        <p style="text-align:center;">How did you get here? silly rabbit Trix are for kids!.</p>
        </body></html>';
	echo $HTMLOUT;
	exit();
}

	$id = (isset($_GET['id']) ? intval($_GET['id']) :  (isset($_POST['id']) ? intval($_POST['id']) :  0));
   
    if (!is_valid_id($id))
    {
	stderr('Error', 'Bad ID.');
    }	

//print $upload_folder;
//exit();

	//=== log  people who DL the file
	sql_query('UPDATE `attachments` SET `times_downloaded` = times_downloaded + 1 WHERE `id` = '.$id);
	
	$what_to_download_res = sql_query('SELECT file, extension FROM `attachments` WHERE `id` = '.$id);
	$what_to_download_arr = mysqli_fetch_assoc($what_to_download_res);
	
header('Content-type: application/'.$what_to_download_arr['extension']);
header('Content-Disposition: attachment; filename="'.$what_to_download_arr['file'].'"');
readfile($upload_folder.$what_to_download_arr['file']);
?>