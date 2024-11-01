<?php

/**
 * Plugin Name: Simple Contact Button
 * Description: Easily add a straightforward contact button to your WordPress site. Enable visitors to reach you instantly with a single click. Customize the button's position, icon, and message for a seamless communication experience.
 * Version: 1.3
 * Author: BBSEO Ventures
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: simple-contact-button
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( function_exists( 'scb_fs' ) ) {
    scb_fs()->set_basename( false, __FILE__ );
} else {
    if ( !function_exists( 'scb_fs' ) ) {
        function scb_fs() {
            global $scb_fs;
            if ( !isset( $scb_fs ) ) {
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $scb_fs = fs_dynamic_init( array(
                    'id'             => '16631',
                    'slug'           => 'simple-contact-button',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_4ba6d769de6cd2943c2a914968dd5',
                    'is_premium'     => false,
                    'premium_suffix' => 'Premium',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'menu'           => array(
                        'slug'    => 'simple-contact-button',
                        'support' => false,
                    ),
                    'is_live'        => true,
                ) );
            }
            return $scb_fs;
        }

        scb_fs();
        do_action( 'scb_fs_loaded' );
    }
    class Simple_Contact_Button {
        private static $instance = null;

        private function __construct() {
            add_action( 'plugins_loaded', [$this, 'load_textdomain'] );
            add_action( 'admin_menu', [$this, 'add_admin_menu'] );
            add_action( 'admin_init', [$this, 'simple_contact_button_register_settings'] );
            add_action( 'wp_footer', [$this, 'add_contact_icon'] );
            add_action( 'wp_enqueue_scripts', [$this, 'enqueue_styles'] );
            add_action( 'wp_enqueue_scripts', [$this, 'simple_contact_button_enqueue_scripts'] );
            add_action( 'admin_enqueue_scripts', [$this, 'simple_contact_button_enqueue_admin_scripts'] );
            add_action( 'wp_ajax_get_specific_posts', [$this, 'simple_contact_button_get_specific_posts'] );
            add_action( 'wp_ajax_nopriv_get_specific_posts', [$this, 'simple_contact_button_get_specific_posts'] );
        }

        public function load_textdomain() {
            $loaded = load_plugin_textdomain( 'simple-contact-button', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
            if ( !$loaded ) {
                error_log( 'Failed to load textdomain for Simple Contact Button plugin.' );
            } else {
                error_log( 'Textdomain for Simple Contact Button plugin loaded successfully.' );
            }
        }

        public static function get_instance() {
            if ( self::$instance === null ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function add_admin_menu() {
            add_menu_page(
                __( 'Simple Contact Button', 'simple-contact-button' ),
                __( 'Simple Contact Button', 'simple-contact-button' ),
                'manage_options',
                'simple-contact-button',
                [$this, 'settings_page'],
                'dashicons-whatsapp'
            );
        }

        public function settings_page() {
            $activation_setting = get_option( 'simple_contact_button_activation', 'sitewide' );
            $saved_post_type = get_option( 'simple_contact_button_post_type', '' );
            error_log( 'Debug: saved_post_type = ' . $saved_post_type );
            $saved_page_id = get_option( 'simple_contact_button_specific_page', '' );
            if ( isset( $_GET['settings-updated'] ) && sanitize_text_field( wp_unslash( $_GET['settings-updated'] ) ) === 'true' ) {
                echo '<div id="settings-saved-notification" class="notice notice-success is-dismissible">';
                echo '<p>' . esc_html_e( 'Settings Saved', 'simple-contact-button' ) . '</p>';
                echo '</div>';
            }
            ?>
        <div style="display: flex;">
            <div style="width: 70%; padding-right: 20px;">
                <h1><?php 
            echo esc_html( get_admin_page_title() );
            ?></h1>
                <form method="post" action="options.php" id="simple-contact-button-settings-form">
					
                    <?php 
            wp_nonce_field( 'simple_contact_button_save', 'simple_contact_button_nonce' );
            settings_fields( 'simple_contact_button_settings_group' );
            do_settings_sections( 'simple-contact-button' );
            ?>
                <?php 
            ?>
    <table class="form-table">
        <p><?php 
            esc_html_e( 'To make the WhatsApp button appear on specific pages, posts, and only on the homepage, and to activate the analytics tracker feature, you need to purchase the premium version.', 'simple-contact-button' );
            ?></p>
        <a href="<?php 
            echo esc_url( scb_fs()->get_upgrade_url() );
            ?>" class="button-secondary">
            <?php 
            esc_html_e( 'Upgrade to Premium', 'simple-contact-button' );
            ?>
        </a>
        <tr valign="top">
            <th scope="row"><?php 
            esc_html_e( 'Button Activation', 'simple-contact-button' );
            ?></th>
            <td>
                <select id="" name="simple_contact_button_activation" disabled>
                    <option value="sitewide" <?php 
            selected( $activation_setting, 'sitewide' );
            ?>><?php 
            esc_html_e( 'Sitewide', 'simple-contact-button' );
            ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php 
            esc_html_e( 'Select Post Type', 'simple-contact-button' );
            ?></th>
            <td>
                <select id="" name="simple_contact_button_post_type" disabled>
                    <option value=""><?php 
            esc_html_e( 'Select', 'simple-contact-button' );
            ?></option>
                    <option value="post" <?php 
            selected( $saved_post_type, 'post' );
            ?>><?php 
            esc_html_e( 'Post', 'simple-contact-button' );
            ?></option>
                </select>
            </td>
        </tr>
    </table>
<?php 
            ?>

                
				
                <?php 
            submit_button();
            ?>
            </form>
        </div>
    

            <div style="width: 30%; text-align: center;">
                <div style="padding: 20px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 8px; max-width: 100%; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
                    <h2 style="font-size: 24px; margin-bottom: 10px;">BB Seo Ventures</h2>
                    
                    <p style="font-size: 14px; margin-bottom: 10px;"><?php 
            esc_html_e( 'If you love our plugin, please consider supporting us by clicking the Donate button below.', 'simple-contact-button' );
            ?> 😊</p>
                    <a href="https://buymeacoffee.com/bbseo" target="_blank" style="display: inline-block; margin-top: 10px; background-color: #ffdd00; padding: 10px 20px; font-size: 16px; font-weight: bold; text-decoration: none; color: #000; border-radius: 5px;">
                        <?php 
            esc_html_e( 'Donate', 'simple-contact-button' );
            ?>
                    </a>
                </div>
            </div>
            </div>
<?php 
            ?>
    <script>
         document.addEventListener('DOMContentLoaded', function () {
                const notification = document.querySelector('.notice.is-dismissible');
                if (notification) {
                    setTimeout(() => {
                        notification.style.display = 'none';
                    }, 3000);
                }
            });
    </script>
        <?php 
        }

        public function simple_contact_button_register_settings() {
            register_setting( 'simple_contact_button_settings_group', 'simple_contact_button_phone_number', [
                'sanitize_callback' => [$this, 'sanitize_phone_number'],
            ] );
            register_setting( 'simple_contact_button_settings_group', 'simple_contact_button_initial_message', [
                'sanitize_callback' => [$this, 'sanitize_message'],
            ] );
            register_setting( 'simple_contact_button_settings_group', 'simple_contact_button_icon_position', [
                'sanitize_callback' => [$this, 'sanitize_icon_position'],
            ] );
            register_setting( 'simple_contact_button_settings_group', 'simple_contact_button_device_visibility', [
                'sanitize_callback' => [$this, 'sanitize_device_visibility'],
            ] );
            register_setting( 'simple_contact_button_settings_group', 'simple_contact_button_icon_selection', [
                'sanitize_callback' => [$this, 'sanitize_icon_selection'],
            ] );
            add_settings_section(
                'simple_contact_button_settings_section',
                __( 'Settings', 'simple-contact-button' ),
                [$this, 'settings_section_callback'],
                'simple-contact-button'
            );
            add_settings_field(
                'simple_contact_button_phone_number',
                __( 'Enter Contact Phone Number', 'simple-contact-button' ),
                [$this, 'phone_number_callback'],
                'simple-contact-button',
                'simple_contact_button_settings_section'
            );
            add_settings_field(
                'simple_contact_button_initial_message',
                __( 'Set Initial Contact Message', 'simple-contact-button' ),
                [$this, 'initial_message_callback'],
                'simple-contact-button',
                'simple_contact_button_settings_section'
            );
            add_settings_field(
                'simple_contact_button_icon_position',
                __( 'Select Icon Position', 'simple-contact-button' ),
                [$this, 'icon_position_callback'],
                'simple-contact-button',
                'simple_contact_button_settings_section'
            );
            add_settings_field(
                'simple_contact_button_device_visibility',
                __( 'Choose Device Visibility', 'simple-contact-button' ),
                [$this, 'device_visibility_callback'],
                'simple-contact-button',
                'simple_contact_button_settings_section'
            );
            add_settings_field(
                'simple_contact_button_icon_selection',
                __( 'Pick an Icon', 'simple-contact-button' ),
                [$this, 'icon_selection_callback'],
                'simple-contact-button',
                'simple_contact_button_settings_section'
            );
            register_setting( 'simple_contact_button_settings_group', 'simple_contact_button_activation', [
                'sanitize_callback' => [$this, 'sanitize_activation_setting'],
            ] );
            register_setting( 'simple_contact_button_settings_group', 'simple_contact_button_specific_page', [
                'sanitize_callback' => 'absint',
            ] );
            register_setting( 'simple_contact_button_settings_group', 'simple_contact_button_post_type', [
                'sanitize_callback' => [$this, 'sanitize_post_type'],
            ] );
        }

        public function settings_section_callback() {
            esc_html_e( 'Configure your contact button by entering your phone number, setting an initial message, choosing the icon position and visibility, and selecting your preferred icon design.', 'simple-contact-button' );
        }

        public function phone_number_callback() {
            $phone_number = get_option( 'simple_contact_button_phone_number' );
            $country_code = substr( $phone_number, 0, strpos( $phone_number, ' ' ) );
            $number = substr( $phone_number, strpos( $phone_number, ' ' ) + 1 );
            ?>
        <select name="simple_contact_button_country_code">
        <option value="+90" <?php 
            selected( $country_code, '+90' );
            ?>><?php 
            esc_html_e( 'Turkey (+90)', 'simple-contact-button' );
            ?></option>
        <option value="+44" <?php 
            selected( $country_code, '+44' );
            ?>><?php 
            esc_html_e( 'United Kingdom (+44)', 'simple-contact-button' );
            ?></option>
        <option value="+1" <?php 
            selected( $country_code, '+1' );
            ?>><?php 
            esc_html_e( 'United States (+1)', 'simple-contact-button' );
            ?></option>
        <option value="+49" <?php 
            selected( $country_code, '+49' );
            ?>><?php 
            esc_html_e( 'Germany (+49)', 'simple-contact-button' );
            ?></option>
        <option value="+33" <?php 
            selected( $country_code, '+33' );
            ?>><?php 
            esc_html_e( 'France (+33)', 'simple-contact-button' );
            ?></option>
        <option value="+39" <?php 
            selected( $country_code, '+39' );
            ?>><?php 
            esc_html_e( 'Italy (+39)', 'simple-contact-button' );
            ?></option>
        <option value="+34" <?php 
            selected( $country_code, '+34' );
            ?>><?php 
            esc_html_e( 'Spain (+34)', 'simple-contact-button' );
            ?></option>
        <option value="+7" <?php 
            selected( $country_code, '+7' );
            ?>><?php 
            esc_html_e( 'Russia (+7)', 'simple-contact-button' );
            ?></option>
        <option value="+81" <?php 
            selected( $country_code, '+81' );
            ?>><?php 
            esc_html_e( 'Japan (+81)', 'simple-contact-button' );
            ?></option>
        <option value="+86" <?php 
            selected( $country_code, '+86' );
            ?>><?php 
            esc_html_e( 'China (+86)', 'simple-contact-button' );
            ?></option>
        <option value="+91" <?php 
            selected( $country_code, '+91' );
            ?>><?php 
            esc_html_e( 'India (+91)', 'simple-contact-button' );
            ?></option>
        <option value="+61" <?php 
            selected( $country_code, '+61' );
            ?>><?php 
            esc_html_e( 'Australia (+61)', 'simple-contact-button' );
            ?></option>
        <option value="+55" <?php 
            selected( $country_code, '+55' );
            ?>><?php 
            esc_html_e( 'Brazil (+55)', 'simple-contact-button' );
            ?></option>
        <option value="+52" <?php 
            selected( $country_code, '+52' );
            ?>><?php 
            esc_html_e( 'Mexico (+52)', 'simple-contact-button' );
            ?></option>
        <option value="+1" <?php 
            selected( $country_code, '+1' );
            ?>><?php 
            esc_html_e( 'Canada (+1)', 'simple-contact-button' );
            ?></option>
        <option value="+31" <?php 
            selected( $country_code, '+31' );
            ?>><?php 
            esc_html_e( 'Netherlands (+31)', 'simple-contact-button' );
            ?></option>
        <option value="+27" <?php 
            selected( $country_code, '+27' );
            ?>><?php 
            esc_html_e( 'South Africa (+27)', 'simple-contact-button' );
            ?></option>
        <option value="+46" <?php 
            selected( $country_code, '+46' );
            ?>><?php 
            esc_html_e( 'Sweden (+46)', 'simple-contact-button' );
            ?></option>
        <option value="+82" <?php 
            selected( $country_code, '+82' );
            ?>><?php 
            esc_html_e( 'South Korea (+82)', 'simple-contact-button' );
            ?></option>
        <option value="+65" <?php 
            selected( $country_code, '+65' );
            ?>><?php 
            esc_html_e( 'Singapore (+65)', 'simple-contact-button' );
            ?></option>
        <option value="+62" <?php 
            selected( $country_code, '+62' );
            ?>><?php 
            esc_html_e( 'Indonesia (+62)', 'simple-contact-button' );
            ?></option>
        <option value="+60" <?php 
            selected( $country_code, '+60' );
            ?>><?php 
            esc_html_e( 'Malaysia (+60)', 'simple-contact-button' );
            ?></option>
        <option value="+64" <?php 
            selected( $country_code, '+64' );
            ?>><?php 
            esc_html_e( 'New Zealand (+64)', 'simple-contact-button' );
            ?></option>
        <option value="+66" <?php 
            selected( $country_code, '+66' );
            ?>><?php 
            esc_html_e( 'Thailand (+66)', 'simple-contact-button' );
            ?></option>
        <option value="+971" <?php 
            selected( $country_code, '+971' );
            ?>><?php 
            esc_html_e( 'United Arab Emirates (+971)', 'simple-contact-button' );
            ?></option>
        <option value="+20" <?php 
            selected( $country_code, '+20' );
            ?>><?php 
            esc_html_e( 'Egypt (+20)', 'simple-contact-button' );
            ?></option>
        <option value="+212" <?php 
            selected( $country_code, '+212' );
            ?>><?php 
            esc_html_e( 'Morocco (+212)', 'simple-contact-button' );
            ?></option>
        <option value="+234" <?php 
            selected( $country_code, '+234' );
            ?>><?php 
            esc_html_e( 'Nigeria (+234)', 'simple-contact-button' );
            ?></option>
        <option value="+358" <?php 
            selected( $country_code, '+358' );
            ?>><?php 
            esc_html_e( 'Finland (+358)', 'simple-contact-button' );
            ?></option>
        <option value="+93" <?php 
            selected( $country_code, '+93' );
            ?>><?php 
            esc_html_e( 'Afghanistan (+93)', 'simple-contact-button' );
            ?></option>
        <option value="+355" <?php 
            selected( $country_code, '+355' );
            ?>><?php 
            esc_html_e( 'Albania (+355)', 'simple-contact-button' );
            ?></option>
        <option value="+213" <?php 
            selected( $country_code, '+213' );
            ?>><?php 
            esc_html_e( 'Algeria (+213)', 'simple-contact-button' );
            ?></option>
        <option value="+376" <?php 
            selected( $country_code, '+376' );
            ?>><?php 
            esc_html_e( 'Andorra (+376)', 'simple-contact-button' );
            ?></option>
        <option value="+244" <?php 
            selected( $country_code, '+244' );
            ?>><?php 
            esc_html_e( 'Angola (+244)', 'simple-contact-button' );
            ?></option>
        <option value="+54" <?php 
            selected( $country_code, '+54' );
            ?>><?php 
            esc_html_e( 'Argentina (+54)', 'simple-contact-button' );
            ?></option>
        <option value="+374" <?php 
            selected( $country_code, '+374' );
            ?>><?php 
            esc_html_e( 'Armenia (+374)', 'simple-contact-button' );
            ?></option>
        <option value="+43" <?php 
            selected( $country_code, '+43' );
            ?>><?php 
            esc_html_e( 'Austria (+43)', 'simple-contact-button' );
            ?></option>
        <option value="+994" <?php 
            selected( $country_code, '+994' );
            ?>><?php 
            esc_html_e( 'Azerbaijan (+994)', 'simple-contact-button' );
            ?></option>
        <option value="+973" <?php 
            selected( $country_code, '+973' );
            ?>><?php 
            esc_html_e( 'Bahrain (+973)', 'simple-contact-button' );
            ?></option>
        <option value="+880" <?php 
            selected( $country_code, '+880' );
            ?>><?php 
            esc_html_e( 'Bangladesh (+880)', 'simple-contact-button' );
            ?></option>
        <option value="+375" <?php 
            selected( $country_code, '+375' );
            ?>><?php 
            esc_html_e( 'Belarus (+375)', 'simple-contact-button' );
            ?></option>
        <option value="+32" <?php 
            selected( $country_code, '+32' );
            ?>><?php 
            esc_html_e( 'Belgium (+32)', 'simple-contact-button' );
            ?></option>
        <option value="+501" <?php 
            selected( $country_code, '+501' );
            ?>><?php 
            esc_html_e( 'Belize (+501)', 'simple-contact-button' );
            ?></option>
        <option value="+229" <?php 
            selected( $country_code, '+229' );
            ?>><?php 
            esc_html_e( 'Benin (+229)', 'simple-contact-button' );
            ?></option>
        <option value="+975" <?php 
            selected( $country_code, '+975' );
            ?>><?php 
            esc_html_e( 'Bhutan (+975)', 'simple-contact-button' );
            ?></option>
        <option value="+591" <?php 
            selected( $country_code, '+591' );
            ?>><?php 
            esc_html_e( 'Bolivia (+591)', 'simple-contact-button' );
            ?></option>
        <option value="+387" <?php 
            selected( $country_code, '+387' );
            ?>><?php 
            esc_html_e( 'Bosnia and Herzegovina (+387)', 'simple-contact-button' );
            ?></option>
        <option value="+267" <?php 
            selected( $country_code, '+267' );
            ?>><?php 
            esc_html_e( 'Botswana (+267)', 'simple-contact-button' );
            ?></option>
        <option value="+673" <?php 
            selected( $country_code, '+673' );
            ?>><?php 
            esc_html_e( 'Brunei (+673)', 'simple-contact-button' );
            ?></option>
        <option value="+359" <?php 
            selected( $country_code, '+359' );
            ?>><?php 
            esc_html_e( 'Bulgaria (+359)', 'simple-contact-button' );
            ?></option>
        <option value="+226" <?php 
            selected( $country_code, '+226' );
            ?>><?php 
            esc_html_e( 'Burkina Faso (+226)', 'simple-contact-button' );
            ?></option>
        <option value="+257" <?php 
            selected( $country_code, '+257' );
            ?>><?php 
            esc_html_e( 'Burundi (+257)', 'simple-contact-button' );
            ?></option>
        <option value="+855" <?php 
            selected( $country_code, '+855' );
            ?>><?php 
            esc_html_e( 'Cambodia (+855)', 'simple-contact-button' );
            ?></option>
        <option value="+237" <?php 
            selected( $country_code, '+237' );
            ?>><?php 
            esc_html_e( 'Cameroon (+237)', 'simple-contact-button' );
            ?></option>
        <option value="+238" <?php 
            selected( $country_code, '+238' );
            ?>><?php 
            esc_html_e( 'Cape Verde (+238)', 'simple-contact-button' );
            ?></option>
        <option value="+236" <?php 
            selected( $country_code, '+236' );
            ?>><?php 
            esc_html_e( 'Central African Republic (+236)', 'simple-contact-button' );
            ?></option>
        <option value="+235" <?php 
            selected( $country_code, '+235' );
            ?>><?php 
            esc_html_e( 'Chad (+235)', 'simple-contact-button' );
            ?></option>
        <option value="+56" <?php 
            selected( $country_code, '+56' );
            ?>><?php 
            esc_html_e( 'Chile (+56)', 'simple-contact-button' );
            ?></option>
        <option value="+57" <?php 
            selected( $country_code, '+57' );
            ?>><?php 
            esc_html_e( 'Colombia (+57)', 'simple-contact-button' );
            ?></option>
        <option value="+269" <?php 
            selected( $country_code, '+269' );
            ?>><?php 
            esc_html_e( 'Comoros (+269)', 'simple-contact-button' );
            ?></option>
        <option value="+243" <?php 
            selected( $country_code, '+243' );
            ?>><?php 
            esc_html_e( 'Democratic Republic of the Congo (+243)', 'simple-contact-button' );
            ?></option>
        <option value="+242" <?php 
            selected( $country_code, '+242' );
            ?>><?php 
            esc_html_e( 'Republic of the Congo (+242)', 'simple-contact-button' );
            ?></option>
        <option value="+506" <?php 
            selected( $country_code, '+506' );
            ?>><?php 
            esc_html_e( 'Costa Rica (+506)', 'simple-contact-button' );
            ?></option>
        <option value="+385" <?php 
            selected( $country_code, '+385' );
            ?>><?php 
            esc_html_e( 'Croatia (+385)', 'simple-contact-button' );
            ?></option>
        <option value="+53" <?php 
            selected( $country_code, '+53' );
            ?>><?php 
            esc_html_e( 'Cuba (+53)', 'simple-contact-button' );
            ?></option>
        <option value="+357" <?php 
            selected( $country_code, '+357' );
            ?>><?php 
            esc_html_e( 'Cyprus (+357)', 'simple-contact-button' );
            ?></option>
        <option value="+420" <?php 
            selected( $country_code, '+420' );
            ?>><?php 
            esc_html_e( 'Czech Republic (+420)', 'simple-contact-button' );
            ?></option>
        <option value="+45" <?php 
            selected( $country_code, '+45' );
            ?>><?php 
            esc_html_e( 'Denmark (+45)', 'simple-contact-button' );
            ?></option>
        <option value="+253" <?php 
            selected( $country_code, '+253' );
            ?>><?php 
            esc_html_e( 'Djibouti (+253)', 'simple-contact-button' );
            ?></option>
        <option value="+670" <?php 
            selected( $country_code, '+670' );
            ?>><?php 
            esc_html_e( 'East Timor (+670)', 'simple-contact-button' );
            ?></option>
        <option value="+593" <?php 
            selected( $country_code, '+593' );
            ?>><?php 
            esc_html_e( 'Ecuador (+593)', 'simple-contact-button' );
            ?></option>
        <option value="+503" <?php 
            selected( $country_code, '+503' );
            ?>><?php 
            esc_html_e( 'El Salvador (+503)', 'simple-contact-button' );
            ?></option>
        <option value="+240" <?php 
            selected( $country_code, '+240' );
            ?>><?php 
            esc_html_e( 'Equatorial Guinea (+240)', 'simple-contact-button' );
            ?></option>
        <option value="+291" <?php 
            selected( $country_code, '+291' );
            ?>><?php 
            esc_html_e( 'Eritrea (+291)', 'simple-contact-button' );
            ?></option>
        <option value="+372" <?php 
            selected( $country_code, '+372' );
            ?>><?php 
            esc_html_e( 'Estonia (+372)', 'simple-contact-button' );
            ?></option>
        <option value="+268" <?php 
            selected( $country_code, '+268' );
            ?>><?php 
            esc_html_e( 'Eswatini (+268)', 'simple-contact-button' );
            ?></option>
        <option value="+251" <?php 
            selected( $country_code, '+251' );
            ?>><?php 
            esc_html_e( 'Ethiopia (+251)', 'simple-contact-button' );
            ?></option>
        <option value="+679" <?php 
            selected( $country_code, '+679' );
            ?>><?php 
            esc_html_e( 'Fiji (+679)', 'simple-contact-button' );
            ?></option>
        <option value="+241" <?php 
            selected( $country_code, '+241' );
            ?>><?php 
            esc_html_e( 'Gabon (+241)', 'simple-contact-button' );
            ?></option>
        <option value="+220" <?php 
            selected( $country_code, '+220' );
            ?>><?php 
            esc_html_e( 'Gambia (+220)', 'simple-contact-button' );
            ?></option>
        <option value="+995" <?php 
            selected( $country_code, '+995' );
            ?>><?php 
            esc_html_e( 'Georgia (+995)', 'simple-contact-button' );
            ?></option>
        <option value="+233" <?php 
            selected( $country_code, '+233' );
            ?>><?php 
            esc_html_e( 'Ghana (+233)', 'simple-contact-button' );
            ?></option>
        <option value="+30" <?php 
            selected( $country_code, '+30' );
            ?>><?php 
            esc_html_e( 'Greece (+30)', 'simple-contact-button' );
            ?></option>
        <option value="+502" <?php 
            selected( $country_code, '+502' );
            ?>><?php 
            esc_html_e( 'Guatemala (+502)', 'simple-contact-button' );
            ?></option>
        <option value="+224" <?php 
            selected( $country_code, '+224' );
            ?>><?php 
            esc_html_e( 'Guinea (+224)', 'simple-contact-button' );
            ?></option>
        <option value="+245" <?php 
            selected( $country_code, '+245' );
            ?>><?php 
            esc_html_e( 'Guinea-Bissau (+245)', 'simple-contact-button' );
            ?></option>
        <option value="+592" <?php 
            selected( $country_code, '+592' );
            ?>><?php 
            esc_html_e( 'Guyana (+592)', 'simple-contact-button' );
            ?></option>
        <option value="+509" <?php 
            selected( $country_code, '+509' );
            ?>><?php 
            esc_html_e( 'Haiti (+509)', 'simple-contact-button' );
            ?></option>
        <option value="+504" <?php 
            selected( $country_code, '+504' );
            ?>><?php 
            esc_html_e( 'Honduras (+504)', 'simple-contact-button' );
            ?></option>
        <option value="+36" <?php 
            selected( $country_code, '+36' );
            ?>><?php 
            esc_html_e( 'Hungary (+36)', 'simple-contact-button' );
            ?></option>
        <option value="+354" <?php 
            selected( $country_code, '+354' );
            ?>><?php 
            esc_html_e( 'Iceland (+354)', 'simple-contact-button' );
            ?></option>
        <option value="+98" <?php 
            selected( $country_code, '+98' );
            ?>><?php 
            esc_html_e( 'Iran (+98)', 'simple-contact-button' );
            ?></option>
        <option value="+964" <?php 
            selected( $country_code, '+964' );
            ?>><?php 
            esc_html_e( 'Iraq (+964)', 'simple-contact-button' );
            ?></option>
        <option value="+353" <?php 
            selected( $country_code, '+353' );
            ?>><?php 
            esc_html_e( 'Ireland (+353)', 'simple-contact-button' );
            ?></option>
        <option value="+972" <?php 
            selected( $country_code, '+972' );
            ?>><?php 
            esc_html_e( 'Israel (+972)', 'simple-contact-button' );
            ?></option>
        <option value="+225" <?php 
            selected( $country_code, '+225' );
            ?>><?php 
            esc_html_e( 'Ivory Coast (Côte d\'Ivoire) (+225)', 'simple-contact-button' );
            ?></option>
        <option value="+962" <?php 
            selected( $country_code, '+962' );
            ?>><?php 
            esc_html_e( 'Jordan (+962)', 'simple-contact-button' );
            ?></option>
        <option value="+7" <?php 
            selected( $country_code, '+7' );
            ?>><?php 
            esc_html_e( 'Kazakhstan (+7)', 'simple-contact-button' );
            ?></option>
        <option value="+254" <?php 
            selected( $country_code, '+254' );
            ?>><?php 
            esc_html_e( 'Kenya (+254)', 'simple-contact-button' );
            ?></option>
        <option value="+686" <?php 
            selected( $country_code, '+686' );
            ?>><?php 
            esc_html_e( 'Kiribati (+686)', 'simple-contact-button' );
            ?></option>
        <option value="+965" <?php 
            selected( $country_code, '+965' );
            ?>><?php 
            esc_html_e( 'Kuwait (+965)', 'simple-contact-button' );
            ?></option>
        <option value="+996" <?php 
            selected( $country_code, '+996' );
            ?>><?php 
            esc_html_e( 'Kyrgyzstan (+996)', 'simple-contact-button' );
            ?></option>
        <option value="+856" <?php 
            selected( $country_code, '+856' );
            ?>><?php 
            esc_html_e( 'Laos (+856)', 'simple-contact-button' );
            ?></option>
        <option value="+371" <?php 
            selected( $country_code, '+371' );
            ?>><?php 
            esc_html_e( 'Latvia (+371)', 'simple-contact-button' );
            ?></option>
        <option value="+961" <?php 
            selected( $country_code, '+961' );
            ?>><?php 
            esc_html_e( 'Lebanon (+961)', 'simple-contact-button' );
            ?></option>
        <option value="+266" <?php 
            selected( $country_code, '+266' );
            ?>><?php 
            esc_html_e( 'Lesotho (+266)', 'simple-contact-button' );
            ?></option>
        <option value="+231" <?php 
            selected( $country_code, '+231' );
            ?>><?php 
            esc_html_e( 'Liberia (+231)', 'simple-contact-button' );
            ?></option>
        <option value="+218" <?php 
            selected( $country_code, '+218' );
            ?>><?php 
            esc_html_e( 'Libya (+218)', 'simple-contact-button' );
            ?></option>
        <option value="+423" <?php 
            selected( $country_code, '+423' );
            ?>><?php 
            esc_html_e( 'Liechtenstein (+423)', 'simple-contact-button' );
            ?></option>
        <option value="+370" <?php 
            selected( $country_code, '+370' );
            ?>><?php 
            esc_html_e( 'Lithuania (+370)', 'simple-contact-button' );
            ?></option>
        <option value="+352" <?php 
            selected( $country_code, '+352' );
            ?>><?php 
            esc_html_e( 'Luxembourg (+352)', 'simple-contact-button' );
            ?></option>
        <option value="+261" <?php 
            selected( $country_code, '+261' );
            ?>><?php 
            esc_html_e( 'Madagascar (+261)', 'simple-contact-button' );
            ?></option>
        <option value="+265" <?php 
            selected( $country_code, '+265' );
            ?>><?php 
            esc_html_e( 'Malawi (+265)', 'simple-contact-button' );
            ?></option>
        <option value="+960" <?php 
            selected( $country_code, '+960' );
            ?>><?php 
            esc_html_e( 'Maldives (+960)', 'simple-contact-button' );
            ?></option>
        <option value="+223" <?php 
            selected( $country_code, '+223' );
            ?>><?php 
            esc_html_e( 'Mali (+223)', 'simple-contact-button' );
            ?></option>
        <option value="+356" <?php 
            selected( $country_code, '+356' );
            ?>><?php 
            esc_html_e( 'Malta (+356)', 'simple-contact-button' );
            ?></option>
        <option value="+692" <?php 
            selected( $country_code, '+692' );
            ?>><?php 
            esc_html_e( 'Marshall Islands (+692)', 'simple-contact-button' );
            ?></option>
        <option value="+222" <?php 
            selected( $country_code, '+222' );
            ?>><?php 
            esc_html_e( 'Mauritania (+222)', 'simple-contact-button' );
            ?></option>
        <option value="+230" <?php 
            selected( $country_code, '+230' );
            ?>><?php 
            esc_html_e( 'Mauritius (+230)', 'simple-contact-button' );
            ?></option>
        <option value="+691" <?php 
            selected( $country_code, '+691' );
            ?>><?php 
            esc_html_e( 'Micronesia (+691)', 'simple-contact-button' );
            ?></option>
        <option value="+373" <?php 
            selected( $country_code, '+373' );
            ?>><?php 
            esc_html_e( 'Moldova (+373)', 'simple-contact-button' );
            ?></option>
        <option value="+377" <?php 
            selected( $country_code, '+377' );
            ?>><?php 
            esc_html_e( 'Monaco (+377)', 'simple-contact-button' );
            ?></option>
        <option value="+976" <?php 
            selected( $country_code, '+976' );
            ?>><?php 
            esc_html_e( 'Mongolia (+976)', 'simple-contact-button' );
            ?></option>
        <option value="+382" <?php 
            selected( $country_code, '+382' );
            ?>><?php 
            esc_html_e( 'Montenegro (+382)', 'simple-contact-button' );
            ?></option>
        <option value="+258" <?php 
            selected( $country_code, '+258' );
            ?>><?php 
            esc_html_e( 'Mozambique (+258)', 'simple-contact-button' );
            ?></option>
        <option value="+95" <?php 
            selected( $country_code, '+95' );
            ?>><?php 
            esc_html_e( 'Myanmar (+95)', 'simple-contact-button' );
            ?></option>
        <option value="+264" <?php 
            selected( $country_code, '+264' );
            ?>><?php 
            esc_html_e( 'Namibia (+264)', 'simple-contact-button' );
            ?></option>
        <option value="+674" <?php 
            selected( $country_code, '+674' );
            ?>><?php 
            esc_html_e( 'Nauru (+674)', 'simple-contact-button' );
            ?></option>
        <option value="+977" <?php 
            selected( $country_code, '+977' );
            ?>><?php 
            esc_html_e( 'Nepal (+977)', 'simple-contact-button' );
            ?></option>
        <option value="+505" <?php 
            selected( $country_code, '+505' );
            ?>><?php 
            esc_html_e( 'Nicaragua (+505)', 'simple-contact-button' );
            ?></option>
        <option value="+227" <?php 
            selected( $country_code, '+227' );
            ?>><?php 
            esc_html_e( 'Niger (+227)', 'simple-contact-button' );
            ?></option>
        <option value="+850" <?php 
            selected( $country_code, '+850' );
            ?>><?php 
            esc_html_e( 'North Korea (+850)', 'simple-contact-button' );
            ?></option>
        <option value="+389" <?php 
            selected( $country_code, '+389' );
            ?>><?php 
            esc_html_e( 'North Macedonia (+389)', 'simple-contact-button' );
            ?></option>
        <option value="+47" <?php 
            selected( $country_code, '+47' );
            ?>><?php 
            esc_html_e( 'Norway (+47)', 'simple-contact-button' );
            ?></option>
        <option value="+968" <?php 
            selected( $country_code, '+968' );
            ?>><?php 
            esc_html_e( 'Oman (+968)', 'simple-contact-button' );
            ?></option>
        <option value="+92" <?php 
            selected( $country_code, '+92' );
            ?>><?php 
            esc_html_e( 'Pakistan (+92)', 'simple-contact-button' );
            ?></option>
        <option value="+680" <?php 
            selected( $country_code, '+680' );
            ?>><?php 
            esc_html_e( 'Palau (+680)', 'simple-contact-button' );
            ?></option>
        <option value="+507" <?php 
            selected( $country_code, '+507' );
            ?>><?php 
            esc_html_e( 'Panama (+507)', 'simple-contact-button' );
            ?></option>
        <option value="+675" <?php 
            selected( $country_code, '+675' );
            ?>><?php 
            esc_html_e( 'Papua New Guinea (+675)', 'simple-contact-button' );
            ?></option>
        <option value="+595" <?php 
            selected( $country_code, '+595' );
            ?>><?php 
            esc_html_e( 'Paraguay (+595)', 'simple-contact-button' );
            ?></option>
        <option value="+51" <?php 
            selected( $country_code, '+51' );
            ?>><?php 
            esc_html_e( 'Peru (+51)', 'simple-contact-button' );
            ?></option>
        <option value="+63" <?php 
            selected( $country_code, '+63' );
            ?>><?php 
            esc_html_e( 'Philippines (+63)', 'simple-contact-button' );
            ?></option>
        <option value="+48" <?php 
            selected( $country_code, '+48' );
            ?>><?php 
            esc_html_e( 'Poland (+48)', 'simple-contact-button' );
            ?></option>
        <option value="+351" <?php 
            selected( $country_code, '+351' );
            ?>><?php 
            esc_html_e( 'Portugal (+351)', 'simple-contact-button' );
            ?></option>
        <option value="+974" <?php 
            selected( $country_code, '+974' );
            ?>><?php 
            esc_html_e( 'Qatar (+974)', 'simple-contact-button' );
            ?></option>
        <option value="+40" <?php 
            selected( $country_code, '+40' );
            ?>><?php 
            esc_html_e( 'Romania (+40)', 'simple-contact-button' );
            ?></option>
        <option value="+250" <?php 
            selected( $country_code, '+250' );
            ?>><?php 
            esc_html_e( 'Rwanda (+250)', 'simple-contact-button' );
            ?></option>
        <option value="+685" <?php 
            selected( $country_code, '+685' );
            ?>><?php 
            esc_html_e( 'Samoa (+685)', 'simple-contact-button' );
            ?></option>
        <option value="+378" <?php 
            selected( $country_code, '+378' );
            ?>><?php 
            esc_html_e( 'San Marino (+378)', 'simple-contact-button' );
            ?></option>
        <option value="+239" <?php 
            selected( $country_code, '+239' );
            ?>><?php 
            esc_html_e( 'Sao Tome and Principe (+239)', 'simple-contact-button' );
            ?></option>
        <option value="+966" <?php 
            selected( $country_code, '+966' );
            ?>><?php 
            esc_html_e( 'Saudi Arabia (+966)', 'simple-contact-button' );
            ?></option>
        <option value="+221" <?php 
            selected( $country_code, '+221' );
            ?>><?php 
            esc_html_e( 'Senegal (+221)', 'simple-contact-button' );
            ?></option>
        <option value="+381" <?php 
            selected( $country_code, '+381' );
            ?>><?php 
            esc_html_e( 'Serbia (+381)', 'simple-contact-button' );
            ?></option>
        <option value="+248" <?php 
            selected( $country_code, '+248' );
            ?>><?php 
            esc_html_e( 'Seychelles (+248)', 'simple-contact-button' );
            ?></option>
        <option value="+232" <?php 
            selected( $country_code, '+232' );
            ?>><?php 
            esc_html_e( 'Sierra Leone (+232)', 'simple-contact-button' );
            ?></option>
        <option value="+421" <?php 
            selected( $country_code, '+421' );
            ?>><?php 
            esc_html_e( 'Slovakia (+421)', 'simple-contact-button' );
            ?></option>
        <option value="+386" <?php 
            selected( $country_code, '+386' );
            ?>><?php 
            esc_html_e( 'Slovenia (+386)', 'simple-contact-button' );
            ?></option>
        <option value="+677" <?php 
            selected( $country_code, '+677' );
            ?>><?php 
            esc_html_e( 'Solomon Islands (+677)', 'simple-contact-button' );
            ?></option>
        <option value="+252" <?php 
            selected( $country_code, '+252' );
            ?>><?php 
            esc_html_e( 'Somalia (+252)', 'simple-contact-button' );
            ?></option>
        <option value="+211" <?php 
            selected( $country_code, '+211' );
            ?>><?php 
            esc_html_e( 'South Sudan (+211)', 'simple-contact-button' );
            ?></option>
        <option value="+94" <?php 
            selected( $country_code, '+94' );
            ?>><?php 
            esc_html_e( 'Sri Lanka (+94)', 'simple-contact-button' );
            ?></option>
        <option value="+249" <?php 
            selected( $country_code, '+249' );
            ?>><?php 
            esc_html_e( 'Sudan (+249)', 'simple-contact-button' );
            ?></option>
        <option value="+597" <?php 
            selected( $country_code, '+597' );
            ?>><?php 
            esc_html_e( 'Suriname (+597)', 'simple-contact-button' );
            ?></option>
        <option value="+41" <?php 
            selected( $country_code, '+41' );
            ?>><?php 
            esc_html_e( 'Switzerland (+41)', 'simple-contact-button' );
            ?></option>
        <option value="+963" <?php 
            selected( $country_code, '+963' );
            ?>><?php 
            esc_html_e( 'Syria (+963)', 'simple-contact-button' );
            ?></option>
        <option value="+886" <?php 
            selected( $country_code, '+886' );
            ?>><?php 
            esc_html_e( 'Taiwan (+886)', 'simple-contact-button' );
            ?></option>
        <option value="+992" <?php 
            selected( $country_code, '+992' );
            ?>><?php 
            esc_html_e( 'Tajikistan (+992)', 'simple-contact-button' );
            ?></option>
        <option value="+255" <?php 
            selected( $country_code, '+255' );
            ?>><?php 
            esc_html_e( 'Tanzania (+255)', 'simple-contact-button' );
            ?></option>
        <option value="+228" <?php 
            selected( $country_code, '+228' );
            ?>><?php 
            esc_html_e( 'Togo (+228)', 'simple-contact-button' );
            ?></option>
        <option value="+676" <?php 
            selected( $country_code, '+676' );
            ?>><?php 
            esc_html_e( 'Tonga (+676)', 'simple-contact-button' );
            ?></option>
        <option value="+216" <?php 
            selected( $country_code, '+216' );
            ?>><?php 
            esc_html_e( 'Tunisia (+216)', 'simple-contact-button' );
            ?></option>
        <option value="+993" <?php 
            selected( $country_code, '+993' );
            ?>><?php 
            esc_html_e( 'Turkmenistan (+993)', 'simple-contact-button' );
            ?></option>
        <option value="+688" <?php 
            selected( $country_code, '+688' );
            ?>><?php 
            esc_html_e( 'Tuvalu (+688)', 'simple-contact-button' );
            ?></option>
        <option value="+256" <?php 
            selected( $country_code, '+256' );
            ?>><?php 
            esc_html_e( 'Uganda (+256)', 'simple-contact-button' );
            ?></option>
        <option value="+380" <?php 
            selected( $country_code, '+380' );
            ?>><?php 
            esc_html_e( 'Ukraine (+380)', 'simple-contact-button' );
            ?></option>
        <option value="+598" <?php 
            selected( $country_code, '+598' );
            ?>><?php 
            esc_html_e( 'Uruguay (+598)', 'simple-contact-button' );
            ?></option>
        <option value="+998" <?php 
            selected( $country_code, '+998' );
            ?>><?php 
            esc_html_e( 'Uzbekistan (+998)', 'simple-contact-button' );
            ?></option>
        <option value="+678" <?php 
            selected( $country_code, '+678' );
            ?>><?php 
            esc_html_e( 'Vanuatu (+678)', 'simple-contact-button' );
            ?></option>
        <option value="+379" <?php 
            selected( $country_code, '+379' );
            ?>><?php 
            esc_html_e( 'Vatican City (+379)', 'simple-contact-button' );
            ?></option>
        <option value="+58" <?php 
            selected( $country_code, '+58' );
            ?>><?php 
            esc_html_e( 'Venezuela (+58)', 'simple-contact-button' );
            ?></option>
        <option value="+84" <?php 
            selected( $country_code, '+84' );
            ?>><?php 
            esc_html_e( 'Vietnam (+84)', 'simple-contact-button' );
            ?></option>
        <option value="+967" <?php 
            selected( $country_code, '+967' );
            ?>><?php 
            esc_html_e( 'Yemen (+967)', 'simple-contact-button' );
            ?></option>
        <option value="+260" <?php 
            selected( $country_code, '+260' );
            ?>><?php 
            esc_html_e( 'Zambia (+260)', 'simple-contact-button' );
            ?></option>
        <option value="+263" <?php 
            selected( $country_code, '+263' );
            ?>><?php 
            esc_html_e( 'Zimbabwe (+263)', 'simple-contact-button' );
            ?></option>
        </select>
        <input type="text" name="simple_contact_button_phone_number" value="<?php 
            echo esc_attr( $number );
            ?>" />
        <?php 
        }

        public function initial_message_callback() {
            $initial_message = get_option( 'simple_contact_button_initial_message' );
            echo '<input type="text" name="simple_contact_button_initial_message" value="' . esc_attr( $initial_message ) . '" />';
        }

        public function icon_position_callback() {
            $icon_position = get_option( 'simple_contact_button_icon_position', 'right' );
            ?>
        <select name="simple_contact_button_icon_position">
            <option value="right" <?php 
            selected( $icon_position, 'right' );
            ?>><?php 
            esc_html_e( 'Right Bottom', 'simple-contact-button' );
            ?></option>
            <option value="left" <?php 
            selected( $icon_position, 'left' );
            ?>><?php 
            esc_html_e( 'Left Bottom', 'simple-contact-button' );
            ?></option>
        </select>
        <?php 
        }

        public function device_visibility_callback() {
            $device_visibility = get_option( 'simple_contact_button_device_visibility', 'both' );
            ?>
        <select name="simple_contact_button_device_visibility">
            <option value="both" <?php 
            selected( $device_visibility, 'both' );
            ?>><?php 
            esc_html_e( 'Show on Both', 'simple-contact-button' );
            ?></option>
            <option value="desktop" <?php 
            selected( $device_visibility, 'desktop' );
            ?>><?php 
            esc_html_e( 'Show on Desktop Only', 'simple-contact-button' );
            ?></option>
            <option value="mobile" <?php 
            selected( $device_visibility, 'mobile' );
            ?>><?php 
            esc_html_e( 'Show on Mobile Only', 'simple-contact-button' );
            ?></option>
        </select>
        <?php 
        }

        public function icon_selection_callback() {
            $icon_selection = get_option( 'simple_contact_button_icon_selection', 'icon1.png' );
            $icons = array('icon1.png', 'icon2.png', 'icon3.png');
            echo '<div class="simple-contact-button-icon-selection">';
            foreach ( $icons as $icon ) {
                $checked = ( $icon_selection === $icon ? 'checked' : '' );
                echo '<label style="display: inline-block; margin-right: 10px;">';
                echo '<img src="' . esc_url( plugins_url( 'assets/' . $icon, __FILE__ ) ) . '" alt="' . esc_attr( $icon ) . '" style="width: 75px; height: 75px; vertical-align: middle;">';
                echo '<input type="radio" name="simple_contact_button_icon_selection" value="' . esc_attr( $icon ) . '" ' . esc_attr( $checked ) . '> ';
                echo '</label>';
            }
            echo '</div>';
        }

        public function simple_contact_button_get_specific_posts() {
            check_ajax_referer( 'simple_contact_button_get_posts_nonce' );
            $post_type = ( isset( $_POST['post_type'] ) ? sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) : '' );
            $valid_post_types = ['post', 'page'];
            if ( !in_array( $post_type, $valid_post_types, true ) ) {
                wp_send_json_error( 'Invalid post type' );
            }
            $args = [
                'post_type'      => $post_type,
                'posts_per_page' => -1,
                'post_status'    => 'publish',
            ];
            $posts = get_posts( $args );
            $options = '';
            if ( !empty( $posts ) ) {
                foreach ( $posts as $post ) {
                    $options .= '<option value="' . esc_attr( $post->ID ) . '">' . esc_html( $post->post_title ) . '</option>';
                }
            } else {
                $options = '<option value="">' . esc_html__( 'No posts available', 'simple-contact-button' ) . '</option>';
            }
            wp_send_json_success( $options );
        }

        public function sanitize_phone_number( $input ) {
            if ( !isset( $_POST['simple_contact_button_nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['simple_contact_button_nonce'] ) ), 'simple_contact_button_save' ) ) {
                return '';
            }
            $country_code = ( isset( $_POST['simple_contact_button_country_code'] ) ? sanitize_text_field( wp_unslash( $_POST['simple_contact_button_country_code'] ) ) : '' );
            $phone_number = preg_replace( '/[^0-9]/', '', $input );
            if ( strpos( $phone_number, ltrim( $country_code, '+' ) ) === 0 ) {
                $phone_number = substr( $phone_number, strlen( ltrim( $country_code, '+' ) ) );
            }
            $full_number = $country_code . ' ' . $phone_number;
            return $full_number;
        }

        public function sanitize_message( $input ) {
            return sanitize_text_field( $input );
        }

        public function sanitize_icon_position( $input ) {
            $valid_positions = ['left', 'right'];
            return ( in_array( $input, $valid_positions, true ) ? $input : 'right' );
        }

        public function sanitize_device_visibility( $input ) {
            $valid_options = ['both', 'desktop', 'mobile'];
            return ( in_array( $input, $valid_options, true ) ? $input : 'both' );
        }

        public function sanitize_icon_selection( $input ) {
            $valid_icons = ['icon1.png', 'icon2.png', 'icon3.png'];
            return ( in_array( $input, $valid_icons, true ) ? $input : 'icon1.png' );
        }

        public function sanitize_post_type( $input ) {
            $valid_post_types = ['post', 'page'];
            return ( in_array( $input, $valid_post_types, true ) ? $input : '' );
        }

        public function sanitize_activation_setting( $input ) {
            $valid_options = ['sitewide', 'specific', 'homepage'];
            return ( in_array( $input, $valid_options, true ) ? $input : 'sitewide' );
        }

        public function add_contact_icon() {
            $phone_number = get_option( 'simple_contact_button_phone_number' );
            $initial_message = get_option( 'simple_contact_button_initial_message' );
            $icon_position = get_option( 'simple_contact_button_icon_position', 'right' );
            $device_visibility = get_option( 'simple_contact_button_device_visibility', 'both' );
            $icon_selection = get_option( 'simple_contact_button_icon_selection', 'icon1.png' );
            $activation_setting = get_option( 'simple_contact_button_activation', 'sitewide' );
            $specific_page_id = get_option( 'simple_contact_button_specific_page', '' );
            if ( !empty( $phone_number ) ) {
                $message = ( $initial_message ? urlencode( $initial_message ) : '' );
                $position_class = ( $icon_position === 'left' ? 'simple-contact-button-left' : 'simple-contact-button-right' );
                $visibility_class = '';
                if ( $device_visibility === 'desktop' ) {
                    $visibility_class = 'simple-contact-button-desktop-only';
                } elseif ( $device_visibility === 'mobile' ) {
                    $visibility_class = 'simple-contact-button-mobile-only';
                }
                $display_button = false;
                if ( $activation_setting === 'sitewide' ) {
                    $display_button = true;
                } elseif ( $activation_setting === 'homepage' && (is_front_page() || is_home()) ) {
                    $display_button = true;
                } elseif ( $activation_setting === 'specific' && get_the_ID() == $specific_page_id ) {
                    $display_button = true;
                }
                if ( $display_button ) {
                    $clean_phone_number = str_replace( ' ', '', $phone_number );
                    echo '<div id="simple-contact-button-icon" class="' . esc_attr( $position_class . ' ' . $visibility_class ) . '">
                    <a href="https://wa.me/' . esc_attr( $clean_phone_number ) . '?text=' . esc_attr( $message ) . '" onclick="trackButtonClick(event, this, \'WhatsApp\')">
                        <img src="' . esc_url( plugins_url( 'assets/' . $icon_selection, __FILE__ ) ) . '" alt="WhatsApp Contact">
                    </a>
                  </div>';
                    echo '<style>
                #simple-contact-button-icon {
                    position: fixed;
                    bottom: 10px;
                    z-index: 1000;
                }
                .simple-contact-button-left {
                    left: 10px;
                }
                .simple-contact-button-right {
                    right: 10px;
                }
                #simple-contact-button-icon img {
                    width: 100px;
                    height: 100px;
                    max-width: 100%;
                    max-height: 100%;
                    object-fit: contain;
                }
                @media screen and (max-width: 768px) {
                    .simple-contact-button-desktop-only { 
                        display: none !important; 
                    }
                    .simple-contact-button-mobile-only { 
                        display: block !important; 
                    }
                }
                @media screen and (min-width: 769px) {
                    .simple-contact-button-desktop-only { 
                        display: block !important; 
                    }
                    .simple-contact-button-mobile-only { 
                        display: none !important; 
                    }
                }
            </style>';
                }
            }
        }

        public function enqueue_styles() {
            // Remove enqueue style warning by removing the stylesheet enqueue
            // wp_enqueue_style('simple-contact-button-styles', plugins_url('style.css', __FILE__), array(), '1.3.0');
        }

        public function simple_contact_button_enqueue_scripts() {
        }

        public function simple_contact_button_enqueue_admin_scripts( $hook ) {
            if ( $hook != 'toplevel_page_simple-contact-button' ) {
                return;
            }
            wp_enqueue_script(
                'simple-contact-button-js',
                plugins_url( 'js/simple-contact-button-script.js', __FILE__ ),
                ['jquery'],
                null,
                true
            );
            $saved_page_id = get_option( 'simple_contact_button_specific_page', '' );
            $saved_post_type = get_option( 'simple_contact_button_post_type', '' );
            $activation_setting = get_option( 'simple_contact_button_activation', 'sitewide' );
            wp_localize_script( 'simple-contact-button-js', 'simpleContactButton', [
                'nonce'             => wp_create_nonce( 'simple_contact_button_get_posts_nonce' ),
                'ajaxurl'           => admin_url( 'admin-ajax.php' ),
                'savedPageId'       => $saved_page_id,
                'savedPostType'     => $saved_post_type,
                'activationSetting' => $activation_setting,
                'errorLoadingPosts' => esc_html__( 'Error loading posts', 'simple-contact-button' ),
            ] );
        }

    }

    Simple_Contact_Button::get_instance();
}