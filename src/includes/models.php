<?php
/**
 * @package Abricos
 * @subpackage Invite
 * @copyright 2013-2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

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
    protected $_structModule = 'company';
    protected $_structName = 'Invite';
}
