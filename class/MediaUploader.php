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

use XoopsModules\Xhelp;

/**
 * @copyright    XOOPS Project https://xoops.org/
 * @license      GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @package
 * @since
 * @author       XOOPS Development Team, Kazumi Ono (AKA onokazu)
 */

/**
 * !
 * Example
 *
 * require_once __DIR__ . '/uploader.php';
 * $allowed_mimetypes = array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/x-png');
 * $maxfilesize = 50000;
 * $maxfilewidth = 120;
 * $maxfileheight = 120;
 * $uploader = new Xhelp\MediaUploader('/home/xoops/uploads', $allowed_mimetypes, $maxfilesize, $maxfilewidth, $maxfileheight);
 * if ($uploader->fetchMedia($HTTP_POST_VARS['uploade_file_name'])) {
 * if (!$uploader->upload()) {
 * echo $uploader->getErrors();
 * } else {
 * echo '<h4>File uploaded successfully!</h4>'
 * echo 'Saved as: ' . $uploader->getSavedFileName() . '<br>';
 * echo 'Full path: ' . $uploader->getSavedDestination();
 * }
 * } else {
 * echo $uploader->getErrors();
 * }
 */

/**
 * Upload Media files
 *
 * Example of usage:
 * <code>
 * require_once __DIR__ . '/uploader.php';
 * $allowed_mimetypes = array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/x-png');
 * $maxfilesize = 50000;
 * $maxfilewidth = 120;
 * $maxfileheight = 120;
 * $uploader = new Xhelp\MediaUploader('/home/xoops/uploads', $allowed_mimetypes, $maxfilesize, $maxfilewidth, $maxfileheight);
 * if ($uploader->fetchMedia($HTTP_POST_VARS['uploade_file_name'])) {
 *            if (!$uploader->upload()) {
 *               echo $uploader->getErrors();
 *            } else {
 *               echo '<h4>File uploaded successfully!</h4>'
 *               echo 'Saved as: ' . $uploader->getSavedFileName() . '<br>';
 *               echo 'Full path: ' . $uploader->getSavedDestination();
 *            }
 * } else {
 *            echo $uploader->getErrors();
 * }
 * </code>
 *
 * @package       kernel
 * @subpackage    core
 * @author        Kazumi Ono <onokazu@xoops.org>
 * @copyright (c) 2000-2003 XOOPS Project (https://xoops.org)
 */


mt_srand((double)microtime() * 1000000);

/**
 * class MediaUploader
 */
class MediaUploader
{
    public $mediaName;
    public $mediaType;
    public $mediaSize;
    public $mediaTmpName;
    public $mediaError;
    public $uploadDir        = '';
    public $allowedMimeTypes = [];
    public $maxFileSize      = 0;
    public $maxWidth;
    public $maxHeight;
    public $targetFileName;
    public $prefix;
    public $ext;
    public $dimension;
    public $errors           = [];
    public $savedDestination;
    public $savedFileName;
    /**
     * No admin check for uploads
     */
    public $noadmin_sizecheck;

    /**
     * Constructor
     *
     * @param string    $uploadDir
     * @param array|int $allowedMimeTypes
     * @param int       $maxFileSize
     * @param int       $maxWidth
     * @param int       $maxHeight
     * @internal param int $cmodvalue
     */
    public function __construct($uploadDir, $allowedMimeTypes = 0, $maxFileSize, $maxWidth = 0, $maxHeight = 0)
    {
        if (is_array($allowedMimeTypes)) {
            $this->allowedMimeTypes =& $allowedMimeTypes;
        }
        $this->uploadDir   = $uploadDir;
        $this->maxFileSize = (int)$maxFileSize;
        if (isset($maxWidth)) {
            $this->maxWidth = (int)$maxWidth;
        }
        if (isset($maxHeight)) {
            $this->maxHeight = (int)$maxHeight;
        }
    }

    /**
     * @param $value
     */
    public function noAdminSizeCheck($value)
    {
        $this->noadmin_sizecheck = $value;
    }

