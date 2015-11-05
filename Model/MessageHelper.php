<?php

/*
 * Copyright 2015 Philipp A. Mohrenweiser <phiamo@gmail.com>
 * All rights reserved
 */

namespace Mopa\Bundle\FeedBundle\Model;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;

//used if installed
use Mopa\Bundle\BooksyncBundle\WebSocket\Server\Connection;

/**
 * Class MessageHelper
 * @package Mopa\Bundle\FeedBundle\Model
 */
class MessageHelper
{
    /**
     * @var array
     */
    public static $dataTypes = [
        'txt',
        'html'
    ];

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
     */
    public function __construct(ContainerInterface $container, $templatePrefix)
    {
        $this->container = $container;
        $this->templatePrefix = $templatePrefix;
    }

    /**
     * @return array
     */
    protected static function getDataTypes()
    {
        if(class_exists('Connection')){
            return array_merge(Connection::$dataTypes, self::$dataTypes);
        }
        return self::$dataTypes;
    }

    /**
     * Decorate a Message depending on the formats given
     *
     * @param Message $message
     * @param array   $formats
     * @return Message
     */
    public function decorate(Message $message, array $formats = [])
    {
        if (null !== $message->getFeedItem()) {
            $message->setData($message->getFeedItem()->getMessageData());
        }
        if ($message->getDecorate()) {
            exit("da");
            return $this->render($message, $formats);
        }

        return $message;
    }

    /**
     *
     * @param Message $message
     * @param array $requestedFormats
     * @param array $parts
     * @return Message
     * @throws \Exception
     */
    protected function render(Message $message, array $requestedFormats = [], array $parts = ["title", "body"])
    {
        if(count($formats = $message->getForceFormats()) == 0) {
            // fallback to render all formats
            if (count($requestedFormats) == 0) {
                $requestedFormats = self::getDataTypes();
            }

            $formats = array_merge($message->getDefaultFormats(), $requestedFormats);
        }

        $data = $message->getData();

        if (isset($data["route"])) {
            $parameters = is_array($data["routeParameters"]) ? $data["routeParameters"] : [];
            $data["url"] = $this->container->get('router')->generate($data["route"], $parameters, Router::ABSOLUTE_URL); //make absolute urls
        }

        foreach ($formats as $format) {
            $partData = [];

            foreach ($parts as $part) {
                if($message->getEvent() == null || $message->getEvent() == '') {
                    throw new \Exception('No Message Event set for ' . $message->getId() . var_export($message->getData(), true));
                }

                $template = $this->templatePrefix . $message->getEvent() . "." . $part . "." . $format . '.twig';

                if(!$this->container->get('templating')->exists($template)){
                    $this->container->get('logger')->warning(sprintf('Template %s does not exist', $template));
                }
                else {
                    $this->container->get('logger')->debug(sprintf('Rendering template %s with %', $template, $data));
                    $partData[$part] = $this->container->get('templating')->render($template, ["msg" => $message]);
                }
            }

            $template = $this->templatePrefix . "message_item.html.twig";

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
}
