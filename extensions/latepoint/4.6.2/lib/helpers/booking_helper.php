<?php

class OsBookingHelper {

	/**
	 * @param OsBookingModel $booking
	 * @param bool $force_recalculate
	 * @param array $rows_to_hide
	 * @return array[]
	 */
	public static function generate_price_breakdown_rows(OsBookingModel $booking, bool $force_recalculate = false, array $rows_to_hide = []): array{
		$rows = [
			'before_subtotal' => [],
			'subtotal' => [],
			'after_subtotal' => [],
			'total' => [],
			'payments' => [],
			'balance' => []
		];

		if(empty($booking->service_id)) return $rows;

		// payments and balance have to always be recalculated, even if requested for existing booking
		if(!in_array('payments', $rows_to_hide)) {
			$total_payments_amount = $booking->get_total_amount_paid_from_transactions();
			$rows['payments'][] = [
				'label' => __('Payments and Credits', 'latepoint'),
				'raw_value' => OsMoneyHelper::pad_to_db_format($total_payments_amount),
				'value' => (($total_payments_amount > 0) ? '-' : '') . OsMoneyHelper::format_price($total_payments_amount, true, false),
				'type' => ($total_payments_amount > 0) ? 'credit' : ''
			];
		}
		if(!in_array('balance', $rows_to_hide)){
			$balance_due_amount = $booking->get_total_balance_due(true);
			$rows['balance'] = [
				'label' => __('Balance Due', 'latepoint'),
				'raw_value' => OsMoneyHelper::pad_to_db_format($balance_due_amount),
				'value' => OsMoneyHelper::format_price($balance_due_amount, true, false),
				'style' => 'total'
			];
		}

		// try to get existing price breakdown from meta
		if(!$force_recalculate && !$booking->is_new_record()){
			$existing_rows = json_decode($booking->get_meta_by_key('price_breakdown', ''), true);
			if(!empty($existing_rows)){
				return array_merge($rows, $existing_rows);
			}else{
				// probably old booking (before big update), we need to set subtotal
				$booking->subtotal = ($booking->coupon_discount > 0) ? OsMoneyHelper::convert_amount_from_money_input_to_db_format($booking->price) - OsMoneyHelper::convert_amount_from_money_input_to_db_format($booking->coupon_discount) : $booking->price;
				$rows['after_subtotal']['discounts']['items'][] = [
					'label' => __('Coupon', 'latepoint-coupons'),
					'raw_value' => -OsMoneyHelper::pad_to_db_format($booking->coupon_discount),
					'value' => '-'.OsMoneyHelper::format_price($booking->coupon_discount),
					'badge' => $booking->coupon_code,
					'type' => 'credit'
				];;
				return $rows;
			}
		}

		// recalculations are below this point
		$service_row = [
			'heading' => __('Service', 'latepoint'),
			'items' => []
		];
		$service_amount = OsMoneyHelper::calculate_full_amount_for_service($booking, false, false);
		$service_row_item = [
			'label' => $booking->service->name,
			'raw_value' => OsMoneyHelper::pad_to_db_format($service_amount),
			'value' => OsMoneyHelper::format_price($service_amount, true, false)
		];
		$service_row_item = apply_filters('latepoint_price_breakdown_service_row_item', $service_row_item, $booking);
		$service_row['items'][] = $service_row_item;
		$rows['before_subtotal'][] = $service_row;

		$subtotal_amount = $booking->full_amount_to_charge(false, false);
		$rows['subtotal'] = [
			'label' => __('Sub Total', 'latepoint'),
			'style' => 'strong',
			'raw_value' => OsMoneyHelper::pad_to_db_format($subtotal_amount),
			'value' => OsMoneyHelper::format_price($subtotal_amount, true, false)
		];
		$rows['after_subtotal'] = [];
		$total_amount = $booking->full_amount_to_charge();
		$rows['total'] = [
			'label' => __('Total Price', 'latepoint'),
			'style' => in_array('balance', $rows_to_hide) ? 'total' : 'strong',
			'raw_value' => OsMoneyHelper::pad_to_db_format($total_amount),
			'value' => OsMoneyHelper::format_price($total_amount, true, false)
		];

		// filter only applies to recalculated rows, do not apply it to the existing captured data, since it's already ran
		$rows = apply_filters('latepoint_price_breakdown_rows', $rows, $booking, $rows_to_hide);
		return $rows;
	}

	/**
	 *
	 * Determine whether to show
	 *
	 * @param $rows
	 * @return bool
	 */
	public static function is_breakdown_free($rows){
		return ((empty($rows['subtotal']['raw_value']) || ((float)$rows['subtotal']['raw_value'] <= 0)) && (empty($rows['total']['raw_value']) || ((float)$rows['total']['raw_value'] <= 0)));
	}

	public static function output_price_breakdown($rows){
		foreach($rows['before_subtotal'] as $row){
			self::output_price_breakdown_row($row);
		}
		// if there is nothing between subtotal and total - don't show subtotal as it will be identical to total
		if(!empty($rows['after_subtotal'])){
			if(!empty($rows['subtotal'])) {
				echo '<div class="subtotal-separator"></div>';
				self::output_price_breakdown_row($rows['subtotal']);
			}
			foreach($rows['after_subtotal'] as $row){
				self::output_price_breakdown_row($row);
			}
		}
		if(!empty($rows['total'])) {
			self::output_price_breakdown_row($rows['total']);
		}
		if(!empty($rows['payments'])){
			foreach($rows['payments'] as $row){
				self::output_price_breakdown_row($row);
			}
		}
		if(!empty($rows['balance'])) {
			self::output_price_breakdown_row($rows['balance']);
		}
	}

	public static function output_price_breakdown_row($row){
		if(!empty($row['items'])){
			if(!empty($row['heading'])) echo '<div class="summary-box-heading"><div class="sbh-item">'.$row['heading'].'</div><div class="sbh-line"></div></div>';
			foreach($row['items'] as $row_item){
				self::output_price_breakdown_row($row_item);
			}
		}else{
			$extra_class = '';
			if(isset($row['style']) && $row['style'] == 'strong') $extra_class.= ' spi-strong';
			if(isset($row['style']) && $row['style'] == 'total') $extra_class.= ' spi-total';
			if(isset($row['type']) && $row['type'] == 'credit') $extra_class.= ' spi-positive';
			?>
		  <div class="summary-price-item-w <?php echo $extra_class; ?>">
			  <div class="spi-name">
				  <?php echo $row['label']; ?>
				  <?php if(!empty($row['note'])) echo '<span class="pi-note">'.$row['note'].'</span>'; ?>
				  <?php if(!empty($row['badge'])) echo '<span class="pi-badge">'.$row['badge'].'</span>'; ?>
			  </div>
			  <div class="spi-price"><?php echo $row['value'] ?></div>
		  </div>
			<?php
		}
	}

	public static function output_price_breakdown_row_as_input_field($row, $base_name){
		if(!empty($row['items'])){
			$field_name = $base_name.'['.OsUtilHelper::random_text('alnum', 8).']';
			echo OsFormHelper::hidden_field($field_name.'[heading]', $row['heading']);
			foreach($row['items'] as $row_item){
				self::output_price_breakdown_row_as_input_field($row_item, $field_name.'[items]');
			}
		}else{
			$field_name = $base_name.'['.OsUtilHelper::random_text('alnum', 8).']';
			$wrapper_class = ($row['raw_value'] < 0) ? ['class' => 'green-value-input'] : [];
			echo OsFormHelper::money_field($field_name.'[value]', $row['label'], $row['raw_value'], ['theme' => 'right-aligned'], [], $wrapper_class);
			echo OsFormHelper::hidden_field($field_name.'[label]', $row['label']);
			echo OsFormHelper::hidden_field($field_name.'[style]', $row['style']);
			echo OsFormHelper::hidden_field($field_name.'[type]', $row['type']);
			echo OsFormHelper::hidden_field($field_name.'[note]', $row['note']);
			echo OsFormHelper::hidden_field($field_name.'[badge]', $row['badge']);
		}
	}

