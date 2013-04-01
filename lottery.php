<?
include("../include/debug.php");
include("../include/dir.php");
header("content-type: image/jpeg");
header("Expires: Tue, 1 Apr 2014 05:00:00 GMT");
//愚人節結束 start
header("location: http://i.imgur.com/e8UsU7c.jpg");
exit();
//愚人節結束 end

//echo file_get_contents("plurk_lottery.jpg");
$base_dir = "./lottery/";
$refer = $_SERVER["HTTP_REFERER"];
preg_match("/plurk\.com\/([a-zA-Z0-9\_]+)[^\/]*$/", $refer, $match);
if($match){
	$nick_name = $match[1];
} else {
	//使用 cache 的圖
	echo file_get_contents("plurk_lottery.cache.jpg");
	exit();
}
if($nick_name == "grassboy"){
	echo file_get_contents("plurk_lottery.cache.jpg");
	exit();
}
function getNickDir($file){
	$dir = hexdec(substr(md5($file),0,3)) % 100;
	return $dir."/".$file;
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
function getIPdir($filename){
	$ip = explode(".", $filename);
	$dir = (intval($ip[3]) % 100);
	return $dir."/".$filename;
}
$cache_file = $base_dir.getIPdir($_SERVER["REMOTE_ADDR"]).".jpg";
if(file_exists($cache_file)){
	echo file_get_contents($cache_file);
	exit();
}
// http://avatars.plurk.com/4104895-big50.jpg
// http://avatars.plurk.com/4891790-medium14.gif
$log_file = $base_dir."log.txt";
$tmp = "|".implode("|", explode("\r\n", file_get_contents($log_file)))."|";
$tmp = preg_replace("/[\s]/","", $tmp);
$tmp = preg_replace("/[\|]{2,}/","|", $tmp);
$tmp = str_replace("|".$nick_name."|", "|", $tmp).$nick_name;
$tmp_array = explode("|", substr($tmp, 1));
$tmp = implode("\r\n", $tmp_array);
file_put_contents($log_file, $tmp);
$user_array = array_slice($tmp_array, -6);

$img = imagecreatefromjpeg("plurk_lottery.jpg");
function getUserData($nick_name){
	global $base_dir;
	$user_info = $base_dir."data/".getNickDir($nick_name.".txt");
	if(!file_exists($user_info)){
		preg_match("/var GLOBAL = ([^\r\n]+)/", file_get_contents("http://www.plurk.com/".$nick_name), $match);
		// json_decode('{"page_user": {"verified_account": false, "page_title": "!!yobssarG\u5152\u8349\u662f\u6211", "uid": 4104895, "full_name": "\u5433\u5609\u7965", "name_color": "0A9C17", "timezone": null, "id": 4104895, "num_of_fans": 326, "display_name": "\u5c0f\u80d6\u5b50\uff0e\u8349\u5152", "num_of_friends": 113, "use_dark_icons": false, "theme": "boooring-theme", "date_of_birth": new Date("Sun, 02 Sep 1984 00:01:00 GMT"), "theme_db": "dark-theme", "location": "\u65b0\u5929\u9f8d\u570b\uff0e\u6c38\u548c\u5340, Taiwan", "theme_fg": "marine-theme", "recruited": 17, "bday_privacy": 2, "creature": 12, "default_lang": "tr_ch", "has_custom_theme": true, "relationship": "single", "dateformat": 0, "has_profile_image": 1, "karma_change": -0.05, "email_confirmed": true, "nick_name": "grassboy", "gender": 1, "avatar": 50, "karma": 128.86}, "session_user": null}');
		$json = $match[1];
		$result = array();
		preg_match("/\"full_name\": (\"[^\"]+\")/", $json, $match);
		$result["full_name"] = json_decode($match[1]);
		preg_match("/\"display_name\": (\"[^\"]+\")/", $json, $match);
		$result["display_name"] = json_decode($match[1]);
		preg_match("/\"nick_name\": (\"[^\"]+\")/", $json, $match);
		$result["nick_name"] = json_decode($match[1]);
		preg_match("/\"avatar\": ([\d]+)/", $json, $match);
		$result["avatar"] = json_decode($match[1]);
		preg_match("/\"uid\": ([\d]+)/", $json, $match);
		$result["uid"] = json_decode($match[1]);
		file_put_contents($user_info, json_encode($result));
	} else {
		$result = json_decode(file_get_contents($user_info), true);
	}
	return $result;
}
function getUserAvatar($nick_name, $uid, $avatar, $isBig){
	global $base_dir;
	$filename_big = sprintf("%d-big%d.jpg", $uid, $avatar);
	$filename_med = sprintf("%d-medium%d.gif", $uid, $avatar);
	$filename_big_local = $base_dir."pic_big/".getSNdir($nick_name.".".$filename_big);
	$filename_med_local = $base_dir."pic_med/".getSNdir($nick_name.".".$filename_med);
	$filename_big_remote = "http://avatars.plurk.com/".$filename_big;
	$filename_med_remote = "http://avatars.plurk.com/".$filename_med;
	if(file_exists($filename_big_local) && file_exists($filename_med_local)){
		if($isBig){
			return imagecreatefromjpeg($filename_big_local);
		} else {
			return imagecreatefromgif($filename_med_local);
		}
	} else {
		$img_big = imagecreatefromstring(file_get_contents($filename_big_remote));
		$img_med = imagecreatefromstring(file_get_contents($filename_med_remote));
		imagejpeg($img_big, $filename_big_local);
		imagegif($img_med, $filename_med_local);
		if($isBig){
			return $img_big;
		} else {
			return $img_med;
		}
	}
}
$font = "liheipro.ttf";
$black = ImageColorAllocate($img, 0,0,0);

$result = getUserData($user_array[5]);
$thumb = getUserAvatar($result["nick_name"], $result["uid"], $result["avatar"], true);
imagecopyresized($img, $thumb, 58, 305, 0, 0, 195, 195, 195, 195);
ImageTTFText($img, 36, 0, 268, 382, $black, $font, $result["nick_name"]);
ImageTTFText($img, 24, 0, 268, 422, $black, $font, $result["display_name"]);
ImageTTFText($img, 20, 0, 268, 452, $black, $font, $result["full_name"]);

$result = getUserData($user_array[4]);
$thumb = getUserAvatar($result["nick_name"], $result["uid"], $result["avatar"], false);
imagecopyresized($img, $thumb, 57, 135, 0, 0, 45, 45, 45, 45);
ImageTTFText($img, 18, 0, 112, 160, $black, $font, $result["nick_name"]);

$result = getUserData($user_array[3]);
$thumb = getUserAvatar($result["nick_name"], $result["uid"], $result["avatar"], false);
imagecopyresized($img, $thumb, 293, 135, 0, 0, 45, 45, 45, 45);
ImageTTFText($img, 18, 0, 348, 160, $black, $font, $result["nick_name"]);

$result = getUserData($user_array[2]);
$thumb = getUserAvatar($result["nick_name"], $result["uid"], $result["avatar"], false);
imagecopyresized($img, $thumb, 529, 135, 0, 0, 45, 45, 45, 45);
ImageTTFText($img, 18, 0, 584, 160, $black, $font, $result["nick_name"]);

$result = getUserData($user_array[1]);
$thumb = getUserAvatar($result["nick_name"], $result["uid"], $result["avatar"], false);
imagecopyresized($img, $thumb, 175, 189, 0, 0, 45, 45, 45, 45);
ImageTTFText($img, 18, 0, 230, 214, $black, $font, $result["nick_name"]);

$result = getUserData($user_array[0]);
$thumb = getUserAvatar($result["nick_name"], $result["uid"], $result["avatar"], false);
imagecopyresized($img, $thumb, 411, 189, 0, 0, 45, 45, 45, 45);
ImageTTFText($img, 18, 0, 466, 214, $black, $font, $result["nick_name"]);


if(rand(0,10)==5) imagejpeg($img, "plurk_lottery.cache.jpg");
imagejpeg($img, $cache_file);
imagejpeg($img);
?>

