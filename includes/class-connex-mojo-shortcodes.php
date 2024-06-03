<?php
class Connex_Mojo_Shortcodes {
    private $api;

    public function __construct( $api ) {
        $this->api = $api;
        add_shortcode( 'connex_mojo_login', array( $this, 'display_login_form' ) );
        add_shortcode( 'connex_mojo_committee_members', array( $this, 'display_committee_members' ) );
        add_shortcode( 'connex_mojo_all_events', array( $this, 'display_all_events' ) );
        add_shortcode( 'connex_mojo_member_details', array( $this, 'display_member_details' ) );
        add_shortcode( 'connex_mojo_member_details_updated_since', array( $this, 'display_member_details_updated_since' ) );
        add_shortcode( 'connex_mojo_all_members', array( $this, 'display_all_members' ) );
        add_shortcode( 'connex_mojo_all_committee_members', array( $this, 'display_all_committee_members' ) );
        add_shortcode( 'connex_mojo_all_committees', array( $this, 'display_all_committees' ) );
        add_shortcode( 'connex_mojo_event_details', array( $this, 'display_event_details' ) );
    }

    public function display_login_form() {
        ob_start();
        ?>
        <form id="connex-mojo-login-form" method="post">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Login</button>
        </form>
        <div id="login-result"></div>
        <script>
            jQuery(document).ready(function($) {
                $('#connex-mojo-login-form').on('submit', function(e) {
                    e.preventDefault();
                    var username = $('#username').val();
                    var password = $('#password').val();
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        method: 'POST',
                        data: {
                            action: 'connex_mojo_login',
                            username: username,
                            password: password
                        },
                        success: function(response) {
                            $('#login-result').html(response);
                        }
                    });
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }

    function shouldHidePosition($position) {
        $positionsToHideLowerCase = array("connex staff liaison");
        return isset($position) && in_array(strtolower($position), $positionsToHideLowerCase);
    }

    public function display_committee_members( $atts ) {
        $atts = shortcode_atts( array(
            'committee_id' => '',
        ), $atts, 'connex_mojo_committee_members' );

        if ( empty( $atts['committee_id'] ) ) {
            return 'Committee ID is required.';
        }

        $response = $this->api->request( '/ConnexFmCommittee/AllCommitteeMembers?committeeId=' . $atts['committee_id'] );

        ob_start();

        if ( $response ) {
    echo '<ul class="committee-members">';
    foreach ( $response as $member ) {
        echo '<li class="committee-member-item">';
        echo '<div class="member-card">';
        echo '<div class="member-icon">';
        echo '<img src="' . esc_url( 'https://prod24.connexfm.com/wp-content/uploads/2024/05/user-duotone-w.svg' ) . '" alt="Member Icon">';
        echo '</div>';
        echo '<div class="member-details">';
        echo '<p class="member-name">' . esc_html( $member['name'] ) . '</p>';
        echo '<p class="member-position">' . esc_html( $member['orgname'] ) . '</p>';
        echo '<p class="member-role">' . esc_html( $member['position'] ) . '</p>'; 
        echo '</div>';
        echo '</div>';
        echo '</li>';
    }
    echo '</ul>';
} else {
    echo 'No committee members found.';
}


        return ob_get_clean();
    }

public function display_all_events() {
    $response = $this->api->request('/ConnexFmEvent/AllEvent');

    ob_start();

    if ($response) {
        echo '<div class="events-grid">';
        foreach ($response as $event) {
            $event_id = esc_attr($event['eventId']);
            $event_url = add_query_arg('event_id', $event_id, get_permalink(get_page_by_path('event-details'))); // Assumes 'event-details' is the slug of your event details page

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

    return ob_get_clean();
}
	

   public function display_event_details() {
        if (!isset($_GET['event_id'])) {
            return 'Event ID is missing.';
        }

        $event_id = sanitize_text_field($_GET['event_id']);
        $response = $this->api->request('/ConnexFmEvent/EventInfo?eventId=' . $event_id);

        if (!$response) {
            return 'Event details not found.';
        }

        ob_start();
        echo '<div class="event-detail">';
        echo '<h1>' . esc_html($response['eventName']) . '</h1>';
        echo '<p><strong>Date:</strong> ' . esc_html($response['startDate']) . ' - ' . esc_html($response['endDate']) . '</p>';
        echo '<p><strong>Location:</strong> ' . esc_html($response['locationCity']) . ', ' . esc_html($response['locationStateId']) . '</p>';
        echo '<p>' . $response['htmlDescription'] . '</p>';
        echo '</div>';
        return ob_get_clean();
    }

    public function display_member_details_updated_since( $atts ) {
        $atts = shortcode_atts( array(
            'updated_since' => '',
        ), $atts, 'connex_mojo_member_details_updated_since' );

        if ( empty( $atts['updated_since'] ) ) {
            return 'Updated Since date is required.';
        }

        $response = $this->api->request( '/ConnexFmMember/MemberDetailBasedOnUpdatedSince?updatedSince=' . $atts['updated_since'] );

        ob_start();

        if ( $response ) {
            echo '<ul class="member-details-updated-since-list">';
            foreach ( $response as $member ) {
                echo '<li class="member-details-updated-since-item">';
                echo '<p><strong>First Name:</strong> ' . esc_html( $member['firstName'] ) . '</p>';
                echo '<p><strong>Last Name:</strong> ' . esc_html( $member['lastName'] ) . '</p>';
                echo '<p><strong>Email:</strong> ' . esc_html( $member['email'] ) . '</p>';
                echo '<p><strong>Job Title:</strong> ' . esc_html( $member['jobTitle'] ) . '</p>';
                echo '<p><strong>Organization:</strong> ' . esc_html( $member['orgname'] ) . '</p>';
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo 'No member details found.';
        }

        return ob_get_clean();
    }

    public function display_all_members( $atts ) {
        $atts = shortcode_atts( array(
            'offset' => 0,
            'limit' => 1000,
        ), $atts, 'connex_mojo_all_members' );

        $offset = intval($atts['offset']);
        $limit = intval($atts['limit']);

        $response = $this->api->request( '/ConnexFmMember/AllMembers?offset=' . $offset . '&limit=' . $limit );

        ob_start();

        if ( $response ) {
            echo '<ul class="all-members">';
            foreach ( $response as $member ) {
                echo '<li class="all-member-item">';
                echo '<p><strong>First Name:</strong> ' . esc_html( $member['firstName'] ) . '</p>';
                echo '<p><strong>Last Name:</strong> ' . esc_html( $member['lastName'] ) . '</p>';
                echo '<p><strong>Email:</strong> ' . esc_html( $member['email'] ) . '</p>';
                echo '<p><strong>Job Title:</strong> ' . esc_html( $member['jobTitle'] ) . '</p>';
                echo '<p><strong>Organization:</strong> ' . esc_html( $member['orgname'] ) . '</p>';
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo 'No members found.';
        }

        return ob_get_clean();
    }

    public function display_all_committee_members() {
        $response = $this->api->request( '/ConnexFmCommittee/AllCommitteeMembers' );

        ob_start();

        if ( $response ) {
            echo '<ul class="all-committee-members">';
            foreach ( $response as $member ) {
                echo '<li class="committee-member-item">';
                echo '<p><strong>Name:</strong> ' . esc_html( $member['name'] ) . '</p>';
                echo '<p><strong>Position:</strong> ' . esc_html( $member['position'] ) . '</p>';
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo 'No committee members found.';
        }

        return ob_get_clean();
    }

    
    public function display_all_committees() {
        $response = $this->api->request( '/ConnexFmCommittee/AllCommittee');

        ob_start();

        if ( $response ) {
            echo '<ul class="all-committees">';
            foreach ( $response as $member ) {
                echo '<li class="committee-item">';
                echo '<p><strong>ID:</strong> ' . esc_html( $member['committeeId'] ) . '</p>';
                echo '<p><strong>Name:</strong> ' . esc_html( $member['name'] ) . '</p>';
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo 'No committees found.';
        }

        return ob_get_clean();
    }

    


}
