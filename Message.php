<?php

namespace Messaging;

use kernel;

class Message extends \Core\Module
{
    protected $options = array();

    public function __construct()
    {
        parent::__construct();
        $options = $this->getModuleValue('options');
        if (is_array($options)) {
            $this->options = $options;
        }
    }

    public function send(string $handler_type_or_class, string $subject, string $content, array $recipients)
    {
        if (empty($recipients)) {
            kernel::log(LOG_NOTICE, 'no recipients for sending message, handler: ' . $handler_type_or_class . ', subject: ' . $subject);
            return false;
        }
        $handler = $this->getHandlerClass($handler_type_or_class);
        if (!$handler->send($subject, $content, $recipients)) {
            kernel::log(LOG_ERR, 'failed to send message, handler: ' . $handler_type_or_class . ', subject: ' . $subject);
            return false;
        }
        return true;
    }

    public function setOption(string $handler, string $key, string $value)
    {
        if (!isset($this->options[$handler])) {
            $this->options[$handler] = array();
        }
        $this->options[$handler][$key] = $value;
    }

    public function getOption(string $handler, string $key, $no_global = false)
    {
        if (isset($this->options[$handler][$key])) {
            return $this->options[$handler][$key];
        } else if (!$no_global && isset($this->options['global'][$key])) {
            return $this->options['global'][$key];
        }
        return null;
    }

    public function setGlobalOption($key, $value)
    {
        $this->setOption('global', $key, $value);
    }

    public function getGlobalOption($key)
    {
        return $this->getOption('global', $key);
    }

    public function setTypeOption($key, $value)
    {
        $this->setOption($this->getType(), $key, $value);
    }

    public function getTypeOption($key, $no_global = false)
    {
        return $this->getOption($this->getType(), $key, $no_global);
    }

    private function getHandlerClass($handler_type_or_class)
    {
        $handler = null;
        try
        {
            $handler_class = $handler_type_or_class[0] === '\\' ? $handler_type_or_class : '\\Messaging\\' . $handler_type_or_class . '\\Message';
            $handler       = new $handler_class($this);
        } catch (Exception $e) {
            /* log error */
            kernel::log(LOG_ERR, 'message handler class not found: ' . $handler);
            return null;
        }
        return $handler;
    }

}
