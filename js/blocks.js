jQuery(document).ready(function($) {

  indiciaData.onloadFns.push(function() {
    if (indiciaData.speciesGrid && indiciaData.occurrenceAttributeCaptions) {
      $.each(indiciaData.speciesGrid, function(gridId) {
        $.each(indiciaData.occurrenceAttributeCaptions, function(attrId) {
          $('th#' + gridId + '-' + attrId + '-0').text(this);
        });
      });
    }
  });

});
