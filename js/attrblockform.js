
(function ($) {
  var lastLabelSet = '';

  $(document).on('change', '[name="settings[option_admin_name]"]', function() {
    // If the label has not been changed by the user, default it to the same as the admin name.
    if ($('[name="settings[option_label]"]').val() === lastLabelSet) {
      $('[name="settings[option_label]"]').val($('[name="settings[option_admin_name]"]').val());
      lastLabelSet = $('[name="settings[option_label]"]').val();
    }
  });
})(jQuery);