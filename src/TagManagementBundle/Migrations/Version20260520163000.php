<?php

namespace Weblizards\TagManagementBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Pimcore\Model\Translation;

class Version20260520163000 extends AbstractTranslationMigration
{
    /**
     * @var array[]
     */
    protected array $translations = [
        'wl_tagmanagement.invalid_tag_name' => [
            'de' => 'Ungültiger Tag-Name',
            'en' => 'Invalid tag name',
        ],
        'wl_tagmanagement.tag_not_found' => [
            'de' => 'Tag wurde nicht gefunden',
            'en' => 'Tag not found',
        ],
    ];

    public function getDescription(): string
    {
        return 'Add missing admin error translations for tag management';
    }

    public function up(Schema $schema): void
    {
        $this->updateTranslations($this->translations, Translation::DOMAIN_ADMIN, true);
    }
}
