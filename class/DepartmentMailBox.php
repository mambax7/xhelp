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
// require_once XHELP_CLASS_PATH . '/mailbox.php';
// require_once XHELP_CLASS_PATH . '/mailboxPOP3.php';

/**
 * Xhelp\DepartmentMailBox class
 *
 * @author  Nazar Aziz <nazar@panthersoftware.com>
 * @access  public
 * @package xhelp
 */
class DepartmentMailBox extends \XoopsObject
{
    public $_mBox;
//    public $_errors;
    public $_msgCount;
    public $_curMsg;

    /**
     * Class Constructor
     *
     * @param mixed $id ID of Mailbox or array containing mailbox info
     * @access public
     */
    public function __construct($id = null)
    {
        $this->initVar('id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('emailaddress', XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('departmentid', XOBJ_DTYPE_INT, null, true);
        $this->initVar('server', XOBJ_DTYPE_TXTBOX, null, false, 50);
        $this->initVar('serverport', XOBJ_DTYPE_INT, null, false);
        $this->initVar('username', XOBJ_DTYPE_TXTBOX, null, false, 50);
        $this->initVar('password', XOBJ_DTYPE_TXTBOX, null, false, 50);
        $this->initVar('priority', XOBJ_DTYPE_INT, null, false);
        $this->initVar('mboxtype', XOBJ_DTYPE_INT, _XHELP_MAILBOXTYPE_POP3, false);
        $this->initVar('active', XOBJ_DTYPE_INT, true, true);

        if (null !== $id) {
            if (is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
        $this->_errors = [];
    }

    /**
     * Connect to Mailbox
     *
     * @return bool True if connected, False on Errors
     * @access public
     */
    public function connect()
    {
        //Create an instance of the Proper Xhelp\MailBox object
        if (null === $this->_mBox) {
            if (!$this->_mBox = $this->_getMailBox($this->getVar('mboxtype'))) {
                $this->setErrors(_XHELP_MBOX_INV_BOXTYPE);

                return false;
            }
        }
        if (!$this->_mBox->connect($this->getVar('server'), $this->getVar('serverport'))) {
            $this->setErrors(_XHELP_MAILEVENT_DESC0);

            return false;
        }

        if (!$this->_mBox->login($this->getVar('username'), $this->getVar('password'))) {
            $this->setErrors(_XHELP_MBOX_ERR_LOGIN);

            return false;
        }
        //Reset Message Pointer/Message Count
        unset($this->_msgCount);
        $this->_curMsg = 0;

        return true;
    }

    /**
     * @return mixed
     */
    public function disconnect()
    {
        return $this->_mBox->disconnect();
    }

    /**
     * @return bool
     */
    public function hasMessages()
    {
        return ($this->messageCount() > 0);
    }

    /**
     * @return array|bool
     */
    public function &getMessage()
    {
        $msg = [];
        $this->_curMsg++;
        if ($this->_curMsg > $this->_msgCount) {
            return false;
        }
        $msg['index'] = $this->_curMsg;
        //$msg['headers'] = $this->_mBox->getHeaders($this->_curMsg);
        $msg['msg'] = $this->_mBox->getMsg($this->_curMsg);

        //$msg['body']     = $this->_mBox->getBody($this->_curMsg);
        return $msg;
    }

    /**
     * @return mixed
     */
    public function messageCount()
    {
        if (null === $this->_msgCount) {
            $this->_msgCount = $this->_mBox->messageCount();
        }

        return $this->_msgCount;
    }

    /**
     * @param $mboxType
     * @return bool|Xhelp\MailBoxIMAP|Xhelp\MailBoxPOP3
     */
    public function _getMailBox($mboxType)
    {
        switch ($mboxType) {
            case _XHELP_MAILBOXTYPE_IMAP:
                return new Xhelp\MailBoxIMAP;
                break;
            case _XHELP_MAILBOXTYPE_POP3:
                return new Xhelp\MailBoxPOP3;
                break;
            default:
                return false;
        }
    }

    /**
     * @param $msg
     * @return bool
     */
    public function deleteMessage($msg)
    {
        if (is_array($msg)) {
            if (isset($msg['index'])) {
                $msgid = (int)$msg['index'];
            }
        } else {
            $msgid = (int)$msg;
        }

        if (null === $msgid) {
            return false;
        }

        return $this->_mBox->deleteMessage($msgid);
    }
}
