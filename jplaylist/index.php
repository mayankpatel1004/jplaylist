<?php
$relMusicDir='media';
//set default playlist sorting order options
$sortby='mtime';
$ID3sort='track';
$skipdesc=false;
$autoplay='false';
$useID3=true;
$showfromtext='true';
$allowdownload='true';
$playermode='av';
$posterlocation='graphics/jplayer_playlister_poster.png';
$charset='utf-8';
$debug=false;
$autoplay='true';
if($_GET["name"]!=''){$relMusicDir=$relMusicDir.'/'.$_GET["name"];}
if($useID3==true){

//get php version in parts for comparison
$phpvparts=explode('.',phpversion());

//display error if php version is lower than needed for GETID3
if($phpvparts[0]<5 OR ( $phpvparts[0]=5 AND $phpvparts[1]<1 AND $phpvparts[2]<5)){
echo 'GETID3 1.9.3 (used for ID3 tag information parsing) requires PHP 5.0.5 or higher.  Get older version or upgrade PHP for ID3 usage.  <br />*disabling id3*';
}

else{

//for ID3 (not just filename) information, include getID3 php class
require_once('getid3/getid3.php'); /* Comment out if you only want to use filenames */

// Initialize getID3 engine
$getID3 = new getID3;

}

}

//call function to get information for all songs in target directory (and sub-directories) and store in an array
$fileinfo=recursiveGetSongs($relMusicDir, $fileinfo, $useID3, $getID3, null,$debug, $filtered,$playermode);

//debug -- show results
if($debug==true) var_dump($fileinfo);


/*
*	Prepare jplayer filetype inclusions as determined by the available file extensions
*/

//create 'supplied' extension list from the keys of $fileinfo['extensions']
$supplied='supplied: "'.implode(', ',array_keys($fileinfo['extensions'])).'"';
unset($fileinfo['extensions']); //unset subarray so it doesn't break the playlist creation
$comma='';
?>

<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' lang='en' xml:lang='en'>
<head>
<!-- Website Design By: www.happyworm.com | being used by Nick Chapman for a JPlayer Playlister-->
<title>JPlayer Playlister<?php echo $ver;?></title>
<!--<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />-->
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset; ?>" />

