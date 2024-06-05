<?php
class Connex_Mojo_Shortcodes {
    private $api;

    public function __construct( $api ) {
        $this->api = $api;
        add_shortcode('connex_mojo_login', array($this, 'display_login_form'));
        add_shortcode('connex_mojo_committee_members', array($this, 'display_committee_members'));
        add_shortcode('connex_mojo_all_events', array($this, 'display_all_events'));
        add_shortcode('connex_mojo_member_details', array($this, 'display_member_details'));
        add_shortcode('connex_mojo_member_details_updated_since', array($this, 'display_member_details_updated_since'));
        add_shortcode('connex_mojo_all_members', array($this, 'display_all_members'));
        add_shortcode('connex_mojo_all_committee_members', array($this, 'display_all_committee_members'));
        add_shortcode('connex_mojo_all_committees', array($this, 'display_all_committees'));
        add_shortcode('connex_mojo_event_details', array($this, 'display_event_details'));
        add_shortcode('connex_mojo_search_form', array($this, 'display_search_form'));
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

    function shouldHideCommitteeMemberPosition($position) {
        $positionsToHideLowerCase = array("connex staff liaison"); # in case we need to hide others later
        return isset($position) && in_array(strtolower($position), $positionsToHideLowerCase);
    }

    function formatCommitteeMemberName($name) {
        # change "LAST, FIRST" to "FIRST LAST"
        $nameParts = explode(",", $name, 2);
        if (count($nameParts) == 2) {
            return trim($nameParts[1]) . " " . trim($nameParts[0]);
        }
        return $name;
    }

    const COMMITTEE_POSITION_CHAIR = "Chair";
    function sortCommitteeMembers(array &$memberArray) {
        # show members with position containing "Chair" first; otherwise, sort by name (assuming "LAST, FIRST" format)
        usort($memberArray, function($a,$b) {
            $positionA = $a['position'];
            $positionB = $b['position'];
            $memberIsChairA = stripos($positionA, self::COMMITTEE_POSITION_CHAIR) !== false;
            $memberIsChairB = stripos($positionB, self::COMMITTEE_POSITION_CHAIR) !== false;
            if ($memberIsChairA && !$memberIsChairB) {
                return -1;
            } else if (!$memberIsChairA && $memberIsChairB) {
                return 1;
            } else {
                return strcasecmp($a['name'], $b['name']);
            }
        });
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
            $this->sortCommitteeMembers($response);

    echo '<ul class="committee-members">';
    foreach ( $response as $member ) {

        $position = esc_html( $member['position'] );
        if ($this->shouldHideCommitteeMemberPosition($position)) {
            continue;
        }

        echo '<li class="committee-member-item">';
        echo '<div class="member-card">';
        echo '<div class="member-icon">';
        echo '<img src="' . esc_url( '/wp-content/uploads/2024/05/user-duotone-w.svg' ) . '" alt="Member Icon">';
        echo '</div>';
        echo '<div class="member-details">';
        echo '<p class="member-name">' . $this->formatCommitteeMemberName(esc_html( $member['name'] )) . '</p>';
        echo '<p class="member-position">' . esc_html( $member['orgname'] ) . '</p>';
        echo '<p class="member-role">' . mb_convert_case($position, MB_CASE_TITLE) . '</p>';
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

    // Events search form
    public function display_search_form() {
        ob_start();
        ?>
        <form id="connex-mojo-search-form">
            <input type="text" id="search" name="search" placeholder="Search events">
            <input type="date" id="start_date" name="start_date">
            <input type="date" id="end_date" name="end_date">
            <select id="status" name="status">
                <option value="all">All</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <button type="submit">Search</button>
            <button type="button" id="reset">Reset</button>
        </form>
        <div id="connex-mojo-events-results"></div>
        <?php
        return ob_get_clean();
    }



    // Display all events
    public function display_all_events() {
        $response = $this->api->request('/ConnexFmEvent/AllEvent');

        if (!$response) {
            return 'No events found.';
        }

        // Get today's date
        $today = strtotime(date('Y-m-d'));

        // Filter only active events
        $active_events = array_filter($response, function($event) use ($today) {
            return strtotime($event['startDate']) >= $today;
        });

        // Sort events by start date in descending order
        usort($active_events, function($a, $b) {
            return strtotime($b['startDate']) - strtotime($a['startDate']);
        });

        ob_start();

        echo '<div class="events-grid">';
        foreach ($active_events as $event) {
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
        ?>
        <div class="">
            <div class="event-header-wrapper-detail">
                <div class="event-header-detail">
                    <div class="event-title">
                        <h1><?php echo esc_html($response['eventName']); ?></h1>
                        <p class="event-date-detail">
                            <strong>Start Date - End Date:</strong><br>
                            <?php echo esc_html($response['startDate']); ?> - <?php echo esc_html($response['endDate']); ?>
                        </p>

                        <p class="event-location-detail">
                            <strong>Location:</strong> <?php echo esc_html($response['locationCity']); ?>, <?php echo esc_html($response['locationStateId']); ?>
                        </p>
                    </div>
                    <div class="event-registration-detail">
                        <h2><strong>Registration Deadline:</strong></h2>
                        <button class="btn-detail">Register</button>

                    </div>
                </div>
            </div>
            <div class="event-description-detail">
                <?php echo $response['htmlDescription']; ?>
            </div>

        </div>
        <?php
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
