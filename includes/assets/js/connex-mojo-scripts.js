jQuery(document).ready(function($) {
    // Existing button click handlers
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

    // Function to fetch events
    function fetchEvents(data) {
        $.post(connexMojo.ajax_url, data, function(response) {
            if (response.success) {
                $('#connex-mojo-events-results').html(response.data);
            } else {
                $('#connex-mojo-events-results').html('<p>No events found.</p>');
            }
        });
    }

    // Fetch active events on page load
    fetchEvents({
        action: 'connex_mojo_search_events',
        nonce: connexMojo.nonce,
        search: '',
        start_date: '',
        end_date: '',
        status: 'active'
    });

    // Handle form submission
    $('#connex-mojo-search-form').on('submit', function(e) {
        e.preventDefault();

        var data = {
            action: 'connex_mojo_search_events',
            nonce: connexMojo.nonce,
            search: $('#search').val(),
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            status: $('#status').val()
        };

        fetchEvents(data);
    });

    // Handle form reset
    $('#reset').on('click', function() {
        $('#connex-mojo-search-form').trigger('reset');
        $('#connex-mojo-events-results').html('');
        fetchEvents({
            action: 'connex_mojo_search_events',
            nonce: connexMojo.nonce,
            search: '',
            start_date: '',
            end_date: '',
            status: 'active'
        });
    });
});
