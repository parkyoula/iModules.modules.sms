<?php
/**
 * 이 파일은 아이모듈 SMS모듈의 일부입니다. (https://www.imodules.io)
 *
 * SMS 발송 목록을 가져온다.
 *
 * @file /modules/sms/processes/messages.get.php
 * @author youlapark <youlapark@naddle.net>
 * @license MIT License
 * @modified 2024. 10. 15.
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
$sorters = Request::getJson('sorters');
$start = Request::getInt('start') ?? 0;
$limit = Request::getInt('limit') ?? 50;
$filters = Request::getJson('filters');
$keyword = Request::get('keyword');

$records = $me
    ->db()
    ->select()
    ->from($me->table('messages'));

if ($filters !== null) {
    $records->setFilters($filters, 'AND', [
        'status' => 'status',
        'type' => 'type',
        'sended_at' => 'sended_at',
    ]);
}

if ($keyword !== null) {
    $records->where('(');
    $records->where('name', '%' . $keyword . '%', 'LIKE');
    $records->orWhere('cellphone', '%' . $keyword . '%', 'LIKE');
    $records->orWhere('sended_cellphone', '%' . $keyword . '%', 'LIKE');
    $records->where(')');
}

$total = $records->copy()->count();

if ($sorters !== null) {
    foreach ($sorters as $field => $direction) {
        $records->orderBy($field, $direction);
    }
}

$records = $records->limit($start, $limit)->get();

if ($records === null) {
    $results->success = true;
    $results->message = $me->getErrorText('NOT_FOUND_DATA');
    return;
}

$results->success = true;
$results->records = $records;
