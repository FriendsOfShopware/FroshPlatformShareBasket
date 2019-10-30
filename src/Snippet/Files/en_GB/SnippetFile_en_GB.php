<?php declare(strict_types=1);

namespace Frosh\ShareBasket\Snippet\Files\en_GB;

use Shopware\Core\Framework\Snippet\Files\SnippetFileInterface;

class SnippetFile_en_GB implements SnippetFileInterface
{
    public function getName(): string
    {
        return 'storefront.en-GB';
    }

    public function getPath(): string
    {
        return __DIR__ . '/storefront.en-GB.json';
    }

    public function getIso(): string
    {
        return 'en-GB';
    }

    public function getAuthor(): string
    {
        return 'Friends of Shopware';
    }

    public function isBase(): bool
    {
        return true;
    }
}
