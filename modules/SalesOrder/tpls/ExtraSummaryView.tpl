{strip}
<div class="summaryView">
    <div class="summaryViewHeader" style="margin-bottom: 15px;">
        <h4 class="display-inline-block">{vtranslate('LBL_KEY_METRICS', Accounts)}</h4>
    </div>
    <div class="summaryViewFields">

        <div class="row textAlignCenter roundedCorners">

            <div class="col-lg-3">
                <div class="well" style="min-height: 125px; padding-left: 0px; padding-right: 0px;">
                    <div>
                        <label class="font-x-small">
                            Hóa đơn
                        </label>
                    </div>
                    <div>
                        <label class="font-x-x-large">
                            {$EXTRA_SUMMARY_INVOICES}
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="well" style="min-height: 125px; padding-left: 0px; padding-right: 0px;">
                    <div>
                        <label class="font-x-small">
                            Phiếu thu
                        </label>
                    </div>
                    <div>
                        <label class="font-x-x-large">
                            {$EXTRA_SUMMARY_RECEIPTS}
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="well" style="min-height: 125px; padding-left: 0px; padding-right: 0px;">
                    <div>
                        <label class="font-x-small">
                            Phiếu chi
                        </label>
                    </div>
                    <div>
                        <label class="font-x-x-large">
                            {$EXTRA_SUMMARY_EXPENSES}
                        </label>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
{/strip}