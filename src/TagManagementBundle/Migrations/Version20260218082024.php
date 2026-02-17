<?php

namespace Weblizards\TagManagementBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;

class Version20260218082024 extends AbstractTranslationMigration
{
    /**
     * @var array[]
     */
    protected array $translations = [
        'tag_snippet_management' => [
            'de' => 'Tag & Snippet Management',
            'en' => 'Tag & Snippet Management',
        ]
    ];

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Add translations for tag & snippet management';
    }
}
