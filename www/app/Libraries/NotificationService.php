<?php

namespace App\Libraries;

use App\Models\HomeModel;
use App\Models\UserHomesModel;
use App\Models\ExpenseModel;
use App\Models\ChoreModel;

class NotificationService
{
    private const DEMO_DOMAIN = '@flatsync.internal';

    /**
     * Send monthly summary to every member of every home.
     * Pass $year/$month to target a specific month (defaults to previous month).
     */
    public function sendMonthlySummaries(?int $year = null, ?int $month = null): int
    {
        $month = $month ?? (int) date('m', strtotime('-1 month'));
        $year  = $year  ?? (int) date('Y', strtotime('-1 month'));

        $homeModel = new HomeModel();
        $uhModel   = new UserHomesModel();
        $homes     = $homeModel->findAll();

        $sent = 0;
        foreach ($homes as $home) {
            $members = $uhModel->getMembersOfHome($home['id']);
            if (empty($members)) continue;

            $stats = $this->computeStats((int)$home['id'], $year, $month, $members);
            if ($stats['total'] == 0 && $stats['chores_done'] == 0) continue;

            foreach ($members as $m) {
                if (!$this->canReceive($m['email'])) continue;
                $body = $this->summaryBody($m, $home['name'], $stats, $year, $month);
                $monthName = $this->monthName($month, $year);
                $this->send($m['email'], "[{$home['name']}] Resumen de {$monthName}", $body);
                $sent++;
            }
        }

        return $sent;
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function computeStats(int $homeId, int $year, int $month, array $members): array
    {
        $expModel  = new ExpenseModel();
        $chorModel = new ChoreModel();
        $memberIds = array_column($members, 'id');

        $expenses = $expModel
            ->select('amount, paid_by, split_with')
            ->where('home_id', $homeId)
            ->where('YEAR(date)', $year)
            ->where('MONTH(date)', $month)
            ->findAll();

        $total     = array_sum(array_column($expenses, 'amount'));
        $paid      = array_fill_keys($memberIds, 0.0);
        $shouldPay = array_fill_keys($memberIds, 0.0);

        foreach ($expenses as $e) {
            $payer = (int) $e['paid_by'];
            if (isset($paid[$payer])) $paid[$payer] += (float) $e['amount'];

            $parts = $e['split_with'] ? array_map('intval', json_decode($e['split_with'], true)) : $memberIds;
            $count = count($parts);
            if ($count === 0) continue;
            $share = (float) $e['amount'] / $count;
            foreach ($parts as $uid) {
                if (isset($shouldPay[$uid])) $shouldPay[$uid] += $share;
            }
        }

        $balances = [];
        foreach ($members as $m) {
            $uid = (int) $m['id'];
            $balances[$uid] = [
                'username' => $m['username'],
                'paid'     => $paid[$uid] ?? 0,
                'balance'  => ($paid[$uid] ?? 0) - ($shouldPay[$uid] ?? 0),
            ];
        }

        // Top 3 expenses
        $topExpenses = $expModel
            ->select('expenses.title, expenses.amount, users.username AS paid_by_name')
            ->join('users', 'users.id = expenses.paid_by')
            ->where('expenses.home_id', $homeId)
            ->where('YEAR(expenses.date)', $year)
            ->where('MONTH(expenses.date)', $month)
            ->orderBy('expenses.amount', 'DESC')
            ->limit(3)
            ->findAll();

        $choresDone = $chorModel
            ->where('home_id', $homeId)
            ->where('status', 'done')
            ->where('YEAR(due_date)', $year)
            ->where('MONTH(due_date)', $month)
            ->countAllResults();

        $choresPending = $chorModel
            ->where('home_id', $homeId)
            ->where('status', 'pending')
            ->where('YEAR(due_date)', $year)
            ->where('MONTH(due_date)', $month)
            ->countAllResults();

        return [
            'total'          => $total,
            'expense_count'  => count($expenses),
            'balances'       => $balances,
            'top_expenses'   => $topExpenses,
            'chores_done'    => $choresDone,
            'chores_pending' => $choresPending,
        ];
    }

    private function summaryBody(array $member, string $homeName, array $stats, int $year, int $month): string
    {
        $monthLabel  = $this->monthName($month, $year);
        $home        = htmlspecialchars($homeName);
        $name        = htmlspecialchars($member['username']);
        $total       = number_format($stats['total'], 2);
        $expCount    = $stats['expense_count'];
        $myBalance   = $stats['balances'][(int)$member['id']]['balance'] ?? 0;
        $balanceSign = $myBalance >= 0 ? '+' : '';
        $balanceColor = $myBalance >= 0 ? '#16a34a' : '#dc2626';
        $dashUrl     = rtrim(base_url(), '/') . '/dashboard';

        // Balance rows
        $balanceRows = '';
        foreach ($stats['balances'] as $b) {
            $sign  = $b['balance'] >= 0 ? '+' : '';
            $color = $b['balance'] >= 0 ? '#16a34a' : '#dc2626';
            $balanceRows .= "
              <tr>
                <td style='padding:8px 0;font-size:14px;color:#333'>" . htmlspecialchars($b['username']) . "</td>
                <td style='padding:8px 0;font-size:14px;color:#888;text-align:right'>€" . number_format($b['paid'], 2) . " pagado</td>
                <td style='padding:8px 0;font-size:14px;font-weight:700;color:{$color};text-align:right'>{$sign}€" . number_format(abs($b['balance']), 2) . "</td>
              </tr>";
        }

        // Top expenses
        $topRows = '';
        foreach ($stats['top_expenses'] as $e) {
            $topRows .= "
              <div style='display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f0f0f0;font-size:13px'>
                <span style='color:#333'>" . htmlspecialchars($e['title']) . " <span style='color:#aaa'>· " . htmlspecialchars($e['paid_by_name']) . "</span></span>
                <span style='font-weight:600;color:#111'>€" . number_format($e['amount'], 2) . "</span>
              </div>";
        }

        $content = "
          <h2 style='margin:0 0 4px;font-size:20px;color:#111'>Resumen de {$monthLabel}</h2>
          <p style='color:#888;font-size:13px;margin:0 0 28px'>{$home}</p>

          <!-- My balance highlight -->
          <div style='background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:20px;margin-bottom:24px;text-align:center'>
            <div style='font-size:12px;font-weight:600;color:#3b82f6;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px'>Tu balance del mes</div>
            <div style='font-size:32px;font-weight:800;color:{$balanceColor}'>{$balanceSign}€" . number_format(abs($myBalance), 2) . "</div>
            <div style='font-size:12px;color:#888;margin-top:4px'>" . ($myBalance >= 0 ? 'El hogar te debe' : 'Debes al hogar') . "</div>
          </div>

          <!-- Total + chores row -->
          <div style='display:flex;gap:12px;margin-bottom:24px'>
            <div style='flex:1;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px;text-align:center'>
              <div style='font-size:22px;font-weight:800;color:#111'>€{$total}</div>
              <div style='font-size:11px;color:#888;margin-top:3px'>{$expCount} gastos</div>
            </div>
            <div style='flex:1;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px;text-align:center'>
              <div style='font-size:22px;font-weight:800;color:#15803d'>{$stats['chores_done']}</div>
              <div style='font-size:11px;color:#888;margin-top:3px'>tareas completadas</div>
            </div>
          </div>

          <!-- Balance table -->
          <div style='margin-bottom:24px'>
            <div style='font-size:12px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px'>Balance por persona</div>
            <table style='width:100%;border-collapse:collapse'>
              {$balanceRows}
            </table>
          </div>

          <!-- Top expenses -->
          " . ($topRows ? "
          <div style='margin-bottom:24px'>
            <div style='font-size:12px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px'>Mayores gastos</div>
            {$topRows}
          </div>" : "") . "

          <a href='{$dashUrl}' style='display:block;text-align:center;background:#2563eb;color:#fff;text-decoration:none;padding:13px;border-radius:8px;font-weight:600;font-size:14px'>Ver dashboard completo</a>";

        return $this->wrap($content);
    }

    private function monthName(int $month, int $year): string
    {
        $names = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        return ($names[$month - 1] ?? '') . ' ' . $year;
    }

    private function canReceive(string $email): bool
    {
        return !str_contains($email, self::DEMO_DOMAIN) && filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    private function send(string $to, string $subject, string $body): void
    {
        try {
            $svc = \Config\Services::email();
            $svc->setFrom(
                getenv('MAIL_FROM_ADDRESS') ?: 'noreply@flatsync.es',
                getenv('MAIL_FROM_NAME')    ?: 'FlatSync'
            );
            $svc->setTo($to);
            $svc->setSubject($subject);
            $svc->setMessage($body);
            $svc->send();
        } catch (\Throwable $e) {
            log_message('error', 'NotificationService::send failed: ' . $e->getMessage());
        }
    }

    private function header(): string
    {
        return '
        <div style="background:linear-gradient(135deg,#1e3a5f,#2563eb);padding:32px 40px;text-align:center">
          <span style="font-size:26px;font-weight:800;color:#fff;letter-spacing:-1px">flat<span style="color:#93c5fd">sync</span></span>
        </div>';
    }

    private function footer(): string
    {
        return '
        <div style="padding:20px 40px;border-top:1px solid #f0f0f0;text-align:center">
          <p style="margin:0;font-size:12px;color:#aaa">Recibes este resumen mensual porque eres miembro de un hogar en FlatSync.</p>
        </div>';
    }

    private function wrap(string $content): string
    {
        return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
        <body style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',sans-serif;background:#f4f4f4;margin:0;padding:0">
        <div style="max-width:540px;margin:40px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08)">
          ' . $this->header() . '
          <div style="padding:32px 40px">' . $content . '</div>
          ' . $this->footer() . '
        </div></body></html>';
    }
}
