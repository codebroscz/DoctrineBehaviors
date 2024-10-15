<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Translatable;

use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Exception\ShouldNotHappenException;

trait TranslationMethodsTrait
{
    public static function getTranslatableEntityClass(): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr(static::class, 0, -11, 'UTF-8'); // MB is much faster
        }

        if (! extension_loaded('iconv')) {
            throw new ShouldNotHappenException(
                __METHOD__ . '() requires extension ICONV or MBSTRING, neither is loaded.'
            );
        }

        // By default, the translatable class has the same name but without the "Translation" suffix
        $part = \iconv_substr(static::class, 0, -11, 'UTF-8');

        if ($part === false) {
            throw new ShouldNotHappenException(__METHOD__ . '() failed to extract the translatable class name.');
        }

        return $part;
    }

    /**
     * Sets entity, that this translation should be mapped to.
     */
    public function setTranslatable(TranslatableInterface $translatable): void
    {
        $this->translatable = $translatable;
    }

    /**
     * Returns entity, that this translation is mapped to.
     */
    public function getTranslatable(): TranslatableInterface
    {
        return $this->translatable;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function isEmpty(): bool
    {
        foreach (get_object_vars($this) as $var => $value) {
            if (in_array($var, ['id', 'translatable', 'locale'], true)) {
                continue;
            }

            if (is_string($value) && strlen(trim($value)) > 0) {
                return false;
            }

            if (! empty($value)) {
                return false;
            }
        }

        return true;
    }
}
