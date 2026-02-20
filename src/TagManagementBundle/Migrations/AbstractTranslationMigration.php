<?php

namespace Weblizards\TagManagementBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Pimcore\Model\Translation;

class AbstractTranslationMigration extends AbstractMigration
{

    /**
     * @example <code>[
     *  'tag_snippet_management' => [
     *      'de' => 'Tag & Snippet Management',
     *      'en' => 'Tag & Snippet Management',
     * ]</code>
     *
     * @var array[]
     */
    protected array $translations = [];

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Update/Insert Translations';
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function up(Schema $schema): void
    {
        $this->updateTranslations($this->translations);
    }

    /**
     * @throws \Exception
     */
    protected function updateTranslations(array $translations, string $domain = Translation::DOMAIN_DEFAULT, bool $overwrite = false): void
    {
        foreach ($translations as $key => $values) {
            if (!is_array($values)) {
                throw new \Exception('Values for translation must be an array with languages as keys.');
            }
            $translation = Translation::getByKey($key, $domain, true);
            $this->updateTranslation($translation, $values, $domain, $overwrite);
            $translation->save();
        }

    }

    protected function updateTranslation(Translation $object, array $translations, string $domain = Translation::DOMAIN_DEFAULT, bool $overwrite = false): void
    {
        $domain = ucfirst($domain);
        foreach ($translations as $language => $translation) {
            if (!$overwrite) {
                if (empty($translation)) {
                    $this->write($domain . ' translation <error>empty</error>: ' . $object->getKey());
                    continue;
                }
                $current = $object->getTranslation($language);
                if (!empty($current)) {
                    $this->write($domain . ' translation <error>skipped</error>: ' . $object->getKey());
                    continue;
                }
            }
            $object->addTranslation($language, $translation);
            $this->write(
                $domain . ' translation <comment>created/updated</comment>: ' . strtoupper(
                    $language
                ) . ' ' . $object->getKey() . ' = ' . $translation
            );
        }
    }

    /**
     * @param Schema $schema
     *
     * @return void
     */
    public function down(Schema $schema): void
    {
        $this->write('No down migration');
    }

}