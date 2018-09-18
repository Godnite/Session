<?php

declare(strict_types=1);

namespace Rancoud\Session\Test;

use PHPUnit\Framework\TestCase;
use Rancoud\Session\FileEncryption;

/**
 * Class FileEncryptionTest.
 */
class FileEncryptionTest extends TestCase
{
    protected function setUp()
    {
        $path = ini_get('session.save_path');
        if (empty($path)) {
            $path = DIRECTORY_SEPARATOR . 'tmp';
        }

        $pattern = $path . DIRECTORY_SEPARATOR . 'sess_*';
        foreach (glob($pattern) as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        if (is_dir($path . DIRECTORY_SEPARATOR . 'tests')) {
            rmdir($path . DIRECTORY_SEPARATOR . 'tests');
        }
    }

    private function getPath()
    {
        $path = ini_get('session.save_path');
        if (empty($path)) {
            return DIRECTORY_SEPARATOR . 'tmp';
        }

        return $path;
    }

    private function openSessionForSavingSavePath(FileEncryption $fileEncryption)
    {
        $success = $fileEncryption->open($this->getPath(), '');
        static::assertTrue($success);
    }

    public function testOpen()
    {
        $fileEncryption = new FileEncryption();
        $fileEncryption->setKey('randomKey');

        $savePath = $this->getPath();
        $sessionName = '';
        $success = $fileEncryption->open($savePath, $sessionName);
        static::assertTrue($success);

        $savePathNotCreated = $savePath . DIRECTORY_SEPARATOR . 'tests';
        $success = $fileEncryption->open($savePathNotCreated, $sessionName);
        static::assertTrue($success);
        $success = file_exists($savePathNotCreated);
        static::assertTrue($success);
    }

    public function testClose()
    {
        $fileEncryption = new FileEncryption();
        $fileEncryption->setKey('randomKey');
        $success = $fileEncryption->close();
        static::assertTrue($success);
    }

    public function testWrite()
    {
        $fileEncryption = new FileEncryption();
        $fileEncryption->setKey('randomKey');

        $this->openSessionForSavingSavePath($fileEncryption);

        $sessionId = 'sessionId';
        $data = 'azerty';
        $success = $fileEncryption->write($sessionId, $data);
        static::assertTrue($success);

        $dataInFile = file_get_contents($this->getPath() . DIRECTORY_SEPARATOR . 'sess_' . $sessionId);
        static::assertNotEquals($data, $dataInFile);

        $encryptionTrait = $this->getObjectForTrait('Rancoud\Session\Encryption');
        $encryptionTrait->setKey('randomKey');
        $dataInFileDecrypted = $encryptionTrait->decrypt($dataInFile);
        static::assertEquals($data, $dataInFileDecrypted);
    }

    public function testRead()
    {
        $fileEncryption = new FileEncryption();
        $fileEncryption->setKey('randomKey');

        $this->openSessionForSavingSavePath($fileEncryption);

        $sessionId = 'sessionId';
        $data = 'azerty';
        $success = $fileEncryption->write($sessionId, $data);
        static::assertTrue($success);

        $dataOutput = $fileEncryption->read($sessionId);
        static::assertTrue(!empty($dataOutput));
        static::assertTrue(is_string($dataOutput));
        static::assertEquals($data, $dataOutput);

        $sessionId = '';
        $dataOutput = $fileEncryption->read($sessionId);
        static::assertTrue(empty($dataOutput));
        static::assertTrue(is_string($dataOutput));
    }

    public function testDestroy()
    {
        $fileEncryption = new FileEncryption();
        $fileEncryption->setKey('randomKey');

        $this->openSessionForSavingSavePath($fileEncryption);

        $sessionId = 'todelete';
        $success = $fileEncryption->destroy($sessionId);
        static::assertTrue($success);

        $sessionId = 'sessionId';
        $data = 'azerty';
        $success = $fileEncryption->write($sessionId, $data);
        static::assertTrue($success);

        $isFileExist = file_exists($this->getPath() . DIRECTORY_SEPARATOR . 'sess_' . $sessionId);
        static::assertTrue($isFileExist);
        $success = $fileEncryption->destroy($sessionId);
        static::assertTrue($success);
        $isFileNotExist = !file_exists($this->getPath() . DIRECTORY_SEPARATOR . 'sess_' . $sessionId);
        static::assertTrue($isFileNotExist);
    }

    public function testGc()
    {
        $fileEncryption = new FileEncryption();
        $fileEncryption->setKey('randomKey');

        $this->openSessionForSavingSavePath($fileEncryption);

        $sessionId = 'sessionId';
        $data = 'azerty';
        $success = $fileEncryption->write($sessionId, $data);
        static::assertTrue($success);

        $isFileExist = file_exists($this->getPath() . DIRECTORY_SEPARATOR . 'sess_' . $sessionId);
        static::assertTrue($isFileExist);

        $lifetime = -1000;
        $success = $fileEncryption->gc($lifetime);
        static::assertTrue($success);

        $isFileNotExist = !file_exists($this->getPath() . DIRECTORY_SEPARATOR . 'sess_' . $sessionId);
        static::assertTrue($isFileNotExist);
    }

    public function testValidateId()
    {
        $fileEncryption = new FileEncryption();
        $fileEncryption->setKey('randomKey');

        $this->openSessionForSavingSavePath($fileEncryption);

        $baseId = 'TiqKrZDUGp5ubt3klF0oorIlFiADXC9jxig9e8leUcCYuZ9w0mXh0b1foEGIBs7SSsdOuLor58vU5liBRVPsTobnvt';
        $endId1 = 'Tj8hh65DlR3tTFI1SGX3mFciDA9rMOa4LlnMr';
        $endId2 = 'Tklezfoipvfk0lferijkoefzjklgrvefLlnMr';

        $fileEncryption->write($baseId . $endId1, 'a');

        static::assertTrue($fileEncryption->validateId($baseId . $endId1));
        static::assertFalse($fileEncryption->validateId($baseId . $endId2));
        static::assertFalse($fileEncryption->validateId('kjlfez/fez'));
    }

    public function testUpdateTimestamp()
    {
        $fileEncryption = new FileEncryption();
        $fileEncryption->setKey('randomKey');

        $this->openSessionForSavingSavePath($fileEncryption);

        $sessionId = 'sessionId';
        $data = 'azerty';
        $success = $fileEncryption->write($sessionId, $data);
        static::assertTrue($success);

        $dataInFile = file_get_contents($this->getPath() . DIRECTORY_SEPARATOR . 'sess_' . $sessionId);
        $oldFileModifiedTime = filemtime($this->getPath() . DIRECTORY_SEPARATOR . 'sess_' . $sessionId);
        static::assertNotEquals($data, $dataInFile);

        $encryptionTrait = $this->getObjectForTrait('Rancoud\Session\Encryption');
        $encryptionTrait->setKey('randomKey');
        $dataInFileDecrypted = $encryptionTrait->decrypt($dataInFile);
        static::assertEquals($data, $dataInFileDecrypted);

        sleep(1);
        $success = $fileEncryption->updateTimestamp($sessionId, $data);
        static::assertTrue($success);

        clearstatcache();

        $dataInFile2 = file_get_contents($this->getPath() . DIRECTORY_SEPARATOR . 'sess_' . $sessionId);
        static::assertNotEquals($data, $dataInFile2);

        $encryptionTrait = $this->getObjectForTrait('Rancoud\Session\Encryption');
        $encryptionTrait->setKey('randomKey');
        $dataInFileDecrypted = $encryptionTrait->decrypt($dataInFile2);
        static::assertEquals($data, $dataInFileDecrypted);
        static::assertNotEquals($dataInFile, $dataInFile2);

        $newFileModifiedTime = filemtime($this->getPath() . DIRECTORY_SEPARATOR . 'sess_' . $sessionId);

        static::assertTrue($oldFileModifiedTime < $newFileModifiedTime);
    }

    public function testCreateId()
    {
        $fileEncryption = new FileEncryption();
        $fileEncryption->setKey('randomKey');

        $string = $fileEncryption->create_sid();

        static::assertTrue(preg_match('/^[a-zA-Z0-9-]{127}+$/', $string) === 1);
    }
}
