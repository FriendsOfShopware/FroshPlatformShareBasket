import template from './frosh-share-basket-list.html.twig';
import './frosh-share-basket-list.html.scss';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('frosh-share-basket-list', {
    template,

    inject: ['repositoryFactory', 'apiContext', 'syncService'],

    mixins: [
        Mixin.getByName('listing')
    ],

    data() {
        return {
            isLoading: false,
            items: null,
            total: 0,
            repository: null,
            sortBy: 'saveCount',
            sortDirection: 'DESC',
            filterSidebarIsOpen: false,
            salesChannelFilters: [],
            internalFilters: {}
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        columns() {
            return [{
                property: 'lineItems.identifier',
                label: this.$t('frosh-share-basket.list.columnProductNumber'),
                allowResize: true,
                primary: true
            }, {
                property: 'salesChannel.name',
                label: this.$t('frosh-share-basket.list.columnProductName'),
                allowResize: true
            }, {
                property: 'saveCount',
                label: this.$t('frosh-share-basket.list.columnProductSaveCount'),
                allowResize: true
            }, {
                property: 'lineItems.quantity',
                label: this.$t('frosh-share-basket.list.columnProductQuantity'),
                allowResize: true
            }];
        },

        languageStore() {
            return this.repositoryFactory.create('language');
        },

        salesChannelStore() {
            return this.repositoryFactory.create('sales_channel');
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            const criteria = new Criteria(1, 100);

            this.salesChannelStore.search(criteria, this.apiContext).then((items) => {
                this.salesChannelFilters = items;
            });

            this.getList();
        },

        handleBooleanFilter(filter) {
            if (!Array.isArray(this[filter.group])) {
                this[filter.group] = [];
            }

            if (!filter.value) {
                this[filter.group] = this[filter.group].filter((x) => { return x !== filter.id; });

                if (this[filter.group].length > 0) {
                    this.internalFilters[filter.group] = Criteria.equalsAny(filter.group, this[filter.group]);
                } else {
                    delete this.internalFilters[filter.group];
                }

                return;
            }

            this[filter.group].push(filter.id);
            this.internalFilters[filter.group] = Criteria.equalsAny(filter.group, this[filter.group]);
        },

        onChange(filter) {
            if (filter === null) {
                filter = [];
            }

            this.handleBooleanFilter(filter);
            this.getList();
        },

        closeContent() {
            if (this.filterSidebarIsOpen) {
                this.$refs.filterSideBar.closeContent();
                this.filterSidebarIsOpen = false;
                return;
            }

            this.$refs.filterSideBar.openContent();
            this.filterSidebarIsOpen = true;
        },

        getList() {
            this.isLoading = true;
            const criteria = new Criteria(this.page, this.limit, this.term);
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));
            criteria.addAssociation('salesChannel');

            Object.values(this.internalFilters).forEach((item) => {
                criteria.addFilter(item);
            });

            return this.syncService.httpClient.post(
                '/frosh/sharebasket/statistics',
                criteria,
                {
                    headers: this.syncService.getBasicHeaders()
                }
            ).then((result) => {
                console.log(result);
                this.items = result.data.data;
                this.total = result.data.meta.total;

                this.isLoading = false;
            }).catch(() => {
                this.isLoading = false;
            });
        }
    }
});
