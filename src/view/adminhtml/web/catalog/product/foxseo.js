
require([
    'jquery'
], function ($) {

    $(window).load(toggleProductRedirectSku);

    function toggleProductRedirectSku() {

        if ($('select#flipdevseo_discontinued').val() == 3)
        {
            $('.field-flipdevseo_discontinued_product').show()
            $('#flipdevseo_discontinued_product').addClass('required-entry');
        } else {
            $('.field-flipdevseo_discontinued_product').hide();
            $('#flipdevseo_discontinued_product').removeClass('required-entry');
        }

    }

    $(document).ready(function(){
        $('select#flipdevseo_discontinued').on('change', function() {
            toggleProductRedirectSku();
        });
    });
});
