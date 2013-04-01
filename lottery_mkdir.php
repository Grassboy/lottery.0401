<?
//改架構時有用到的 php code 快速產生 100 個資料夾 & 搬檔案
include("../include/dir.php");
for($i = 0; $i < 100; $i++){
	mkdir("lottery/".$i);
	mkdir("lottery/data/".$i);
	mkdir("lottery/pic_big/".$i);
	mkdir("lottery/pic_med/".$i);
}
function getSNdir($file){
	preg_match("/[^\.]*\.([\d]+)\-/", $file, $match);
	if($match){
		$dir = (intval($match[1]) % 100);
		return $dir."/".$file;
	} else {
		return $file;
	}
}
function getNickDir($file){
	$dir = hexdec(substr(md5($file),0,3)) % 100;
	return $dir."/".$file;
}
function getIPdir($filename){
	$ip = explode(".", $filename);
	$dir = (intval($ip[3]) % 100);
	return $dir."/".$filename;
}
$files = get_file_by_ext("lottery/", array(".jpg"));
foreach($files as $file){
	rename("lottery/".$file, "lottery/".getIPdir($file));
}
$files = get_file_by_ext("lottery/data/", array(".txt"));
foreach($files as $file){
	rename("lottery/data/".$file, "lottery/data/".getNickDir($file));
}
$files = get_file_by_ext("lottery/pic_big/", array(".jpg"));
foreach($files as $file){
	rename("lottery/pic_big/".$file, "lottery/pic_big/".getSNdir($file));
}
$files = get_file_by_ext("lottery/pic_med/", array(".gif"));
foreach($files as $file){
	rename("lottery/pic_med/".$file, "lottery/pic_med/".getSNdir($file));
}

?>
