<?php namespace XoopsModules\Xhelp;

/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright    {@link https://xoops.org/ XOOPS Project}
 * @license      {@link http://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2 or later}
 * @package
 * @since
 * @author       XOOPS Development Team
 */

use XoopsModules\Xhelp;

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';


/**
 * class StaffRoleHandler
 */
class StaffRoleHandler extends Xhelp\BaseObjectHandler
{
    public $_idfield = 'roleid';

    /**
     * Name of child class
     *
     * @var string
     * @access  private
     */
    public $classname = StaffRole::class;

    /**
     * DB Table Name
     *
     * @var string
     * @access  private
     */
    public $_dbtable = 'xhelp_staffroles';

    /**
     * Constructor
     *
     * @param \XoopsDatabase $db reference to a xoopsDB object
     */
    public function __construct(\XoopsDatabase $db)
    {
        parent::init($db);
    }

    /**
     * @param int  $uid
     * @param null $roleid
     * @param null $deptid
     * @return array|bool
     */
    public function get($uid, $roleid = null, $deptid = null)
    {
        $crit = new \CriteriaCompo('uid', $uid);
        $crit->add(new \Criteria('roleid', $roleid));
        $crit->add(new \Criteria('deptid', $deptid));

        if (!$role = $this->getObjects($crit)) {
            return false;
        }

        return $role;
    }

    /**
     * @param      $uid
     * @param bool $id_as_key
     * @return array|bool
     */
    public function &getObjectsByStaff($uid, $id_as_key = false)
    {
        $uid  = (int)$uid;
        $crit = new \Criteria('uid', $uid);

        $arr = $this->getObjects($crit, $id_as_key);

        if (0 == count($arr)) {
            $arr = false;
        }

        return $arr;
    }

    /**
     * @param $uid
     * @param $roleid
     * @return bool
     */
    public function staffInRole($uid, $roleid)
    {
        $crit = new \CriteriaCompo('uid', $uid);
        $crit->add(new \Criteria('roleid', $roleid));

        if (!$role = $this->getObjects($crit)) {
            return false;
        }

        return true;
    }

    /**
     * @param $obj
     * @return string
     */
    public function _insertQuery($obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf('INSERT INTO %s (uid, roleid, deptid) VALUES (%u, %u, %u)', $this->_db->prefix($this->_dbtable), $uid, $roleid, $deptid);

        return $sql;
    }

    /**
     * @param $obj
     * @return string
     */
    public function _deleteQuery($obj)
    {
        $sql = sprintf('DELETE FROM %s WHERE uid = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('uid'));

        return $sql;
    }
}   // end of handler class