	/**
	 * @param \LatePoint\Misc\Filter $filter
	 * @param bool $allow_full_access
	 * @return array
	 */
	public static function get_blocked_periods_grouped_by_day(\LatePoint\Misc\Filter $filter, bool $allow_full_access = false): array{
		$grouped_blocked_periods = [];

		if($filter->date_from) {
			$date_from = OsWpDateTime::os_createFromFormat('Y-m-d', $filter->date_from);
			$date_to = ($filter->date_to) ? OsWpDateTime::os_createFromFormat('Y-m-d', $filter->date_to) : OsWpDateTime::os_createFromFormat('Y-m-d', $filter->date_from);

			# Loop through days to fill in days that might have no bookings
			for ($day = clone $date_from; $day->format('Y-m-d') <= $date_to->format('Y-m-d'); $day->modify('+1 day')) {
				$grouped_blocked_periods[$day->format('Y-m-d')] = [];
			}
		}
		if(!$allow_full_access){
			$today = new OsWpDateTime('today');
			$earliest_possible_booking = OsSettingsHelper::get_settings_value('earliest_possible_booking', false);
			$block_end_datetime = OsTimeHelper::now_datetime_object();
			if($earliest_possible_booking){
				try{
					$block_end_datetime->modify($earliest_possible_booking);
				}catch(Exception $e){
					$block_end_datetime = OsTimeHelper::now_datetime_object();
				}
			}
			for($day = clone $today; $day->format('Y-m-d') <= $block_end_datetime->format('Y-m-d'); $day->modify('+1 day')) {
				// loop days from now to earliest possible booking and block timeslots if these days were actually requested
				if(isset($grouped_blocked_periods[$day->format('Y-m-d')])) {
					$grouped_blocked_periods[$day->format('Y-m-d')][] = new \LatePoint\Misc\BlockedPeriod([
						'start_time' => 0,
						'end_time' => ($day->format('Y-m-d') < $block_end_datetime->format('Y-m-d')) ? 24*60 : OsTimeHelper::convert_datetime_to_minutes($block_end_datetime),
						'start_date' => $day->format('Y-m-d'),
						'end_date' => $day->format('Y-m-d')]);
				}
			}
		}

		$grouped_blocked_periods = apply_filters('latepoint_blocked_periods_for_range', $grouped_blocked_periods, $filter);
		return $grouped_blocked_periods;
	}

	/**
	 * @param \LatePoint\Misc\Filter $filter
	 * @return array
	 */
	public static function get_booked_periods_grouped_by_day(\LatePoint\Misc\Filter $filter): array{
		$booked_periods = self::get_booked_periods($filter);

		$grouped_booked_periods = [];
		if($filter->date_from){
			$date_from = OsWpDateTime::os_createFromFormat('Y-m-d', $filter->date_from);
			$date_to = ($filter->date_to) ? OsWpDateTime::os_createFromFormat('Y-m-d', $filter->date_to) : OsWpDateTime::os_createFromFormat('Y-m-d', $filter->date_from);

			# Loop through days to fill in days that might have no bookings
      for($day = clone $date_from; $day->format('Y-m-d') <= $date_to->format('Y-m-d'); $day->modify('+1 day')){
				$grouped_booked_periods[$day->format('Y-m-d')] = [];
      }
			foreach($booked_periods as $booked_period){
				$grouped_booked_periods[$booked_period->start_date][] = $booked_period;
				// if event spans multiple days - add to other days as well
				if($booked_period->end_date && ($booked_period->start_date != $booked_period->end_date)) $grouped_booked_periods[$booked_period->end_date][] = $booked_period;
			}
		}
		return $grouped_booked_periods;
	}

	/**
	 * @param \LatePoint\Misc\Filter $filter
	 * @return \LatePoint\Misc\BookedPeriod[]
	 */
	public static function get_booked_periods(\LatePoint\Misc\Filter $filter): array{


		$bookings = self::get_bookings($filter, true);
		$booked_periods = [];

    foreach($bookings as $booking){
      $booked_periods[] = \LatePoint\Misc\BookedPeriod::create_from_booking_model($booking);
    }

		// TODO Update all filters to accept new "filter" variable (In Google Calendar addon)
    $booked_periods = apply_filters('latepoint_get_booked_periods', $booked_periods, $filter);
		return $booked_periods;
	}


	/**
	 * @param \LatePoint\Misc\Filter $filter
	 * @param bool $as_models
	 * @return array
	 */
	public static function get_bookings(\LatePoint\Misc\Filter $filter, bool $as_models = false): array{
		$bookings = new OsBookingModel();
		if($filter->date_from){
			if($filter->date_from && $filter->date_to){
				# both start and end date provided - means it's a range
				$bookings->where(['start_date >=' => $filter->date_from, 'start_date <=' => $filter->date_to]);
			}else{
				# only start_date provided - means it's a specific date requested
				$bookings->where(['start_date' => $filter->date_from]);
			}
		}


		if($filter->connections){
			$connection_conditions = [];
			foreach($filter->connections as $connection){
				$connection_conditions[] = ['AND' => ['agent_id' => $connection->agent_id, 'service_id' => $connection->service_id, 'location_id' => $connection->location_id]];
			}
			$bookings->where(['OR' => $connection_conditions]);
		}else{
	    if($filter->agent_id)     $bookings->where(['agent_id' => $filter->agent_id]);
	    if($filter->location_id)  $bookings->where(['location_id' => $filter->location_id]);
	    if($filter->service_id)   $bookings->where(['service_id' => $filter->service_id]);
		}
    if($filter->statuses)     $bookings->where(['status' => $filter->statuses]);
    if($filter->exclude_booking_ids)  $bookings->where(['id NOT IN' => $filter->exclude_booking_ids]);
		$bookings->order_by('start_time asc, end_time asc, service_id asc');
    $bookings = ($as_models) ? $bookings->get_results_as_models() : $bookings->get_results();

		// make sure to return empty array if nothing is found
		if(empty($bookings)) $bookings = [];

		return $bookings;
	}

  public static function generate_ical_event_string($booking){
    $booking_description = sprintf(__('Appointment with %s for %s', 'latepoint'), $booking->agent->full_name, $booking->service->name);
    $livesmart_url = ($booking->livesmart_visitor_url) ? 'Meeting URL: ' . $booking->livesmart_visitor_url : '';
    $ics = new ICS(array(
      'location' => $booking->location->full_address,
      'description' => $livesmart_url,
      'dtstart' => $booking->format_start_date_and_time_for_google(),
      'dtend' => $booking->format_end_date_and_time_for_google(),
      'summary' => $booking_description,
      'url' => get_site_url()
    ));

    return $ics->to_string();
  }

	/**
	 * @param \LatePoint\Misc\BookingRequest $booking_request
	 * @return bool
	 *
	 * Checks if requested booking slot is available, loads work periods and booked periods from database and checks availabilty against them
	 */
	public static function is_booking_request_available(\LatePoint\Misc\BookingRequest $booking_request): bool{
		$requested_date = new OsWpDateTime($booking_request->start_date);
		$resources = OsResourceHelper::get_resources_grouped_by_day($booking_request, $requested_date);
		if(empty($resources[$requested_date->format('Y-m-d')])) return false;
		$is_available = false;
		foreach($resources[$requested_date->format('Y-m-d')] as $resource){
			foreach($resource->slots as $slot){
				if($slot->start_time == $booking_request->start_time && $slot->can_accomodate($booking_request->total_attendies)) $is_available = true;
				if($is_available) break;
			}
			if($is_available) break;
		}
		return $is_available;
	}

