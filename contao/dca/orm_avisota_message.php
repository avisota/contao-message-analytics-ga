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
 * Table orm_avisota_message
 * Entity Avisota\Contao:Message
 */
$GLOBALS['TL_DCA']['orm_avisota_message']['config']['onload_callback'][] = function (
	\ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat $dc
) {
	$dataDefinition     = $dc->getEnvironment()->getDataDefinition();
	$palettesDefinition = $dataDefinition->getPalettesDefinition();

	if (!$palettesDefinition->hasPaletteByName('__google_analytics__')) {
		return;
	}

	$legends = $palettesDefinition->getPaletteByName('__google_analytics__')->getLegends();

	$palettes = $palettesDefinition->getPalettes();
	foreach ($palettes as $palette) {
		foreach ($legends as $legend) {
			$palette->addLegend(clone $legend);
		}
	}
};

$GLOBALS['TL_DCA']['orm_avisota_message']['metapalettes']['__google_analytics__'] = array(
	'google_analytics' => array('gaEnable'),
);
$GLOBALS['TL_DCA']['orm_avisota_message']['metasubpalettes']['gaEnable']         = array('gaCampaign', 'gaTerm');

$GLOBALS['TL_DCA']['orm_avisota_message']['list']['operations'] = array_merge(
	array(
		'ga_enabled' => array(
			'label' => &$GLOBALS['TL_LANG']['orm_avisota_message']['gaEnabled'],
			'icon'  => 'assets/avisota/message-analytics-ga/images/analytics_icon.png',
		),
	),
	$GLOBALS['TL_DCA']['orm_avisota_message']['list']['operations']
);

$GLOBALS['TL_DCA']['orm_avisota_message']['fields']['gaEnable']   = array
(
	'label'     => &$GLOBALS['TL_LANG']['orm_avisota_message']['gaEnable'],
	'default'   => false,
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => array(
		'submitOnChange' => true,
		'tl_class'       => 'm12',
	),
);
$GLOBALS['TL_DCA']['orm_avisota_message']['fields']['gaCampaign'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['orm_avisota_message']['gaCampaign'],
	'default'   => false,
	'exclude'   => true,
	'inputType' => 'text',
	'eval'      => array(
		'tl_class' => 'w50',
	),
);
$GLOBALS['TL_DCA']['orm_avisota_message']['fields']['gaTerm']     = array
(
	'label'     => &$GLOBALS['TL_LANG']['orm_avisota_message']['gaTerm'],
	'default'   => false,
	'exclude'   => true,
	'inputType' => 'text',
	'eval'      => array(
		'tl_class' => 'w50',
	),
);