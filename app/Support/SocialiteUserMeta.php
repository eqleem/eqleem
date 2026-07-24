<?php

namespace App\Support;

use Laravel\Socialite\Contracts\User as SocialiteUser;

class SocialiteUserMeta
{
    /**
     * @return array{id: mixed, nickname: mixed, name: mixed, email: mixed, avatar: mixed}
     */
    public static function from(SocialiteUser $socialUser): array
    {
        return [
            'id' => $socialUser->getId(),
            'nickname' => $socialUser->getNickname(),
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            'avatar' => $socialUser->getAvatar(),
        ];
    }
}
