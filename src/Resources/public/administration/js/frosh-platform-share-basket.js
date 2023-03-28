!function(e){var t={};function n(r){if(t[r])return t[r].exports;var s=t[r]={i:r,l:!1,exports:{}};return e[r].call(s.exports,s,s.exports,n),s.l=!0,s.exports}n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var s in e)n.d(r,s,function(t){return e[t]}.bind(null,s));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p=(window.__sw__.assetPath + '/bundles/froshplatformsharebasket/'),n(n.s="cc1i")}({b4be:function(e,t,n){},cc1i:function(e,t,n){"use strict";n.r(t);n("wd0h");var r=Shopware,s=r.Component,a=r.Mixin,i=Shopware.Data.Criteria;s.register("frosh-share-basket-list",{template:'{% block frosh_share_basket_list %}\n    <sw-page class="frosh-share-basket-list">\n\n        {% block frosh_share_basket_list_content %}\n            <template slot="content">\n\n                {% block frosh_share_basket_list_grid %}\n                    <sw-data-grid\n                        v-if="items"\n                        :dataSource="items"\n                        :showSelection="false"\n                        :showActions="false"\n                        :sortBy="sortBy"\n                        :sortDirection="sortDirection"\n                        :columns="columns"\n                        @column-sort="onSortColumn">\n\n                        {% block frosh_share_basket_list_grid_columns %}\n                            {% block frosh_share_basket_list_grid_columns_save_count %}\n                                <template #column-saveCount="{ item }">\n                                    {% block frosh_share_basket_list_grid_columns_save_count_content %}\n                                        <template>\n                                            {{ item.saveCount }} x\n                                        </template>\n                                    {% endblock %}\n                                </template>\n                            {% endblock %}\n                        {% endblock %}\n\n                        <template slot="pagination">\n                            {% block frosh_share_basket_list_grid_pagination %}\n                                <sw-pagination :page="page"\n                                               :limit="limit"\n                                               :total="total"\n                                               :total-visible="7"\n                                               @page-change="onPageChange">\n                                </sw-pagination>\n                            {% endblock %}\n                        </template>\n                    </sw-data-grid>\n                {% endblock %}\n\n                {% block frosh_share_basket_list_grid_loader %}\n                    <sw-loader v-if="isLoading"></sw-loader>\n                {% endblock %}\n\n            </template>\n        {% endblock %}\n\n        {% block frosh_share_basket_list_sidebar %}\n            <sw-sidebar slot="sidebar">\n\n                {% block frosh_share_basket_list_sidebar_refresh %}\n                    <sw-sidebar-item\n                        icon="regular-undo"\n                        :title="$tc(\'frosh-share-basket.list.titleSidebarItemRefresh\')"\n                        @click="onRefresh">\n                    </sw-sidebar-item>\n                {% endblock %}\n\n                {% block frosh_share_basket_list_sidebar %}\n                <sw-sidebar-item\n                    ref="filterSideBar"\n                    icon="regular-filter"\n                    :title="$tc(\'frosh-share-basket.list.titleSidebarItemFilter\')"\n                    @sw-sidebar-item-close-content="closeContent"\n                    @click="closeContent">\n\n                    {% block frosh_share_basket_list_sidebar_filter_sales_channel %}\n                        <sw-sidebar-collapse>\n                            <template slot="header">{{ $tc(\'frosh-share-basket.general.salesChannel\') }}</template>\n                            <template slot="content">\n\n                                {% block frosh_share_basket_list_sidebar_filter_sales_channel_items %}\n                                <div v-for="(item, index) in salesChannelFilters">\n                                    <sw-newsletter-recipient-filter-switch\n                                        :id="item.id"\n                                        group="sales_channel_id"\n                                        :label="item.translated.name"\n                                        @change="onChange">\n                                    </sw-newsletter-recipient-filter-switch>\n                                </div>\n                                {% endblock %}\n\n                            </template>\n                        </sw-sidebar-collapse>\n                    {% endblock %}\n\n                </sw-sidebar-item>\n                {% endblock %}\n\n            </sw-sidebar>\n        {% endblock %}\n\n    </sw-page>\n{% endblock %}\n',inject:["repositoryFactory","syncService","localeToLanguageService"],mixins:[a.getByName("listing")],data:function(){return{isLoading:!1,items:null,languageId:localStorage.getItem("sw-admin-current-language"),total:0,sortBy:"saveCount",sortDirection:"DESC",filterSidebarIsOpen:!1,salesChannelFilters:[],internalFilters:{}}},metaInfo:function(){return{title:this.$createTitle()}},computed:{columns:function(){return[{property:"productName",label:"frosh-share-basket.list.columnProductName",allowResize:!0,primary:!0},{property:"productNumber",label:"frosh-share-basket.list.columnProductNumber",allowResize:!0},{property:"saveCount",label:"frosh-share-basket.list.columnProductSaveCount",allowResize:!0},{property:"totalQuantity",label:"frosh-share-basket.list.columnProductQuantity",allowResize:!0}]},salesChannelStore:function(){return this.repositoryFactory.create("sales_channel")}},created:function(){this.createdComponent()},methods:{createdComponent:function(){var e=this,t=new i(1,100),n=localStorage.getItem("sw-admin-locale");this.salesChannelStore.search(t,Shopware.Context.api).then((function(t){e.salesChannelFilters=t})),this.localeToLanguageService.localeToLanguage(n).then((function(t){e.languageId=t,e.getList()}))},handleBooleanFilter:function(e){if(Array.isArray(this[e.group])||(this[e.group]=[]),!e.value)return this[e.group]=this[e.group].filter((function(t){return t!==e.id})),void(this[e.group].length>0?this.internalFilters[e.group]=i.equalsAny(e.group,this[e.group]):delete this.internalFilters[e.group]);this[e.group].push(e.id),this.internalFilters[e.group]=i.equalsAny(e.group,this[e.group])},onChange:function(e){null===e&&(e=[]),this.handleBooleanFilter(e),this.getList()},closeContent:function(){if(this.filterSidebarIsOpen)return this.$refs.filterSideBar.closeContent(),void(this.filterSidebarIsOpen=!1);this.$refs.filterSideBar.openContent(),this.filterSidebarIsOpen=!0},getList:function(){var e=this;this.isLoading=!0;var t=new i(this.page,this.limit,this.term);return t.languageId=this.languageId,t.addSorting(i.sort(this.sortBy,this.sortDirection)),Object.values(this.internalFilters).forEach((function(e){t.addFilter(e)})),this.syncService.httpClient.post("/frosh/sharebasket/statistics",t,{headers:this.syncService.getBasicHeaders()}).then((function(t){e.items=t.data.data,e.total=t.data.total,e.isLoading=!1})).catch((function(){e.isLoading=!1}))}}}),Shopware.Module.register("frosh-share-basket",{type:"plugin",name:"ShareBasket",title:"frosh-share-basket.general.mainMenuItemGeneral",description:"frosh-share-basket.general.descriptionTextModule",color:"#079FDF",icon:"default-shopping-paper-bag-product",routes:{list:{component:"frosh-share-basket-list",path:"list"}},navigation:[{label:"frosh-share-basket.general.mainMenuItemGeneral",color:"#079FDF",path:"frosh.share.basket.list",icon:"default-shopping-paper-bag-product",parent:"sw-marketing"}]})},wd0h:function(e,t,n){var r=n("b4be");r.__esModule&&(r=r.default),"string"==typeof r&&(r=[[e.i,r,""]]),r.locals&&(e.exports=r.locals);(0,n("ydqr").default)("92f45e86",r,!0,{})},ydqr:function(e,t,n){"use strict";function r(e,t){for(var n=[],r={},s=0;s<t.length;s++){var a=t[s],i=a[0],o={id:e+":"+s,css:a[1],media:a[2],sourceMap:a[3]};r[i]?r[i].parts.push(o):n.push(r[i]={id:i,parts:[o]})}return n}n.r(t),n.d(t,"default",(function(){return p}));var s="undefined"!=typeof document;if("undefined"!=typeof DEBUG&&DEBUG&&!s)throw new Error("vue-style-loader cannot be used in a non-browser environment. Use { target: 'node' } in your Webpack config to indicate a server-rendering environment.");var a={},i=s&&(document.head||document.getElementsByTagName("head")[0]),o=null,l=0,c=!1,u=function(){},d=null,h="data-vue-ssr-id",f="undefined"!=typeof navigator&&/msie [6-9]\b/.test(navigator.userAgent.toLowerCase());function p(e,t,n,s){c=n,d=s||{};var i=r(e,t);return b(i),function(t){for(var n=[],s=0;s<i.length;s++){var o=i[s];(l=a[o.id]).refs--,n.push(l)}t?b(i=r(e,t)):i=[];for(s=0;s<n.length;s++){var l;if(0===(l=n[s]).refs){for(var c=0;c<l.parts.length;c++)l.parts[c]();delete a[l.id]}}}}function b(e){for(var t=0;t<e.length;t++){var n=e[t],r=a[n.id];if(r){r.refs++;for(var s=0;s<r.parts.length;s++)r.parts[s](n.parts[s]);for(;s<n.parts.length;s++)r.parts.push(m(n.parts[s]));r.parts.length>n.parts.length&&(r.parts.length=n.parts.length)}else{var i=[];for(s=0;s<n.parts.length;s++)i.push(m(n.parts[s]));a[n.id]={id:n.id,refs:1,parts:i}}}}function g(){var e=document.createElement("style");return e.type="text/css",i.appendChild(e),e}function m(e){var t,n,r=document.querySelector("style["+h+'~="'+e.id+'"]');if(r){if(c)return u;r.parentNode.removeChild(r)}if(f){var s=l++;r=o||(o=g()),t=k.bind(null,r,s,!1),n=k.bind(null,r,s,!0)}else r=g(),t=y.bind(null,r),n=function(){r.parentNode.removeChild(r)};return t(e),function(r){if(r){if(r.css===e.css&&r.media===e.media&&r.sourceMap===e.sourceMap)return;t(e=r)}else n()}}var _,v=(_=[],function(e,t){return _[e]=t,_.filter(Boolean).join("\n")});function k(e,t,n,r){var s=n?"":r.css;if(e.styleSheet)e.styleSheet.cssText=v(t,s);else{var a=document.createTextNode(s),i=e.childNodes;i[t]&&e.removeChild(i[t]),i.length?e.insertBefore(a,i[t]):e.appendChild(a)}}function y(e,t){var n=t.css,r=t.media,s=t.sourceMap;if(r&&e.setAttribute("media",r),d.ssrId&&e.setAttribute(h,t.id),s&&(n+="\n/*# sourceURL="+s.sources[0]+" */",n+="\n/*# sourceMappingURL=data:application/json;base64,"+btoa(unescape(encodeURIComponent(JSON.stringify(s))))+" */"),e.styleSheet)e.styleSheet.cssText=n;else{for(;e.firstChild;)e.removeChild(e.firstChild);e.appendChild(document.createTextNode(n))}}}});