<!-- PICK A THEME
<link href="skin/pink.flag/jplayer.pink.flag.css" rel="stylesheet" type="text/css" />
-->
<link href="skin/blue.monday/jplayer.blue.monday.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery.jplayer.min.js"></script>
<script type="text/javascript" src="js/jplayer.playlist.min.js"></script>
<?php
if( ($_SERVER['SERVER_NAME']=='jplaylister.yaheard.us') OR ($_SERVER['SERVER_NAME']=='chapmanit.thruhere.net') )
include '../ga.php';
?>
<script type="text/javascript">
//<![CDATA[
$(document).ready(function(){

new jPlayerPlaylist({
jPlayer: "#jquery_jplayer_1",
cssSelectorAncestor: "#jp_container_1"
}, [

/* ORIGINAL PLAYLIST -- FOR REFERENCE */
/*
{
title:"Hidden",
artist:"Miaow",
mp3:"http://www.jplayer.org/audio/mp3/Miaow-02-Hidden.mp3",
oga:"http://www.jplayer.org/audio/ogg/Miaow-02-Hidden.ogg",
poster: "http://www.jplayer.org/audio/poster/Miaow_640x360.png"
},
{
title:"Big Buck Bunny Trailer",
artist:"Blender Foundation",
m4v:"http://www.jplayer.org/video/m4v/Big_Buck_Bunny_Trailer.m4v",
ogv:"http://www.jplayer.org/video/ogv/Big_Buck_Bunny_Trailer.ogv",
webmv: "http://www.jplayer.org/video/webm/Big_Buck_Bunny_Trailer.webm",
poster:"http://www.jplayer.org/video/poster/Big_Buck_Bunny_Trailer_480x270.png"
},
*/


<?php

//set spacer variable for cleaner playlist layout
$plspacer="\n\t\t\t";
$counter=0;

foreach ($fileinfo as $value) {

//modify 'show from' text if configuration variable set
if($showfromtext=='true'){
if( $_GET["name"]=='' AND $value["from"]!='root' ){

$strippedrmd=str_replace(array('.', '/'), array('', ''), $relMusicDir);
$showfrom='<br />[from '.str_replace($strippedrmd.'&raquo;', '', $value['from']).']';

}
else
$showfrom='<br />'.$relmusicdir; //explicitely state
}
else
$showfrom='';


//allow download if variable set
if($allowdownload=='true'){
//works for files with three character extensions only
$dl=','.$plspacer.'free:true,'.$plspacer.substr($value[path],-3).':"'.$value[path].'"';
}
else{
$dl=','.$plspacer.substr($value[path],-3).':"'.$value[path].'"';
}

//check for poster
if( $playermode!='audio' AND isset($value['art']) ){
//$poster=','.$plspacer.'poster:"'.$posterlocation.'"';
$poster=','.$plspacer.'poster:"'.$value['art'].'"';
}
else if ( $playermode!='audio' AND !isset($value['art']) ){
$poster=','.$plspacer.'poster:"'.$posterlocation.'"';
}
else{
$poster='';
}

//if array is valid, start digging for information
if (is_array($value)){

if($useID3==TRUE){
//if artist or title are empty, use filename
if( (!isset($value['artist'])) AND (!isset($value['title'])) )
echo$comma.'{'.$plspacer.'title:"'.$value['filename'].'",'.$plspacer.'artist:"'.$showfrom.'"'.$dl.$poster.'}';

//otherwise, use artist and title correctly
else if( (isset($value['filename'])) OR (isset($value['path'])) )
echo$comma.'{'.$plspacer.'artist:"'.$value['artist'].$showfrom.'",'.$plspacer.'title:"'.$value['title'].'"'.$dl.$poster.'}';

}

else 
//assume filename only
echo$comma.'{'.$plspacer.'title:"'.$value['fn'].'",'.$plspacer.'artist:"'.$showfrom.'"'.$dl.$poster.'"}';

$comma=','."\n\t\t";


}
}

?>

], {
playlistOptions: {
<?php
//set autoplay
if($autoplay=='true')echo'autoPlay: true';
?>
},
swfPath: "js",
<?php echo $supplied; ?>		

});
});
//]]>
</script>
</head>
<body>
<?php
echo'<div style="text-align: center;">';

//if development version, echo link to main version
/*if ($verdev==true){
echo'<h3 style="color: red;">';
include'../current_version.html';
echo'</h3>';
}	

echo'<h1>'.$verfulltitle.' '.$ver
.'<br /><span style="font-size: 70%; color: #AAA;">'.$versubtitle.'</span></h1></div>';


echo $notice;*/

/*
* check for skipdesc variable or makesparse arg || otherwise, set content div -- this section can be deleted safely
*/
//prep jplayer controls
if($playermode=='audio'){

$jpinstance = <<<EOF
<div id="jquery_jplayer_1" class="jp-jplayer" style="height: 0px;"></div>

<div id="jp_container_1" class="jp-audio">
<div class="jp-type-playlist">
<div class="jp-gui jp-interface">
<ul class="jp-controls">
<li><a href="javascript:;" class="jp-previous" tabindex="1">previous</a></li>
<li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>
<li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li>
<li><a href="javascript:;" class="jp-next" tabindex="1">next</a></li>
<li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li>
<li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">mute</a></li>
<li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">unmute</a></li>
<li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">max volume</a></li>
</ul>
<div class="jp-progress">
<div class="jp-seek-bar">
<div class="jp-play-bar"></div>
</div>
</div>
<div class="jp-volume-bar">
<div class="jp-volume-bar-value"></div>
</div>
<div class="jp-time-holder">
<div class="jp-current-time"></div>
<div class="jp-duration"></div>
</div>
<ul class="jp-toggles">
<li><a href="javascript:;" class="jp-shuffle" tabindex="1" title="shuffle">shuffle</a></li>
<li><a href="javascript:;" class="jp-shuffle-off" tabindex="1" title="shuffle off">shuffle off</a></li>
<li><a href="javascript:;" class="jp-repeat" tabindex="1" title="repeat">repeat</a></li>
<li><a href="javascript:;" class="jp-repeat-off" tabindex="1" title="repeat off">repeat off</a></li>
</ul>
</div>
<div class="jp-playlist">
<ul>
<li></li>
</ul>
</div>
<div class="jp-no-solution">
<span>Update Required</span>
To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
</div>
</div>
</div>
EOF;
}

