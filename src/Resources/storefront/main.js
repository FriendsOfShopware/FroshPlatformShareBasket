import PluginManager from 'src/script/plugin-system/plugin.manager';
import FroshSharebasketButtons from './script/frosh-share-basket-buttons';

PluginManager.register('FroshSharebasketButtons', FroshSharebasketButtons, '[data-frosh-share-basket-buttons]');
