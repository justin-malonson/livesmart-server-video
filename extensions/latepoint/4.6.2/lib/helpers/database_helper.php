<?php 

class OsDatabaseHelper {

	public static function run_setup(){
		self::run_version_specific_updates();
		self::install_database();
	}

  public static function check_db_version(){
    $current_db_version = OsSettingsHelper::get_db_version();
    if(!$current_db_version || version_compare(LATEPOINT_DB_VERSION, $current_db_version)){
      self::install_database();
    }
  }

  // [name => 'addon_name', 'db_version' => '1.0.0', 'version' => '1.0.0']
  public static function get_installed_addons_list(){
    $installed_addons = [];
    $installed_addons = apply_filters('latepoint_installed_addons', $installed_addons);
    return $installed_addons;
  }


  // Check if addons databases are up to date
  public static function check_db_version_for_addons(){
    $is_new_addon_db_version_available = false;
    $installed_addons = self::get_installed_addons_list();
    if(empty($installed_addons)) return;
    foreach($installed_addons as $installed_addon){
      $current_addon_db_version = get_option($installed_addon['name'] . '_addon_db_version');
      if(!$current_addon_db_version || version_compare($current_addon_db_version, $installed_addon['db_version'])){
        OsAddonsHelper::save_addon_info($installed_addon['name'], $installed_addon['db_version']);
        $is_new_addon_db_version_available = true;
      }
    }
    if($is_new_addon_db_version_available) self::install_database_for_addons();
  }


  // Install queries for addons
	public static function install_database_for_addons(){
		$sqls = self::get_table_queries_for_addons();
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    foreach($sqls as $sql){
      error_log(print_r(dbDelta( $sql ), true));
    }
	}



  public static function install_database(){
    $sqls = self::get_initial_table_queries();
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    foreach($sqls as $sql){
      error_log(print_r(dbDelta( $sql ), true));
    }
    self::run_version_specific_updates();
    update_option( 'latepoint_db_version', LATEPOINT_DB_VERSION );
  }

	public static function run_version_specific_updates(){
		$current_db_version = OsSettingsHelper::get_db_version();
		if(!$current_db_version) return false;
		$sqls = [];
		if(version_compare('1.0.2', $current_db_version) > 0){
			// lower than 1.0.2
			$sqls = self::get_queries_for_nullable_columns();
			self::run_queries($sqls);
		}
    if(version_compare('1.1.0', $current_db_version) > 0){
      // lower than 1.1.0
      $sqls = self::set_end_date_for_bookings();
      self::run_queries($sqls);
    }
    if(version_compare('1.3.0', $current_db_version) > 0){
      // lower than 1.3.0
      $sqls = [];
      $sqls[] = "UPDATE ".LATEPOINT_TABLE_BOOKINGS." SET total_attendies = 1 WHERE total_attendies IS NULL;";
      $sqls[] = "UPDATE ".LATEPOINT_TABLE_SERVICES." SET visibility = '".LATEPOINT_SERVICE_VISIBILITY_VISIBLE."' WHERE visibility IS NULL OR visibility = '';";
      $sqls[] = "UPDATE ".LATEPOINT_TABLE_SERVICES." SET capacity_min = 1 WHERE capacity_min IS NULL;";
      $sqls[] = "UPDATE ".LATEPOINT_TABLE_SERVICES." SET capacity_max = 1 WHERE capacity_max IS NULL;";
      self::run_queries($sqls);
    }
    if(version_compare('1.3.1', $current_db_version) > 0){
      $sqls = [];
      $sqls[] = "ALTER TABLE ".LATEPOINT_TABLE_CUSTOMERS." MODIFY COLUMN first_name varchar(255)";
      self::run_queries($sqls);
    }
    if(version_compare('1.3.7', $current_db_version) > 0){
      $sqls = [];
      $sqls[] = "ALTER TABLE ".LATEPOINT_TABLE_AGENTS." MODIFY COLUMN wp_user_id int(11)";
      self::run_queries($sqls);
    }
		return true;
	}

	public static function run_queries($sqls){
    global $wpdb;
		if($sqls && is_array($sqls)){
			foreach($sqls as $sql){
				$wpdb->query($sql);
        OsDebugHelper::log($sql);
			}
		}
	}

  public static function set_end_date_for_bookings(){
    $sqls = [];

    $sqls[] = "UPDATE ".LATEPOINT_TABLE_BOOKINGS." SET end_date = start_date WHERE end_date IS NULL;";
    return $sqls;
  }



  // Get queries registered by addons
  public static function get_table_queries_for_addons(){
    $sqls = [];
    $sqls = apply_filters('latepoint_addons_sqls', $sqls);
    return $sqls;
  }



