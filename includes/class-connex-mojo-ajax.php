<?php
// AJAX handler for fetching and filtering events
add_action('wp_ajax_nopriv_connex_mojo_search_events', 'connex_mojo_search_events');
add_action('wp_ajax_connex_mojo_search_events', 'connex_mojo_search_events');

function connex_mojo_search_events() {
    check_ajax_referer('connex_mojo_nonce', 'nonce');

    $search = sanitize_text_field($_POST['search']);
    $start_date = sanitize_text_field($_POST['start_date']);
    $end_date = sanitize_text_field($_POST['end_date']);
    $status = sanitize_text_field($_POST['status']);

    // Make API request to get all events
    $api = new Connex_Mojo_API(); // Ensure this is the correct instantiation for your API class
    $response = $api->request('/ConnexFmEvent/AllEvent');

    if (!$response) {
        wp_send_json_error('No events found.');
    }

    // Filter events based on search criteria
    $filtered_events = array_filter($response, function($event) use ($search, $start_date, $end_date, $status) {
        $event_start_date = strtotime($event['startDate']);
        $event_end_date = strtotime($event['endDate']);
        $today = strtotime(date('Y-m-d'));

        $search_match = empty($search) || stripos($event['eventName'], $search) !== false;
        $start_date_match = empty($start_date) || $event_start_date >= strtotime($start_date);
        $end_date_match = empty($end_date) || $event_end_date <= strtotime($end_date);
        $status_match = $status === 'all' || ($status === 'active' && $event_start_date >= $today) || ($status === 'inactive' && $event_start_date < $today);

        return $search_match && $start_date_match && $end_date_match && $status_match;
    });

    // Sort events by start date in ascending order
    usort($filtered_events, function($a, $b) {
        return strtotime($a['startDate']) - strtotime($b['startDate']);
    });

    ob_start();
    if ($filtered_events) {
        echo '<div class="events-grid">';
        foreach ($filtered_events as $event) {
            $event_id = esc_attr($event['eventId']);
            $event_url = add_query_arg('event_id', $event_id, get_permalink(get_page_by_path('event-details')));

            echo '<a href="' . $event_url . '" class="event-item">';
            echo '<div class="event-date">';
            echo '<span class="event-month">' . esc_html(date('M', strtotime($event['startDate']))) . '</span>';
            echo '<span class="event-day">' . esc_html(date('d', strtotime($event['startDate']))) . '</span>';
            echo '</div>';
            echo '<div class="event-details">';
            echo '<h3 class="event-name">' . esc_html($event['eventName']) . '</h3>';
            echo '</div>';
            echo '</a>';
        }
        echo '</div>';
    } else {
        echo 'No events found.';
    }

    wp_send_json_success(ob_get_clean());
}

