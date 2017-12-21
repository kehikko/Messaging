<?php

namespace Messaging;

abstract class MessageHandler extends \Core\Module
{
	abstract public function getType();
	abstract public function send(string $subject, string $content, array $recipients);

	protected $master = null;

	public function __construct($master)
	{
		parent::__construct();
		$this->master = $master;
	}

	public function getOption($key, $no_global = false)
	{
		return $this->master->getOption($this->getType(), $key, $no_global);
	}
}
