<?php
/**
 * 이 파일은 아이모듈 SMS모듈의 일부입니다. (https://www.imodules.io)
 *
 * SMS모듈 클래스 정의한다.
 *
 * @file /modules/sms/Sms.php
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2024. 10. 13.
 */
namespace modules\sms;
class Sms extends \Module
{
    /**
     * 모듈을 설정을 초기화한다.
     */
    public function init(): void
    {
    }

    /**
     * SMS를 전송하기 위한 전송자 클래스를 가져온다.
     *
     * @param \Component $component SMS를 전송하는 컴포넌트 객체
     * @return \modules\sms\Sender $sender
     */
    public function getSender(\Component $component): \modules\sms\Sender
    {
        return new \modules\sms\Sender($component);
    }
}
