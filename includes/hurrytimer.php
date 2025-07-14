<?php
/**
 * Primary countdown shortcode: [act]
 */
function act_shortcode( array $atts = [], $content = null ): string {
	ob_start();

	// load settings
	$opts             = get_option( 'act_settings', [] );
	$today_str        = date( 'Y-m-d' );
	$weekday_key      = strtolower( date( 'l' ) );
	$saved_time       = $opts[ $weekday_key ] ?? null;
	$custom_dates     = $opts['custom_dates'] ?? [];
	$default_before   = $opts['default_before_text'] ?? 'BESTIL INDEN';
	$default_after    = $opts['default_after_text']  ?? 'OG VI SENDER SAMME DAG';
	$after_count_text = $opts['after_countdown_text'] ?? '';

	// figure out which slot applies
	$time_to_use = $saved_time;
	$before_text = $default_before;
	$after_text  = $default_after;
	$hide_time   = 'off';

	foreach ( $custom_dates as $entry ) {
		$start = $entry['start_date'] ?? '';
		$end   = $entry['end_date']   ?? '';
		if ( ! $start ) {
			continue;
		}
		$in_range = empty( $end )
			? ( $today_str === $start )
			: ( $today_str >= $start && $today_str <= $end );
		if ( $in_range ) {
			$time_to_use = $entry['time']     ?? $time_to_use;
			$before_text = $entry['b_text']   ?? $before_text;
			$after_text  = $entry['a_text']   ?? $after_text;
			$hide_time   = $entry['hide_time'] ?? 'off';
			break;
		}
	}

	// nothing to show?
	if ( ! $time_to_use ) {
		echo '<div id="act"></div>';
		return ob_get_clean();
	}

	// escape for JS
	$js_time       = esc_js( $time_to_use );
	$js_before     = esc_js( $before_text );
	$js_after      = esc_js( $after_text );        // this now holds your “during” text
	$js_hide       = esc_js( $hide_time );
	$js_finish_txt = esc_js( $after_count_text );  // this holds your “after countdown” text

	// output HTML + inline script
	?>
    <div id="act"></div>
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            const [hr, min]    = "<?php echo $js_time; ?>".split(':').map(n => parseInt(n,10));
            const beforeText   = "<?php echo $js_before; ?>";
            const afterText    = "<?php echo $js_after; ?>";
            const finishText   = "<?php echo $js_finish_txt; ?>";
            const hideTimeFlag = "<?php echo $js_hide; ?>";
            const now          = new Date();
            const target       = new Date(now.getFullYear(), now.getMonth(), now.getDate(), hr, min, 0).getTime();

            function tick(){
                const diff = target - Date.now();
                if ( diff <= 0 ) {
                    // countdown is over → show finishText only
                    document.getElementById('act').textContent = finishText || '';
                    return;
                }
                // still counting → show beforeText … afterText
                const hrs  = Math.floor(diff / 3600000);
                const mins = Math.floor((diff % 3600000) / 60000);
                const secs = Math.floor((diff % 60000) / 1000);
                document.getElementById('act').textContent =
                    `${beforeText} ${hrs} TIMER ${mins} MIN. ${secs} SEK. ${afterText}`;
                setTimeout(tick, 1000);
            }

            if ( hideTimeFlag === 'off' ) {
                tick();
            } else {
                document.getElementById('act').textContent = `${beforeText} ${afterText}`;
            }
        });
    </script>
	<?php

	return ob_get_clean();
}
add_shortcode( 'act', 'act_shortcode' );



/**
 * Compact H:M:S shortcode: [act_min]
 */
function act_shortcode_min( array $atts = [], $content = null ): string {
	ob_start();

	// load settings
	$opts         = get_option( 'act_settings', [] );
	$today_str    = date( 'Y-m-d' );
	$weekday_key  = strtolower( date( 'l' ) );
	$saved_time   = $opts[ $weekday_key ] ?? null;
	$custom_dates = $opts['custom_dates'] ?? [];

	// determine time + hide
	$time_to_use = $saved_time;
	$hide_time   = 'off';

	foreach ( $custom_dates as $entry ) {
		$start = $entry['start_date'] ?? '';
		$end   = $entry['end_date']   ?? '';
		if ( ! $start ) {
			continue;
		}
		$in_range = empty( $end )
			? ( $today_str === $start )
			: ( $today_str >= $start && $today_str <= $end );
		if ( $in_range ) {
			$time_to_use = $entry['time']     ?? $time_to_use;
			$hide_time   = $entry['hide_time'] ?? 'off';
			break;
		}
	}

	if ( ! $time_to_use ) {
		echo '<div id="act_min"></div>';
		return ob_get_clean();
	}

	// escape for JS
	$js_time = esc_js( $time_to_use );
	$js_hide = esc_js( $hide_time );

	?>
    <div id="act_min"></div>
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            const [hr, min] = "<?php echo $js_time; ?>".split(':').map(n => parseInt(n,10));
            const hideTime  = "<?php echo $js_hide; ?>";
            const now       = new Date();
            const target    = new Date(now.getFullYear(), now.getMonth(), now.getDate(), hr, min, 0).getTime();

            function tick(){
                const diff = target - Date.now();
                if (diff <= 0) {
                    document.getElementById('act_min').textContent = '';
                    return;
                }
                const hrs  = Math.floor(diff / 3600000) % 24;
                const mins = Math.floor((diff % 3600000) / 60000);
                const secs = Math.floor((diff % 60000) / 1000);
                document.getElementById('act_min').innerHTML =
                    "<span class='act_hours'>" + hrs + "</span>:" +
                    "<span class='act_minutes'>" + mins + "</span>:" +
                    "<span class='act_seconds'>" + secs + "</span>";
                setTimeout(tick, 1000);
            }

            if (hideTime === 'off') {
                tick();
            } else {
                document.getElementById('act_min').textContent =
                    "Bestil nu, så sender vi den næste hverdag.";
            }
        });
    </script>
	<?php

	return ob_get_clean();
}
add_shortcode( 'act_min', 'act_shortcode_min' );
