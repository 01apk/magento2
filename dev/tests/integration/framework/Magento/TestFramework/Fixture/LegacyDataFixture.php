<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

use PHPUnit\Framework\Exception;

/**
 * File based data fixture
 */
class LegacyDataFixture implements RevertibleDataFixtureInterface
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @param LegacyDataFixturePathResolver $fixturePathResolver
     * @param string $filePath
     */
    public function __construct(
        LegacyDataFixturePathResolver $fixturePathResolver,
        string $filePath
    ) {
        $this->filePath = $fixturePathResolver->resolve($filePath);
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?array
    {
        $this->execute($this->filePath);
        return null;
    }

    /**
     * @inheritdoc
     */
    public function revert(array $data = []): void
    {
        $fileInfo = pathinfo($this->filePath);
        $extension = '';
        if (isset($fileInfo['extension'])) {
            $extension = '.' . $fileInfo['extension'];
        }
        $rollbackScript = $fileInfo['dirname'] . DIRECTORY_SEPARATOR . $fileInfo['filename'] . '_rollback' . $extension;
        if (file_exists($rollbackScript)) {
            $this->execute($rollbackScript);
        }
    }

    /**
     * Execute file
     *
     * @param string $filePath
     */
    private function execute(string $filePath): void
    {
        try {
            require $filePath;
        } catch (\Exception $e) {
            throw new Exception(
                sprintf(
                    "Error in fixture: %s.\n %s\n %s",
                    $filePath,
                    $e->getMessage(),
                    $e->getTraceAsString()
                ),
                500,
                $e
            );
        }
    }
}
