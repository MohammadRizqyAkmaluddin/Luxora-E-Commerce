
$(document).ready(function(){
    $('#searchOnly').on('keyup', function(){
        let query = $(this).val();
        if (query.length > 0) {
            $.ajax({
                url: 'searchProductIndex.php',
                method: 'GET',
                data: { searchOnly: query },
                success: function(data) {
                    $('#searchDropdown').html(data).show();
                }
            });
        } else {
            $('#searchDropdown').hide();
        }
    });

    // Hide dropdown saat klik luar
    $(document).on('click', function(e){
        if (!$(e.target).closest('.search').length) {
            $('#searchDropdown').hide();
        }
    });
});