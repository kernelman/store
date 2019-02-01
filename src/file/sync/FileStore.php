<?php
/**
 * Created by IntelliJ IDEA.
 * User: kernel Huang
 * Email: kernelman79@gmail.com
 * Date: 2018/11/17
 * Time: 10:06 AM
 */

namespace Store\File\Sync;

use Exceptions\AlreadyExistsException;
use Exceptions\InvalidArgumentException;
use Exceptions\NonEmptyException;
use Exceptions\NotFoundException;
use Exceptions\UnExecutableException;
use Exceptions\UnReadableException;
use Exceptions\UnWritableException;

/**
 * 操作目录和文件的文件存储类
 *
 * Class FileStore
 * @package Api
 */
class FileStore {

    public static $files   = null;     // 文件绝对路径
    public static $content = null;     // 写入内容，内容须为字符串

    /**
     * 检查路径和内容参数
     *
     * @return bool
     */
    private static function check() {

        if(!is_string(self::$content) && !is_int(self::$content) || self::$content === null) {
            return false;
        }

        if(!is_string(self::$files) || self::$files === null) {
            return false;
        }

        return true;
    }

    /**
     * 检查目录是否存在
     *
     * @param $dir
     * @return bool
     */
    public static function checkDir($dir) {

        if(is_dir($dir)) {
            return true;
        }

        return false;
    }

    /**
     * 创建目录
     *
     * @param $dir
     * @return bool
     * @throws AlreadyExistsException
     * @throws UnExecutableException
     * @throws UnWritableException
     */
    public static function createDir($dir) {
        $parentDir = dirname($dir);

        $isExists       = self::checkDir($dir);
        $isWritable     = self::checkDirWritable($parentDir);
        $isExecutable   = self::checkDirExecutable($parentDir);

        if(!$isExists && $isExecutable && $isWritable) {
            return mkdir($dir);
        }

        if($isExists) {
            throw new AlreadyExistsException($dir);
        }

        return false;
    }

    /**
     * 判断空目录
     *
     * @param $dir
     * @return bool
     */
    public static function isEmptyDir($dir) {

        if (self::checkDir($dir) && !self::filterDir($dir)) {
            return true;
        }

        return false;
    }

    /**
     * 过滤.和..目录
     *
     * @param $dir
     * @return array|bool
     */
    private static function filterDir($dir) {
        $scan   = array_diff(scandir($dir), array('.', '..'));
        $result = count($scan);

        if($result === 0) {
            return false;
        }

        return $scan;
    }

    /**
     * 判断非空目录
     *
     * @param $dir
     * @return array|bool
     */
    public static function nonEmptyDir($dir) {
        $scan = self::filterDir($dir);

        if (self::checkDir($dir) && $scan) {
            return $scan;
        }

        return false;
    }

    /**
     * 移动(重命名)文件
     *
     * @param $oldPath
     * @param $newPath
     * @return bool
     * @throws UnReadableException
     * @throws UnWritableException
     */
    public static function moveFile($oldPath, $newPath) {
        if (self::checkFile($oldPath)) {

            $checkOldReadable       = self::checkFileReadable($oldPath);
            $checkOldWritable       = self::checkFileWritable($oldPath);
            $checkNewDirReadable    = self::checkDirReadable(dirname($newPath));
            $checkNewDirWritable    = self::checkDirWritable(dirname($newPath));

            if($checkOldReadable && $checkOldWritable && $checkNewDirReadable && $checkNewDirWritable) {
                return rename($oldPath, $newPath);
            }
        }

        return false;
    }

    /**
     * 删除文件
     *
     * @param $file
     * @return bool
     * @throws NotFoundException
     * @throws UnExecutableException
     * @throws UnReadableException
     * @throws UnWritableException
     */
    public static function deleteFile($file) {
        self::$files    = $file;
        $currentDir     = dirname(self::$files);

        $checkDirReadable       = self::checkDirReadable($currentDir);
        $checkDirWritable       = self::checkDirWritable($currentDir);
        $checkDirExecutable     = self::checkDirExecutable($currentDir);
        $checkFileReadable      = self::checkFileReadable(self::$files);
        $checkFileWritable      = self::checkFileWritable(self::$files);

        if($checkDirReadable && $checkDirExecutable && $checkDirWritable && $checkFileReadable && $checkFileWritable) {
            return unlink(self::$files);
        }

        throw new NotFoundException(self::$files);
    }

