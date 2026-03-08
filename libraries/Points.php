<?php

namespace Modules\Ladder\Libraries;

use Modules\Ladder\Mappers\LadderMapper;
use Modules\Ladder\Mappers\MatchMapper;
use Modules\Ladder\Mappers\ParticipantMapper;

class Points
{
    public function recalculate(int $ladderId): void
    {
        $ladderMapper = new LadderMapper();
        $participantMapper = new ParticipantMapper();
        $matchMapper = new MatchMapper();

        $ladder = $ladderMapper->getById($ladderId);
        if (!$ladder) {
            return;
        }

        $participants = $participantMapper->getAcceptedByLadderId($ladderId);
        if (!$participants) {
            return;
        }

        $stats = [];
        foreach ($participants as $participant) {
            $stats[(int)$participant['id']] = [
                'wins' => 0,
                'draws' => 0,
                'losses' => 0,
                'played' => 0,
                'points' => 0,
                'score_for' => 0,
                'score_against' => 0,
                'last_match_at' => null,
            ];
        }

        $winPoints = (int)$ladder['points_win'];
        $drawPoints = (int)$ladder['points_draw'];
        $lossPoints = (int)$ladder['points_loss'];

        $doneMatches = $matchMapper->getDoneByLadderId($ladderId);
        foreach ($doneMatches as $match) {
            $p1 = (int)$match['participant1_id'];
            $p2 = (int)$match['participant2_id'];
            if (!isset($stats[$p1], $stats[$p2])) {
                continue;
            }

            if ($match['score1'] === null || $match['score2'] === null) {
                continue;
            }

            $score1 = (int)$match['score1'];
            $score2 = (int)$match['score2'];

            $stats[$p1]['played']++;
            $stats[$p2]['played']++;
            $stats[$p1]['score_for'] += $score1;
            $stats[$p1]['score_against'] += $score2;
            $stats[$p2]['score_for'] += $score2;
            $stats[$p2]['score_against'] += $score1;
            $stats[$p1]['last_match_at'] = $match['updated_at'] ?? date('Y-m-d H:i:s');
            $stats[$p2]['last_match_at'] = $match['updated_at'] ?? date('Y-m-d H:i:s');

            if ($score1 > $score2) {
                $stats[$p1]['wins']++;
                $stats[$p2]['losses']++;
                $stats[$p1]['points'] += $winPoints;
                $stats[$p2]['points'] += $lossPoints;
            } elseif ($score2 > $score1) {
                $stats[$p2]['wins']++;
                $stats[$p1]['losses']++;
                $stats[$p2]['points'] += $winPoints;
                $stats[$p1]['points'] += $lossPoints;
            } else {
                $stats[$p1]['draws']++;
                $stats[$p2]['draws']++;
                $stats[$p1]['points'] += $drawPoints;
                $stats[$p2]['points'] += $drawPoints;
            }
        }

        foreach ($stats as $participantId => $row) {
            $participantMapper->updateStats($participantId, $row);
        }
    }
}
