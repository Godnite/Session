<?php

declare(strict_types=1);

namespace Rancoud\Session;

use SessionHandlerInterface;
use SessionIdInterface;
use SessionUpdateTimestampHandlerInterface;

/**
 * Class File.
 */
class File implements SessionHandlerInterface, SessionIdInterface, SessionUpdateTimestampHandlerInterface
{
    protected $savePath;
    protected $prefix = 'sess_';

    /**
     * @param string $prefix
     */
    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    /**
     * @param $savePath
     * @param $sessionName
     *
     * @return bool
     */
    public function open($savePath, $sessionName): bool
    {
        $this->savePath = $savePath;

        if (!is_dir($this->savePath)) {
            mkdir($this->savePath, 0777);
        }

        return true;
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * @param $sessionId
     *
     * @return string
     */
    public function read($sessionId): string
    {
        $filename = $this->savePath . DIRECTORY_SEPARATOR . $this->prefix . $sessionId;
        if (file_exists($filename)) {
            return (string) file_get_contents($filename);
        }

        return '';
    }

    /**
     * @param $sessionId
     * @param $data
     *
     * @return bool
     */
    public function write($sessionId, $data): bool
    {
        $filename = $this->savePath . DIRECTORY_SEPARATOR . $this->prefix . $sessionId;

        return file_put_contents($filename, $data) === false ? false : true;
    }

    /**
     * @param $sessionId
     *
     * @return bool
     */
    public function destroy($sessionId): bool
    {
        $filename = $this->savePath . DIRECTORY_SEPARATOR . $this->prefix . $sessionId;
        if (file_exists($filename)) {
            unlink($filename);
        }

        return true;
    }

    /**
     * @param $lifetime
     *
     * @return bool
     */
    public function gc($lifetime): bool
    {
        $pattern = $this->savePath . DIRECTORY_SEPARATOR . $this->prefix . '*';
        foreach (glob($pattern) as $file) {
            if (filemtime($file) + $lifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }

        return true;
    }

    /**
     * Checks if a session identifier already exists or not.
     *
     * @param string $key
     *
     * @return bool
     */
    public function validateId($key)
    {
        var_dump('validateId', $key);
        $path = $this->savePath . DIRECTORY_SEPARATOR . $this->prefix;
        $files = glob($path . '*');
        if(in_array($path . $key, $files)){
            return true;
        }

        return false;
    }

    /**
     * Updates the timestamp of a session when its data didn't change.
     *
     * @param string $key
     * @param string $val
     *
     * @return bool
     */
    public function updateTimestamp($key, $val)
    {
        var_dump('updateTimestamp', $key, $val);
        // TODO: Implement updateTimestamp() method.
    }

    public function create_sid()
    {
        var_dump('create_sid');
        return 'a';
    }
}