    /**
     * 删除空目录
     *
     * @param $dir
     * @return bool
     * @throws NonEmptyException
     * @throws UnExecutableException
     * @throws UnWritableException
     */
    public static function deleteEmptyDir($dir) {
        $parentDir = dirname($dir);

        $isEmptyDir     = self::isEmptyDir($dir);
        $isWritable     = self::checkDirWritable($parentDir);
        $isExecutable   = self::checkDirExecutable($parentDir);

        if($isEmptyDir && $isExecutable && $isWritable) {
            return rmdir($dir);
        }

        if(!$isEmptyDir) {
            throw new NonEmptyException('directory ', $dir);
        }

        return false;
    }

    /**
     * 检查文件是否存在
     *
     * @param $file
     * @return bool
     */
    public static function checkFile($file) {

        if(file_exists($file)) {
            return true;
        }

        return false;
    }

    /**
     * 检查文件是否有读权限
     *
     * @param $path
     * @return bool
     * @throws UnReadableException
     */
    public static function checkFileReadable($path) {
        if (self::checkFile($path)) {
            if (is_readable($path)) {
                return true;
            }

            throw new UnReadableException($path);
        }

        return false;
    }

    /**
     * 检查文件是否有写权限
     *
     * @param $path
     * @return bool
     * @throws UnWritableException
     */
    public static function checkFileWritable($path) {

        if(self::checkFile($path)) {

            if(is_writable($path)) {
                return true;
            }

            throw new UnWritableException($path);
        }

        return false;
    }

    /**
     * 检查文件是否有执行权限
     *
     * @param $path
     * @return bool
     * @throws UnExecutableException
     */
    public static function checkFileExecutable($path) {

        if(self::checkFile($path)) {

            if (is_executable($path)) {
                return true;
            }

            throw new UnExecutableException($path);
        }

        return false;
    }

    /**
     * 检查目录是否有读权限
     *
     * @param $path
     * @return bool
     * @throws UnReadableException
     */
    public static function checkDirReadable($path) {

        if (self::checkDir($path)) {
            if (is_readable($path)) {
                return true;
            }

            throw new UnReadableException($path);
        }

        return false;
    }

    /**
     * 检查目录是否有写权限
     *
     * @param $path
     * @return bool
     * @throws UnWritableException
     */
    public static function checkDirWritable($path) {

        if(self::checkDir($path)) {

            if(is_writable($path)) {
                return true;
            }

            throw new UnWritableException($path);
        }

        return false;
    }

    /**
     * 检查目录是否有执行权限
     *
     * @param $path
     * @return bool
     * @throws UnExecutableException
     */
    public static function checkDirExecutable($path) {

        if(self::checkDir($path)) {

            if (is_executable($path)) {
                return true;
            }

            throw new UnExecutableException($path);
        }

        return false;
    }

    /**
     * 保存文件
     *
     * @param $files
     * @param $content
     * @param bool $append
     * @param bool $locked
     * @return bool
     * @throws AlreadyExistsException
     * @throws InvalidArgumentException
     * @throws UnExecutableException
     * @throws UnReadableException
     * @throws UnWritableException
     */
    public static function save($files, $content, $append = true, $locked = true) {

        self::$files    = $files;
        self::$content  = $content;

        if(!self::check()) {
            throw new InvalidArgumentException('$files: ' . $files . ' || ' . '$content: ' . $content);
        }

        $currentDir = dirname(self::$files);

        $checkDirReadable       = self::checkDirReadable($currentDir);
        $checkDirWritable       = self::checkDirWritable($currentDir);
        $checkDirExecutable     = self::checkDirExecutable($currentDir);
        $checkFileReadable      = self::checkFileReadable($files);
        $checkFileWritable      = self::checkFileWritable($files);

        // 如果文件存在
        if(self::checkFile(self::$files)) {

            // 当前目录和文件有读取、执行和写入权限
            if($checkDirReadable && $checkDirExecutable && $checkDirWritable && $checkFileReadable && $checkFileWritable) {
                $put = new FilePut(self::$files, self::$content, $append, $locked); // 默认锁定文件并追加写入
                return $put->run();
            }

        } else { // 当文件不存在

            // 如果文件的当前目录存在, 并有读取、执行和写入权限
            if (self::checkDir($currentDir) && $checkDirReadable && $checkDirWritable && $checkDirExecutable) {
                $put = new FilePut(self::$files, self::$content, $append, $locked); // 默认锁定文件并追加写入
                return $put->run();
            }

            // 如果文件的当前目录不存在，那么就创建目录
            if (!self::checkDir($currentDir) && self::createDir($currentDir)) {
                $put = new FilePut(self::$files, self::$content, $append, $locked); // 默认锁定文件并追加写入
                return $put->run();
            }
        }

        throw new UnWritableException(self::$files);
    }

    /**
     * 获取文件
     *
     * @param $file
     * @return bool|string
     */
    public static function get($file) {

        if(self::checkFile($file)) {
            return file_get_contents($file);
        }

        return false;
    }
}
