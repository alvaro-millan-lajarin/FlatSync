<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\NotificationService;

class MonthlySummary extends BaseCommand
{
    protected $group       = 'FlatSync';
    protected $name        = 'notify:monthly';
    protected $description = 'Send monthly summary email to all home members.';
    protected $usage       = 'notify:monthly [year] [month]';
    protected $arguments   = [
        'year'  => '(optional) Year, e.g. 2026',
        'month' => '(optional) Month number 1-12',
    ];

    public function run(array $params): void
    {
        $year  = isset($params[0]) ? (int)$params[0] : null;
        $month = isset($params[1]) ? (int)$params[1] : null;

        CLI::write('Sending monthly summaries...', 'yellow');

        $sent = (new NotificationService())->sendMonthlySummaries($year, $month);

        CLI::write("Done. {$sent} email(s) sent.", 'green');
    }
}
