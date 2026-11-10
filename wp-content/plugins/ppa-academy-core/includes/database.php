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
            KEY receipt_lookup (receipt_number),
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
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    foreach (ppa_db_tables() as $sql) { dbDelta($sql); }
    // Earlier development builds used a UNIQUE receipt index. Drop only that
    // index so legacy duplicate receipt labels cannot overwrite each other.
    $transaction_table = $wpdb->prefix . 'ppa_payment_transactions';
    $indexes = $wpdb->get_results("SHOW INDEX FROM {$transaction_table}");
    if (is_array($indexes)) {
        foreach ($indexes as $index) {
            if (isset($index->Key_name, $index->Non_unique) && $index->Key_name === 'receipt_number' && (int)$index->Non_unique === 0) {
                $wpdb->query("ALTER TABLE {$transaction_table} DROP INDEX receipt_number");
                break;
            }
        }
    }
    update_option('ppa_db_schema_version', '1.0.1', false);
}
function ppa_db_meta($post_id, $key, $default = '') {
    $was_bypassed = !empty($GLOBALS['ppa_db_meta_bypass']);
    $GLOBALS['ppa_db_meta_bypass'] = true;
    $value = get_post_meta($post_id, '_ppa_' . $key, true);
    if (!$was_bypassed) { unset($GLOBALS['ppa_db_meta_bypass']); }
    return ($value === '' || $value === null) ? $default : $value;
}
function ppa_db_datetime($post) {
    $date = $post && !empty($post->post_date) ? $post->post_date : current_time('mysql');
    return $date === '0000-00-00 00:00:00' ? current_time('mysql') : $date;
}
function ppa_db_safe_date($value) {
    $value = trim((string)$value);
    if ($value === '' || $value === '0000-00-00') { return null; }
    if (preg_match('/^(\\d{4})-(\\d{2})-(\\d{2})$/', $value, $match) && checkdate((int)$match[2], (int)$match[3], (int)$match[1])) {
        return $value;
    }
    $timestamp = strtotime($value);
    return $timestamp ? gmdate('Y-m-d', $timestamp) : null;
}
function ppa_db_migrate_legacy_records() {
    if (get_option('ppa_db_legacy_migration_v1_complete')) { return true; }
    global $wpdb;
    $migration_errors = array();
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
        if ($code === '') { $migration_errors[] = 'Student post #' . $post->ID . ' is missing its student ID; record was not skipped silently.'; continue; }
        $duplicate_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$students_table} WHERE student_code = %s AND (legacy_post_id IS NULL OR legacy_post_id <> %d) LIMIT 1", $code, $post->ID));
        if ($duplicate_id) { $migration_errors[] = 'Duplicate student ID ' . $code . ' on legacy post #' . $post->ID . '; existing post data was not overwritten.'; continue; }
        $row = array(
            'legacy_post_id'=>$post->ID,'student_code'=>$code,'full_name'=>$post->post_title,
            'bangla_name'=>ppa_db_meta($post->ID,'bangla_name'),'class_level'=>ppa_db_meta($post->ID,'class_level'),
            'batch_name'=>ppa_db_meta($post->ID,'batch_name'),'institution'=>ppa_db_meta($post->ID,'institution'),
            'student_phone'=>ppa_db_meta($post->ID,'phone'),'guardian_name'=>ppa_db_meta($post->ID,'guardian_name'),
            'guardian_phone'=>ppa_db_meta($post->ID,'guardian_phone'),'whatsapp_phone'=>ppa_db_meta($post->ID,'whatsapp_phone'),
            'monthly_fee'=>(float)ppa_db_meta($post->ID,'monthly_fee',0),'details'=>$post->post_content,
            'status'=>'active','created_at'=>ppa_db_datetime($post),'updated_at'=>current_time('mysql')
        );
        if ($wpdb->replace($students_table,$row) === false) { $migration_errors[] = 'Student post #' . $post->ID . ': ' . $wpdb->last_error; continue; }
        $new_id=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$students_table} WHERE student_code=%s",$code));
        if ($new_id) { $map[$code]=$new_id; }
    }

    $batches = get_posts(array('post_type'=>'ppa_batch','post_status'=>array('publish','draft','private'),'numberposts'=>-1));
    foreach ($batches as $post) {
        $batch_write = $wpdb->replace($batches_table,array(
            'legacy_post_id'=>$post->ID,'batch_name'=>$post->post_title,
            'class_level'=>ppa_db_meta($post->ID,'class_level'),'max_capacity'=>(int)ppa_db_meta($post->ID,'max_capacity',0),
            'schedule_days'=>ppa_db_meta($post->ID,'schedule_days'),'time_slot'=>ppa_db_meta($post->ID,'time_slot'),
            'instructor'=>ppa_db_meta($post->ID,'instructor'),'details'=>$post->post_content,
            'status'=>'active','created_at'=>ppa_db_datetime($post),'updated_at'=>current_time('mysql')
        ));
        if ($batch_write === false) { $migration_errors[] = 'Batch post #' . $post->ID . ': ' . $wpdb->last_error; }
    }

    $payments = get_posts(array('post_type'=>'ppa_fee_payment','post_status'=>array('publish','draft','private'),'numberposts'=>-1));
    foreach ($payments as $post) {
        $code=(string)ppa_db_meta($post->ID,'student_id');
        $student_id=isset($map[$code])?$map[$code]:0;
        $paid=(float)ppa_db_meta($post->ID,'paid_amount',ppa_db_meta($post->ID,'amount',0));
        $payable=(float)ppa_db_meta($post->ID,'payable_amount',0);
        $due=(float)ppa_db_meta($post->ID,'due_amount',max(0,$payable-$paid));
        $status=$paid>0?($due<=0?'paid':'partial'):'due';
        $fee_write = $wpdb->replace($fees_table,array(
            'legacy_post_id'=>$post->ID,'student_id'=>$student_id,'student_code'=>$code,'student_name'=>$post->post_title,
            'billing_month'=>ppa_db_meta($post->ID,'billing_month','Unknown'),
            'billing_year'=>(int)ppa_db_meta($post->ID,'billing_year',0),'payable_amount'=>$payable,
            'paid_amount'=>$paid,'due_amount'=>$due,'payment_status'=>$status,'details'=>$post->post_content,
            'created_at'=>ppa_db_datetime($post),'updated_at'=>current_time('mysql')
        ));
        if ($fee_write === false) { $migration_errors[] = 'Fee post #' . $post->ID . ': ' . $wpdb->last_error; continue; }
        $fee_id=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$fees_table} WHERE legacy_post_id=%d",$post->ID));
        $receipt=(string)ppa_db_meta($post->ID,'receipt_number');
        if ($receipt === '') { $receipt = 'LEGACY-' . (int)$post->ID; }
        if ($fee_id && $paid>0) {
            $tx_write = $wpdb->replace($tx_table,array(
                'fee_record_id'=>$fee_id,'student_id'=>$student_id,'student_code'=>$code,'amount'=>$paid,
                'payment_method'=>ppa_db_meta($post->ID,'payment_method','Cash'),
                'transaction_reference'=>ppa_db_meta($post->ID,'transaction_reference'),
                'receipt_number'=>$receipt,'payment_date'=>ppa_db_safe_date(ppa_db_meta($post->ID,'payment_date')),
                'legacy_post_id'=>$post->ID,'created_at'=>ppa_db_datetime($post)
            ));
            if ($tx_write === false) { $migration_errors[] = 'Payment transaction post #' . $post->ID . ': ' . $wpdb->last_error; }
        }
    }

    $results = get_posts(array('post_type'=>'ppa_exam_result','post_status'=>array('publish','draft','private'),'numberposts'=>-1));
    foreach ($results as $post) {
        $code=(string)ppa_db_meta($post->ID,'student_id');
        $result_write = $wpdb->replace($results_table,array(
            'legacy_post_id'=>$post->ID,'student_id'=>isset($map[$code])?$map[$code]:0,'student_code'=>$code,
            'student_name'=>$post->post_title,'exam_name'=>ppa_db_meta($post->ID,'exam_name'),
            'chapter'=>ppa_db_meta($post->ID,'chapter'),'marks'=>(float)ppa_db_meta($post->ID,'marks',0),
            'total_marks'=>(float)ppa_db_meta($post->ID,'total_marks',0),'highest_marks'=>(float)ppa_db_meta($post->ID,'highest_marks',0),
            'grade'=>ppa_db_meta($post->ID,'grade'),'exam_date'=>ppa_db_safe_date(ppa_db_meta($post->ID,'exam_date')),
            'details'=>$post->post_content,'created_at'=>ppa_db_datetime($post)
        ));
        if ($result_write === false) { $migration_errors[] = 'Exam result post #' . $post->ID . ': ' . $wpdb->last_error; }
    }

    if ($migration_errors) {
        update_option('ppa_db_legacy_migration_v1_report',array('errors'=>$migration_errors,'failed_at'=>current_time('mysql')),false);
        error_log('Photon Physics Academy legacy MySQL migration incomplete: ' . implode(' | ', $migration_errors));
        return false;
    }
    update_option('ppa_db_legacy_migration_v1_complete',1,false);
    update_option('ppa_db_legacy_migration_v1_report',array(
        'students'=>count($students),'batches'=>count($batches),'fee_records'=>count($payments),'exam_results'=>count($results),
        'migrated_at'=>current_time('mysql')
    ),false);
    return true;
}
function ppa_db_maybe_upgrade() {
    if (get_option('ppa_db_schema_version') !== '1.0.1') { ppa_db_install_schema(); }
    if (!get_option('ppa_db_legacy_migration_v1_complete')) { ppa_db_migrate_legacy_records(); }
}

