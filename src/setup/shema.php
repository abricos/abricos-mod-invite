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
			`userid` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Пригласили пользователя',
			`authorid` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Автор пригласительного',
			
			`module` varchar(50) NOT NULL DEFAULT '' COMMENT 'Модуль инициатор',
			`pubkey` varchar(32) NOT NULL DEFAULT '' COMMENT 'Идентификатор пригласительного',
			
			`dateline` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Дата создания',
			`dateuse` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Дата использования',
			
			UNIQUE KEY  (`userid`),
			KEY (`authorid`)
		)".$charset
    );
}
