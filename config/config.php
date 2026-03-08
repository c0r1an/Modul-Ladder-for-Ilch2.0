<?php

namespace Modules\Ladder\Config;

use Ilch\Config\Install;

class Config extends Install
{
    public $config = [
        'key' => 'ladder',
        'version' => '1.3.0',
        'icon_small' => 'fa-solid fa-ranking-star',
        'author' => 'c0rian',
        'link' => 'https://github.com/c0r1an',
        'languages' => [
            'de_DE' => [
                'name' => 'Ladder',
                'description' => 'Punktebasierte Ladder mit Teilnehmern, Matches, Reporting und Disputes.',
            ],
            'en_EN' => [
                'name' => 'Ladder',
                'description' => 'Points-based ladder with participants, matches, reporting and disputes.',
            ],
        ],
        'boxes' => [
            'nextladdermatches' => [
                'de_DE' => ['name' => 'Nächste Ladder-Matches'],
                'en_EN' => ['name' => 'Next Ladder Matches'],
            ],
            'topladder' => [
                'de_DE' => ['name' => 'Top Ladder'],
                'en_EN' => ['name' => 'Top Ladder'],
            ],
        ],
        'ilchCore' => '2.2.0',
        'phpVersion' => '7.3',
        'folderRights' => [
            'storage'
        ]
    ];

    public function install()
    {
        $this->db()->queryMulti($this->getInstallSql());
    }

    public function uninstall()
    {
        $this->db()->drop('ladder_audit_log', true);
        $this->db()->drop('ladder_points_events', true);
        $this->db()->drop('ladder_match_disputes', true);
        $this->db()->drop('ladder_match_evidence', true);
        $this->db()->drop('ladder_match_reports', true);
        $this->db()->drop('ladder_matches', true);
        $this->db()->drop('ladder_member_profiles', true);
        $this->db()->drop('ladder_team_members', true);
        $this->db()->drop('ladder_participants', true);
        $this->db()->drop('ladder_ladders', true);
    }

