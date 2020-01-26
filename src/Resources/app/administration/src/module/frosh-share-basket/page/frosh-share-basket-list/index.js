import template from './frosh-share-basket-list.html.twig';
import './frosh-share-basket-list.html.scss';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('frosh-share-basket-list', {
    template,

    inject: ['repositoryFactory', 'syncService', 'localeToLanguageService'],

    mixins: [
        Mixin.getByName('listing')
    ],

    data() {
        return {
            isLoading: false,
            items: null,
            languageId: localStorage.getItem('sw-admin-current-language'),
            total: 0,
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
                property: 'productName',
                label: 'frosh-share-basket.list.columnProductName',
                allowResize: true,
                primary: true
            }, {
                property: 'productNumber',
                label: 'frosh-share-basket.list.columnProductNumber',
                allowResize: true
            }, {
                property: 'saveCount',
                label: 'frosh-share-basket.list.columnProductSaveCount',
                allowResize: true
            }, {
                property: 'totalQuantity',
                label: 'frosh-share-basket.list.columnProductQuantity',
                allowResize: true
            }];
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
            const locale = localStorage.getItem('sw-admin-locale');

            this.salesChannelStore.search(criteria, Shopware.Context.api).then((items) => {
                this.salesChannelFilters = items;
            });

            this.localeToLanguageService.localeToLanguage(locale).then((languageId) => {
                this.languageId = languageId;
                this.getList();
            });
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
            criteria.languageId = this.languageId;
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));

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
                this.items = result.data.data;
                this.total = result.data.total;
                this.isLoading = false;
            }).catch(() => {
                this.isLoading = false;
            });
        }
    }
});
