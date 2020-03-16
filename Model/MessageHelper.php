<?php

/*
 * Copyright 2015 Philipp A. Mohrenweiser <phiamo@gmail.com>
 * All rights reserved
 */

namespace Mopa\Bundle\FeedBundle\Model;

use Mopa\Bundle\FeedBundle\WebSocket\Server\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Class MessageHelper
 * @package Mopa\Bundle\FeedBundle\Model
 */
class MessageHelper
{
    /**
     * @var array
     */
    const dataTypes = [
        'txt',
        'html'
    ];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $templatePrefix;

    /**
     * @param ContainerInterface $container
     * @param $templatePrefix
     * @param LoggerInterface $logger
     */
    public function __construct(ContainerInterface $container, $templatePrefix, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->templatePrefix = $templatePrefix;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    protected static function getDataTypes()
    {
        return array_merge(Connection::dataTypes, self::dataTypes);
    }

    /**
     * Decorate a Message depending on the formats given
     * Take decorator service if needed by message
     * @param Message $message
     * @param array   $formats
     * @return Message
     */
    public function decorate(Message $message, array $formats = [])
    {
        $this->logger->notice("decorate");
        if (null !== $message->getFeedItem()) {
            $message->setData($message->getFeedItem()->getMessageData());
        }

        if ($message->getDecorate()) {
            if($message->getDecoratorService() === 'templating'){
                return $this->render($message, $formats);
            }
            elseif($this->container->has($message->getDecoratorService())) {
                /** @var MessageDecoratorInterface $decorator */
                $decorator = $this->container->get($message->getDecoratorService());
                return $decorator->decorate($message, $formats);
            }
            else{
                $this->container->get('logger')->warning(sprintf('Decorator Service "%s" does not exist', $message->getDecoratorService()));
            }
        }

        return $message;
    }

    /**
     * @param Message $message
     * @param array $formats
     * @param array $parts
     * @return Message
     */
    protected function render(Message $message, array $formats = [], array $parts = ["title", "body"])
    {
        // fallback to render all formats
        if (count($formats) == 0) {
            $formats = self::getDataTypes();
        }

        $data = $message->getData();

        if (isset($data["route"])) {
            $parameters = is_array($data["routeParameters"]) ? $data["routeParameters"] : [];
            $data["url"] = $this->container->get('router')->generate($data["route"], $parameters, Router::ABSOLUTE_URL); //make absolute urls
        }

        foreach ($formats as $format) {
            $partData = [];

            $template = $this->getTemplatePrefix($message) . "message_item.html.twig";

            if(!$this->container->get('templating')->exists($template)){
                $this->container->get('logger')->warning(sprintf('Template %s does not exist', $template));
            }
            else {
                $partData["item"] = $this->container->get('templating')->render($template, ["msg" => $message]);
            }

            $data[$format] = $partData;
        }

        $message->setData($data);

        return $message;
    }

    /**
     * @param Message $message
     * @return string
     */
    public function getTemplatePrefix(Message $message)
    {
        if($prefix = $message->getTemplatePrefix()){
            return $prefix;
        }

        return $this->templatePrefix;
    }
}
