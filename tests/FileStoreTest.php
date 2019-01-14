<?php
/**
 * Class MemoryTableTest
 *
 * Author:  Kernel Huang
 * Mail:    kernelman79@gmail.com
 * Date:    1/14/19
 * Time:    11:05 AM
 */


use Store\File\Sync\FileStore;

/**
 * Class FileStoreTest
 */
class FileStoreTest extends \PHPUnit\Framework\TestCase
{

    public function testCheckFile() {
        $path   = dirname(dirname(__FILE__)) . '/composer.json';
        $get    = FileStore::checkFile($path);
        $this->assertTrue($get);
    }
}
