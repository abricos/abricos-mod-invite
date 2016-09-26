<?php
/**
 * @package Abricos
 * @subpackage Invite
 * @copyright 2013-2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Class InviteManager
 *
 * @property InviteManager $manager
 */
class InviteApp extends AbricosApplication {

    protected function GetClasses(){
        return array(
            'Owner' => 'InviteOwner',
            'Invite' => 'Invite',
            'UserSearch' => 'InviteUserSearch',
            'Create' => 'InviteCreate',
        );
    }

    protected function GetStructures(){
        return 'Owner,Invite,UserSearch';
    }

    public function ResponseToJSON($d){
        switch ($d->do){
            case 'userSearch':
                return $this->UserSearchToJSON($d->data);
        }
        return null;
    }

    public function IsAdminRole(){
        return $this->manager->IsAdminRole();
    }

    public function IsWriteRole(){
        return $this->manager->IsWriteRole();
    }

    private function OwnerAppFunctionExist($module, $fn){
        $ownerApp = Abricos::GetApp($module);
        if (empty($ownerApp)){
            return false;
        }
        if (!method_exists($ownerApp, $fn)){
            return false;
        }
        return true;
    }

    public function UserSearchToJSON($d){
        $res = $this->UserSearch($d);
        return $this->ResultToJSON('userSearch', $res);
    }

    /**
     * @param $d
     * @return InviteUserSearch
     */
    public function UserSearch($d){
        /** @var InviteUserSearch $ret */
        $ret = $this->InstanceClass('UserSearch', $d);

        if (!$this->IsWriteRole()){
            return $ret->SetError(AbricosResponse::ERR_FORBIDDEN);
        }

        $owner = $ret->vars->owner;
        if (!$this->OwnerAppFunctionExist($owner->module, 'Invite_IsUserSearch')){
            return AbricosResponse::ERR_BAD_REQUEST;
        }

        $ownerApp = Abricos::GetApp($owner->module);
        if (!$ownerApp->Invite_IsUserSearch($ret->vars)){
            return $ret->SetError(AbricosResponse::ERR_BAD_REQUEST);
        }

        if (empty(Abricos::$user->firstname) || empty(Abricos::$user->lastname)){
            return $ret->SetError(
                AbricosResponse::ERR_BAD_REQUEST,
                InviteUserSearch::CODE_MYNAME_IS_BAD
            );
        }

        $loginOrEmail = $ret->vars->loginOrEmail;

        if (empty($loginOrEmail)){
            return $ret->SetError(
                AbricosResponse::ERR_BAD_REQUEST,
                InviteUserSearch::CODE_INPUT_IS_EMPTY
            );
        }

        $ret->AddCode(InviteUserSearch::CODE_OK);
        $ret->userid = 0;

        if (UserManager::EmailValidate($loginOrEmail)){
            $ret->AddCode(InviteUserSearch::CODE_EMAIL_VALID);

            $ret->email = $loginOrEmail;

            $row = InviteQuery::UserByEmail($this->db, $loginOrEmail);

            if (!empty($row)){
                $ret->userid = intval($row['userid']);
            } else {
                $ret->AddCode(InviteUserSearch::CODE_INVITE_ALLOWED);
            }
        } else {
            $ret->AddCode(InviteUserSearch::CODE_LOGIN_VALID);

            $ret->login = $loginOrEmail;

            $row = InviteQuery::UserByLogin($this->db, $loginOrEmail);
            if (!empty($row)){
                $ret->userid = intval($row['userid']);
            }
        }

        if ($ret->userid > 0){
            $ret->AddCode(InviteUserSearch::CODE_EXISTS);

            /** @var UProfileManager $uprofileManager */
            $uprofileManager = Abricos::GetModuleManager('uprofile');

            if (!$uprofileManager->UserPublicityCheck($ret->userid)){
                $ret->AddCode(InviteUserSearch::CODE_ADD_DENIED);
            } else {
                $ret->AddCode(InviteUserSearch::CODE_ADD_ALLOWED);
            }
        } else {
            $ret->AddCode(InviteUserSearch::CODE_NOT_EXISTS);
        }

        return $ret;
    }

