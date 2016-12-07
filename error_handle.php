# php-error-handle
php自定义方法捕获错误 只是范例，不可运行
<?php

//ini_set('display_errors','On');
error_reporting(E_ERROR);

function handle_error($errno, $errstr, $errfile, $errline){
    if($errno == E_ERROR) {
        $data['type'] = 'E_ERROR';
        $data['message'] = $errstr . ' no:' . $errno;
        $data['file'] = $errfile;
        $data['line'] = $errline;
        $data['trace'] = debug_backtrace();
        $result = serialize($data);
        insert_error_log(-1, $result);
    }
}
function shutdown_func(){
    $rs = error_get_last();
    if($rs && is_array($rs)) {
        $data['type'] = 'SHUTDOWN';
        $data['message'] = isset($rs['message']) ? 'result:'.$rs['message'] : '';
        $data['file'] = isset($rs['file']) ? 'file:'.$rs['file'] : '';
        $data['line'] = isset($rs['line']) ? 'line:'.$rs['line'] : '';
        $data['trace'] = debug_backtrace();
        $result = serialize($data);
        insert_error_log(0,$result);
        exit;
    }
}
function handle_exception(Exception $exception){
    $data['type'] = 'E_WARNING';
    $data['message'] = $exception->getMessage();
    $data['file'] = $exception->getFile();
    $data['line'] = $exception->getLine();
    $data['trace']  = $exception->getTrace();
    $result = serialize($data);
    insert_error_log(-2,$result);
}
function insert_error_log($status,$result){
   // trigger_error($result);
    global $execute_time;
    global $task_id;
    $dbCron = Db::getDbAdapter('test');
    $data['task_id'] = $task_id;
    $data['execute_time'] = $execute_time;
    $data['duration'] = time()-$execute_time;
    $data['status'] = $status;
    $data['record_time'] = time();
    $data['result_msg'] = $result;
    $dbCron->insert('error_log', $data);
   // email_tip_error($result);
}

set_error_handler('handle_error');
set_exception_handler('handle_exception');
register_shutdown_function("shutdown_func");
$execute_time = time();
