<?php
$booking_duration = $booking->get_total_duration(true);
if($booking_duration <= 0) $booking_duration = ($booking->service->duration > 0) ? $booking->service->duration : 60;
$booking_start_percent = ($booking->start_time - $work_boundaries->start_time) / ($work_boundaries->end_time - $work_boundaries->start_time) * 100;
$booking_duration_percent = min($booking_duration / $work_total_minutes * 100, 100 - $booking_start_percent);
if($booking_start_percent < 0) $booking_start_percent = 0;
$buffer_before_height_percent = (($booking->start_time - $booking->buffer_before) >= $work_boundaries->start_time) ? ($booking->buffer_before / $booking_duration * 100) : 0;
$buffer_after_height_percent = (($booking->end_time + $booking->buffer_after) <= $work_boundaries->end_time) ? ($booking->buffer_after / $booking_duration * 100) : 0;
$left = (isset($overlaps_count) && $overlaps_count > 1) ? 'left:'.(100 - round(150 / $overlaps_count)).'%' : '';

$max_capacity = OsServiceHelper::get_max_capacity($booking->service);
if($max_capacity > 1){
  $action_html = OsBookingHelper::group_booking_btn_html($booking->id);
}else{
	$action_html = OsBookingHelper::quick_booking_btn_html($booking->id);
}
?>
<div class="ch-day-booking status-<?php echo $booking->status; ?>" <?php echo $action_html; ?> style="top: <?php echo $booking_start_percent; ?>%; height: <?php echo $booking_duration_percent; ?>%; background-color: <?php echo $booking->service->bg_color; ?>; <?php echo $left; ?>">
	<?php if($buffer_before_height_percent) echo '<div class="ch-day-buffer-before" style="height: '.$buffer_before_height_percent.'%;"></div>'; ?>
	<div class="ch-day-booking-i">
		<div class="booking-service-name"><?php echo OsReplacerHelper::replace_all_vars(OsSettingsHelper::get_booking_template_for_calendar(), array('customer' => $booking->customer, 'agent' => $booking->agent, 'booking' => $booking)); ?></div>
		<div class="booking-time"><?php echo OsTimeHelper::minutes_to_hours_and_minutes($booking->start_time); ?> - <?php echo OsTimeHelper::minutes_to_hours_and_minutes($booking->end_time); ?></div>
		<?php if($max_capacity > 1){ 
			$total_attendies_in_group = $total_attendies_in_group + $booking->total_attendies; ?>
			<div class="booking-attendies">
				<div><?php echo __('Booked:', 'latepoint'). ' <span>'.sprintf(__('%d of %d', 'latepoint'), $total_attendies_in_group, $booking->service->capacity_max).'</span>'; ?></div>
				<div class="booked-percentage">
					<div class="booked-bar" style="width: <?php echo OsServiceHelper::get_percent_of_capacity_booked($booking->service, $total_attendies_in_group); ?>%;"></div>
				</div>
			</div>
		<?php } ?>
		<?php if ($booking->livesmart_visitor_url) { ?>
        <div>
			<span class="booking-info-label"><?php _e('Meeting', 'latepoint'); ?></span>
			<span class="latepoint-btn "><a style="color:white;text-decoration:none;    padding: 5px 5px;" href="<?php echo $booking->livesmart_visitor_url; ?>" target="_blank"><?php _e('Join Meeting', 'latepoint'); ?></a></span>
		</div>
	<?php } ?>
	</div>
	<?php if($buffer_after_height_percent) echo '<div class="ch-day-buffer-after" style="height: '.$buffer_after_height_percent.'%;"></div>'; ?>
</div>