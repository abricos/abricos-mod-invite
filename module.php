<?php 
/**
 * @version $Id: module.php 982 2012-10-09 15:32:19Z roosit $
 * @package Abricos
 * @subpackage Invite
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Приглашение пользователей в систему
 */
class InviteModule extends Ab_Module {

	/**
	 * @var InviteModule
	 */
	public static $instance = null;
	
	/**
	 * @var InviteData
	 */
	public $currentInvite = null;
	
	public function __construct(){
		$this->version = "0.1";
		$this->name = "invite";
		$this->takelink = "invite";
		$this->permission = new InvitePermission($this);
		
		InviteModule::$instance = $this;
	}
	
	/**
	 * Получить менеджер
	 *
	 * @return InviteManager
	 */
	public function GetManager(){
		if (is_null($this->_manager)){
			require_once 'includes/manager.php';
			$this->_manager = new InviteManager($this);
		}
		return $this->_manager;
	}
	
}

class InviteData {
	public $user = null;
	public $author = null;
	public $pubkey = 0;
	
	public function __construct($user, $author, $pubkey){
		$this->user = $user;
		$this->author = $author;
		$this->pubkey = $pubkey;
	}
	
	public function GetData(){
		$ret = new stdClass();
		$ret->user = $this->user;
		$ret->author = $this->author;
		$ret->key = $this->pubkey;
		return $ret;
	}
}

class InviteAction {
	const VIEW	= 10;
	const WRITE	= 30;
	const ADMIN	= 50;
}

class InvitePermission extends Ab_UserPermission {
	
	public function InvitePermission(InviteModule $module){
		
		$defRoles = array(
			new Ab_UserRole(InviteAction::VIEW, Ab_UserGroup::GUEST),
			new Ab_UserRole(InviteAction::VIEW, Ab_UserGroup::REGISTERED),
			new Ab_UserRole(InviteAction::VIEW, Ab_UserGroup::ADMIN),
			
			new Ab_UserRole(InviteAction::WRITE, Ab_UserGroup::REGISTERED),
			new Ab_UserRole(InviteAction::WRITE, Ab_UserGroup::ADMIN),
				
			new Ab_UserRole(InviteAction::ADMIN, Ab_UserGroup::ADMIN),
		);
		parent::__construct($module, $defRoles);
	}
	
	public function GetRoles(){
		return array(
			InviteAction::VIEW => $this->CheckAction(InviteAction::VIEW),
			InviteAction::WRITE => $this->CheckAction(InviteAction::WRITE),
			InviteAction::ADMIN => $this->CheckAction(InviteAction::ADMIN)
		);
	}
}

Abricos::ModuleRegister(new InviteModule());

?>