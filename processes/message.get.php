<?php
/**
 * 이 파일은 아이모듈 SMS모듈의 일부입니다. (https://www.imodules.io)
 *
 * SMS 발송 목록을 가져온다.
 *
 * @file /modules/sms/processes/messages.get.php
 * @author youlapark <youlapark@naddle.net>
 * @license MIT License
 * @modified 2024. 10. 16.
 *
 * @var \modules\sms\Sms $me
 */
if (defined('__IM_PROCESS__') == false) {
    exit();
}
/**
 * 관리자권한이 존재하는지 확인한다.
 */
if ($me->getAdmin()->checkPermission('messages') == false) {
    $results->success = false;
    $results->message = $me->getErrorText('FORBIDDEN');
    return;
}

$message_id = Request::get('message_id', true);

$records = $me
    ->db()
    ->select()
    ->from($me->table('messages'))
    ->where('message_id', $message_id)
    ->getOne();

if ($records === null) {
    $results->success = true;
    $results->message = $me->getErrorText('NOT_FOUND_DATA');
    return;
}

$results->success = true;
$results->data = $records;
