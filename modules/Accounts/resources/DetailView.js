/*
	DetailView.js
	Author: Hieu Nguyen
	Date: 2022-01-18
	Purpose: to handle logic on the UI
*/

jQuery(function ($) {
  removeRelationUnlinkButtons();
  alert("Xin chào");
  app.event.on("post.relatedListLoad.click", (event, data) => {
    removeRelationUnlinkButtons();
  });

  function removeRelationUnlinkButtons() {
    // Remove unlink button of activities that does not link directly to current account
    if ($(".related-tabs").find("li.active").data("module") == "Calendar") {
      $("#listview-table")
        .find(".listViewEntries")
        .each(function () {
          let currentAccountId = app.getRecordId();
          let relatedAccountId = $(this).find('[name="related_account"]').val();

          if (relatedAccountId != currentAccountId) {
            $(this).find(".relationDelete").remove();
          }
        });
    }
  }
});
