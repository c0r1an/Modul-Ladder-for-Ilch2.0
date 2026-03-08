<?php

namespace Modules\Ladder\Mappers;

use Ilch\Mapper;

class LadderMapper extends Mapper
{
    public function getAll(array $where = [], array $order = ['start_at' => 'ASC']): array
    {
        return $this->db()->select('*')
            ->from('ladder_ladders')
            ->where($where)
            ->order($order)
            ->execute()
            ->fetchRows() ?: [];
    }

    public function getById(int $id): ?array
    {
        $row = $this->db()->select('*')
            ->from('ladder_ladders')
            ->where(['id' => $id])
            ->execute()
            ->fetchAssoc();
        return $row ?: null;
    }

    public function save(array $data, ?int $id = null): int
    {
        if ($id) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db()->update('ladder_ladders')->values($data)->where(['id' => $id])->execute();
            return $id;
        }

        $now = date('Y-m-d H:i:s');
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        return (int)$this->db()->insert('ladder_ladders')->values($data)->execute();
    }

    public function setStatus(int $id, string $status): void
    {
        $this->db()->update('ladder_ladders')
            ->values(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')])
            ->where(['id' => $id])
            ->execute();
    }

    public function delete(int $id): bool
    {
        return (bool)$this->db()->delete('ladder_ladders')->where(['id' => $id])->execute();
    }

    public function getRunningForBox(int $limit = 5): array
    {
        $limit = max(1, $limit);

        return $this->db()->select('*')
            ->from('ladder_ladders')
            ->where(['status' => 'running'])
            ->order(['start_at' => 'ASC'])
            ->limit($limit)
            ->execute()
            ->fetchRows() ?: [];
    }
}