    /**
     * Fetch the uploaded file
     *
     * @param string $media_name Name of the file field
     * @param int    $index      Index of the file (if more than one uploaded under that name)
     * @global       $HTTP_POST_FILES
     * @return bool
     */
    public function fetchMedia($media_name, $index = null)
    {
        global $_FILES;

        if (!isset($_FILES[$media_name])) {
            $this->setErrors('You either did not choose a file to upload or the server has insufficient read/writes to upload this file.!');

            return false;
        } elseif (is_array($_FILES[$media_name]['name']) && isset($index)) {
            $index              = (int)$index;
            $this->mediaName    = get_magic_quotes_gpc() ? stripslashes($_FILES[$media_name]['name'][$index]) : $_FILES[$media_name]['name'][$index];
            $this->mediaType    = $_FILES[$media_name]['type'][$index];
            $this->mediaSize    = $_FILES[$media_name]['size'][$index];
            $this->mediaTmpName = $_FILES[$media_name]['tmp_name'][$index];
            $this->mediaError   = !empty($_FILES[$media_name]['error'][$index]) ? $_FILES[$media_name]['errir'][$index] : 0;
        } else {
            $media_name         = @$_FILES[$media_name];
            $this->mediaName    = get_magic_quotes_gpc() ? stripslashes($media_name['name']) : $media_name['name'];
            $this->mediaName    = $media_name['name'];
            $this->mediaType    = $media_name['type'];
            $this->mediaSize    = $media_name['size'];
            $this->mediaTmpName = $media_name['tmp_name'];
            $this->mediaError   = !empty($media_name['error']) ? $media_name['error'] : 0;
        }
        $this->dimension = getimagesize($this->mediaTmpName);

        $this->errors = [];

        if ((int)$this->mediaSize < 0) {
            $this->setErrors('Invalid File Size');

            return false;
        }
        if ('' == $this->mediaName) {
            $this->setErrors('Filename Is Empty');

            return false;
        }

        if ('none' == $this->mediaTmpName) {
            $this->setErrors('No file uploaded, this is a error');

            return false;
        }

        if (!$this->checkMaxFileSize()) {
            $this->setErrors(sprintf('File Size: %u. Maximum Size Allowed: %u', $this->mediaSize, $this->maxFileSize));
        }

        if (is_array($this->dimension)) {
            if (!$this->checkMaxWidth($this->dimension[0])) {
                $this->setErrors(sprintf('File width: %u. Maximum width allowed: %u', $this->dimension[0], $this->maxWidth));
            }
            if (!$this->checkMaxHeight($this->dimension[1])) {
                $this->setErrors(sprintf('File height: %u. Maximum height allowed: %u', $this->dimension[1], $this->maxHeight));
            }
        }

        if (count($this->errors) > 0) {
            return false;
        }

        if (!$this->checkMimeType()) {
            $this->setErrors('MIME type not allowed: ' . $this->mediaType);
        }

        if (!is_uploaded_file($this->mediaTmpName)) {
            switch ($this->mediaError) {
                case 0: // no error; possible file attack!
                    $this->setErrors('There was a problem with your upload. Error: 0');
                    break;
                case 1: // uploaded file exceeds the upload_max_filesize directive in php.ini
                    //if ($this->noAdminSizeCheck)
                    //{
                    //    return true;
                    //}
                    $this->setErrors('The file you are trying to upload is too big. Error: 1');
                    break;
                case 2: // uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form
                    $this->setErrors('The file you are trying to upload is too big. Error: 2');
                    break;
                case 3: // uploaded file was only partially uploaded
                    $this->setErrors('The file you are trying upload was only partially uploaded. Error: 3');
                    break;
                case 4: // no file was uploaded
                    $this->setErrors('No file selected for upload. Error: 4');
                    break;
                default: // a default error, just in case!  :)
                    $this->setErrors('No file selected for upload. Error: 5');
                    break;
            }

            return false;
        }

        return true;
    }

    /**
     * Set the target filename
     *
     * @param string $value
     */
    public function setTargetFileName($value)
    {
        $this->targetFileName = trim($value);
    }

    /**
     * Set the prefix
     *
     * @param string $value
     */
    public function setPrefix($value)
    {
        $this->prefix = trim($value);
    }

    /**
     * Get the uploaded filename
     *
     * @return string
     */
    public function getMediaName()
    {
        return $this->mediaName;
    }

    /**
     * Get the type of the uploaded file
     *
     * @return string
     */
    public function getMediaType()
    {
        return $this->mediaType;
    }

    /**
     * Get the size of the uploaded file
     *
     * @return int
     */
    public function getMediaSize()
    {
        return $this->mediaSize;
    }

    /**
     * Get the temporary name that the uploaded file was stored under
     *
     * @return string
     */
    public function getMediaTmpName()
    {
        return $this->mediaTmpName;
    }

    /**
     * Get the saved filename
     *
     * @return string
     */
    public function getSavedFileName()
    {
        return $this->savedFileName;
    }

    /**
     * Get the destination the file is saved to
     *
     * @return string
     */
    public function getSavedDestination()
    {
        return $this->savedDestination;
    }

