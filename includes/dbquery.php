<?php
/**
 * @version $Id: dbquery.php 989 2012-10-10 17:11:31Z roosit $
 * @package Abricos
 * @subpackage Invite
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

class InviteQuery {
	
	public static function UserByInvite(Ab_Database $db, $invite){
		$sql = "
			SELECT 
				u.userid as id,
				u.avatar as avt,
				u.username as unm,
				u.firstname as fnm,
				u.lastname as lnm,
				u.agreement as agr
			FROM ".$db->prefix."invite i
			INNER JOIN 	".$db->prefix."user u ON i.userid=u.userid
			WHERE pubkey='".bkstr($invite)."'
			LIMIT 1
		";
		return $db->query_first($sql);
	}
	
	public static function AuthorByInvite(Ab_Database $db, $invite){
		$sql = "
			SELECT
				u.userid as id,
				u.avatar as avt,
				u.username as unm,
				u.firstname as fnm,
				u.lastname as lnm
			FROM ".$db->prefix."invite i
			INNER JOIN 	".$db->prefix."user u ON i.authorid=u.userid
			WHERE pubkey='".bkstr($invite)."'
			LIMIT 1
		";
		return $db->query_first($sql);
	}
	
	
	public static function UserByEmailInfo(Ab_Database $db, $email){
		$sql = "
			SELECT *
			FROM ".$db->prefix."user
			WHERE email='".bkstr($email)."'
			LIMIT 1
		";
		return $db->query_first($sql);
	}
	
	public static function UserCount(Ab_Database $db){
		$sql = "
			SELECT count(*) as cnt
			FROM ".$db->prefix."user
			LIMIT 1
		";
		$row = $db->query_first($sql);
		return $row['cnt'];
	}
	
	public static function InviteAppend(Ab_Database $db, $modname, $userid, $authorid, $pubkey){
		$sql = "
			INSERT INTO ".$db->prefix."invite (userid, authorid, module, pubkey, dateline) VALUES (
				".bkint($userid).",
				".bkint($authorid).",
				'".bkstr($modname)."',
				'".bkstr($pubkey)."',
				".TIMENOW."
			)
		";
		$db->query_write($sql);
	}
	
	public static function InviteRemove(Ab_Database $db, $invite){
		$sql = "
			UPDATE ".$db->prefix."invite
			SET pubkey=''
			WHERE pubkey='".$invite."'
			LIMIT 1
		";
		$db->query_write($sql);
	}
	
	public static function UserSetFLName(Ab_Database $db, $userid, $fname, $lname){
		$sql = "
			UPDATE ".$db->prefix."user
			SET 
				firstname='".$fname."',
				lastname='".$lname."'
			WHERE userid=".bkint($userid)."
			LIMIT 1
		";
		$db->query_write($sql);
	}
}

?>