<?php

declare(strict_types=1);

namespace Rancoud\Session\Test;

use PHPUnit\Framework\TestCase;
use Rancoud\Session\DefaultEncryption;
use Rancoud\Session\Session;

/**
 * Class DefaultEncryptionTest.
 */
class DefaultEncryptionTest extends TestCase
{
    private function foundSessionFile()
    {
        $path = ini_get('session.save_path');
        if (empty($path)) {
            $path = DIRECTORY_SEPARATOR . 'tmp';
        }
        $id = session_id();

        $files = scandir($path);
        foreach ($files as $file) {
            if (mb_strpos($file, $id) !== false) {
                return file_get_contents($path . DIRECTORY_SEPARATOR . 'sess_' . $id);
            }
        }

        return false;
    }

    /**
     * @runInSeparateProcess
     */
    public function testWrite()
    {
        Session::useDefaultEncryptionDriver('randomKey');

        Session::set('a', 'b');

        session_commit();
        $data = $this->foundSessionFile();
        static::assertNotFalse($data);

        $encryptionTrait = $this->getObjectForTrait('Rancoud\Session\Encryption');
        $encryptionTrait->setKey('randomKey');
        $endData = $encryptionTrait->decrypt($data);
        static::assertEquals('a|s:1:"b";', $endData);
    }

    /*
        public function testRead()
        {
            $defaultEncryption = new DefaultEncryption();
            $defaultEncryption->setKey('randomKey');
            $sessionId = 'test';
            $data = $defaultEncryption->read($sessionId);
            static::assertTrue(!empty($data));
            static::assertTrue(is_string($data));

            $sessionId = '';
            $data = $defaultEncryption->read($sessionId);
            static::assertTrue(empty($data));
            static::assertTrue(is_string($data));
        }*/
}
