<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 07/12/2015
 * Time: 21:06
 */
/**
 * send mail
 */
function _sendmail($cp) {
    //$mail->SMTPDebug = 3;                               // Enable verbose debug output
    $to = $cp->email_domain? $cp->email_domain : ADMIN_EMAIL;

    $res = send_mail1($to, _post('subject'), _post('body'));
    ajax_output(strip_tags($res));
}
add_action('ajax_task_sendmail', '_sendmail');