/**
 * Mirror a custom-admin legacy post into the dedicated Academy tables.
 * The CPT row remains as a compatibility mirror for existing public/theme code;
 * the dedicated tables are also updated for every admin create/edit operation.
 */
function ppa_db_find_student($student_code) {
    global $wpdb;
    $table = $wpdb->prefix . 'ppa_students';
    return $wpdb->get_row($wpdb->prepare("SELECT id, student_code, full_name FROM {$table} WHERE student_code = %s LIMIT 1", $student_code));
}
function ppa_db_sync_record($post_id) {
    global $wpdb;
    $post = get_post(absint($post_id));
    if (!$post) { return new WP_Error('ppa_db_missing_post', 'The record could not be found.'); }
    $now = current_time('mysql');
    $created = ppa_db_datetime($post);
    $type = $post->post_type;
    $code = (string) ppa_db_meta($post->ID, 'student_id');
    $students = $wpdb->prefix . 'ppa_students';
    $batches = $wpdb->prefix . 'ppa_batches';
    $fees = $wpdb->prefix . 'ppa_fee_records';
    $transactions = $wpdb->prefix . 'ppa_payment_transactions';
    $results = $wpdb->prefix . 'ppa_exam_results';

    if ($type === 'ppa_student') {
        $student_code = (string) ppa_db_meta($post->ID, 'student_id');
        if ($student_code === '') { return new WP_Error('ppa_db_student_code', 'Student ID is missing.'); }
        $ok = $wpdb->replace($students, array(
            'legacy_post_id'=>$post->ID,'student_code'=>$student_code,'full_name'=>$post->post_title,
            'bangla_name'=>ppa_db_meta($post->ID,'bangla_name'),'class_level'=>ppa_db_meta($post->ID,'class_level'),
            'batch_name'=>ppa_db_meta($post->ID,'batch_name'),'institution'=>ppa_db_meta($post->ID,'institution'),
            'student_phone'=>ppa_db_meta($post->ID,'phone'),'guardian_name'=>ppa_db_meta($post->ID,'guardian_name'),
            'guardian_phone'=>ppa_db_meta($post->ID,'guardian_phone'),'whatsapp_phone'=>ppa_db_meta($post->ID,'whatsapp_phone'),
            'monthly_fee'=>(float)ppa_db_meta($post->ID,'monthly_fee',0),'details'=>$post->post_content,
            'status'=>'active','created_at'=>$created,'updated_at'=>$now
        ));
    } elseif ($type === 'ppa_batch') {
        $ok = $wpdb->replace($batches, array(
            'legacy_post_id'=>$post->ID,'batch_name'=>$post->post_title,
            'class_level'=>ppa_db_meta($post->ID,'class_level'),'max_capacity'=>(int)ppa_db_meta($post->ID,'max_capacity',0),
            'schedule_days'=>ppa_db_meta($post->ID,'schedule_days'),'time_slot'=>ppa_db_meta($post->ID,'time_slot'),
            'instructor'=>ppa_db_meta($post->ID,'instructor'),'details'=>$post->post_content,
            'status'=>'active','created_at'=>$created,'updated_at'=>$now
        ));
    } elseif ($type === 'ppa_fee_payment') {
        $student = $code !== '' ? ppa_db_find_student($code) : null;
        $student_id = $student ? (int)$student->id : 0;
        $paid = (float)ppa_db_meta($post->ID,'paid_amount',ppa_db_meta($post->ID,'amount',0));
        $payable = (float)ppa_db_meta($post->ID,'payable_amount',0);
        $due_value = ppa_db_meta($post->ID,'due_amount',null);
        $due = ($due_value === null || $due_value === '') ? max(0, $payable - $paid) : max(0, (float)$due_value);
        $status = $paid > 0 ? ($due <= 0 ? 'paid' : 'partial') : 'due';
        $ok = $wpdb->replace($fees, array(
            'legacy_post_id'=>$post->ID,'student_id'=>$student_id,'student_code'=>$code,
            'student_name'=>$post->post_title,'billing_month'=>ppa_db_meta($post->ID,'billing_month','Unknown'),
            'billing_year'=>(int)ppa_db_meta($post->ID,'billing_year',0),'payable_amount'=>$payable,
            'paid_amount'=>$paid,'due_amount'=>$due,'payment_status'=>$status,'details'=>$post->post_content,
            'created_at'=>$created,'updated_at'=>$now
        ));
        if ($ok === false) { return new WP_Error('ppa_db_fee_write', 'Could not save the fee record: ' . $wpdb->last_error); }
        $fee_id = (int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$fees} WHERE legacy_post_id = %d", $post->ID));
        $receipt = (string)ppa_db_meta($post->ID,'receipt_number');
        if ($receipt === '') { $receipt = 'PPA-' . wp_date('ym') . '-' . str_pad((string)$post->ID, 6, '0', STR_PAD_LEFT); }
        if ($paid > 0) {
            $ok = $wpdb->replace($transactions, array(
                'fee_record_id'=>$fee_id,'student_id'=>$student_id,'student_code'=>$code,
                'amount'=>$paid,'payment_method'=>ppa_db_meta($post->ID,'payment_method','Cash'),
                'transaction_reference'=>ppa_db_meta($post->ID,'transaction_reference'),
                'receipt_number'=>$receipt,'payment_date'=>ppa_db_safe_date(ppa_db_meta($post->ID,'payment_date')),
                'legacy_post_id'=>$post->ID,'created_at'=>$created
            ));
        } else {
            $wpdb->delete($transactions, array('legacy_post_id'=>$post->ID), array('%d'));
            $ok = true;
        }
    } elseif ($type === 'ppa_exam_result') {
        $student = $code !== '' ? ppa_db_find_student($code) : null;
        $ok = $wpdb->replace($results, array(
            'legacy_post_id'=>$post->ID,'student_id'=>$student ? (int)$student->id : 0,'student_code'=>$code,
            'student_name'=>$post->post_title,'exam_name'=>ppa_db_meta($post->ID,'exam_name'),
            'chapter'=>ppa_db_meta($post->ID,'chapter'),'marks'=>(float)ppa_db_meta($post->ID,'marks',0),
            'total_marks'=>(float)ppa_db_meta($post->ID,'total_marks',0),
            'highest_marks'=>(float)ppa_db_meta($post->ID,'highest_marks',0),'grade'=>ppa_db_meta($post->ID,'grade'),
            'exam_date'=>ppa_db_safe_date(ppa_db_meta($post->ID,'exam_date')),'details'=>$post->post_content,'created_at'=>$created
        ));
    } else {
        return new WP_Error('ppa_db_unsupported_type', 'This record type is not managed by Academy MySQL storage.');
    }
    if ($ok === false) {
        return new WP_Error('ppa_db_write_failed', 'Could not save the record to the Academy database: ' . $wpdb->last_error);
    }
    return true;
}