	/**
	 *
	 * Checks if two bookings are part of the same group appointment
	 *
	 * @param bool|OsBookingModel $booking
	 * @param bool|OsBookingModel $compare_booking
	 * @return bool
	 */
  public static function check_if_group_bookings($booking, $compare_booking): bool{
    if($booking && $compare_booking && ($compare_booking->start_time == $booking->start_time) && ($compare_booking->end_time == $booking->end_time) && ($compare_booking->service_id == $booking->service_id) && ($compare_booking->location_id == $booking->location_id)){
      return true;
    }else{
      return false;
    }
  }

  public static function get_default_payment_portion_type($booking){
    $regular_price = $booking->full_amount_to_charge(false, false);
    $deposit_price = $booking->deposit_amount_to_charge(false);
    if(($regular_price == 0) && ($deposit_price > 0)){
      return LATEPOINT_PAYMENT_PORTION_DEPOSIT;
    }else{
      return LATEPOINT_PAYMENT_PORTION_FULL;
    }
  }

  public static function widget_day_info(DateTime $target_date, $args = []){
    $location_id = isset($args['location_id']) ? $args['location_id'] : false;
    $bookings = OsBookingHelper::get_stat_for_period('bookings', $target_date->format('Y-m-d'), $target_date->format('Y-m-d'), false, false, false, $location_id);
    $bookings_per_service = OsBookingHelper::get_stat_for_period('bookings', $target_date->format('Y-m-d'), $target_date->format('Y-m-d'), 'service_id', false, false, $location_id);
    $work_periods_arr = OsWorkPeriodsHelper::get_work_periods(new \LatePoint\Misc\Filter(['date_from' => $target_date->format('Y-m-d'),
																                                                          'location_id' => $location_id,
																                                                          'week_day' => $target_date->format('N')]));

    $agents = new OsAgentModel();
    $agents_ids = $agents->select('id')->should_be_active()->get_results(ARRAY_A);
    $agents_on_duty = [];
    foreach($agents_ids as $agent_id){
      $work_periods = OsWorkPeriodsHelper::get_work_periods(new \LatePoint\Misc\Filter(['date_from' => $target_date->format('Y-m-d'),
														                                                            'week_day' => $target_date->format('N'),
														                                                            'agent_id' => $agent_id['id'],
														                                                            'location_id' => $location_id]));
      if($work_periods && $work_periods[0] != '0:0'){
        $agents_on_duty[] = $agent_id['id'];
      }else{
        continue;
      }
    }
    ?>
    <div class="os-widget-today-info">
      <div class="day-sub-info">
        <div class="day-sub-info-col">
          <div class="sub-info-label"><?php _e('Number of Bookings:', 'latepoint') ?></div>
          <div class="sub-info-value"><?php echo $bookings; ?></div>
        </div>
	      <div class="day-info-progress">
	        <?php if($bookings && $bookings_per_service){ ?>
	          <?php foreach($bookings_per_service as $stat_info){
	            $service = new OsServiceModel($stat_info['service_id']);
	          ?>
	          <div class="di-progress-value" style="width: <?php echo $stat_info['stat'] / $bookings * 100; ?>%; background-color: <?php echo $service->bg_color; ?>">
	            <div class="progress-label-w">
	              <div class="progress-value"><strong><?php echo $stat_info['stat']; ?></strong> <span><?php _e('Bookings', 'latepoint'); ?></span></div>
	              <div class="progress-label"><?php echo $service->name; ?></div>
	            </div>
	          </div>
	          <?php } ?>
	        <?php } ?>
	      </div>
        <div class="day-sub-info-col">
          <div class="sub-info-label"><?php _e('Work Hours:', 'latepoint') ?></div>
	        <div class="sub-info-value">
	          <?php foreach($work_periods_arr as $work_period){
	            if(!$work_period->start_time && !$work_period->end_time) {
	              echo '<div class="sub-info-value">'.__('Day Off', 'latepoint').'</div>';
	            }else{ ?>
		            <?php echo '<div class="sub-info-period">'. OsTimeHelper::minutes_to_hours_and_minutes($work_period->start_time).' - '.OsTimeHelper::minutes_to_hours_and_minutes($work_period->end_time). '</div>'; ?>
	            <?php } ?>
	          <?php } ?>
          </div>
        </div>
        <div class="day-sub-info-col with-avatars">
          <div class="sub-info-label"><?php _e('Agents On Duty:', 'latepoint') ?></div>
          <div class="sub-info-value">
	          <div class="agents-on-duty-avatars">
	            <?php foreach($agents_on_duty as $index => $agent_id_on_duty){
	              if($index == 3) break;
	              $agent = new OsAgentModel($agent_id_on_duty);
	              echo '<div class="avatar-w" style="background-image: url('.esc_attr($agent->get_avatar_url()).')"></div>';
	            } ?>
	          </div>
          </div>
        </div>
	      <a href="#" class="latepoint-top-new-appointment-btn latepoint-btn latepoint-btn-white" <?php echo OsBookingHelper::quick_booking_btn_html(false, ['start_date' => $target_date->format('Y-m-d'), 'location_id' => $location_id]); ?>>
					<i class="latepoint-icon latepoint-icon-plus"></i>
					<span><?php _e('New Appointment', 'latepoint'); ?></span>
				</a>
      </div>
    </div>
    <?php
  }

  public static function get_payment_total_info_html($booking){
    $payment_portion = (OsBookingHelper::get_default_payment_portion_type($booking) == LATEPOINT_PAYMENT_PORTION_DEPOSIT) ? ' paying-deposit ' : '';
    $html = '<div class="payment-total-info '.$payment_portion.'">
              <div class="payment-total-price-w"><span>'.__('Total booking price: ', 'latepoint').'</span><span class="lp-price-value">'.$booking->formatted_full_price().'</span></div>
              <div class="payment-deposit-price-w"><span>'.__('Deposit Amount: ', 'latepoint').'</span><span class="lp-price-value">'.$booking->formatted_deposit_price().'</span></div>
            </div>';
    $html = apply_filters('latepoint_filter_payment_total_info', $html, $booking);
    return $html;
  }


  public static function process_actions_after_save($booking_id){
  }

	/**
	 * @param DateTime $start_date
	 * @param DateTime $end_date
	 * @param \LatePoint\Misc\BookingRequest $booking_request
	 * @param \LatePoint\Misc\BookingResource[] $resources
	 * @param array $settings
	 * @return string
	 * @throws Exception
	 */
  public static function get_quick_availability_days(DateTime $start_date, DateTime $end_date, \LatePoint\Misc\BookingRequest $booking_request, array $resources = [], array $settings = []){
    $default_settings = [
      'work_boundaries' => false,
      'exclude_booking_ids' => []
    ];
    $settings = array_merge($default_settings, $settings);

    $html = '';

		if(!$resources) $resources = OsResourceHelper::get_resources_grouped_by_day($booking_request, $start_date, $end_date, $settings);
		if(!$settings['work_boundaries']) $settings['work_boundaries'] = OsResourceHelper::get_work_boundaries_for_groups_of_resources($resources);

    for($day_date=clone $start_date; $day_date<=$end_date; $day_date->modify('+1 day')){
			// first day of month, output month name
      if($day_date->format('j') == '1'){
        $html.= '<div class="ma-month-label">'.OsUtilHelper::get_month_name_by_number($day_date->format('n')).'</div>';
      }
      $html.= '<div class="ma-day ma-day-number-'.$day_date->format('N').'">';
        $html.= '<div class="ma-day-info">';
          $html.= '<span class="ma-day-number">'.$day_date->format('j').'</span>';
          $html.= '<span class="ma-day-weekday">'.OsUtilHelper::get_weekday_name_by_number($day_date->format('N'), true).'</span>';
        $html.= '</div>';
        $html.= OsTimelineHelper::availability_timeline($booking_request, $settings['work_boundaries'], $resources[$day_date->format('Y-m-d')], ['book_on_click' => false]);
      $html.= '</div>';
    }
    return $html;
  }

