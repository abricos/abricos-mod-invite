<?php
/**
 * @package Abricos
 * @subpackage Invite
 * @copyright 2013-2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

require_once 'dbquery.php';

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

    public function IsViewRole(){
        if ($this->IsWriteRole()){
            return true;
        }
        return $this->IsRoleEnable(InviteAction::VIEW);
    }

    public function AJAX($d){
        switch ($d->do){
            // case 'invitelistbyuser': return $this->InviteListByUser($d->userid);
        }
        return null;
    }

    public function ToArray($rows, &$ids1 = "", $fnids1 = 'uid', &$ids2 = "", $fnids2 = '', &$ids3 = "", $fnids3 = ''){
        $ret = array();
        while (($row = $this->db->fetch_array($rows))){
            array_push($ret, $row);
            if (is_array($ids1)){
                $ids1[$row[$fnids1]] = $row[$fnids1];
            }
            if (is_array($ids2)){
                $ids2[$row[$fnids2]] = $row[$fnids2];
            }
            if (is_array($ids3)){
                $ids3[$row[$fnids3]] = $row[$fnids3];
            }
        }
        return $ret;
    }

    public function ToArrayId($rows, $field = "id"){
        $ret = array();
        while (($row = $this->db->fetch_array($rows))){
            $ret[$row[$field]] = $row;
        }
        return $ret;
    }

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


    /**
     * Зарегистрировать пользователя по приглашению
     *
     * Если пользователь виртуальный, то его можно будет пригласить позже.
     * Виртаульный пользователь необходим для того, чтобы можно было работать с
     * его учеткой как с реальным пользователем.
     * Допустим, создается список сотрудников компании.
     * Выяснять их существующие емайлы или регить новые - процесс длительный,
     * а работать в системе уже нужно сейчас. Поэтому сначало создается виртуальный
     * пользователь, а уже потом, если необходимо, он будет переводиться в статус реального
     * с формированием пароля и отправкой приглашения.
     *
     * Коды ошибок:
     *    1 - неверный емайл;
     *  2 - не все поля заполнены;
     *  3 - пользователь уже зарегистрирован с таким email;
     *  4 - пользователь который приглашает не указал свое имя и фамилию;
     *  99 - прочая ошибка
     *
     * @param string $modname
     * @param string $email
     * @param string $firstname
     * @param string $lastname
     * @param boolean $isVirtual True-виртуальный пользователь
     */
    public function UserRegister($modname, $email, $firstname, $lastname, $isVirtual = false){
        if (!$this->IsWriteRole()){
            return null;
        }

        if ($isVirtual){ // виртуальному пользователю емайл не нужен
            $email = '';
        }

        $ret = new stdClass();
        $ret->error = 0;

        // приглашающий пользователь должен иметь имя и фамилию
        // иначе как приглашенный пользователь поймет кто его пригласил?
        if (empty($this->user->info['firstname']) || empty($this->user->info['lastname'])){
            $ret->error = 4;
            return $ret;
        }

        $manUser = Abricos::$user->GetManager();
        if (!$isVirtual){
            // приглашение реального пользователя
            $email = strtolower($email);
            if (!$manUser->EmailValidate($email)){
                $ret->error = 1; // емайл указан не верно
                return $ret;
            }
            $uinfo = InviteQuery::UserByEmailInfo($this->db, $email);
            if (!empty($uinfo)){
                $ret->error = 3; // уже есть пользователь с таким емайл
                return $ret;
            }
        }

        $fnmLat = translateruen($firstname);
        $fnmLat = strtoupper(substr($fnmLat, 0, 1)).substr($fnmLat, 1);

        $lnmLat = translateruen($lastname);
        $lnmLat = strtoupper(substr($lnmLat, 0, 1)).substr($lnmLat, 1);

        if (empty($firstname) || empty($lastname) || empty($fnmLat) || empty($lnmLat)){
            $ret->error = 2; // имя и фамилия пользователя неверны
            return $ret;
        }

        // TODO: Использовать эту подборку для генерации логина

        // подборка логина
        $login = substr($fnmLat, 0, 1).$lnmLat; // И+Фамилия
        $uinfo = UserQueryExt::UserByName($this->db, $login);

        if (!empty($uinfo)){ // следующий вариант Имя+Фамилия
            $login = $fnmLat.$lnmLat;
            $uinfo = UserQueryExt::UserByName($this->db, $login);
        }

        if (!empty($uinfo)){ // следующий вариант Фамилия
            $login = $lnmLat;
            $uinfo = UserQueryExt::UserByName($this->db, $login);
        }

        if (!$isVirtual){
            if (!empty($uinfo)){ // уже есть такой логин, может по мылу свободно?
                $arr = explode("@", $email);
                $login = $arr[0];
                $uinfo = UserQueryExt::UserByName($this->db, $login);
            }
        }

        if (!empty($uinfo)){ // следующий вариант  И+Фамилия+номерпользователя
            $cnt = InviteQuery::UserCount($this->db);
            for ($i = $cnt; $i < ($cnt + 300); $i++){
                $login = substr($fnmLat, 0, 1).$lnmLat.$i;
                $uinfo = UserQueryExt::UserByName($this->db, $login);
                if (empty($uinfo)){
                    break;
                }
            }
        }

        if (!empty($uinfo)){ // здаюсь
            $ret->error = 99;
            return $ret;
        }

        // логин подобрали, генерируем пароль
        $password = $this->PasswordGenerate();
        $salt = $manUser->UserCreateSalt();

        $user = array();
        $user["username"] = $login;
        $user["joindate"] = TIMENOW;
        $user["salt"] = $salt;
        $user["password"] = $manUser->UserPasswordCrypt($password, $salt);
        $user["email"] = $email;

        // Добавление пользователя в базу
        $userid = UserQueryExt::UserAppend($this->db, $user, User::UG_REGISTERED, '', false, $isVirtual);
        InviteQuery::UserSetFLName($this->db, $userid, $firstname, $lastname);

        $pubkey = md5(TIMENOW.$login.$password);
        $ret->user = array();
        $ret->user['id'] = $userid;
        $ret->user['login'] = $login;
        $ret->user['password'] = $password;
        $ret->user['pubkey'] = $pubkey;


        $ret->URL = Abricos::$adress->host."/invite/".$pubkey."/".$userid."/".$modname."/?redirect=";

        if (!$isVirtual){
            // добавить инвайт в базу
            InviteQuery::InviteAppend($this->db, $modname, $userid, $this->userid, $pubkey);
        }

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
}
