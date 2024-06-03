jQuery(document).ready(function($) {
    $('.btn').on('click', function(e) {
        e.preventDefault();
        var action = $(this).data('action');
        var data = {
            action: action
        };

        if (action === 'connex_mojo_get_committee_members') {
            data.committee_id = $(this).data('committee-id');
        } else if (action === 'connex_mojo_get_member_details') {
            data.customer_id = $(this).data('customer-id');
        } else if (action === 'connex_mojo_get_member_details_updated_since') {
            data.updated_since = $(this).data('updated-since');
        }

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: data,
            success: function(response) {
                $('#' + action + '-result').html(response);
            }
        });
    });

    $('#fetch-all-committee-members').on('click', function(e) {
        e.preventDefault();
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'connex_mojo_get_all_committee_members'
            },
            success: function(response) {
                $('#all-committee-members-result').html(response);
            }
        });
    });
});
