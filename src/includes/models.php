<?php
/**
 * @package Abricos
 * @subpackage Invite
 * @copyright 2013-2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Interface InviteUserSearchVars
 *
 * @property string $loginOrEmail
 * @property InviteOwner $owner
 */
interface InviteUserSearchVars {
}

/**
 * Class InviteUserSearch
 *
 * @property int $userid
 * @property string $email
 * @property string $login
 *
 * @property InviteUserSearchVars $vars
 */
class InviteUserSearch extends AbricosResponse {

    const CODE_OK = 1;
    const CODE_MYNAME_IS_BAD = 2;
    const CODE_INPUT_IS_EMPTY = 4;
    const CODE_EMAIL_VALID = 8;
    const CODE_LOGIN_VALID = 16;
    const CODE_EXISTS = 32;
    const CODE_ADD_ALLOWED = 64;
    const CODE_ADD_DENIED = 128;
    const CODE_NOT_EXISTS = 256;
    const CODE_INVITE_ALLOWED = 512;
    const CODE_INVITE_DENIED = 1024;

    protected $_structModule = 'invite';
    protected $_structName = 'UserSearch';
}

/**
 * Interface InviteCreateVars
 *
 * @property string $firstName
 * @property string $lastName
 */
interface InviteCreateVars {
}

/**
 * Class InviteCreate
 *
 * @property InviteCreateVars $vars
 * @property int $userid
 * @property string $email
 * @property string $firstName
 * @property string $lastName
 * @property string $pubkey
 */
class InviteCreate extends AbricosResponse {

    const CODE_OK = 1;

    protected $_structModule = 'invite';
    protected $_structName = 'Create';
}

/**
 * Class Invite
 *
 * @property int $authorid
 * @property string $module Owner Module
 * @property string $pubkey
 * @property int $date Create Date
 * @property int $use Use Date
 */
class Invite extends AbricosModel {
    protected $_structModule = 'invite';
    protected $_structName = 'Invite';
}

/**
 * Class InviteOwner
 *
 * @property string $module
 * @property string $type
 * @property int $ownerid
 */
class InviteOwner extends AbricosModel {
    protected $_structModule = 'invite';
    protected $_structName = 'Owner';
}
