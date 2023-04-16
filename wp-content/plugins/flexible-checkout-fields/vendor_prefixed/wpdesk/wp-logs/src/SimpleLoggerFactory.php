<?php

declare (strict_types=1);
namespace FcfVendor\WPDesk\Logger;

use FcfVendor\Monolog\Handler\HandlerInterface;
use FcfVendor\Monolog\Handler\NullHandler;
use FcfVendor\Monolog\Logger;
use FcfVendor\Monolog\Handler\ErrorLogHandler;
use FcfVendor\WPDesk\Logger\WC\WooCommerceHandler;
final class SimpleLoggerFactory implements \FcfVendor\WPDesk\Logger\LoggerFactory
{
    /** @var Settings */
    private $options;
    /** @var string */
    private $channel;
    /** @var Logger */
    private $logger;
    public function __construct(string $channel, \FcfVendor\WPDesk\Logger\Settings $options = null)
    {
        $this->channel = $channel;
        $this->options = $options ?? new \FcfVendor\WPDesk\Logger\Settings();
    }
    public function getLogger($name = null) : \FcfVendor\Monolog\Logger
    {
        if ($this->logger) {
            return $this->logger;
        }
        $this->logger = new \FcfVendor\Monolog\Logger($this->channel);
        if ($this->options->use_wc_log) {
            if (\function_exists('wc_get_logger')) {
                $this->create_wc_handler();
            } else {
                \add_action('woocommerce_init', [$this, 'create_wc_handler']);
            }
        }
        // Adding WooCommerce logger may have failed, if so add WP by default.
        if ($this->options->use_wp_log || empty($this->logger->getHandlers())) {
            $this->logger->pushHandler($this->get_wp_handler());
        }
        return $this->logger;
    }
    /**
     * @internal
     */
    public function create_wc_handler()
    {
        while (!$this->options->use_wp_log && !empty($this->logger->getHandlers())) {
            $this->logger->popHandler();
        }
        $this->logger->pushHandler(new \FcfVendor\WPDesk\Logger\WC\WooCommerceHandler(\wc_get_logger(), $this->channel));
    }
    private function get_wp_handler() : \FcfVendor\Monolog\Handler\HandlerInterface
    {
        if (\defined('FcfVendor\\WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            return new \FcfVendor\Monolog\Handler\ErrorLogHandler(\FcfVendor\Monolog\Handler\ErrorLogHandler::OPERATING_SYSTEM, $this->options->level);
        }
        return new \FcfVendor\Monolog\Handler\NullHandler();
    }
}
