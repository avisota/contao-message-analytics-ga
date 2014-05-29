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

/**
 * Fields
 */
$GLOBALS['TL_LANG']['orm_avisota_message']['ga_enable'] = array(
	'Enable google analytics',
	'Enable google analytics tracking for this mailing.'
);
$GLOBALS['TL_LANG']['orm_avisota_message']['ga_campaign'] = array(
	'Campaign Name',
	'Used for keyword analysis. Identify a specific product promotion or strategic campaign. Default: <em>The mailing subject</em>. Example: <code>utm_campaign=spring_sale</code>'
);
$GLOBALS['TL_LANG']['orm_avisota_message']['ga_term'] = array(
	'Campaign Term ',
	'Used for paid search. Use utm_term to note the keywords for this ad. Example: <code>utm_term=running+shoes</code>c'
);


/**
 * Legends
 */
$GLOBALS['TL_LANG']['orm_avisota_message']['google_analytics_legend'] = 'Google Analytics';

/**
 * Reference
 */
$GLOBALS['TL_LANG']['orm_avisota_message']['ga_campain_title'] = 'Campaign: %s';