  public static function count_pending_bookings($agent_id = false, $location_id = false){
    $bookings = new OsBookingModel();
    if($agent_id){
      $bookings->where(['agent_id' => $agent_id]);
    }
    if($location_id){
      $bookings->where(['location_id' => $location_id]);
    }
    return $bookings->where(['status IN' => apply_filters('latepoint_pending_booking_statuses', [LATEPOINT_BOOKING_STATUS_PENDING, LATEPOINT_BOOKING_STATUS_PAYMENT_PENDING])])->count();
  }


  public static function generate_services_list($services = false, $preselected_service = false, $preselected_duration = false, $preselected_total_attendies = false){
    if($services && is_array($services) && !empty($services)){ ?>
      <div class="os-services os-animated-parent os-items os-as-rows os-selectable-items">
        <?php foreach($services as $service){
          // if service is preselected - only output that service, skip the rest
          if($preselected_service && $service->id != $preselected_service->id) continue;
          $service_durations = $service->get_all_durations_arr();
          $is_priced = (!(count($service_durations) > 1) && $service->charge_amount) ? true : false;
          $show_capacity_selector = $service->should_show_capacity_selector();
          $activate_sub_step = (count($service_durations) > 1 && !$preselected_duration) ? 'latepoint_show_durations' : (($show_capacity_selector && !$preselected_total_attendies) ? 'latepoint_show_capacity_selector' : '');
          ?>
          <div class="os-animated-child os-item os-selectable-item <?php echo ($preselected_service && $service->id == $preselected_service->id) ? 'selected is-preselected' : ''; ?> <?php echo ($is_priced) ? 'os-priced-item' : ''; ?> <?php if($service->short_description) echo 'with-description'; ?> <?php if($service->get_extra_durations()) echo 'has-multiple-durations has-child-items'; ?>"
              data-item-price="<?php echo $service->charge_amount; ?>"
              data-priced-item-type="service"
              data-summary-field-name="service"
              data-summary-value="<?php echo esc_attr($service->name); ?>"
              data-item-id="<?php echo $service->id; ?>"
              data-min-capacity="<?php echo $service->capacity_min; ?>"
              data-max-capacity="<?php echo $service->capacity_max; ?>"
              data-activate-sub-step="<?php echo $activate_sub_step; ?>"
              data-id-holder=".latepoint_service_id" >
            <div class="os-service-selector os-item-i os-animated-self"
              data-min-capacity="<?php echo $service->capacity_min; ?>"
              data-max-capacity="<?php echo $service->capacity_max; ?>"
              data-service-id="<?php echo $service->id; ?>">
              <?php if($service->selection_image_id){ ?>
                <span class="os-item-img-w" style="background-image: url(<?php echo $service->selection_image_url; ?>);"></span>
              <?php } ?>
              <span class="os-item-name-w">
                <span class="os-item-name"><?php echo $service->name; ?></span>
                <?php if($service->short_description){ ?>
                  <span class="os-item-desc"><?php echo $service->short_description; ?></span>
                <?php } ?>
              </span>
              <?php if($service->price_min > 0){ ?>
                <span class="os-item-price-w">
                  <span class="os-item-price">
                    <?php echo $service->price_min_formatted; ?>
                  </span>
                  <?php if($service->price_min != $service->price_max){ ?>
                    <span class="os-item-price-label"><?php _e('Starts From', 'latepoint'); ?></span>
                  <?php } ?>
                </span>
              <?php } ?>
            </div>
            <?php if(count($service_durations) > 1){ ?>
              <div class="os-service-durations os-animated-parent os-items os-as-grid os-three-columns os-selectable-items">
              <?php
              $previous_sub_step = $activate_sub_step;
              $activate_sub_sub_step = ($show_capacity_selector && !$preselected_total_attendies) ? 'latepoint_show_capacity_selector' : '';
              foreach($service_durations as $service_duration){
								$summary_duration_label = OsServiceHelper::get_summary_duration_label($service_duration['duration']);
                $is_priced = ($service_duration['charge_amount']) ? true : false;?>
                <div class="os-duration-item os-animated-child os-selectable-item os-item with-floating-price <?php echo ($is_priced) ? 'os-priced-item' : ''; ?>"
                      data-item-price="<?php echo $service_duration['charge_amount']; ?>"
                      data-priced-item-type="service"
                      data-summary-field-name="duration"
                      data-min-capacity="<?php echo $service->capacity_min; ?>"
                      data-max-capacity="<?php echo $service->capacity_max; ?>"
                      data-previous-sub-step="<?php echo $previous_sub_step; ?>"
                      data-activate-sub-step="<?php echo $activate_sub_sub_step; ?>"
                      data-summary-value="<?php echo esc_attr($summary_duration_label); ?>"
                      data-item-id="<?php echo $service_duration['duration']; ?>"
                      data-id-holder=".latepoint_duration">
                  <div class="os-animated-self os-item-i os-service-duration-selector">
                    <?php if(($service_duration['duration'] >= 60) && !OsSettingsHelper::is_on('steps_show_duration_in_minutes')){ ?>
                      <?php
                      $hours = floor($service_duration['duration'] / 60);
                      $minutes = $service_duration['duration'] % 60;
                      ?>
                      <div class="os-duration-value"><?php echo $hours; ?></div>
                      <div class="os-duration-label"><?php echo ($hours > 1) ? __('Hours', 'latepoint') : __('Hour', 'latepoint'); ?></div>
                      <?php if($minutes) echo '<div class="os-duration-sub-label"><span>'.$minutes.'</span> '.__('Minutes', 'latepoint').'</div>'; ?>
                    <?php }else{ ?>
                      <div class="os-duration-value"><?php echo $service_duration['duration']; ?></div>
                      <div class="os-duration-label"><?php _e('Minutes', 'latepoint'); ?></div>
                    <?php } ?>
                    <?php if($service_duration['charge_amount']) echo '<div class="os-duration-price">'.OsMoneyHelper::format_price($service_duration['charge_amount']).'</div>'; ?>
                  </div>
                </div>
                <?php
              } ?>
              </div>
              <?php
            } ?>
          </div>
        <?php } ?>
      </div>
    <?php }
  }

