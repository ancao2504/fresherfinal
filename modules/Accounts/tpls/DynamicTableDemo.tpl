{strip}
<!-- Nhúng CSS và JS -->
<link type="text/css" rel="stylesheet" href="resources/libraries/DynamicTable/DynamicTable.css" />
<script type="text/javascript" src="resources/libraries/DynamicTable/DynamicTable.js"></script>

<!-- Bảng động -->
<table id="tblDemo" class="dynamicTable" width="60%">
    <thead>
        <tr>
            <th>Column1</th>
            <th>Column2</th>
            <th>Column3</th>
            <th>Column4</th>
            <th>Column5</th>
            <th>
                <button type="button" class="btnAddRow btn-primary">
                    <i class="fa fa-plus"></i>
                </button>
            </th>
        </tr>
    </thead>
    <tbody></tbody>
    <tfoot class="template" style="display:none">
        <tr>
            <td>
                <input type="text" name="field1[]" class="form-control" />
                <input type="hidden" name="deleted[]" />
            </td>
            <td><input type="text" name="field2[]" class="form-control" /></td>
            <td><input type="text" name="field3[]" class="form-control" /></td>
            <td><input type="text" name="field4[]" class="form-control" /></td>
            <td><input type="text" name="field5[]" class="form-control" /></td>
            <td>
                <button type="button" class="btnDelRow btn-danger">
                    <i class="fa fa-minus"></i>
                </button>
            </td>
        </tr>
    </tfoot>
</table>

{/strip}