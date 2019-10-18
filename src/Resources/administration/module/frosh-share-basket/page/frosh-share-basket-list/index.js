import template from './frosh-share-basket-list.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('frosh-share-basket-list', {
    template,

    inject: [
        'repositoryFactory',
        'context'
    ],

    data() {
        return {
            repository: null,
            items: null
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
        }
    },

    created() {
        this.repository = this.repositoryFactory.create('frosh_share_basket');

        const criteria = new Criteria();

        criteria.addAssociation('lineItems');
        criteria.addAssociation('lineItems.product');

        this.repository
            .search(criteria, this.context)
            .then((result) => {
                console.log(result);
                this.items = result;
            });
    }
});