  public static function get_initial_table_queries(){

    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $sqls = [];

    $sqls[] = "CREATE TABLE ".LATEPOINT_TABLE_BOOKING_INTENTS." (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      intent_key varchar(55) NOT NULL,
      customer_id int(11) NOT NULL,
      booking_data text,
      restrictions_data text,
      payment_data text,
      booking_id int(11),
      booking_form_page_url text,
      created_at datetime,
      updated_at datetime,
      PRIMARY KEY  (id),
      UNIQUE KEY intent_key_index (intent_key)
    ) $charset_collate;";

    $sqls[] = "CREATE TABLE ".LATEPOINT_TABLE_SESSIONS." (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      session_key varchar(55) NOT NULL,
      session_value longtext NOT NULL,
      expiration BIGINT UNSIGNED NOT NULL,
      hash varchar(50) NOT NULL,
      PRIMARY KEY  (id),
      UNIQUE KEY session_key (session_key)
    ) $charset_collate;";

    $sqls[] = "CREATE TABLE ".LATEPOINT_TABLE_BOOKINGS." (
      id int(11) NOT NULL AUTO_INCREMENT,
      booking_code varchar(10),
      start_datetime_gmt datetime,
      end_datetime_gmt datetime,
      start_date date,
      end_date date,
      start_time mediumint(9),
      end_time mediumint(9),
      start_datetime_utc datetime,
      end_datetime_utc datetime,
      buffer_before mediumint(9) NOT NULL,
      buffer_after mediumint(9) NOT NULL,
      duration mediumint(9),
      subtotal decimal(20,4),
      price decimal(20,4),
      status varchar(30) DEFAULT 'pending' NOT NULL,
      payment_status varchar(30) DEFAULT 'not_paid' NOT NULL,
      customer_id mediumint(9) NOT NULL,
      service_id mediumint(9) NOT NULL,
      agent_id mediumint(9) NOT NULL,
      location_id mediumint(9),
      total_attendies mediumint(4),
      payment_method varchar(55),
      payment_portion varchar(55),
      ip_address varchar(55),
      coupon_code varchar(100),
      coupon_discount decimal(20,4),
      customer_comment text,
      livesmart_agent_url varchar(255),
      livesmart_visitor_url varchar(255),
      created_at datetime,
      updated_at datetime,
      KEY start_date_index (start_date),
      KEY end_date_index (end_date),
      KEY status_index (status),
      KEY customer_id_index (customer_id),
      KEY service_id_index (service_id),
      KEY agent_id_index (agent_id),
      KEY location_id_index (location_id),
      PRIMARY KEY  (id)
    ) $charset_collate;";


    $sqls[] = "CREATE TABLE ".LATEPOINT_TABLE_BOOKING_META." (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      object_id mediumint(9) NOT NULL,
      meta_key varchar(110) NOT NULL,
      meta_value text,
      created_at datetime,
      updated_at datetime,
      KEY meta_key_index (meta_key),
      KEY object_id_index (object_id),
      PRIMARY KEY  (id)
    ) $charset_collate;";

    $sqls[] = "CREATE TABLE ".LATEPOINT_TABLE_SENT_REMINDERS." (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      booking_id mediumint(9) NOT NULL,
      reminder_id varchar(30) NOT NULL,
      created_at datetime,
      updated_at datetime,
      KEY booking_id_index (booking_id),
      KEY reminder_id_index (reminder_id),
      PRIMARY KEY  (id)
    ) $charset_collate;";

    $sqls[] = "CREATE TABLE ".LATEPOINT_TABLE_PROCESSES." (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      created_at datetime,
      updated_at datetime,
      PRIMARY KEY  (id)
    ) $charset_collate;";

    $sqls[] = "CREATE TABLE ".LATEPOINT_TABLE_SERVICE_META." (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      object_id mediumint(9) NOT NULL,
      meta_key varchar(110) NOT NULL,
      meta_value text,
      created_at datetime,
      updated_at datetime,
      KEY meta_key_index (meta_key),
      KEY object_id_index (object_id),
      PRIMARY KEY  (id)
    ) $charset_collate;";

    $sqls[] = "CREATE TABLE ".LATEPOINT_TABLE_CUSTOMER_META." (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      object_id mediumint(9) NOT NULL,
      meta_key varchar(110) NOT NULL,
      meta_value text,
      created_at datetime,
      updated_at datetime,
      KEY meta_key_index (meta_key),
      KEY object_id_index (object_id),
      PRIMARY KEY  (id)
    ) $charset_collate;";

    $sqls[] = "CREATE TABLE ".LATEPOINT_TABLE_AGENT_META." (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      object_id mediumint(9) NOT NULL,
      meta_key varchar(110) NOT NULL,
      meta_value text,
      created_at datetime,
      updated_at datetime,
      KEY meta_key_index (meta_key),
      KEY object_id_index (object_id),
      PRIMARY KEY  (id)
    ) $charset_collate;";


    $sqls[] = "CREATE TABLE ".LATEPOINT_TABLE_SETTINGS." (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      name varchar(110) NOT NULL,
      value longtext,
      created_at datetime,
      updated_at datetime,
      KEY name_index (name),
      PRIMARY KEY  (id)
    ) $charset_collate;";


    $sqls[] = "CREATE TABLE ".LATEPOINT_TABLE_LOCATIONS." (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      name varchar(255) NOT NULL,
      full_address text,
      status varchar(20) NOT NULL,
      category_id int(11),
      order_number int(11),
      selection_image_id int(11),
      created_at datetime,
      updated_at datetime,
      KEY status_index (status),
      PRIMARY KEY  (id)
    ) $charset_collate;";


    $sqls[] = "CREATE TABLE ".LATEPOINT_TABLE_LOCATION_CATEGORIES." (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      name varchar(100) NOT NULL,
      short_description text,
      parent_id mediumint(9),
      selection_image_id int(11),
      order_number int(11),
      created_at datetime,
      updated_at datetime,
      KEY order_number_index (order_number),
      KEY parent_id_index (parent_id),
      PRIMARY KEY  (id)
    ) $charset_collate;";


    $sqls[] = "CREATE TABLE ".LATEPOINT_TABLE_SERVICES." (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      name varchar(255) NOT NULL,
      short_description text,
      is_price_variable boolean,
      price_min decimal(20,4),
      price_max decimal(20,4),
      charge_amount decimal(20,4),
      deposit_amount decimal(20,4),
      is_deposit_required boolean,
      duration_name varchar(255) NOT NULL,
      duration int(11) NOT NULL,
      buffer_before int(11),
      buffer_after int(11),
      category_id int(11),
      order_number int(11),
      selection_image_id int(11),
      description_image_id int(11),
      bg_color varchar(20),
      timeblock_interval int(11),
      capacity_min int(4),
      capacity_max int(4),
      status varchar(20) NOT NULL,
      visibility varchar(20) NOT NULL,
      created_at datetime,
      updated_at datetime,
      KEY category_id_index (category_id),
      KEY order_number_index (order_number),
      KEY status_index (status),
      PRIMARY KEY  (id)
    ) $charset_collate;";

    $sqls[] = "CREATE TABLE ".LATEPOINT_TABLE_AGENTS." (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      avatar_image_id int(11),
      bio_image_id int(11),
      first_name varchar(255) NOT NULL,
      last_name varchar(255),
      display_name varchar(255),
      title varchar(255),
      bio text,
      features text,
      email varchar(110) NOT NULL,
      phone varchar(255),
      password varchar(255),
      custom_hours boolean,
      wp_user_id int(11),
      status varchar(20) NOT NULL,
      extra_emails text,
      extra_phones text,
      created_at datetime,
      updated_at datetime,
      KEY email_index (email),
      PRIMARY KEY  (id)
    ) $charset_collate;";

    $sqls[] = "CREATE TABLE ".LATEPOINT_TABLE_STEP_SETTINGS." (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      label varchar(50) NOT NULL,
      value text,
      step varchar(50),
      created_at datetime,
      updated_at datetime,
      KEY step_index (step),
      KEY label_index (label),
      PRIMARY KEY  (id)
    ) $charset_collate;";
    
    
    $sqls[] = "CREATE TABLE ".LATEPOINT_TABLE_CUSTOMERS." (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      first_name varchar(255),
      last_name varchar(255),
      email varchar(110) NOT NULL,
      phone varchar(255),
      avatar_image_id int(11),
      status varchar(50) NOT NULL,
      password varchar(255),
      activation_key varchar(255),
      account_nonse varchar(255),
      google_user_id varchar(255),
      facebook_user_id varchar(255),
      wordpress_user_id int(11),
      is_guest boolean,
      notes text,
      admin_notes text,
      created_at datetime,
      updated_at datetime,
      KEY email_index (email),
      KEY status_index (status),
      KEY wordpress_user_id_index (wordpress_user_id),
      PRIMARY KEY  (id)
    ) $charset_collate;";

    $sqls[] = "CREATE TABLE ".LATEPOINT_TABLE_SERVICE_CATEGORIES." (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      name varchar(100) NOT NULL,
      short_description text,
      parent_id mediumint(9),
      selection_image_id int(11),
      order_number int(11),
      created_at datetime,
      updated_at datetime,
      KEY order_number_index (order_number),
      KEY parent_id_index (parent_id),
      PRIMARY KEY  (id)
    ) $charset_collate;";

    $sqls[] = "CREATE TABLE ".LATEPOINT_TABLE_CUSTOM_PRICES." (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      agent_id int(11) NOT NULL,
      service_id int(11) NOT NULL,
      location_id int(11) NOT NULL,
      is_price_variable boolean,
      price_min decimal(20,4),
      price_max decimal(20,4),
      charge_amount decimal(20,4),
      is_deposit_required boolean,
      deposit_amount decimal(20,4),
      created_at datetime,
      updated_at datetime,
      KEY agent_id_index (agent_id),
      KEY service_id_index (service_id),
      KEY location_id_index (location_id),
      PRIMARY KEY  (id)
    ) $charset_collate;";

    $sqls[] = "CREATE TABLE ".LATEPOINT_TABLE_WORK_PERIODS." (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      agent_id int(11) NOT NULL,
      service_id int(11) NOT NULL,
      location_id int(11) NOT NULL,
      start_time smallint(6) NOT NULL,
      end_time smallint(6) NOT NULL,
      week_day tinyint(3) NOT NULL,
      custom_date date,
      chain_id varchar(20),
      created_at datetime,
      updated_at datetime,
      KEY agent_id_index (agent_id),
      KEY service_id_index (service_id),
      KEY location_id_index (location_id),
      KEY week_day_index (week_day),
      KEY custom_date_index (custom_date),
      PRIMARY KEY  (id)
    ) $charset_collate;";

    $sqls[] = "CREATE TABLE ".LATEPOINT_TABLE_AGENTS_SERVICES." (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      agent_id int(11) NOT NULL,
      service_id int(11) NOT NULL,
      location_id int(11),
      is_custom_hours BOOLEAN,
      is_custom_price BOOLEAN,
      is_custom_duration BOOLEAN,
      created_at datetime,
      updated_at datetime,
      KEY agent_id_index (agent_id),
      KEY service_id_index (service_id),
      KEY location_id_index (location_id),
      PRIMARY KEY  (id)
    ) $charset_collate;";

    $sqls[] = "CREATE TABLE ".LATEPOINT_TABLE_ACTIVITIES." (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      agent_id int(11),
      booking_id int(11),
      service_id int(11),
      customer_id int(11),
      code varchar(255) NOT NULL,
      description text,
      initiated_by varchar(100),
      initiated_by_id int(11),
      created_at datetime,
      updated_at datetime,
      PRIMARY KEY  (id)
    ) $charset_collate;";

    $sqls[] = "CREATE TABLE ".LATEPOINT_TABLE_TRANSACTIONS." (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      token text NOT NULL,
      booking_id int(11) NOT NULL,
      customer_id int(11) NOT NULL,
      processor varchar(100) NOT NULL,
      payment_method varchar(55),
      payment_portion varchar(55),
      funds_status varchar(40),
      status varchar(100) NOT NULL,
      amount decimal(20,4),
      notes text,
      created_at datetime,
      updated_at datetime,
      PRIMARY KEY  (id)
    ) $charset_collate;";


    return $sqls;
  }

























  public static function get_queries_for_nullable_columns(){
  	$sqls = [];

    $sqls[] = "ALTER TABLE ".LATEPOINT_TABLE_BOOKINGS."
					      MODIFY COLUMN ip_address varchar(55),
					      MODIFY COLUMN created_at datetime,
					      MODIFY COLUMN updated_at datetime;";


    $sqls[] = "ALTER TABLE ".LATEPOINT_TABLE_CUSTOMER_META."
					      MODIFY COLUMN meta_value text,
					      MODIFY COLUMN created_at datetime,
					      MODIFY COLUMN updated_at datetime;";

    $sqls[] = "ALTER TABLE ".LATEPOINT_TABLE_SETTINGS."
					      MODIFY COLUMN value text,
					      MODIFY COLUMN created_at datetime,
					      MODIFY COLUMN updated_at datetime;";

    $sqls[] = "ALTER TABLE ".LATEPOINT_TABLE_SERVICES."
					      MODIFY COLUMN short_description text,
					      MODIFY COLUMN is_price_variable boolean,
					      MODIFY COLUMN price_min decimal(20,4),
					      MODIFY COLUMN price_max decimal(20,4),
					      MODIFY COLUMN charge_amount decimal(20,4),
					      MODIFY COLUMN is_deposit_required boolean,
					      MODIFY COLUMN buffer_before int(11),
					      MODIFY COLUMN buffer_after int(11),
					      MODIFY COLUMN category_id int(11),
					      MODIFY COLUMN order_number int(11),
					      MODIFY COLUMN selection_image_id int(11),
					      MODIFY COLUMN description_image_id int(11),
					      MODIFY COLUMN bg_color varchar(20),
					      MODIFY COLUMN created_at datetime,
					      MODIFY COLUMN updated_at datetime;";

    $sqls[] = "ALTER TABLE ".LATEPOINT_TABLE_AGENTS."
					      MODIFY COLUMN avatar_image_id int(11),
					      MODIFY COLUMN last_name varchar(255),
					      MODIFY COLUMN phone varchar(255),
					      MODIFY COLUMN password varchar(255),
					      MODIFY COLUMN custom_hours boolean,
					      MODIFY COLUMN created_at datetime,
					      MODIFY COLUMN updated_at datetime;";

    $sqls[] = "ALTER TABLE ".LATEPOINT_TABLE_STEP_SETTINGS."
					      MODIFY COLUMN value text,
					      MODIFY COLUMN step varchar(50),
					      MODIFY COLUMN created_at datetime,
					      MODIFY COLUMN updated_at datetime;";

  	$sqls[] = "ALTER TABLE ".LATEPOINT_TABLE_CUSTOMERS." 
						    MODIFY COLUMN last_name varchar(255),
						    MODIFY COLUMN phone varchar(255),
						    MODIFY COLUMN avatar_image_id int(11),
						    MODIFY COLUMN password varchar(255),
						    MODIFY COLUMN activation_key varchar(255),
						    MODIFY COLUMN account_nonse varchar(255),
						    MODIFY COLUMN google_user_id varchar(255),
						    MODIFY COLUMN facebook_user_id varchar(255),
						    MODIFY COLUMN is_guest boolean,
						    MODIFY COLUMN notes text,
						    MODIFY COLUMN created_at datetime,
						    MODIFY COLUMN updated_at datetime;";

    $sqls[] = "ALTER TABLE ".LATEPOINT_TABLE_SERVICE_CATEGORIES." 
					      MODIFY COLUMN short_description text,
					      MODIFY COLUMN parent_id mediumint(9),
					      MODIFY COLUMN selection_image_id int(11),
					      MODIFY COLUMN order_number int(11),
					      MODIFY COLUMN created_at datetime,
					      MODIFY COLUMN updated_at datetime";

    $sqls[] = "ALTER TABLE ".LATEPOINT_TABLE_CUSTOM_PRICES." 
					      MODIFY COLUMN is_price_variable boolean,
					      MODIFY COLUMN price_min decimal(20,4),
					      MODIFY COLUMN price_max decimal(20,4),
					      MODIFY COLUMN charge_amount decimal(20,4),
					      MODIFY COLUMN is_deposit_required boolean,
					      MODIFY COLUMN created_at datetime,
					      MODIFY COLUMN updated_at datetime";

    $sqls[] = "ALTER TABLE ".LATEPOINT_TABLE_WORK_PERIODS." 
					      MODIFY COLUMN custom_date date,
					      MODIFY COLUMN created_at datetime,
					      MODIFY COLUMN updated_at datetime";

    $sqls[] = "ALTER TABLE ".LATEPOINT_TABLE_AGENTS_SERVICES." 
					      MODIFY COLUMN is_custom_hours BOOLEAN,
					      MODIFY COLUMN is_custom_price BOOLEAN,
					      MODIFY COLUMN is_custom_duration BOOLEAN,
					      MODIFY COLUMN created_at datetime,
					      MODIFY COLUMN updated_at datetime";

    $sqls[] = "ALTER TABLE ".LATEPOINT_TABLE_ACTIVITIES." 
					      MODIFY COLUMN agent_id int(11),
					      MODIFY COLUMN booking_id int(11),
					      MODIFY COLUMN service_id int(11),
					      MODIFY COLUMN customer_id int(11),
					      MODIFY COLUMN description text,
					      MODIFY COLUMN initiated_by varchar(100),
					      MODIFY COLUMN initiated_by_id int(11),
					      MODIFY COLUMN created_at datetime,
					      MODIFY COLUMN updated_at datetime";

    $sqls[] = "ALTER TABLE ".LATEPOINT_TABLE_TRANSACTIONS." 
					      MODIFY COLUMN notes text,
					      MODIFY COLUMN created_at datetime,
					      MODIFY COLUMN updated_at datetime";
		return $sqls;
  }


}