  public static function generate_services_and_categories_list($parent_id = false, array $settings = []){
		$default_settings = [
			'show_service_categories_arr' => false,
			'show_services_arr' => false,
			'preselected_category' => false,
			'preselected_duration' => false,
			'preselected_total_attendies' => false
		];
		$settings = array_merge($default_settings, $settings);

    $service_categories = new OsServiceCategoryModel();
    $args = array();
    if($settings['show_service_categories_arr'] && is_array($settings['show_service_categories_arr'])){
      if($parent_id){
        $service_categories->where(['parent_id' => $parent_id]);
      }else{
        if($settings['preselected_category']){
          $service_categories->where(['id' => $settings['preselected_category']]);
        }else{
          $service_categories->where_in('id', $settings['show_service_categories_arr']);
          $service_categories->where(['parent_id' => ['OR' => ['IS NULL', ' NOT IN' => $settings['show_service_categories_arr']] ]]);
        }
      }
    }else{
      if($settings['preselected_category']){
        $service_categories->where(['id' => $settings['preselected_category']]);
      }else{
        $args['parent_id'] = $parent_id ? $parent_id : 'IS NULL';
      }
    }
    $service_categories = $service_categories->where($args)->order_by('order_number asc')->get_results_as_models();

    $main_parent_class = ($parent_id) ? 'os-animated-parent': 'os-item-categories-main-parent os-animated-parent';
    if(!$settings['preselected_category']) echo '<div class="os-item-categories-holder '.$main_parent_class.'">';

    // generate services that have no category
    if($parent_id == false && $settings['preselected_category'] == false){ ?>
          <?php
          $services_without_category = new OsServiceModel();
          if($settings['show_services_arr']) $services_without_category->where_in('id', $settings['show_services_arr']);
          $services_without_category = $services_without_category->where(['category_id' => 0])->should_be_active()->get_results_as_models();
          if($services_without_category) OsBookingHelper::generate_services_list($services_without_category, false, $settings['preselected_duration'], $settings['preselected_total_attendies']);
    }

    if(is_array($service_categories)){
      foreach($service_categories as $service_category){ ?>
        <?php
        $services = [];
        $category_services = $service_category->get_active_services();
        if(is_array($category_services)){
          // if show selected services restriction is set - filter
          if($settings['show_services_arr']){
            foreach($category_services as $category_service){
              if(in_array($category_service->id, $settings['show_services_arr'])) $services[] = $category_service;
            }
          }else{
            $services = $category_services;
          }
        }
        $child_categories = new OsServiceCategoryModel();
        $count_child_categories = $child_categories->where(['parent_id' => $service_category->id])->count();
        // show only if it has either at least one child category or service
        if($count_child_categories || count($services)){
          // preselected category, just show contents, not the wrapper
          if($service_category->id == $settings['preselected_category']){
            OsBookingHelper::generate_services_list($services, false, $settings['preselected_duration'], $settings['preselected_total_attendies']);
            OsBookingHelper::generate_services_and_categories_list($service_category->id, array_merge($settings, ['preselected_category' => false]));
          }else{ ?>
            <div class="os-item-category-w os-items os-as-rows os-animated-child" data-id="<?php echo $service_category->id; ?>">
              <div class="os-item-category-info-w os-item os-animated-self with-plus">
                <div class="os-item-category-info os-item-i">
                  <div class="os-item-img-w" style="background-image: url(<?php echo $service_category->selection_image_url; ?>);"></div>
                  <div class="os-item-name-w">
                    <div class="os-item-name"><?php echo $service_category->name; ?></div>
                  </div>
                  <?php if(count($services)){ ?>
                    <div class="os-item-child-count"><span><?php echo count($services); ?></span> <?php _e('Services', 'latepoint'); ?></div>
                  <?php } ?>
                </div>
              </div>
              <?php OsBookingHelper::generate_services_list($services, false, $settings['preselected_duration'], $settings['preselected_total_attendies']); ?>
              <?php OsBookingHelper::generate_services_and_categories_list($service_category->id, array_merge($settings, ['preselected_category' => false])); ?>
            </div><?php
          }
        }
      }
    }
    if(!$settings['preselected_category']) echo '</div>';
  }

  public static function group_booking_btn_html($booking_id = false){
    $html = 'data-os-params="'.http_build_query(['booking_id' => $booking_id]).'" 
                  data-os-action="'.OsRouterHelper::build_route_name('bookings', 'grouped_bookings_quick_view').'" 
                  data-os-output-target="lightbox"
                  data-os-lightbox-classes="width-500"
                  data-os-after-call="latepoint_init_grouped_bookings_form"';
    return $html;
  }

  public static function quick_booking_btn_html($booking_id = false, $params = array()){
    $html = '';
    if($booking_id) $params['id'] = $booking_id;
    $route = OsRouterHelper::build_route_name('bookings', 'quick_edit');

    $params_str = http_build_query($params);
    $html = 'data-os-params="'.$params_str.'" 
    data-os-action="'.$route.'" 
    data-os-output-target="side-panel"
    data-os-after-call="latepoint_init_quick_booking_form"';
    return $html;
  }

  public static function get_services_count_by_type_for_date($date, $agent_id = false){
    $bookings = new OsBookingModel();
    $where_args = array('start_date' => $date);
    if($agent_id) $where_args['agent_id'] = $agent_id;
    return $bookings->select(LATEPOINT_TABLE_SERVICES.".name, count(".LATEPOINT_TABLE_BOOKINGS.".id) as count, bg_color")->join(LATEPOINT_TABLE_SERVICES, array(LATEPOINT_TABLE_SERVICES.".id" => 'service_id'))->where($where_args)->group_by('service_id')->get_results(ARRAY_A);
  }

	/**
	 * @param OsBookingModel $booking
	 * @return false|mixed
	 *
	 * Search for available agent based on booking requirements and agent picking preferences. Will return false if no available agent found.
	 */
  public static function get_any_agent_for_booking_by_rule(OsBookingModel $booking){
    // ANY AGENT SELECTED
    // get available agents
    $connected_ids = OsAgentHelper::get_agent_ids_for_service_and_location($booking->service_id, $booking->location_id);

    // If date/time is selected - filter agents who are available at that time
    if($booking->start_date && $booking->start_time){
      $available_agent_ids = [];
			$booking_request = \LatePoint\Misc\BookingRequest::create_from_booking_model($booking);
      foreach($connected_ids as $agent_id){
				$booking_request->agent_id = $agent_id;
        if(OsBookingHelper::is_booking_request_available($booking_request)){
          $available_agent_ids[] = $agent_id;
        }
      }
      $connected_ids = array_intersect($available_agent_ids, $connected_ids);
    }


    $agents_model = new OsAgentModel();
    if(!empty($connected_ids)){
      $agents_model->where_in('id', $connected_ids);
      $agents = $agents_model->should_be_active()->get_results_as_models();
    }else{
      $agents = [];
    }

    if(empty($agents)){
      return false;
    }


    $selected_agent_id = false;
    switch(OsSettingsHelper::get_any_agent_order()){
      case LATEPOINT_ANY_AGENT_ORDER_RANDOM:
        $selected_agent_id = $connected_ids[rand(0, count($connected_ids) - 1)];
      break;
      case LATEPOINT_ANY_AGENT_ORDER_PRICE_HIGH:
        $highest_price = false;
        foreach($agents as $agent){
          $booking->agent_id = $agent->id;
          $price = OsMoneyHelper::calculate_full_amount_to_charge($booking);
          if($highest_price === false && $selected_agent_id === false){
            $highest_price = $price;
            $selected_agent_id = $agent->id;
          }else{
            if($highest_price < $price){
              $highest_price = $price;
              $selected_agent_id = $agent->id;
            }
          }
        }
      break;
      case LATEPOINT_ANY_AGENT_ORDER_PRICE_LOW:
        $lowest_price = false;
        foreach($agents as $agent){
          $booking->agent_id = $agent->id;
          $price = OsMoneyHelper::calculate_full_amount_to_charge($booking);
          if($lowest_price === false && $selected_agent_id === false){
            $lowest_price = $price;
            $selected_agent_id = $agent->id;
          }else{
            if($lowest_price > $price){
              $lowest_price = $price;
              $selected_agent_id = $agent->id;
            }
          }
        }
      break;
      case LATEPOINT_ANY_AGENT_ORDER_BUSY_HIGH:
        $max_bookings = false;
        foreach($agents as $agent){
          $agent_total_bookings = OsBookingHelper::get_total_bookings_for_date($booking->start_date, ['agent_id' => $agent->id]);
          if($max_bookings === false && $selected_agent_id === false){
            $max_bookings = $agent_total_bookings;
            $selected_agent_id = $agent->id;
          }else{
            if($max_bookings < $agent_total_bookings){
              $max_bookings = $agent_total_bookings;
              $selected_agent_id = $agent->id;
            }
          }
        }
      break;
      case LATEPOINT_ANY_AGENT_ORDER_BUSY_LOW:
        $min_bookings = false;
        foreach($agents as $agent){
          $agent_total_bookings = OsBookingHelper::get_total_bookings_for_date($booking->start_date, ['agent_id' => $agent->id]);
          if($min_bookings === false && $selected_agent_id === false){
            $min_bookings = $agent_total_bookings;
            $selected_agent_id = $agent->id;
          }else{
            if($min_bookings > $agent_total_bookings){
              $min_bookings = $agent_total_bookings;
              $selected_agent_id = $agent->id;
            }
          }
        }
      break;
    }
    $booking->agent_id = $selected_agent_id;
    return $selected_agent_id;
  }

