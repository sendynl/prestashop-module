<?php

declare(strict_types=1);

$config = new class extends PrestaShop\CodingStandards\CsFixer\Config {
    public function getRules(): array
    {
        return [
            ...parent::getRules(),
            'declare_strict_types' => true,
        ];
    }
};

/** @var Symfony\Component\Finder\Finder $finder */
$finder = $config->setUsingCache(true)->getFinder();
$finder->in(__DIR__)->exclude('vendor');

return $config;
