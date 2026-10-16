<?php

declare(strict_types=1);

return [
    'profile' => [
        'unlink_blocked_no_password' => '비밀번호가 설정되어 있지 않고 다른 연동 계정도 없어 해제할 수 없습니다. 먼저 비밀번호를 설정해주세요.',
        'unlink_success' => '소셜 계정 연동이 해제되었습니다.',
        'operation_failed' => '요청을 처리할 수 없습니다. 다시 시도해 주세요.',
    ],
    'too_many_attempts' => '요청이 너무 많습니다. :seconds초 후에 다시 시도해 주세요.',
    'registration_failed' => '소셜 회원가입을 완료할 수 없습니다. 다시 시도해 주세요.',
    'existing_account_link_required' => '이 소셜 계정으로 로그인을 완료할 수 없습니다. 기존 계정으로 로그인한 뒤 소셜 계정 연결을 진행해 주세요.',
    'totp' => [
        'reauthentication_required' => 'TOTP 등록·변경·해제에는 별도의 재인증이 필요합니다. 현재 안전한 재인증 경로가 없어 요청을 처리할 수 없습니다.',
        'enrollment_success' => 'TOTP가 활성화되었습니다.',
        'enrollment_failed' => 'TOTP 등록에 실패했습니다.',
        'disabled' => 'TOTP가 해제되었습니다.',
        'disable_failed' => 'TOTP 해제에 실패했습니다.',
    ],
];
