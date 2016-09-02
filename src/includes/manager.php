<?php
/**
 * @package Abricos
 * @subpackage Invite
 * @copyright 2013-2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

class InviteManager extends Ab_ModuleManager {

    public function IsAdminRole(){
        return $this->IsRoleEnable(InviteAction::ADMIN);
    }

    public function IsWriteRole(){
        if ($this->IsAdminRole()){
            return true;
        }
        return $this->IsRoleEnable(InviteAction::WRITE);
    }

    public function AJAX($d){
        return $this->GetApp()->AJAX($d);
    }

}
