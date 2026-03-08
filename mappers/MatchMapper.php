<?php

namespace Modules\Ladder\Mappers;

use Ilch\Mapper;

class MatchMapper extends Mapper
{
    public function getById(int $id): ?array
    {
        $row = $this->db()->select([
                'm.*',
                'participant1_name' => 'p1.team_name',
                'participant2_name' => 'p2.team_name',
                'participant1_tag' => 'p1.tag',
                'participant2_tag' => 'p2.tag',
                'participant1_logo' => 'p1.logo',
                'participant2_logo' => 'p2.logo',
            ])
            ->from(['m' => 'ladder_matches'])
            ->join(['p1' => 'ladder_participants'], 'm.participant1_id = p1.id', 'LEFT')
            ->join(['p2' => 'ladder_participants'], 'm.participant2_id = p2.id', 'LEFT')
            ->where(['m.id' => $id])
            ->execute()
            ->fetchAssoc();

        return $row ?: null;
    }

    public function getByLadderId(int $ladderId): array
    {
        return $this->db()->select([
                'm.*',
                'participant1_name' => 'p1.team_name',
                'participant2_name' => 'p2.team_name',
                'participant1_tag' => 'p1.tag',
                'participant2_tag' => 'p2.tag',
                'participant1_logo' => 'p1.logo',
                'participant2_logo' => 'p2.logo',
            ])
            ->from(['m' => 'ladder_matches'])
            ->join(['p1' => 'ladder_participants'], 'm.participant1_id = p1.id', 'LEFT')
            ->join(['p2' => 'ladder_participants'], 'm.participant2_id = p2.id', 'LEFT')
            ->where(['m.ladder_id' => $ladderId])
            ->order(['m.round' => 'ASC', 'm.match_no' => 'ASC'])
            ->execute()
            ->fetchRows() ?: [];
    }

    public function getByParticipantId(int $participantId): array
    {
        $sql = 'SELECT m.*,
                    p1.team_name AS participant1_name,
                    p2.team_name AS participant2_name,
                    p1.tag AS participant1_tag,
                    p2.tag AS participant2_tag,
                    p1.logo AS participant1_logo,
                    p2.logo AS participant2_logo
                FROM [prefix]_ladder_matches m
                LEFT JOIN [prefix]_ladder_participants p1 ON m.participant1_id = p1.id
                LEFT JOIN [prefix]_ladder_participants p2 ON m.participant2_id = p2.id
                WHERE m.participant1_id = ' . (int)$participantId . '
                   OR m.participant2_id = ' . (int)$participantId . '
                ORDER BY m.scheduled_at DESC, m.id DESC';

        return $this->db()->queryArray($sql) ?: [];
    }

    public function getGroupedByRound(int $ladderId): array
    {
        $matches = $this->getByLadderId($ladderId);
        $rounds = [];
        foreach ($matches as $match) {
            $rounds[(int)$match['round']][] = $match;
        }

        ksort($rounds);
        return $rounds;
    }

    public function getDoneByLadderId(int $ladderId): array
    {
        return $this->db()->select('*')
            ->from('ladder_matches')
            ->where(['ladder_id' => $ladderId, 'status' => 'done'])
            ->order(['updated_at' => 'ASC', 'id' => 'ASC'])
            ->execute()
            ->fetchRows() ?: [];
    }

    public function countByLadderId(int $ladderId): int
    {
        return (int)$this->db()->select('COUNT(*)')
            ->from('ladder_matches')
            ->where(['ladder_id' => $ladderId])
            ->execute()
            ->fetchCell();
    }

    public function create(array $data): int
    {
        $now = date('Y-m-d H:i:s');
        $data['created_at'] = $now;
        $data['updated_at'] = $now;

        return (int)$this->db()->insert('ladder_matches')->values($data)->execute();
    }

    public function update(int $id, array $data): void
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db()->update('ladder_matches')->values($data)->where(['id' => $id])->execute();
    }

    public function deleteByLadderId(int $ladderId): void
    {
        $this->db()->delete('ladder_matches')->where(['ladder_id' => $ladderId])->execute();
    }

    public function getUpcomingForBox(int $limit = 5): array
    {
        $limit = max(1, $limit);
        $sql = 'SELECT 
                    m.id,
                    m.ladder_id,
                    m.round,
                    m.match_no,
                    m.scheduled_at,
                    m.status,
                    l.title AS ladder_title,
                    l.game AS ladder_game,
                    p1.tag AS participant1_tag,
                    p2.tag AS participant2_tag,
                    p1.logo AS participant1_logo,
                    p2.logo AS participant2_logo
                FROM [prefix]_ladder_matches m
                INNER JOIN [prefix]_ladder_ladders l ON l.id = m.ladder_id
                LEFT JOIN [prefix]_ladder_participants p1 ON p1.id = m.participant1_id
                LEFT JOIN [prefix]_ladder_participants p2 ON p2.id = m.participant2_id
                WHERE m.status IN ("scheduled", "ready")
                  AND l.status IN ("running", "registration_closed")
                ORDER BY (m.scheduled_at IS NULL) ASC, m.scheduled_at ASC, m.round ASC, m.match_no ASC
                LIMIT ' . (int)$limit;

        return $this->db()->queryArray($sql) ?: [];
    }
}
