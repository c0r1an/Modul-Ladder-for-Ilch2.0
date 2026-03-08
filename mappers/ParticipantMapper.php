<?php

namespace Modules\Ladder\Mappers;

use Ilch\Mapper;

class ParticipantMapper extends Mapper
{
    public function getById(int $id): ?array
    {
        $row = $this->db()->select('*')->from('ladder_participants')->where(['id' => $id])->execute()->fetchAssoc();
        return $row ?: null;
    }

    public function getByLadderId(int $ladderId): array
    {
        return $this->db()->select([
                'p.*',
                'captain_name' => 'u.name',
            ])
            ->from(['p' => 'ladder_participants'])
            ->join(['u' => 'users'], 'u.id = p.captain_user_id', 'LEFT')
            ->where(['p.ladder_id' => $ladderId])
            ->order(['p.id' => 'ASC'])
            ->execute()
            ->fetchRows() ?: [];
    }

    public function getAcceptedByLadderId(int $ladderId): array
    {
        return $this->db()->queryArray('SELECT *
            FROM [prefix]_ladder_participants
            WHERE ladder_id = ' . (int)$ladderId . ' AND status IN ("accepted", "checked_in")
            ORDER BY points DESC, wins DESC, team_name ASC') ?: [];
    }

    public function getStandingsByLadderId(int $ladderId): array
    {
        return $this->db()->queryArray('SELECT *,
                (`score_for` - `score_against`) AS score_diff
            FROM [prefix]_ladder_participants
            WHERE `ladder_id` = ' . (int)$ladderId . ' AND `status` IN ("accepted", "checked_in")
            ORDER BY `points` DESC, score_diff DESC, `wins` DESC, `team_name` ASC') ?: [];
    }

    public function getByCaptainAndLadder(int $userId, int $ladderId): ?array
    {
        $row = $this->db()->select('*')
            ->from('ladder_participants')
            ->where(['ladder_id' => $ladderId, 'captain_user_id' => $userId])
            ->execute()
            ->fetchAssoc();
        return $row ?: null;
    }

    public function getByLadderAndCaptain(int $ladderId, int $captainId): ?array
    {
        return $this->getByCaptainAndLadder($captainId, $ladderId);
    }

    public function getByCaptain(int $userId): array
    {
        return $this->db()->select('*')
            ->from('ladder_participants')
            ->where(['captain_user_id' => $userId])
            ->order(['id' => 'DESC'])
            ->execute()
            ->fetchRows() ?: [];
    }

    public function create(array $data): int
    {
        $now = date('Y-m-d H:i:s');
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        return (int)$this->db()->insert('ladder_participants')->values($data)->execute();
    }

    public function save(array $data, ?int $id = null): int
    {
        if ($id) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db()->update('ladder_participants')->values($data)->where(['id' => $id])->execute();
            return $id;
        }

        return $this->create($data);
    }

    public function updateStatus(int $id, string $status): void
    {
        $this->db()->update('ladder_participants')
            ->values(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')])
            ->where(['id' => $id])
            ->execute();
    }

    public function countAcceptedByLadder(int $ladderId): int
    {
        return (int)$this->db()->queryCell('SELECT COUNT(*)
            FROM [prefix]_ladder_participants
            WHERE ladder_id = ' . (int)$ladderId . ' AND status IN ("accepted", "checked_in")');
    }

    public function isCaptain(int $participantId, int $userId): bool
    {
        return (bool)$this->db()->select('COUNT(*)')
            ->from('ladder_participants')
            ->where(['id' => $participantId, 'captain_user_id' => $userId])
            ->execute()
            ->fetchCell();
    }

    public function delete(int $id): bool
    {
        return (bool)$this->db()->delete('ladder_participants')
            ->where(['id' => $id])
            ->execute();
    }

    public function updatePlayersCount(int $id, int $playersCount): void
    {
        $this->db()->update('ladder_participants')
            ->values([
                'players_count' => max(1, $playersCount),
                'updated_at' => date('Y-m-d H:i:s'),
            ])
            ->where(['id' => $id])
            ->execute();
    }

    public function updateStats(int $id, array $stats): void
    {
        $stats['updated_at'] = date('Y-m-d H:i:s');
        $this->db()->update('ladder_participants')->values($stats)->where(['id' => $id])->execute();
    }

    public function getTopForBox(int $limit = 5): array
    {
        $limit = max(1, $limit);
        return $this->db()->queryArray('SELECT p.*, l.title AS ladder_title
            FROM [prefix]_ladder_participants p
            INNER JOIN [prefix]_ladder_ladders l ON l.id = p.ladder_id
            WHERE l.status = "running" AND p.status IN ("accepted", "checked_in")
            ORDER BY p.points DESC, p.wins DESC, p.team_name ASC
            LIMIT ' . (int)$limit) ?: [];
    }
}