else{
$jpinstance = <<<EOF

<div id="jp_container_1" class="jp-video jp-video-270p">
<div class="jp-type-playlist">
<div id="jquery_jplayer_1" class="jp-jplayer"></div>
<div class="jp-gui">
<div class="jp-video-play">
<a href="javascript:;" class="jp-video-play-icon" tabindex="1">play</a>
</div>
<div class="jp-interface">
<div class="jp-progress">
<div class="jp-seek-bar">
<div class="jp-play-bar"></div>
</div>
</div>
<div class="jp-current-time"></div>
<div class="jp-duration"></div>
<!-- added style below to fix floating issue (default was clear:both by css) -- not important to code function -->
<div class="jp-controls-holder" style="clear:left;">
<ul class="jp-controls">
<li><a href="javascript:;" class="jp-previous" tabindex="1">previous</a></li>
<li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>
<li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li>
<li><a href="javascript:;" class="jp-next" tabindex="1">next</a></li>
<li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li>
<li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">mute</a></li>
<li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">unmute</a></li>
<li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">max volume</a></li>
</ul>
<div class="jp-volume-bar">
<div class="jp-volume-bar-value"></div>
</div>
<ul class="jp-toggles">
<li><a href="javascript:;" class="jp-full-screen" tabindex="1" title="full screen">full screen</a></li>
<li><a href="javascript:;" class="jp-restore-screen" tabindex="1" title="restore screen">restore screen</a></li>
<li><a href="javascript:;" class="jp-shuffle" tabindex="1" title="shuffle">shuffle</a></li>
<li><a href="javascript:;" class="jp-shuffle-off" tabindex="1" title="shuffle off">shuffle off</a></li>
<li><a href="javascript:;" class="jp-repeat" tabindex="1" title="repeat">repeat</a></li>
<li><a href="javascript:;" class="jp-repeat-off" tabindex="1" title="repeat off">repeat off</a></li>
</ul>
</div>
<div class="jp-title">
<ul>
<li></li>
</ul>
</div>
</div>
</div>
<div class="jp-playlist">
<ul>
<!-- The method Playlist.displayPlaylist() uses this unordered list -->
<li></li>
</ul>
</div>
<div class="jp-no-solution">
<span>Update Required</span>
To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
</div>
</div>
</div>
EOF;
}

?>
<div> 

<!-- show sorting options -->
<?php 

echo'<h3>'.$from.'</h3>'.$backlink.$sortedbytext.'<br />'.$sortoptions;

//show jplayer instance code
echo $jpinstance;

//show filtering options
echo$foldershtml.$playalllink;

