{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
    <div id="recent-comments">  {* Replaced wrapper form into wrapper div by Hieu Nguyen on 2021-05-12 to prevent error while submitting quick edit fields at Summary View *}
        {assign var="COMMENT_TEXTAREA_DEFAULT_ROWS" value="2"}
        {assign var="PRIVATE_COMMENT_MODULES" value=Vtiger_Functions::getPrivateCommentModules()}
        {assign var=IS_CREATABLE value=$COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
        {assign var=IS_EDITABLE value=$COMMENTS_MODULE_MODEL->isPermitted('EditView')}

        <div class="commentContainer commentsRelatedContainer container-fluid">
            {if $IS_CREATABLE}
                <div class="commentTitle row">
                    <div class="addCommentBlock">
                        {*  Modified by Hieu Nguyen on 2021-03-16 to support @mention in comment *}
                        <div class="row">
                            <div class=" col-lg-12">
                                <textarea type="text" name="commentcontent" class="commentcontent"></textarea>
                                <div id="newCommentTextArea" class="commentTextArea" contenteditable="true" placeholder="{vtranslate('LBL_POST_YOUR_COMMENT_HERE', $MODULE_NAME)}"></div>
                            </div>
                        </div>
                        {* End Hieu Nguyen *}
                        <div class="row">
                            <div class="col-xs-4 pull-right">
                                <div class="pull-right">
                                    {if in_array($MODULE_NAME, $PRIVATE_COMMENT_MODULES)}
                                        <input type="checkbox" id="is_private">&nbsp;&nbsp;{vtranslate('LBL_INTERNAL_COMMENT')}&nbsp;
                                        <i class="far fa-info-circle cursorPointer" data-toggle="tooltip" data-placement="top" data-original-title="{vtranslate('LBL_INTERNAL_COMMENT_INFO')}"></i>&nbsp;&nbsp;
                                    {/if}
                                    <button class="btn btn-success btn-sm saveComment" type="button" data-mode="add"><strong>{vtranslate('LBL_POST', $MODULE_NAME)}</strong></button>
                                </div>
                            </div>
                            <div class="col-xs-8 pull-left">
                                {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) MODULE="ModComments"}
                            </div>
                        </div>
                    </div>
                </div>
            {/if}
            <div class="showcomments container-fluid row" style="margin-top:10px;">
                <div class="recentCommentsHeader row">
                    <h4 class="display-inline-block col-lg-5 textOverflowEllipsis" title="{vtranslate('LBL_RECENT_COMMENTS', $MODULE_NAME)}"> {* Modified column size by Hieu Nguyen on 2021-06-30 *}
                        {vtranslate('LBL_RECENT_COMMENTS', $MODULE)}    {* Updated block label by Hieu Nguyen on 2021-03-16 *}
                    </h4>
                    {if $MODULE_NAME ne 'Leads'}
                        <div class="col-lg-7 commentHeader pull-right" style="margin-top:5px;text-align:right;padding-right:20px;"> {* Modified column size by Hieu Nguyen on 2021-06-30 *}
                            <div class="display-inline-block">
                                <span class="">{vtranslate('LBL_ROLL_UP',$QUALIFIED_MODULE)} &nbsp;</span>
                                <span class="far fa-info-circle" data-toggle="tooltip" data-placement="top" title="{vtranslate('LBL_ROLLUP_COMMENTS_INFO',$QUALIFIED_MODULE)}"></span>&nbsp;&nbsp;
                            </div>
                            {* Refactored code by Hieu Nguyen on 2021-06-29 *}
                            <input type="checkbox" id="rollup-comments" class="bootstrap-switch hide" data-has-comments="1" data-start-index="{$STARTINDEX}"
                                data-rollup-id="{$ROLLUPID}" data-module-name="{$MODULE_NAME}" data-record-id="{$MODULE_RECORD}" {if $ROLLUP_STATUS}checked{/if} />
                            {* End Hieu Nguyen *}
                        </div> 
                    {/if}
                </div>
                <hr>
                <div class="commentsList commentsBody marginBottom15">
                    {include file='CommentsList.tpl'|@vtemplate_path COMMENT_MODULE_MODEL=$COMMENTS_MODULE_MODEL IS_CREATABLE=$IS_CREATABLE IS_EDITABLE=$IS_EDITABLE}
                </div>

                <div class="hide basicAddCommentBlock container-fluid">
                    {*  Modified by Hieu Nguyen on 2021-03-16 to support @mention in comment *}
                    <textarea type="text" name="commentcontent" class="commentcontent"></textarea>
                    <div  id="replyCommentTextArea" class="commentTextArea" contenteditable="true" placeholder="{vtranslate('LBL_POST_YOUR_COMMENT_HERE', $MODULE_NAME)}"></div>
                    {* End Hieu Nguyen *}
                    <div class="pull-right row">
                        {if in_array($MODULE_NAME, $PRIVATE_COMMENT_MODULES)}
                            <input type="checkbox" id="is_private">&nbsp;&nbsp;{vtranslate('LBL_INTERNAL_COMMENT')}&nbsp;&nbsp;
                        {/if}
                        <button class="btn btn-success btn-sm saveComment" type="button" data-mode="add"><strong>{vtranslate('LBL_POST', $MODULE_NAME)}</strong></button>
                        <a href="javascript:void(0);" class="cursorPointer closeCommentBlock cancelLink" type="reset">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</a>
                    </div>
                </div>

                <div class="hide basicEditCommentBlock container-fluid">
                    <div class="row" style="padding-bottom: 10px;">
                        <input style="width:100%;height:30px;" type="text" name="reasonToEdit" placeholder="{vtranslate('LBL_REASON_FOR_CHANGING_COMMENT', $MODULE_NAME)}" class="input-block-level"/>
                    </div>
                    <div class="row">
                        {*  Modified by Hieu Nguyen on 2021-03-16 to support @mention in comment *}
                        <textarea type="text" name="commentcontent" class="commentcontent"></textarea>
                        <div id="editCommentTextArea" class="commentTextArea" contenteditable="true" placeholder="{vtranslate('LBL_POST_YOUR_COMMENT_HERE', $MODULE_NAME)}"></div>
                        {* End Hieu Nguyen *}
                    </div>
                    <input type="hidden" name="is_private">
                    <div class="pull-right row">
                        <button class="btn btn-success btn-sm saveComment" type="button" data-mode="edit"><strong>{vtranslate('LBL_POST', $MODULE_NAME)}</strong></button>
                        <a href="javascript:void(0);" class="cursorPointer closeCommentBlock cancelLink" type="reset">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}