  public static function get_total_customers_for_date($date, $condition = []){
    $args = ['start_date' => $date];
    if(isset($conditions['agent_id']) && $conditions['agent_id']) $args['agent_id'] = $conditions['agent_id'];
    if(isset($conditions['service_id']) && $conditions['service_id']) $args['service_id'] = $conditions['service_id'];
    if(isset($conditions['location_id']) && $conditions['location_id']) $args['location_id'] = $conditions['location_id'];

    $bookings = new OsBookingModel();
    return $bookings->group_by('customer_id')->where($args)->count();
  }


  public static function get_total_bookings_for_date($date, $conditions = [], $grouped = false){
    $args = ['start_date' => $date];
    if(isset($conditions['agent_id']) && $conditions['agent_id']) $args['agent_id'] = $conditions['agent_id'];
    if(isset($conditions['service_id']) && $conditions['service_id']) $args['service_id'] = $conditions['service_id'];
    if(isset($conditions['location_id']) && $conditions['location_id']) $args['location_id'] = $conditions['location_id'];


    $bookings = new OsBookingModel();
    if($grouped) $bookings->group_by('start_date, start_time, end_time, service_id, location_id');
    $bookings = $bookings->where($args);
    return $bookings->count();
  }

  public static function get_default_booking_status(){
    $default_status = OsSettingsHelper::get_settings_value('default_booking_status');
    if($default_status){
      return $default_status;
    }else{
      return LATEPOINT_BOOKING_STATUS_APPROVED;
    }
  }


  public static function change_booking_status($booking_id, $new_status){
    $booking = new OsBookingModel($booking_id);
    if(!$booking_id || !$booking) return false;
    $old_status = $booking->status;
    $old_nice_status = $booking->nice_status;

    if($new_status == $old_status){
      return true;
    }else{
      if($booking->update_status($new_status)){
        OsNotificationsHelper::process_booking_status_changed_notifications($booking, $old_nice_status);
        do_action('latepoint_booking_status_changed', $booking, $old_status);
        OsActivitiesHelper::create_activity(array('code' => 'booking_change_status', 'booking' => $booking, 'old_value' => $old_status));
        return true;
      }else{
        return false;
      }
    }
  }


	/**
	 * @param \LatePoint\Misc\BookingRequest $booking_request
	 * @param \LatePoint\Misc\BookedPeriod[]
	 * @param int $capacity
	 * @return bool
	 */
  public static function is_timeframe_in_booked_periods(\LatePoint\Misc\BookingRequest $booking_request, array $booked_periods, OsServiceModel $service): bool{
    if(empty($booked_periods)) return false;
    $count_existing_attendies = 0;
    foreach($booked_periods as $period){
      if(self::is_period_overlapping($booking_request->get_start_time_with_buffer(), $booking_request->get_end_time_with_buffer(), $period->start_time_with_buffer(), $period->end_time_with_buffer())){
        // if it's the same service overlapping - count how many times
	      // TODO maybe add an option to toggle on/off ability to share a timeslot capacity between two different services
        if($booking_request->service_id == $period->service_id){
          $count_existing_attendies+= $period->total_attendies;
        }else{
          return true;
        }
      }
    }
		if($count_existing_attendies > 0){
			// if there are attendees, check if they are below minimum need for timeslot to be blocked, if they are - then the slot is considered booked
	    if(($count_existing_attendies + $booking_request->total_attendies) <= $service->get_capacity_needed_before_slot_is_blocked()){
	      return false;
	    }else{
	      return true;
	    }
		}else{
			// no attendees in the overlapping booked periods yet, just check if the requested number of attendees is within the service capacity
			if($booking_request->total_attendies <= $service->capacity_max){
				return false;
			}else{
				return true;
			}
		}
  }


  public static function is_period_overlapping($period_one_start, $period_one_end, $period_two_start, $period_two_end){
    // https://stackoverflow.com/questions/325933/determine-whether-two-date-ranges-overlap/
    return (($period_one_start < $period_two_end) && ($period_two_start < $period_one_end));
  }

  public static function is_period_inside_another($period_one_start, $period_one_end, $period_two_start, $period_two_end){
    return (($period_one_start >= $period_two_start) && ($period_one_end <= $period_two_end));
  }

  // args = [agent_id, 'service_id', 'location_id']
  public static function get_bookings_for_date($date, $args = []){
    $bookings = new OsBookingModel();
    $args['start_date'] = $date;
    // if any of these are false or 0 - remove it from arguments list
    if(isset($args['location_id']) && empty($args['location_id'])) unset($args['location_id']);
    if(isset($args['agent_id']) && empty($args['agent_id'])) unset($args['agent_id']);
    if(isset($args['service_id']) && empty($args['service_id'])) unset($args['service_id']);
    $bookings->should_not_be_cancelled()->where($args)->order_by('start_time asc, end_time asc, service_id asc');
    return $bookings->get_results_as_models();
  }

	/**
	 * @param \LatePoint\Misc\Filter $filter
	 * @return int
	 */
  public static function count_bookings(\LatePoint\Misc\Filter $filter){
    $bookings = new OsBookingModel();
    $query_args = [];
    if($filter->date_from) $query_args['start_date'] = $filter->date_from;
    if($filter->location_id) $query_args['location_id'] = $filter->location_id;
    if($filter->agent_id) $query_args['agent_id'] = $filter->agent_id;
    if($filter->service_id) $query_args['service_id'] = $filter->service_id;

    return $bookings->should_not_be_cancelled()->where($query_args)->count();
  }


  public static function get_nice_status_name($status){
    $statuses_list = OsBookingHelper::get_statuses_list();
    if($status && isset($statuses_list[$status])){
      return $statuses_list[$status];
    }else{
      return __('Undefined Status', 'latepoint');
    }
  }

  public static function get_nice_payment_status_name($status){
    $statuses_list = OsBookingHelper::get_payment_statuses_list();
    if($status && isset($statuses_list[$status])){
      return $statuses_list[$status];
    }else{
      return __('Undefined Status', 'latepoint');
    }
  }

  public static function get_payment_statuses_list(){
    $statuses = [ LATEPOINT_PAYMENT_STATUS_NOT_PAID => __('Not Paid', 'latepoint'),
                  LATEPOINT_PAYMENT_STATUS_PARTIALLY_PAID => __('Partially Paid', 'latepoint'),
                  LATEPOINT_PAYMENT_STATUS_PROCESSING => __('Processing', 'latepoint'),
                  LATEPOINT_PAYMENT_STATUS_FULLY_PAID => __('Fully Paid', 'latepoint')];
		$statuses = apply_filters('latepoint_payment_statuses', $statuses);
		return $statuses;
  }