/** Keep the dedicated tables in step when a legacy record is permanently deleted. */
function ppa_db_delete_record($post_id) {
    global $wpdb;
    $post = get_post(absint($post_id));
    if (!$post) { return; }
    $students = $wpdb->prefix . 'ppa_students';
    $fees = $wpdb->prefix . 'ppa_fee_records';
    $transactions = $wpdb->prefix . 'ppa_payment_transactions';
    $results = $wpdb->prefix . 'ppa_exam_results';
    if ($post->post_type === 'ppa_student') {
        $student = $wpdb->get_row($wpdb->prepare("SELECT id, student_code FROM {$students} WHERE legacy_post_id = %d LIMIT 1", $post->ID));
        $student_code = $student ? (string)$student->student_code : (string)ppa_db_meta($post->ID,'student_id');
        $student_id = $student ? (int)$student->id : 0;
        if ($student_id) {
            $fee_ids = $wpdb->get_col($wpdb->prepare("SELECT id FROM {$fees} WHERE student_id = %d OR student_code = %s", $student_id, $student_code));
            if ($fee_ids) {
                foreach ($fee_ids as $fee_id) { $wpdb->delete($transactions, array('fee_record_id'=>(int)$fee_id), array('%d')); }
            }
            $wpdb->delete($fees, array('student_id'=>$student_id), array('%d'));
            $wpdb->delete($fees, array('student_code'=>$student_code), array('%s'));
            $wpdb->delete($transactions, array('student_id'=>$student_id), array('%d'));
            $wpdb->delete($transactions, array('student_code'=>$student_code), array('%s'));
            $wpdb->delete($results, array('student_id'=>$student_id), array('%d'));
            $wpdb->delete($results, array('student_code'=>$student_code), array('%s'));
            $wpdb->delete($students, array('id'=>$student_id), array('%d'));
        }
        // Remove legacy mirror posts too, so deleted students do not leave
        // orphan fee/result entries in wp_posts and wp_postmeta.
        if ($student_code !== '') {
            foreach (array('ppa_fee_payment','ppa_exam_result') as $related_type) {
                $related_posts = get_posts(array(
                    'post_type'=>$related_type,'post_status'=>array('publish','draft','private'),
                    'numberposts'=>-1,'meta_key'=>'_ppa_student_id','meta_value'=>$student_code
                ));
                foreach ($related_posts as $related_post) { wp_delete_post($related_post->ID, true); }
            }
        }
    } elseif ($post->post_type === 'ppa_fee_payment') {
        $wpdb->delete($transactions, array('legacy_post_id'=>$post->ID), array('%d'));
        $wpdb->delete($fees, array('legacy_post_id'=>$post->ID), array('%d'));
    } elseif ($post->post_type === 'ppa_batch') {
        $wpdb->delete($wpdb->prefix . 'ppa_batches', array('legacy_post_id'=>$post->ID), array('%d'));
    } elseif ($post->post_type === 'ppa_exam_result') {
        $wpdb->delete($results, array('legacy_post_id'=>$post->ID), array('%d'));
    }
}