    public function getInstallSql(): string
    {
        return 'CREATE TABLE IF NOT EXISTS `[prefix]_ladder_ladders` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(255) NOT NULL,
            `slug` VARCHAR(255) NULL DEFAULT NULL,
            `banner` VARCHAR(255) NULL DEFAULT NULL,
            `game` VARCHAR(255) NOT NULL,
            `team_size` INT(11) NOT NULL DEFAULT 5,
            `max_participants` INT(11) NOT NULL DEFAULT 16,
            `start_at` DATETIME NOT NULL,
            `end_at` DATETIME NULL DEFAULT NULL,
            `rules` MEDIUMTEXT NULL,
            `points_win` INT(11) NOT NULL DEFAULT 3,
            `points_draw` INT(11) NOT NULL DEFAULT 1,
            `points_loss` INT(11) NOT NULL DEFAULT 0,
            `status` VARCHAR(50) NOT NULL DEFAULT "draft",
            `created_by` INT(11) NOT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_ladder_ladders_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `[prefix]_ladder_participants` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `ladder_id` INT(11) NOT NULL,
            `team_name` VARCHAR(255) NOT NULL,
            `tag` VARCHAR(32) NULL DEFAULT NULL,
            `logo` VARCHAR(255) NULL DEFAULT NULL,
            `captain_user_id` INT(11) NOT NULL,
            `status` VARCHAR(20) NOT NULL DEFAULT "accepted",
            `players_count` INT(11) NOT NULL DEFAULT 0,
            `wins` INT(11) NOT NULL DEFAULT 0,
            `draws` INT(11) NOT NULL DEFAULT 0,
            `losses` INT(11) NOT NULL DEFAULT 0,
            `played` INT(11) NOT NULL DEFAULT 0,
            `points` INT(11) NOT NULL DEFAULT 0,
            `score_for` INT(11) NOT NULL DEFAULT 0,
            `score_against` INT(11) NOT NULL DEFAULT 0,
            `last_match_at` DATETIME NULL DEFAULT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_ladder_participants_name` (`ladder_id`, `team_name`),
            INDEX `idx_ladder_participants_status` (`status`),
            CONSTRAINT `fk_ladder_participants_ladder` FOREIGN KEY (`ladder_id`) REFERENCES `[prefix]_ladder_ladders` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `[prefix]_ladder_team_members` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `participant_id` INT(11) NOT NULL,
            `user_id` INT(11) NULL DEFAULT NULL,
            `nickname` VARCHAR(255) NULL DEFAULT NULL,
            `role` VARCHAR(20) NOT NULL DEFAULT "member",
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_ladder_team_members_participant_id` (`participant_id`),
            CONSTRAINT `fk_ladder_team_members_participant` FOREIGN KEY (`participant_id`) REFERENCES `[prefix]_ladder_participants` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `[prefix]_ladder_member_profiles` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `team_member_id` INT(11) NOT NULL,
            `full_name` VARCHAR(255) NULL DEFAULT NULL,
            `nickname` VARCHAR(255) NULL DEFAULT NULL,
            `age` INT(11) NULL DEFAULT NULL,
            `gender` VARCHAR(32) NULL DEFAULT NULL,
            `social_links` MEDIUMTEXT NULL,
            `bio` MEDIUMTEXT NULL,
            `games` MEDIUMTEXT NULL,
            `homepage` VARCHAR(500) NULL DEFAULT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_ladder_member_profiles_member` (`team_member_id`),
            CONSTRAINT `fk_ladder_member_profiles_member` FOREIGN KEY (`team_member_id`) REFERENCES `[prefix]_ladder_team_members` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `[prefix]_ladder_matches` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `ladder_id` INT(11) NOT NULL,
            `round` INT(11) NOT NULL DEFAULT 1,
            `match_no` INT(11) NOT NULL DEFAULT 1,
            `participant1_id` INT(11) NOT NULL,
            `participant2_id` INT(11) NOT NULL,
            `winner_participant_id` INT(11) NULL DEFAULT NULL,
            `score1` INT(11) NULL DEFAULT NULL,
            `score2` INT(11) NULL DEFAULT NULL,
            `best_of` INT(11) NOT NULL DEFAULT 1,
            `map` VARCHAR(255) NULL DEFAULT NULL,
            `scheduled_at` DATETIME NULL DEFAULT NULL,
            `status` VARCHAR(20) NOT NULL DEFAULT "ready",
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_ladder_matches_ladder` (`ladder_id`),
            INDEX `idx_ladder_matches_status` (`status`),
            CONSTRAINT `fk_ladder_matches_ladder` FOREIGN KEY (`ladder_id`) REFERENCES `[prefix]_ladder_ladders` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `[prefix]_ladder_match_reports` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `match_id` INT(11) NOT NULL,
            `reported_by_participant_id` INT(11) NOT NULL,
            `score1` INT(11) NOT NULL,
            `score2` INT(11) NOT NULL,
            `winner_participant_id` INT(11) NULL DEFAULT NULL,
            `comment` MEDIUMTEXT NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_ladder_match_reports_match` (`match_id`),
            CONSTRAINT `fk_ladder_match_reports_match` FOREIGN KEY (`match_id`) REFERENCES `[prefix]_ladder_matches` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `[prefix]_ladder_match_evidence` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `match_report_id` INT(11) NOT NULL,
            `type` VARCHAR(20) NOT NULL,
            `path_or_url` VARCHAR(500) NOT NULL,
            `note` VARCHAR(255) NULL DEFAULT NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_ladder_match_evidence_report` (`match_report_id`),
            CONSTRAINT `fk_ladder_match_evidence_report` FOREIGN KEY (`match_report_id`) REFERENCES `[prefix]_ladder_match_reports` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `[prefix]_ladder_match_disputes` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `match_id` INT(11) NOT NULL,
            `opened_by_participant_id` INT(11) NOT NULL,
            `reason` MEDIUMTEXT NOT NULL,
            `status` VARCHAR(20) NOT NULL DEFAULT "open",
            `resolved_by_user_id` INT(11) NULL DEFAULT NULL,
            `resolution_note` MEDIUMTEXT NULL,
            `resolved_at` DATETIME NULL DEFAULT NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_ladder_match_disputes_match` (`match_id`),
            INDEX `idx_ladder_match_disputes_status` (`status`),
            CONSTRAINT `fk_ladder_match_disputes_match` FOREIGN KEY (`match_id`) REFERENCES `[prefix]_ladder_matches` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `[prefix]_ladder_points_events` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `ladder_id` INT(11) NOT NULL,
            `match_id` INT(11) NOT NULL,
            `participant_id` INT(11) NOT NULL,
            `event_type` VARCHAR(20) NOT NULL,
            `points_delta` INT(11) NOT NULL,
            `balance_after` INT(11) NOT NULL,
            `payload_json` MEDIUMTEXT NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_ladder_points_events_ladder` (`ladder_id`),
            INDEX `idx_ladder_points_events_participant` (`participant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `[prefix]_ladder_audit_log` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `entity` VARCHAR(50) NOT NULL,
            `entity_id` INT(11) NOT NULL,
            `action` VARCHAR(100) NOT NULL,
            `data_json` MEDIUMTEXT NULL,
            `user_id` INT(11) NOT NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_ladder_audit_log_entity` (`entity`, `entity_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
    }

    public function getUpdate(string $installedVersion): string
    {
        switch ($installedVersion) {
            case "1.0.0":
                if (!$this->columnExists('ladder_participants', 'players_count')) {
                    $this->db()->query('ALTER TABLE `[prefix]_ladder_participants` ADD COLUMN `players_count` INT(11) NOT NULL DEFAULT 0 AFTER `status`;');
                }
                if (!$this->columnExists('ladder_matches', 'round')) {
                    $this->db()->query('ALTER TABLE `[prefix]_ladder_matches` ADD COLUMN `round` INT(11) NOT NULL DEFAULT 1 AFTER `ladder_id`;');
                }
                if (!$this->columnExists('ladder_matches', 'match_no')) {
                    $this->db()->query('ALTER TABLE `[prefix]_ladder_matches` ADD COLUMN `match_no` INT(11) NOT NULL DEFAULT 1 AFTER `round`;');
                }
                // no break
            case "1.1.0":
                if (!$this->tableExists('ladder_team_members')) {
                    $this->db()->query('CREATE TABLE `[prefix]_ladder_team_members` (
                        `id` INT(11) NOT NULL AUTO_INCREMENT,
                        `participant_id` INT(11) NOT NULL,
                        `user_id` INT(11) NULL DEFAULT NULL,
                        `nickname` VARCHAR(255) NULL DEFAULT NULL,
                        `role` VARCHAR(20) NOT NULL DEFAULT "member",
                        `created_at` DATETIME NOT NULL,
                        PRIMARY KEY (`id`),
                        INDEX `idx_ladder_team_members_participant_id` (`participant_id`),
                        CONSTRAINT `fk_ladder_team_members_participant` FOREIGN KEY (`participant_id`) REFERENCES `[prefix]_ladder_participants` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
                }

                if (!$this->tableExists('ladder_member_profiles')) {
                    $this->db()->query('CREATE TABLE `[prefix]_ladder_member_profiles` (
                        `id` INT(11) NOT NULL AUTO_INCREMENT,
                        `team_member_id` INT(11) NOT NULL,
                        `full_name` VARCHAR(255) NULL DEFAULT NULL,
                        `nickname` VARCHAR(255) NULL DEFAULT NULL,
                        `age` INT(11) NULL DEFAULT NULL,
                        `gender` VARCHAR(32) NULL DEFAULT NULL,
                        `social_links` MEDIUMTEXT NULL,
                        `bio` MEDIUMTEXT NULL,
                        `games` MEDIUMTEXT NULL,
                        `homepage` VARCHAR(500) NULL DEFAULT NULL,
                        `updated_at` DATETIME NOT NULL,
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `uniq_ladder_member_profiles_member` (`team_member_id`),
                        CONSTRAINT `fk_ladder_member_profiles_member` FOREIGN KEY (`team_member_id`) REFERENCES `[prefix]_ladder_team_members` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
                }

                $this->db()->query('INSERT INTO `[prefix]_ladder_team_members` (`participant_id`, `user_id`, `nickname`, `role`, `created_at`)
                    SELECT p.id, p.captain_user_id, u.name, "captain", NOW()
                    FROM `[prefix]_ladder_participants` p
                    LEFT JOIN `[prefix]_users` u ON u.id = p.captain_user_id
                    LEFT JOIN `[prefix]_ladder_team_members` tm ON tm.participant_id = p.id AND tm.role = "captain"
                    WHERE tm.id IS NULL;');

                $this->db()->query('UPDATE `[prefix]_ladder_participants` p
                    SET p.players_count = (
                        SELECT COUNT(*)
                        FROM `[prefix]_ladder_team_members` tm
                        WHERE tm.participant_id = p.id
                          AND tm.role IN ("captain", "member")
                    );');
                // no break
            case "1.2.0":
                if (!$this->columnExists('ladder_ladders', 'banner')) {
                    $this->db()->query('ALTER TABLE `[prefix]_ladder_ladders` ADD COLUMN `banner` VARCHAR(255) NULL DEFAULT NULL AFTER `slug`;');
                }
                // no break
        }

        return '"' . $this->config['key'] . '" Update-function executed.';
    }

    private function columnExists(string $table, string $column): bool
    {
        $dbName = $this->db()->queryCell("SELECT DATABASE()");
        return (bool)$this->db()->queryCell("SELECT EXISTS (
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = '" . $dbName . "'
              AND TABLE_NAME = '[prefix]_" . $table . "'
              AND COLUMN_NAME = '" . $column . "'
        )");
    }

    private function tableExists(string $table): bool
    {
        $dbName = $this->db()->queryCell("SELECT DATABASE()");
        return (bool)$this->db()->queryCell("SELECT EXISTS (
            SELECT 1
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = '" . $dbName . "'
              AND TABLE_NAME = '[prefix]_" . $table . "'
        )");
    }
}
