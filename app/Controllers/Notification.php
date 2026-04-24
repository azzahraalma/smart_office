<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\NotificationModel;

class Notification extends BaseController
{
    protected $notifModel;

    public function __construct()
    {
        $this->notifModel = new NotificationModel();
    }

    // LIST NOTIFIKASI
    public function index()
    {
        $userId = session()->get('user_id');

        // Ambil notif khusus user + urut terbaru
        $notifications = $this->notifModel
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // 🔥 AUTO MARK AS READ (biar titik hilang)
        $this->notifModel
            ->where('user_id', $userId)
            ->where('is_read', 0)
            ->set(['is_read' => 1])
            ->update();

        return view('notifications/index', [
            'notifications' => $notifications
        ]);
    }

    // MARK 1 NOTIFIKASI
    public function markAsRead($id)
    {
        $this->notifModel->update($id, ['is_read' => 1]);

        return redirect()->back();
    }

    // MARK SEMUA NOTIFIKASI
    public function markAll()
    {
        $userId = session()->get('user_id');

        $this->notifModel
            ->where('user_id', $userId)
            ->set(['is_read' => 1])
            ->update();

        return redirect()->back();
    }

    // CLEAR SEMUA NOTIFIKASI
    public function clear()
    {
        $userId = session()->get('user_id');

        $this->notifModel
            ->where('user_id', $userId)
            ->delete();

        return redirect()->back();
    }
}