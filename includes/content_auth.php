<?php
/**
 * @package Abricos
 * @subpackage Sportsman
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$brick = Brick::$builder->brick;

$invite = Abricos::$adress->dir[1];
$userid = Abricos::$adress->dir[2];

Abricos::GetModule('invite')->GetManager();
InviteManager::$instance->AuthByInvite($userid, $invite);

$redirect = Abricos::CleanGPC("g", "redirect", TYPE_STR);
if (empty($redirect)){
	$redirect = "/";
}

$brick->content = Brick::ReplaceVarByData($brick->content, array(
	"url" => $redirect
));

header("Location: ".$redirect);

?>