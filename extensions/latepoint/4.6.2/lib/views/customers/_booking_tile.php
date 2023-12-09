<div class="customer-booking status-<?php echo $booking->status; ?>" data-id="<?php echo $booking->id; ?>">
	<h6 class="customer-booking-service-name"><?php echo $booking->service->name; ?></h6>

	<?php if($editable_booking){ ?>
		<div class="customer-booking-buttons">
			<a href="<?php echo $booking->ical_download_link; ?>" target="_blank" class="latepoint-btn latepoint-btn-primary latepoint-btn-link">
				<i class="latepoint-icon latepoint-icon-calendar"></i>
				<span><?php _e('Add to Calendar', 'latepoint'); ?></span>
			</a>
			<?php /* <a href="#" class="latepoint-btn"><i class="latepoint-icon latepoint-icon-ui-46"></i><span><?php _e('Edit', 'latepoint'); ?></span></a> */ ?>
			<?php if(OsCustomerHelper::can_cancel_booking($booking)){ ?>
				<a href="#" class="latepoint-btn latepoint-btn-danger latepoint-request-booking-cancellation latepoint-btn-link" data-route="<?php echo OsRouterHelper::build_route_name('bookings', 'request_cancellation'); ?>">
					<i class="latepoint-icon latepoint-icon-ui-24"></i>
					<span><?php _e('Cancel', 'latepoint'); ?></span>
				</a>
			<?php } ?>
		</div>
	<?php } ?>
		<div class="customer-booking-service-color"></div>

	<div class="customer-booking-info">
		<div class="customer-booking-info-row">
			<span class="booking-info-label"><?php _e('Date', 'latepoint'); ?></span>
			<span class="booking-info-value"><?php echo $booking->format_start_date_and_time(OsSettingsHelper::get_readable_date_format()); ?></span>
		</div>
		<div class="customer-booking-info-row">
			<span class="booking-info-label"><?php _e('Time', 'latepoint'); ?></span>
			<span class="booking-info-value">
				<?php echo OsTimeHelper::minutes_to_hours_and_minutes($booking->get_start_time_shifted_for_customer()); ?>
				<?php if(OsSettingsHelper::get_settings_value('show_booking_end_time') == 'on') echo ' - '. OsTimeHelper::minutes_to_hours_and_minutes($booking->get_end_time_shifted_for_customer()); ?>
			</span>
		</div>
		<div class="customer-booking-info-row">
			<span class="booking-info-label"><?php _e('Agent', 'latepoint'); ?></span>
			<span class="booking-info-value"><?php echo $booking->agent->full_name; ?></span>
		</div>
		<?php if ($booking->livesmart_visitor_url) { ?>
		<div class="customer-booking-info-row">
			<span class="booking-info-label"><?php _e('Video URL', 'latepoint'); ?></span>
			<span class="booking-info-value"><a href="<?php echo $booking->livesmart_visitor_url; ?>" target="_blank"><?php _e('Visitor URL', 'latepoint'); ?></a></span>
		</div>
		<?php } ?>
		<div class="customer-booking-info-row">
			<span class="booking-info-label"><?php _e('Status', 'latepoint'); ?></span>
			<span class="booking-info-value status-<?php echo $booking->status; ?>"><?php echo $booking->nice_status; ?></span>
		</div>
		<?php do_action('latepoint_customer_dashboard_after_booking_info_tile', $booking); ?>
	</div>
	<div class="load-booking-summary-btn-w">
		<a href="#"
		   class="latepoint-btn latepoint-btn-primary latepoint-btn-outline latepoint-btn-block"
		   data-os-after-call="latepoint_init_booking_summary_lightbox"
		   data-os-params="<?php echo OsUtilHelper::build_os_params(['booking_id' => $booking->id]) ?>"
		   data-os-action="<?php echo OsRouterHelper::build_route_name('bookings', 'view_summary_in_lightbox'); ?>"
		   data-os-output-target="lightbox"
			data-os-lightbox-classes="width-500 front-lightbox customer-dashboard-booking-summary-lightbox">
			<i class="latepoint-icon latepoint-icon-file-text"></i>
			<span><?php _e('View Summary', 'latepoint'); ?></span>
		</a>
	</div>
</div>
