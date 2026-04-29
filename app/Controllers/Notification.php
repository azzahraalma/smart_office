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

    // list notif
    public function index()
    {
        $userId = session()->get('user_id');

        // short
        $notifications = $this->notifModel
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // mark all as read
        $this->notifModel
            ->where('user_id', $userId)
            ->where('is_read', 0)
            ->set(['is_read' => 1])
            ->update();

        return view('notifications/index', [
            'notifications' => $notifications
        ]);
    }

    // mark 1 as read
    public function markAsRead($id)
    {
        $this->notifModel->update($id, ['is_read' => 1]);

        return redirect()->back();
    }

    // mark all as read
    public function markAll()
    {
        $userId = session()->get('user_id');

        $this->notifModel
            ->where('user_id', $userId)
            ->set(['is_read' => 1])
            ->update();

        return redirect()->back();
    }

    // delete notif
    public function clear()
    {
        $userId = session()->get('user_id');

        $this->notifModel
            ->where('user_id', $userId)
            ->delete();

        return redirect()->back();
    }
}