  public static function get_statuses_list(){
    $statuses = [ LATEPOINT_BOOKING_STATUS_APPROVED => __('Approved', 'latepoint'),
                  LATEPOINT_BOOKING_STATUS_PENDING => __('Pending Approval', 'latepoint'),
                  LATEPOINT_BOOKING_STATUS_CANCELLED => __('Cancelled', 'latepoint'),
                  LATEPOINT_BOOKING_STATUS_NO_SHOW => __('No Show', 'latepoint'),
                  LATEPOINT_BOOKING_STATUS_COMPLETED => __('Completed', 'latepoint'),
	    ];
		$additional_statuses = array_map('trim', explode(',', OsSettingsHelper::get_settings_value('additional_booking_statuses', '')));
		if(!empty($additional_statuses)){
			foreach($additional_statuses as $status){
				if(!empty($status)) $statuses[str_replace(' ', '_', strtolower($status))] = $status;
			}
		}
		$statuses = apply_filters('latepoint_booking_statuses', $statuses);
		return $statuses;
  }

  public static function get_funds_statuses_list(){
    $statuses = [ LATEPOINT_TRANSACTION_FUNDS_STATUS_CAPTURED => __('Captured', 'latepoint'),
                  LATEPOINT_TRANSACTION_FUNDS_STATUS_AUTHORIZED => __('Authorized', 'latepoint'),
                  LATEPOINT_TRANSACTION_FUNDS_STATUS_PROCESSING => __('Processing', 'latepoint'),
                  LATEPOINT_TRANSACTION_FUNDS_STATUS_REFUNDED => __('Refunded', 'latepoint')];

		$statuses = apply_filters('latepoint_funds_statuses', $statuses);
		return $statuses;
  }

  public static function get_payment_methods_select_list(){
    $payment_methods_list = [];
    $enabled_payment_methods = OsPaymentsHelper::get_enabled_payment_methods();
    foreach($enabled_payment_methods as $payment_method_code => $payment_method){
      $payment_methods_list[$payment_method_code] = $payment_method['label'];
    }
    return apply_filters('latepoint_payment_methods_for_select', $payment_methods_list);
  }

  public static function get_payment_portions_list(){
    $payment_portions = [LATEPOINT_PAYMENT_PORTION_FULL => 'Full Balance', LATEPOINT_PAYMENT_PORTION_REMAINING => 'Remaining Balance', LATEPOINT_PAYMENT_PORTION_DEPOSIT => __('Deposit', 'latepoint')];
    return $payment_portions;
  }



  public static function get_weekdays_arr($full_name = false) {
    if($full_name){
      $weekdays = array(__('Monday', 'latepoint'),
                        __('Tuesday', 'latepoint'),
                        __('Wednesday', 'latepoint'),
                        __('Thursday', 'latepoint'),
                        __('Friday', 'latepoint'),
                        __('Saturday', 'latepoint'),
                        __('Sunday', 'latepoint'));
    }else{
      $weekdays = array(__('Mon', 'latepoint'),
                        __('Tue', 'latepoint'),
                        __('Wed', 'latepoint'),
                        __('Thu', 'latepoint'),
                        __('Fri', 'latepoint'),
                        __('Sat', 'latepoint'),
                        __('Sun', 'latepoint'));
    }
    return $weekdays;
  }

  public static function get_weekday_name_by_number($weekday_number, $full_name = false) {
    $weekdays = OsBookingHelper::get_weekdays_arr($full_name);
    if(!isset($weekday_number) || $weekday_number < 1 || $weekday_number > 7) return '';
    else return $weekdays[$weekday_number - 1];
  }

  public static function get_stat($stat, $args = []){
    if(!in_array($stat, ['duration', 'price', 'bookings'])) return false;
    $defaults = [
      'customer_id' => false,
      'agent_id' => false,
      'service_id' => false,
      'location_id' => false,
      'customer_id' => false,
      'date_from' => false,
      'date_to' => false,
      'group_by' => false,
      'exclude_status' => false
    ];
    $args = array_merge($defaults, $args);
    $bookings = new OsBookingModel();
    $query_args = array($args['date_from'], $args['date_to']);
    switch($stat){
      case 'duration':
        $stat_query = 'SUM(end_time - start_time)';
      break;
      case 'price':
        $stat_query = 'sum(price)';
      break;
      case 'bookings':
        $stat_query = 'count(id)';
      break;
    }
    $select_query = $stat_query.' as stat';
    if($args['group_by']) $select_query.= ','.$args['group_by'];
    $bookings->select($select_query);


    if($args['date_from']) $bookings->where(['start_date >=' => $args['date_from']]);
    if($args['date_to']) $bookings->where(['start_date <=' => $args['date_to']]);
    if($args['service_id']) $bookings->where(['service_id' => $args['service_id']]);
    if($args['agent_id']) $bookings->where(['agent_id' => $args['agent_id']]);
    if($args['location_id']) $bookings->where(['location_id' => $args['location_id']]);
    if($args['customer_id']) $bookings->where(['customer_id' => $args['customer_id']]);
    if($args['group_by']) $bookings->group_by($args['group_by']);
    // TODO, need to support custom status exclusions
    if($args['exclude_status'] == LATEPOINT_BOOKING_STATUS_CANCELLED) $bookings->should_not_be_cancelled();

    $stat_total = $bookings->get_results(ARRAY_A);
    if($args['group_by']){
      return $stat_total;
    }else{
      return isset($stat_total[0]['stat']) ? $stat_total[0]['stat'] : 0;
    }
  }

	public static function get_new_customer_stat_for_period(DateTime $date_from, DateTime $date_to, \LatePoint\Misc\Filter $filter){
		// TODO make sure filter is respected
		$customers = new OsCustomerModel();
		return $customers->where(['created_at >=' => $date_from->format('Y-m-d'), 'created_at <=' => $date_to->format('Y-m-d')])->count();
	}

  public static function get_stat_for_period($stat, $date_from, $date_to, $group_by = false, $service_id = false, $agent_id = false, $location_id = false){
    if(!in_array($stat, ['duration', 'price', 'bookings'])) return false;
    if(!in_array($group_by, [false, 'agent_id', 'service_id', 'location_id'])) return false;
    $bookings = new OsBookingModel();
    $query_args = array($date_from, $date_to);
    switch($stat){
      case 'duration':
        $stat_query = 'SUM(end_time - start_time)';
      break;
      case 'price':
        $stat_query = 'sum(price)';
      break;
      case 'bookings':
        $stat_query = 'count(id)';
      break;
    }
    $select_query = $stat_query.' as stat';
    if($group_by) $select_query.= ','.$group_by;
    $bookings->select($select_query)->where(['start_date >=' => $date_from, 'start_date <= ' => $date_to]);

    if($service_id) $bookings->where(['service_id' => $service_id]);
    if($agent_id) $bookings->where(['agent_id' => $agent_id]);
    if($location_id) $bookings->where(['location_id' => $location_id]);

    $bookings->should_not_be_cancelled();

    if($group_by) $bookings->group_by($group_by);

    $stat_total = $bookings->get_results(ARRAY_A);
    if($group_by){
      return $stat_total;
    }else{
      return isset($stat_total[0]['stat']) ? $stat_total[0]['stat'] : 0;
    }
  }

