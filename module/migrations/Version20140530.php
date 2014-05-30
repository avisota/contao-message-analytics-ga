<?php

/**
 * Avisota newsletter and mailing system
 * Copyright (C) 2013 Tristan Lins
 *
 * PHP version 5
 *
 * @copyright  bit3 UG 2013
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @package    avisota/contao-message-element-image
 * @license    LGPL-3.0+
 * @filesource
 */

namespace DoctrineMigrations\AvisotaMessageAnalyticsGa;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140530 extends AbstractMigration
{
	public function up(Schema $schema)
	{
		if (!$schema->hasTable('orm_avisota_message')) {
			return;
		}

		$table = $schema->getTable('orm_avisota_message');

		if ($table->hasColumn('ga_enable')) {
			$this->addSql('ALTER TABLE orm_avisota_message CHANGE ga_enable gaEnable TINYINT(1) DEFAULT NULL');
		}
		if ($table->hasColumn('ga_campaign')) {
			$this->addSql('ALTER TABLE orm_avisota_message CHANGE ga_campaign gaCampaign VARCHAR(255) DEFAULT NULL');
		}
		if ($table->hasColumn('ga_term')) {
			$this->addSql('ALTER TABLE orm_avisota_message CHANGE ga_term gaTerm VARCHAR(255) DEFAULT NULL');
		}
	}

	public function down(Schema $schema)
	{
	}
}
