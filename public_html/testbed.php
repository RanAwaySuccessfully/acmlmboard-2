<?php
require 'lib/common.php';

if (!has_perm('use-test-bed')) {
	pageheader("Nope");
	no_perm();
}

pageheader("Sandbox");

$pagebar = array();
$pagebar['title'] = 'The Sandbox of Champions';

if ((isset($_GET['user_id']) || isset($_GET['group_id'])) && (isset($_GET['forum_id']) || isset($_GET['cat_id'])) && isset($_GET['act']) && isset($_GET['type'])) {
		$xt = 0;
		$xx = 0;
		$xvalue = 0;
		$bindvalue = 0;
		if (isset($_GET['cat_id'])) {
			$bindvalue = $_GET['cat_id'];
			$xt = 'cat';
			$btable = 'categories';
			$bname = 'title';
		}
		else if (isset($_GET['forum_id'])) {			
			$bindvalue = $_GET['forum_id'];
			$xt = 'forum';
                        $btable = 'forums';
                        $bname = 'title';

		}
		if (isset($_GET['user_id'])) {
			$xvalue = $_GET['user_id'];
			$xx = 'user';
                        $xtable = 'users';
                        $xname = 'name';
		}
		else if (isset($_GET['group_id'])) {
			$xvalue = $_GET['group_id'];
			$xx = 'group';
                        $xtable = 'group';
                        $xname = 'title';
		}

		$xdisplay = $sql->resultp("SELECT $xname FROM $xtable WHERE id=?",array($xvalue));
                $bdisplay = $sql->resultp("SELECT $bname FROM $btable WHERE id=?",array($bindvalue));


		$modpermst['forum']['mod'] = array(
			'edit-forum-thread',
			'delete-forum-thread',
			'edit-forum-post',
			'delete-forum-post',
			'view-forum-post-history');
		$modpermst['forum']['part'] = array(
			'view-private-forum',
			'create-private-forum-post',
			'create-private-forum-thread'
		);
		$modpermst['cat']['part'] = array(
			'view-private-category'
		);

		$modperms = $modpermst[$xt][$_GET['type']];
	if ($modperms && $xvalue) {

	$modpermstring = '(';
	$c = 0;
	foreach ($modperms as $v) {
		if ($c > 0) $modpermstring .=",";
		$modpermstring .= "'".$v."'";
		$c++;
	}
	$modpermstring .= ')';
	$msgtext = "";
	if ($_GET['act'] == 'revoke' || $_GET['act'] == 'grant') {

		$sql->prepare("
		
		DELETE FROM x_perm 

		WHERE x_id=? AND x_type=? AND bindvalue=? AND 
		perm_id IN $modpermstring ;",
			array($xvalue,$xx,$bindvalue)
			);
		$msgtext .= "Removing $xx $xvalue ($xdisplay) current ".$_GET['type']." rights for $xt id ".$bindvalue." ($bdisplay)..\n";
	}

	if ($_GET['act'] == 'grant') {
		$msgtext .= "Assigning $xx $xvalue ($xdisplay) ".$_GET['type']." rights for $xt id ".$bindvalue." ($bdisplay)..\n";
		foreach ($modperms as $v) {
			$sql->prepare(
				"INSERT INTO x_perm (x_id,x_type,perm_id,permbind_id,bindvalue,`revoke`) ".
				"VALUES (?,?,?,?,?,0) ",
				array($xvalue,$xx,$v,$bindtype,$bindvalue));
		}
	}

	}
}


else {


$uid = $_GET['user'];
checknumeric($uid);
if (!$uid) $uid = $loguser['id'];


$user = $sql->fetchp("SELECT * FROM users WHERE id=?",array($uid));

$msgtext = "User id $uid = ".userdisp($user)."\n";

$msgtext .= "User Primary Group:\n";
$gid = $user['group_id'];
$msgtext.= "$gid ";
while ($gid = parent_group_for_group($gid)) {
	$msgtext.= "-> $gid ";
}
$msgtext.= "\n";

$msgtext .= "User Secondary Groups:\n";

$groups = secondary_groups_for_user($uid);

foreach ($groups as $gid) {
	$msgtext.= "$gid ";
	while ($gid = parent_group_for_group($gid)) {
		$msgtext.= "-> $gid ";
	}
	$msgtext.= "\n";
}

$permset = permset_for_user($uid);

$msgtext .= "User permissions:\n";
foreach ($permset as $k => $v) {
	$msgtext .= "$k -> ".(($v['revoke'])?'!':'').$v['id'].'('.$v['bindvalue'].") = ".title_for_perm($v['id'])." inherited from ".$v['xtype']."(".$v['xid'].")\n";
}

$msgtext .=" CAN WE CAPTURE SPRITES?!?!\n";
$cansprite = has_perm('capture-sprites');
$msgtext .= (($cansprite)?"Yup":"Nope!") . "\n";

$msgtext .="\n\n COOL show me your badges!\n";

//HOSTILE DEBUGGING echo 'query fourm<br>';
$forums = $sql->prepare("SELECT id,title FROM forums");

//HOSTILE DEBUGGING echo 'fetch fourm<br>';
while ($forum = mysql_fetch_array($forums)) {
	//HOSTILE DEBUGGING echo 'check access fo forum '.$forum['id'].'<br>';
	$access = can_view_forum($forum['id']);
//HOSTILE DEBUGGING echo 'goa <br>';
	$msgtext .= "Got Access to ".$forum['id']." ".$forum['title']."? => ".($access ? "Hai!" : "hahaha.. no.")."\n";
}

$msgtext .="\n\n Alright! show me your moves!\n";

//HOSTILE DEBUGGING echo 'query fourm<br>';
$cats = $sql->prepare("SELECT id,title FROM categories");

//HOSTILE DEBUGGING echo 'fetch fourm<br>';
while ($cat = mysql_fetch_array($cats)) {
	//HOSTILE DEBUGGING echo 'check access fo forum '.$forum['id'].'<br>';
	$access = can_view_cat($cat['id']);
//HOSTILE DEBUGGING echo 'goa <br>';
	$msgtext .= "Got Access to ".$cat['id']." ".$cat['title']."? => ".($access ? "Mmhmm." : "no dice there..")."\n";
}


}

$pagebar['message'] = "<pre>$msgtext</pre>";

RenderPageBar($pagebar);

pagefooter();
?>
