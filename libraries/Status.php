<?php

namespace Modules\Ladder\Libraries;

class Status
{
    public const LADDER_DRAFT = 'draft';
    public const LADDER_REGISTRATION_OPEN = 'registration_open';
    public const LADDER_REGISTRATION_CLOSED = 'registration_closed';
    public const LADDER_RUNNING = 'running';
    public const LADDER_FINISHED = 'finished';
    public const LADDER_ARCHIVED = 'archived';

    public const PARTICIPANT_PENDING = 'pending';
    public const PARTICIPANT_ACCEPTED = 'accepted';
    public const PARTICIPANT_REJECTED = 'rejected';
    public const PARTICIPANT_CHECKED_IN = 'checked_in';

    public const MATCH_PENDING = 'pending';
    public const MATCH_SCHEDULED = 'scheduled';
    public const MATCH_READY = 'ready';
    public const MATCH_REPORTED = 'reported';
    public const MATCH_DISPUTE = 'dispute';
    public const MATCH_DONE = 'done';

    public const DISPUTE_OPEN = 'open';
    public const DISPUTE_RESOLVED = 'resolved';
    public const DISPUTE_REJECTED = 'rejected';

    public static function ladderStatuses(): array
    {
        return [
            self::LADDER_DRAFT,
            self::LADDER_REGISTRATION_OPEN,
            self::LADDER_REGISTRATION_CLOSED,
            self::LADDER_RUNNING,
            self::LADDER_FINISHED,
            self::LADDER_ARCHIVED,
        ];
    }

    public static function matchStatuses(): array
    {
        return [
            self::MATCH_PENDING,
            self::MATCH_SCHEDULED,
            self::MATCH_READY,
            self::MATCH_REPORTED,
            self::MATCH_DISPUTE,
            self::MATCH_DONE,
        ];
    }
}
