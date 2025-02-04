{*
    ChatbotIframeRelatedListPopup.tpl
    Author: Phu Vo
    Date: 2020.09.11
*}

{strip}
    <b-overlay :show="overlay" id="app" style="display: none">
        <b-container fluid>
            <div class="header">
                <h5 class="mt-2 text-center" v-html="title"></h5>
            </div>
            <div class="body mt-3">
                {if file_exists($RELATED_LIST_FILTER_TPL_NAME)}
                    {include file=$RELATED_LIST_FILTER_TPL_NAME}
                {else}
                    <b-form name="filters" @submit="reloadDataTable">
                        <b-form-row>
                            <b-col cols="6">
                                <b-form-input name="label" v-model="form_data.label" placeholder="{vtranslate('LBL_TYPE_SEARCH')}"></b-form-input>
                            </b-col>
                            <b-col cols="auto" class="ml-auto">
                                <b-button variant="default" @click="clearFilter" class="ml-1">{vtranslate('LBL_CLEAR_FILTERS')}</b-button>
                                <b-button variant="primary" @click="reloadDataTable" class="ml-1">{vtranslate('LBL_SEARCH')}</b-button>
                            </b-col>
                        </b-form-row>
                    </b-form>
                {/if}
                <hr class="mt-2 mb-2" />
                <table id="related-list" class="table table-striped table-bordered mb-2"  style="width: 100%">
                    <thead>
                        <tr>
                            <th v-for="field in fields" :key="field.key" v-html="field.label"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </b-container>
    </b-overlay>
{/strip}