<?php declare(strict_types=1);

namespace Sas\BlogModule\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1634482771BlogProducts extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1634482771;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(
            '
            CREATE TABLE IF NOT EXISTS `sas_blog_product` (
            `product_id` BINARY(16) NOT NULL,
            `product_version_id` BINARY(16) NOT NULL,
            `sas_blog_entries_id` BINARY(16) NOT NULL,
            PRIMARY KEY (`product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        '
        );
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
