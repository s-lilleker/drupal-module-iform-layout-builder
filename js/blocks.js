jQuery(document).ready(function($) {

  indiciaData.onloadFns.push(function() {
    // Copy grid attribute captions (from local config) into the grid
    // headings which are initially populated from the database.
    if (indiciaData.speciesGrid && indiciaData.occurrenceAttributeCaptions) {
      $.each(indiciaData.speciesGrid, function(gridId) {
        $.each(indiciaData.occurrenceAttributeCaptions, function(attrId) {
          $('th#' + gridId + '-' + attrId + '-0').text(this);
        });
      });
    }
  });

  // Delete button confirmation.
  $('.block-data-entry-submit-buttons-block button[value="DELETE"]').click(function(e) {
    if (!confirm('Are you sure you want to delete all the data on this form?')) {
      e.preventDefault();
      return false;
    }
  });

});
