import './page/frosh-share-basket-list';

const { Module } = Shopware;

Module.register('frosh-share-basket', {
    type: 'plugin',
    name: 'ShareBasket',
    title: 'frosh-share-basket.general.mainMenuItemGeneral',
    description: 'frosh-share-basket.general.descriptionTextModule',
    color: '#079FDF',
    icon: 'default-shopping-paper-bag-product',

    routes: {
        list: {
            component: 'frosh-share-basket-list',
            path: 'list'
        }
    },

    navigation: [{
        label: 'frosh-share-basket.general.mainMenuItemGeneral',
        color: '#079FDF',
        path: 'frosh.share.basket.list',
        icon: 'default-shopping-paper-bag-product',
        parent: 'sw-marketing'
    }]
});