  public static function get_total_bookings_per_day_for_period($date_from, $date_to, $service_id = false, $agent_id = false, $location_id = false){
    $bookings = new OsBookingModel();
    $query_args = array($date_from, $date_to);
    $query = 'SELECT count(id) as bookings_per_day, start_date FROM '.$bookings->table_name.' WHERE start_date >= %s AND start_date <= %s';
    if($service_id){
      $query.= ' AND service_id = %d';
      $query_args[] = $service_id;
    }
    if($agent_id){
      $query.= ' AND agent_id = %d';
      $query_args[] = $agent_id;
    }
    if($location_id){
      $query.= ' AND location_id = %d';
      $query_args[] = $location_id;
    }
    $query.= ' AND status != "'.LATEPOINT_BOOKING_STATUS_CANCELLED.'"';
    $query.= ' GROUP BY start_date';
    return $bookings->get_query_results($query, $query_args);
  }



	/**
	 * @param \LatePoint\Misc\BookedPeriod[] $daily_periods
	 * @param int $timeshift_minutes
	 * @return \LatePoint\Misc\BookedPeriod[]
	 */
  public static function apply_timeshift(array $daily_periods, int $timeshift_minutes): array{
    if(empty($timeshift_minutes)) return $daily_periods;
    $shifted_periods = [];
    // apply timeshift
    foreach($daily_periods as $day => $periods){
      $shifted_periods[$day] = [];
      // search for the periods that has to be moved to the next day after a timeshift
      if($timeshift_minutes > 0){
        $day_obj = OsWpDateTime::os_createFromFormat('Y-m-d', $day);
        $day_obj->modify('-1 day');
        if(isset($daily_periods[$day_obj->format('Y-m-d')])){
          foreach($daily_periods[$day_obj->format('Y-m-d')] as $prev_day_period){
            list($period_start, $period_end) = explode(':', $prev_day_period);
            // if period is a day off - don't shift it
            if($period_start == 0 && $period_end == 0) continue;
            $period_start = $period_start + $timeshift_minutes;
            $period_end = $period_end + $timeshift_minutes;
            // if shifted period still ends on the same day - skip to next
            if($period_end <= (24 * 60)) continue;
            // we need to capture the remaining minutes of this period in the day after shift
            $period_start = max(24 * 60, $period_start);
            $period_start = $period_start - 24 * 60;
            $period_end = $period_end - 24 * 60;

            $period_info_arr = explode(':', $prev_day_period);
            $period_info_arr[0] = $period_start;
            $period_info_arr[1] = $period_end;
            $shifted_periods[$day][] = implode(':', $period_info_arr);
          }
        }
      }
      // work on the periods that stay in the same day
      if(!empty($periods)){
        foreach($periods as $period){
          list($period_start, $period_end) = explode(':', $period);
          // if period is a day off - don't shift it
          if($period_start == 0 && $period_end == 0){
            $shifted_periods[$day][] = $period;
          }else{
            $period_start = $period_start + $timeshift_minutes;
            $period_end = $period_end + $timeshift_minutes;
            // if starts next day or ended previous day - skip
            if($period_start >= (24 * 60) || $period_end <= 0) continue;
            if($period_end > (24 * 60)) $period_end = 24 * 60;
            if($period_start < 0) $period_start = 0;

            $period_info_arr = explode(':', $period);
            $period_info_arr[0] = $period_start;
            $period_info_arr[1] = $period_end;
            $shifted_periods[$day][] = implode(':', $period_info_arr);
          }
        }
      }
      // search for the periods that has to be moved to the previous day after a timeshift
      if($timeshift_minutes < 0){
        $day_obj = OsWpDateTime::os_createFromFormat('Y-m-d', $day);
        $day_obj->modify('+1 day');
        if(isset($daily_periods[$day_obj->format('Y-m-d')])){
          foreach($daily_periods[$day_obj->format('Y-m-d')] as $next_day_period){
            list($period_start, $period_end) = explode(':', $next_day_period);
            // if period is a day off - don't shift it
            if($period_start == 0 && $period_end == 0) continue;
            $period_start = $period_start + $timeshift_minutes;
            $period_end = $period_end + $timeshift_minutes;
            // if shifted period still starts on the same day - skip to next
            if($period_start >= 0) continue;
            $period_end = min(0, $period_end);
            $period_start = $period_start + 24 * 60;
            $period_end = $period_end + 24 * 60;

            $period_info_arr = explode(':', $next_day_period);
            $period_info_arr[0] = $period_start;
            $period_info_arr[1] = $period_end;
            $shifted_periods[$day][] = implode(':', $period_info_arr);
          }
        }
      }
    }
    return $shifted_periods;
  }


  public static function get_min_max_work_periods($specific_weekdays = false, $service_id = false, $agent_id = false){
    $select_string = 'MIN(start_time) as start_time, MAX(end_time) as end_time';
    $work_periods = new OsWorkPeriodModel();
    $work_periods = $work_periods->select($select_string);
    $query_args = array('service_id' => 0, 'agent_id' => 0);
    if($service_id) $query_args['service_id'] = $service_id;
    if($agent_id) $query_args['agent_id'] = $agent_id;
    if($specific_weekdays && !empty($specific_weekdays)) $query_args['week_day'] = $specific_weekdays;
    $results = $work_periods->set_limit(1)->where($query_args)->get_results(ARRAY_A);
    if(($service_id || $agent_id) && empty($results['min_start_time'])){
      if($service_id && empty($results['min_start_time'])){
        $query_args['service_id'] = 0;
        $work_periods = new OsWorkPeriodModel();
        $work_periods = $work_periods->select($select_string);
        $results = $work_periods->set_limit(1)->where($query_args)->get_results(ARRAY_A);
      }
      if($agent_id && empty($results['min_start_time'])){
        $query_args['agent_id'] = 0;
        $work_periods = new OsWorkPeriodModel();
        $work_periods = $work_periods->select($select_string);
        $results = $work_periods->set_limit(1)->where($query_args)->get_results(ARRAY_A);
      }
    }
    if($results){
      return array($results['start_time'], $results['end_time']);
    }else{
      return false;
    }
  }




  public static function get_work_start_end_time_for_multiple_dates($dates = false, $service_id = false, $agent_id = false){
    $specific_weekdays = array();
    if($dates){
      foreach($dates as $date){
        $target_date = new OsWpDateTime($date);
        $weekday = $target_date->format('N');
        if(!in_array($weekday, $specific_weekdays)) $specific_weekdays[] = $weekday;
      }
    }
    $work_minmax_start_end = self::get_min_max_work_periods($specific_weekdays, $service_id, $agent_id);
    return $work_minmax_start_end;
  }

	/**
	 * @param int $minute
	 * @param \LatePoint\Misc\WorkPeriod[] $work_periods_arr
	 * @return bool
	 */
  public static function is_minute_in_work_periods(int $minute, array $work_periods_arr): bool{
    // print_r($work_periods_arr);
    if(empty($work_periods_arr)) return false;
    foreach($work_periods_arr as $work_period){
      // end of period does not count because we cant make appointment with 0 duration
      if($work_period->start_time <= $minute && $work_period->end_time > $minute){
        return true;
      }
    }
    return false;
  }

  public static function get_calendar_start_end_time($bookings, $work_start_minutes, $work_end_minutes){
    $calendar_start_minutes = $work_start_minutes;
    $calendar_end_minutes = $work_end_minutes;
    if($bookings){
      foreach($bookings as $bookings_for_agent){
        if($bookings_for_agent){
          foreach($bookings_for_agent as $booking){
            if($booking->start_time < $calendar_start_minutes) $calendar_start_minutes = $booking->start_time;
            if($booking->end_time > $calendar_end_minutes) $calendar_end_minutes = $booking->end_time;
          }
        }
      }
    }
    return [$calendar_start_minutes, $calendar_end_minutes];
  }




}
