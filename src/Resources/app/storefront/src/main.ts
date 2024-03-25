import FroshShareBasketButtons from './plugin/frosh-share-basket-buttons';

const { PluginManager } = window;

PluginManager.register('FroshShareBasketButtons', FroshShareBasketButtons, '[data-frosh-share-basket-buttons]');
