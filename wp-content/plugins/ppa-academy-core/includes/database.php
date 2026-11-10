<?php
/**
 * Dedicated MySQL storage for Academy student, batch, fee, transaction and result records.
 * Legacy CPT records are retained and copied idempotently; nothing is deleted.
 */
if (!defined('ABSPATH')) { exit; }

function ppa_db_tables() {
    global $wpdb;
    $c = $wpdb->get_charset_collate();
    $p = $wpdb->prefix;
    return array(
        'students' => "CREATE TABLE {$p}ppa_students (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            legacy_post_id bigint(20) unsigned DEFAULT NULL,
            student_code varchar(20) NOT NULL,
            full_name varchar(190) NOT NULL,
            bangla_name varchar(190) NOT NULL DEFAULT '',
            class_level varchar(30) NOT NULL DEFAULT '',
            batch_name varchar(190) NOT NULL DEFAULT '',
            institution varchar(190) NOT NULL DEFAULT '',
            student_phone varchar(32) NOT NULL DEFAULT '',
            guardian_name varchar(190) NOT NULL DEFAULT '',
            guardian_phone varchar(32) NOT NULL DEFAULT '',
            whatsapp_phone varchar(32) NOT NULL DEFAULT '',
            monthly_fee decimal(12,2) NOT NULL DEFAULT 0.00,
            details longtext NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY student_code (student_code),
            UNIQUE KEY legacy_post_id (legacy_post_id),
            KEY class_level (class_level),
            KEY batch_name (batch_name),
            KEY guardian_phone (guardian_phone)
        ) {$c};",
        'batches' => "CREATE TABLE {$p}ppa_batches (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            legacy_post_id bigint(20) unsigned DEFAULT NULL,
            batch_name varchar(190) NOT NULL,
            class_level varchar(30) NOT NULL DEFAULT '',
            max_capacity int(10) unsigned NOT NULL DEFAULT 0,
            schedule_days varchar(190) NOT NULL DEFAULT '',
            time_slot varchar(100) NOT NULL DEFAULT '',
            instructor varchar(190) NOT NULL DEFAULT '',
            details longtext NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY legacy_post_id (legacy_post_id),
            KEY class_level (class_level),
            KEY status (status)
        ) {$c};",
        'fees' => "CREATE TABLE {$p}ppa_fee_records (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            legacy_post_id bigint(20) unsigned DEFAULT NULL,
            student_id bigint(20) unsigned NOT NULL DEFAULT 0,
            student_code varchar(20) NOT NULL DEFAULT '',
            student_name varchar(190) NOT NULL DEFAULT '',
            billing_month varchar(20) NOT NULL,
            billing_year smallint(5) unsigned NOT NULL,
            payable_amount decimal(12,2) NOT NULL DEFAULT 0.00,
            paid_amount decimal(12,2) NOT NULL DEFAULT 0.00,
            due_amount decimal(12,2) NOT NULL DEFAULT 0.00,
            payment_status varchar(20) NOT NULL DEFAULT 'due',
            details longtext NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY legacy_post_id (legacy_post_id),
            KEY student_period (student_id,billing_year,billing_month),
            KEY payment_status (payment_status)
        ) {$c};",
        'transactions' => "CREATE TABLE {$p}ppa_payment_transactions (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            fee_record_id bigint(20) unsigned NOT NULL DEFAULT 0,
            student_id bigint(20) unsigned NOT NULL DEFAULT 0,
            student_code varchar(20) NOT NULL DEFAULT '',
            amount decimal(12,2) NOT NULL DEFAULT 0.00,
            payment_method varchar(30) NOT NULL DEFAULT 'Cash',
            transaction_reference varchar(120) NOT NULL DEFAULT '',
            receipt_number varchar(60) NOT NULL DEFAULT '',
            payment_date date DEFAULT NULL,
            legacy_post_id bigint(20) unsigned DEFAULT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY legacy_post_id (legacy_post_id),
            UNIQUE KEY receipt_number (receipt_number),
            KEY fee_record_id (fee_record_id),
            KEY student_id (student_id),
            KEY payment_date (payment_date)
        ) {$c};",
        'results' => "CREATE TABLE {$p}ppa_exam_results (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            legacy_post_id bigint(20) unsigned DEFAULT NULL,
            student_id bigint(20) unsigned NOT NULL DEFAULT 0,
            student_code varchar(20) NOT NULL DEFAULT '',
            student_name varchar(190) NOT NULL DEFAULT '',
            exam_name varchar(190) NOT NULL DEFAULT '',
            chapter varchar(190) NOT NULL DEFAULT '',
            marks decimal(10,2) NOT NULL DEFAULT 0.00,
            total_marks decimal(10,2) NOT NULL DEFAULT 0.00,
            highest_marks decimal(10,2) NOT NULL DEFAULT 0.00,
            grade varchar(12) NOT NULL DEFAULT '',
            exam_date date DEFAULT NULL,
            details longtext NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY legacy_post_id (legacy_post_id),
            KEY student_id (student_id),
            KEY exam_date (exam_date)
        ) {$c};"
    );
}

