/*
    MentiontHandler.js
    Author: Hieu Nguyen
    Date: 2021-03-15
    Purpose: To handle mention logic on the UI
    Usage: mentionHandler.attach($('div-selector'));
*/

let mentionHandler = new Tribute({
    values: function (term, callback) {
        if (term.length < _VALIDATION_CONFIG.autocomplete_min_length) return [];

        var params = {
            module: 'Vtiger',
            action: 'HandleOwnerFieldAjax',
            mode: 'loadOwnerList',
            keyword: term,
            user_only: true,
            skip_current_user: true
        };

        app.request.post({ data: params }).then((err, res) => {
            if (res && res.length > 0 && res[0].children) {
                var users = res[0].children;
                callback(users);
                return;
            }

            callback([]);
        });
    },
    itemClass: 'tribute-item',
    selectClass: 'active',
    lookup: 'text',
    fillAttr: 'text',
    allowSpaces: true,
    menuShowMinLength: _VALIDATION_CONFIG.autocomplete_min_length,
    searchOpts: {
        skip: true  // Do not perform local search
    },
    menuItemTemplate: function (item) {
        return item.original.text;
    },
    selectTemplate: function (item) {
        if (typeof item === 'undefined') return null;
        var name = item.original.text.split(' (')[0].trim();
        return (this.getMentionTag(item.original.id, name));
    }
});

// Get a single mention tag
mentionHandler.getMentionTag = function (id, name) {
    return '<mention contenteditable="false" id="'+ id +'">@' + name + '</mention>';
}

// Inset a mention tag into the input box
mentionHandler.insertNewMention = function (mentionContainer, id, name) {
    var htmlString = $(mentionContainer).html();
    htmlString += this.getMentionTag(id, name);
    mentionContainer.html(htmlString);
}

// Get db string with mentions in mark-down format for saving
mentionHandler.getDbFormat = function (mentionContainer) {
    var htmlString = $(mentionContainer).html();

    $(mentionContainer).find('mention').each(function () {
        var userId = $(this).attr('id').trim();
        var userName = $(this).text().trim().replace('@', '');
        var mentionHtml = $(this)[0].outerHTML;
        htmlString = htmlString.replace(mentionHtml, `@[${userName}](${userId})`);
    });

    dbString = htmlString.replaceAll('<div>', "\n");
    dbString = dbString.replaceAll('<br>', "\n");
    dbString = dbString.replaceAll('\n\n', "\n");
    dbString = dbString.replace(/(<([^>]+)>)/ig,"");
    return dbString.trim();
}