    /**
     * Check the file and copy it to the destination
     *
     * @param int $chmod
     * @return bool
     */
    public function upload($chmod = 0644)
    {
        if ('' == $this->uploadDir) {
            $this->setErrors('Upload directory not set');

            return false;
        }

        if (!is_dir($this->uploadDir)) {
            $this->setErrors('Failed opening directory: ' . $this->uploadDir);
        }

        if (!is_writable($this->uploadDir)) {
            $this->setErrors('Failed opening directory with write permission: ' . $this->uploadDir);
        }

        if (!$this->checkMaxFileSize()) {
            $this->setErrors(sprintf('File Size: %u. Maximum Size Allowed: %u', $this->mediaSize, $this->maxFileSize));
        }

        if (is_array($this->dimension)) {
            if (!$this->checkMaxWidth($this->dimension[0])) {
                $this->setErrors(sprintf('File width: %u. Maximum width allowed: %u', $this->dimension[0], $this->maxWidth));
            }
            if (!$this->checkMaxHeight($this->dimension[1])) {
                $this->setErrors(sprintf('File height: %u. Maximum height allowed: %u', $this->dimension[1], $this->maxHeight));
            }
        }

        if (!$this->checkMimeType()) {
            $this->setErrors('MIME type not allowed: ' . $this->mediaType);
        }

        if (!$this->_copyFile($chmod)) {
            $this->setErrors('Failed uploading file: ' . $this->mediaName);
        }

        if (count($this->errors) > 0) {
            return false;
        }

        return true;
    }

    /**
     * Copy the file to its destination
     *
     * @param $chmod
     * @return bool
     */
    public function _copyFile($chmod)
    {
        $matched = [];
        if (!preg_match("/\.([a-zA-Z0-9]+)$/", $this->mediaName, $matched)) {
            return false;
        }
        if (isset($this->targetFileName)) {
            $this->savedFileName = $this->targetFileName;
        } elseif (isset($this->prefix)) {
            $this->savedFileName = uniqid($this->prefix) . '.' . strtolower($matched[1]);
        } else {
            $this->savedFileName = strtolower($this->mediaName);
        }
        $this->savedFileName    = preg_replace('!\s+!', '_', $this->savedFileName);
        $this->savedDestination = $this->uploadDir . $this->savedFileName;
        if (is_file($this->savedDestination) && !!is_dir($this->savedDestination)) {
            $this->setErrors('File ' . $this->mediaName . ' already exists on the server. Please rename this file and try again.<br>');

            return false;
        }
        if (!move_uploaded_file($this->mediaTmpName, $this->savedDestination)) {
            return false;
        }
        @chmod($this->savedDestination, $chmod);

        return true;
    }

    /**
     * Is the file the right size?
     *
     * @return bool
     */
    public function checkMaxFileSize()
    {
        if ($this->noadmin_sizecheck) {
            return true;
        }
        if ($this->mediaSize > $this->maxFileSize) {
            return false;
        }

        return true;
    }

    /**
     * Is the picture the right width?
     *
     * @param $dimension
     * @return bool
     */
    public function checkMaxWidth($dimension)
    {
        if (!isset($this->maxWidth)) {
            return true;
        }
        if ($dimension > $this->maxWidth) {
            return false;
        }

        return true;
    }

    /**
     * Is the picture the right height?
     *
     * @param $dimension
     * @return bool
     */
    public function checkMaxHeight($dimension)
    {
        if (!isset($this->maxHeight)) {
            return true;
        }
        if ($dimension > $this->maxWidth) {
            return false;
        }

        return true;
    }

    /**
     * Is the file the right Mime type
     *
     * (is there a right type of mime? ;-)
     *
     * @return bool
     */
    public function checkMimeType()
    {
        if (count($this->allowedMimeTypes) > 0 && !in_array($this->mediaType, $this->allowedMimeTypes)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Add an error
     *
     * @param string $error
     */
    public function setErrors($error)
    {
        $this->errors[] = trim($error);
    }

    /**
     * Get generated errors
     *
     * @param  bool $ashtml Format using HTML?
     * @return array |string    Array of array messages OR HTML string
     */
    public function &getErrors($ashtml = true)
    {
        if (!$ashtml) {
            return $this->errors;
        } else {
            $ret = '';
            if (count($this->errors) > 0) {
                $ret = '<h4>Errors Returned While Uploading</h4>';
                foreach ($this->errors as $error) {
                    $ret .= $error . '<br>';
                }
            }

            return $ret;
        }
    }
}
