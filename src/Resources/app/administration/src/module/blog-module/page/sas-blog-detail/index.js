import template from './sas-blog-detail.html.twig';
import './sas-blog-detail.scss';
import slugify from '@slugify';

const { Component, Mixin } = Shopware;
const Criteria = Shopware.Data.Criteria;
const { mapPropertyErrors } = Shopware.Component.getComponentHelper();
const ShopwareError = Shopware.Classes.ShopwareError;


Component.register('sas-blog-detail', {
    template,

    inject: ['repositoryFactory', 'systemConfigApiService'],

    mixins: [Mixin.getByName('notification'),'placeholder'],

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    props: {
        blogId: {
            type: String,
            required: false,
            default() {
                return null;
            },
        },
        isLoading: {
            type: Boolean,
            required: true,
        }
    },

    data() {
        return {
            blog: null,
            maximumMetaTitleCharacter: 160,
            maximumMetaDescriptionCharacter: 160,
            configOptions: {},
            isLoading: true,
            processSuccess: false,
            fileAccept: 'image/*',
            moduleData: this.$route.meta.$module,
            isProVersion: false,
            productStreamFilter: null,
            productStreamInvalid: false,
            manualAssignedProductsCount: 0,
        };
    },

    created() {
        this.createdComponent();
    },

    watch: {
        'blog.active': function () {
            return this.blog.active ? 1 : 0;
        },
        'blog.title': function (value) {
            if (typeof value !== 'undefined') {
                this.blog.slug = slugify(value, {
                    lower: true
                });
            }
        },
        blogId() {
            this.createdComponent();
        },
        'blog.productStreamId'(id) {
            if (!id) {
                this.productStreamFilter = null;
                return;
            }
            this.loadProductStreamPreview();
        },
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('sas_blog_entries');
        },

        mediaItem() {
            return this.blog !== null ? this.blog.media : null;
        },

        mediaRepository() {
            return this.repositoryFactory.create('media');
        },

        backPath() {
            if (this.$route.query.ids && this.$route.query.ids.length > 0) {
                return {
                    name: 'blog.module.index',
                    query: {
                        ids: this.$route.query.ids,
                        limit: this.$route.query.limit,
                        page: this.$route.query.page
                    }
                };
            }
            return { name: 'blog.module.index' };
        },

        isCreateMode() {
            return this.$route.name === 'blog.module.create';
        },

        ...mapPropertyErrors(
            'blog', [
                'title',
                'slug',
                'teaser',
                'authorId',
                'publishedAt',
                'productStreamId',
                'productAssignmentType',
            ]
        ),

        productStreamRepository() {
            return this.repositoryFactory.create('product_stream');
        },

        productColumns() {
            return [
                {
                    property: 'name',
                    label: this.$tc('sw-category.base.products.columnNameLabel'),
                    dataIndex: 'name',
                    routerLink: 'sw.product.detail',
                    sortable: false,
                }, {
                    property: 'manufacturer.name',
                    label: this.$tc('sw-category.base.products.columnManufacturerLabel'),
                    routerLink: 'sw.manufacturer.detail',
                    sortable: false,
                },
            ];
        },

        manufacturerColumn() {
            return 'column-manufacturer.name';
        },

        nameColumn() {
            return 'column-name';
        },

        productCriteria() {
            return (new Criteria(1, 10))
                .addAssociation('options.group')
                .addAssociation('manufacturer')
                .addFilter(Criteria.equals('parentId', null));
        },

        productStreamInvalidError() {
            if (this.productStreamInvalid) {
                return new ShopwareError({
                    code: 'PRODUCT_STREAM_INVALID',
                    detail: this.$tc('sw-category.base.products.dynamicProductGroupInvalidMessage'),
                });
            }
            return null;
        },

        productAssignmentTypes() {
            return [
               /* {
                    value: 'product',
                    label: this.$tc('sw-category.base.products.productAssignmentTypeManualLabel'),
                },*/
                {
                    value: 'product_stream',
                    label: this.$tc('sw-category.base.products.productAssignmentTypeStreamLabel'),
                },
            ];
        },

        dynamicProductGroupHelpText() {
            const link = {
                name: 'sw.product.stream.index',
            };

            const helpText = this.$tc('sw-category.base.products.dynamicProductGroupHelpText.label', 0, {
                link: `<sw-internal-link
                           :router-link=${JSON.stringify(link)}
                           :inline="true">
                           ${this.$tc('sw-category.base.products.dynamicProductGroupHelpText.linkText')}
                       </sw-internal-link>`,
            });

            try {
                // eslint-disable-next-line no-new
                new URL(this.$tc('sw-category.base.products.dynamicProductGroupHelpText.videoUrl'));
            } catch {
                return helpText;
            }

            return `${helpText}
                    <br>
                    <sw-external-link
                        href="${this.$tc('sw-category.base.products.dynamicProductGroupHelpText.videoUrl')}">
                        ${this.$tc('sw-category.base.products.dynamicProductGroupHelpText.videoLink')}
                    </sw-external-link>`;
        },
    },

    methods: {
        async createdComponent() {
            if(this.isCreateMode) {
                if (Shopware.Context.api.languageId !== Shopware.Context.api.systemLanguageId) {
                    Shopware.State.commit('context/setApiLanguageId', Shopware.Context.api.languageId)
                }

                if (!Shopware.State.getters['context/isSystemDefaultLanguage']) {
                    Shopware.State.commit('context/resetLanguageToDefault');
                }
            }

            await Promise.all([
                this.getPluginConfig(),
                this.getBlog()
            ]);

            if (!this.blog.productStreamId) {
                return;
            }
            this.loadProductStreamPreview();

            this.isLoading = false;
        },

        async getPluginConfig() {
            const config = await this.systemConfigApiService.getValues('SasBlogModule.config');

            this.maximumMetaTitleCharacter = config['SasBlogModule.config.maximumMetaTitleCharacter'];
            this.maximumMetaDescriptionCharacter = config['SasBlogModule.config.maximumMetaDescriptionCharacter'];
        },

        async getBlog() {
            if(!this.blogId) {
                this.blog = this.repository.create(Shopware.Context.api);

                return;
            }

            const criteria = new Criteria();
            criteria.addAssociation('blogCategories');

            return this.repository
                .get(this.blogId, Shopware.Context.api, criteria)
                .then((entity) => {
                    this.blog = entity;

                    return Promise.resolve();
                });
        },

        async changeLanguage() {
            await this.getBlog();
        },

        onClickSave() {
            if (!this.blog.blogCategories || this.blog.blogCategories.length === 0) {
                this.createNotificationError({
                    message: this.$tc('sas-blog.detail.notification.error.missingCategory')
                });

                return;
            }

            this.isLoading = true;

            this.repository
                .save(this.blog, Shopware.Context.api)
                .then(() => {
                    this.isLoading = false;
                    this.$router.push({ name: 'blog.module.detail', params: {id: this.blog.id} });

                    this.createNotificationSuccess({
                        title: this.$tc('sas-blog.detail.notification.save-success.title'),
                        message: this.$tc('sas-blog.detail.notification.save-success.text')
                    });
                })
                .catch(exception => {
                    this.isLoading = false;
                });
        },

        onCancel() {
            this.$router.push({ name: 'blog.module.index' });
        },

        onSetMediaItem({ targetId }) {
            this.mediaRepository.get(targetId, Shopware.Context.api).then((updatedMedia) => {
                this.blog.mediaId = targetId;
                this.blog.media = updatedMedia;
            });
        },

        setMedia([mediaItem], mediaAssoc) {
            this.blog.mediaId = mediaItem.id;
            this.blog.media = mediaItem;
        },

        onRemoveMediaItem() {
            this.blog.mediaId = null;
            this.blog.media = null;
        },

        onMediaDropped(dropItem) {
            this.onSetMediaItem({ targetId: dropItem.id });
        },

        openMediaSidebar() {
            this.$parent.$parent.$parent.$parent.$refs.mediaSidebarItem.openContent();
        },

        loadProductStreamPreview() {
            this.productStreamRepository.get(this.blog.productStreamId)
                .then((response) => {
                    this.productStreamFilter = response.apiFilter;
                    this.productStreamInvalid = response.invalid;
                }).catch(() => {
                this.productStreamFilter = null;
                this.productStreamInvalid = true;
            });
        },

        onPaginateManualProductAssignment(assignment) {
            this.manualAssignedProductsCount = assignment.total;
        },
    }
});
