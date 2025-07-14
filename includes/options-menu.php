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
    echo '<input class="default-input" type="text" name="' . $field_name . '" value="' . $value . '" />';
}

    function act_time_field_callback( $args ) {
        $options = get_option( 'act_settings', [] );
        $day = $args['day'];
        $field_name = 'act_settings[' . strtolower( $day ) . ']';
        $value = isset( $options[ strtolower( $day ) ] ) ? esc_attr( $options[ strtolower( $day ) ] ) : '';
        echo '<input class="default-input" type="time" name="' . $field_name . '" value="' . $value . '" />';
    }

    function act_default_text_callback($args) {
        $options = get_option('act_settings', []);
        $text_type = $args['text_type']; // Either 'before' or 'after'
        $field_name = 'act_settings[default_' . $text_type . '_text]';
        $value = isset($options['default_' . $text_type . '_text']) ? esc_attr($options['default_' . $text_type . '_text']) : '';
    
        echo '<input class="default-input" type="text" name="' . $field_name . '" value="' . $value . '" />';
    }

function act_sanitize_settings( $input ) {
	$sanitized = [];

	foreach ( $input as $key => $value ) {
		if ( $key === 'custom_dates' && is_array( $value ) ) {
			foreach ( $value as $i => $entry ) {
				$sanitized['custom_dates'][ $i ] = [
					'start_date'    => sanitize_text_field( $entry['start_date'] ?? '' ),
					'end_date'      => sanitize_text_field( $entry['end_date']   ?? '' ),
					'time'          => sanitize_text_field( $entry['time']       ?? '' ),
					'b_text'        => sanitize_text_field( $entry['b_text']     ?? '' ),
					'a_text'        => sanitize_text_field( $entry['a_text']     ?? '' ),
					'hide_time'     => isset( $entry['hide_time'] ) ? 'on' : 'off',
				];
			}
		} else {
			$sanitized[ $key ] = sanitize_text_field( $value );
		}
	}

	return $sanitized;
}
function act_custom_dates_callback() {
	$options       = get_option( 'act_settings', [] );
	$custom_dates  = $options['custom_dates'] ?? [];

	echo '<div id="custom-dates-container">';

	foreach ( $custom_dates as $i => $entry ) {
		$start = esc_attr( $entry['start_date']  ?? '' );
		$end   = esc_attr( $entry['end_date']    ?? '' );
		$time  = esc_attr( $entry['time']        ?? '' );
		$b_txt = esc_attr( $entry['b_text']      ?? '' );
		$a_txt = esc_attr( $entry['a_text']      ?? '' );
		$hide  = checked( $entry['hide_time'] === 'on', true, false );

		echo "<div class='custom-date-entry'>";
		echo "<label>Start date: <input type='date'    name='act_settings[custom_dates][{$i}][start_date]' value='{$start}' /></label>";
		echo "<label>End date:   <input type='date'    name='act_settings[custom_dates][{$i}][end_date]'   value='{$end}'   /></label>";
		echo "<label>Time:       <input type='time'    name='act_settings[custom_dates][{$i}][time]'       value='{$time}'  /></label>";
		echo "<label>Before txt: <input type='text'    name='act_settings[custom_dates][{$i}][b_text]'     value='{$b_txt}' /></label>";
		echo "<label>After txt:  <input type='text'    name='act_settings[custom_dates][{$i}][a_text]'     value='{$a_txt}' /></label>";
		echo "<label class='checkbox'><input type='checkbox' name='act_settings[custom_dates][{$i}][hide_time]' {$hide} /> Hide countdown</label>";
		echo "<button type='button' class='remove-date'>Remove</button>";
		echo "</div>";
	}

	echo '</div>';
	echo '<button type="button" id="add-custom-date">Add Custom Date</button>';

    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('custom-dates-container');
            const addButton = document.getElementById('add-custom-date');

            addButton.addEventListener('click', function() {
                const index = container.children.length;
                const div = document.createElement('div');
                div.classList.add('custom-date-entry');
                div.innerHTML = `
            <label>Start date: <input type="date" name="act_settings[custom_dates][${index}][start_date]" /></label>
            <label>End date:   <input type="date" name="act_settings[custom_dates][${index}][end_date]"   /></label>
            <label>Time:       <input type="time" name="act_settings[custom_dates][${index}][time]"       /></label>
            <label>Before txt: <input type="text" name="act_settings[custom_dates][${index}][b_text]"     placeholder="Before time" /></label>
            <label>After txt:  <input type="text" name="act_settings[custom_dates][${index}][a_text]"     placeholder="After time"  /></label>
            <label class="checkbox"><input type="checkbox" name="act_settings[custom_dates][${index}][hide_time]" /> Hide countdown</label>
            <button type="button" class="remove-date">Remove</button>
        `;
                container.appendChild(div);
            });

            document.addEventListener('click', function(event) {
                if (event.target.classList.contains('remove-date')) {
                    event.target.parentElement.remove();
                    // (Optional) You might want to re-index the remaining entries here
                }
            });
        });
    </script>
    <?php
}

?>