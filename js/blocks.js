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

});
