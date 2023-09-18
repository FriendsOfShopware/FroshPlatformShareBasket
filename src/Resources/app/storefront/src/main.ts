import FroshShareBasketButtons from './plugin/frosh-share-basket-buttons';

const { PluginManager } = window;

PluginManager.register('FroshSharebasketButtons', FroshShareBasketButtons, '[data-frosh-share-basket-buttons]');
