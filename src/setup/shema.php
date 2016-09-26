<?php
/**
 * @package Abricos
 * @subpackage Invite
 * @copyright 2013-2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$charset = "CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'";
$updateManager = Ab_UpdateManager::$current;
$db = Abricos::$db;
$pfx = $db->prefix;

if ($updateManager->isInstall()){
    Abricos::GetModule('invite')->permission->Install();

    $db->query_write("
		CREATE TABLE IF NOT EXISTS ".$pfx."invite (
            inviteid int(10) UNSIGNED NOT NULL auto_increment,
            
			userid int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Приглашенный',
			authorid int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Отправитель',
			
            ownerModule VARCHAR(50) NOT NULL COMMENT 'Owner Module Name',
            ownerType VARCHAR(16) NOT NULL DEFAULT '' COMMENT 'Owner Type',
            ownerid int(10) UNSIGNED NOT NULL COMMENT 'Owner ID',
			
			pubkey varchar(32) NOT NULL DEFAULT '' COMMENT 'Идентификатор пригласительного',
			
			dateline int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Дата создания',
			dateuse int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Дата использования',
			deldate int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Дата удаления',
			
            PRIMARY KEY (inviteid),
			KEY (authorid)
		)".$charset
    );
}