/**
 * Read the admin's student/batch/fee/result lists from dedicated MySQL tables.
 * Legacy post IDs are retained as compatibility references for existing UI
 * actions, while displayed values come from the dedicated tables.
 */
function ppa_db_read_admin_records($post_type, $limit = 100) {
    global $wpdb;
    if (!get_option('ppa_db_legacy_migration_v1_complete')) { return null; }
    $limit = max(1, min(5000, absint($limit)));
    $students = $wpdb->prefix . 'ppa_students';
    $batches = $wpdb->prefix . 'ppa_batches';
    $fees = $wpdb->prefix . 'ppa_fee_records';
    $tx = $wpdb->prefix . 'ppa_payment_transactions';
    $results = $wpdb->prefix . 'ppa_exam_results';
    $sql = '';
    $meta_fields = array();
    $name_field = 'student_name';
    if ($post_type === 'ppa_student') {
        $sql = $wpdb->prepare("SELECT s.*, s.full_name AS display_name FROM {$students} s WHERE s.legacy_post_id IS NOT NULL ORDER BY s.created_at DESC, s.id DESC LIMIT %d", $limit);
        $meta_fields = array('student_id'=>'student_code','bangla_name'=>'bangla_name','class_level'=>'class_level','batch_name'=>'batch_name','institution'=>'institution','phone'=>'student_phone','guardian_name'=>'guardian_name','guardian_phone'=>'guardian_phone','whatsapp_phone'=>'whatsapp_phone','monthly_fee'=>'monthly_fee');
        $name_field = 'full_name';
    } elseif ($post_type === 'ppa_batch') {
        $sql = $wpdb->prepare("SELECT b.*, b.batch_name AS display_name FROM {$batches} b WHERE b.legacy_post_id IS NOT NULL ORDER BY b.created_at DESC, b.id DESC LIMIT %d", $limit);
        $meta_fields = array('class_level'=>'class_level','max_capacity'=>'max_capacity','schedule_days'=>'schedule_days','time_slot'=>'time_slot','instructor'=>'instructor');
        $name_field = 'batch_name';
    } elseif ($post_type === 'ppa_fee_payment') {
        $sql = $wpdb->prepare("SELECT f.*, f.student_name AS display_name, t.payment_method, t.transaction_reference, t.receipt_number, t.payment_date FROM {$fees} f LEFT JOIN {$tx} t ON t.legacy_post_id = f.legacy_post_id WHERE f.legacy_post_id IS NOT NULL ORDER BY f.created_at DESC, f.id DESC LIMIT %d", $limit);
        $meta_fields = array('student_id'=>'student_code','billing_month'=>'billing_month','billing_year'=>'billing_year','payable_amount'=>'payable_amount','paid_amount'=>'paid_amount','due_amount'=>'due_amount','payment_status'=>'payment_status','amount'=>'paid_amount','payment_method'=>'payment_method','transaction_reference'=>'transaction_reference','receipt_number'=>'receipt_number','payment_date'=>'payment_date');
        $name_field = 'student_name';
    } elseif ($post_type === 'ppa_exam_result') {
        $sql = $wpdb->prepare("SELECT r.*, r.student_name AS display_name FROM {$results} r WHERE r.legacy_post_id IS NOT NULL ORDER BY r.created_at DESC, r.id DESC LIMIT %d", $limit);
        $meta_fields = array('student_id'=>'student_code','exam_name'=>'exam_name','chapter'=>'chapter','marks'=>'marks','total_marks'=>'total_marks','highest_marks'=>'highest_marks','grade'=>'grade','exam_date'=>'exam_date');
        $name_field = 'student_name';
    } else {
        return null;
    }
    $rows = $wpdb->get_results($sql);
    if (!is_array($rows)) { return null; }
    $posts = array();
    if (!isset($GLOBALS['ppa_db_admin_meta']) || !is_array($GLOBALS['ppa_db_admin_meta'])) { $GLOBALS['ppa_db_admin_meta'] = array(); }
    foreach ($rows as $row) {
        $legacy_id = absint($row->legacy_post_id);
        if (!$legacy_id) { continue; }
        $post = get_post($legacy_id);
        if (!$post || $post->post_type !== $post_type || !in_array($post->post_status, array('publish','draft','private'), true)) { continue; }
        $GLOBALS['ppa_db_admin_meta'][$legacy_id] = array();
        foreach ($meta_fields as $meta_key=>$column) {
            $GLOBALS['ppa_db_admin_meta'][$legacy_id]['_ppa_' . $meta_key] = isset($row->$column) ? $row->$column : '';
        }
        $posts[] = clone $post;
        $last = count($posts) - 1;
        $posts[$last]->post_title = isset($row->$name_field) ? (string)$row->$name_field : $post->post_title;
        if (isset($row->details)) { $posts[$last]->post_content = (string)$row->details; }
    }
    return $posts;
}
function ppa_db_filter_legacy_post_meta($value, $object_id, $meta_key, $single) {
    if (!empty($GLOBALS['ppa_db_meta_bypass'])) { return $value; }
    $object_id = absint($object_id);
    if (!isset($GLOBALS['ppa_db_admin_meta'][$object_id]) || !array_key_exists($meta_key, $GLOBALS['ppa_db_admin_meta'][$object_id])) { return $value; }
    $stored = $GLOBALS['ppa_db_admin_meta'][$object_id][$meta_key];
    return $single ? $stored : array($stored);
}
add_filter('get_post_metadata', 'ppa_db_filter_legacy_post_meta', 10, 4);
function ppa_db_admin_record_count($post_type) {
    global $wpdb;
    if (!get_option('ppa_db_legacy_migration_v1_complete')) { return null; }
    $tables = array(
        'ppa_student'=>$wpdb->prefix . 'ppa_students',
        'ppa_batch'=>$wpdb->prefix . 'ppa_batches',
        'ppa_fee_payment'=>$wpdb->prefix . 'ppa_fee_records',
        'ppa_exam_result'=>$wpdb->prefix . 'ppa_exam_results'
    );
    if (!isset($tables[$post_type])) { return null; }
    $count = $wpdb->get_var("SELECT COUNT(*) FROM {$tables[$post_type]} WHERE legacy_post_id IS NOT NULL");
    return is_numeric($count) ? (int)$count : null;
}
