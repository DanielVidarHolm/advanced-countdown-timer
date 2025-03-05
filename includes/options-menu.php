<?php
/**
 * Options Page for Teledele Hurrytimer Plugin
 */

function teledele_hurrytimer_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php 
                settings_fields( 'teledele-hurrytimer_options' );
                do_settings_sections( 'teledele-hurrytimer' );
                submit_button( __( 'Save Settings', 'teledele-hurrytimer' ) );
                
            ?>
        </form>
    </div>
    <?php
}

function teledele_hurrytimer_options_page() {
    add_submenu_page(
        'tools.php',
        'Teledele Hurrytimer',
        'Teledele Hurrytimer',
        'manage_options',
        'teledele-hurrytimer',
        'teledele_hurrytimer_page_html'
    );
}
add_action( 'admin_menu', 'teledele_hurrytimer_options_page' );

function teledele_hurrytimer_register_settings() {
    register_setting(
        'teledele-hurrytimer_options', // Option group
        'teledele_hurrytimer_settings',
        'teledele_hurrytimer_sanitize_settings' // Option name in the database
        // Optionally, you can add a sanitize callback as a third argument
        // 'sanitize_callback' => 'your_sanitize_function'
    );

    add_settings_section(
        'teledele_hurrytimer_default_section', 
        __( 'Hurry Timer Default Weekday Settings', 'teledele-hurrytimer' ),
        '',
        'teledele-hurrytimer'
    );

    add_settings_section(
        'teledele_hurrytimer_special_section',
        __('Hurry timer special settings', 'teledele-hurrytimer'),
        '',
        'teledele-hurrytimer'
    );

    $weekdays = [ 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ];

    foreach ( $weekdays as $day ) {
        add_settings_field(
            'teledele_hurrytimer_' . strtolower( $day ),
            sprintf( __( '%s Time', 'teledele-hurrytimer' ), $day ),
            'teledele_hurrytimer_time_field_callback',
            'teledele-hurrytimer',
            'teledele_hurrytimer_default_section',
            [ 'day' => $day ]
        );

       
    }

    add_settings_field(
        'teledele_hurrytimer_default_before_text', 
        __( 'Default Before Time Text', 'teledele-hurrytimer' ),
        'teledele_hurrytimer_default_text_callback',
        'teledele-hurrytimer',
        'teledele_hurrytimer_default_section',
        ['text_type' => 'before']
    );

    add_settings_field(
        'teledele_hurrytimer_default_after_text', 
        __( 'Default After Time Text', 'teledele-hurrytimer' ),
        'teledele_hurrytimer_default_text_callback',
        'teledele-hurrytimer',
        'teledele_hurrytimer_default_section',
        ['text_type' => 'after']
    );

    add_settings_field(
        'teledele_hurrytimer_custom_dates',
        'Custom Countdown Dates',
        'teledele_hurrytimer_custom_dates_callback',
        'teledele-hurrytimer',
        'teledele_hurrytimer_special_section'
    );

    add_settings_field(
        'teledele_hurrytimer_after_countdown_text', 
        __( 'After Countdown Text', 'teledele-hurrytimer' ),
        'teledele_hurrytimer_after_countdown_text_callback',
        'teledele-hurrytimer',
        'teledele_hurrytimer_default_section'
    );
}
add_action( 'admin_init', 'teledele_hurrytimer_register_settings' );

