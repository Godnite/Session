<?php

declare(strict_types=1);

namespace Rancoud\Session;

/**
 * Class FileEncryption.
 */
class FileEncryption extends File
{
    use Encryption;

    /**
     * @param $sessionId
     *
     * @return string
     * @throws SessionException
     */
    public function read($sessionId): string
    {
        $encryptedData = parent::read($sessionId);

        return $this->decrypt($encryptedData);
    }

    /**
     * @param $sessionId
     * @param $data
     *
     * @return bool
     * @throws SessionException
     */
    public function write($sessionId, $data): bool
    {
        $cryptedData = $this->encrypt($data);

        return parent::write($sessionId, $cryptedData);
    }
}
