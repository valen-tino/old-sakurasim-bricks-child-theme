jQuery(document).ready(function($) {
    $('.gift.clickable').on('click', function() {
        $('.gift').removeClass('selected'); // Remove 'selected' class from all gifts
        $(this).addClass('selected'); // Add 'selected' class to clicked gift
        var selectedGiftId = $(this).data('gift-id'); // Get the data-gift-id attribute value
        $('#selected_gift_input').val(selectedGiftId); // Set the value of hidden input
    });
});








