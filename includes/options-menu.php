<?php
/**
 * Options Page for ACT Plugin
 */

function act_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php 
                settings_fields( 'act_options' );
                do_settings_sections( 'act' );
                submit_button( __( 'Save Settings', 'act' ) );
                
            ?>
        </form>
    </div>
    <?php
}

function act_options_page() {
    add_submenu_page(
        'tools.php',
        'ACT',
        'ACT',
        'manage_options',
        'act',
        'act_page_html'
    );
}
add_action( 'admin_menu', 'act_options_page' );

function act_register_settings() {
    register_setting(
        'act_options', // Option group
        'act_settings',
        'act_sanitize_settings' // Option name in the database
        // Optionally, you can add a sanitize callback as a third argument
        // 'sanitize_callback' => 'your_sanitize_function'
    );

    add_settings_section(
        'act_default_section', 
        __( 'ACT Default Weekday Settings', 'act' ),
        '',
        'act'
    );

    add_settings_section(
        'act_special_section',
        __('ACT special settings', 'act'),
        '',
        'act'
    );

    $weekdays = [ 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ];

    foreach ( $weekdays as $day ) {
        add_settings_field(
            'act_' . strtolower( $day ),
            sprintf( __( '%s Time', 'act' ), $day ),
            'act_time_field_callback',
            'act',
            'act_default_section',
            [ 'day' => $day ]
        );

       
    }

    add_settings_field(
        'act_default_before_text', 
        __( 'Default Before Time Text', 'act' ),
        'act_default_text_callback',
        'act',
        'act_default_section',
        ['text_type' => 'before']
    );

    add_settings_field(
        'act_default_after_text', 
        __( 'Default After Time Text', 'act' ),
        'act_default_text_callback',
        'act',
        'act_default_section',
        ['text_type' => 'after']
    );

    add_settings_field(
        'act_custom_dates',
        'Custom Countdown Dates',
        'act_custom_dates_callback',
        'act',
        'act_special_section'
    );

    add_settings_field(
        'act_after_countdown_text', 
        __( 'After Countdown Text', 'act' ),
        'act_after_countdown_text_callback',
        'act',
        'act_default_section'
    );
}
add_action( 'admin_init', 'act_register_settings' );

function act_after_countdown_text_callback() {
    $options = get_option('act_settings', []);
    $field_name = 'act_settings[after_countdown_text]';
    $value = isset($options['after_countdown_text']) ? esc_attr($options['after_countdown_text']) : '';
    echo '<input type="text" name="' . $field_name . '" value="' . $value . '" />';
}

    function act_time_field_callback( $args ) {
        $options = get_option( 'act_settings', [] );
        $day = $args['day'];
        $field_name = 'act_settings[' . strtolower( $day ) . ']';
        $value = isset( $options[ strtolower( $day ) ] ) ? esc_attr( $options[ strtolower( $day ) ] ) : '';
        echo '<input type="time" name="' . $field_name . '" value="' . $value . '" />';
    }

    function act_default_text_callback($args) {
        $options = get_option('act_settings', []);
        $text_type = $args['text_type']; // Either 'before' or 'after'
        $field_name = 'act_settings[default_' . $text_type . '_text]';
        $value = isset($options['default_' . $text_type . '_text']) ? esc_attr($options['default_' . $text_type . '_text']) : '';
    
        echo '<input type="text" name="' . $field_name . '" value="' . $value . '" />';
    }

    function act_sanitize_settings($input) {
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

function act_custom_dates_callback() {
    $options = get_option('act_settings', []);
    $custom_dates = isset($options['custom_dates']) ? $options['custom_dates'] : [];

    echo '<div id="custom-dates-container">';
    
    if (!empty($custom_dates)) {
        foreach ($custom_dates as $index => $entry) {
            echo '<div class="custom-date-entry">';
            echo '<input type="date" name="act_settings[custom_dates][' . $index . '][date]" value="' . esc_attr($entry['date']) . '" />';
            echo '<input type="time" name="act_settings[custom_dates][' . $index . '][time]" value="' . esc_attr($entry['time']) . '" />';
            echo '<input type="text" name="act_settings[custom_dates][' . $index . '][b_text]" placeholder="Before time" value="' . esc_attr($entry['b_text']) . '" />';
            echo '<input type="text" name="act_settings[custom_dates][' . $index . '][a_text]" placeholder="After time" value="' . esc_attr($entry['a_text']) . '" />';
            echo '<label for="act_settings[custom_dates][' . $index . '][hide_time]">Hide countown</label>';
            echo '<input type="checkbox" name="act_settings[custom_dates][' . $index . '][hide_time]" ' . checked(isset($entry['hide_time']) && $entry['hide_time'] === 'on', true, false) . ' />';
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
                <input type="date" name="act_settings[custom_dates][${index}][date]" />
                <input type="time" name="act_settings[custom_dates][${index}][time]" />
                <input type="text" name="act_settings[custom_dates][${index}][a_text]" placeholder="Before time" />
                <input type="text" name="act_settings[custom_dates][${index}][b_text]" placeholder="After time" />
                <label for="act_settings[custom_dates][${index}][hide_time]">
                Hide countown
                </label>
                <input type="checkbox" name="act_settings[custom_dates][${index}][hide_time]" />
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