function teledele_hurrytimer_after_countdown_text_callback() {
    $options = get_option('teledele_hurrytimer_settings', []);
    $field_name = 'teledele_hurrytimer_settings[after_countdown_text]';
    $value = isset($options['after_countdown_text']) ? esc_attr($options['after_countdown_text']) : '';
    echo '<input type="text" name="' . $field_name . '" value="' . $value . '" />';
}

    function teledele_hurrytimer_time_field_callback( $args ) {
        $options = get_option( 'teledele_hurrytimer_settings', [] );
        $day = $args['day'];
        $field_name = 'teledele_hurrytimer_settings[' . strtolower( $day ) . ']';
        $value = isset( $options[ strtolower( $day ) ] ) ? esc_attr( $options[ strtolower( $day ) ] ) : '';
        echo '<input type="time" name="' . $field_name . '" value="' . $value . '" />';
    }

    function teledele_hurrytimer_default_text_callback($args) {
        $options = get_option('teledele_hurrytimer_settings', []);
        $text_type = $args['text_type']; // Either 'before' or 'after'
        $field_name = 'teledele_hurrytimer_settings[default_' . $text_type . '_text]';
        $value = isset($options['default_' . $text_type . '_text']) ? esc_attr($options['default_' . $text_type . '_text']) : '';
    
        echo '<input type="text" name="' . $field_name . '" value="' . $value . '" />';
    }

    function teledele_hurrytimer_sanitize_settings($input) {
        $sanitized = [];
        foreach ($input as $key => $value) {
            if ($key === 'custom_dates') {
                foreach ($value as $index => $date) {
                    $sanitized['custom_dates'][$index]['date'] = sanitize_text_field($date['date']);
                    $sanitized['custom_dates'][$index]['time'] = sanitize_text_field($date['time']);
                    $sanitized['custom_dates'][$index]['b_text'] = sanitize_text_field($date['b_text']);
                    $sanitized['custom_dates'][$index]['a_text'] = sanitize_text_field($date['a_text']);
                    $sanitized['custom_dates'][$index]['hide_time'] = isset($date['hide_time']) ? 'on' : 'off';
                }
            } else {
                $sanitized[$key] = sanitize_text_field($value);
            }
        }
        return $sanitized;
    }

function teledele_hurrytimer_custom_dates_callback() {
    $options = get_option('teledele_hurrytimer_settings', []);
    $custom_dates = isset($options['custom_dates']) ? $options['custom_dates'] : [];

    echo '<div id="custom-dates-container">';
    
    if (!empty($custom_dates)) {
        foreach ($custom_dates as $index => $entry) {
            echo '<div class="custom-date-entry">';
            echo '<input type="date" name="teledele_hurrytimer_settings[custom_dates][' . $index . '][date]" value="' . esc_attr($entry['date']) . '" />';
            echo '<input type="time" name="teledele_hurrytimer_settings[custom_dates][' . $index . '][time]" value="' . esc_attr($entry['time']) . '" />';
            echo '<input type="text" name="teledele_hurrytimer_settings[custom_dates][' . $index . '][b_text]" placeholder="Before time" value="' . esc_attr($entry['b_text']) . '" />';
            echo '<input type="text" name="teledele_hurrytimer_settings[custom_dates][' . $index . '][a_text]" placeholder="After time" value="' . esc_attr($entry['a_text']) . '" />';
            echo '<label for="teledele_hurrytimer_settings[custom_dates][' . $index . '][hide_time]">Hide countown</label>';
            echo '<input type="checkbox" name="teledele_hurrytimer_settings[custom_dates][' . $index . '][hide_time]" ' . checked(isset($entry['hide_time']) && $entry['hide_time'] === 'on', true, false) . ' />';
            echo '<button type="button" class="remove-date">Remove</button>';
            echo '</div>';
        }
    }

    echo '</div>';
    echo '<button type="button" id="add-custom-date">Add Custom Date</button>';

    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let container = document.getElementById('custom-dates-container');
        let addButton = document.getElementById('add-custom-date');

        addButton.addEventListener('click', function() {
            let index = container.children.length;
            let div = document.createElement('div');
            div.classList.add('custom-date-entry');
            div.innerHTML = `
                <input type="date" name="teledele_hurrytimer_settings[custom_dates][${index}][date]" />
                <input type="time" name="teledele_hurrytimer_settings[custom_dates][${index}][time]" />
                <input type="text" name="teledele_hurrytimer_settings[custom_dates][${index}][a_text]" placeholder="Before time" />
                <input type="text" name="teledele_hurrytimer_settings[custom_dates][${index}][b_text]" placeholder="After time" />
                <label for="teledele_hurrytimer_settings[custom_dates][${index}][hide_time]">
                Hide countown
                </label>
                <input type="checkbox" name="teledele_hurrytimer_settings[custom_dates][${index}][hide_time]" />
                <button type="button" class="remove-date">Remove</button>
            `;
            container.appendChild(div);
        });
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('remove-date')) {
                event.target.parentElement.remove();
            }
        });
    });
    </script>
    <?php
}

?>