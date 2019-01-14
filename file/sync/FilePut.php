<?php
/**
 * Created by IntelliJ IDEA.
 * User: kernel Huang
 * Email: kernelman79@gmail.com
 * Date: 2018/11/24
 * Time: 4:11 PM
 */

namespace Store\File\Sync;


/**
 * 文件写入类
 *
 * Class FilePut
 * @package Processor
 */
class FilePut {

    public $file    = null; // 文件路径
    public $append  = null; // 添加/覆盖
    public $locked  = null; // 锁定/非锁定
    public $content = null; // 写入内容

    /**
     * FilePut constructor.
     *
     * @param $file
     * @param $content
     * @param bool $append
     * @param bool $locked
     */
    public function __construct($file, $content, $append = true, $locked = true) {
        $this->file     = $file;
        $this->append   = $append;
        $this->locked   = $locked;
        $this->content  = $content;
    }

    /**
     * 执行文件写入操作
     *
     * @return bool
     */
    public function run() {
        return $this->lockedAndAppend()
            ?: $this->lockedAndNonAppend()
            ?: $this->nonLockedAndAppend()
            ?: $this->nonLockedAndNonAppend();
    }

    /**
     * 锁定并添加文件
     *
     * @return bool|int
     */
    private function lockedAndAppend() {
        if ($this->locked && $this->append) {
            return file_put_contents($this->file, $this->content, FILE_APPEND | LOCK_EX);
        }

        return false;
    }

    /**
     * 锁定非添加（覆写文件）
     *
     * @return bool|int
     */
    private function lockedAndNonAppend() {
        if ($this->locked && !$this->append) {
            return file_put_contents($this->file, $this->content, LOCK_EX);
        }

        return false;
    }

    /**
     * 非锁定添加文件
     *
     * @return bool|int
     */
    private function nonLockedAndAppend() {
        if (!$this->locked && $this->append) {
            return file_put_contents($this->file, $this->content, FILE_APPEND);
        }

        return false;
    }

    /**
     * 非锁定非添加（直接覆写文件）
     *
     * @return bool|int
     */
    private function nonLockedAndNonAppend() {
        if (!$this->locked && !$this->append) {
            return file_put_contents($this->file, $this->content);
        }

        return false;
    }

    /**
     * 释放内存
     */
    public function __destruct() {
        unset($this->file);
        unset($this->append);
        unset($this->locked);
        unset($this->content);
    }
}
