{*
    File SocialReportPopup.tpl
    Author: Phuc Lu
    Date: 2019-08-29
    Purpose: to render layout for social report
*}

{strip}
    <div id="socialReportModal" class="modal-dialog modal-content hide">
        {assign var='SOCIAL_REPORT_LABEL' value=vtranslate('LBL_SOCIAL_REPORT', $MODULE)}
        {assign var='CAMPAIGN_NAME' value=$RECORD->get('campaignname')}
        {assign var='SOCIAL_REPORT_MODAL_TITLE' value="`$SOCIAL_REPORT_LABEL`: `$CAMPAIGN_NAME`"}
        {include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=$SOCIAL_REPORT_MODAL_TITLE}

        <form id="socialReportForm" class="form-horizontal" method="POST">
            <div class="session">
                <h4>
                    {vtranslate('LBL_SOCIAL_MESSAGE', $MODULE)}
                </h4>

                <div class="form-group">
                    <div class="col-sm-6">
                        <h5>{vtranslate('LBL_SOCIAL_REPORT_SENT_SOURCE', $MODULE)}</h5>

                        <div>
                            {vtranslate('LBL_SOCIAL_REPORT_TOTAL', $MODULE)} <span class="zalo_number"></span> {vtranslate('LBL_SOCIAL_REPORT_ZALO_NUMBERS', $MODULE)} / <span class="total_number"></span> {vtranslate('LBL_SOCIAL_REPORT_TARGET_CUSTOMERS', $MODULE)|lower}
                        </div>

                        <div class = "div_sent_source_chart">
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <h5>{vtranslate('LBL_SOCIAL_REPORT_SENT_STATUS', $MODULE)}</h5>

                        <div style="margin-top: 30px;">
                            <div>{vtranslate('LBL_SOCIAL_REPORT_TOTAL_SENT_MESSAGES', $MODULE)}: <span class="sent_number"></span></div>

                            <div class="div_sent_detail_chart">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            <div class="session">
                <h4>
                    {vtranslate('LBL_SOCIAL_ARTICLE', $MODULE)}
                </h4>

                <div class="form-group">
                    <div class="col-sm-6">
                        <h5>{vtranslate('LBL_SOCIAL_REPORT_ARTICLE_FILTER', $MODULE)}</h5>

                        <div id="div_filter_detail">
                            <ul>
                                <li><strong>{vtranslate('LBL_SOCIAL_REPORT_CPTARGETLIST_ZALO_CITY', $MODULE)}:</strong> <span class="filter_city"></span></li>
                                <li><strong>{vtranslate('LBL_SOCIAL_REPORT_CPTARGETLIST_ZALO_AGE', $MODULE)}:</strong> <span class="filter_age"></span></li>
                                <li><strong>{vtranslate('LBL_SOCIAL_REPORT_CPTARGETLIST_ZALO_GENDER', $MODULE)}:</strong> <span class="filter_gender"></span></li>
                                <li><strong>{vtranslate('LBL_SOCIAL_REPORT_CPTARGETLIST_ZALO_PLATFORM', $MODULE)}:</strong> <span class="filter_platform"></span></li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <h5>{vtranslate('LBL_SOCIAL_REPORT_SENT_ARTICLE_LIST', $MODULE)}</h5>

                        <div>
                            <table id = "tbl_article_list">
                                <thead>
                                    <tr>
                                        <th width = 60%>{vtranslate('LBL_SOCIAL_REPORT_ARTICLE', $MODULE)}</th>
                                        <th width = 40%>{vtranslate('LBL_SOCIAL_REPORT_SENT_FROM', $MODULE)}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                {if count($ARTICLES) >= 1} 
                                    {foreach from=$ARTICLES key=ID item=ARTICLE}
                                        <tr>
                                            <td><a target = "_blank" title="{$ARTICLE['name']}" href = "index.php?module=CPSocialArticle&view=Detail&record={$ID}&app=MARKETING">{$ARTICLE['short_name']}</td>
                                            <td><span title="{$ARTICLE['social_holder_name']}">{$ARTICLE['short_social_holder_name']}</span></td>
                                        </tr>
                                    {/foreach}
                                {/if}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            <div class="session">
                <h4>
                    {vtranslate('LBL_SOCIAL_REPORT_CAMPAIGN_EFFECTIVENESS', $MODULE)}
                </h4>

                <div class="form-group">
                    <div class="col-sm-6">
                        <h5>{vtranslate('LBL_SOCIAL_REPORT_COLLECTED_DATA', $MODULE)}</h5>

                        <div class="div_collected_data_chart">
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <h5>{vtranslate('LBL_SOCIAL_REPORT_COLLECTED_RELATED_RECORD', $MODULE)}</h5>

                        <div class="div_related_data_chart">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <link type="text/css" rel="stylesheet" href="{vresource_url('resources/libraries/DataTables/css/CustomTable.css')}" />
    
	<script type="text/javascript" src="{vresource_url('modules/Campaigns/resources/SocialReport.js')}" async defer ></script>
{/strip}