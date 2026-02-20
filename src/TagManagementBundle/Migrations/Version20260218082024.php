<?php

namespace Weblizards\TagManagementBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;

class Version20260218082024 extends AbstractTranslationMigration
{
    /**
     * @var array[]
     */
    protected array $translations = [
        'wl_tagmanagement.tag_snippet_management' => [
            'de' => 'Tag & Snippet Management',
            'en' => 'Tag & Snippet Management',
        ],
        'wl_tagmanagement.save' => [
            'de' => 'Speichern',
            'en' => 'Save',
        ],
        'wl_tagmanagement.tags' => [
            'de' => 'Tags',
            'en' => 'Tags',
        ],
        'wl_tagmanagement.name' => [
            'de' => 'Name',
            'en' => 'Name',
        ],
        'wl_tagmanagement.value' => [
            'de' => 'Wert',
            'en' => 'Value',
        ],
        'wl_tagmanagement.parameters' => [
            'de' => 'Parameter',
            'en' => 'Parameters',
        ],
        'wl_tagmanagement.description' => [
            'de' => 'Beschreibung',
            'en' => 'Description',
        ],
        'wl_tagmanagement.temporarily_disabled' => [
            'de' => 'Vorübergehend deaktiviert',
            'en' => 'Temporarily disabled',
        ],
        'wl_tagmanagement.conditions' => [
            'de' => 'Bedingungen',
            'en' => 'Conditions',
        ],
        'wl_tagmanagement.site' => [
            'de' => 'Seite',
            'en' => 'Site',
        ],
        'wl_tagmanagement.url_pattern' => [
            'de' => 'URL Muster',
            'en' => 'URL Pattern',
        ],
        'wl_tagmanagement.http_method' => [
            'de' => 'HTTP Methode',
            'en' => 'HTTP Method',
        ],
        'wl_tagmanagement.any' => [
            'de' => 'beliebig',
            'en' => 'any',
        ],
        'wl_tagmanagement.matching_text' => [
            'de' => 'Passender Text',
            'en' => 'Matching Text',
        ],
        'wl_tagmanagement.code' => [
            'de' => 'Code',
            'en' => 'Code',
        ],
        'wl_tagmanagement.element_css_selector' => [
            'de' => 'Element CSS Selektor',
            'en' => 'Element CSS Selector',
        ],
        'wl_tagmanagement.insert_position' => [
            'de' => 'Einfügeposition',
            'en' => 'Insert Position',
        ],
        'wl_tagmanagement.beginning' => [
            'de' => 'Anfang',
            'en' => 'Beginning',
        ],
        'wl_tagmanagement.end' => [
            'de' => 'Ende',
            'en' => 'End',
        ],
        'wl_tagmanagement.enabled_in_editmode' => [
            'de' => 'Im Bearbeitungsmodus aktiviert',
            'en' => 'Enabled in editmode',
        ],
        'wl_tagmanagement.expiry' => [
            'de' => 'Ablauf',
            'en' => 'Expiry',
        ],
        'wl_tagmanagement.success' => [
            'de' => 'Erfolg',
            'en' => 'Success',
        ],
        'wl_tagmanagement.saved_successfully' => [
            'de' => 'Erfolgreich gespeichert',
            'en' => 'Saved successfully',
        ],
        'wl_tagmanagement.add' => [
            'de' => 'Hinzufügen',
            'en' => 'Add',
        ],
        'wl_tagmanagement.delete' => [
            'de' => 'Löschen',
            'en' => 'Delete',
        ],
        'wl_tagmanagement.enter_the_name_of_the_new_item' => [
            'de' => 'Geben Sie den Namen des neuen Eintrags ein',
            'en' => 'Enter the name of the new item',
        ],
        'wl_tagmanagement.name_already_in_use' => [
            'de' => 'Name wird bereits verwendet',
            'en' => 'Name already in use',
        ],
        'wl_tagmanagement.failed_to_create_new_item' => [
            'de' => 'Neuer Eintrag konnte nicht erstellt werden',
            'en' => 'Failed to create new item',
        ],
    ];

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Add translations for tag & snippet management';
    }
}
