<?php declare(strict_types=1);

namespace Sas\BlogModule\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1634379425AddProductAssignment extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1634379425;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
   ALTER TABLE `sas_blog_entries`
   ADD `product_assignment_type` VARCHAR(32) NULL,
ADD `product_stream_id` binary(16) NULL
SQL;

        $connection->executeUpdate($query);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
