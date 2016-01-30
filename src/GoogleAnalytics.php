<?php

/**
 * Avisota newsletter and mailing system
 * Copyright © 2016 Sven Baumann
 *
 * PHP version 5
 *
 * @copyright  way.vision 2016
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @package    avisota/contao-message-element-article
 * @license    LGPL-3.0+
 * @filesource
 */

namespace Avisota\Contao\Message\Analytics\GA;

use Avisota\Contao\Core\Service\SuperglobalsService;
use Avisota\Contao\Entity\Message;
use Avisota\Contao\Message\Core\Event\AvisotaMessageEvents;
use Avisota\Contao\Message\Core\Event\PostRenderMessageContentEvent;
use Avisota\Contao\Message\Core\Event\RenderMessageEvent;
use Contao\Doctrine\ORM\DataContainer\General\EntityModel;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
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
            AvisotaMessageEvents::POST_RENDER_MESSAGE_CONTENT => array(
                array('injectGA', -500),
            ),

            GetOperationButtonEvent::NAME => array(
                array('prepareButton'),
            ),

            BuildDataDefinitionEvent::NAME => array(
                array('injectGALegend'),
            ),
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
        $base    = \Environment::get('base');

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

        /** @var \Pimple $container */
        global $container;
        /** @var SuperglobalsService $superGlobals */
        $superGlobals = $container['avisota.superglobals'];

        /** @var EntityModel $model */
        $model = $event->getModel();
        /** @var Message $message */
        $message = $model->getEntity();

        if ($message->getGaEnable()) {
            $title = $message->getGaCampaign() ? $message->getGaCampaign() : $message->getSubject();
            $title = sprintf($superGlobals->getLanguage('orm_avisota_message/ga_campain_title'), $title);

            $generateHtmlEvent = new GenerateHtmlEvent(
                'assets/avisota/message-analytics-ga/images/analytics_icon.png',
                $title,
                sprintf('title="%s"', htmlentities($title, ENT_QUOTES, 'UTF-8'))
            );
            $event->getEnvironment()->getEventDispatcher()->dispatch(ContaoEvents::IMAGE_GET_HTML, $generateHtmlEvent);

            $event->setHtml($generateHtmlEvent->getHtml());
        } else {
            $event->setHtml('');
        }
    }

    /**
     * @param BuildDataDefinitionEvent $event
     */
    public function injectGALegend(BuildDataDefinitionEvent $event)
    {
        if ($event->getContainer()->getName() != 'orm_avisota_message') {
            return;
        }

        $container = $event->getContainer();

        $palettesDefinition = $container->getPalettesDefinition();
        $palettes = $palettesDefinition->getPalettes();
        $gAPalette = null;
        foreach($palettes as $palette) {
            if ($palette->getName() === '__google_analytics__') {
                $gAPalette = $palette;
            }
        }

        if (!$gAPalette) {
            return;
        }

        foreach ($palettes as $palette) {
            if ($palette->getName() === '__google_analytics__') {
                continue;
            }
            foreach($gAPalette->getLegends() as $gALegend) {
                $palette->addLegend(clone $gALegend);
            }
        }
    }
}
