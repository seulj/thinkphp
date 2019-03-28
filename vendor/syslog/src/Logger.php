<?php
/**
 * A simple PSR-3 logger implementation that outputs to syslog.
 *
 * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
 *
 * @author Chris Johnson
 * @package cxj/phpsyslog
 */

namespace ItFarm\SysLog;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;


/**
 * A very simple PSR-3 logger implementation that outputs to syslog.
 */
class Logger extends AbstractLogger
{

    /**
     * Default values used if not provided in constructor or via setters.
     */
    const LOG_OPTIONS = LOG_PID;
    const LOG_FACILITY = LOG_USER;

    /**
     * Map LogLevel strings to integers required by syslog().
     * @var array
     */
    private static $levels = [
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT     => 1,
        LogLevel::CRITICAL  => 2,
        LogLevel::ERROR     => 3,
        LogLevel::WARNING   => 4,
        LogLevel::NOTICE    => 5,
        LogLevel::INFO      => 6,
        LogLevel::DEBUG     => 7,
    ];

    // syslog() controls:
    /**
     * See openlog() syslog options.
     * @var int
     */
    private $options; // initialized in constructor.
    /**
     * See openlog() syslog facilities.
     * @var int
     */
    private $facility; // initialized in constructor.
    /**
     * Lowest level priority of messages to log.
     * @var int
     */
    private $minimumLogLevel; // initialized in constructor.
    /**
     * Identifier.
     * @var null|string
     */
    private $ident; // initialized in constructor.

    /**
     * Has syslog connection and configuration been initialized?
     * @var boolean
     */
    private $isInit;


    /**
     * CONSTRUCTOR
     *
     * @param string $ident - usually program name, defaults to null.
     * @param string $minimumLogLevel - log only messages with >= this value.
     * @param int $options - see openlog() syslog options.
     * @param int $facility - see openlog() syslog facility.
     */
    public function __construct(
        $ident = null,
        $minimumLogLevel = LogLevel::DEBUG,
        $options = self::LOG_OPTIONS,
        $facility = self::LOG_FACILITY
    )
    {
        $this->ident           = $ident;
        $this->minimumLogLevel = self::$levels[$minimumLogLevel];
        $this->options         = $options;
        $this->facility        = $facility;
    }

    /**
     * Log a message.
     *
     * @param mixed $level The log level.
     * @param string $message The message to log.
     * @param array $context Additional contextual information.
     *
     * @return boolean true success | false failure.
     */
    public function log($level, $message, array $context = [])
    {
        if (self::$levels[$level] > $this->minimumLogLevel) {
            return true;
        }

        if ($this->init()) {

            return syslog(
                self::$levels[$level],
                $this->substitutePlaceholders($message, $context)
            );
        }

        return false; // init() failed.
    }

    /**
     * Sets the identification string, logging options and facility.
     *
     * @return boolean true success | false failure
     */
    private function init()
    {
        if (!$this->isInit) {
            $this->isInit = true;


            return openlog($this->ident, $this->options, $this->facility);
        }
        return true;
    }

    /**
     * Substitute PSR-3 style placeholders in a message.
     *
     * @param string $message The message template.
     * @param array <string, mixed> $context The placeholder values.
     *
     * @return string The message template with placeholder values substituted.
     */
    private function substitutePlaceholders($message, array $context)
    {
        if (false === strpos($message, '{')) {
            return $message;
        }
        $replacements = [];
        foreach ($context as $key => $value) {
            $replacements['{' . $key . '}'] = $value;
        }
        return strtr($message, $replacements);
    }

    /**
     * Setter.
     * @param int $facility
     */
    public function setFacility($facility)
    {
        $this->facility = $facility;
    }

    /**
     * Getter.
     * @return int
     */
    public function getFacility()
    {
        return $this->facility;
    }

    /**
     * Setter.
     * @param null|string $ident
     */
    public function setIdent($ident)
    {
        $this->ident = $ident;
    }

    /**
     * Getter.
     * @return null|string
     */
    public function getIdent()
    {
        return $this->ident;
    }

    /**
     * Setter.
     * @param mixed
     */
    public function setMinimumLogLevel($minimumLogLevel)
    {
        if (is_string($minimumLogLevel)) {
            $this->minimumLogLevel = self::$levels[$minimumLogLevel];
        }
        else if (is_integer($minimumLogLevel)) {
            $this->minimumLogLevel = $minimumLogLevel;
        }
    }

    /**
     * Getter.
     * @return int
     */
    public function getMinimumLogLevel()
    {
        return $this->minimumLogLevel;
    }

    /**
     * Getter.
     * @return mixed
     */
    public function getMinimumLogLevelString()
    {
        return array_search($this->minimumLogLevel, self::$levels);
    }

    /**
     * Setter.
     * @param int $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * Getter.
     * @return int
     */
    public function getOptions()
    {
        return $this->options;
    }
}
