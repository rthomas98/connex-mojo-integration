<?php
class Connex_Mojo_Endpoints {
    private $api;

    public function __construct( $api ) {
        $this->api = $api;
        add_action( 'wp_ajax_connex_mojo_get_committee_members', array( $this, 'get_committee_members' ) );
        add_action( 'wp_ajax_nopriv_connex_mojo_get_committee_members', array( $this, 'get_committee_members' ) );
        add_action( 'wp_ajax_connex_mojo_get_all_events', array( $this, 'get_all_events' ) );
        add_action( 'wp_ajax_nopriv_connex_mojo_get_all_events', array( $this, 'get_all_events' ) );
        add_action( 'wp_ajax_connex_mojo_get_member_details', array( $this, 'get_member_details' ) );
        add_action( 'wp_ajax_nopriv_connex_mojo_get_member_details', array( $this, 'get_member_details' ) );
        add_action( 'wp_ajax_connex_mojo_get_member_details_updated_since', array( $this, 'get_member_details_updated_since' ) );
        add_action( 'wp_ajax_nopriv_connex_mojo_get_member_details_updated_since', array( $this, 'get_member_details_updated_since' ) );
        add_action( 'wp_ajax_connex_mojo_get_all_members', array( $this, 'get_all_members' ) );
        add_action( 'wp_ajax_nopriv_connex_mojo_get_all_members', array( $this, 'get_all_members' ) );
        add_action( 'wp_ajax_connex_mojo_get_all_committee_members', array( $this, 'get_all_committee_members' ) );
        add_action( 'wp_ajax_nopriv_connex_mojo_get_all_committee_members', array( $this, 'get_all_committee_members' ) );
    }

    public function get_committee_members() {
        $committee_id = $_POST['committee_id'];
        $response = $this->api->request( '/ConnexFmCommittee/AllCommitteeMembers?committeeId=' . $committee_id );

        if ( $response ) {
            echo '<ul class="committee-members">';
            foreach ( $response as $member ) {
                echo '<li class="committee-member-item">' . esc_html( $member['name'] ) . ' (' . esc_html( $member['position'] ) . ')</li>';
            }
            echo '</ul>';
        } else {
            echo 'No committee members found.';
        }

        wp_die();
    }

    public function get_all_events() {
        $response = $this->api->request( '/ConnexFmEvent/AllEvent' );

        if ( $response ) {
            echo '<ul class="events">';
            foreach ( $response as $event ) {
                echo '<li class="event-item">';
                echo '<h3>' . esc_html( $event['eventName'] ) . '</h3>';
                echo '<p><strong>Date:</strong> ' . esc_html( $event['startDate'] ) . ' - ' . esc_html( $event['endDate'] ) . '</p>';
                echo '<p><strong>Location:</strong> ' . esc_html( $event['locationCityId'] ) . ', ' . esc_html( $event['locationStateId'] ) . '</p>';
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo 'No events found.';
        }

        wp_die();
    }

    public function get_member_details() {
        $customer_id = $_POST['customer_id'];
        $response = $this->api->request( '/ConnexFmMember/MemberDetail?customerId=' . $customer_id );

        if ( $response ) {
            echo '<div class="member-detail">';
            echo '<p><strong>First Name:</strong> ' . esc_html( $response['firstName'] ) . '</p>';
            echo '<p><strong>Last Name:</strong> ' . esc_html( $response['lastName'] ) . '</p>';
            echo '<p><strong>Email:</strong> ' . esc_html( $response['email'] ) . '</p>';
            echo '<p><strong>Job Title:</strong> ' . esc_html( $response['jobTitle'] ) . '</p>';
            echo '<p><strong>Organization:</strong> ' . esc_html( $response['orgname'] ) . '</p>';
            echo '</div>';
        } else {
            echo 'No member details found.';
        }

        wp_die();
    }

    public function get_member_details_updated_since() {
        $updated_since = $_POST['updated_since'];
        $response = $this->api->request( '/ConnexFmMember/MemberDetailBasedOnUpdatedSince?updatedSince=' . $updated_since );

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

        wp_die();
    }

    public function get_all_members() {
        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 1000;
        $response = $this->api->request( '/ConnexFmMember/AllMembers?offset=' . $offset . '&limit=' . $limit );

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

        wp_die();
    }

    public function get_all_committee_members() {
        $response = $this->api->request( '/ConnexFmCommittee/AllCommitteeMembers' );

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

        wp_die();
    }
}
