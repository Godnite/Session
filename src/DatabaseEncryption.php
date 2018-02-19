<?php

declare(strict_types=1);

namespace Rancoud\Session;

/**
 * Class DatabaseEncryption.
 */
class DatabaseEncryption extends Database
{
    use Encryption;

    /**
     * @param $sessionId
     *
     * @throws \Exception
     *
     * @return string
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
     * @throws \Exception
     *
     * @return bool
     */
    public function write($sessionId, $data): bool
    {
        $cryptedData = $this->encrypt($data);

        return parent::write($sessionId, $cryptedData);
    }
}
