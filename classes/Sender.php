<?php
/**
 * 이 파일은 아이모듈 SMS모듈의 일부입니다. (https://www.imodules.io)
 *
 * SMS 전송자 클래스를 정의한다.
 *
 * @file /modules/sms/classes/Sender.php
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2024. 10. 13.
 */
namespace modules\sms;
class Sender
{
    /**
     * @var \Component $_component 알림을 전송하는 컴포넌트 객체
     */
    private \Component $_component;

    /**
     * @var int $_member_id 수신자 회원고유값
     */
    private int $_member_id;

    /**
     * @var ?string $_cellphone 수신자 휴대전화번호
     */
    private ?string $_cellphone;

    /**
     * @var ?string $_name 수신자명
     */
    private ?string $_name;

    /**
     * @var string $_content 본문내용
     */
    private string $_content;

    /**
     * @var ?string $_from 발송자휴대전화번호
     */
    private ?string $_from = null;

    /**
     * @var string $_type 발송타입
     */
    private string $_type;

    /**
     * SMS 전송자 클래스를 정의한다.
     *
     * @param \Component $component 알림을 전송하는 컴포넌트 객체
     */
    public function __construct(\Component $component)
    {
        $this->_component = $component;
    }

    /**
     * 컴포넌트를 호출한다.
     *
     * @param \Component $component 알림을 전송하는 컴포넌트 객체
     */
    public function getComponent()
    {
        return $this->_component;
    }

    /**
     * 수신자를 설정한다.
     *
     * @param int $member_id 수신자 회원고유값 (0 인 경우 비회원으로 휴대전화번호를 추가로 설정해주어야 한다.)
     * @param ?string $cellphone 휴대전화번호
     * @param ?string $name 수신자명
     * @return \modules\sms\Sender $this
     */
    public function setTo(int $member_id, ?string $cellphone = null, ?string $name = null): \modules\sms\Sender
    {
        if ($member_id > 0 && ($cellphone !== null || $name !== null)) {
            /**
             * @var \modules\member\Member $mMember
             */
            $mMember = \Modules::get('member');
            $member = $mMember->getMember($member_id);

            $name ??= $member->getName();
            $cellphone ??= $member->getCellphone();
        }

        $this->_member_id = $member_id;
        $this->_cellphone = $cellphone;
        $this->_name = $name;

        return $this;
    }

    /**
     * 수신자휴대전화번호를 가져온다.
     *
     * @return string $cellphone
     */
    public function getCellphone(): string
    {
        return $this->_cellphone;
    }

    /**
     * 발송번호를 설정한다.
     *
     * @param ?string $cellphone 발송휴대전화번호
     * @return \modules\sms\Sender $this
     */
    public function setFrom(?string $cellphone): \modules\sms\Sender
    {
        $this->_from = $cellphone;
        return $this;
    }

    /**
     * 발송번호를 설정한다.
     *
     * @return ?string $from
     */
    public function getFrom(): ?string
    {
        return $this->_from;
    }

    /**
     * 본문내용을 설정한다.
     *
     * @param string $content 본문
     * @return \modules\sms\Sender $this
     */
    public function setContent(string $content): \modules\sms\Sender
    {
        $this->_content = $content;
        return $this;
    }

    /**
     * 본문내용을 가져온다.
     *
     * @return string $content
     */
    public function getContent(): string
    {
        return $this->_content;
    }

    /**
     * 발송타입을 가져온다.
     *
     * @return string $type 발송타입[SMS,LMS,KAKAO]
     */
    public function getType(): string
    {
        return $this->_type;
    }

    /**
     * 발송타입을 설정한다. [SMS, LMS, KAKAO]
     *
     * @param string $type 발송타입[SMS,LMS,KAKAO]
     */
    public function setType(string $type): void
    {
        $this->_type = $type;
    }

    /**
     * SMS를 전송한다.
     *
     * @param ?int $sended_at - 전송시각(NULL 인 경우 현재시각)
     * @return bool $success 성공여부
     */
    public function send(?int $sended_at = null): bool
    {
        if (isset($this->_member_id) == false || isset($this->_content) == false) {
            return false;
        }

        /**
         * @var \modules\sms\Sms $mSms
         */
        $mSms = \Modules::get('sms');

        $sended_at ??= time();

        /**
         * SMS 전송 플러그인을 이용하여 SMS 를 발송한다.
         */
        $success =
            \Events::fireEvent($mSms, 'send', [$this, $sended_at], 'NOTNULL') ??
            $mSms->getErrorText('NOT_FOUND_SENDER_PLUGIN');

        /**
         * 설정된 타입이 존재하지 않을 경우 문자열의 길이에 따라 $type을 LMS, SMS로 지정한다.
         */
        $type = $this->getType();
        if ($this->getType() === null) {
            $type = $mSms->getContentLength($this->_content) > 80 ? 'LMS' : 'SMS';
            $this->setType($type);
        }

        $message_id = \UUID::v1($this->_cellphone);
        $mSms
            ->db()
            ->insert($mSms->table('messages'), [
                'message_id' => $message_id,
                'member_id' => $this->_member_id,
                'cellphone' => $this->_cellphone,
                'name' => $this->_name,
                'component_type' => $this->_component->getType(),
                'component_name' => $this->_component->getName(),
                'content' => $this->_content,
                'sended_cellphone' => $this->_from,
                'sended_at' => $sended_at,
                'status' => $success === true ? 'TRUE' : 'FALSE',
                'response' => is_bool($success) == false ? \Format::toJson($success) : null,
                'type' => $type,
            ])
            ->execute();

        return $success === true;
    }
}
