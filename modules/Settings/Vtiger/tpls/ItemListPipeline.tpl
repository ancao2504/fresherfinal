{strip}
{if !empty($PIPELINE_LIST)}
{foreach key=PIPELINE_ID item=PIPELINE from=$PIPELINE_LIST}
<tr class="listViewEntries">
    <td>
        <span class="fieldValue">
            <span class="value pipeline-name textOverflowEllipsis">
                {$PIPELINE.name}
            </span>
        </span>
    </td>
    <td>
        <span class="fieldValue">
            <span class="value textOverflowEllipsis pipeline-step">
                {$PIPELINE.steps_count}
            </span>
        </span>
    </td>
    <td>
        <span class="fieldValue">
            <div class="toggle-switch {if $PIPELINE.status}active{/if} pipeline-status"></div>
        </span>
    </td>
    <td>
        <span class="fieldValue">
            <span class="value textOverflowEllipsis pipeline-permission">
                {$PIPELINE.permissions|default:"Tất cả"}
            </span>
        </span>
    </td>
    <td>
        <span class="fieldValue">
            <span class="value pipeline-description">
                {$PIPELINE.description}
            </span>
        </span>
    </td>
    <td>
        <span class="fieldValue">
            <span class="value textOverflowEllipsis pipeline-creator">
                {$PIPELINE.created_by}
            </span>
        </span>
    </td>
    <td>
        <span class="fieldValue">
            <div class="action-buttons">
                <span><i class="far fa-pen icon" onclick="app.controller().editPipeline('{$PIPELINE_ID}')"
                        title="Sửa"></i></span>
                <span><i class="far fa-clone icon" onclick="app.controller().clonePipeline('{$PIPELINE_ID}')"
                        title="Nhân bản"></i></span>
                <span><i class="far fa-trash-alt icon" onclick="app.controller().deletePipeline('{$PIPELINE_ID}')"
                        title="Xóa"></i></span>
            </div>
        </span>
    </td>
</tr>
{/foreach}
{else}
<tr>
    <td colspan="7" class="text-center">
        <div>Không tìm thấy Pipeline nào {$TEST_PIPELINE}</div>
    </td>
</tr>
{/if}
{/strip}