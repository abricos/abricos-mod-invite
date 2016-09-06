<?php
/**
 * @package Abricos
 * @subpackage Invite
 * @copyright 2013-2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Class InviteQuery
 */
class InviteQuery {

    public static function UserByEmail(Ab_Database $db, $email){
        $sql = "
			SELECT *
			FROM ".$db->prefix."user
			WHERE email='".bkstr($email)."'
			LIMIT 1
		";
        return $db->query_first($sql);
    }

    public static function UserByLogin(Ab_Database $db, $login){
        $sql = "
			SELECT *
			FROM ".$db->prefix."user
			WHERE username='".bkstr($login)."'
			LIMIT 1
		";
        return $db->query_first($sql);
    }


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

    public static function InviteUse(Ab_Database $db, $userid, $invite){
        $sql = "
			UPDATE ".$db->prefix."invite
			SET dateuse=".TIMENOW."
			WHERE userid=".bkint($userid)." AND pubkey='".bkstr($invite)."'
			LIMIT 1
		";
        $db->query_write($sql);
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

    /**
     * Удалить старые и не используемые инвайты
     *
     * @param Ab_Database $db
     * @param unknown_type $invite
     */
    public static function InviteClean(Ab_Database $db){
        $sql = "
			UPDATE ".$db->prefix."invite
			SET pubkey=''
			WHERE 
				(dateuse=0 AND dateline<".(TIMENOW - 60 * 60 * 24 * 14).") OR
				(dateuse>0 AND dateuse<".(TIMENOW - 60 * 60 * 24).")
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
