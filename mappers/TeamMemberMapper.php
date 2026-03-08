<?php

namespace Modules\Ladder\Mappers;

use Ilch\Mapper;

class TeamMemberMapper extends Mapper
{
    public function getById(int $id): ?array
    {
        $row = $this->db()->select('*')
            ->from('ladder_team_members')
            ->where(['id' => $id])
            ->execute()
            ->fetchAssoc();

        return $row ?: null;
    }

    public function getByParticipantId(int $participantId): array
    {
        return $this->db()->select('*')
            ->from('ladder_team_members')
            ->where(['participant_id' => $participantId])
            ->order(['id' => 'ASC'])
            ->execute()
            ->fetchRows() ?: [];
    }

    public function countPlayers(int $participantId): int
    {
        $count = 0;
        foreach ($this->getByParticipantId($participantId) as $member) {
            if (in_array((string)$member['role'], ['captain', 'member'], true)) {
                $count++;
            }
        }

        return $count;
    }

    public function add(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return (int)$this->db()->insert('ladder_team_members')->values($data)->execute();
    }

    public function remove(int $id): void
    {
        $this->db()->delete('ladder_team_members')->where(['id' => $id])->execute();
    }

    public function isUserInParticipant(int $participantId, int $userId): bool
    {
        return (bool)$this->db()->select('COUNT(*)')
            ->from('ladder_team_members')
            ->where(['participant_id' => $participantId, 'user_id' => $userId])
            ->execute()
            ->fetchCell();
    }
}
