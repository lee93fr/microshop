<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\NotificationTemplateService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $definitions = NotificationTemplateService::definitions();

        $templates = [];
        foreach ($definitions as $key => $def) {
            $templates[$key] = [
                'def'     => $def,
                'subject' => Setting::get("notif_tpl_{$key}_subject") ?: NotificationTemplateService::defaultSubject($key),
                'body'    => Setting::get("notif_tpl_{$key}_body")    ?: NotificationTemplateService::defaultBody($key),
            ];
        }

        return view('admin.notifications.index', compact('templates'));
    }

    public function update(Request $request, string $key)
    {
        $definitions = NotificationTemplateService::definitions();

        if (!array_key_exists($key, $definitions)) {
            abort(404);
        }

        $request->validate([
            'subject' => 'required|string|max:255',
            'body'    => 'required|string',
        ]);

        Setting::set("notif_tpl_{$key}_subject", $request->subject);
        Setting::set("notif_tpl_{$key}_body",    $request->body);

        return back()->with('success', 'Template sauvegardé.');
    }

    public function reset(string $key)
    {
        $definitions = NotificationTemplateService::definitions();

        if (!array_key_exists($key, $definitions)) {
            abort(404);
        }

        Setting::set("notif_tpl_{$key}_subject", '');
        Setting::set("notif_tpl_{$key}_body",    '');

        return back()->with('success', 'Template réinitialisé aux valeurs par défaut.');
    }
}
