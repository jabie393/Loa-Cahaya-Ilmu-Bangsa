<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ChatbotFaq;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChatbotFaqPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function view(AuthUser $authUser, ChatbotFaq $chatbotFaq): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function update(AuthUser $authUser, ChatbotFaq $chatbotFaq): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function delete(AuthUser $authUser, ChatbotFaq $chatbotFaq): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole('super_admin');
    }
}
