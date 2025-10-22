(()=>{var r=`{% block frosh_share_basket_list %}
    <sw-page class="frosh-share-basket-list">
        {% block frosh_share_basket_list_content %}
        <template #content>
            {% block frosh_share_basket_list_grid %}
            <sw-data-grid
                v-if="items"
                :dataSource="items"
                :showSelection="false"
                :showActions="false"
                :sortBy="sortBy"
                :sortDirection="sortDirection"
                :columns="columns"
                @column-sort="onSortColumn"
            >
                {% block frosh_share_basket_list_grid_columns %}
                            {% block frosh_share_basket_list_grid_columns_save_count %}
                <template #column-saveCount="{ item }">
                                    {% block frosh_share_basket_list_grid_columns_save_count_content %}
                                        {{ item.saveCount }} x
                                    {% endblock %}
                                </template>
                {% endblock %}
                        {% endblock %}
                <template #pagination>
                    {% block frosh_share_basket_list_grid_pagination %}
                    <sw-pagination
                        :page="page"
                        :limit="limit"
                        :total="total"
                        :total-visible="7"
                        @page-change="onPageChange"
                    >
                                </sw-pagination>
                    {% endblock %}
                </template>
            </sw-data-grid>
            {% endblock %}

                {% block frosh_share_basket_list_grid_loader %}
            <sw-loader
                v-if="isLoading"
                deprecated
            ></sw-loader>
            {% endblock %}
        </template>
        {% endblock %}

        {% block frosh_share_basket_list_sidebar %}
        <template #sidebar>
            <sw-sidebar>
                {% block frosh_share_basket_list_sidebar_refresh %}
                <sw-sidebar-item
                    icon="regular-undo"
                    :title="$tc('frosh-share-basket.list.titleSidebarItemRefresh')"
                    @click="onRefresh"
                >
                        </sw-sidebar-item>
                {% endblock %}

                    {% block frosh_share_basket_list_sidebar %}
                <sw-sidebar-item
                    ref="filterSideBar"
                    icon="regular-filter"
                    :title="$tc('frosh-share-basket.list.titleSidebarItemFilter')"
                    @sw-sidebar-item-close-content="closeContent"
                    @click="closeContent"
                >
                    {% block frosh_share_basket_list_sidebar_filter_sales_channel %}
                    <sw-sidebar-collapse>
                        <template #header>
                            {{ $tc('frosh-share-basket.general.salesChannel') }}
                        </template>

                        <template #content>
                            {% block frosh_share_basket_list_sidebar_filter_sales_channel_items %}
                            <div v-for="(item, index) in salesChannelFilters">
                                <sw-newsletter-recipient-filter-switch
                                    :id="item.id"
                                    group="sales_channel_id"
                                    :label="item.translated.name"
                                    @update:value="onChange"
                                />
                            </div>
                            {% endblock %}
                        </template>
                    </sw-sidebar-collapse>
                    {% endblock %}
                </sw-sidebar-item>
                {% endblock %}
            </sw-sidebar>
        </template>
        {% endblock %}
    </sw-page>
{% endblock %}`;var{Component:o,Mixin:l}=Shopware,{Criteria:s}=Shopware.Data;o.register("frosh-share-basket-list",{template:r,inject:["repositoryFactory","syncService","localeToLanguageService"],mixins:[l.getByName("listing")],data(){return{isLoading:!1,items:null,languageId:localStorage.getItem("sw-admin-current-language"),total:0,sortBy:"saveCount",sortDirection:"DESC",filterSidebarIsOpen:!1,salesChannelFilters:[],internalFilters:{}}},metaInfo(){return{title:this.$createTitle()}},computed:{columns(){return[{property:"productName",label:"frosh-share-basket.list.columnProductName",allowResize:!0,primary:!0},{property:"productNumber",label:"frosh-share-basket.list.columnProductNumber",allowResize:!0},{property:"saveCount",label:"frosh-share-basket.list.columnProductSaveCount",allowResize:!0},{property:"totalQuantity",label:"frosh-share-basket.list.columnProductQuantity",allowResize:!0}]},salesChannelStore(){return this.repositoryFactory.create("sales_channel")}},created(){this.createdComponent()},methods:{createdComponent(){let e=new s(1,100),t=localStorage.getItem("sw-admin-locale");this.salesChannelStore.search(e,Shopware.Context.api).then(a=>{this.salesChannelFilters=a}),this.localeToLanguageService.localeToLanguage(t).then(a=>{this.languageId=a,this.getList()})},handleBooleanFilter(e){if(Array.isArray(this[e.group])||(this[e.group]=[]),!e.value){this[e.group]=this[e.group].filter(t=>t!==e.id),this[e.group].length>0?this.internalFilters[e.group]=s.equalsAny(e.group,this[e.group]):delete this.internalFilters[e.group];return}this[e.group].push(e.id),this.internalFilters[e.group]=s.equalsAny(e.group,this[e.group])},onChange(e){e===null&&(e=[]),this.handleBooleanFilter(e),this.getList()},closeContent(){if(this.filterSidebarIsOpen){this.$refs.filterSideBar.closeContent(),this.filterSidebarIsOpen=!1;return}this.$refs.filterSideBar.openContent(),this.filterSidebarIsOpen=!0},getList(){this.isLoading=!0;let e=new s(this.page,this.limit,this.term);return e.languageId=this.languageId,e.addSorting(s.sort(this.sortBy,this.sortDirection)),Object.values(this.internalFilters).forEach(t=>{e.addFilter(t)}),this.syncService.httpClient.post("/frosh/sharebasket/statistics",e,{headers:this.syncService.getBasicHeaders()}).then(t=>{this.items=t.data.data,this.total=t.data.total,this.isLoading=!1}).catch(()=>{this.isLoading=!1})}}});var{Module:n}=Shopware;n.register("frosh-share-basket",{type:"plugin",name:"ShareBasket",title:"frosh-share-basket.general.mainMenuItemGeneral",description:"frosh-share-basket.general.descriptionTextModule",color:"#079FDF",icon:"default-shopping-paper-bag-product",routes:{list:{component:"frosh-share-basket-list",path:"list"}},navigation:[{label:"frosh-share-basket.general.mainMenuItemGeneral",color:"#079FDF",path:"frosh.share.basket.list",icon:"default-shopping-paper-bag-product",parent:"sw-marketing"}]});})();