?>
</div>
</body>
</html>
<?php
function recursiveGetSongs($directory, $fileinfo, $useID3, $getID3, $parent=null, $debug, $filtered=null, $playermode='audio'){

/*
* configure function here:
*
* _usage_
*	> the disallowed array should include any folders or files you don't want displayed
*	> the allowedfiletypes array should include any file extentions you want to play
*/
$disallowed=array('..', '.', 'js', 'skin', 'z_jquery-ui-1.7.1.custom', 'getid3');

//for audio playermode, only look for defined audio files
if($playermode=='audio'){
$allowedfiletypes=array('mp3', 'ogg', 'oga', 'm4a', 'wma', 'webma');
}

else{
$allowedfiletypes=array('mp3', 'ogg', 'oga', 'm4a', 'wma', 'webma', 'm4v', 'ogv', 'mp4', 'webmv', 'webm');
}

if($filtered!=null){
$disallowed=array_merge((array)$filtered, (array)$disallowed);
}

//simple error fix
if($directory=='./')
$directory='.';

//debug
if ($debug==true)echo'Dir to open: '.$directory;

//open directory
$dir = opendir($directory); 

while ($read = readdir($dir)){

//if ( !in_array($read, $disallowed) AND ( $filter!=null AND in_array($read, $filter) ) )
if ( !in_array($read, $disallowed) )
{ 
if($debug==true)echo $read.'<br />';
//if is not dir, handle file
if ( !is_dir($directory.'/'.$read) ){

if($debug==true)echo '^^ not dir | dir: '.$directory.'<br />';

if( in_array(substr($read, -3, 3), $allowedfiletypes) ){

if($useID3==TRUE){

//store id3 info
$FullFileName = realpath($directory.'/'.$read);
if($debug==TRUE)echo'<br />FFN &raquo; '.$FullFileName;
$ThisFileInfo = $getID3->analyze($FullFileName);
getid3_lib::CopyTagsToComments($ThisFileInfo);
$fileinfo[$read]['artist']=$ThisFileInfo['comments_html']['artist'][0];
$fileinfo[$read]['album']=$ThisFileInfo['comments_html']['album'][0];
$fileinfo[$read]['title']=$ThisFileInfo['comments_html']['title'][0];
$fileinfo[$read]['filename']=$ThisFileInfo['filename'];
//$fileinfo[$read]['filenamealt']=$read; //alternate filename for hebrew problem
$fileinfo[$read]['modified']=date ("YmdHis", filemtime($directory.'/'.$read));

/*
* ALBUM ART TESTING //ID3
*/

$usealbumart=true;
$albumartdefaultname='album_art.jpg'; //set as text string ('front.jpg', 'album_art.jpg') which will be displayed as art
$albumartaltname=$fileinfo[$read]['artist'].'_'.$fileinfo[$read]['album'].'.jpg'; //allows alternate naming convention


//look for album art in media directory based on default name
if($usealbumart==true AND file_exists($directory.'/'.$albumartdefaultname)){
$fileinfo[$read]['art']=$directory.'/'.$albumartdefaultname;
if($debug==true)
echo'album art already exists for '.$fileinfo[$read]['filename'].' @ '.$fileinfo[$read]['art'].'<br />';
}

//look for album art in media directory based on alternate name (allow two folder-based locations/naming options)
else if($usealbumart==true AND file_exists($directory.'/'.$albumartaltname)){
$fileinfo[$read]['art']=$directory.'/'.$albumartaltname;
if($debug==true)
echo'album art already exists for '.$fileinfo[$read]['filename'].' @ '.$fileinfo[$read]['art'].'<br />';
}

//if embedded art exists -- extract it
else if($usealbumart==true AND isset($ThisFileInfo['comments']['picture'][0]['data'])){

//determine filename -- if album name exists, use artist_album -- else use filename
if(!isset($fileinfo[$read]['album']))
$fn='graphics/artstore/'.$fileinfo[$read]['artist'].'_'.$fileinfo[$read]['album'].'.jpg';

else
$fn='graphics/artstore/'.$fileinfo[$read]['filename'].'.jpg';

//if fn doesn't exist, create it
if (!file_exists($fn)){
//create image
$img=imagecreatefromstring($ThisFileInfo['comments']['picture'][0]['data']);
imagejpeg($img, $fn);
if($debug==true)
echo'file created: <img src="'.$fn.'" />';
imagedestroy($img);

}
else if($debug==true){
//file already exists, pass fn back for poster usage
echo'file exists: <img src="'.$fn.'" />';
}

//set fn as array item
$fileinfo[$read]['art']=$fn;

}

//END ALBUM ART TESTING

if($debug==true)
echo "<br />$read was last modified: " . date ("YmdHis", filemtime($directory.'/'.$read));

$fileinfo[$read]['path']=$directory.'/'.$read;
if($debug==true)echo'<span style="margin-left: 10px;">path:'.$fileinfo[$read]['path'].' > fn: '.$fileinfo[$read]['filename'].'</span><br /><br />';

if($parent!=null)
$fileinfo[$read]['from']=str_replace(array('./', '//', '/'), array('', '&raquo;', '&raquo;'), $directory); // was =$parent

else
$fileinfo[$read]['from']='root'; //testing this

if($debug==true){
echo'<br />'.$fileinfo[$read]['from'].'<br />';
echo$ThisFileInfo['filename'].' '.$fileinfo[$read]['path'].'<br />'; 
}

//capture file extension
$fileinfo['extensions'][substr($ThisFileInfo['filename'],strrpos($ThisFileInfo['filename'],'.')+1)]=1;

}
else{
//store filename
$fileinfo[$fileinfo['count']]['path']=$directory.'/'.$read;
$fileinfo[$fileinfo['count']]['fn']=$read;
if($parent!=null)
$fileinfo[$fileinfo['count']]['from']=str_replace(array('./', '//', '/'), array('', '&raquo;', '&raquo;'), $directory);

$fileinfo[$fileinfo['count']]['modified']=date ("YmdHis", filemtime($directory.'/'.$read));
//$fileinfo[$fileinfo['count']]=date ("YmdHis", filemtime($directory.'/'.$read));

/*
* ALBUM ART TESTING //NON ID3
*/

$usealbumart=true;
$albumartdefaultname='album_art.jpg'; //set as text string ('front.jpg', 'album_art.jpg') which will be displayed as art
$albumartaltname=$read.'.jpg'; //allows alternate naming convention


//look for album art in media directory based on default name
if($usealbumart==true AND file_exists($directory.'/'.$albumartdefaultname)){
$fileinfo[$read]['art']=$directory.'/'.$albumartdefaultname;
if($debug==true)
echo'album art already exists for '.$fileinfo[$read]['filename'].' @ '.$fileinfo[$read]['art'].'<br />';
}

//look for album art in media directory based on alternate name (allow two folder-based locations/naming options)
else if($usealbumart==true AND file_exists($directory.'/'.$albumartaltname)){
$fileinfo[$read]['art']=$directory.'/'.$albumartaltname;
if($debug==true)
echo'album art already exists for '.$fileinfo[$read]['filename'].' @ '.$fileinfo[$read]['art'].'<br />';
}

//if embedded art exists -- extract it
else if($usealbumart==true AND isset($ThisFileInfo['comments']['picture'][0]['data'])){

//determine filename -- if album name exists, use artist_album -- else use filename
if(!isset($fileinfo[$read]['album']))
$fn='graphics/artstore/'.$fileinfo[$read]['artist'].'_'.$fileinfo[$read]['album'].'.jpg';

else
$fn='graphics/artstore/'.$fileinfo[$read]['filename'].'.jpg';

//if fn doesn't exist, create it
if (!file_exists($fn)){
//create image
$img=imagecreatefromstring($ThisFileInfo['comments']['picture'][0]['data']);
imagejpeg($img, $fn);
if($debug==true)
echo'file created: <img src="'.$fn.'" />';
imagedestroy($img);

}
else if($debug==true){
//file already exists, pass fn back for poster usage
echo'file exists: <img src="'.$fn.'" />';
}

//set fn as array item
$fileinfo[$read]['art']=$fn;

}

//END ALBUM ART TESTING

}

//inc counter
$fileinfo['count']=$fileinfo['count']+1; // had ++ and it didn't work
}
else
;//do nothing
}

//else, must be a folder (as determined above), recurse folder
else{

//debug
if($debug==true)echo '^^ DIR<br />';

//capture subfolders in case they are needed
if($parent!='')$fileinfo['folders'].=$parent.'&raquo;'.$read.'|';
else $fileinfo['folders'].=$read.'|';
$fileinfo['folderpaths'].=$directory.'/|';

$fileinfo=recursiveGetSongs($directory.'/'.$read, $fileinfo, $useID3, $getID3, $parent.'/'.$read, $debug, $filtered, $playermode);

}

}

}
closedir($dir); 

return $fileinfo;
}

?>