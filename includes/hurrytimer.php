<?php 

function act_shortcode($atts = [], $content = null) {
    ob_start();

    $options = get_option('act_settings', []);
    $current_day = strtolower(date('l'));

    $default_before_text = isset($options['default_before_text']) ? $options['default_before_text'] : 'BESTIL INDEN';
    $default_after_text = isset($options['default_after_text']) ? $options['default_after_text'] : 'OG VI SENDER SAMME DAG';
    
    // Get the saved time for the current weekday
    $saved_time = isset($options[$current_day]) ? $options[$current_day] : null;
    
    // Check for custom dates
    $custom_dates = isset($options['custom_dates']) ? $options['custom_dates'] : [];

    // Find a custom date that matches today's date
    $custom_time = null;
    $before_text = $default_before_text; 
    $after_text = $default_after_text; 
    $hide_time = 'off';
    foreach ($custom_dates as $entry) {
        $custom_date = $entry['date'];
        if ($custom_date === date('Y-m-d')) {
            $custom_time = isset($entry['time']) ? $entry['time'] : null;
            $before_text = isset($entry['b_text']) ? $entry['b_text'] : $default_before_text;
            $after_text = isset($entry['a_text']) ? $entry['a_text'] : $default_after_text;
            $hide_time = isset($entry['hide_time']) ? $entry['hide_time'] : '';
            break;
        }
    }

    // Use custom time if found, otherwise fallback to the weekday time
    $time_to_use = $custom_time ? $custom_time : $saved_time;


    $after_countdown_text = isset($options['after_countdown_text']) ? $options['after_countdown_text'] : '';

    if (!$time_to_use) {
        return "<div id='act'></div>";
    }
    ?>
    <div id="act"></div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const savedTime = "<?php echo esc_html($time_to_use); ?>";
            const beforeText = "<?php echo esc_html($before_text); ?>";
            const afterText = "<?php echo esc_html($after_text); ?>";
            const hideTime = "<?php echo esc_html($hide_time); ?>";
            const afterCountdownText = "<?php echo esc_html($after_countdown_text); ?>";
            console.log(hideTime);
            const today = new Date();
            const targetDate = new Date(
                today.getFullYear(),
                today.getMonth(),
                today.getDate(),
                parseInt(savedTime.split(':')[0]),
                parseInt(savedTime.split(':')[1]),
                0
            ).getTime();

            function updateCountdown() {
                const now = new Date().getTime();
                const timeLeft = targetDate - now;

                if (timeLeft <= 0) {
                    if (afterCountdownText) {
                        document.getElementById('act').innerHTML = afterCountdownText;
                    } else {
                        document.getElementById('act').innerHTML = '';
                    }
                    return;
                }

                const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
                
                document.getElementById('act').innerHTML = beforeText + " " + hours + " TIMER " + minutes + " MIN. " + seconds + " SEK. " + afterText;

                setTimeout(updateCountdown, 1000);
            }

            function staticMessage(){
                document.getElementById('act').innerHTML = beforeText + " " + afterText;
            }
            if (hideTime === "off"){
                updateCountdown();
            }else{
                staticMessage();
            }
            
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('act', 'act_shortcode');

?>
