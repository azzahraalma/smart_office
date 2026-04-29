<?php

namespace App\Controllers;

use App\Models\TaskModel;
use App\Models\TaskFileModel;
use App\Helpers\NotificationHelper;

class Task extends BaseController
{
    protected $taskModel;
    protected $taskFileModel;

    public function __construct()
    {
        $this->taskModel     = new TaskModel();
        $this->taskFileModel = new TaskFileModel();
    }

    //index
    public function index()
    {
        $role   = session()->get('role');
        $userId = session()->get('user_id');

        if ($role === 'manager') {
            $tasks = $this->taskModel->orderBy('created_at', 'DESC')->findAll();
        } else {
            $tasks = $this->taskModel
                ->where('assigned_to', $userId)
                ->orderBy('created_at', 'DESC')
                ->findAll();
        }

        $notifModel = new \App\Models\NotificationModel();
        foreach ($tasks as $t) {
            if (
                $t['deadline'] &&
                strtotime($t['deadline']) < time() &&
                $t['status'] !== 'done' &&
                $t['assigned_to']
            ) {
                $sudahAda = $notifModel
                    ->where('user_id', $t['assigned_to'])
                    ->like('pesan', $t['judul'])
                    ->like('judul', 'Telat')
                    ->first();

                if (!$sudahAda) {
                    NotificationHelper::taskTelat($t['assigned_to'], $t['judul'], $t['deadline']);
                }
            }
        }

        return view('task/index', ['tasks' => $tasks]);
    }
    // create
    public function create()
    {
        if (session()->get('role') !== 'manager') {
            return redirect()->to('/task')->with('error', 'Akses ditolak');
        }

        $db       = \Config\Database::connect();
        $karyawan = $db->table('users')
            ->where('role', 'karyawan')
            ->get()->getResultArray();

        return view('task/create', ['karyawan' => $karyawan]);
    }

    // store
    public function store()
    {
        if (session()->get('role') !== 'manager') {
            return redirect()->to('/task')->with('error', 'Akses ditolak');
        }

        $managerFile = null;
        $file = $this->request->getFile('manager_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];
            if (!in_array(strtolower($file->getExtension()), $allowed)) {
                return redirect()->back()->with('error', 'Format file tidak didukung');
            }
            if (!is_dir(FCPATH . 'uploads/tasks')) {
                mkdir(FCPATH . 'uploads/tasks', 0777, true);
            }
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/tasks', $newName);
            $managerFile = $newName;
        }

        $judul      = $this->request->getPost('judul');
        $prioritas  = $this->request->getPost('prioritas');
        $assignedTo = (int) $this->request->getPost('assigned_to');

        $deadlineDate = $this->request->getPost('deadline_date');
        $deadlineTime = $this->request->getPost('deadline_time') ?: '00:00';
        $deadline     = $deadlineDate ? $deadlineDate . ' ' . $deadlineTime . ':00' : null;

        $this->taskModel->insert([
            'judul'        => $judul,
            'deskripsi'    => $this->request->getPost('deskripsi'),
            'deadline'     => $deadline,
            'prioritas'    => $prioritas,
            'status'       => 'todo',
            'dibuat_oleh'  => session()->get('user_id'),
            'assigned_to'  => $assignedTo,
            'manager_file' => $managerFile,
        ]);

        NotificationHelper::taskBaru($assignedTo, $judul, $deadline, $prioritas);

        return redirect()->to('/task')->with('success', 'Task berhasil dibuat');
    }
    // task detail
    public function detail($id)
    {
        $task = $this->taskModel->find($id);

        if (!$task) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Task tidak ditemukan");
        }

        $role   = session()->get('role');
        $userId = session()->get('user_id');

        if ($role === 'karyawan' && $task['assigned_to'] != $userId) {
            return redirect()->to('/task')->with('error', 'Akses ditolak');
        }

        $files = $this->taskFileModel->getByTask($id);

        return view('task/detail', [
            'task'  => $task,
            'files' => $files,
        ]);
    }

    // task update progress
    public function updateStatus($id)
    {
        $task   = $this->taskModel->find($id);
        $userId = session()->get('user_id');

        if (!$task || $task['assigned_to'] != $userId) {
            return redirect()->to('/task')->with('error', 'Akses ditolak');
        }

        $status  = $this->request->getPost('status');
        $allowed = ['todo', 'on_progress', 'done'];

        if (!in_array($status, $allowed)) {
            return redirect()->back()->with('error', 'Status tidak valid');
        }

        $this->taskModel->update($id, ['status' => $status]);

        NotificationHelper::taskProgressUpdate($userId, $task['judul'], $status);

        return redirect()->to('/task/detail/' . $id)->with('success', 'Status berhasil diupdate');
    }

    // delete task
    public function delete($id)
    {
        if (session()->get('role') !== 'manager') {
            return redirect()->to('/task')->with('error', 'Akses ditolak');
        }

        $this->taskModel->delete($id);

        return redirect()->to('/task')->with('success', 'Task dihapus');
    }

    // upload hasil task
    public function upload($id)
    {
        $task   = $this->taskModel->find($id);
        $userId = session()->get('user_id');
        $role   = session()->get('role');

        if (!$task) {
            return redirect()->back()->with('error', 'Task tidak ditemukan');
        }

        if ($role === 'karyawan' && $task['assigned_to'] != $userId) {
            return redirect()->back()->with('error', 'Akses ditolak');
        }

        $file = $this->request->getFile('file');

        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return redirect()->back()->with('error', 'File tidak valid');
        }

        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];

        if (!in_array(strtolower($file->getExtension()), $allowed)) {
            return redirect()->back()->with('error', 'Format file tidak didukung');
        }

        if (!is_dir(FCPATH . 'uploads/tasks')) {
            mkdir(FCPATH . 'uploads/tasks', 0777, true);
        }

        $originalName = $file->getClientName();
        $newName      = $file->getRandomName();
        $file->move(FCPATH . 'uploads/tasks', $newName);

        $this->taskFileModel->insert([
            'task_id'       => $id,
            'user_id'       => $userId,
            'file_name'     => $newName,
            'original_name' => $originalName,
        ]);

        return redirect()->back()->with('success', 'File berhasil diupload');
    }
}