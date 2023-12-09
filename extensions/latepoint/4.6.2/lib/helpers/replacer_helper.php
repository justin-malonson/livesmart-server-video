<?php 

class OsReplacerHelper {


  public static function replace_customer_vars($text, $customer){
  	$needles = array('{customer_full_name}','{customer_first_name}', '{customer_last_name}', '{customer_email}','{customer_phone}', '{customer_notes}');
  	$replacements = array($customer->full_name, $customer->first_name, $customer->last_name, $customer->email, $customer->formatted_phone, $customer->notes);
  	$text = str_replace($needles, $replacements, $text);
    $text = apply_filters('latepoint_replace_customer_vars', $text, $customer);
  	return $text;
  }

  public static function replace_agent_vars($text, $agent){
  	$needles = array('{agent_full_name}', '{agent_display_name}', '{agent_email}','{agent_phone}');
  	$replacements = array($agent->full_name, $agent->display_name, $agent->email, $agent->formatted_phone);
  	$text = str_replace($needles, $replacements, $text);
  	return $text;
  }

  public static function replace_tracking_vars($text, $booking){
    $needles = ['{booking_id}',
                '{agent_id}',
                '{customer_id}',
                '{total_price}',
                '{service_id}'];
    $replacements = [$booking->id,
                    $booking->agent_id,        
                    $booking->customer_id,        
                    $booking->price,        
                    $booking->service_id];    
    $text = str_replace($needles, $replacements, $text);
    $text = apply_filters('latepoint_replace_tracking_vars', $text, $booking);
    return $text;    

  }

  public static function replace_booking_vars($text, $booking){
  	$needles = ['{booking_id}',
                '{booking_code}',
                '{service_name}',
                '{service_category}',
                '{start_date}',
                '{start_time}',
                '{end_time}',
                '{booking_status}',
                '{location_name}', 
                '{location_full_address}', 
                '{booking_duration}', 
                '{booking_price}', 
                '{booking_payment_portion}', 
                '{booking_payment_method}', 
                '{booking_payment_amount}',
                '{livesmart_agent_url}',
                '{livesmart_visitor_url}'];
    $total_duration = ($booking->get_total_duration() > 0) ? $booking->get_total_duration().' '.__('minutes', 'latepoint') : __('n/a', 'latepoint');
  	$replacements = [$booking->id,
                      $booking->booking_code,
                      $booking->service->name,
                      $booking->service->category_name,
                      $booking->format_start_date_and_time(OsSettingsHelper::get_readable_date_format(), false), 
                      $booking->nice_start_time, 
                      $booking->nice_end_time, 
                      $booking->nice_status,
                      $booking->location->name,
                      $booking->location->full_address, 
                      $total_duration, 
                      OsMoneyHelper::format_price($booking->price), 
                      $booking->get_payment_portion_nice_name(), 
                      $booking->get_payment_method_nice_name(),
                      OsMoneyHelper::format_price($booking->get_total_amount_paid_from_transactions()),
                      $booking->livesmart_agent_url,
                      $booking->livesmart_visitor_url];
    $text = str_replace($needles, $replacements, $text);
    $text = apply_filters('latepoint_replace_booking_vars', $text, $booking);
  	return $text;
  }

  public static function replace_other_vars($text, $other_vars){
    if(isset($other_vars['old_status'])){
      $text = str_replace('{booking_old_status}', $other_vars['old_status'], $text);
    }
    if(isset($other_vars['token'])){
      $text = str_replace('{token}', $other_vars['token'], $text);
    }
    return $text;
  }

  public static function replace_all_vars($text, $vars){
  	if(isset($vars['booking'])) $text = self::replace_booking_vars($text, $vars['booking']);
  	if(isset($vars['customer'])) $text = self::replace_customer_vars($text, $vars['customer']);
    if(isset($vars['agent'])) $text = self::replace_agent_vars($text, $vars['agent']);
  	if(isset($vars['other_vars'])) $text = self::replace_other_vars($text, $vars['other_vars']);
    $text = apply_filters('latepoint_replace_all_vars_in_template', $text, $vars);
  	return $text;
  }
}