    public function Create(InviteUserSearch $rUS, $d){
        /** @var InviteCreate $ret */
        $ret = $this->InstanceClass('Create', $d);

        if (!$rUS->IsSetCode(InviteUserSearch::CODE_INVITE_ALLOWED)){
            return $ret->SetError(AbricosResponse::ERR_BAD_REQUEST);
        }

        $vars = $ret->vars;

        $firstName = ucfirst(strtolower($vars->firstName));
        $lastName = ucfirst(strtolower($vars->lastName));

        $latFirstName = ucfirst(translateruen($firstName));
        $latLastName = ucfirst(translateruen($lastName));

        $email = strtolower($rUS->email);

        if (empty($firstName) || empty($lastName) || empty ($latFirstName) || empty($latLastName)){
            return $ret->SetError(
                AbricosResponse::ERR_BAD_REQUEST,
                InviteCreate::CODE_USER_FULLNAME_EMPTY
            );
        }

        $login = substr($latFirstName, 0, 1).$latLastName; // И+Фамилия
        $info = InviteQuery::UserByLogin($this->db, $login);

        if (!empty($info)){ // следующий вариант Имя+Фамилия
            $login = $latFirstName.$latLastName;
            $info = InviteQuery::UserByLogin($this->db, $login);
        }

        if (!empty($info)){ // уже есть такой логин, может по мылу свободно?
            $arr = explode("@", $email);
            $login = $arr[0];
            $info = InviteQuery::UserByLogin($this->db, $login);
        }

        if (!empty($info)){ // следующий вариант И+Фамилия+номерпользователя
            $cnt = InviteQuery::UserCount($this->db);
            for ($i = $cnt; $i < ($cnt + 300); $i++){
                $login = substr($latFirstName, 0, 1).$latLastName.$i;
                $info = InviteQuery::UserByLogin($this->db, $login);
                if (empty($info)){
                    break;
                }
            }
        }

        if (!empty($info)){ // сдаюсь
            return $ret->SetError(AbricosResponse::ERR_BAD_REQUEST);
        }

        $password = $this->PasswordGenerate();

        /** @var UserManager $userManager */
        $userManager = Abricos::GetModuleManager('user');

        $userManager->RolesDisable();
        $userRegResult = $userManager->GetRegistrationManager()->Register($login, $password, $email, false, false);
        $userManager->RolesEnable();

        if (is_integer($userRegResult)){
            return $ret->SetError(AbricosResponse::ERR_SERVER_ERROR);
        }

        $ret->AddCode(InviteCreate::CODE_OK);

        $ret->userid = $userRegResult->userid;
        $ret->firstName = $firstName;
        $ret->lastName = $lastName;
        $ret->email = $email;
        $ret->pubkey = md5(TIMENOW.$login.$password.$ret->userid);

        InviteQuery::InviteAppend($this->db, $rUS, $ret);
        InviteQuery::UserSetFullName($this->db, $ret);

        return $ret;
    }

    public function PasswordGenerate(){
        $word1 = array(
            'b',
            'c',
            'd',
            'f',
            'g',
            'h',
            'k',
            'l',
            'm',
            'n',
            'p',
            'r',
            's',
            't',
            'v',
            'x',
            'z',
            'B',
            'C',
            'D',
            'F',
            'G',
            'H',
            'K',
            'L',
            'M',
            'N',
            'P',
            'R',
            'S',
            'T',
            'V',
            'X',
            'Z'
        );
        $word2 = array(
            'a',
            'e',
            'i',
            'o',
            'u',
            'y',
            'A',
            'E',
            'I',
            'O',
            'U',
            'Y'
        );

        $pass = "";
        $flag = false;
        for ($i = 0; $i < 11; $i++){
            if ($i >= 5 && $i < 8){
                $flag = false;
                $pass .= rand(0, 9);
            } else {
                $arr = !$flag ? $word1 : $word2;
                $flag = !$flag;
                $index = rand(0, count($arr) - 1);
                $pass .= $arr[$index];
            }
        }
        return $pass;
    }


    /* * * * * * * * * * * OLD FUNCTIONS * * * * * * * * * */

    public function UserByInvite($invite){
        if (!$this->IsViewRole()){
            return null;
        }

        $user = InviteQuery::UserByInvite($this->db, $invite);
        if (!empty($user)){
            $author = InviteQuery::AuthorByInvite($this->db, $invite);

            InviteModule::$instance->currentInvite = new InviteData($user, $author, $invite);
        }

        return $user;
    }

    public function AuthByInvite($userid, $invite){
        if (!$this->IsViewRole()){
            return false;
        }

        InviteQuery::InviteClean($this->db);

        $row = InviteQuery::UserByInvite($this->db, $invite);
        if (empty($row) || $row['id'] != $userid){
            sleep(5);
            return false;
        }

        InviteQuery::InviteUse($this->db, $userid, $invite);

        $userMan = Abricos::$user->GetManager();

        $user = UserQuery::User($this->db, $row['id']);

        $userMan->LoginMethod($user);

        return true;
    }
}
