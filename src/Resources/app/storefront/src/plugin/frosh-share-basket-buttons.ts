const { PluginBaseClass } = window;

export default class FroshShareBasketButtons extends PluginBaseClass {
    public urlShareBtn: HTMLElement | null = null;
    public urlShareInput: HTMLInputElement | null = null;
    public webShareBtn: HTMLElement | null = null;

    options = {
        urlShareSelector: '.btn-share-basket-url',
        urlInputSelector: '#share-basket-url',
        webShareSelector: '.btn-share-basket-webshare',
    };

    constructor(element, options, pluginName) {
        super(element, options, pluginName);

        this.urlShareBtn = this.el.querySelector(this.options.urlShareSelector);
        this.urlShareInput = this.el.querySelector(
            this.options.urlInputSelector
        );
        this.webShareBtn = this.el.querySelector(this.options.webShareSelector);

        this.#registerEvents();
    }

    init() {}

    #registerEvents() {
        if (this.urlShareBtn) {
            this.urlShareBtn.addEventListener(
                'click',
                this.#onClickUrlShare.bind(this)
            );
        }

        if (this.webShareBtn && navigator.share !== undefined) {
            this.webShareBtn.addEventListener(
                'click',
                this.#onClickWebShare.bind(this)
            );
            this.webShareBtn.style.display = 'inline-block';
        }
    }

    #onClickUrlShare(e: MouseEvent) {
        e.preventDefault();

        if (this.urlShareInput) {
            this.urlShareInput.select();
            document.execCommand('copy');
        }
    }

    #onClickWebShare(e: MouseEvent) {
        e.preventDefault();

        const target = e.currentTarget as HTMLButtonElement;

        navigator.share({
            title: target.dataset.shareTitle,
            text: target.dataset.shareText,
            url: target.dataset.shareUrl,
        });
    }
}
