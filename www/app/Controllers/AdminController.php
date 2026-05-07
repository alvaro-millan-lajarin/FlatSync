<?php

namespace App\Controllers;

class AdminController extends BaseController
{
    private function requireAdmin(): bool
    {
        if ($this->requireLogin()) return true;
        $adminId = (int)(getenv('ADMIN_USER_ID') ?: 1);
        if ((int) session()->get('user_id') !== $adminId) {
            return redirect()->to('/dashboard')->send() || true;
        }
        return false;
    }

    public function leads()
    {
        if ($this->requireAdmin()) return;

        $db = \Config\Database::connect();

        $providers = $db->query("
            SELECT
                sp.*,
                COUNT(l.id)                                              AS leads_total,
                SUM(MONTH(l.created_at) = MONTH(NOW())
                    AND YEAR(l.created_at) = YEAR(NOW()))                AS leads_month
            FROM service_providers sp
            LEFT JOIN leads l ON l.provider_id = sp.id
            GROUP BY sp.id
            ORDER BY sp.active DESC, leads_month DESC, sp.created_at DESC
        ")->getResultArray();

        return view('admin/leads', [
            'pageTitle' => 'Panel de leads',
            'providers' => $providers,
        ]);
    }

    public function toggleProvider(int $id)
    {
        if ($this->requireAdmin()) return;
        $model    = model('ServiceProviderModel');
        $provider = $model->find($id);
        if (!$provider) return redirect()->to('/admin/leads');
        $model->update($id, ['active' => $provider['active'] ? 0 : 1]);
        return redirect()->to('/admin/leads');
    }

    public function deleteProvider(int $id)
    {
        if ($this->requireAdmin()) return;
        model('ServiceProviderModel')->delete($id);
        return redirect()->to('/admin/leads');
    }
}
