/* eslint-disable import/no-unresolved */

import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';

export default class FroshShareBasketButtons extends Plugin {
    static options = {
        urlShareSelector: '.btn-share-basket-url',
        urlInputSelector: '#share-basket-url',
        webShareSelector: '.btn-share-basket-webshare'
    };

    init() {
        this._UrlShareBtn = DomAccess.querySelector(this.el, this.options.urlShareSelector, false);
        this._UrlShareInput = DomAccess.querySelector(this.el, this.options.urlInputSelector, false);
        this._webShareBtn = DomAccess.querySelector(this.el, this.options.webShareSelector, false);

        this._registerEvents();
    }

    _registerEvents() {
        if (this._UrlShareBtn) {
            this._UrlShareBtn.addEventListener('click', this._onClickUrlShare.bind(this));
        }

        if (this._webShareBtn && navigator.share !== undefined) {
            this._webShareBtn.addEventListener('click', this._onClickWebShare.bind(this));
            this._webShareBtn.style.display = 'inline-block';
        }
    }

    _onClickUrlShare(e) {
        e.preventDefault();

        this._UrlShareInput.select();
        document.execCommand('copy');
    }

    _onClickWebShare(e) {
        e.preventDefault();

        const shareTitle = DomAccess.getDataAttribute(e.target, 'data-share-title');
        const shareText = DomAccess.getDataAttribute(e.target, 'data-share-text');
        const shareUrl = DomAccess.getDataAttribute(e.target, 'data-share-url');

        navigator.share({
            title: shareTitle,
            text: shareText,
            url: shareUrl
        });
    }
}
