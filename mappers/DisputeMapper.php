<?php

namespace Modules\Ladder\Mappers;

use Ilch\Mapper;

class DisputeMapper extends Mapper
{
    public function create(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return (int)$this->db()->insert('ladder_match_disputes')->values($data)->execute();
    }

    public function getAll(string $status = ''): array
    {
        $query = $this->db()->select([
                'd.*',
                'm.ladder_id',
                'm.round',
                'm.match_no',
                'ladder_title' => 'l.title',
            ])
            ->from(['d' => 'ladder_match_disputes'])
            ->join(['m' => 'ladder_matches'], 'd.match_id = m.id', 'INNER')
            ->join(['l' => 'ladder_ladders'], 'm.ladder_id = l.id', 'INNER')
            ->order(['d.id' => 'DESC']);

        if ($status !== '') {
            $query->where(['d.status' => $status]);
        }

        return $query->execute()->fetchRows() ?: [];
    }

    public function getById(int $id): ?array
    {
        $row = $this->db()->select('*')
            ->from('ladder_match_disputes')
            ->where(['id' => $id])
            ->execute()
            ->fetchAssoc();

        return $row ?: null;
    }

    public function getByMatchId(int $matchId): array
    {
        return $this->db()->select('*')
            ->from('ladder_match_disputes')
            ->where(['match_id' => $matchId])
            ->order(['id' => 'DESC'])
            ->execute()
            ->fetchRows() ?: [];
    }

    public function updateById(int $id, array $data): void
    {
        if (!$data) {
            return;
        }

        $this->db()->update('ladder_match_disputes')->values($data)->where(['id' => $id])->execute();
    }

    public function delete(int $id): bool
    {
        return (bool)$this->db()->delete('ladder_match_disputes')
            ->where(['id' => $id])
            ->execute();
    }
}
