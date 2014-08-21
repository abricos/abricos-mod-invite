<?php
/**
 * @package Abricos
 * @subpackage Invite
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author  Alexander Kuzmin <roosit@abricos.org>
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

?>