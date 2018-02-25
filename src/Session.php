<?php

declare(strict_types=1);

namespace Rancoud\Session;

use Exception;

/**
 * Class Session.
 */
class Session extends DriverManager
{
    use ArrayManager;

    /** @var bool */
    protected static $hasStarted = false;
    /** @var bool */
    protected static $options = [
        'read_and_close'   => true,
        'cookie_httponly'  => '1',
        'use_only_cookies' => '1',
        'use_trans_sid'    => '0'
    ];

    /**
     * @param array $options
     *
     * @throws Exception
     */
    public static function start(array $options = []): void
    {
        static::throwExceptionIfHasStarted();

        static::populateOptions($options);

        static::setupAndStart();
    }

    /**
     * @param array $options
     *
     * @throws Exception
     */
    protected static function populateOptions(array $options = []): void
    {
        self::validateOptions($options);

        static::setOptions($options);
    }

    /**
     * @throws Exception
     */
    protected static function setupAndStart()
    {
        static::configureDriver();
        static::setupSessionParameters();
        static::startSession();

        static::$hasStarted = true;
        if (static::$options['read_and_close']) {
            static::$hasStarted = false;
        } else {
            static::restoreFlashData();
        }
    }

    /**
     * @throws \Exception
     */
    protected static function throwExceptionIfHasStarted(): void
    {
        if (static::hasStarted()) {
            throw new Exception('Session already started');
        }
    }

    /**
     * @throws Exception
     */
    protected static function setupSessionParameters(): void
    {
        session_name(static::getOption('name'));

        session_set_save_handler(static::$driver);

        session_save_path(static::getOption('save_path'));

        static::setupCookieParams();

        register_shutdown_function('session_write_close');
    }

    /**
     * @param array $options
     *
     * @throws Exception
     *
     * @return bool
     */
    protected static function startSession(): bool
    {
        static::validateOptions(static::$options);

        static::setupCookieParams();

        return session_start(static::$options);
    }

    /**
     * @param array $options
     *
     * @throws Exception
     */
    protected static function validateOptions(array $options = []): void
    {
        if (empty($options)) {
            return;
        }

        $validOptions = [
            'save_path',
            'name',
            'save_handler',
            'auto_start',
            'gc_probability',
            'gc_divisor',
            'gc_maxlifetime',
            'serialize_handler',
            'cookie_lifetime',
            'cookie_path',
            'cookie_domain',
            'cookie_secure',
            'cookie_httponly',
            'use_strict_mode',
            'use_cookies',
            'use_only_cookies',
            'referer_check',
            'cache_limiter',
            'cache_expire',
            'use_trans_sid',
            'trans_sid_tags',
            'trans_sid_hosts',
            'sid_length',
            'sid_bits_per_character',
            'upload_progress.enabled',
            'upload_progress.cleanup',
            'upload_progress.prefix',
            'upload_progress.name',
            'upload_progress.freq',
            'upload_progress.min_freq',
            'lazy_write',
            'read_and_close'
        ];

        $keys = array_keys($options);
        foreach ($keys as $key) {
            if (!in_array($key, $validOptions, true)) {
                throw new Exception('Incorrect option: ' . $key);
            }
        }
    }

    /**
     * @throws Exception
     */
    protected static function setupCookieParams(): void
    {
        session_set_cookie_params(
            static::getOption('cookie_lifetime'),
            static::getOption('cookie_path'),
            static::getOption('cookie_domain'),
            isset($_SERVER['HTTPS']),
            true
        );
    }

    /**
     * @throws Exception
     */
    public static function regenerate(): void
    {
        session_name(static::getOption('name'));
        session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        session_unset();
        session_destroy();
    }

    /**
     * @return bool
     */
    public static function abort(): bool
    {
        return session_abort();
    }

    /**
     * @return bool
     */
    public static function hasStarted(): bool
    {
        return static::$hasStarted;
    }

    /**
     * @throws \Exception
     */
    protected static function startSessionIfNotHasStarted(): void
    {
        if (!static::hasStarted()) {
            static::setupAndStart();
        }
    }

    /**
     * @throws Exception
     */
    protected static function startSessionIfNotHasStartedForceWrite()
    {
        if (!static::hasStarted()) {
            static::$options['read_and_close'] = false;
            static::setupAndStart();
        }
    }

    /**
     * @throws Exception
     *
     * @return string
     */
    protected static function getLifetimeForRedis()
    {
        return static::getOption('cookie_lifetime');
    }

    /**
     * @param $key
     * @param $value
     *
     * @throws Exception
     */
    public static function setOption(string $key, $value)
    {
        static::validateOptions([$key => $value]);
        static::$options[$key] = $value;
    }

    /**
     * @param array $options
     *
     * @throws Exception
     */
    public static function setOptions(array $options)
    {
        static::validateOptions($options);
        static::$options = $options + static::$options;
    }

    /**
     * @param string $key
     *
     * @throws Exception
     *
     * @return mixed
     */
    public static function getOption(string $key)
    {
        if (array_key_exists($key, static::$options)) {
            return static::$options[$key];
        }

        static::validateOptions([$key => '']);
        static::$options[$key] = ini_get('session.' . $key);

        if ($key === 'save_path' && empty(static::$options[$key])) {
            static::$options[$key] = '/tmp';
        }

        return static::$options[$key];
    }
}
