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

    /**
     * 휴대전화번호 구조체를 가져온다.
     *
     * @param string $cellphone 휴대전화번호
     * @param ?string $name 이름
     * @param ?int $member_id 회원고유값
     * @return \modules\sms\dtos\Cellphone $cellphone 휴대전화번호 구조체
     */
    public function getCellphone(
        string $cellphone,
        ?string $name = null,
        ?int $member_id = null
    ): \modules\sms\dtos\Cellphone {
        return new \modules\sms\dtos\Cellphone($cellphone, $name, $member_id);
    }

    /**
     * 회원정보를 통해 휴대전화번호 구조체를 가져온다.
     *
     * @param int $member_id 회원고유값
     * @return \modules\sms\dtos\Cellphone $cellphone 휴대전화번호 구조체
     */
    public function getCellphoneFromMember(int $member_id): \modules\sms\dtos\Cellphone
    {
        /**
         * @var \modules\member\Member $mMember
         */
        $mMember = \Modules::get('member');
        $member = $mMember->getMember($member_id);
        if ($member->getId() === 0) {
            \ErrorHandler::print($this->error('NOT_FOUND_MEMBER'));
        }

        return new \modules\sms\dtos\Cellphone(
            $member->getCellphone(),
            $member->getDisplayName(false),
            $member->getId()
        );
    }

    /**
     * 한글인 경우 2글자, 그 외 1글자로 계산한 한국 SMS 규격에 따른 본문내용 길이를 가져온다.
     *
     * @param string $content 본문내용
     * @return int $length 길이
     */
    public function getContentLength(string $content): int
    {
        return strlen(iconv('UTF-8', 'CP949//IGNORE', $content));
    }
}
