"use strict";(self.webpackChunk=self.webpackChunk||[]).push([["frosh-platform-share-basket"],{5142:(e,t,r)=>{var a,i,s,n=r(9068),l=r(6285),h=r(3206);class o extends l.Z{init(){this._UrlShareBtn=h.Z.querySelector(this.el,this.options.urlShareSelector,!1),this._UrlShareInput=h.Z.querySelector(this.el,this.options.urlInputSelector,!1),this._webShareBtn=h.Z.querySelector(this.el,this.options.webShareSelector,!1),this._registerEvents()}_registerEvents(){this._UrlShareBtn&&this._UrlShareBtn.addEventListener("click",this._onClickUrlShare.bind(this)),this._webShareBtn&&void 0!==navigator.share&&(this._webShareBtn.addEventListener("click",this._onClickWebShare.bind(this)),this._webShareBtn.style.display="inline-block")}_onClickUrlShare(e){e.preventDefault(),this._UrlShareInput.select(),document.execCommand("copy")}_onClickWebShare(e){e.preventDefault();const t=h.Z.getDataAttribute(e.target,"data-share-title"),r=h.Z.getDataAttribute(e.target,"data-share-text"),a=h.Z.getDataAttribute(e.target,"data-share-url");navigator.share({title:t,text:r,url:a})}}a=o,s={urlShareSelector:".btn-share-basket-url",urlInputSelector:"#share-basket-url",webShareSelector:".btn-share-basket-webshare"},(i=function(e){var t=function(e,t){if("object"!=typeof e||null===e)return e;var r=e[Symbol.toPrimitive];if(void 0!==r){var a=r.call(e,t||"default");if("object"!=typeof a)return a;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===t?String:Number)(e)}(e,"string");return"symbol"==typeof t?t:String(t)}(i="options"))in a?Object.defineProperty(a,i,{value:s,enumerable:!0,configurable:!0,writable:!0}):a[i]=s,n.Z.register("FroshSharebasketButtons",o,"[data-frosh-share-basket-buttons]")}},e=>{e.O(0,["vendor-node","vendor-shared"],(()=>{return t=5142,e(e.s=t);var t}));e.O()}]);