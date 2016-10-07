<?php
/**
 * @package Abricos
 * @subpackage Invite
 * @copyright 2013-2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Приглашение пользователей в систему
 */
class InviteModule extends Ab_Module {

    public function __construct(){
        $this->version = "0.1.0";
        $this->name = "invite";
        $this->takelink = "invite";
        $this->permission = new InvitePermission($this);
    }
}

class InviteAction {
    const WRITE = 30;
    const ADMIN = 50;
}

class InvitePermission extends Ab_UserPermission {

    public function __construct(InviteModule $module){
        $defRoles = array(
            new Ab_UserRole(InviteAction::WRITE, Ab_UserGroup::REGISTERED),
            new Ab_UserRole(InviteAction::WRITE, Ab_UserGroup::ADMIN),

            new Ab_UserRole(InviteAction::ADMIN, Ab_UserGroup::ADMIN),
        );
        parent::__construct($module, $defRoles);
    }

    public function GetRoles(){
        return array(
            InviteAction::WRITE => $this->CheckAction(InviteAction::WRITE),
            InviteAction::ADMIN => $this->CheckAction(InviteAction::ADMIN)
        );
    }
}

Abricos::ModuleRegister(new InviteModule());
