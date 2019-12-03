/* eslint-disable import/no-unresolved */

import PluginManager from 'src/plugin-system/plugin.manager';
import FroshSharebasketButtons from './plugin/frosh-share-basket-buttons';

PluginManager.register('FroshSharebasketButtons', FroshSharebasketButtons, '[data-frosh-share-basket-buttons]');
