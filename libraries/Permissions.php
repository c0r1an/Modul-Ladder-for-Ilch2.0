<?php

namespace Modules\Ladder\Libraries;

use Modules\User\Models\User;

class Permissions
{
    public const ADMIN = 'ladder_admin';
    public const MANAGE = 'ladder_manage';
    public const DISPUTE = 'ladder_dispute';
    public const TEAM_MANAGE = 'ladder_team_manage';
    public const REPORT = 'ladder_report';

    public static function canAdmin(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->isAdmin() || $user->hasAccess('module_ladder');
    }
}
