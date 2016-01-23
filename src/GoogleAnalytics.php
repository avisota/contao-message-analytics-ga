<?php

/**
 * Avisota newsletter and mailing system
 * Copyright (C) 2013 Tristan Lins
 *
 * PHP version 5
 *
 * @copyright  bit3 UG 2013
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @package    avisota/contao-message-element-article
 * @license    LGPL-3.0+
 * @filesource
 */

namespace Avisota\Contao\Message\Analytics\GA;

use Avisota\Contao\Entity\Message;
use Avisota\Contao\Message\Core\Event\AvisotaMessageEvents;
use Avisota\Contao\Message\Core\Event\PostRenderMessageContentEvent;
use Avisota\Contao\Message\Core\Event\PostRenderMessageTemplateEvent;
use Avisota\Contao\Message\Core\Event\RenderMessageEvent;
use Avisota\Contao\Message\Core\Template\MutablePreRenderedMessageTemplate;
use Contao\Doctrine\ORM\DataContainer\General\EntityModel;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class GoogleAnalytics
 *
 * @package Avisota\Contao\Message\Analytics\GA
 */
class GoogleAnalytics implements EventSubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            AvisotaMessageEvents::POST_RENDER_MESSAGE_CONTENT                   => array('injectGA', -500),
            GetOperationButtonEvent::NAME  => 'prepareButton',
        );
    }

    /**
     * Inject the GA parameters to each url in the newsletter.
     *
     * @param PostRenderMessageContentEvent|RenderMessageEvent $event
     */
    public function injectGA(PostRenderMessageContentEvent $event)
    {
        $message = $event->getMessage();

        if (!$message->getGaEnable()) {
            return;
        }

        $content = $event->getContent();
        $query   = http_build_query(
            array(
                'utm_source'   => 'Newsletter ' . $message->getSubject(),
                'utm_medium'   => 'E-Mail',
                'utm_campaign' => $message->getGaCampaign() ?: $message->getSubject(),
                'utm_term'     => $message->getGaTerm(),
            )
        );
        $base    = \Environment::getInstance()->base;

        $content = preg_replace_callback(
            '~href=(["\'])(.*)\1~U',
            function ($matches) use ($query, $base) {
                $url = $matches[2];

                if (preg_match('~^\w+:~', $url) && substr($url, 0, strlen($base)) != $base) {
                    return $matches[0];
                }

                $parts = parse_url($url);

                if ($parts['query']) {
                    $parts['query'] .= '&' . $query;
                } else {
                    $parts['query'] = $query;
                }

                $url = $parts['scheme'] . '://';
                if ($parts['user']) {
                    $url .= $parts['user'];

                    if ($parts['pass']) {
                        $url .= ':' . $parts['pass'];
                    }

                    $url .= '@';
                }
                $url .= $parts['host'];
                if ($parts['port']) {
                    $url .= ':' . $parts['port'];
                }
                $url .= $parts['path'];
                $url .= '?' . $parts['query'];
                if ($parts['fragment']) {
                    $url .= '#' . $parts['fragment'];
                }

                return sprintf('href="%s"', htmlentities($url, ENT_QUOTES, 'UTF-8'));
            },
            $content
        );

        $event->setContent($content);
    }

    /**
     * @param GetOperationButtonEvent $event
     */
    public function prepareButton(GetOperationButtonEvent $event)
    {
        if ($event->getCommand()->getName() != 'ga_enabled') {
            return;
        }

        /** @var EntityModel $model */
        $model = $event->getModel();
        /** @var Message $message */
        $message = $model->getEntity();

        if ($message->getGaEnable()) {
            $title = $message->getGaCampaign() ? $message->getGaCampaign() : $message->getSubject();
            $title = sprintf($GLOBALS['TL_LANG']['orm_avisota_message']['ga_campain_title'], $title);

            $generateHtmlEvent = new GenerateHtmlEvent(
                'assets/avisota/message-analytics-ga/images/analytics_icon.png',
                $title,
                sprintf('title="%s"', htmlentities($title, ENT_QUOTES, 'UTF-8'))
            );
            $event->getDispatcher()->dispatch(ContaoEvents::IMAGE_GET_HTML, $generateHtmlEvent);

            $event->setHtml($generateHtmlEvent->getHtml());
        } else {
            $event->setHtml('');
        }
    }
}
