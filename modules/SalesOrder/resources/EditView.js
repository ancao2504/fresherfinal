/*
    EditView.js
    Author: Hieu Nguyen
    Date: 2018-11-29
    Purpose: to handle logic on the UI
*/

$(document).ready(() => {
  // Disable input fields with class 'listPrice' and 'taxPercentage'
  $("input.listPrice").prop("readonly", true);
  $("input.taxPercentage").prop("readonly", true);
});
