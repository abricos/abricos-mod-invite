<?php
/**
 * @package Abricos
 * @subpackage Invite
 * @copyright 2013-2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
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
