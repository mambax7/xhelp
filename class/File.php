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
 * Xhelp\File class
 *
 * @author  Eric Juden <ericj@epcusa.com>
 * @access  public
 * @package xhelp
 */
class File extends \XoopsObject
{
    /**
     * Xhelp\File constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->initVar('id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('filename', XOBJ_DTYPE_TXTBOX, null, true, 255);
        $this->initVar('ticketid', XOBJ_DTYPE_INT, null, true);
        $this->initVar('responseid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('mimetype', XOBJ_DTYPE_TXTBOX, null, true, 255);

        if (null !== $id) {
            if (is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        $path = XHELP_UPLOAD_PATH . '/' . $this->getVar('filename');

        return $path;
    }

    /**
     * @param     $ticketid
     * @param int $responseid
     * @return bool
     */
    public function rename($ticketid, $responseid = 0)
    {
        $ticketid       = (int)$ticketid;
        $responseid     = (int)$responseid;
        $old_ticketid   = $this->getVar('ticketid');
        $old_responseid = $this->getVar('responseid');

        $filename = $this->getVar('filename');
        if ((0 != $old_responseid) && (0 != $responseid)) {   // Was a response and is going to be a response
            $newFilename = str_replace('_' . $old_responseid . '_', '_' . $responseid . '_', $filename);
            $newFilename = str_replace($old_ticketid . '_', $ticketid . '_', $newFilename);
        } elseif ((0 != $old_responseid) && (0 == $responseid)) { // Was a response and is part of the ticket now
            $newFilename = str_replace('_' . $old_responseid . '_', '_', $filename);
            $newFilename = str_replace($old_ticketid . '_', $ticketid . '_', $newFilename);
        } elseif ((0 == $old_responseid) && (0 != $responseid)) {  // Was part of the ticket, now going to a response
            $newFilename = str_replace($old_ticketid . '_', $ticketid . '_' . $responseid . '_', $filename);
        } elseif ((0 == $old_responseid)
                  && (0 == $responseid)) {  // Was part of the ticket, and is part of the ticket now
            $newFilename = str_replace($old_ticketid . '_', $ticketid . '_', $filename);
        }

        $hFile = new Xhelp\FileHandler($GLOBALS['xoopsDB']);
        $this->setVar('filename', $newFilename);
        $this->setVar('ticketid', $ticketid);
        $this->setVar('responseid', $responseid);
        if ($hFile->insert($this, true)) {
            $success = true;
        } else {
            $success = false;
        }

        $ret = false;
        if ($success) {
            $ret = $this->renameAtFS($filename, $newFilename);
        }

        return $ret;
    }

    /**
     * @param $oldName
     * @param $newName
     * @return bool
     */
    public function renameAtFS($oldName, $newName)
    {
        $ret = rename(XHELP_UPLOAD_PATH . '/' . $oldName, XHELP_UPLOAD_PATH . '/' . $newName);

        return $ret;
    }
}   //end of class
