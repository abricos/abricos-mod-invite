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
 */
interface InviteUserSearchVars {
}

/**
 * Interface InviteUserSearchCodes
 *
 * @property int $OK
 * @property int $INPUT_IS_EMPTY
 * @property int $EMAIL_VALID
 * @property int $LOGIN_VALID
 * @property int $EXISTS
 * @property int $ADD_ALLOWED
 * @property int $ADD_DENIED
 * @property int $NOT_EXISTS
 * @property int $INVITE_ALLOWED
 * @property int $INVITE_DENIED
 */
interface InviteUserSearchCodes {
}

/**
 * Class InviteUserSearch
 *
 * @property int $userid
 * @property string $email
 * @property string $login
 *
 * @property InviteUserSearchVars $vars
 * @property InviteUserSearchCodes $codes
 */
class InviteUserSearch extends AbricosResponse {
    /**
     * Input var `loginOrEmail` is empty
     */
    const CODE_VAR_EMPTY = 1;

    protected $_structModule = 'invite';
    protected $_structName = 'UserSearch';
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
