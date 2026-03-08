<?php

namespace Modules\Ladder\Mappers;

use Ilch\Mapper;

class EvidenceMapper extends Mapper
{
    public function add(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return (int)$this->db()->insert('ladder_match_evidence')->values($data)->execute();
    }

    public function getByReportId(int $reportId): array
    {
        return $this->db()->select('*')
            ->from('ladder_match_evidence')
            ->where(['match_report_id' => $reportId])
            ->order(['id' => 'ASC'])
            ->execute()
            ->fetchRows() ?: [];
    }
}
