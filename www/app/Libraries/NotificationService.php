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

        // By category
        $byCategory = $expModel
            ->select('category, SUM(amount) AS total')
            ->where('home_id', $homeId)
            ->where('YEAR(date)', $year)
            ->where('MONTH(date)', $month)
            ->groupBy('category')
            ->orderBy('total', 'DESC')
            ->findAll();

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

        $choresMissed = $chorModel
            ->where('home_id', $homeId)
            ->where('status', 'missed')
            ->where('YEAR(due_date)', $year)
            ->where('MONTH(due_date)', $month)
            ->countAllResults();

        // Chores done/missed per member
        $choresByMember = [];
        foreach ($members as $m) {
            $done = $chorModel
                ->where('home_id', $homeId)
                ->where('assigned_user_id', $m['id'])
                ->where('status', 'done')
                ->where('YEAR(due_date)', $year)
                ->where('MONTH(due_date)', $month)
                ->countAllResults();
            $missed = $chorModel
                ->where('home_id', $homeId)
                ->where('assigned_user_id', $m['id'])
                ->where('status', 'missed')
                ->where('YEAR(due_date)', $year)
                ->where('MONTH(due_date)', $month)
                ->countAllResults();
            if ($done > 0 || $missed > 0) {
                $choresByMember[] = ['username' => $m['username'], 'done' => $done, 'missed' => $missed];
            }
        }

        return [
            'total'            => $total,
            'expense_count'    => count($expenses),
            'member_count'     => count($members),
            'balances'         => $balances,
            'by_category'      => $byCategory,
            'top_expenses'     => $topExpenses,
            'chores_done'      => $choresDone,
            'chores_missed'    => $choresMissed,
            'chores_by_member' => $choresByMember,
        ];
    }

    private function summaryBody(array $member, string $homeName, array $stats, int $year, int $month): string
    {
        $monthLabel   = $this->monthName($month, $year);
        $home         = htmlspecialchars($homeName);
        $total        = number_format($stats['total'], 2);
        $perPerson    = $stats['member_count'] > 0
            ? number_format($stats['total'] / $stats['member_count'], 2) : '0.00';
        $myUid        = (int) $member['id'];
        $myBalance    = $stats['balances'][$myUid]['balance'] ?? 0;
        $balSign      = $myBalance >= 0 ? '+' : '';
        $balColor     = $myBalance >= 0 ? '#16a34a' : '#dc2626';
        $dashUrl      = rtrim(base_url(), '/') . '/dashboard';

        $catColors  = ['food'=>'#F59E0B','cleaning'=>'#4F80FF','bills'=>'#6366F1','other'=>'#4ECDC4'];
        $catLabels  = ['food'=>'Comida','cleaning'=>'Limpieza','bills'=>'Facturas','other'=>'Otros'];
        $catFb      = ['#94A3B8','#64748B','#475569','#334155'];

        // ── SECTION: 4 stat boxes ──────────────────────────────────────────
        $statsBox = "
        <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse;margin-bottom:24px'>
          <tr>
            <td width='50%' style='padding:0 5px 8px 0'>
              <div style='background:#eff6ff;border-radius:10px;padding:16px;text-align:center'>
                <div style='font-size:22px;font-weight:800;color:#1d4ed8'>€{$total}</div>
                <div style='font-size:11px;color:#64748b;margin-top:3px'>{$stats['expense_count']} gastos</div>
              </div>
            </td>
            <td width='50%' style='padding:0 0 8px 5px'>
              <div style='background:#fafafa;border:1px solid #e2e8f0;border-radius:10px;padding:16px;text-align:center'>
                <div style='font-size:22px;font-weight:800;color:#334155'>€{$perPerson}</div>
                <div style='font-size:11px;color:#64748b;margin-top:3px'>por persona</div>
              </div>
            </td>
          </tr>
          <tr>
            <td style='padding:0 5px 0 0'>
              <div style='background:#f0fdf4;border-radius:10px;padding:16px;text-align:center'>
                <div style='font-size:22px;font-weight:800;color:#15803d'>{$stats['chores_done']}</div>
                <div style='font-size:11px;color:#64748b;margin-top:3px'>tareas realizadas</div>
              </div>
            </td>
            <td style='padding:0 0 0 5px'>
              <div style='background:#fef2f2;border-radius:10px;padding:16px;text-align:center'>
                <div style='font-size:22px;font-weight:800;color:#dc2626'>{$stats['chores_missed']}</div>
                <div style='font-size:11px;color:#64748b;margin-top:3px'>tareas fallidas</div>
              </div>
            </td>
          </tr>
        </table>";

        // ── SECTION: Por categoría ─────────────────────────────────────────
        $catSection = '';
        if (!empty($stats['by_category'])) {
            $maxCat  = max(array_column($stats['by_category'], 'total'));
            $catRows = '';
            $fi = 0;
            foreach ($stats['by_category'] as $cat) {
                $pct = $maxCat > 0 ? round($cat['total'] / $maxCat * 100) : 0;
                $col = $catColors[$cat['category']] ?? $catFb[$fi % 4];
                $lbl = $catLabels[$cat['category']] ?? htmlspecialchars($cat['category']);
                $pctTotal = round($cat['total'] / max($stats['total'], 1) * 100);
                $fi++;
                $catRows .= "
                <div style='margin-bottom:10px'>
                  <table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:5px'>
                    <tr>
                      <td style='font-size:12px;color:#555'>{$lbl}</td>
                      <td style='text-align:right;font-size:12px;font-weight:700;color:#111'>
                        €" . number_format($cat['total'], 2) . "
                        <span style='color:#aaa;font-weight:400'> {$pctTotal}%</span>
                      </td>
                    </tr>
                  </table>
                  <div style='background:#f1f5f9;border-radius:4px;height:8px;overflow:hidden'>
                    <div style='background:{$col};height:8px;width:{$pct}%;border-radius:4px'></div>
                  </div>
                </div>";
            }
            $catSection = "
            <div style='margin-bottom:24px'>
              <div style='font-size:11px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.07em;margin-bottom:12px'>Por categoría</div>
              {$catRows}
            </div>";
        }

        // ── SECTION: Gastos por miembro ────────────────────────────────────
        $memberSection = '';
        if (!empty($stats['balances'])) {
            $maxPaid = max(array_map(fn($b) => $b['paid'], $stats['balances']) ?: [1]);
            $mRows   = '';
            foreach ($stats['balances'] as $b) {
                $pct   = $maxPaid > 0 ? round($b['paid'] / $maxPaid * 100) : 0;
                $sign  = $b['balance'] >= 0 ? '+' : '';
                $col   = $b['balance'] >= 0 ? '#16a34a' : '#dc2626';
                $mRows .= "
                <div style='margin-bottom:14px'>
                  <table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:6px'>
                    <tr>
                      <td style='font-size:13px;color:#333;font-weight:600'>" . htmlspecialchars($b['username']) . "</td>
                      <td style='text-align:right;font-size:12px'>
                        <span style='color:#888'>€" . number_format($b['paid'], 2) . " pagado</span>
                        <span style='font-weight:700;color:{$col};margin-left:8px'>{$sign}€" . number_format(abs($b['balance']), 2) . "</span>
                      </td>
                    </tr>
                  </table>
                  <div style='background:#f1f5f9;border-radius:4px;height:7px;overflow:hidden'>
                    <div style='background:#3b82f6;height:7px;width:{$pct}%;border-radius:4px'></div>
                  </div>
                </div>";
            }
            $memberSection = "
            <div style='margin-bottom:24px'>
              <div style='font-size:11px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.07em;margin-bottom:12px'>Gastos por miembro</div>
              {$mRows}
            </div>";
        }

        // ── SECTION: Tareas por miembro ────────────────────────────────────
        $choresSection = '';
        if (!empty($stats['chores_by_member'])) {
            $cRows = '';
            foreach ($stats['chores_by_member'] as $cm) {
                $tot     = $cm['done'] + $cm['missed'];
                $dPct    = $tot > 0 ? round($cm['done']   / $tot * 100) : 0;
                $mPct    = $tot > 0 ? round($cm['missed'] / $tot * 100) : 0;
                // stacked bar via table (email-safe)
                $doneTd   = $dPct > 0
                    ? "<td width='{$dPct}%'  style='background:#22c55e;height:8px;font-size:0;line-height:0'>&nbsp;</td>" : '';
                $missedTd = $mPct > 0
                    ? "<td width='{$mPct}%'  style='background:#ef4444;height:8px;font-size:0;line-height:0'>&nbsp;</td>" : '';
                $restPct  = 100 - $dPct - $mPct;
                $restTd   = $restPct > 0
                    ? "<td style='background:#f1f5f9;height:8px;font-size:0;line-height:0'>&nbsp;</td>" : '';
                $cRows .= "
                <div style='margin-bottom:12px'>
                  <table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:5px'>
                    <tr>
                      <td style='font-size:13px;color:#333;font-weight:500'>" . htmlspecialchars($cm['username']) . "</td>
                      <td style='text-align:right;font-size:11px;color:#aaa'>{$tot} tareas</td>
                    </tr>
                  </table>
                  <table width='100%' cellpadding='0' cellspacing='0' style='border-radius:4px;overflow:hidden;height:8px;background:#f1f5f9;table-layout:fixed'>
                    <tr>{$doneTd}{$missedTd}{$restTd}</tr>
                  </table>
                  <table width='100%' cellpadding='0' cellspacing='0' style='margin-top:4px'>
                    <tr>
                      <td style='font-size:11px;color:#16a34a;font-weight:600'>{$cm['done']} completadas</td>
                      <td style='text-align:right;font-size:11px;color:#dc2626;font-weight:600'>{$cm['missed']} fallidas</td>
                    </tr>
                  </table>
                </div>";
            }
            $choresSection = "
            <div style='margin-bottom:24px'>
              <div style='font-size:11px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.07em;margin-bottom:12px'>Cumplimiento de tareas</div>
              {$cRows}
            </div>";
        }

        // ── SECTION: Top expenses ──────────────────────────────────────────
        $topSection = '';
        if (!empty($stats['top_expenses'])) {
            $tRows = '';
            foreach ($stats['top_expenses'] as $i => $e) {
                $num = ($i + 1) . '.';
                $tRows .= "
                <tr>
                  <td style='padding:9px 0;border-bottom:1px solid #f5f5f5'>
                    <div style='font-size:13px;color:#222;font-weight:500'>" . htmlspecialchars($e['title']) . "</div>
                    <div style='font-size:11px;color:#aaa;margin-top:2px'>" . htmlspecialchars($e['paid_by_name']) . "</div>
                  </td>
                  <td style='padding:9px 0;border-bottom:1px solid #f5f5f5;text-align:right;font-size:14px;font-weight:800;color:#1d4ed8;white-space:nowrap'>
                    €" . number_format($e['amount'], 2) . "
                  </td>
                </tr>";
            }
            $topSection = "
            <div style='margin-bottom:24px'>
              <div style='font-size:11px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.07em;margin-bottom:12px'>Mayores gastos</div>
              <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse'>{$tRows}</table>
            </div>";
        }

        // ── Divider helper ────────────────────────────────────────────────
        $div = "<div style='height:1px;background:#f0f0f0;margin:0 0 24px'></div>";

        $content = "
          <h2 style='margin:0 0 4px;font-size:22px;font-weight:800;color:#111'>Resumen de {$monthLabel}</h2>
          <p style='color:#888;font-size:13px;margin:0 0 28px'>{$home}</p>

          <div style='background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:22px;margin-bottom:24px;text-align:center'>
            <div style='font-size:11px;font-weight:700;color:#3b82f6;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px'>Tu balance del mes</div>
            <div style='font-size:36px;font-weight:900;color:{$balColor}'>{$balSign}€" . number_format(abs($myBalance), 2) . "</div>
            <div style='font-size:12px;color:#888;margin-top:6px'>" . ($myBalance >= 0 ? 'El hogar te debe' : 'Debes al hogar') . "</div>
          </div>

          {$statsBox}
          {$div}
          {$catSection}
          {$div}
          {$memberSection}
          {$div}
          {$choresSection}
          {$topSection}

          <a href='{$dashUrl}' style='display:block;text-align:center;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;text-decoration:none;padding:14px;border-radius:10px;font-weight:700;font-size:14px;letter-spacing:0.01em'>Ver dashboard completo &rarr;</a>";

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