function ppa_db_install_schema() {
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    foreach (ppa_db_tables() as $sql) { dbDelta($sql); }
    update_option('ppa_db_schema_version', '1.0.0', false);
}
function ppa_db_meta($post_id, $key, $default = '') {
    $value = get_post_meta($post_id, '_ppa_' . $key, true);
    return ($value === '' || $value === null) ? $default : $value;
}
function ppa_db_datetime($post) {
    $date = $post && !empty($post->post_date) ? $post->post_date : current_time('mysql');
    return $date === '0000-00-00 00:00:00' ? current_time('mysql') : $date;
}
function ppa_db_migrate_legacy_records() {
    if (get_option('ppa_db_legacy_migration_v1_complete')) { return; }
    global $wpdb;
    $tables = ppa_db_tables();
    $students_table = $wpdb->prefix . 'ppa_students';
    $batches_table = $wpdb->prefix . 'ppa_batches';
    $fees_table = $wpdb->prefix . 'ppa_fee_records';
    $tx_table = $wpdb->prefix . 'ppa_payment_transactions';
    $results_table = $wpdb->prefix . 'ppa_exam_results';
    $map = array();

    $students = get_posts(array('post_type'=>'ppa_student','post_status'=>array('publish','draft','private'),'numberposts'=>-1,'orderby'=>'ID','order'=>'ASC'));
    foreach ($students as $post) {
        $code = (string) ppa_db_meta($post->ID,'student_id');
        if ($code === '') { continue; }
        $row = array(
            'legacy_post_id'=>$post->ID,'student_code'=>$code,'full_name'=>$post->post_title,
            'bangla_name'=>ppa_db_meta($post->ID,'bangla_name'),'class_level'=>ppa_db_meta($post->ID,'class_level'),
            'batch_name'=>ppa_db_meta($post->ID,'batch_name'),'institution'=>ppa_db_meta($post->ID,'institution'),
            'student_phone'=>ppa_db_meta($post->ID,'phone'),'guardian_name'=>ppa_db_meta($post->ID,'guardian_name'),
            'guardian_phone'=>ppa_db_meta($post->ID,'guardian_phone'),'whatsapp_phone'=>ppa_db_meta($post->ID,'whatsapp_phone'),
            'monthly_fee'=>(float)ppa_db_meta($post->ID,'monthly_fee',0),'details'=>$post->post_content,
            'status'=>'active','created_at'=>ppa_db_datetime($post),'updated_at'=>current_time('mysql')
        );
        $wpdb->replace($students_table,$row);
        $new_id=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$students_table} WHERE student_code=%s",$code));
        if ($new_id) { $map[$code]=$new_id; }
    }

    $batches = get_posts(array('post_type'=>'ppa_batch','post_status'=>array('publish','draft','private'),'numberposts'=>-1));
    foreach ($batches as $post) {
        $wpdb->replace($batches_table,array(
            'legacy_post_id'=>$post->ID,'batch_name'=>$post->post_title,
            'class_level'=>ppa_db_meta($post->ID,'class_level'),'max_capacity'=>(int)ppa_db_meta($post->ID,'max_capacity',0),
            'schedule_days'=>ppa_db_meta($post->ID,'schedule_days'),'time_slot'=>ppa_db_meta($post->ID,'time_slot'),
            'instructor'=>ppa_db_meta($post->ID,'instructor'),'details'=>$post->post_content,
            'status'=>'active','created_at'=>ppa_db_datetime($post),'updated_at'=>current_time('mysql')
        ));
    }

    $payments = get_posts(array('post_type'=>'ppa_fee_payment','post_status'=>array('publish','draft','private'),'numberposts'=>-1));
    foreach ($payments as $post) {
        $code=(string)ppa_db_meta($post->ID,'student_id');
        $student_id=isset($map[$code])?$map[$code]:0;
        $paid=(float)ppa_db_meta($post->ID,'paid_amount',ppa_db_meta($post->ID,'amount',0));
        $payable=(float)ppa_db_meta($post->ID,'payable_amount',0);
        $due=(float)ppa_db_meta($post->ID,'due_amount',max(0,$payable-$paid));
        $status=$paid>0?($due<=0?'paid':'partial'):'due';
        $wpdb->replace($fees_table,array(
            'legacy_post_id'=>$post->ID,'student_id'=>$student_id,'student_code'=>$code,'student_name'=>$post->post_title,
            'billing_month'=>ppa_db_meta($post->ID,'billing_month','Unknown'),
            'billing_year'=>(int)ppa_db_meta($post->ID,'billing_year',0),'payable_amount'=>$payable,
            'paid_amount'=>$paid,'due_amount'=>$due,'payment_status'=>$status,'details'=>$post->post_content,
            'created_at'=>ppa_db_datetime($post),'updated_at'=>current_time('mysql')
        ));
        $fee_id=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$fees_table} WHERE legacy_post_id=%d",$post->ID));
        $receipt=(string)ppa_db_meta($post->ID,'receipt_number');
        if ($fee_id && $paid>0) {
            $wpdb->replace($tx_table,array(
                'fee_record_id'=>$fee_id,'student_id'=>$student_id,'student_code'=>$code,'amount'=>$paid,
                'payment_method'=>ppa_db_meta($post->ID,'payment_method','Cash'),
                'transaction_reference'=>ppa_db_meta($post->ID,'transaction_reference'),
                'receipt_number'=>$receipt,'payment_date'=>ppa_db_meta($post->ID,'payment_date') ?: null,
                'legacy_post_id'=>$post->ID,'created_at'=>ppa_db_datetime($post)
            ));
        }
    }

    $results = get_posts(array('post_type'=>'ppa_exam_result','post_status'=>array('publish','draft','private'),'numberposts'=>-1));
    foreach ($results as $post) {
        $code=(string)ppa_db_meta($post->ID,'student_id');
        $wpdb->replace($results_table,array(
            'legacy_post_id'=>$post->ID,'student_id'=>isset($map[$code])?$map[$code]:0,'student_code'=>$code,
            'student_name'=>$post->post_title,'exam_name'=>ppa_db_meta($post->ID,'exam_name'),
            'chapter'=>ppa_db_meta($post->ID,'chapter'),'marks'=>(float)ppa_db_meta($post->ID,'marks',0),
            'total_marks'=>(float)ppa_db_meta($post->ID,'total_marks',0),'highest_marks'=>(float)ppa_db_meta($post->ID,'highest_marks',0),
            'grade'=>ppa_db_meta($post->ID,'grade'),'exam_date'=>ppa_db_meta($post->ID,'exam_date') ?: null,
            'details'=>$post->post_content,'created_at'=>ppa_db_datetime($post)
        ));
    }

    update_option('ppa_db_legacy_migration_v1_complete',1,false);
    update_option('ppa_db_legacy_migration_v1_report',array(
        'students'=>count($students),'batches'=>count($batches),'fee_records'=>count($payments),'exam_results'=>count($results),
        'migrated_at'=>current_time('mysql')
    ),false);
}
function ppa_db_maybe_upgrade() {
    if (get_option('ppa_db_schema_version') !== '1.0.0') { ppa_db_install_schema(); }
    if (!get_option('ppa_db_legacy_migration_v1_complete')) { ppa_db_migrate_legacy_records(); }
}
