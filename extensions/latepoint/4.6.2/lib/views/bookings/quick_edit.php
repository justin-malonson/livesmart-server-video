<div class="os-form-w quick-booking-form-w">
  <form action=""
    data-route-name="<?php echo ($booking->is_new_record()) ? OsRouterHelper::build_route_name('bookings', 'create') : OsRouterHelper::build_route_name('bookings', 'update'); ?>"
    class="booking-quick-edit-form">
    <div class="os-form-header">
      <?php if($booking->is_new_record()){ ?>
        <h2><?php _e('New Appointment', 'latepoint'); ?></h2>
      <?php }else{ ?>
        <h2><?php _e('Edit Appointment #', 'latepoint'); ?><?php echo $booking->id; ?></h2>
      <?php } ?>
      <a href="#" class="latepoint-side-panel-close latepoint-side-panel-close-trigger"><i class="latepoint-icon latepoint-icon-x"></i></a>
    </div>
    <div class="os-form-content">
      <?php if($services){ ?>
        <div class="os-form-group os-form-group-transparent">
          <div class="os-services-select-field-w">
            <div class="services-options-list">
              <?php if(count($services) > 7){ ?>
                <div class="service-options-filter-input-w"><input class="service-options-filter-input" type="text" placeholder="<?php _e('Start typing to filter...', 'latepoint'); ?>"></div>
              <?php } ?>
              <?php
                $service_categories = [];
                foreach($services as $service){
                  $service_categories['cat_'.$service->category_id][] = $service;
                }
                if($service_categories){
                  foreach($service_categories as $key => $service_category_services){
                    $category_id = str_replace('cat_', '', $key);
                    if($category_id == '0' || !$category_id){
                      $category_name = __('Uncategorized', 'latepoint');
                    }else{
                      $category = new OsServiceCategoryModel($category_id);
                      $category_name = ($category) ? $category->name : __('Uncategorized', 'latepoint');
                    }
                    echo '<div class="os-option-group">'.$category_name.'</div>';
                    foreach($service_category_services as $service){
                      $selected = ($booking->service_id == $service->id) ? true : false;
                      OsServiceHelper::service_option_html_for_select($service, $selected);
                    }
                  }
                }
                ?>
            </div>
            <?php if($booking->service_id){ ?>
              <div class="service-option-selected"
                data-id="<?php echo $booking->service->id; ?>"
                data-buffer-before="<?php echo $booking->service->buffer_before; ?>"
                data-buffer-after="<?php echo $booking->service->buffer_after; ?>"
                data-capacity-min="<?php echo $booking->service->capacity_min; ?>"
                data-capacity-max="<?php echo $booking->service->capacity_max; ?>"
                data-duration="<?php echo $booking->service->duration; ?>">
                  <div class="service-color" style="background-color: <?php echo $booking->service->bg_color; ?>"></div>
                  <span><?php echo $booking->service->name ?></span>
              </div>
            <?php }else{ ?>
              <div class="service-option-selected">
                <div class="service-color"></div>
                <span><?php _e('Select Service','latepoint'); ?></span>
              </div>
            <?php } ?>
          </div>
        </div>
      <?php
      }else{
        echo '<div class="latepoint-message latepoint-message-error">'.__('No Active Services Found.', 'latepoint').'</div>';
      } ?>
      <div class="os-service-durations" style="<?php echo ($booking->service_id && count($booking->service->get_all_durations_arr()) > 1) ? '' : 'display: none;'; ?>">
        <div class="os-form-group os-form-select-group os-form-group-transparent">
          <label for=""><?php _e('Duration', 'latepoint'); ?></label>
          <select class="os-form-control os-affects-duration os-affects-price" name="booking[duration]" id="">
            <?php if($booking->service_id){
                foreach($booking->service->get_all_durations_arr() as $extra_duration){
                  $selected = ($extra_duration['duration'] == $booking->duration) ? 'selected' : '';
                  echo '<option value="'.$extra_duration['duration'].'" '.$selected.'>'.$extra_duration['duration'].' '.__('minutes', 'latepoint').'</option>';
                }
              } ?>
          </select>
        </div>
      </div>
      <?php do_action('latepoint_quick_form_after_service', $booking); ?>
      <?php if(OsLocationHelper::count_locations(OsAuthHelper::get_logged_in_agent_id()) > 1){ ?>
        <div class="os-row">
          <div class="os-col-12">
            <?php echo OsFormHelper::select_field('booking[location_id]', __('Select Location', 'latepoint'), OsLocationHelper::get_locations_list(OsAuthHelper::get_logged_in_agent_id()), $booking->location_id, ['class' => 'location_id_holder']); ?>
          </div>
        </div>
        <?php
      }else{
				// single location exist in database - assign it automatically
        echo OsFormHelper::hidden_field('booking[location_id]', OsLocationHelper::get_default_location_id(OsAuthHelper::get_logged_in_agent_id()), ['class' => 'location_id_holder']);
      } ?>
      <div class="os-row">
        <div class="os-col-6">
          <div class="agent-info-w <?php echo ($booking->agent_id) ? 'selected': 'selecting'; ?>">
            <div class="agents-selector-w">
              <div class="os-form-group os-form-select-group os-form-group-transparent">
                <label for=""><?php _e('Agent', 'latepoint'); ?></label>
                <select name="booking[agent_id]" class="os-form-control agent-selector">
                  <?php foreach($agents as $agent){ ?>
                    <option value="<?php echo $agent->id; ?>" <?php if($agent->id == $booking->agent_id) echo 'selected'; ?>><?php echo join(' ', array($agent->first_name, $agent->last_name)); ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="os-col-6">
          <?php echo OsFormHelper::select_field('booking[status]', __('Status', 'latepoint'), OsBookingHelper::get_statuses_list(), $booking->status, array('placeholder' => __('Set Status', 'latepoint'))); ?>
        </div>
      </div>
      <div class="os-row">
        <div class="os-col-6">
          <?php echo OsFormHelper::text_field('booking[start_date]', __('Start Date', 'latepoint'), $booking->format_start_date(), array('class' => 'os-mask-date', 'data-route' => OsRouterHelper::build_route_name('bookings', 'quick_availability'))); ?>
        </div>
        <div class="os-col-6">
          <a href="#" data-route="<?php echo OsRouterHelper::build_route_name('bookings', 'quick_availability'); ?>" class="latepoint-btn latepoint-btn-white open-quick-availability-btn trigger-quick-availability"><i class="latepoint-icon latepoint-icon-calendar"></i><span><?php _e('Show Calendar', 'latepoint'); ?></span></a>
        </div>
      </div>
      <div class="os-row">
        <div class="os-col-12">
          <div class="ws-period os-period-transparent">
            <div class="quick-start-time-w">
              <?php echo OsFormHelper::time_field('booking[start_time]', __('Start Time', 'latepoint'), $booking->start_time, true); ?>
            </div>
            <div class="quick-end-time-w <?php if($booking->end_time && ($booking->end_time <= $booking->start_time)) echo 'ending-next-day' ?>">
              <?php echo OsFormHelper::time_field('booking[end_time]', __('End Time', 'latepoint'), $booking->end_time, true); ?>
	            <div class="plus-day-label"><?php _e('+1 day', 'latepoint'); ?></div>
            </div>
          </div>
        </div>
      </div>
      <div class="os-row">
        <div class="os-col-6">
          <?php echo OsFormHelper::text_field('booking[buffer_before]', __('Buffer Before', 'latepoint'), $booking->buffer_before); ?>
        </div>
        <div class="os-col-6">
          <?php echo OsFormHelper::text_field('booking[buffer_after]', __('Buffer After', 'latepoint'), $booking->buffer_after); ?>
        </div>
      </div>
      <div class="os-row">
        <div class="os-col-12">
          <?php echo OsFormHelper::textarea_field('booking[customer_comment]', __('Comment left by customer', 'latepoint'), $booking->customer_comment, ['rows' => 1]); ?>
        </div>
      </div>
      <div class="transactions-info-w">
        <div class="os-form-sub-header">
          <h3><?php _e('Video Session URLs', 'latepoint'); ?></h3>
        </div>
      <div class="os-row">
        <div class="os-col-6">
          <?php if($booking->livesmart_agent_url){ ?>
            <a href="<?php echo $booking->livesmart_agent_url; ?>" target="_blank" class="latepoint-btn latepoint-btn-white"><?php _e('Agent URL', 'latepoint'); ?></span></a>
          <?php } ?>
        </div>
        <div class="os-col-6">
        <?php if($booking->livesmart_visitor_url){ ?>
            <a href="<?php echo $booking->livesmart_visitor_url; ?>" target="_blank" class="latepoint-btn latepoint-btn-white"><?php _e('Visitor URL', 'latepoint'); ?></span></a>
          <?php } ?>
        </div>
      </div>
      </div>

      <?php do_action('latepoint_booking_quick_edit_form_after', $booking); ?>


      <div class="customer-info-w selected">
        <div class="os-form-sub-header">
          <h3><?php _e('Customer', 'latepoint'); ?></h3>
          <div class="os-form-sub-header-actions">
            <a href="#" class="latepoint-btn latepoint-btn-sm latepoint-btn-white customer-info-create-btn"
              data-os-output-target=".customer-quick-edit-form-w"
              data-os-action="<?php echo OsRouterHelper::build_route_name('bookings', 'customer_quick_edit_form'); ?>">
              <i class="latepoint-icon latepoint-icon-plus"></i><span><?php _e('New', 'latepoint'); ?></span>
            </a>
            <a href="#" class="latepoint-btn latepoint-btn-sm latepoint-btn-white customer-info-load-btn">
              <i class="latepoint-icon latepoint-icon-search"></i><span><?php _e('Find', 'latepoint'); ?></span>
            </a>
          </div>
        </div>
        <div class="customers-selector-w">
          <div class="customers-selector-search-w">
            <i class="latepoint-icon latepoint-icon-search"></i>
            <input type="text" data-route="<?php echo OsRouterHelper::build_route_name('customers', 'query_for_booking_form'); ?>" class="customers-selector-search-input" placeholder="<?php _e('Start typing to search...', 'latepoint'); ?>">
            <span class="customers-selector-cancel">
              <i class="latepoint-icon latepoint-icon-x"></i>
              <span><?php _e('cancel', 'latepoint'); ?></span>
            </span>
          </div>
          <?php if($customers){ ?>
            <div class="customers-options-list">
              <?php foreach($customers as $customer){ ?>
                <div class="customer-option" data-os-params="<?php echo OsUtilHelper::build_os_params(['customer_id' => $customer->id]); ?>"
                    data-os-after-call="latepoint_quick_booking_customer_selected"
                    data-os-output-target=".customer-quick-edit-form-w"
                    data-os-action="<?php echo OsRouterHelper::build_route_name('bookings', 'customer_quick_edit_form'); ?>">
                  <div class="customer-option-avatar" style="background-image: url(<?php echo OsCustomerHelper::get_avatar_url($customer); ?>)"></div>
                  <div class="customer-option-info">
                    <h4 class="customer-option-info-name"><span><?php echo $customer->full_name; ?></span></h4>
                    <ul>
                      <li>
                        <?php _e('Email: ','latepoint'); ?>
                        <strong><?php echo $customer->email; ?></strong>
                      </li>
                      <li>
                        <?php _e('Phone: ','latepoint'); ?>
                        <strong><?php echo $customer->formatted_phone; ?></strong>
                      </li>
                    </ul>
                  </div>
                </div>
              <?php } ?>
            </div>
          <?php } ?>
        </div>
        <div class="customer-quick-edit-form-w">
          <?php require('customer_quick_edit_form.php'); ?>
        </div>
      </div>
      <div>
        <div class="os-form-sub-header">
          <h3><?php _e('Price Breakdown', 'latepoint'); ?></h3>
          <div class="os-form-sub-header-actions">
            <a href="#" class="latepoint-btn latepoint-btn-sm latepoint-btn-white reload-price-breakdown"
              data-route="<?php echo OsRouterHelper::build_route_name('bookings', 'reload_price_breakdown') ?>">
              <i class="latepoint-icon latepoint-icon-refresh-cw"></i>
	            <span><?php _e('Recalculate', 'latepoint'); ?></span>
            </a>
          </div>
        </div>
	      <div class="price-breakdown-wrapper">
		      <?php include '_price_breakdown.php'; ?>
	      </div>
      </div>
			<div class="balance-payment-wrapper">
		    <?php include '_balance_and_payments.php'; ?>
			</div>

      <div class="transactions-info-w">
        <div class="os-form-sub-header">
          <h3><?php _e('Transactions', 'latepoint'); ?></h3>
        </div>
        <div class="quick-transactions-list-w">
        <?php
          if($transactions){
            foreach ($transactions as $transaction):
              include '_transaction_box.php';
            endforeach;
				  }
        ?>
        </div>
        <div class="quick-add-transaction trigger-add-transaction-btn"
             data-os-after-call="latepoint_init_quick_transaction_form"
             data-os-before-after="before"
             data-os-action="<?php echo OsRouterHelper::build_route_name('transactions', 'edit_form'); ?>">
          <i class="latepoint-icon latepoint-icon-plus2"></i>
          <span><?php _e('Add Transaction', 'latepoint'); ?></span>
        </div>
      </div>
      <?php do_action('latepoint_booking_quick_form_after', $booking); ?>
    </div>
    <div class="os-form-buttons os-quick-form-buttons">
      <?php if($booking->is_new_record()) { ?>
        <button type="submit" class="latepoint-btn latepoint-btn-block latepoint-btn-lg"><?php _e('Create Appointment', 'latepoint'); ?></button>
      <?php }else{ ?>
        <div class="os-full">
          <button type="submit" class="latepoint-btn latepoint-btn-block latepoint-btn-lg"><?php _e('Save Changes', 'latepoint'); ?></button>
        </div>
        <div class="os-compact">
          <a href="#"
              data-os-success-action="reload"
              data-os-action="<?php echo OsRouterHelper::build_route_name('bookings', 'delete'); ?>"
              data-os-params="<?php echo OsUtilHelper::build_os_params(['booking_id' => $booking->id]); ?>"
              data-os-prompt="<?php _e('Are you sure you want to delete this appointment?', 'latepoint'); ?>"
              class="latepoint-delete-appointment latepoint-btn latepoint-btn-secondary latepoint-btn-lg latepoint-btn-just-icon"
              title="<?php _e('Delete Appointment', 'latepoint'); ?>">
                <i class="latepoint-icon latepoint-icon-trash-2"></i>
          </a>
        </div>
      <?php } ?>
    </div>
    <?php
    echo OsFormHelper::hidden_field('booking[id]', $booking->id);
    echo OsFormHelper::hidden_field('booking[service_id]', $booking->service_id, ['class' => 'os-affects-price']);
    echo OsFormHelper::hidden_field('nonse', OsUtilHelper::create_nonce( $booking->id ));
    ?>
  </form>
</div>
