<?php declare(strict_types=1);
/*
 * @Copyright (c) 2021. hbee@maxout
 * @author Heiko Bee <hb@maxout.de>
 * @package Shopware
 * @subpackage Plugins
 * @creation_date 17.10.2021
 * @version 1.0.0
 */

namespace Sas\BlogModule\Content\Blog\Aggregate;

use Sas\BlogModule\Content\Blog\BlogEntriesDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;

class ProductBlogDefinition extends MappingEntityDefinition
{
    public const ENTITY_NAME = 'sas_blog_product';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function isVersionAware(): bool
    {
        return true;
    }

    public function since(): ?string
    {
        return '6.4.5.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new PrimaryKey(), new Required()),

            (new FkField('sas_blog_entries_id', 'blogEntriesId', BlogEntriesDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new ReferenceVersionField(BlogEntriesDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class, 'id', false),
            new ManyToOneAssociationField('sas_blog_entries', 'sas_blog_entries_id', BlogEntriesDefinition::class, 'id', false),
        ]);
    }
}
