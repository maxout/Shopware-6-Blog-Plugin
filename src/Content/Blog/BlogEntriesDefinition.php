<?php declare(strict_types=1);

namespace Sas\BlogModule\Content\Blog;

use Sas\BlogModule\Content\Blog\Aggregate\BlogCategoryMappingDefinition;
use Sas\BlogModule\Content\Blog\BlogTranslation\BlogTranslationDefinition;
use Sas\BlogModule\Content\BlogAuthor\BlogAuthorDefinition;
use Sas\BlogModule\Content\BlogCategory\BlogCategoryDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Sas\BlogModule\Content\Blog\Aggregate\ProductBlogDefinition;
use Shopware\Core\Content\ProductStream\ProductStreamDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ReverseInherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class BlogEntriesDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'sas_blog_entries';
    public const PRODUCT_ASSIGNMENT_TYPE_PRODUCT_STREAM = 'product_stream';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return BlogEntriesEntity::class;
    }

    public function getCollectionClass(): string
    {
        return BlogEntriesCollection::class;
    }

    public function getDefaults(): array
    {
        return [
            'publishedAt' => new \DateTime(),
            'productAssignmentType' => self::PRODUCT_ASSIGNMENT_TYPE_PRODUCT_STREAM,];

    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey(), new ApiAware()),
            (new BoolField('active', 'active'))->addFlags(new ApiAware()),
            (new BoolField('detail_teaser_image', 'detailTeaserImage'))->addFlags(new ApiAware()),
            (new StringField('product_assignment_type', 'productAssignmentType'))->addFlags(new ApiAware(), new Required()),
            new FkField('product_stream_id', 'productStreamId', ProductStreamDefinition::class),
            new ManyToOneAssociationField('productStream', 'product_stream_id', ProductStreamDefinition::class, 'id', false),

            new FkField('media_id', 'mediaId', MediaDefinition::class),
            (new FkField('author_id', 'authorId', BlogAuthorDefinition::class))->addFlags(new Required()),
            (new OneToOneAssociationField('media', 'media_id', 'id', MediaDefinition::class, true))->addFlags(new ApiAware()),
            (new ManyToManyAssociationField('products', ProductDefinition::class, ProductBlogDefinition::class, 'sas_blog_entries_id', 'product_id'))->addFlags(new CascadeDelete(), new ReverseInherited('sas_blog_entries')),


            (new TranslatedField('title'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new TranslatedField('slug'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new TranslatedField('teaser'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new TranslatedField('metaTitle'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new TranslatedField('metaDescription'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING)),
            (new TranslatedField('content'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING)),
            (new TranslatedField('customFields'))->addFlags(new ApiAware()),

            (new DateField('published_at', 'publishedAt'))->addFlags(new Required(), new ApiAware()),

            (new TranslationsAssociationField(BlogTranslationDefinition::class, 'sas_blog_entries_id'))->addFlags(new Required()),

            (new ManyToManyAssociationField('blogCategories', BlogCategoryDefinition::class, BlogCategoryMappingDefinition::class, 'sas_blog_entries_id', 'sas_blog_category_id'))->addFlags(new CascadeDelete(), new ApiAware(), new SearchRanking(SearchRanking::ASSOCIATION_SEARCH_RANKING)),
            (new ManyToOneAssociationField('author', 'author_id', BlogAuthorDefinition::class, 'id', false))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::ASSOCIATION_SEARCH_RANKING))
        ]);
    }
}
