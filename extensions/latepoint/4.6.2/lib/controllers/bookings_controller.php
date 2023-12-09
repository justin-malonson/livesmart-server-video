<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsBookingsController' ) ) :


  class OsBookingsController extends OsController {

    private $booking;

    function __construct(){
      parent::__construct();

      $this->action_access['public'] = array_merge($this->action_access['public'], ['request_cancellation' ,'print_booking_info','ical_download', 'continue_booking_intent']);
			$this->action_access['customer'] = array_merge($this->action_access['customer'], ['view_summary_in_lightbox']);

      $this->views_folder = LATEPOINT_VIEWS_ABSPATH . 'bookings/';
      $this->vars['page_header'] = OsMenuHelper::get_menu_items_by_id('appointments');
      $this->vars['breadcrumbs'][] = array('label' => __('Appointments', 'latepoint'), 'link' => OsRouterHelper::build_link(OsRouterHelper::build_route_name('bookings', 'pending_approval') ) );

      

    }

		public function view_summary_in_lightbox(){
			$booking = new OsBookingModel($this->params['booking_id']);
			$this->vars['booking'] = $booking;
			$this->vars['price_breakdown_rows'] = OsBookingHelper::generate_price_breakdown_rows($booking);
      $this->format_render(__FUNCTION__);
		}

    public function continue_booking_intent(){
      $booking_intent_key = $this->params['booking_intent_key'];
      OsBookingIntentHelper::convert_intent_to_booking($booking_intent_key);

      $booking_intent = new OsBookingIntentModel();
      $booking_intent = $booking_intent->where(['intent_key' => $booking_intent_key])->set_limit(1)->get_results_as_models();

      if($booking_intent){
        wp_redirect($booking_intent->page_url_with_intent);
      }
    }

    public function grouped_bookings_quick_view(){
      if(!isset($this->params['booking_id'])) return false;

      $booking = new OsBookingModel($this->params['booking_id']);
      $this->vars['booking'] = $booking;

      $group_bookings = new OsBookingModel();
      $group_bookings = $group_bookings->where(['start_time' => $booking->start_time,
                              'start_date' => $booking->start_date,
                              'service_id' => $booking->service_id,
                              'location_id' => $booking->location_id,
                              'agent_id' => $booking->agent_id])->should_not_be_cancelled()->get_results_as_models();
      $total_attendies = 0;
      if($group_bookings){
        foreach($group_bookings as $group_booking){
          $total_attendies = $total_attendies + $group_booking->total_attendies;
        }
      }
      $this->vars['total_attendies'] = $total_attendies;
      $this->vars['group_bookings'] = $group_bookings;
      $this->format_render(__FUNCTION__);
    }

    public function pending_approval(){
      $this->vars['breadcrumbs'][] = array('label' => __('Pending Appointments', 'latepoint'), 'link' => false );

      $page_number = isset($this->params['page_number']) ? $this->params['page_number'] : 1;
      $per_page = 20;
      $offset = ($page_number > 1) ? (($page_number - 1) * $per_page) : 0;

      $bookings = new OsBookingModel();
      $query_args = ['status' => apply_filters('latepoint_pending_booking_statuses', [LATEPOINT_BOOKING_STATUS_PENDING, LATEPOINT_BOOKING_STATUS_PAYMENT_PENDING])];

      if($this->logged_in_agent_id) $query_args['agent_id'] = $this->logged_in_agent_id;
      $this->vars['bookings'] = $bookings->where($query_args)->set_limit($per_page)->set_offset($offset)->order_by('id desc')->get_results_as_models();

      $count_bookings = new OsBookingModel();
      $total_bookings = $count_bookings->where($query_args)->count();
      $total_pages = ceil($total_bookings / $per_page);

      $this->vars['total_pages'] = $total_pages;
      $this->vars['total_bookings'] = $total_bookings;
      $this->vars['per_page'] = $per_page;
      $this->vars['current_page_number'] = $page_number;

      $this->vars['showing_from'] = (($page_number - 1) * $per_page) ? (($page_number - 1) * $per_page) : 1;
      $this->vars['showing_to'] = min($page_number * $per_page, $this->vars['total_bookings']);

      $this->format_render(__FUNCTION__);
    }

    public function customize_table(){
      $this->vars['selected_columns'] = OsSettingsHelper::get_selected_columns_for_bookings_table();
      $this->vars['available_columns'] = OsSettingsHelper::get_available_columns_for_bookings_table();

      $this->format_render(__FUNCTION__);
    }

    public function index(){
      
      $this->vars['breadcrumbs'][] = array('label' => __('All', 'latepoint'), 'link' => false );

      $page_number = isset($this->params['page_number']) ? $this->params['page_number'] : 1;
      $per_page = 20;
      $offset = ($page_number > 1) ? (($page_number - 1) * $per_page) : 0;


      $bookings = new OsBookingModel();
      $query_args = [];

			$selected_columns = OsSettingsHelper::get_selected_columns_for_bookings_table();
			$available_columns = OsSettingsHelper::get_available_columns_for_bookings_table();

      $filter = isset($this->params['filter']) ? $this->params['filter'] : false;

      // TABLE SEARCH FILTERS
      if($filter){
        if(isset($filter['service_id']) && !empty($filter['service_id'])) $query_args['service_id'] = $filter['service_id'];
        if(isset($filter['agent_id']) && !empty($filter['agent_id'])) $query_args['agent_id'] = $filter['agent_id'];
        if(isset($filter['location_id']) && !empty($filter['location_id'])) $query_args['location_id'] = $filter['location_id'];
        if(isset($filter['status']) && !empty($filter['status'])) $query_args[LATEPOINT_TABLE_BOOKINGS.'.status'] = $filter['status'];
        if(isset($filter['payment_status']) && !empty($filter['payment_status'])) $query_args[LATEPOINT_TABLE_BOOKINGS.'.payment_status'] = $filter['payment_status'];
        if(isset($filter['id']) && !empty($filter['id'])) $query_args[LATEPOINT_TABLE_BOOKINGS.'.id'] = $filter['id'];
        if(isset($filter['created_date_from']) && !empty($filter['created_date_from'])){
          $query_args[LATEPOINT_TABLE_BOOKINGS.'.created_at >='] = $filter['created_date_from'].' 00:00:00';
          $query_args[LATEPOINT_TABLE_BOOKINGS.'.created_at <='] = $filter['created_date_from'].' 23:59:59';
        }
        if(isset($filter['booking_date_from']) && isset($filter['booking_date_to']) && !empty($filter['booking_date_from']) && !empty($filter['booking_date_to'])){
          $query_args[LATEPOINT_TABLE_BOOKINGS.'.start_date >='] = $filter['booking_date_from'];
          $query_args[LATEPOINT_TABLE_BOOKINGS.'.start_date <='] = $filter['booking_date_to'];
        }
        if(isset($filter['customer']) && !empty($filter['customer'])){
          $bookings->select(LATEPOINT_TABLE_BOOKINGS.'.*, '.LATEPOINT_TABLE_CUSTOMERS.'.first_name, '.LATEPOINT_TABLE_CUSTOMERS.'.last_name');
          $bookings->join(LATEPOINT_TABLE_CUSTOMERS, [LATEPOINT_TABLE_CUSTOMERS.'.id' => 'customer_id']);
          $query_args['CONCAT('.LATEPOINT_TABLE_CUSTOMERS.'.first_name, " " ,'.LATEPOINT_TABLE_CUSTOMERS.'.last_name) LIKE'] = '%'.$filter['customer']['full_name'].'%';
          $this->vars['customer_name_query'] = $filter['customer']['full_name'];
					if(isset($selected_columns['customer'])){
	          foreach($selected_columns['customer'] as $customer_column_key){
	            if(isset($available_columns['customer'][$customer_column_key]) && isset($filter['customer'][$customer_column_key]) && !empty($filter['customer'][$customer_column_key])) $query_args[LATEPOINT_TABLE_CUSTOMERS.'.'.$customer_column_key.' LIKE'] = '%'.$filter['customer'][$customer_column_key].'%';
	          }
		      }
        }
				// filters for custom selected columns, only related to booking fields
	      if(isset($selected_columns['booking'])){
          foreach($selected_columns['booking'] as $booking_column_key){
            if(isset($available_columns['booking'][$booking_column_key]) && isset($filter[$booking_column_key]) && !empty($filter[$booking_column_key])) $query_args[$booking_column_key.' LIKE'] = '%'.$filter[$booking_column_key].'%';
          }
	      }
      }

      if($this->logged_in_agent_id){
        $query_args['agent_id'] = $this->logged_in_agent_id;
        $this->vars['show_single_agent'] = $this->logged_in_agent;
      }else{
        $this->vars['show_single_agent'] = false;
      }

      $this->vars['locations_list'] = OsLocationHelper::get_locations_list($this->logged_in_agent_id);
      $this->vars['services_list'] = OsServiceHelper::get_services_list();

      $this->vars['selected_columns'] = $selected_columns;
      $this->vars['available_columns'] = $available_columns;

			$order_by = ['column' => 'id', 'direction' => 'desc'];
			$this->vars['order_by'] = $order_by;

      // OUTPUT CSV IF REQUESTED
      if(isset($this->params['download']) && $this->params['download'] == 'csv'){
        $csv_filename = 'all_bookings_'.OsUtilHelper::random_text().'.csv';
        
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename={$csv_filename}");

        $labels_row = [  __('ID', 'latepoint'), 
                              __('Service', 'latepoint'), 
                              __('Start Date & Time', 'latepoint'), 
                              __('Duration', 'latepoint'), 
                              __('Customer', 'latepoint'), 
                              __('Customer Phone', 'latepoint'), 
                              __('Customer Email', 'latepoint'), 
                              __('Agent', 'latepoint'), 
                              __('Agent Phone', 'latepoint'), 
                              __('Agent Email', 'latepoint'), 
                              __('Status', 'latepoint'), 
                              __('Price', 'latepoint'),
                              __('Booked On', 'latepoint') ];


        $bookings_data = [];
        $bookings_data[] = $labels_row;


        $bookings_arr = $bookings->where($query_args)->order_by($order_by['column'].' '.$order_by['direction'])->get_results_as_models();

        if($bookings_arr){
          foreach($bookings_arr as $booking){
            $values_row = [  $booking->id, 
                                  $booking->service->name, 
                                  $booking->nice_start_datetime,
                                  $booking->get_total_duration(), 
                                  $booking->customer->full_name, 
                                  $booking->customer->phone, 
                                  $booking->customer->email, 
                                  $booking->agent->full_name, 
                                  $booking->agent->phone, 
                                  $booking->agent->email, 
                                  $booking->nice_status, 
                                  $booking->formatted_price,
                                  $booking->nice_created_at];
            $values_row = apply_filters('latepoint_booking_row_for_csv_export', $values_row, $booking, $this->params);
            $bookings_data[] = $values_row;
          }

        }

        $bookings_data = apply_filters('latepoint_bookings_data_for_csv_export', $bookings_data, $this->params);
        OsCSVHelper::array_to_csv($bookings_data);
        return;
      }

      $this->vars['bookings'] = $bookings->where($query_args)->set_limit($per_page)->set_offset($offset)->order_by($order_by['column'].' '.$order_by['direction'])->get_results_as_models();

      $count_total_bookings = new OsBookingModel();
      if($filter && $filter['customer']){
        $count_total_bookings->join(LATEPOINT_TABLE_CUSTOMERS, [LATEPOINT_TABLE_CUSTOMERS.'.id' => 'customer_id']);
      }
      $total_bookings = $count_total_bookings->where($query_args)->count();
      $this->vars['total_bookings'] = $total_bookings;
      $total_pages = ceil($total_bookings / $per_page);

      $this->vars['total_pages'] = $total_pages;
      $this->vars['per_page'] = $per_page;
      $this->vars['current_page_number'] = $page_number;
      
      $this->vars['showing_from'] = (($page_number - 1) * $per_page) ? (($page_number - 1) * $per_page) : 1;
      $this->vars['showing_to'] = min($page_number * $per_page, $this->vars['total_bookings']);

      $this->format_render(['json_view_name' => '_table_body', 'html_view_name' => __FUNCTION__], [], ['total_pages' => $total_pages, 'showing_from' => $this->vars['showing_from'], 'showing_to' => $this->vars['showing_to'], 'total_records' => $total_bookings]);
    }

    function quick_availability(){

      $booking = new OsBookingModel();
      $this->params['booking']['start_date'] = OsTimeHelper::reformat_date_string($this->params['booking']['start_date'], OsSettingsHelper::get_date_format(), 'Y-m-d');
      $booking->set_data($this->params['booking']);

      $calendar_start_date = isset($this->params['start_date']) ? new OsWpDateTime($this->params['start_date']) : new OsWpDateTime($booking->start_date);
      // show one more day before so the current selection does not look weird
      if(isset($this->params['previous_days'])){
        $calendar_end_date = clone $calendar_start_date;
        $calendar_start_date->modify('-30 days');
      }else{
        if(!isset($this->params['show_days_only'])) $calendar_start_date->modify('-1 day');
        $calendar_end_date = clone $calendar_start_date;
				$calendar_end_date->modify('+30 days');
      }
      
      if($this->logged_in_agent_id) $booking->agent_id = $this->logged_in_agent_id;

      $work_periods = OsWorkPeriodsHelper::get_work_periods(new \LatePoint\Misc\Filter(['date_from' => $calendar_start_date->format('Y-m-d'), 'date_to' => $calendar_end_date->format('Y-m-d'), 'service_id' => $booking->service_id, 'agent_id' => $booking->agent_id, 'location_id' => $booking->location_id]));
      $work_start_end = OsWorkPeriodsHelper::get_work_start_end_time($work_periods);

			$booking_request = \LatePoint\Misc\BookingRequest::create_from_booking_model($booking);
			$settings = [];
			$settings['allow_full_access'] = true;
			if(!$booking->is_new_record()) $settings['exclude_booking_ids'] = [$booking->id];
			$resources = OsResourceHelper::get_resources_grouped_by_day($booking_request, $calendar_start_date, $calendar_end_date, $settings);
			$work_boundaries = OsResourceHelper::get_work_boundaries_for_groups_of_resources($resources);

      $this->vars['booking'] = $booking;
      $this->vars['work_boundaries'] = $work_boundaries;
      $this->vars['show_days_only'] = isset($this->params['show_days_only']) ? true : false;
      
      $this->vars['timeblock_interval'] = $booking->service->get_timeblock_interval();
      $this->vars['calendar_start_date'] = $calendar_start_date;
      $this->vars['calendar_end_date'] = $calendar_end_date;
      $this->vars['booking_request'] = $booking_request;
      $this->vars['resources'] = $resources;

      $agents = new OsAgentModel();
      if($this->logged_in_agent_id) $agents->where(['id' => $this->logged_in_agent_id]);
      $this->vars['agents'] = $agents->get_results_as_models();

      $this->format_render(__FUNCTION__);
    }


		// reloads a section of a quick edit form that has a price breakdown
		public function reload_price_breakdown(){
      $booking = new OsBookingModel();
      $booking->set_data($this->params['booking']);

			$booking->subtotal = $booking->full_amount_to_charge(false, false);
			$booking->price = $booking->full_amount_to_charge();
			$booking = apply_filters('latepoint_booking_reload_price_breakdown', $booking);

			$this->vars['price_breakdown_rows'] = OsBookingHelper::generate_price_breakdown_rows($booking, true, ['balance', 'payments']);

			$this->vars['booking'] = $booking;
      $this->format_render(__FUNCTION__);
		}


		// handy endpoint to get a full charge amount for a booking
    function calculate_full_price(){
      $booking = new OsBookingModel();
      $booking->set_data($this->params['booking']);
      if($this->get_return_format() == 'json'){
        $coupon_discount = $booking->full_amount_to_charge(false, false) - $booking->full_amount_to_charge(true, false);
        $this->send_json(['status' => LATEPOINT_STATUS_SUCCESS, 'message' => OsMoneyHelper::to_money_field_format($booking->full_amount_to_charge()), 'coupon_discount' => OsMoneyHelper::to_money_field_format($coupon_discount)]);
      }
    }

    function request_cancellation(){
      $booking_id = $this->params['id'];
      $booking = new OsBookingModel($booking_id);
      if(OsAuthHelper::get_logged_in_customer_id() == $booking->customer_id){
        $this->params['status'] = LATEPOINT_BOOKING_STATUS_CANCELLED;
        $this->change_status();
      }else{
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = __('Error! JSf29834', 'latepoint');
      }
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }

    function change_status(){
      $booking_id = $this->params['id'];
      $new_status = $this->params['status'];
      $booking = new OsBookingModel($booking_id);
      if((OsAuthHelper::get_highest_current_user_type() == 'agent') && ($booking->agent_id != OsAuthHelper::get_logged_in_agent_id())) exit;
      if((OsAuthHelper::get_highest_current_user_type() == 'customer') && ($booking->customer_id != OsAuthHelper::get_logged_in_customer_id())) exit;
      $old_booking = clone $booking;
      if($new_status == $old_booking->status){
        $response_html = __('Appointment Status Updated', 'latepoint');
        $status = LATEPOINT_STATUS_SUCCESS;
      }else{
        if($booking->update_status($new_status)){
          $response_html = __('Appointment Status Updated', 'latepoint');
          $status = LATEPOINT_STATUS_SUCCESS;
          OsNotificationsHelper::process_booking_status_changed_notifications($booking, $old_booking->nice_status);
          do_action('latepoint_booking_status_changed', $booking, $old_booking->status);
          do_action('latepoint_booking_updated_frontend', $booking);
          OsActivitiesHelper::create_activity(array('code' => 'booking_change_status', 'booking' => $booking, 'old_value' => $old_booking->status));
        }else{
          $response_html = $booking->get_error_messages();
          $status = LATEPOINT_STATUS_ERROR;
        }
      }

      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }

    function print_booking_info(){
      $booking_id = $this->params['latepoint_booking_id'];
      if($booking_id){
        $booking = new OsBookingModel($booking_id);
        if($booking->id && OsAuthHelper::is_customer_logged_in() && ($booking->customer_id == OsAuthHelper::get_logged_in_customer_id())){
          $customer = $booking->customer;
          $default_fields_for_customer = OsSettingsHelper::get_default_fields_for_customer();
					$price_breakdown_rows = OsBookingHelper::generate_price_breakdown_rows($booking);
          ?>
          <!DOCTYPE html>
          <html lang="en">
          <head>
            <meta charset="UTF-8">
            <title></title>
            <link rel="stylesheet" href="<?php echo LATEPOINT_STYLESHEETS_URL . 'front.css' ?>">
          </head>
          <body>
            <div class="latepoint-w">
              <div class="latepoint-print-confirmation-w">
                <?php do_action('latepoint_step_confirmation_before', $booking); ?>
                <div class="confirmation-head-info">
                  <?php do_action('latepoint_step_confirmation_head_info_before', $booking); ?>
                  <div class="confirmation-number"><?php _e('Confirmation #', 'latepoint'); ?> <strong><?php echo $booking->booking_code; ?></strong></div>
                  <?php do_action('latepoint_step_confirmation_head_info_after', $booking); ?>
                </div>
                <div class="confirmation-info-w">
	                <?php include(LATEPOINT_VIEWS_ABSPATH.'steps/partials/_booking_summary.php'); ?>
                </div>
              </div>
            </div>
            <script type="text/javascript">window.onload = function() { window.print(); }</script>
          </body>
          </html>
          <?php
        }
      }
    }

    function ical_download(){
      $booking_id = $this->params['latepoint_booking_id'];
      if($booking_id){
        $booking = new OsBookingModel($booking_id);
        if($booking->id && OsAuthHelper::is_customer_logged_in() && ($booking->customer_id == OsAuthHelper::get_logged_in_customer_id())){

          header('Content-Type: text/calendar; charset=utf-8');
          header('Content-Disposition: attachment; filename=booking_'.$booking->id.'.ics');

          echo OsBookingHelper::generate_ical_event_string($booking);
        }
      }
    }

		// Create/Update booking from quick form in admin
		public function create_or_update(){
			// input fields are formatted in customer preferred format, we need to convert that to database format Y-m-d
      $this->params['booking']['start_date'] = OsTimeHelper::reformat_date_string($this->params['booking']['start_date'], OsSettingsHelper::get_date_format(), 'Y-m-d');

			// get data from params
      $booking_params = $this->params['booking'];
      $customer_params = $this->params['customer'];
			$form_values_to_update = [];

      $booking = new OsBookingModel($booking_params['id']);

			// if we are updating a booking - save a copy by cloning old booking
      $old_booking = ($booking->is_new_record()) ? false : clone $booking;
      $booking->set_data($booking_params);

			// Because price is not in allowed_params to bulk set, check if it's passed in params and set it, OTHERWISE CALCULATE IT
			if(isset($booking_params['price'])) $booking->price = OsParamsHelper::sanitize_param($booking_params['price'], 'money');
			if(isset($booking_params['subtotal'])) $booking->subtotal = OsParamsHelper::sanitize_param($booking_params['subtotal'], 'money');

			// set custom end time/date if it was passed in params
			if(isset($booking_params['end_time']['formatted_value'])){
				$booking->set_custom_end_time_and_date($booking_params);
			}
      // Customer update/create
      if($booking->customer_id){
        $customer = new OsCustomerModel($booking->customer_id);
        $is_new_customer = false;
      }else{
        $customer = new OsCustomerModel();
        $is_new_customer = true;
      }
      $customer->set_data($customer_params);
      if($customer->save()){
        if($is_new_customer){
					do_action('latepoint_customer_created', $customer);
          OsNotificationsHelper::process_new_customer_notifications($customer);
          OsActivitiesHelper::create_activity(array('code' => 'customer_create', 'customer_id' => $customer->id));
        }else{
					do_action('latepoint_customer_updated', $customer);
        }

        $booking->customer_id = $customer->id;
				// update customer ID on the form that called it
        $form_values_to_update['booking[customer_id]'] = $booking->customer_id;
				$booking->set_utc_datetimes();
        $lsv_agent_url = wp_generate_password(8, false);
        $lsv_visitor_url = wp_generate_password(8, false);
        $lsv_url = get_option('livesmart_server_url');
       $booking->customer_comment = 'Meeting agent URL: &#13;&#10;' . $lsv_url . $lsv_agent_url . '&#13;&#10;Meeting visitor URL: &#13;&#10;' . $lsv_url . $lsv_visitor_url.'<a href="'.$lsv_url . $lsv_visitor_url.'">Join</a>';
       $booking->livesmart_agent_url = $lsv_url . $lsv_agent_url;
       $booking->livesmart_visitor_url = $lsv_url . $lsv_visitor_url;
        if($booking->save()){
					// save transactions
	        if(!empty($this->params['transactions'])){
						foreach($this->params['transactions'] as $transaction_params){
							if(!empty($transaction_params['id']) && filter_var($transaction_params['id'], FILTER_VALIDATE_INT)){
								// update existing transaction
								$transaction = new OsTransactionModel($transaction_params['id']);
								$is_new_transaction = false;
							}else{
								// new transaction
								$transaction = new OsTransactionModel();
								$is_new_transaction = true;
							}
							unset($transaction_params['id']);
							$transaction->set_data($transaction_params);
							$transaction->booking_id = $booking->id;
							$transaction->customer_id = $customer->id;
							$transaction->status = LATEPOINT_TRANSACTION_STATUS_APPROVED;
							$transaction->save();
							if($is_new_transaction){
								do_action('latepoint_transaction_created', $transaction);
							}
						}
	        }

					// save price breakdown
					if(!empty($this->params['price_breakdown'])){
						$price_breakdown_rows = [];
						$allowed_keys = ['before_subtotal', 'after_subtotal'];
						foreach($allowed_keys as $key){
							foreach($this->params['price_breakdown'][$key] as $row){
								if(!empty($row['items'])){
									$group = ['heading' => '', 'items' => []];
									if(!empty($row['heading'])) $group['heading'] = $row['heading'];
									foreach($row['items'] as $item){
										if(isset($item['value'])) $item['raw_value'] = OsMoneyHelper::convert_amount_from_money_input_to_db_format($item['value']);
										$group['items'][] = $item;
									}
									$price_breakdown_rows[$key][] = $group;
								}else{
									if(isset($row['value'])) $row['raw_value'] = OsMoneyHelper::convert_amount_from_money_input_to_db_format($row['value']);
									$price_breakdown_rows[$key][] = $row;
								}
							}
						}
						$booking->save_meta_by_key('price_breakdown', json_encode($price_breakdown_rows));
					}
					if($old_booking){
						// booking was updated
	          do_action('latepoint_booking_updated_admin', $booking, $old_booking);
	          if($old_booking->status != $booking->status){
							// if status has changed - process notifications and fire action hooks
	            OsNotificationsHelper::process_booking_status_changed_notifications($booking, $old_booking->nice_status);
	            do_action('latepoint_booking_status_changed', $booking, $old_booking->status);
	          }
	          $response_html = __('Appointment Updated: ID#', 'latepoint') . $booking->id;
	          OsActivitiesHelper::create_activity(array('code' => 'booking_update', 'booking' => $booking, 'old_booking' => $booking));
					}else{
						// new booking
	          do_action('latepoint_booking_created_admin', $booking);
	          OsNotificationsHelper::process_new_booking_notifications($booking);

	          $response_html = __('Appointment Added: ID#', 'latepoint') . $booking->id;
	          OsActivitiesHelper::create_activity(array('code' => 'booking_create', 'booking' => $booking));
		        OsLiveSmartHelper::insertRoom($booking, $lsv_agent_url, $lsv_visitor_url);
						// update booking ID on the form that called it
	          $form_values_to_update['booking[id]'] = $booking->id;
					}
          $status = LATEPOINT_STATUS_SUCCESS;
        }else{
          $response_html = $booking->get_error_messages();
          $status = LATEPOINT_STATUS_ERROR;
        }
      }else{
        // error customer validation/saving
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = $customer->get_error_messages();
        if(is_array($response_html)) $response_html = implode(', ', $response_html);
      }
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html, 'form_values_to_update' => $form_values_to_update));
      }

		}


    /*
      Update booking (used in admin on quick side form save)
    */

    public function update(){
			$this->create_or_update();
    }


    /*
      Create booking (used in admin on quick side form save)
    */

    public function create(){
			$this->create_or_update();
    }

    public function delete(){
      if(!isset($this->params['booking_id']) || empty($this->params['booking_id']) || !is_numeric($this->params['booking_id'])) return false;
      $booking_id_to_delete = $this->params['booking_id'];
      $booking = new OsBookingModel($booking_id_to_delete);
      if(!$booking->is_new_record()){
        // check permissions
        if(OsAuthHelper::is_admin_logged_in() || (OsAuthHelper::get_logged_in_agent_id() == $booking->agent_id)){
          do_action('latepoint_booking_will_be_deleted', $booking_id_to_delete);
          $transactions = new OsTransactionModel();
          $transactions->delete_where(['booking_id' => $booking_id_to_delete]);
          $booking_metas = new OsBookingMetaModel();
          $booking_metas->delete_where(['object_id' => $booking_id_to_delete]);
          $booking->delete();
          do_action('latepoint_booking_deleted', $booking_id_to_delete);
          $status = LATEPOINT_STATUS_SUCCESS;
          $response_html = __('Appointment has been deleted', 'latepoint');
        }else{
          $status = LATEPOINT_STATUS_ERROR;
          $response_html = __('Not Allowed', 'latepoint');
        }
      }else{
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = __('Invalid Data', 'latepoint');
      }
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }

		function reload_balance_and_payments(){
			$booking_params = $this->params['booking'];

      $booking = new OsBookingModel();
      $booking->set_data($booking_params);


			// Because price is not in allowed_params to bulk set, check if it's passed in params and set it, OTHERWISE CALCULATE IT
			if(isset($booking_params['price'])) $booking->price = OsParamsHelper::sanitize_param($booking_params['price'], 'money');
			if(isset($booking_params['subtotal'])) $booking->subtotal = OsParamsHelper::sanitize_param($booking_params['subtotal'], 'money');


			$this->vars['booking'] = $booking;
      $this->format_render(__FUNCTION__)  ;
		}



    function customer_quick_edit_form(){
      $selected_customer = new OsCustomerModel();
      if(isset($this->params['customer_id'])){
        $selected_customer->load_by_id($this->params['customer_id']);
      }
      $this->vars['default_fields_for_customer'] = OsSettingsHelper::get_default_fields_for_customer();
      $this->vars['selected_customer'] = $selected_customer;
      $this->format_render(__FUNCTION__);
    }

		function quick_edit(){
      $agents = new OsAgentModel();
      if($this->logged_in_agent_id) $agents->where(['id' => $this->logged_in_agent_id]);
      $agents_arr = $agents->get_results();
      $this->vars['agents'] = $agents_arr;

      $customers = new OsCustomerModel();

			// only load customers that belong to logged in agent, if any
      if($this->logged_in_agent_id) $customers->select(LATEPOINT_TABLE_CUSTOMERS.'.*')->join(LATEPOINT_TABLE_BOOKINGS, ['customer_id' => LATEPOINT_TABLE_CUSTOMERS.'.id'])->group_by(LATEPOINT_TABLE_CUSTOMERS.'.id')->where(['agent_id' => $this->logged_in_agent_id]);

      $customers_arr = $customers->order_by('first_name asc, last_name asc')->set_limit(20)->get_results_as_models();
      $this->vars['customers'] = $customers_arr;

			if(!empty($this->params['id'])){
				// EDITING EXISTING BOOKING
	      $booking = new OsBookingModel($this->params['id']);
	      $transactions_model = new OsTransactionModel();
	      $transactions = $transactions_model->where(['booking_id' => $this->params['id']])->get_results_as_models();

			}else{
				// CREATING NEW BOOKING
				$booking = new OsBookingModel();

				// LOAD FROM PASSED PARAMS
	      $booking->agent_id = isset($this->params['agent_id']) ? $this->params['agent_id'] : '';
	      $booking->service_id = isset($this->params['service_id']) ? $this->params['service_id'] : '';
	      $booking->customer_id = isset($this->params['customer_id']) ? $this->params['customer_id'] : '';
	      $booking->location_id = isset($this->params['location_id']) ? $this->params['location_id'] : OsLocationHelper::get_default_location_id($this->logged_in_agent_id);
	      $booking->start_date = isset($this->params['start_date']) ? $this->params['start_date'] : OsTimeHelper::today_date('Y-m-d');
	      $booking->start_time = isset($this->params['start_time']) ? $this->params['start_time'] : 600;
	      $booking->end_time = ($booking->service_id) ? $booking->calculate_end_time() : $booking->start_time + 60;
	      $booking->end_date = $booking->calculate_end_date();
	      $booking->buffer_before = $booking->service->buffer_before;
	      $booking->buffer_after = $booking->service->buffer_after;
	      $booking->status = LATEPOINT_BOOKING_STATUS_APPROVED;

				$transactions = [];
			}

			$this->vars['price_breakdown_rows'] = OsBookingHelper::generate_price_breakdown_rows($booking);

      $service_categories = new OsServiceCategoryModel();
      $this->vars['service_categories'] = $service_categories->get_results_as_models();
      $this->vars['services'] = OsServiceHelper::get_services($this->logged_in_agent_id);;

      $services = new OsServiceModel();
      $this->vars['uncategorized_services'] = $services->where(array('category_id' => ['OR' => [0, 'IS NULL']]))->order_by('order_number asc')->get_results_as_models();

			$booking = apply_filters('latepoint_prepare_booking_for_quick_view', $booking);

      $this->vars['selected_customer'] = new OsCustomerModel($booking->customer_id);
      $this->vars['booking'] = $booking;
      $this->vars['transactions'] = $transactions;
      $this->vars['default_fields_for_customer'] = OsSettingsHelper::get_default_fields_for_customer();
      $this->format_render(__FUNCTION__);
		}

  }

endif;
